HUAYKINMAIMOD PORTABLE 8.2 - MOCK ALL-IN-ONE (ENGLISH)

What this package is
---------------------
This is a self-contained MOCK edition of HuayKinMaiMod for testing and demo.
It includes a mock HuayKinMaiMod_Full_Build_Final.zip (index.php + assets) and a bundled PowerShell downloader for PHP 8.2.

How to use
----------
1. Extract the ZIP to any folder (e.g., C:\HuayKinMaiMod_portable)
2. Double-click AUTO_RUN_HUAYKINMAIMOD_Portable.bat
   - The script will extract the mock project, download PHP 8.2 if needed (requires internet), start PHP built-in server, and open the demo page.

Notes
-----
- This is a MOCK edition to allow you to test the portable runner and UI locally without providing your real project ZIP.
- If you want me to create a true All-in-One including your real HuayKinMaiMod_Full_Build_Final.zip, upload the ZIP here and I will rebuild the package and send it to you.
- The downloader uses a fixed PHP filename (php-8.2.29) as of mid-2025. If the URL changes, edit download_php.ps1 to the correct file.

Created automatically by assistant for Woravej


Local AI image generator (experimental)
---------------------------------------
If you want to render brand-new background art or concepts locally, a simple Python helper is now included.

Requirements:
- Windows with Python 3.10+ and at least 12 GB VRAM recommended (CPU works but is extremely slow).
- Internet access on the first run to download the Stable Diffusion model from Hugging Face (unless you provide a local path).

Setup steps:
1. Open PowerShell inside this folder (Shift + Right Click → "Open PowerShell window here").
2. Create / activate a virtual environment (optional but recommended).
3. Install dependencies:
   pip install -r tools/requirements_local_ai.txt
4. Run the generator with your prompt (defaults to SDXL Turbo with 4 steps ~4GB VRAM):
   python tools/local_image_generator.py --prompt "golden ticket booth in thai style" --output generated\ticket.png

Tips for low resource machines:
- Add ``--engine kandinsky2.2`` for a ~2GB option (slightly slower, softer output).
- Reduce resolution using ``--width`` / ``--height`` (e.g. 384) to cut memory use.
- Keep ``--steps`` small (1-6 for turbo, 10-20 for SD1.5) to reduce compute.

Useful flags:
- --negative "low quality, blurry" to avoid undesired artifacts.
- --engine sd15 to revert to Stable Diffusion 1.5 if you have ≥6GB VRAM.
- --model path\to\local\model to point at weights you have already downloaded.
- --seed 1234 to make the result reproducible.
- --numbers 500 23 789 จะสร้างพรอมพ์ให้อัตโนมัติ (อ่านเป็นไทย: ห้า ศูนย์ ศูนย์ ฯลฯ) พร้อมระบุพื้นหลัง ``bg_custom_เซียนตอง``
- --bg-name ชื่อไฟล์พื้นหลัง เพื่อให้พรอมพ์อ้างอิงพื้นหลังอื่น

Outputs are saved as PNG under the path you provide. You can then copy the result into assets/ or any other folder.
