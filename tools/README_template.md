Template-based overlay workflow

Goal
- Extract positions of number slots from a sample image (so positions are reusable), then render or paste numbers onto any background image using the saved template.

Files created
- tools/extract_positions.py  -- interactive tool (OpenCV) to draw rectangles for each number slot and save normalized coordinates to template.json
- tools/apply_template.py     -- apply template to an arbitrary background; supports pasting per-slot PNGs or rendering text labels
- tools/README_template.md    -- this file

Install (Windows / PowerShell)
1) Install Python 3.8+ from python.org (if you don't have it).
2) Install dependencies:
   pip install opencv-python pillow numpy

Extract positions (interactive)
1) Open PowerShell in repository root (C:\HuayKinMaiMod_portable)
2) Run:
   python tools\extract_positions.py --image path\to\sample.png --output tools\template.json
3) In the window that opens:
   - Click and drag to draw a rectangle for each slot (top-left to bottom-right).
   - Press 'a' to add the current rectangle to the list.
   - Press 's' to save template.json and exit.
   - Press 'q' or ESC to exit without saving.

Result: tools\template.json with normalized coordinates.

Apply template onto new background
Option A — use per-slot images:
 - Put each slot image named slot0.png, slot1.png, ... into a folder, e.g. numbers\
 - Run:
   python tools\apply_template.py --background new_bg.png --template tools\template.json --numbers-dir numbers --output out.png

Option B — render labels (text) from JSON:
 - Create labels.json, e.g. { "0": "8", "1": "86", "2": "72" }
 - Run:
   python tools\apply_template.py --background new_bg.png --template tools\template.json --labels labels.json --font C:\Windows\Fonts\Arial.ttf --output out.png

Notes / Tips
- The template stores normalized positions so the slots scale with background size. If your background aspect ratio differs a lot you may need to adjust templates or run extraction on a sample with similar aspect ratio.
- If you want numbers with outlines/stroke, prepare PNGs with transparency and appropriate size for best results.
- For higher control, after extraction you can manually edit tools\template.json to tweak cx/cy/w/h values (they are fractions between 0 and 1).

If you want, I can:
- run the extraction on your sample (if you put sample image in repo and tell me the filename),
- or run the apply step to generate an example output from a background you place in the repo.

Tell me which action to do next: (1) I'll run extraction if you placed `sample.png` in repo, (2) I'll run apply if you placed `template.json` and a background, or (3) show an example command to run locally.