<?php
// Generate overlay PNG on server-side (uses server PHP with GD) and save to project root.
if (!extension_loaded('gd')) { header('Content-Type: text/plain'); echo 'GD missing'; exit; }
$assets = __DIR__ . '/../assets';
$bgName = isset($_GET['bgfile']) ? $_GET['bgfile'] : 'bg_custom_เซียนตอง.png';
$san = basename($bgName);
$candidates = [$assets . '/' . $san, $assets . '/' . pathinfo($san, PATHINFO_FILENAME) . '.png', $assets . '/' . pathinfo($san, PATHINFO_FILENAME) . '.jpg'];
$found = '';
foreach ($candidates as $c) { if (file_exists($c)) { $found = $c; break; } }
if ($found === '') {
  foreach (scandir($assets) as $candidate) {
    if ($candidate === '.' || $candidate === '..') continue;
    if (stripos($candidate, 'bg_custom') !== false) { $found = $assets . '/' . $candidate; break; }
  }
}
if ($found === '') { header('Content-Type: text/plain'); echo "bg not found"; exit; }
$info = @getimagesize($found);
$W = $info[0]; $H = $info[1];
$im = imagecreatetruecolor($W,$H);
imagealphablending($im, false);
imagesavealpha($im, true);
$transparent = imagecolorallocatealpha($im,0,0,0,127);
imagefill($im,0,0,$transparent);
$font = $assets . '/Kanit-SemiBold.ttf'; if (!file_exists($font)) $font = $assets . '/arial.ttf';
$gold = imagecolorallocate($im,255,216,104);
// draw number
$n = isset($_GET['n']) ? preg_replace('/[^0-9]/','', $_GET['n']) : '852369';
$size = (int)(220 * ($W / 1080.0));
$bbox = imagettfbbox($size,0,$font,$n); $tw = $bbox[2]-$bbox[0]; $th = $bbox[1]-$bbox[7];
$x = (int)(($W - $tw)/2); $y = (int)(($H - $th)/2 + $th/2);
$stroke = imagecolorallocatealpha($im,0,0,0,100);
for ($i=6;$i>=2;$i-=2) imagettftext($im,$size,0,$x+$i,$y+$i,$stroke,$font,$n);
imagettftext($im,$size,0,$x,$y,$gold,$font,$n);
$out = __DIR__ . '/../sample_ref_final_server.png';
imagepng($im, $out);
imagedestroy($im);
header('Content-Type: text/plain'); echo "saved:$out W=$W H=$H";
?>