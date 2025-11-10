<?php
// Interactive examples page to try poster generator live
$t = time(); $y_th = (int)date('Y',$t)+543; $yy_th = substr((string)$y_th,-2); $dateThai = date('d/m/', $t).$yy_th;
$seed = str_pad((string)rand(0,999999),6,'0',STR_PAD_LEFT);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ตัวอย่างใช้งาน - HuayKinMaiMod</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
  .grid { display:grid; grid-template-columns: 1fr; gap:14px; }
  @media (min-width: 960px){ .grid { grid-template-columns: 1.2fr 1fr; } }
  .card { background:#121212aa; border:1px solid #2b2b2b; border-radius:14px; padding:14px; }
  .row { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
  .label { width:110px; color:#ddd; }
  .numInput { width:80px; text-align:center; }
  .twoInput { width:90px; }
  .threeInput { width:110px; }
  .select, .textInput { padding:10px 12px; border-radius:10px; border:1px solid #333; background:#1a1a1a; color:#eee; }
  .textInput { width:100%; max-width:400px; }
  .pill { padding:6px 10px; border-radius:999px; background:#212121; color:#ddd; border:1px solid #333; }
  .previewBox { min-height:300px; display:flex; align-items:center; justify-content:center; background:#0d0d0f; border:1px dashed #333; border-radius:12px; overflow:auto; }
  .previewBox img { max-width:100%; height:auto; display:block; }
  .help { color:#bbb; font-size:0.95rem; line-height:1.6; }
  .kbd { padding:2px 6px; background:#222; border:1px solid #444; border-radius:6px; }
</style>
</head>
<body>
<div class="page">
  <header class="topBar">
    <img src="assets/logo.png" alt="logo" class="logoSmall" onerror="this.style.display='none'">
    <div class="socialPill">ตัวอย่างหน้าเว็บทดลองกดได้</div>
  </header>
  <main class="panel">
    <div class="navRow" style="justify-content:space-between; gap:10px;">
      <div class="row">
        <a class="btn backBtn" href="index.php">กลับหน้ารายการ</a>
        <button type="button" class="btn ghost" id="btnFillPreset1">ตัวอย่าง VIP</button>
        <button type="button" class="btn ghost" id="btnFillPreset2">ตัวอย่าง Classic</button>
      </div>
      <div class="row">
        <a class="btn" href="#" id="btnOpenImage" target="_blank">เปิดลิงก์รูป</a>
        <button class="btn" id="btnCopyUrl">คัดลอก URL</button>
      </div>
    </div>

    <div class="grid">
      <section class="card">
        <div class="row"><span class="label">ชื่อหัว</span><input id="title" class="textInput" value="ลาวสตาร์VIP"></div>
        <div class="row"><span class="label">รูปแบบ</span>
          <select id="layout" class="select"><option value="vip" selected>VIP</option><option value="classic">Classic</option></select>
        </div>
        <div class="row"><span class="label">วันที่</span>
          <span class="pill" id="dateText"><?php echo htmlspecialchars($dateThai); ?></span>
          <button class="btn small" id="minusDay">ลบวัน</button>
          <button class="btn small" id="plusDay">บวกวัน</button>
        </div>
        <div class="row"><span class="label">Seed (n)</span><input id="seed" class="numInput" maxlength="6" value="<?php echo $seed; ?>"></div>
        <hr class="divider">
        <div class="row"><span class="label">เด่น</span><input id="lead" class="numInput" maxlength="1" placeholder="0"></div>
        <div class="row"><span class="label">สองตัว</span>
          <input class="numInput twoInput" maxlength="2" placeholder="00">
          <input class="numInput twoInput" maxlength="2" placeholder="00">
          <input class="numInput twoInput" maxlength="2" placeholder="00">
        </div>
        <div class="row"><span class="label">สามตัว</span>
          <input class="numInput threeInput" maxlength="3" placeholder="000">
          <input class="numInput threeInput" maxlength="3" placeholder="000">
        </div>
        <div class="row"><span class="label">โซเชียล</span>
          <input id="fb" class="textInput" placeholder="ชื่อเฟส (ถ้าไม่กรอกจะไม่แสดง)" style="max-width:260px;">
          <input id="line" class="textInput" placeholder="ไอดีไลน์ (ถ้าไม่กรอกจะไม่แสดง)" style="max-width:260px;">
        </div>
        <div class="row"><span class="label">ตัวเลือก</span>
          <label><input type="checkbox" id="wm" checked> ใส่วอเตอร์มาร์ค</label>
        </div>
        <div class="row" style="margin-top:8px; gap:8px;">
          <button class="btn primary" id="btnRandomAll">สุ่มเลข</button>
          <button class="btn" id="btnPreview">ดูตัวอย่าง</button>
          <button class="btn" id="btnDownload">ดาวน์โหลด</button>
          <button class="btn" id="btnShare">แชร์รูปนี้</button>
        </div>
      </section>

      <section class="card">
        <div class="previewBox" id="previewBox">กด "ดูตัวอย่าง" เพื่อแสดงรูป</div>
      </section>
    </div>

    <section class="card" style="margin-top:14px;">
      <div class="help">
        <strong>อธิบายสั้น ๆ</strong>
        <ul>
          <li>สคริปต์สร้างรูปคือ <code>download.php</code> ซึ่งรับค่า <code>n</code>, <code>layout</code>, <code>title</code>, <code>date</code>, <code>lead</code>, <code>two</code>, <code>three</code>, <code>fb</code>, <code>line</code>, <code>wm</code></li>
          <li>ถ้าไม่กรอกเลข สคริปต์จะสุ่มให้อัตโนมัติ และจัดรูปแบบวันที่ไทยให้เอง</li>
          <li>สองตัว/สามตัว ให้กรอกทีละช่อง ระบบจะรวมเป็นค่าคั่นด้วยจุลภาคแล้วส่งไปยัง <code>download.php</code></li>
        </ul>
      </div>
    </section>
  </main>
  <footer class="foot">HuayKinMaiMod Portable • Examples</footer>
</div>
<script>
const dateTextEl = document.getElementById('dateText');
const seedEl = document.getElementById('seed');
const titleEl = document.getElementById('title');
const layoutEl = document.getElementById('layout');
const leadEl = document.getElementById('lead');
const twoEls = Array.from(document.querySelectorAll('.twoInput'));
const threeEls = Array.from(document.querySelectorAll('.threeInput'));
const fbEl = document.getElementById('fb');
const lineEl = document.getElementById('line');
const wmEl = document.getElementById('wm');
const previewBox = document.getElementById('previewBox');
const btnOpenImage = document.getElementById('btnOpenImage');

// Date utils (Thai dd/mm/yy BE)
let curDate = (function(){
  const [dd,mm,yy] = dateTextEl.textContent.split('/');
  // Convert BE short year to CE full year
  const ceYear = 2000 + parseInt(yy,10) - 543; // roughly fine for 20xx
  const d = new Date(ceYear, parseInt(mm,10)-1, parseInt(dd,10));
  return isNaN(d.getTime()) ? new Date() : d;
})();
const toThaiShort = (d)=>{
  const dd = String(d.getDate()).padStart(2,'0');
  const mm = String(d.getMonth()+1).padStart(2,'0');
  const beYY = String(d.getFullYear()+543).slice(-2);
  return `${dd}/${mm}/${beYY}`;
};
function renderDate(){ dateTextEl.textContent = toThaiShort(curDate); }
minusDay.onclick = ()=>{ curDate.setDate(curDate.getDate()-1); renderDate(); };
plusDay.onclick = ()=>{ curDate.setDate(curDate.getDate()+1); renderDate(); };

function randInt(max){ return Math.floor(Math.random()*max); }
function randLead(){ return String(randInt(10)); }
function randTwo(){ return String(randInt(100)).padStart(2,'0'); }
function randThree(){ return String(randInt(1000)).padStart(3,'0'); }
function randSeed(){ return String(randInt(1000000)).padStart(6,'0'); }

function randomAll(){
  leadEl.value = randLead();
  twoEls.forEach((el)=> el.value = randTwo());
  threeEls.forEach((el)=> el.value = randThree());
  seedEl.value = randSeed();
}
btnRandomAll.onclick = randomAll;

function sanitizeDigits(str){ return (str||'').replace(/[^0-9]/g,''); }
function joinList(els){
  const arr = els.map(el=>sanitizeDigits(el.value).trim()).filter(Boolean);
  return arr.join(',');
}

function buildUrl(extra={}){
  const params = new URLSearchParams();
  const n = sanitizeDigits(seedEl.value).padStart(6,'0').slice(0,6);
  const lead = sanitizeDigits(leadEl.value).slice(0,1);
  const two = joinList(twoEls);
  const three = joinList(threeEls);
  params.set('layout', layoutEl.value);
  params.set('n', n);
  if (titleEl.value.trim()) params.set('title', titleEl.value.trim());
  if (dateTextEl.textContent.trim()) params.set('date', dateTextEl.textContent.trim());
  if (lead) params.set('lead', lead);
  if (two) params.set('two', two);
  if (three) params.set('three', three);
  if (fbEl.value.trim()) params.set('fb', fbEl.value.trim());
  if (lineEl.value.trim()) params.set('line', lineEl.value.trim());
  if (wmEl.checked) params.set('wm','1');
  for (const [k,v] of Object.entries(extra)) params.set(k,v);
  return 'download.php?'+params.toString();
}

function openPreview(){
  previewBox.textContent = 'กำลังโหลด...';
  const url = buildUrl({ _ : Date.now() });
  btnOpenImage.href = url;
  const img = new Image();
  img.onload = ()=>{ previewBox.innerHTML = ''; previewBox.appendChild(img); };
  img.onerror = ()=>{ previewBox.textContent = 'โหลดไม่สำเร็จ'; };
  img.src = url;
}
btnPreview.onclick = openPreview;

btnDownload.onclick = ()=>{
  const a = document.createElement('a');
  a.href = buildUrl();
  a.download = 'huay_poster.png';
  document.body.appendChild(a); a.click(); a.remove();
};

btnShare.onclick = async()=>{
  const url = buildUrl({ _ : Date.now() });
  try{
    const resp = await fetch(url);
    const blob = await resp.blob();
    const file = new File([blob], 'huay_poster.png', { type:'image/png' });
    if (navigator.canShare && navigator.canShare({ files:[file] })){
      await navigator.share({ files:[file], title:'HuayKinMaiMod', text:'โปสเตอร์เลข' });
    } else {
      window.open(url, '_blank');
    }
  } catch(e){ window.open(url, '_blank'); }
};

btnCopyUrl.onclick = async()=>{
  const url = location.origin + location.pathname.replace(/examples\.php.*/, '') + buildUrl();
  try{ await navigator.clipboard.writeText(url); btnCopyUrl.textContent='คัดลอกแล้ว'; setTimeout(()=>btnCopyUrl.textContent='คัดลอก URL',1200);}catch(e){ alert(url); }
};

// Presets
btnFillPreset1.onclick = ()=>{
  layoutEl.value = 'vip';
  titleEl.value = 'ลาวสตาร์VIP';
  leadEl.value = '7';
  [ '12','45','88' ].forEach((v,i)=> twoEls[i].value = v);
  [ '123','456' ].forEach((v,i)=> threeEls[i].value = v);
  wmEl.checked = true; fbEl.value = 'ชื่อเฟส'; lineEl.value = 'ไอดีไลน์';
  openPreview();
};
btnFillPreset2.onclick = ()=>{
  layoutEl.value = 'classic';
  titleEl.value = 'ฮานอยพิเศษ';
  leadEl.value = '5';
  [ '22','33','44' ].forEach((v,i)=> twoEls[i].value = v);
  [ '555','666' ].forEach((v,i)=> threeEls[i].value = v);
  wmEl.checked = false; fbEl.value = ''; lineEl.value = '';
  openPreview();
};

// Initial preview once for convenience
openPreview();
</script>
</body>
</html>
