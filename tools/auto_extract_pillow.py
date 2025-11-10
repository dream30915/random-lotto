"""
Auto-extract slot bounding boxes using Pillow and a simple flood-fill (no OpenCV / no NumPy compile required).
Usage:
  python tools\auto_extract_pillow.py --image เซียนตอง.png --output tools\template_auto_pillow.json

This is a heuristic: it finds bright regions (white text with dark outlines) and returns bounding boxes.
"""
from PIL import Image
import argparse
import json
import os


def find_components(img, thresh=200, min_area=100):
    # img: grayscale PIL Image
    w,h = img.size
    pix = img.load()
    visited = [[False]*h for _ in range(w)]
    comps = []
    for y in range(h):
        for x in range(w):
            if visited[x][y]:
                continue
            visited[x][y] = True
            if pix[x,y] <= thresh:
                continue
            # flood fill
            stack = [(x,y)]
            xs = []
            ys = []
            area = 0
            while stack:
                cx, cy = stack.pop()
                if cx < 0 or cy < 0 or cx >= w or cy >= h:
                    continue
                if visited[cx][cy]:
                    continue
                visited[cx][cy] = True
                if pix[cx,cy] <= thresh:
                    continue
                area += 1
                xs.append(cx); ys.append(cy)
                # neighbors
                stack.append((cx-1, cy)); stack.append((cx+1, cy)); stack.append((cx, cy-1)); stack.append((cx, cy+1))
            if area >= min_area:
                x0 = min(xs); x1 = max(xs)
                y0 = min(ys); y1 = max(ys)
                comps.append((x0,y0,x1-x0+1,y1-y0+1, area))
    return comps


def merge_boxes(boxes, iou_thresh=0.2):
    merged = []
    for b in boxes:
        x,y,w,h,a = b
        found = False
        for i, m in enumerate(merged):
            mx,my,mw,mh,ma = m
            # compute IoU
            x2 = max(x, mx); y2 = max(y, my)
            xe = min(x+w, mx+mw); ye = min(y+h, my+mh)
            inter_w = max(0, xe-x2); inter_h = max(0, ye-y2)
            inter = inter_w*inter_h
            area1 = w*h; area2 = mw*mh
            union = area1 + area2 - inter
            iou = inter/union if union>0 else 0
            if iou > iou_thresh:
                nx = min(x, mx); ny = min(y, my); nx2 = max(x+w, mx+mw); ny2 = max(y+h, my+mh)
                merged[i] = (nx, ny, nx2-nx, ny2-ny, ma+ a)
                found = True
                break
        if not found:
            merged.append(list(b))
    return merged


def main():
    p = argparse.ArgumentParser()
    p.add_argument('--image','-i', required=True)
    p.add_argument('--output','-o', default='tools/template_auto_pillow.json')
    args = p.parse_args()

    if not os.path.exists(args.image):
        print('Image not found:', args.image)
        return
    im = Image.open(args.image).convert('L')
    w,h = im.size
    # optionally resize for speed if very large
    maxw = 1000
    scale = 1.0
    if w > maxw:
        scale = maxw / w
        im_small = im.resize((int(w*scale), int(h*scale)), Image.LANCZOS)
    else:
        im_small = im
    # increase contrast by simple point transform
    # find components
    comps = find_components(im_small, thresh=200, min_area=50)
    if not comps:
        # try lower threshold
        comps = find_components(im_small, thresh=160, min_area=40)
    # scale boxes back
    boxes = []
    for (x,y,ww,hh,a) in comps:
        if scale != 1.0:
            x = int(x/scale); y = int(y/scale); ww = int(ww/scale); hh = int(hh/scale)
        boxes.append((x,y,ww,hh,a))
    boxes = merge_boxes(boxes, iou_thresh=0.15)
    # filter tiny boxes
    boxes = [b for b in boxes if b[2] > 8 and b[3] > 8]
    boxes.sort(key=lambda b: (b[1], b[0]))
    slots = []
    for i,(x,y,ww,hh,a) in enumerate(boxes):
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
