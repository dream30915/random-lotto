<?php
// Detail page with inputs and gold-bordered slip template
$huay = isset($_GET['huayname']) ? trim($_GET['huayname']) : 'หวยพิเศษ';
$t=time(); $y_th=(int)date('Y',$t)+543; $yy_th=substr((string)$y_th,-2); $dateThai=date('d/m/', $t).$yy_th;
?>
<!doctype html><html lang="th"><head>
<meta charset="utf-8"><title><?php echo htmlspecialchars($huay); ?> - ทำนายเลข</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --gold:#ffd868; --gold-deep:#c7a930; --bg1:#39184a; --bg2:#7b3b00; }
  *{box-sizing:border-box}
  body{margin:0;font-family:"Kanit",Arial,sans-serif;background:linear-gradient(135deg,var(--bg1),var(--bg2));color:#111;}
  .wrap{max-width:1200px;margin:0 auto;padding:22px;}
  .topbar{display:flex;align-items:center;gap:12px;color:#fff;margin-bottom:16px}
  .brand{font-weight:700}
  .brand small{display:block;font-weight:600;color:var(--gold)}
  .card{background:#fff;border:1px solid #bbb;border-radius:10px;box-shadow:0 2px 12px rgba(0,0,0,.24)}
  .grid{display:grid;grid-template-columns: 1.1fr 1fr; gap:18px;}
  @media(max-width:900px){ .grid{grid-template-columns:1fr;}}
  .panel{padding:18px}
  h2{margin:0 0 10px 0;font-size:18px}
  .row{display:flex;gap:8px;align-items:center;margin-bottom:10px;flex-wrap:wrap}
  .row label{min-width:70px}
  input[type=text]{padding:10px 12px;border:1px solid #bdbdbd;border-radius:6px;width:86px;font-family:inherit;font-size:16px;text-align:center}
  input.lead{width:64px}
  .actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px}
  button,.btn{background:#222;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;font-family:inherit;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
  .btn.gold{background:var(--gold-deep); color:#fff;}
  .btn.alt{background:#5a2a80}
  .note{font-size:12px;color:#444}
  /* Slip template */
  .slipWrap{padding:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02));}
  .slip{position:relative;background:rgba(0,0,0,.55);color:#fff;border-radius:14px;padding:18px 18px 22px;border:3px solid var(--gold);
        box-shadow:0 6px 22px rgba(0,0,0,.42), inset 0 0 0 2px rgba(199,169,48,.45)}
  .slip:before,.slip:after{content:"";position:absolute;inset:6px;border-radius:10px;border:1px solid rgba(255,216,104,.5);pointer-events:none}
  .slipHeader{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
  .slipTitle{font-weight:700;font-size:18px;color:var(--gold)}
  .slipBrand{font-weight:700;color:var(--gold)}
  .pill{display:inline-block;background:var(--gold);color:#3a2a00;border:1px solid var(--gold-deep);border-radius:999px;padding:4px 10px;font-weight:700;font-size:13px}
  .list{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px}
  .box{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.35);border-radius:10px;padding:10px;text-align:center}
  .box h4{margin:0 0 6px 0;font-size:14px;color:#ffd}
  .nums{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;font-weight:700;font-size:18px}
  .footer{margin-top:12px;display:flex;justify-content:center}
  .logo{height:28px}
  .toplinks{margin-bottom:8px;}
  .back{color:#fff}
  .k2{color:var(--gold)}
  .hdr{display:flex;align-items:center;gap:12px;margin-bottom:10px}
  .hdr h2{color:#3b1258}
  .titlePill{display:inline-block;background:#fff;border:1px solid #bbb;color:#333;border-radius:6px;padding:4px 10px;margin-left:6px;font-size:12px}
  .lbl{font-size:12px;color:#333}
</style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div class="brand">โปรแกรมทำนายหวยอัตโนมัติ <small>เซียนตอง7K</small></div>
      <div style="margin-left:auto" class="toplinks"><a class="back" href="index.php">← กลับหน้าแรก</a></div>
    </div>

    <div class="grid">
      <!-- Left: form -->
      <div class="card panel">
        <div class="hdr"><h2>ตั้งค่าเลข</h2><span class="titlePill"><?php echo htmlspecialchars($huay); ?></span></div>
        <div class="row"><span class="lbl">งวด</span><div class="pill" id="datePill"><?php echo $dateThai; ?></div></div>
        <div class="row"><label>เด่น</label><input class="lead" id="lead" type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" placeholder="0"></div>
        <div class="row"><label>สองตัว</label>
          <input class="two" id="two1" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
          <input class="two" id="two2" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
          <input class="two" id="two3" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
        </div>
        <div class="row"><label>สามตัว</label>
          <input class="three" id="three1" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
          <input class="three" id="three2" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
          <input class="three" id="three3" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
        </div>
        <div class="actions">
          <button id="randBtn">สุ่มเลข</button>
          <a id="posterBtn" class="btn gold" href="#" target="_blank">ดาวน์โหลดโปสเตอร์</a>
          <button id="copyBtn" class="btn alt">คัดลอกข้อความ</button>
        </div>
        <div class="note">หมายเหตุ: ปุ่มดาวน์โหลดจะสร้างภาพสไตล์ VIP พร้อมกรอบทองจากค่าเลขด้านบน</div>
      </div>

      <!-- Right: slip preview -->
      <div class="card slipWrap">
        <div class="slip" id="slip">
          <div class="slipHeader">
            <div class="slipTitle" id="slipTitle"><?php echo htmlspecialchars($huay); ?></div>
            <div class="pill" id="slipDate"><?php echo $dateThai; ?></div>
          </div>
          <div class="slipBrand">เซียนตอง7K</div>
          <div class="list">
            <div class="box">
              <h4>เด่น</h4>
              <div class="nums" id="leadNums">-</div>
            </div>
            <div class="box">
              <h4>สองตัว</h4>
              <div class="nums" id="twoNums">-</div>
            </div>
            <div class="box">
              <h4>สามตัว</h4>
              <div class="nums" id="threeNums">-</div>
            </div>
          </div>
          <div class="footer">
            <img class="logo" src="assets/logo.png" alt="logo" onerror="this.style.display='none'">
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
const huay = <?php echo json_encode($huay, JSON_UNESCAPED_UNICODE); ?>;
const dateText = <?php echo json_encode($dateThai, JSON_UNESCAPED_UNICODE); ?>;
const lead = document.getElementById('lead');
const two1 = document.getElementById('two1');
const two2 = document.getElementById('two2');
const two3 = document.getElementById('two3');
const three1 = document.getElementById('three1');
const three2 = document.getElementById('three2');
const three3 = document.getElementById('three3');
const twoNodes = [two1,two2,two3];
const threeNodes = [three1,three2,three3];
const leadNums = document.getElementById('leadNums');
const twoNums = document.getElementById('twoNums');
const threeNums = document.getElementById('threeNums');
const posterBtn = document.getElementById('posterBtn');

function pad(val,len){ return (val||'').toString().padStart(len,'0').slice(0,len); }
function sanitize(){
  lead.value = lead.value.replace(/\D/g,'').slice(0,1);
  twoNodes.forEach(n=> n.value = n.value.replace(/\D/g,'').slice(0,2));
  threeNodes.forEach(n=> n.value = n.value.replace(/\D/g,'').slice(0,3));
}
function updateSlip(){
  sanitize();
  leadNums.textContent = lead.value? lead.value : '-';
  const twos = twoNodes.map(n=>n.value).filter(Boolean);
  twoNums.innerHTML = twos.length? twos.map(v=>`<span>${v}</span>`).join('') : '-';
  const threes = threeNodes.map(n=>n.value).filter(Boolean);
  threeNums.innerHTML = threes.length? threes.map(v=>`<span>${v}</span>`).join('') : '-';
  // poster link
  const params = new URLSearchParams();
  // build a 6-digit base from twos if available, else random will be used by server
  const six = (twos.join('') + '000000').slice(0,6);
  if (six.replace(/0/g,'').length) params.set('n', six);
  params.set('title', huay);
  params.set('date', dateText);
  if (lead.value) params.set('lead', lead.value);
  if (twos.length) params.set('two', twos.join(','));
  if (threes.length) params.set('three', threes.join(','));
  params.set('layout','vip');
  params.set('wm','0');
  posterBtn.href = 'download.php?' + params.toString();
}
function randNum(len){ return String(Math.floor(Math.random()*Math.pow(10,len))).padStart(len,'0'); }
function randomize(){
  lead.value = String(Math.floor(Math.random()*10));
  twoNodes.forEach((n,i)=> n.value = randNum(2));
  threeNodes.forEach((n,i)=> n.value = randNum(3));
  updateSlip();
}
function copyText(){
  const twos = twoNodes.map(n=>n.value).filter(Boolean).join(' ');
  const threes = threeNodes.map(n=>n.value).filter(Boolean).join(' ');
  const lines = [
    `${huay} งวด ${dateText}`,
    lead.value? `เด่น: ${lead.value}` : '',
    twos? `สองตัว: ${twos}` : '',
    threes? `สามตัว: ${threes}` : ''
  ].filter(Boolean);
  const text = lines.join('\n');
  navigator.clipboard.writeText(text).then(()=>{
    alert('คัดลอกแล้ว');
  });
}
[lead,...twoNodes,...threeNodes].forEach(n=> n.addEventListener('input', updateSlip));
document.getElementById('randBtn').addEventListener('click', randomize);
document.getElementById('copyBtn').addEventListener('click', copyText);
updateSlip();
</script>
</body></html>