# HuayKinMaiMod Portable — AI Agent Working Guide

This file is a concise, hands‑on primer for automated coding agents working on this repository (Windows portable PHP image poster generator). Keep changes minimal and preserve the portable/offline design.

## Big picture — what and why
- Self-contained Windows portable package that runs a PHP built‑in server and generates poster PNGs with GD.
- Entry orchestration: `AUTO_RUN_HUAYKINMAIMOD_Portable.bat` (boots PHP, extracts `HuayKinMaiMod_Full_Build_Final.zip`, copies demo pages and launches server). Prefer updating that script for changes to start-up behavior.
- Core runtime endpoint: `HuayKinMaiMod_Extracted/download.php` (also mirrored at repo root `download.php` sometimes). It outputs binary PNGs directly — no HTML.

## Runtime & common commands
- Always prefer the bundled PHP binary (downloaded/extracted by `download_php.ps1`) — do not assume system PHP.
- Quick server run (PowerShell):
  - Example: `.in\php.exe -S localhost:8080 -t HuayKinMaiMod_Extracted -c php\php.ini` (adjust path to the actual bundled `php.exe`).
- Syntax check critical files after edits: `php.exe -l HuayKinMaiMod_Extracted\download.php` (do not print before headers in `download.php`).
- Port discovery: the batch script cycles ports 8080–8083. If adding networking changes, mirror that logic.

## Image generator (critical) — `download.php`
- Outputs: always send `Content-Type: image/png` and `Cache-Control: no-store` before output. Any extra text before headers will break downstream clients.
- Inputs (query params): `title`, `date` (Thai dd/mm/yy BE), `seed|n` (6 digits), `lead` (1 digit), `two` (comma list up to 3 two‑digit numbers), `three` (comma list up to 2 three‑digit numbers), `fb`, `line`, `layout` (`vip` or `classic`), `wm` (watermark flag). Keep these names — other pages rely on them.
- Sanitize strictly: numeric fields use `preg_replace('/[^0-9]/','', $v)`. Comma lists use `/[^0-9,]/` then `explode(',', ...)` + `array_filter`.
- Deterministic randomness: use `mt_srand((int)$seed); mt_rand()` pattern to preserve existing deterministic behavior.
- Font discovery: check bundled Kanit fonts first, then fall back to system fonts (`LeelawUI.ttf`, `Tahoma.ttf`, `NotoSansThai-Regular.ttf`). Avoid network font downloads.

## Conventions & patterns to follow
- No persistent state per-request. `download.php` must remain stateless and stream PNG output; don't write generated images to disk.
- Thai dates use Buddhist Era (+543). See existing snippet pattern: `$y_th = (int)date('Y', $t) + 543; $yy_th = substr((string)$y_th, -2);`.
- Keep localization and Thai labels intact when editing UI pages — front‑end relies on these formats.
- When adding new `layout` variants, branch inside the existing `if($layout==='vip'){ ... } else { ... }` logic in `download.php` and add font fallbacks accordingly.

## Testing & validation
- To test the image generator locally: run the bundled PHP server and open `HuayKinMaiMod_Extracted/demo_all.php` or `index.php` which build query strings for `download.php`.
- Validate GD availability: `php.exe -m` should list `gd`.
- After edits to `download.php`, run `php.exe -l HuayKinMaiMod_Extracted\download.php` to catch syntax errors.

## Dont's (repo-specific)
- Do not fetch external fonts or remote assets at runtime — this repo is explicitly portable/offline.
- Do not echo or print debugging text before PNG headers in `download.php` — it breaks client code and the simple server preview.

## Quick edits examples
- Add new font: append its path to the `$candidates` array in `download.php` (maintain the same fallback order).
- Add a new query param: sanitize like other params, add it to front-end builders in `predict.php`, `detail.php`, `demo_all.php`, and read it in `download.php`.

## Files to inspect for patterns and examples
- `AUTO_RUN_HUAYKINMAIMOD_Portable.bat` — full startup flow and port selection
- `download_php.ps1` — how bundled PHP is downloaded, validated and configured
- `HuayKinMaiMod_Extracted/download.php` and root `download.php` — core image generation
- `HuayKinMaiMod_Extracted/*.php` (index, demo_all, detail) — callers that build query strings

If anything above is unclear or you want a different level of detail (for example: code snippets for parameter sanitizers, deterministic seed implementation, or a test harness), tell me which section to expand and I will iterate.
# HuayKinMaiMod Portable – AI Agent Working Guide

> Concise project-specific rules for coding assistants. Focus on THIS repo’s actual patterns (Windows portable PHP image poster generator).

## Big Picture
- Self‑contained Windows portable package. Entry point is `AUTO_RUN_HUAYKINMAIMOD_Portable.bat` which: (1) ensures PHP (downloads via `download_php.ps1` if missing), (2) extracts `HuayKinMaiMod_Full_Build_Final.zip` into `HuayKinMaiMod_Extracted`, (3) copies demo pages (`index_list.php`→`index.php`, `detail.php`, `demo_all.php`), (4) launches PHP built‑in server `php.exe -S localhost:PORT -t HuayKinMaiMod_Extracted -c php.ini` and opens browser.
- Core dynamic endpoint that generates poster images is `HuayKinMaiMod_Extracted/download.php` (also mirrored at root `download.php` sometimes). It produces a PNG directly (no HTML) using GD.
- Front-end pages (`index.php`, `predict.php`, `detail.php`, `demo_all.php` / `examples.php`) build query strings and consume `download.php` as an image source, download target, or blob for sharing.
- No database, no framework: pure PHP + GD + static assets. Thai localization (Buddhist Era year +543) and lottery naming drive UI.

## Runtime & Scripts
- Always invoke PHP via the bundled `php.exe`; don’t assume globally installed PHP.
- Port discovery logic cycles through 8080–8083; don’t hardcode alternative unless updating batch script and docs.
- `download_php.ps1` selects latest acceptable cached archive, ensures size ≥ ~70MB, extracts, rewrites `php.ini` to enable `gd` and set `extension_dir = "ext"`.
- To syntax check a file: `php.exe -l path\to\file.php` (batch script already uses that pattern).

## Image Generator (`download.php`)
- Inputs (query params): `title`, `date` (Thai dd/mm/yy BE), `seed|n` (6 digits for deterministic pseudo-random fallback), `lead` (1 digit), `two` (comma list of up to 3 two‑digit numbers), `three` (comma list of up to 2 three‑digit numbers), `fb`, `line`, `layout` (`vip` or `classic`), `wm` ("1" to apply watermark). Sanitize strictly (see existing regex usage) – preserve patterns.
- Font discovery order: bundled Kanit paths, then Windows system fonts (`LeelawUI.ttf`, `Tahoma.ttf`, `NotoSansThai-Regular.ttf`). Keep fallback logic; avoid adding network font downloads.
- Always send: `Content-Type: image/png` and `Cache-Control: no-store` before output. Changing these may break preview/share flows.
- Keep generation stateless; no session usage here (other pages may start session but poster must remain lightweight).

## Conventions & Patterns
- Thai Buddhist Era year: `$y_th = (int)date('Y',$t)+543; $yy_th = substr((string)$y_th,-2); $dateThai = date('d/m/', $t).$yy_th;` Reuse helper snippet rather than re‑inventing.
- Parameter sanitization: digits via `preg_replace('/[^0-9]/','', ...)`; comma lists via `/[^0-9,]/` then `explode(',', ...)` + `array_filter`.
- Deterministic seed: use `mt_srand((int)$seed); mt_rand(0,999999)` → 6‑digit string; do not switch RNG unless documented.
- Avoid echoing text before headers in `download.php`—it must output only binary PNG (interferes with fetch/share APIs and VS Code Simple Browser). All fatal conditions should set HTTP status and `exit` early.
- UI pages build URLs with `URLSearchParams`; mirror existing key names; don’t rename params without updating every caller.

## When Extending
- Add new layouts: put branching inside existing `if($layout==='vip'){ ... } else { ... }` block; keep font fallback branch for non‑TTF.
- New social fields: add params + pill rendering near the “Social pills” section; preserve conditional rendering (only if value provided).
- Performance: Image is 1080x1350; be cautious enlarging (affects memory). Use `imagecopyresampled` for logo scaling.
- If introducing caching, ensure any dev mode bypass still sends `Cache-Control: no-store` unless you also adjust front-end cache busting (`_` timestamp param).

## Don’ts
- Don’t pull external fonts or remote assets at runtime (offline portable design).
- Don’t introduce composer, frameworks, or DB unless user explicitly wants a non‑portable build.
- Don’t remove Thai localization or change date format silently.
- Don’t write files on disk for each poster – keep in-memory PNG streaming.

## Quick Tasks Examples
- Add another font fallback: append path to `$candidates` array in `download.php`.
- Add a new param: sanitize similarly; include in URL builders in `predict.php`, `detail.php`, `demo_all.php`.
- Change watermark text: edit `$wmText` near bottom; keep alpha at ~115 for readability.

## Testing & Validation
- Run server manually (if batch not used): `php.exe -S localhost:8080 -t HuayKinMaiMod_Extracted -c php\php.ini` then open `/demo_all.php`.
- Validate GD availability: `php.exe -m` includes `gd`.
- Syntax check critical script after edits: `php.exe -l HuayKinMaiMod_Extracted\download.php`.

## Style & Language
- Preserve Thai labels and comments; add English inline only if clarifying without replacing existing Thai context.

Feedback welcome: clarify any missing workflow or add sections you need (e.g., test harness, additional layout patterns).
