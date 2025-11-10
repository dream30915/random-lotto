<?php
// Quick standalone overlay generator for testing canvas sizing.
$assets = __DIR__ . '/../assets';
$bg = $assets . '/bg_custom_เซียนตอง.png';
$out = __DIR__ . '/../sample_ref_final.png';
$n = isset($argv[1]) ? $argv[1] : '852369';
if (!file_exists($bg)) { echo "BG not found: $bg\n"; exit(1); }
$info = getimagesize($bg);
$W = $info[0]; $H = $info[1];
$im = imagecreatetruecolor($W,$H);
imagealphablending($im, false);
imagesavealpha($im, true);
$transparent = imagecolorallocatealpha($im, 0,0,0,127);
imagefill($im, 0,0, $transparent);
// load fonts
$font = $assets . '/Kanit-SemiBold.ttf';
if (!file_exists($font)) { $font = $assets . '/arial.ttf'; }
$gold = imagecolorallocate($im,255,216,104);
$white = imagecolorallocate($im,255,255,255);
// draw big 6-digit centered
$size = (int) (220 * ($W / 1080.0));
$bbox = imagettfbbox($size,0,$font,$n);
$tw = $bbox[2]-$bbox[0]; $th = $bbox[1]-$bbox[7];
$x = (int) (($W - $tw)/2);
$y = (int) (($H - $th)/2 + $th/2);
// stroke
$strokeCol = imagecolorallocatealpha($im,0,0,0,100);
for ($i=6;$i>=2;$i-=2) imagettftext($im,$size,0,$x+$i,$y+$i,$strokeCol,$font,$n);
imagettftext($im,$size,0,$x,$y,$gold,$font,$n);
imagepng($im,$out);
imagedestroy($im);
echo "W={$W} H={$H} -> $out\n";
?>