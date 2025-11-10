<?php
// Detail prediction page (รูปที่3 + รูปที่4 modal preview)
session_start();
function rand_number(){ return rand(0,999999); }
$seed = isset($_GET['n']) ? preg_replace('/[^0-9]/','',$_GET['n']) : rand_number();
$seed = str_pad($seed,6,'0',STR_PAD_LEFT);
$huay = isset($_GET['huayname']) ? $_GET['huayname'] : 'ลาวสตาร์VIP';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ทำนายหวย - <?php echo htmlspecialchars($huay); ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page">
  <header class="topBar">
     <img src="assets/logo.png" alt="LOGO" class="logoSmall" onerror="this.style.display='none'">
     <div class="socialPill">f : ชื่อเฟส</div>
     <div class="socialPill">LINE : ไอดีไลน์</div>
  </header>
  <main class="panel">
    <div class="navRow">
      <button type="button" class="btn backBtn" id="btnBack">กลับหน้ารายการ</button>
      <button type="button" class="btn primary" id="btnRandomAll">สุ่มใหม่</button>
    </div>
    <div class="formRow">
      <select id="lottery" class="select" aria-label="เลือกหวย">
        <option value="<?php echo htmlspecialchars($huay); ?>" selected><?php echo htmlspecialchars($huay); ?></option>
      </select>
    </div>
    <div class="dateRow">
      <div class="dateShow">วันที่ <span id="dateText"></span></div>
      <div class="dateBtns">
        <button class="btn small" id="minusDay">ลบวัน</button>
        <button class="btn small" id="plusDay">บวกวัน</button>
      </div>
    </div>
    <div class="section">
      <div class="sectionTitle">เด่น</div>
      <div class="inputRow leadRow">
        <input id="leadInput" class="numInput lead" maxlength="1" placeholder="0" autocomplete="off">
      </div>
    </div>
    <div class="section">
      <div class="sectionTitle">สองตัว</div>
      <div class="inputRow twoRow">
        <input class="numInput twoInput" maxlength="2" placeholder="00" autocomplete="off">
        <input class="numInput twoInput" maxlength="2" placeholder="00" autocomplete="off">
        <input class="numInput twoInput" maxlength="2" placeholder="00" autocomplete="off">
      </div>
    </div>
    <div class="section">
      <div class="sectionTitle">สามตัว</div>
      <div class="inputRow threeRow">
        <input class="numInput threeInput" maxlength="3" placeholder="000" autocomplete="off">
        <input class="numInput threeInput" maxlength="3" placeholder="000" autocomplete="off">
      </div>
    </div>
    <div class="actions">
      <button class="btn ghost" id="btnPreview">ดูโปสเตอร์</button>
      <button class="btn" id="btnDownload">ดาวน์โหลดโปสเตอร์</button>
      <button class="btn" id="btnShare">แชร์รูปนี้</button>
      <button class="btn" id="btnCopy">คัดลอกเลขทั้งหมด</button>
    </div>
  </main>
  <footer class="foot">HuayKinMaiMod Portable • Detail</footer>
</div>
<div class="modal" id="posterModal" hidden>
  <div class="modalInner">
    <button class="close" id="closeModal" aria-label="ปิด">×</button>
    <div id="posterHolder" class="posterHolder">กำลังโหลด...</div>
  </div>
</div>
<script>
const lotterySel = document.getElementById('lottery');
const dateTextEl = document.getElementById('dateText');
const leadInput = document.getElementById('leadInput');
const twoInputs = Array.from(document.querySelectorAll('.twoInput'));
const threeInputs = Array.from(document.querySelectorAll('.threeInput'));
const modal = document.getElementById('posterModal');
const posterHolder = document.getElementById('posterHolder');

// Date handling (Thai short dd/mm/yy BE)
const toThaiShort = (d)=>{ const dd=String(d.getDate()).padStart(2,'0'); const mm=String(d.getMonth()+1).padStart(2,'0'); const be=(d.getFullYear()+543).toString().slice(-2); return `${dd}/${mm}/${be}`; };
let curDate = new Date();
function renderDate(){ dateTextEl.textContent = toThaiShort(curDate); }
renderDate();
minusDay.onclick=()=>{curDate.setDate(curDate.getDate()-1);renderDate();};
plusDay.onclick=()=>{curDate.setDate(curDate.getDate()+1);renderDate();};

function randLead(){ return Math.floor(Math.random()*10); }
function randTwo(){ return String(Math.floor(Math.random()*100)).padStart(2,'0'); }
function randThree(){ return String(Math.floor(Math.random()*1000)).padStart(3,'0'); }
function randomAll(){ leadInput.value = randLead(); twoInputs.forEach(i=>i.value=randTwo()); threeInputs.forEach(i=>i.value=randThree()); }
btnRandomAll.onclick=()=>randomAll();
randomAll();

function posterUrl(extra={}){
  const lead = leadInput.value.trim();
  const two = twoInputs.map(i=>i.value.trim()).filter(Boolean).join(',');
  const three = threeInputs.map(i=>i.value.trim()).filter(Boolean).join(',');
  const params = new URLSearchParams({layout:'vip', n:'<?php echo $seed; ?>', title: lotterySel.value, date: dateTextEl.textContent, lead, two, three, fb:'ชื่อเฟส', line:'ไอดีไลน์', ...extra});
  return 'download.php?'+params.toString();
}
function openPreview(){ posterHolder.innerHTML='กำลังโหลด...'; const url = posterUrl({wm:'1', _ : Date.now()}); const img = new Image(); img.onload=()=>{posterHolder.innerHTML=''; posterHolder.appendChild(img);}; img.onerror=()=>{posterHolder.textContent='โหลดไม่สำเร็จ';}; img.src=url; modal.hidden=false; document.body.classList.add('noScroll'); }
btnPreview.onclick=openPreview;
closeModal.onclick=()=>{ modal.hidden=true; document.body.classList.remove('noScroll'); };
modal.addEventListener('click',e=>{ if(e.target===modal){ modal.hidden=true; document.body.classList.remove('noScroll'); }});
btnDownload.onclick=()=>{ const a=document.createElement('a'); a.href=posterUrl(); a.download='huay_poster.png'; document.body.appendChild(a); a.click(); a.remove(); };
btnShare.onclick=async()=>{ const url=posterUrl({wm:'1'}); try{ const resp=await fetch(url+'&_='+Date.now()); const blob=await resp.blob(); const file=new File([blob],'huay_poster.png',{type:'image/png'}); if(navigator.canShare && navigator.canShare({files:[file]})){ await navigator.share({files:[file],title:'HuayKinMaiMod',text:'เลขเด่น '+leadInput.value}); } else { window.open(url,'_blank'); } }catch(e){ window.open(url,'_blank'); }};
btnCopy.onclick=()=>{ const txt=[leadInput.value,...twoInputs.map(i=>i.value),...threeInputs.map(i=>i.value)].filter(Boolean).join(' '); navigator.clipboard?.writeText(txt); };
btnBack.onclick=()=>{ window.location.href='index.php'; };
</script>
</body>
</html>