"""
Automatic slot extractor (non-interactive).
Usage:
  python tools\auto_extract.py --image path\to\image.png --output tools\template_auto.json

This script uses simple image processing heuristics to locate candidate number blobs (white text with dark outline).
It outputs a template JSON with normalized slots (cx, cy, w, h). Review and edit the output if needed.

Dependencies: opencv-python, numpy
"""
import cv2
import numpy as np
import argparse
import json
import os


def dedupe_boxes(boxes, iou_thresh=0.3):
    # boxes: list of (x,y,w,h)
    keep = []
    for b in boxes:
        x,y,w,h = b
        found = False
        for k in keep:
            # compute IoU
            x2 = max(x, k[0]); y2 = max(y, k[1]);
            x_end = min(x+w, k[0]+k[2]); y_end = min(y+h, k[1]+k[3]);
            inter_w = max(0, x_end - x2); inter_h = max(0, y_end - y2);
            inter = inter_w * inter_h
            area1 = w*h; area2 = k[2]*k[3]
            union = area1 + area2 - inter
            iou = inter / union if union>0 else 0
            if iou > iou_thresh:
                # merge by union box
                nx = min(x, k[0]); ny = min(y, k[1]); nx2 = max(x+w, k[0]+k[2]); ny2 = max(y+h, k[1]+k[3])
                k[0]=nx; k[1]=ny; k[2]=nx2-nx; k[3]=ny2-ny
                found = True
                break
        if not found:
            keep.append([x,y,w,h])
    return keep


def main():
    p = argparse.ArgumentParser()
    p.add_argument('--image','-i', required=True)
    p.add_argument('--output','-o', default='tools/template_auto.json')
    args = p.parse_args()

    if not os.path.exists(args.image):
        print('Image not found:', args.image)
        return
    img = cv2.imread(args.image)
    if img is None:
        print('Failed to open image')
        return
    h, w = img.shape[:2]
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    # emphasize bright regions
    # apply bilateral or gaussian
    blur = cv2.GaussianBlur(gray, (5,5), 0)
    # adaptive threshold to capture white text
    th = cv2.adaptiveThreshold(blur,255,cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)
    # invert: text (white) stays white
    # morphological operations to close gaps
    kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (5,5))
    mor = cv2.morphologyEx(th, cv2.MORPH_CLOSE, kernel, iterations=1)

    # find contours
    contours, _ = cv2.findContours(mor, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    boxes = []
    areas = []
    for cnt in contours:
        x,y,ww,hh = cv2.boundingRect(cnt)
        area = ww*hh
        areas.append(area)
        # filters: size relative to image
        if ww < 10 or hh < 10: continue
        if area < (w*h)*0.0004: continue  # filter tiny
        if ww > w*0.8 and hh > h*0.8: continue
        boxes.append((x,y,ww,hh))

    if not boxes:
        # fallback: use contour detection on Canny edges
        edges = cv2.Canny(blur, 50, 150)
        dil = cv2.dilate(edges, np.ones((3,3), np.uint8), iterations=1)
        contours, _ = cv2.findContours(dil, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        for cnt in contours:
            x,y,ww,hh = cv2.boundingRect(cnt)
            area = ww*hh
            if area < (w*h)*0.0005: continue
            boxes.append((x,y,ww,hh))

    # dedupe/merge overlapping boxes
    boxes = dedupe_boxes(boxes, iou_thresh=0.2)
    # filter by aspect and size further
    filtered = []
    for (x,y,ww,hh) in boxes:
        ar = ww/float(hh+1e-9)
        if ar > 10 or ar < 0.05: continue
        if ww < 0.02*w and hh < 0.02*h: continue
        filtered.append((x,y,ww,hh))

    # sort boxes top-to-bottom, left-to-right for consistency
    filtered.sort(key=lambda b: (b[1], b[0]))

    slots = []
    for i, (x,y,ww,hh) in enumerate(filtered):
        cx = (x + ww/2.0)/w
        cy = (y + hh/2.0)/h
        rw = ww/float(w)
        rh = hh/float(h)
        slots.append({'id': i, 'cx': round(cx,6), 'cy': round(cy,6), 'w': round(rw,6), 'h': round(rh,6)})

    out = {'image': os.path.basename(args.image), 'width': w, 'height': h, 'slots': slots}
    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(out, f, indent=2, ensure_ascii=False)
    print('Wrote', args.output, 'with', len(slots), 'slots')

if __name__ == '__main__':
    main()
