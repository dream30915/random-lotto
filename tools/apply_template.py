"""
Apply a saved template of slots onto a new background.
Usage examples:
  # use images for each slot (slot0.png, slot1.png, ... in numbers dir)
  python tools/apply_template.py --background bg.png --template template.json --numbers-dir numbers --output out.png

  # or render text labels from JSON mapping
  python tools/apply_template.py --background bg.png --template template.json --labels labels.json --output out.png --font C:/Windows/Fonts/Arial.ttf

Template format is produced by extract_positions.py (normalized cx,cy,w,h).

Behavior:
 - For each slot in template, either an image file named slot{ID}.png inside --numbers-dir will be pasted and resized to the slot size;
 - Or, if --labels is provided (JSON mapping slot id -> text), the script will render text with Pillow and center it in the slot.

Dependencies:
 pip install pillow numpy

"""
from PIL import Image, ImageDraw, ImageFont
import json
import argparse
import os


def load_template(path):
    with open(path, 'r', encoding='utf-8') as f:
        return json.load(f)


def paste_image(base, slot, img_path):
    # slot: dict with cx,cy,w,h normalized
    bw, bh = base.size
    slot_w = int(round(slot['w'] * bw))
    slot_h = int(round(slot['h'] * bh))
    cx = int(round(slot['cx'] * bw))
    cy = int(round(slot['cy'] * bh))
    # open number image
    num = Image.open(img_path).convert('RGBA')
    # resize preserving aspect to fit within slot
    num.thumbnail((slot_w, slot_h), Image.LANCZOS)
    # compute paste top-left
    x = cx - num.width//2
    y = cy - num.height//2
    base.alpha_composite(num, (x, y))


def render_text(base, slot, text, font_path=None, font_size=None, fill=(255,255,255,255), stroke_width=2, stroke_fill=(0,0,0,255)):
    bw, bh = base.size
    slot_w = int(round(slot['w'] * bw))
    slot_h = int(round(slot['h'] * bh))
    cx = int(round(slot['cx'] * bw))
    cy = int(round(slot['cy'] * bh))
    draw = ImageDraw.Draw(base)
    if font_path:
        # choose font size if not specified
        if font_size is None:
            # approximate font size relative to slot height
            font_size = max(12, int(slot_h * 0.7))
        try:
            font = ImageFont.truetype(font_path, font_size)
        except Exception as e:
            print('Failed to load font, falling back to default:', e)
            font = ImageFont.load_default()
    else:
        font = ImageFont.load_default()
    # measure text and adjust size down if needed
    w, h = draw.textsize(text, font=font)
    # shrink font if too wide
    if w > slot_w:
        # reduce font size
        if hasattr(font, 'size'):
            # try iteratively
            fs = font.size
            while w > slot_w and fs > 6:
                fs -= 2
                try:
                    font = ImageFont.truetype(font_path, fs) if font_path else ImageFont.load_default()
                    w, h = draw.textsize(text, font=font)
                except:
                    break
    x = cx - w//2
    y = cy - h//2
    # draw stroke
    if stroke_width and stroke_fill:
        # draw stroke by drawing text multiple times offset
        for dx in range(-stroke_width, stroke_width+1):
            for dy in range(-stroke_width, stroke_width+1):
                if dx == 0 and dy == 0:
                    continue
                draw.text((x+dx, y+dy), text, font=font, fill=stroke_fill)
    draw.text((x,y), text, font=font, fill=fill)


def main():
    p = argparse.ArgumentParser()
    p.add_argument('--background', '-b', required=True)
    p.add_argument('--template', '-t', required=True)
    p.add_argument('--numbers-dir', help='Directory with slot{ID}.png images')
    p.add_argument('--labels', help='JSON file mapping slot id to text, e.g. {"0":"8","1":"86"}')
    p.add_argument('--font', help='path to .ttf font to use when rendering text')
    p.add_argument('--output', '-o', default='out.png')
    args = p.parse_args()

    tpl = load_template(args.template)
    bg = Image.open(args.background).convert('RGBA')

    # ensure output size based on background
    base = Image.new('RGBA', bg.size)
    base.paste(bg, (0,0))

    # for each slot
    for slot in tpl.get('slots',[]):
        sid = slot.get('id')
        if args.numbers_dir:
            candidate = os.path.join(args.numbers_dir, f'slot{sid}.png')
            if os.path.exists(candidate):
                paste_image(base, slot, candidate)
                continue
        if args.labels:
            with open(args.labels, 'r', encoding='utf-8') as f:
                labels = json.load(f)
            text = labels.get(str(sid)) or labels.get(sid)
            if text:
                render_text(base, slot, str(text), font_path=args.font)
                continue
        # if no number or label provided, skip

    # save
    base.convert('RGB').save(args.output)
    print('Saved', args.output)

if __name__ == '__main__':
    main()
