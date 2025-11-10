<?php
// Landing page listing (รูปที่1) – full lottery list with flag codes
$lotteries = [
  // ลาว
  ['name'=>'ลาวสตาร์VIP','flag'=>'laos'],
  ['name'=>'ลาวสตาร์','flag'=>'laos'],
  ['name'=>'ลาวสามัคคีVIP','flag'=>'laos'],
  ['name'=>'ลาวสามัคคี','flag'=>'laos'],
  ['name'=>'ลาวพัฒนา','flag'=>'laos'],
  ['name'=>'ลาวHD','flag'=>'laos'],
  ['name'=>'ลาวทีวี','flag'=>'laos'],
  ['name'=>'ลาวอาเซียน','flag'=>'laos'],
  ['name'=>'ลาวอาเซียนVIP','flag'=>'laos'],
  ['name'=>'ลาวกาชาด','flag'=>'laos'],
  ['name'=>'ลาวกาชาดVIP','flag'=>'laos'],
  // ฮานอย
  ['name'=>'ฮานอยพิเศษ','flag'=>'vietnam'],
  ['name'=>'ฮานอยวีไอพี','flag'=>'vietnam'],
  ['name'=>'ฮานอย','flag'=>'vietnam'],
  ['name'=>'ฮานอยอาเซียน','flag'=>'vietnam'],
  ['name'=>'ฮานอยกาชาด','flag'=>'vietnam'],
  // หุ้นไทย/ต่างประเทศ
  ['name'=>'หุ้นไทยเช้า','flag'=>'thailand'],
  ['name'=>'หุ้นไทยเที่ยง','flag'=>'thailand'],
  ['name'=>'หุ้นไทยบ่าย','flag'=>'thailand'],
  ['name'=>'หุ้นไทยเย็น','flag'=>'thailand'],
  ['name'=>'นิเคอิรอบเช้า','flag'=>'japan'],
  ['name'=>'นิเคอิบ่ายVIP','flag'=>'japan'],
  ['name'=>'จีนเช้าVIP','flag'=>'china'],
  ['name'=>'จีนบ่ายVIP','flag'=>'china'],
  ['name'=>'ดาวโจนส์VIP','flag'=>'us'],
  ['name'=>'เยอรมันVIP','flag'=>'de'],
  ['name'=>'อังกฤษVIP','flag'=>'gb'],
  ['name'=>'อเมริกาVIP','flag'=>'us'],
  ['name'=>'เกาหลีVIP','flag'=>'kr'],
  ['name'=>'สิงคโปร์VIP','flag'=>'sg'],
  ['name'=>'ไต้หวันVIP','flag'=>'tw'],
  ['name'=>'ญี่ปุ่นVIP','flag'=>'jp'],
  // อื่น ๆ
  ['name'=>'รัฐบาลไทย','flag'=>'th'],
  ['name'=>'นิเคอิรอบบ่าย','flag'=>'japan'],
  ['name'=>'ฮานอยกาชาดVIP','flag'=>'vietnam'],
  ['name'=>'หุ้นสิงคโปร์','flag'=>'singapore'],
  ['name'=>'หุ้นจีน','flag'=>'china'],
  ['name'=>'หุ้นอังกฤษ','flag'=>'uk'],
  ['name'=>'หุ้นเยอรมัน','flag'=>'germany'],
];
// Date (dd/mm/yy BE)
$t = time(); $y_th = (int)date('Y',$t)+543; $yy_th = substr((string)$y_th,-2); $dateThai = date('d/m/', $t).$yy_th;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>โปรแกรมทำนายหวย - รายการ</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page">
  <header class="topBar">
    <img src="assets/logo.png" alt="logo" class="logoSmall" onerror="this.style.display='none'">
    <div class="socialPill">โปรแกรมทำนายหวยอัตโนมัติ</div>
  </header>
  <main class="panel">
    <div class="listGrid" id="listGrid">
      <?php
      // map keyword -> flag file code
      $flagMap = [
        'ลาว'=>'la','ฮานอย'=>'vn','นิเคอิ'=>'jp','จีน'=>'cn','ดาวโจนส์'=>'us','เยอรมัน'=>'de','อังกฤษ'=>'gb','อเมริกา'=>'us','เกาหลี'=>'kr','สิงคโปร์'=>'sg','ไต้หวัน'=>'tw','ญี่ปุ่น'=>'jp','รัฐบาลไทย'=>'th','หุ้นไทย'=>'th'
      ];
      foreach($lotteries as $lot):
        $code='generic';
        foreach($flagMap as $k=>$c){ if(strpos($lot['name'],$k)!==false){ $code=$c; break; } }
        $flagPath='assets/flags/'.$code.'.svg';
      ?>
        <a class="lotItem" href="predict.php?huayname=<?php echo urlencode($lot['name']); ?>">
          <div class="flagBox"><?php if(file_exists($flagPath)){ echo file_get_contents($flagPath); } else { echo '🏳️'; } ?></div>
          <div class="lotName"><?php echo htmlspecialchars($lot['name']); ?></div>
          <div class="lotDate"><?php echo $dateThai; ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </main>
  <footer class="foot">HuayKinMaiMod Portable • รายการหวย</footer>
</div>
</body>
</html>
