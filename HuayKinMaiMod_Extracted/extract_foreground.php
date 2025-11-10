<?php
// Extract foreground (numbers/text) from a noisy background using simple color-difference mask.
// Usage: extract_foreground.php?bgfile=bg_custom_เซียนตอง.png&threshold=60

if (!extension_loaded('gd')) { header('Content-Type:text/plain'); echo "GD required"; exit; }
$assets = __DIR__ . '/../assets';
$bgName = isset($_GET['bgfile']) ? $_GET['bgfile'] : 'bg_custom_เซียนตอง.png';
$san = basename($bgName);
$candidates = [$assets . '/' . $san, $assets . '/' . pathinfo($san, PATHINFO_FILENAME) . '.png', $assets . '/' . pathinfo($san, PATHINFO_FILENAME) . '.jpg'];
$found = '';
foreach ($candidates as $c) { if (file_exists($c)) { $found = $c; break; } }
if ($found === '') {
  foreach (scandir($assets) as $candidate) {
    if ($candidate === '.' || $candidate === '..') continue;
    if (stripos($candidate, pathinfo($san, PATHINFO_FILENAME)) !== false) { $found = $assets . '/' . $candidate; break; }
  }
}
if ($found === '') { header('Content-Type:text/plain'); echo "bg not found"; exit; }

$threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 60; // color distance threshold

$info = @getimagesize($found);
$W = $info[0]; $H = $info[1];
$src = null;
$ext = strtolower(pathinfo($found, PATHINFO_EXTENSION));
if ($ext === 'png') $src = @imagecreatefrompng($found);
elseif ($ext === 'jpg' || $ext === 'jpeg') $src = @imagecreatefromjpeg($found);
else { $src = @imagecreatefromstring(file_get_contents($found)); }
if (!$src) { header('Content-Type:text/plain'); echo "failed to load bg"; exit; }

$out = imagecreatetruecolor($W, $H);
imagealphablending($out, false);
imagesavealpha($out, true);
$transparent = imagecolorallocatealpha($out,0,0,0,127);
imagefill($out,0,0,$transparent);

// Estimate background color by averaging small patches at four corners
function sample_patch_avg($img, $x, $y, $w, $h){
  $sum = [0,0,0]; $cnt=0;
  for ($yy=$y; $yy<$y+$h; $yy++){
    for ($xx=$x; $xx<$x+$w; $xx++){
      $rgb = imagecolorat($img, max(0,min(imagesx($img)-1,$xx)), max(0,min(imagesy($img)-1,$yy)));
      $r = ($rgb>>16)&0xFF; $g = ($rgb>>8)&0xFF; $b = $rgb&0xFF;
      $sum[0]+=$r; $sum[1]+=$g; $sum[2]+=$b; $cnt++;
    }
  }
  return [$sum[0]/$cnt, $sum[1]/$cnt, $sum[2]/$cnt];
}

$patchW = max(10, (int)($W*0.05));
$patchH = max(10, (int)($H*0.05));
$corners = [];
$corners[] = sample_patch_avg($src, 0, 0, $patchW, $patchH);
$corners[] = sample_patch_avg($src, $W-$patchW, 0, $patchW, $patchH);
$corners[] = sample_patch_avg($src, 0, $H-$patchH, $patchW, $patchH);
$corners[] = sample_patch_avg($src, $W-$patchW, $H-$patchH, $patchW, $patchH);
$bg = [0,0,0];
foreach ($corners as $c){ $bg[0]+=$c[0]; $bg[1]+=$c[1]; $bg[2]+=$c[2]; }
$bg[0]/=count($corners); $bg[1]/=count($corners); $bg[2]/=count($corners);

$th2 = $threshold * $threshold;

// First pass: copy pixels whose color distance from bg > threshold
for ($y=0;$y<$H;$y++){
  for ($x=0;$x<$W;$x++){
    $rgb = imagecolorat($src,$x,$y);
    $r = ($rgb>>16)&0xFF; $g = ($rgb>>8)&0xFF; $b = $rgb&0xFF;
    $dr = $r - $bg[0]; $dg = $g - $bg[1]; $db = $b - $bg[2];
    $d2 = $dr*$dr + $dg*$dg + $db*$db;
    if ($d2 > $th2) {
      // copy pixel to out (preserve original color)
      $col = imagecolorallocatealpha($out, $r, $g, $b, 0);
      imagesetpixel($out, $x, $y, $col);
    }
  }
}

// Simple dilation to fill small gaps (do 2 passes)
for ($pass=0;$pass<2;$pass++){
  $tmp = imagecreatetruecolor($W,$H);
  imagealphablending($tmp,false); imagesavealpha($tmp,true);
  $tr = imagecolorallocatealpha($tmp,0,0,0,127); imagefill($tmp,0,0,$tr);
  for ($y=0;$y<$H;$y++){
    for ($x=0;$x<$W;$x++){
      $c = imagecolorat($out,$x,$y);
      if ($c !== 0) { imagesetpixel($tmp,$x,$y,$c); continue; }
      // check 8-neighbors in out for any non-transparent; if found, copy pixel from src (soft)
      $foundN = false;
      for ($yy=max(0,$y-1);$yy<=min($H-1,$y+1);$yy++){
        for ($xx=max(0,$x-1);$xx<=min($W-1,$x+1);$xx++){
          if ($xx==$x && $yy==$y) continue;
          $cc = imagecolorat($out,$xx,$yy); if ($cc !== 0) { $foundN = true; break 2; }
        }
      }
      if ($foundN){ $rgb = imagecolorat($src,$x,$y); $r=($rgb>>16)&0xFF; $g=($rgb>>8)&0xFF; $b=$rgb&0xFF; $col = imagecolorallocatealpha($tmp,$r,$g,$b,0); imagesetpixel($tmp,$x,$y,$col); }
    }
  }
  imagedestroy($out); $out = $tmp;
}

$outPath = __DIR__ . '/../assets/ref_foreground.png';
imagepng($out, $outPath);
imagedestroy($out); imagedestroy($src);

header('Content-Type: text/plain');
echo "saved={$outPath}\nW={$W}\nH={$H}\nthreshold={$threshold}\n";
?>
