<?php
// Source list page (copied into HuayKinMaiMod_Extracted/index.php by batch script)
$t=time(); $y_th=(int)date('Y',$t)+543; $yy_th=substr((string)$y_th,-2); $dateThai=date('d/m/', $t).$yy_th;
$lotteries=[
  'ลาวสตาร์VIP','ลาวสตาร์','ลาวสามัคคีVIP','ลาวสามัคคี','ลาวพัฒนา','ลาวHD','ลาวทีวี','ลาวอาเซียน','ลาวอาเซียนVIP','ลาวกาชาด','ลาวกาชาดVIP',
  'ฮานอยพิเศษ','ฮานอยวีไอพี','ฮานอย','ฮานอยอาเซียน','ฮานอยกาชาด','ฮานอยกาชาดVIP',
  'หุ้นไทยเช้า','หุ้นไทยเที่ยง','หุ้นไทยบ่าย','หุ้นไทยเย็น',
  'นิเคอิรอบเช้า','นิเคอิรอบบ่าย','นิเคอิบ่ายVIP',
  'จีนเช้าVIP','จีนบ่ายVIP','หุ้นจีน',
  'ดาวโจนส์VIP','เยอรมันVIP','อังกฤษVIP','หุ้นอังกฤษ','หุ้นเยอรมัน',
  'อเมริกาVIP','เกาหลีVIP','สิงคโปร์VIP','หุ้นสิงคโปร์','ไต้หวันVIP','ญี่ปุ่นVIP','รัฐบาลไทย'
];
$flagMap=[
  'ลาว'=>'la','ฮานอย'=>'vn','นิเคอิ'=>'jp','ญี่ปุ่น'=>'jp','จีน'=>'cn','ดาวโจนส์'=>'us','อเมริกา'=>'us','เยอรมัน'=>'de','อังกฤษ'=>'gb','รัฐบาลไทย'=>'th','หุ้นไทย'=>'th','เกาหลี'=>'kr','สิงคโปร์'=>'sg','ไต้หวัน'=>'tw'
];
$flagsDirLocal=__DIR__.'/assets/flags/';
$flagsDirParent=dirname(__DIR__).'/assets/flags/'; // parent of Extracted (root) has real flags
$flagsDir=is_dir($flagsDirLocal)?$flagsDirLocal:(is_dir($flagsDirParent)?$flagsDirParent:$flagsDirLocal);
function flagPathFor($name,$map,$base){ foreach($map as $k=>$code){ if(strpos($name,$k)!==false){ return $base.$code.'.svg'; } } return $base.'th.svg'; }
?>
<!doctype html><html lang="th"><head>
<meta charset="utf-8"><title>โปรแกรมทำนายหวยอัตโนมัติ - รายการ</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  body{margin:0;font-family:"Kanit",Arial,sans-serif;background:linear-gradient(135deg,#39184a,#7b3b00);}
  .wrap{max-width:1180px;margin:0 auto;padding:24px 26px;box-sizing:border-box;background:rgba(255,255,255,0.08);backdrop-filter:blur(2px);border-radius:14px;min-height:100vh;}
  h1{margin:0 0 4px 0;text-align:center;font-size:20px;font-weight:600;color:#fff;}
  .contact{text-align:center;font-size:13px;line-height:1.3;margin:0 0 18px 0;color:#ffd868;font-weight:600;}
  .topSearch{width:100%;padding:10px 14px;border:1px solid #b9b9b9;border-radius:5px;margin:0 0 14px 0;font-family:inherit;font-size:14px;}
  .sectionTitle{background:#fff;border:1px solid #bbb;padding:6px 12px;font-size:12px;font-weight:700;display:inline-block;margin:0 0 14px 0;border-radius:4px;}
  .grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;}
  @media(max-width:1200px){ .grid{grid-template-columns:repeat(4,1fr);} }
  @media(max-width:960px){ .grid{grid-template-columns:repeat(3,1fr);} }
  @media(max-width:720px){ .grid{grid-template-columns:repeat(2,1fr);} }
  @media(max-width:480px){ .grid{grid-template-columns:1fr;} }
  a.lot{text-decoration:none;color:#111;display:flex;align-items:center;font-size:12px;border-radius:6px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .12s, box-shadow .12s;}
  a.lot:focus,a.lot:hover{transform:translateY(-2px);box-shadow:0 4px 10px rgba(0,0,0,.4);}
  .flagBox{width:64px;min-width:64px;height:44px;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid #bbb;border-right:none;border-radius:6px 0 0 6px;}
  .flagBox svg{width:64px;height:44px;display:block;}
  .info{padding:6px 10px;flex:1;display:flex;flex-direction:column;justify-content:center;background:#fff;border:1px solid #bbb;border-left:none;border-radius:0 6px 6px 0;}
  .name{font-weight:600;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
  .date{font-size:10px;color:#333;}
  .hide{display:none!important;}
</style>
</head><body>
<div class="wrap">
  <input type="text" id="search" class="topSearch" placeholder="ค้นหาชื่อหวย...">
  <h1>โปรแกรมทำนายหวยอัตโนมัติ</h1>
  <div class="contact">เซียนตอง7K</div>
  <div class="sectionTitle">หวยพิเศษเฉพาะ:</div>
  <div class="grid" id="lotGrid">
  <?php foreach($lotteries as $lot): $p=flagPathFor($lot,$flagMap,$flagsDir); $svg=file_exists($p)?file_get_contents($p):''; ?>
  <a class="lot" data-name="<?php echo htmlspecialchars($lot); ?>" href="detail.php?huayname=<?php echo urlencode($lot); ?>">
      <div class="flagBox"><?php echo $svg; ?></div>
      <div class="info">
        <div class="name"><?php echo htmlspecialchars($lot); ?></div>
        <div class="date"><?php echo $dateThai; ?></div>
      </div>
    </a>
  <?php endforeach; ?>
  </div>
</div>
<script>
const search=document.getElementById('search');
const items=[...document.querySelectorAll('#lotGrid .lot')];
search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();items.forEach(el=>{el.classList.toggle('hide',q&& !el.dataset.name.toLowerCase().includes(q));});});
</script>
</body></html>