<?php
// HUAYKINMAIMOD – Poster generator (PNG)
// query: n=123456 (required 6 digits)
// optional: title, date (DD/MM/YY)

if (!extension_loaded('gd')) { http_response_code(500); echo 'GD extension required.'; exit; }

if (!function_exists('load_image_resource')){
  function load_image_resource($path){
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($ext === 'png') { return @imagecreatefrompng($path); }
    if ($ext === 'gif') { return @imagecreatefromgif($path); }
    if ($ext === 'webp' && function_exists('imagecreatefromwebp')) { return @imagecreatefromwebp($path); }
    return @imagecreatefromjpeg($path);
  }
}

$n = isset($_GET['n']) ? preg_replace('/[^0-9]/','', $_GET['n']) : '';
if ($n === '') { $n = str_pad((string)rand(0,999999), 6, '0', STR_PAD_LEFT); }
$n = str_pad(substr($n,0,6), 6, '0', STR_PAD_LEFT);

// Date: default to Thai short date (dd/mm/yy in BE)
$t = time();
$y_th = (int)date('Y',$t) + 543; $yy_th = substr((string)$y_th, -2);
$dateText = isset($_GET['date']) ? trim($_GET['date']) : date('d/m/', $t) . $yy_th;
$title = isset($_GET['title']) && $_GET['title'] !== '' ? substr($_GET['title'],0,64) : 'สุ่มเลขแม่น ๆ by HuayKinMaiMod';
// Optional sub-blocks and layout/contact
$lead = isset($_GET['lead']) ? preg_replace('/[^0-9]/','', $_GET['lead']) : '';
$two  = isset($_GET['two']) ? preg_replace('/[^0-9,]/','', $_GET['two']) : '';
$three= isset($_GET['three'])? preg_replace('/[^0-9,]/','', $_GET['three']) : '';
$fb   = isset($_GET['fb'])   ? substr(trim($_GET['fb']),0,28) : '';
$line = isset($_GET['line'])  ? substr(trim($_GET['line']),0,28) : '';
$layout = isset($_GET['layout']) ? strtolower($_GET['layout']) : 'vip';

// Canvas – portrait for share: 1080x1350 (can be overridden by overlay/ref image)
$overlayMode = isset($_GET['overlay']) && $_GET['overlay']=='1';
$W = 1080; $H = 1350;
// Resolve assets directory robustly (support being required from HuayKinMaiMod_Extracted)
$assetsDir = realpath(__DIR__ . '/assets');
if ($assetsDir === false) { $assetsDir = __DIR__ . '/assets'; }
// If overlay mode requested and a bgfile is provided in assets, use that image size as canvas
$bgFileParam = isset($_GET['bgfile']) ? $_GET['bgfile'] : '';
if ($overlayMode && $bgFileParam !== ''){
  $san = basename($bgFileParam);
  $tryA = $assetsDir.'/'.$san;
  $tryB = $assetsDir.'/'.pathinfo($san, PATHINFO_FILENAME).'.png';
  $tryC = $assetsDir.'/'.pathinfo($san, PATHINFO_FILENAME).'.jpg';
  $found = '';
  if (file_exists($tryA)) $found = $tryA;
  elseif (file_exists($tryB)) $found = $tryB;
  elseif (file_exists($tryC)) $found = $tryC;
  else {
    // Try to be tolerant with unicode/normalization issues: scan assets for a filename
    // that contains the base name as a substring (case-insensitive).
    $base = pathinfo($san, PATHINFO_FILENAME);
    foreach (scandir($assetsDir) as $candidate) {
      if ($candidate === '.' || $candidate === '..') continue;
      if (stripos($candidate, $base) !== false) { $found = $assetsDir.'/'.$candidate; break; }
    }
    // Additional tolerant fallback: if the requested base looks corrupted, try matching well-known ascii prefix
    if ($found === ''){
      foreach (scandir($assetsDir) as $candidate) {
        if ($candidate === '.' || $candidate === '..') continue;
        if (stripos($candidate, 'bg_custom') !== false) { $found = $assetsDir.'/'.$candidate; break; }
      }
    }
    // As a last resort accept an absolute path provided by caller
    if ($found === '' && file_exists($bgFileParam)) { $found = $bgFileParam; }
  }
  if ($found !== ''){
    $info = @getimagesize($found);
    if ($info && isset($info[0]) && isset($info[1])){ $W = (int)$info[0]; $H = (int)$info[1]; }
  }
  // Debug: write resolved bg path and chosen canvas size for troubleshooting
  @file_put_contents(__DIR__ . '/debug_bg_lookup.txt', "found={$found}\nW={$W}\nH={$H}\nassetsDir={$assetsDir}\nbgFileParam={$bgFileParam}\n");
}
$im = imagecreatetruecolor($W,$H);

// scaling factor (relative to base canvas 1080x1350) used in overlay mode
$sScale = $W / 1080.0;

// Optional manual nudges when in overlay mode (px). Use to tweak pixel-perfect placement.
$overlayOffsetX = isset($_GET['overlay_offset_x']) ? (int)$_GET['overlay_offset_x'] : 0;
$overlayOffsetY = isset($_GET['overlay_offset_y']) ? (int)$_GET['overlay_offset_y'] : 0;

// If overlay mode, create a true transparent canvas and disable blending so nothing behind is drawn.
if ($overlayMode) {
  imagealphablending($im, false);
  imagesavealpha($im, true);
  $transparent = imagecolorallocatealpha($im,0,0,0,127);
  imagefill($im,0,0,$transparent);
} else {
  // Default (non-overlay) behavior: draw gradient and optional bg image as before
  imagealphablending($im, true);
  imagesavealpha($im, true);
  $transparent = imagecolorallocatealpha($im,0,0,0,127);
  imagefill($im,0,0,$transparent);

  // Background gradient (simple bands) + optional bg.jpg overlay (auto-generate if missing)
  $bg1 = [59,0,102]; $bg2 = [122,59,0];
  for ($y=0; $y<$H; $y++) {
    $r = (int)($bg1[0] + ($bg2[0]-$bg1[0])*$y/$H);
    $g = (int)($bg1[1] + ($bg2[1]-$bg1[1])*$y/$H);
    $b = (int)($bg1[2] + ($bg2[2]-$bg1[2])*$y/$H);
    $col = imagecolorallocate($im,$r,$g,$b);
    imageline($im,0,$y,$W,$y,$col);
  }
  // Background selection: default bg.jpg, but allow bg=2 (or bg=N) to load assets/bgN.jpg or bgN.png
  $bgParam = isset($_GET['bg']) ? preg_replace('/[^0-9]/','', $_GET['bg']) : '';
  // allow specifying an explicit filename inside assets/ (e.g. bgfile=bg_custom.jpg)
  $bgFileParam = isset($_GET['bgfile']) ? $_GET['bgfile'] : '';
  $bgPath = $assetsDir . '/bg.jpg';
  // If caller passed bgfile, prefer an asset with that basename, but also try common alternates
  if ($bgFileParam !== ''){
    // allow unicode names and try exact basename in assets
    $san = basename($bgFileParam);
    $tryFile = $assetsDir . '/'.$san;
    if (file_exists($tryFile)) {
      $bgPath = $tryFile;
    } else {
      // try same base name with .jpg/.png alternates
      $base = pathinfo($san, PATHINFO_FILENAME);
      $tryJpg = $assetsDir . '/'.$base.'.jpg';
      $tryPng = $assetsDir . '/'.$base.'.png';
      if (file_exists($tryJpg)) { $bgPath = $tryJpg; }
      elseif (file_exists($tryPng)) { $bgPath = $tryPng; }
      // as last resort, accept an absolute path provided by caller
      elseif (file_exists($bgFileParam)) { $bgPath = $bgFileParam; }
    }
  } elseif ($bgParam !== ''){
    $try1 = $assetsDir . '/bg'.$bgParam.'.jpg';
    $try2 = $assetsDir . '/bg'.$bgParam.'.png';
    if (file_exists($try1)) { $bgPath = $try1; }
    elseif (file_exists($try2)) { $bgPath = $try2; }
  }
  if (!file_exists($bgPath)) {
    // Generate a decorative background once, then reuse.
    generate_background($bgPath, max($W,1080), max($H,1350));
  }
  $bgImg = null;
  if (file_exists($bgPath)) {
    $bgImg = load_image_resource($bgPath);
  }
  if (!$bgImg && isset($try2) && file_exists($try2)) {
    $bgImg = load_image_resource($try2);
  }
  if ($bgImg) {
    imagealphablending($bgImg, true);
    imagesavealpha($bgImg, true);
    imagecopyresampled($im,$bgImg,0,0,0,0,$W,$H,imagesx($bgImg),imagesy($bgImg));
    imagedestroy($bgImg);
  }
}

// Extra layout flags
$circleLeft = isset($_GET['circleLeft']) && $_GET['circleLeft']=='1';
$rightColumn = isset($_GET['rightColumn']) && $_GET['rightColumn']=='1';
// strikeStyle: 'line' (default), 'marker' (filled marker block), 'sketch' (hand-sketched strokes)
$strikeStyle = isset($_GET['strikeStyle']) ? $_GET['strikeStyle'] : 'line'; // line|marker|sketch
// optional explicit right-column numbers: comma list (e.g. rightcol=86,88,72,76)
$rightColParam = isset($_GET['rightcol']) ? preg_replace('/[^0-9,]/','', $_GET['rightcol']) : '';

// Colors
$gold = imagecolorallocate($im, 255, 216, 104);
$goldDeep = imagecolorallocate($im, 199, 169, 48);
$white = imagecolorallocate($im, 255,255,255);
$shadow = imagecolorallocatealpha($im,0,0,0,90);

// If overlay mode is off, draw borders and panel; overlay mode should leave background transparent
if (!$overlayMode){
  // Outer Border frame
  imagesetthickness($im, 18);
  imagerectangle($im, 40, 40, $W-40, $H-40, $gold);
  imagesetthickness($im, 6);
  imagerectangle($im, 60, 60, $W-60, $H-60, $goldDeep);

  // Ornate inner panel (rounded rectangle with pattern)
  // Tuned to let the background show more (outline-only) to match sample poster
  $panelX = 120; $panelY = 160; $panelW = $W - 240; $panelH = $H - 520;
  $panelR = 34;
  // Outline-only panel so the paper background remains visible
  imagesetthickness($im,4);
  rounded_rect($im,$panelX,$panelY,$panelX+$panelW,$panelY+$panelH,$panelR,$gold,false);

  // Very subtle pattern lines (light, not a filled dark panel)
  $patternColA = imagecolorallocatealpha($im,255,255,255,115);
  $patternColB = imagecolorallocatealpha($im,0,0,0,115);
  for($y=$panelY+20; $y<$panelY+$panelH; $y+=36){
    imageline($im,$panelX+10,$y,$panelX+$panelW-10,$y,$patternColA);
  }
} else {
  // For overlay mode, set panel coordinates so number placement code below can still reference them (scaled)
  $panelX = 120; $panelY = 160; $panelW = $W - 240; $panelH = $H - 520;
  $panelR = 34;
}

// Fonts
$fontDir = __DIR__.'/assets';
if (!is_dir($fontDir)) { @mkdir($fontDir,0777,true); }

$fontCandidates = [
  $fontDir . '/Kanit-SemiBold.ttf',
  $fontDir . '/Kanit-Bold.ttf',
  __DIR__ . '/HuayKinMaiMod_Extracted/assets/Kanit-SemiBold.ttf',
  __DIR__ . '/HuayKinMaiMod_Extracted/assets/Kanit-Bold.ttf'
];
$winFonts = getenv('WINDIR') ? rtrim(getenv('WINDIR'), '\/') . '/Fonts/' : '';
if ($winFonts){
  $fontCandidates = array_merge($fontCandidates, [
    $winFonts . 'LeelawUI.ttf',
    $winFonts . 'LeelawUIb.ttf',
    $winFonts . 'Tahoma.ttf',
    $winFonts . 'TahomaBD.TTF',
    $winFonts . 'NotoSansThai-Regular.ttf',
    $winFonts . 'NotoSansThaiUI-Regular.ttf',
    $winFonts . 'CordiaNew.ttf',
    $winFonts . 'AngsanaUPC.ttf'
  ]);
}

if (!function_exists('first_existing_font')){
  function first_existing_font(array $candidates){
    foreach ($candidates as $fontPath){
      if (!$fontPath) continue;
      if (file_exists($fontPath)) { return $fontPath; }
    }
    return '';
  }
}

$fontBold = first_existing_font($fontCandidates);
$fontExists = $fontBold !== '';

// Layout-specific drawing
if ($layout !== 'vip') {
  // Classic title at top
  if ($fontExists){
    $size = $overlayMode ? max(8,(int)(54 * $sScale)) : 54; $text = $title; $bbox = imagettfbbox($size,0,$fontBold,$text); $tw = $bbox[2]-$bbox[0];
    $titleX = (int)(($W-$tw)/2);
    imagettftext($im,$size,0, $titleX+2, 200+2, $shadow,$fontBold,$text);
    imagettftext($im,$size,0, $titleX, 200, $gold,$fontBold,$text);
    // Strike-through option: strike=1 will draw a line across the occurrence of strikeText (default 'ลาวสตาร์VIP')
    $doStrike = isset($_GET['strike']) && $_GET['strike']=='1';
    $strikeText = isset($_GET['strikeText']) && $_GET['strikeText']!=='' ? trim($_GET['strikeText']) : 'ลาวสตาร์VIP';
    if ($doStrike && mb_stripos($text, $strikeText, 0, 'UTF-8') !== false) {
      // compute prefix width to position the strike over the substring
      $pos = mb_stripos($text, $strikeText, 0, 'UTF-8');
      $prefix = mb_substr($text, 0, $pos, 'UTF-8');
      $bboxPrefix = imagettfbbox($size, 0, $fontBold, $prefix);
      $prefixW = $bboxPrefix ? ($bboxPrefix[2] - $bboxPrefix[0]) : 0;
      $bboxSub = imagettfbbox($size, 0, $fontBold, $strikeText);
      $subW = $bboxSub ? ($bboxSub[2] - $bboxSub[0]) : 0;
      $lineY = 200 - (int)($size / 3); // approximate midline of text baseline
      imagesetthickness($im, 8);
      $lineCol = imagecolorallocate($im, 255, 0, 0);
      if ($strikeStyle === 'marker') {
        // draw a rounded filled marker-like rectangle
        $markerH = max(28, (int)($size * 0.5));
        $x1 = $titleX + $prefixW - 8; $x2 = $titleX + $prefixW + $subW + 8;
        $y1 = $lineY - (int)($markerH / 2); $y2 = $lineY + (int)($markerH / 2);
        $markerCol = imagecolorallocate($im, 236, 67, 67); // red marker color
        rounded_rect($im, $x1, $y1, $x2, $y2, (int)($markerH / 2), $markerCol, true);
      } elseif ($strikeStyle === 'sketch') {
        // sketch: draw multiple short slightly-angled strokes across the substring
        $num = 12; $segLen = max(20, (int)($subW / $num * 1.3));
        $startX = $titleX + $prefixW; $endX = $titleX + $prefixW + $subW;
        imagesetthickness($im, 10);
        $sketchCol = imagecolorallocate($im, 236, 67, 67);
        for ($s = 0; $s < $num; $s++) {
          // deterministic placement when overlay mode is used; otherwise keep slight randomness
          if ($overlayMode) {
            $px = $startX + (int)(($endX - $startX) * ($s / $num));
            $py = $lineY + (int)(($s % 3) - 1) * 3; // small fixed vertical wiggle
            $qx = $px + $segLen + 4;
            $qy = $py + 2;
          } else {
            $px = $startX + (int)(($endX - $startX) * ($s / $num)) + mt_rand(-6, 6);
            $py = $lineY + mt_rand(-6, 6);
            $qx = $px + $segLen + mt_rand(-10, 10);
            $qy = $py + mt_rand(-8, 8);
          }
          imageline($im, $px, $py, $qx, $qy, $sketchCol);
        }
        imagesetthickness($im, 1);
      } else {
        imageline($im, $titleX + $prefixW, $lineY, $titleX + $prefixW + $subW, $lineY, $lineCol);
      }
      }
  } else {
    imagestring($im,5,(int)(($W-8*strlen($title))/2),190,$title,$gold);
  }
}

if ($layout === 'vip') {
  // Title inside panel top
  if ($fontExists){
    $size = $overlayMode ? max(8,(int)(52 * $sScale)) : 52; $text = $title; $bbox = imagettfbbox($size,0,$fontBold,$text); $tw = $bbox[2]-$bbox[0];
  $titleX = (int)($W/2 - $tw/2);
  $titleY = $panelY + (int)(70 * $sScale);
    imagettftext($im,$size,0,$titleX+2, $titleY+2, $shadow,$fontBold,$text);
    imagettftext($im,$size,0,$titleX,   $titleY,   $gold,$fontBold,$text);
    // strike-through in VIP layout as well
    $doStrike = isset($_GET['strike']) && $_GET['strike']=='1';
    $strikeText = isset($_GET['strikeText']) && $_GET['strikeText']!=='' ? trim($_GET['strikeText']) : 'ลาวสตาร์VIP';
    if ($doStrike && mb_stripos($text, $strikeText, 0, 'UTF-8') !== false){
      $pos = mb_stripos($text, $strikeText, 0, 'UTF-8');
      $prefix = mb_substr($text, 0, $pos, 'UTF-8');
      $bboxPrefix = imagettfbbox($size,0,$fontBold,$prefix);
      $prefixW = $bboxPrefix ? ($bboxPrefix[2]-$bboxPrefix[0]) : 0;
      $bboxSub = imagettfbbox($size,0,$fontBold,$strikeText);
      $subW = $bboxSub ? ($bboxSub[2]-$bboxSub[0]) : 0;
      $lineY = $titleY - (int)($size/3);
      if ($strikeStyle === 'marker'){
        // draw a rounded filled marker-like rectangle
        $markerH = max(28, (int)($size * 0.5));
        $x1 = $titleX + $prefixW - 8; $x2 = $titleX + $prefixW + $subW + 8;
        $y1 = $lineY - (int)($markerH/2); $y2 = $lineY + (int)($markerH/2);
        $markerCol = imagecolorallocate($im, 236, 67, 67); // red marker color
        rounded_rect($im, $x1, $y1, $x2, $y2, (int)($markerH/2), $markerCol, true);
      } elseif ($strikeStyle === 'sketch'){
        // sketch: draw multiple short slightly-angled strokes across the substring
        $num = 12; $segLen = max(20, (int)($subW / $num * 1.3));
        $startX = $titleX + $prefixW; $endX = $titleX + $prefixW + $subW;
        imagesetthickness($im, 10);
        $sketchCol = imagecolorallocate($im, 236, 67, 67);
        for ($s=0;$s<$num;$s++){
          if ($overlayMode){
            $px = $startX + (int)(($endX-$startX) * ($s/$num));
            $py = $lineY + (int)(($s % 3) - 1) * 3;
            $qx = $px + $segLen + 4;
            $qy = $py + 2;
          } else {
            $px = $startX + (int)(($endX-$startX) * ($s/$num)) + mt_rand(-6,6);
            $py = $lineY + mt_rand(-6,6);
            $qx = $px + $segLen + mt_rand(-10,10);
            $qy = $py + mt_rand(-8,8);
          }
          imageline($im,$px,$py,$qx,$qy,$sketchCol);
        }
        imagesetthickness($im,1);
      } else {
        imagesetthickness($im, 8);
        $lineCol = imagecolorallocate($im, 255, 0, 0);
        imageline($im, $titleX + $prefixW, $lineY, $titleX + $prefixW + $subW, $lineY, $lineCol);
        imagesetthickness($im, 1);
      }
    }
    // Draw date under the title (small, stroked) to match sample layout
    if (isset($dateText) && $dateText !== ''){
      $dsize = $overlayMode ? max(8,(int)(36 * $sScale)) : 36;
      $dX = (int)($W/2);
      $dY = $titleY + (int)(62 * $sScale);
      $dtxt = $dateText;
      $bboxD = imagettfbbox($dsize,0,$fontBold,$dtxt);
      $dtw = $bboxD ? ($bboxD[2]-$bboxD[0]) : 0;
      imagettftext($im,$dsize,0,$dX-2 - (int)($dtw/2), $dY+2, $shadow, $fontBold, $dtxt);
      imagettftext($im,$dsize,0,$dX - (int)($dtw/2), $dY, $white, $fontBold, $dtxt);
    }
  }

  // If circleLeft flag is set, draw a circled lead digit on the left side (VIP layout has big leadDigit normally centered)
  if ($circleLeft && $fontExists){
    // draw a red ring and place the lead digit inside
    $leadDigit = ($lead!==''?substr($lead,0,1):substr($n,0,1));
    // place circle further left and slightly larger to match sample
  $circleCX = $panelX - (int)(80 * $sScale); // further left of panel
  $circleCY = $panelY + (int)($panelH/2) - (int)(20 * $sScale);
  $circleR = $overlayMode ? max(8,(int)(160 * $sScale)) : 160;
    // outer ring
    $ringCol = imagecolorallocate($im, 236, 67, 67);
    imagefilledellipse($im, $circleCX, $circleCY, $circleR*2, $circleR*2, $ringCol);
    // inner white
    $innerCol = imagecolorallocate($im, 255,255,255);
    imagefilledellipse($im, $circleCX, $circleCY, (int)($circleR*1.6), (int)($circleR*1.6), $innerCol);
    // draw the digit
  $sizeD = $overlayMode ? max(10,(int)(140 * $sScale)) : 140;
    $bboxD = imagettfbbox($sizeD,0,$fontBold,$leadDigit); $twD = $bboxD[2]-$bboxD[0]; $thD = $bboxD[1]-$bboxD[7];
    $dx = (int)($circleCX - $twD/2) + ($overlayMode ? $overlayOffsetX : 0);
    $dy = (int)($circleCY + $thD/2) - 20;
    // black stroke
  for ($i=4;$i>=2;$i--){ imagettftext($im,$sizeD,0,$dx+$i,$dy+$i,imagecolorallocatealpha($im,0,0,0,100),$fontBold,$leadDigit); }
    imagettftext($im,$sizeD,0,$dx,$dy,$ringCol,$fontBold,$leadDigit);
  }
  // Left-top single digit (like sample) - allow override via leftTop GET param
  $leftTopDigit = isset($_GET['leftTop']) ? preg_replace('/[^0-9]/','', $_GET['leftTop']) : '';
  if ($leftTopDigit === '') { $leftTopDigit = substr($n,0,1); }
  if ($fontExists){
  $sizeLT = $overlayMode ? max(8,(int)(110 * $sScale)) : 110;
  $bboxLT = imagettfbbox($sizeLT,0,$fontBold,$leftTopDigit); $twLT = $bboxLT[2]-$bboxLT[0]; $thLT = $bboxLT[1]-$bboxLT[7];
  $xLT = $panelX + (int)(40 * $sScale) + ($overlayMode ? $overlayOffsetX : 0);
  $yLT = $panelY + (int)(40 * $sScale) + (int)($thLT/2) + ($overlayMode ? $overlayOffsetY : 0);
    $strokeCol = imagecolorallocate($im,0,0,0);
    imagettftext_outline($im,$sizeLT,0,$xLT,$yLT,$white,$strokeCol,$fontBold,$leftTopDigit,4);
  }
  // Big lead digit in center (skip if we used circleLeft to place the lead)
  $leadDigit = ($lead!==''?substr($lead,0,1):substr($n,0,1));
  if (!$circleLeft && $fontExists){
    $size = $overlayMode ? max(10,(int)(220 * $sScale)) : 220; $bbox = imagettfbbox($size,0,$fontBold,$leadDigit); $tw=$bbox[2]-$bbox[0]; $th=$bbox[1]-$bbox[7];
    $x = (int)($W/2 - $tw/2) + ($overlayMode ? $overlayOffsetX : 0);
    $y = $panelY + (int)($panelH/2) + (int)($th/2) - (int)(80 * $sScale) + ($overlayMode ? $overlayOffsetY : 0);
    for ($i=7;$i>=2;$i-=2){ imagettftext($im,$size,0,$x+$i,$y+$i,imagecolorallocatealpha($im,0,0,0,100),$fontBold,$leadDigit); }
    imagettftext($im,$size,0,$x,$y,$gold,$fontBold,$leadDigit);
  }
  // Label "เด่น"
  if ($fontExists){
    $lbl = 'เด่น'; $size = $overlayMode ? max(8,(int)(56 * $sScale)) : 56; $bbox=imagettfbbox($size,0,$fontBold,$lbl); $tw=$bbox[2]-$bbox[0];
    imagettftext($im,$size,0,(int)($W/2 - $tw/2), $panelY + (int)($panelH/2) + (int)(40 * $sScale), $white,$fontBold,$lbl);
  }
  // Two-digit / right-column handling
  $twoArr = array_values(array_filter(explode(',', $two)));
  if (count($twoArr)===0){ $twoArr = [substr($n,0,2), substr($n,2,2), substr($n,4,2)]; }
  $twoArr = array_slice($twoArr,0,3);
  // Three-digit row (2 values centered larger)
  $threeArr = array_values(array_filter(explode(',', $three)));
  if (count($threeArr)===0){ $threeArr = [substr($n,0,3), substr($n,3,3)]; }
  $threeArr = array_slice($threeArr,0,2);
  if ($fontExists){
    if ($rightColumn && $rightColParam !== ''){
      // draw explicit right-column numbers (comma list provided)
      $rc = array_values(array_filter(explode(',', $rightColParam)));
      if (count($rc) > 0) {
  // tuned sizes/positions for right column (bigger and spaced to match sample)
  $sizeRC = $overlayMode ? max(8,(int)(96 * $sScale)) : 96;
  $xRC = $W - (int)(120 * $sScale);            // move a bit more inward from the edge
  $startY = $panelY + (int)(30 * $sScale);     // start near top of panel to match example
  $gapY = $overlayMode ? max(8,(int)(130 * $sScale)) : 130;                // vertical spacing between items

        foreach ($rc as $idx => $val) {
          // imagettftext uses baseline Y, so add half size for better centering
          $yRC = $startY + $idx * $gapY + (int)($sizeRC / 2);
          $bbox = imagettfbbox($sizeRC, 0, $fontBold, $val);
          $tw = $bbox ? ($bbox[2] - $bbox[0]) : 0;
          // draw with black stroke and white fill for poster-style numbers
          $strokeCol = imagecolorallocate($im, 0,0,0);
          imagettftext_outline($im, $sizeRC, 0, $xRC - (int)($tw / 2), $yRC, $white, $strokeCol, $fontBold, $val, 3);
        }
        // draw three-digit numbers along the bottom edge (same placement as default branch)
        $sizeThree = $overlayMode ? max(8,(int)(88 * $sScale)) : 88;
        $yThree = $panelY + $panelH - (int)(180 * $sScale);
        if (isset($threeArr[0])){
          $val = $threeArr[0];
          $bboxThree = imagettfbbox($sizeThree,0,$fontBold,$val);
          $twThree = $bboxThree ? ($bboxThree[2]-$bboxThree[0]) : 0;
          $xLeft = $panelX + (int)(60 * $sScale);
          $strokeCol = imagecolorallocate($im,0,0,0);
          imagettftext_outline($im,$sizeThree,0,$xLeft - (int)($twThree/2),$yThree,$gold,$strokeCol,$fontBold,$val,4);
        }
        if (isset($threeArr[1])){
          $val = $threeArr[1];
          $bboxThree = imagettfbbox($sizeThree,0,$fontBold,$val);
          $twThree = $bboxThree ? ($bboxThree[2]-$bboxThree[0]) : 0;
          $xRight = $panelX + $panelW - (int)(120 * $sScale);
          $strokeCol = imagecolorallocate($im,0,0,0);
          imagettftext_outline($im,$sizeThree,0,$xRight - (int)($twThree/2),$yThree,$gold,$strokeCol,$fontBold,$val,4);
        }
      }
    } else {
    // Default centered rows (existing behavior)
    $size = $overlayMode ? max(8,(int)(64 * $sScale)) : 64; $cx = $W/2; $gap = $overlayMode ? max(6,(int)(180 * $sScale)) : 180; $y = $panelY + (int)($panelH/2) + (int)(110 * $sScale); $i= -1;
  foreach($twoArr as $idx=>$val){ $i=$idx-1; $x=(int)($cx + $i*$gap); $bbox=imagettfbbox($size,0,$fontBold,$val); $tw=$bbox[2]-$bbox[0]; $strokeCol = imagecolorallocate($im,0,0,0); imagettftext_outline($im,$size,0,$x - (int)($tw/2),$y,$white,$strokeCol,$fontBold,$val, max(2, (int)(3 * $sScale))); }
  // Place three-digit numbers near lower-left and lower-right (like sample)
  $size = $overlayMode ? max(8,(int)(88 * $sScale)) : 88; $yThree = $panelY + $panelH - (int)(180 * $sScale);
  // left three
  if (isset($threeArr[0])){
    $val = $threeArr[0]; $xL = $panelX + (int)(60 * $sScale); $bbox=imagettfbbox($size,0,$fontBold,$val); $tw=$bbox[2]-$bbox[0]; $strokeCol = imagecolorallocate($im,0,0,0); imagettftext_outline($im,$size,0,$xL - (int)($tw/2),$yThree,$gold,$strokeCol,$fontBold,$val,4);
  }
  // right three
  if (isset($threeArr[1])){
    $val = $threeArr[1]; $xR = $panelX + $panelW - (int)(120 * $sScale); $bbox=imagettfbbox($size,0,$fontBold,$val); $tw=$bbox[2]-$bbox[0]; $strokeCol = imagecolorallocate($im,0,0,0); imagettftext_outline($im,$size,0,$xR - (int)($tw/2),$yThree,$gold,$strokeCol,$fontBold,$val,4);
  }
    }
  }
} else {
  // Classic layout (existing big 6-digit with sub blocks)
  if ($fontExists){
    $size = 220; $text = $n; $bbox = imagettfbbox($size,0,$fontBold,$text); $tw = $bbox[2]-$bbox[0]; $th = $bbox[1]-$bbox[7];
    $x = (int)(($W-$tw)/2); $yCenter = $panelY + (int)($panelH/2); $y = $yCenter + (int)($th/2) - 50;
    for ($i=7;$i>=2;$i-=2){ imagettftext($im,$size,0,$x+$i,$y+$i,imagecolorallocatealpha($im,0,0,0,100),$fontBold,$text); }
    imagettftext($im,$size,0,$x,$y,$gold,$fontBold,$text);
  } else {
    imagestring($im, 5, (int)($W/2)-40, $panelY + (int)($panelH/2)-10, $n, $gold);
  }
  if ($fontExists){
    $labelCol = $white; $valCol = $gold;
    $sizeLabel = 40; $sizeVal = 44;
    $drawBlock = function($centerX, $label, $values) use($im,$fontBold,$sizeLabel,$sizeVal,$labelCol,$valCol,$panelY){
      $bbox = imagettfbbox($sizeLabel,0,$fontBold,$label); $tw=$bbox[2]-$bbox[0];
      imagettftext($im,$sizeLabel,0,(int)($centerX-$tw/2), $panelY+120, $labelCol,$fontBold,$label);
      if ($values !== ''){
        $bbox2 = imagettfbbox($sizeVal,0,$fontBold,$values); $tw2=$bbox2[2]-$bbox2[0];
        imagettftext($im,$sizeVal,0,(int)($centerX-$tw2/2), $panelY+170, $valCol,$fontBold,$values);
      }
    };
    $leadTxt = $lead !== '' ? $lead : '';
    $twoTxt  = $two  !== '' ? str_replace(',', '  ', $two) : '';
    $threeTxt= $three!== '' ? str_replace(',', '  ', $three) : '';
    $cx1 = (int)($W/2) - 260; $cx2 = (int)($W/2); $cx3 = (int)($W/2) + 260;
    $drawBlock($cx2, 'เด่น', $leadTxt);
    $drawBlock($cx1, 'สองตัว', $twoTxt);
    $drawBlock($cx3, 'สามตัว', $threeTxt);
  }
}

// Date pill (decorative) - skip when overlay mode is used (transparent output)
if (!$overlayMode){
  $pillW = 300; $pillH = 68; $px = (int)(($W-$pillW)/2); $py = $H-220;
  imagefilledrectangle($im,$px,$py,$px+$pillW,$py+$pillH,$gold);
  imagerectangle($im,$px,$py,$px+$pillW,$py+$pillH,$goldDeep);
  if ($fontExists){
    $size=40; $bbox=imagettfbbox($size,0,$fontBold,$dateText); $tw=$bbox[2]-$bbox[0];
    imagettftext($im,$size,0,(int)(($W-$tw)/2),$py+48,$goldDeep,$fontBold,$dateText);
  } else {
    imagestring($im,5,$px+($pillW/2)-40,$py+($pillH/2)-8,$dateText,$goldDeep);
  }
}

// Social pills (optional, draw if text provided) - skip for overlay mode
if (!$overlayMode && $fontExists && ($fb!=='' || $line!=='')){
  $ySocial = $py + 110;
  if ($fb!==''){
    $txt = 'f : '.$fb; $w=330; $h=56; $sx = (int)($W/2 - $w - 20); $sy = $ySocial;
    $fbCol = imagecolorallocate($im, 59,89,152);
    imagefilledrectangle($im,$sx,$sy,$sx+$w,$sy+$h,$fbCol);
    imagettftext($im,28,0,$sx+20,$sy+38,$white,$fontBold,$txt);
  }
  if ($line!==''){
    $txt = 'LINE : '.$line; $w=330; $h=56; $sx = (int)($W/2 + 20); $sy = $ySocial;
    $lnCol = imagecolorallocate($im, 0,195,0);
    imagefilledrectangle($im,$sx,$sy,$sx+$w,$sy+$h,$lnCol);
    imagettftext($im,28,0,$sx+20,$sy+38,$white,$fontBold,$txt);
  }
}

// Optional small footer, logo and watermark - skip in overlay mode (transparent overlay)
if (!$overlayMode){
  // Optional small footer
  $footer = 'HuayKinMaiMod';
  if ($fontExists){ imagettftext($im,22,0,44,$H-44,$white,$fontBold,$footer); }

  // Logo overlay if exists
  $logoPath = __DIR__.'/assets/logo.png';
  if (file_exists($logoPath)){
    $logo = @imagecreatefrompng($logoPath);
    if (!$logo) { $logo = @imagecreatefromjpeg($logoPath); }
    if ($logo){
      $lw = imagesx($logo); $lh = imagesy($logo);
      $tW = 180; $tH = (int)($lh*$tW/$lw);
      imagecopyresampled($im,$logo, $W-40-$tW, 40, 0,0, $tW,$tH, $lw,$lh);
      imagedestroy($logo);
    }
  }

  // Watermark (optional)
  if (isset($_GET['wm']) && $_GET['wm'] == '1' && $fontExists){
    // Soften watermark: higher alpha, fewer tiles
    $wmText = 'HUAYKINMAIMOD';
    $tileW = 760; $tileH = 360; // larger spacing
    for($yy=-$tileH; $yy<$H+$tileH; $yy+=$tileH){
      for($xx=-$tileW; $xx<$W+$tileW; $xx+=$tileW){
        // lighten watermark more (higher alpha = more transparent)
        imagettftext($im,48, -25, $xx+80, $yy+160, imagecolorallocatealpha($im,255,216,104,115), $fontBold, $wmText);
      }
    }
  }
}

header('Content-Type: image/png');
header('Cache-Control: no-store');
imagepng($im);
imagedestroy($im);
?>
<?php
// --- helper: generate ornamental purple/gold background and save as JPEG ---
function generate_background($savePath, $W=1080, $H=1350){
  if (!extension_loaded('gd')) return false;
  $im = imagecreatetruecolor($W,$H);
  imagesavealpha($im, true);
  // base gradient
  for ($y=0; $y<$H; $y++) {
    $r = (int)(59 + (20)*$y/$H);
    $g = (int)(0 + (30)*$y/$H);
    $b = (int)(102 + (-40)*$y/$H);
    $col = imagecolorallocate($im,$r,$g,$b);
    imageline($im,0,$y,$W,$y,$col);
  }
  // subtle diagonal pattern
  $alphaLine = imagecolorallocatealpha($im, 255, 216, 104, 110);
  imagesetthickness($im, 2);
  $step = 28;
  for ($x=-$H; $x<$W+$H; $x+=$step){
    imageline($im, $x, 0, $x+$H, $H, $alphaLine);
  }
  $alphaLine2 = imagecolorallocatealpha($im, 0, 0, 0, 110);
  for ($x=-$H; $x<$W+$H; $x+=$step){
    imageline($im, $x+14, 0, $x+$H+14, $H, $alphaLine2);
  }
  // vignette
  for ($i=0; $i<30; $i++){
    $margin = (int)($i*($W/30));
    $a = 127 - (int)(($i/30)*90);
    $col = imagecolorallocatealpha($im, 0,0,0, $a);
    imagerectangle($im, 0+$i*2, 0+$i*2, $W-1-$i*2, $H-1-$i*2, $col);
  }
  // noise speckles
  $noise = imagecolorallocatealpha($im, 255,255,255,120);
  for ($i=0; $i<2000; $i++){
    imagesetpixel($im, rand(0,$W-1), rand(0,$H-1), $noise);
  }
  // save
  @imagejpeg($im, $savePath, 92);
  imagedestroy($im);
  return file_exists($savePath);
}
?>
<?php
// --- helper: rounded rectangle ---
function rounded_rect($im,$x1,$y1,$x2,$y2,$r,$color,$filled=true){
  // Edges
  if ($filled){
    imagefilledrectangle($im,$x1+$r,$y1,$x2-$r,$y2,$color);
    imagefilledrectangle($im,$x1,$y1+$r,$x2,$y2-$r,$color);
    // corners
    imagefilledellipse($im,$x1+$r,$y1+$r,$r*2,$r*2,$color);
    imagefilledellipse($im,$x2-$r,$y1+$r,$r*2,$r*2,$color);
    imagefilledellipse($im,$x1+$r,$y2-$r,$r*2,$r*2,$color);
    imagefilledellipse($im,$x2-$r,$y2-$r,$r*2,$r*2,$color);
  } else {
    // outline approximation
    for($i=0;$i<2;$i++){
      $ci = $color; // could vary thickness by loop
      // lines
      imageline($im,$x1+$r,$y1+$i,$x2-$r,$y1+$i,$ci);
      imageline($im,$x1+$r,$y2-$i,$x2-$r,$y2-$i,$ci);
      imageline($im,$x1+$i,$y1+$r,$x1+$i,$y2-$r,$ci);
      imageline($im,$x2-$i,$y1+$r,$x2-$i,$y2-$r,$ci);
      // corner arcs (simple with ellipse quadrants)
      imagearc($im,$x1+$r,$y1+$r,$r*2,$r*2,180,270,$ci);
      imagearc($im,$x2-$r,$y1+$r,$r*2,$r*2,270,360,$ci);
      imagearc($im,$x1+$r,$y2-$r,$r*2,$r*2,90,180,$ci);
      imagearc($im,$x2-$r,$y2-$r,$r*2,$r*2,0,90,$ci);
    }
    }

}

// helper: draw TTF with outline/stroke (draw stroke by drawing text multiple offsets)
function imagettftext_outline($im, $size, $angle, $x, $y, $colFill, $colStroke, $fontfile, $text, $thickness=2){
  // draw stroke by drawing text in stroke color around offsets
  for($ox = -$thickness; $ox <= $thickness; $ox++){
    for($oy = -$thickness; $oy <= $thickness; $oy++){
      if ($ox==0 && $oy==0) continue;
      imagettftext($im, $size, $angle, $x+$ox, $y+$oy, $colStroke, $fontfile, $text);
    }
  }
  // draw fill on top
  imagettftext($im, $size, $angle, $x, $y, $colFill, $fontfile, $text);
}

?>