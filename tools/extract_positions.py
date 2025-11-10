"""
Interactive position extractor for number slots.
Usage:
  python tools\extract_positions.py --image path\to\sample.png --output template.json

Instructions:
 - Click and drag with left mouse to draw a rectangle for each slot (top-left to bottom-right).
 - Each completed rectangle will be stored as the next slot (slot0, slot1, ...).
 - Press 'a' to add the current rectangle to the list (if you want to keep drawing), 's' to save & exit, or 'q'/ESC to quit without saving.

Output JSON format:
{
  "image": "sample.png",
  "width": 1080,
  "height": 1350,
  "slots": [
    { "id": 0, "cx": 0.25, "cy": 0.1, "w": 0.12, "h": 0.08 },
    ...
  ]
}
All coordinates are normalized (relative to image width/height) so you can reuse the template on different backgrounds.

"""
import cv2
import json
import argparse
import os

slots = []
drawing = False
ix, iy = -1, -1
img = None
img_disp = None
current_rect = None


def mouse_callback(event, x, y, flags, param):
    global ix, iy, drawing, img_disp, current_rect
    if event == cv2.EVENT_LBUTTONDOWN:
        drawing = True
        ix, iy = x, y
        current_rect = None
    elif event == cv2.EVENT_MOUSEMOVE:
        if drawing:
            img_disp = img.copy()
            cv2.rectangle(img_disp, (ix, iy), (x, y), (0,255,0), 2)
            # redraw existing slots
            for s in slots:
                x0p = int((s['cx']-s['w']/2.0)*img.shape[1])
                y0p = int((s['cy']-s['h']/2.0)*img.shape[0])
                x1p = int((s['cx']+s['w']/2.0)*img.shape[1])
                y1p = int((s['cy']+s['h']/2.0)*img.shape[0])
                cv2.rectangle(img_disp, (x0p,y0p),(x1p,y1p),(255,0,0),2)
    elif event == cv2.EVENT_LBUTTONUP:
        drawing = False
        x0, y0 = min(ix, x), min(iy, y)
        x1, y1 = max(ix, x), max(iy, y)
        current_rect = (x0, y0, x1, y1)
        img_disp = img.copy()
        for s in slots:
            x0p = int((s['cx']-s['w']/2.0)*img.shape[1])
            y0p = int((s['cy']-s['h']/2.0)*img.shape[0])
            x1p = int((s['cx']+s['w']/2.0)*img.shape[1])
            y1p = int((s['cy']+s['h']/2.0)*img.shape[0])
            cv2.rectangle(img_disp, (x0p,y0p),(x1p,y1p),(255,0,0),2)
        cv2.rectangle(img_disp, (x0,y0),(x1,y1),(0,255,0),2)


def add_current_rect():
    global current_rect, slots
    if not current_rect:
        return False
    x0, y0, x1, y1 = current_rect
    h, w = img.shape[0], img.shape[1]
    cx = (x0 + x1) / 2.0 / w
    cy = (y0 + y1) / 2.0 / h
    rw = (x1 - x0) / w
    rh = (y1 - y0) / h
    slot = { 'id': len(slots), 'cx': round(cx,6), 'cy': round(cy,6), 'w': round(rw,6), 'h': round(rh,6) }
    slots.append(slot)
    return True


def main():
    global img, img_disp, current_rect
    p = argparse.ArgumentParser()
    p.add_argument('--image', '-i', required=True)
    p.add_argument('--output', '-o', default='template.json')
    args = p.parse_args()

    if not os.path.exists(args.image):
        print('Image not found:', args.image)
        return

    img = cv2.imread(args.image)
    if img is None:
        print('Failed to open image')
        return
    img_disp = img.copy()

    win = 'extract'
    cv2.namedWindow(win)
    cv2.setMouseCallback(win, mouse_callback)

    print('Instructions: click and drag to draw a slot rectangle. Press a to add current rect, s to save & exit, q/ESC to quit without saving.')

    while True:
        cv2.imshow(win, img_disp)
        key = cv2.waitKey(20) & 0xFF
        if key == ord('q') or key == 27:
            print('Exiting without save.')
            break
        if key == ord('a'):
            if current_rect:
                if add_current_rect():
                    print('Added slot', len(slots)-1)
                    current_rect = None
                else:
                    print('No current rect to add')
        if key == ord('s'):
            # if there's a current rect, add it
            if current_rect:
                add_current_rect()
                current_rect = None
            out = {
                'image': os.path.basename(args.image),
                'width': img.shape[1],
                'height': img.shape[0],
                'slots': slots
            }
            with open(args.output, 'w', encoding='utf-8') as f:
                json.dump(out, f, indent=2, ensure_ascii=False)
            print('Saved template to', args.output)
            break

    cv2.destroyAllWindows()

if __name__ == '__main__':
    main()
