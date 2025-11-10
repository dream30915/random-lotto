<?php
// Detail page with inputs and gold-bordered slip template
$huay = isset($_GET['huayname']) ? trim($_GET['huayname']) : 'หวยพิเศษ';
$t=time(); $y_th=(int)date('Y',$t)+543; $yy_th=substr((string)$y_th,-2); $dateThai=date('d/m/', $t).$yy_th; $dateIso=date('Y-m-d',$t);
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
  button,.btn{background:#222;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;font-family:inherit;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:transform .15s ease, box-shadow .15s ease}
  button:hover,.btn:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(0,0,0,.18);}
  .btn.gold{background:var(--gold-deep); color:#fff;}
  .btn.gold.active{box-shadow:0 0 0 2px rgba(255,216,104,.55);transform:none;}
  .btn.alt{background:#5a2a80}
  .note{font-size:12px;color:#444}
  .textInput{width:100%;max-width:360px;padding:10px 12px;border:1px solid #bdbdbd;border-radius:8px;font-family:inherit;font-size:16px;color:#222;text-align:left}
  .select{padding:10px 40px 10px 12px;border:1px solid #bdbdbd;border-radius:8px;font-family:inherit;font-size:16px;background:#fff url('data:image/svg+xml;utf8,<svg fill="%23222" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 12px center/16px 16px;appearance:none;color:#222;min-width:120px}
  .pill{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;background:#f3f1e9;border:1px solid #d0c59a;font-weight:600;color:#4d3215;min-width:96px;justify-content:center}
  .btn.small{padding:8px 12px;font-size:13px;border-radius:6px;background:#5a2a80}
  .row label.wide{min-width:110px}
  .optionRow label{display:flex;align-items:center;gap:6px;font-size:14px}
  .socialRow input{max-width:220px}
  /* Slip template */
  .slipWrap{padding:18px;background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.02));}
  .posterPreview{position:relative;border-radius:16px;overflow:hidden;box-shadow:0 12px 24px rgba(0,0,0,.35);background:url('assets/bg_custom_เซียนตอง.png') center/cover no-repeat;aspect-ratio:720/1280;min-height:580px;cursor:pointer;transition:box-shadow .18s ease,transform .18s ease;}
  .posterPreview.active{outline:3px solid var(--gold-deep);outline-offset:6px;transform:translateY(-2px);box-shadow:0 18px 32px rgba(0,0,0,.45);}
  .posterPreview.classic{background:linear-gradient(160deg,#4b142f,#1b0636);} 
  .posterPreview.noBg{background-image:linear-gradient(45deg,#bbbbbb55 25%,transparent 25%),linear-gradient(-45deg,#bbbbbb55 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#bbbbbb55 75%),linear-gradient(-45deg,transparent 75%,#bbbbbb55 75%);background-size:32px 32px;background-position:0 0,0 16px,16px -16px,-16px 0;background-color:transparent;}
  .posterLayer{position:absolute;inset:0;padding:28px 24px;color:#f7f0dd;font-weight:700;font-family:"Kanit",sans-serif;text-shadow:0 4px 12px rgba(0,0,0,.65);pointer-events:none;background:linear-gradient(135deg,rgba(0,0,0,.25),rgba(0,0,0,.6));}
  .posterLayer.noBg{background:none;text-shadow:0 4px 12px rgba(0,0,0,.75);}
  .posterHeader{position:absolute;top:24px;left:50%;transform:translateX(-50%);display:flex;flex-direction:column;align-items:center;gap:10px;font-size:50px;color:#ffd868;text-align:center;text-shadow:0 4px 16px rgba(0,0,0,.6);letter-spacing:3px;padding:0 26px;}
  .posterTitleText{position:relative;display:inline-flex;align-items:center;padding:0 26px;}
  .posterTitleText.crossed::after{content:"";position:absolute;left:-30px;right:-30px;height:20px;background:rgba(255,48,88,0.92);top:50%;transform:translateY(-50%);border-radius:20px;box-shadow:0 6px 18px rgba(0,0,0,.35);z-index:1;pointer-events:none;}
  .posterBadge{background:rgba(0,0,0,.55);padding:10px 22px;border-radius:999px;border:1px solid rgba(255,216,104,.35);font-size:25px;}
  .posterLead{position:absolute;top:47%;left:50%;transform:translate(-50%,-50%);font-size:180px;color:#ffd868;letter-spacing:14px;text-shadow:0 10px 30px rgba(0,0,0,.7);}
  .posterSub{position:absolute;right:12%;top:35%;display:flex;flex-direction:column;gap:20px;font-size:44px;align-items:flex-end;text-align:right;color:#fff;text-shadow:0 8px 26px rgba(0,0,0,.72);}
  .posterSub span{padding:0;margin:0;min-width:auto;}
  .posterBottom{position:absolute;left:50%;bottom:208px;transform:translateX(-50%);display:flex;gap:28px;flex-wrap:wrap;justify-content:center;font-size:52px;color:#fff;text-shadow:0 8px 26px rgba(0,0,0,.72);}
  .posterBottom span{padding:0;margin:0;}
  .posterSocial{position:absolute;left:48px;right:48px;bottom:42px;display:flex;align-items:flex-end;gap:16px;}
  .posterSocial.hidden{display:none!important;}
  .posterSocialBadge{display:flex;align-items:center;gap:12px;padding:12px 20px;border-radius:999px;background:#1b74e4;color:#fff;font-size:28px;font-weight:600;box-shadow:0 8px 22px rgba(0,0,0,.28);}  
  .posterSocialBadge .icon{display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 10px rgba(0,0,0,.2);}  
  .posterSocialBadge .text{white-space:nowrap;max-width:320px;overflow:hidden;text-overflow:ellipsis;}
  .posterSocialBadge.type-fb{background:#1b74e4;}
  .posterSocialBadge.type-fb .icon{width:42px;height:42px;border-radius:50%;background:#0866ff;position:relative;}
  .posterSocialBadge.type-fb .icon::before{content:'';width:20px;height:24px;display:block;background:url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20320%20512%22%3E%3Cpath%20fill%3D%22%23fff%22%20d%3D%22M279.14%20288l14.22-92.66h-88.91V127.41c0-25.35%2012.42-50.06%2052.24-50.06h40.42V6.26S264.43%200%20225.36%200c-73.22%200-121.05%2044.38-121.05%20124.72v70.62H22.89V288h81.41v224h100.17V288z%22/%3E%3C/svg%3E') center/contain no-repeat;}
  .posterSocialBadge.type-line{background:#06c755;}
  .posterSocialBadge.type-line .icon{width:44px;height:44px;border-radius:50%;background:#fff;color:#06c755;font-size:18px;font-weight:700;}
  .posterSocialBadge.align-right{margin-left:auto;}
  .posterEmpty{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:20px;color:#f7f0dd;background:rgba(0,0,0,.35);} 
  .posterLayer.noBg .posterEmpty{background:rgba(0,0,0,.45);}
  .toplinks{margin-bottom:8px;}
  .back{color:#fff}
  .k2{color:var(--gold)}
  .hdr{display:flex;align-items:center;gap:12px;margin-bottom:10px}
  .hdr h2{color:#3b1258}
  .titlePill{display:inline-block;background:#fff;border:1px solid #bbb;color:#333;border-radius:6px;padding:4px 10px;margin-left:6px;font-size:12px}
  .lbl{font-size:12px;color:#333}
  .pngRow{margin-top:12px;display:flex;justify-content:center;gap:12px}
  .hidden{display:none!important}
  .actionMenuMask{position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);z-index:2000;}
  .actionMenu{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fcfcfc;border-radius:18px;padding:22px 24px;box-shadow:0 18px 38px rgba(0,0,0,.35);width:min(420px,88%);z-index:2100;display:flex;flex-direction:column;gap:16px;font-family:"Kanit",sans-serif;}
  .actionMenu h3{margin:0;font-size:20px;color:#341246}
  .menuSectionTitle{font-size:14px;font-weight:600;color:#4a3b2a;margin-bottom:4px}
  .menuButtons{display:flex;gap:10px;flex-wrap:wrap}
  .menuBtn{flex:1 1 auto;min-width:120px;padding:10px 12px;border-radius:10px;border:1px solid #d1c68f;background:#fff;color:#4b3117;cursor:pointer;font-family:"Kanit",sans-serif;font-size:15px;display:flex;align-items:center;justify-content:center;transition:all .15s ease}
  .menuBtn:hover{background:#ffe9a6}
  .menuBtn.selected{background:#ffd868;border-color:#bd9933;color:#25160b;box-shadow:0 4px 12px rgba(0,0,0,.18)}
  .menuActions{display:flex;gap:10px;justify-content:flex-end}
  .menuActions .btn{flex:1}
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
        <div class="hdr"><h2>ตั้งค่าเลข</h2><span class="titlePill">ควบคุมโปสเตอร์</span></div>
        <div class="row"><label class="wide">ชื่อหัว</label><input id="title" class="textInput" value="<?php echo htmlspecialchars($huay); ?>" placeholder="เช่น ลาวสตาร์VIP"></div>
        <div class="row"><label class="wide">รูปแบบ</label>
          <select id="layout" class="select">
            <option value="vip" selected>VIP</option>
            <option value="classic">Classic</option>
          </select>
        </div>
        <div class="row"><label class="wide">วันที่</label>
          <div class="pill" id="dateDisplay"><?php echo $dateThai; ?></div>
          <button type="button" class="btn small" id="minusDay">ลบวัน</button>
          <button type="button" class="btn small" id="plusDay">บวกวัน</button>
        </div>
        <div class="row"><label class="wide">Seed (n)</label><input id="seed" class="lead" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric" value="<?php echo str_pad((string)rand(0,999999),6,'0',STR_PAD_LEFT); ?>"></div>
        <hr>
        <div class="row"><label class="wide">เด่น</label><input class="lead" id="lead" type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" placeholder="0"></div>
        <div class="row"><label class="wide">สองตัว</label>
          <input class="two" id="two1" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
          <input class="two" id="two2" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
          <input class="two" id="two3" type="text" maxlength="2" pattern="[0-9]*" inputmode="numeric" placeholder="00">
        </div>
        <div class="row"><label class="wide">สามตัว</label>
          <input class="three" id="three1" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
          <input class="three" id="three2" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
          <input class="three" id="three3" type="text" maxlength="3" pattern="[0-9]*" inputmode="numeric" placeholder="000">
        </div>
        <div class="row socialRow"><label class="wide">โซเชียล</label>
          <input id="fb" class="textInput" placeholder="ชื่อเฟส">
          <input id="line" class="textInput" placeholder="ไอดีไลน์">
        </div>
        <div class="row optionRow"><label class="wide">ตัวเลือก</label>
          <label><input type="checkbox" id="wm" checked> ใส่วอเตอร์มาร์ค</label>
        </div>
        <div class="actions">
          <button type="button" id="randBtn" class="btn gold">สุ่มเลข</button>
          <button type="button" id="previewBtn" class="btn gold">ดูตัวอย่าง</button>
          <button type="button" id="guardBtn" class="btn gold">กันตรวจ</button>
          <button type="button" id="pngBtn" class="btn gold">PNG</button>
          <button type="button" id="downloadBtn" class="btn gold">ดาวน์โหลด</button>
          <button type="button" id="shareBtn" class="btn gold">แชร์รูปนี้</button>
        </div>
      </div>

      <!-- Right: slip preview -->
      <div class="card slipWrap">
        <div class="posterPreview" id="posterPreview">
          <div class="posterLayer">
            <div class="posterHeader">
              <div id="posterTitle" class="posterTitleText"><?php echo htmlspecialchars($huay); ?></div>
              <div class="posterBadge" id="posterDate"><?php echo $dateThai; ?></div>
            </div>
            <div class="posterLead" id="posterLead">-</div>
            <div class="posterSub" id="posterTwo"></div>
            <div class="posterBottom" id="posterThree"></div>
            <div class="posterSocial hidden" id="posterSocial"></div>
            <div class="posterEmpty" id="posterEmpty">กรอกหรือกดสุ่มเพื่อแสดงเลขบนภาพ</div>
          </div>
        </div>
        <div class="posterPreview noBg hidden" id="posterPreviewPng">
          <div class="posterLayer noBg">
            <div class="posterHeader">
              <div id="posterTitlePng" class="posterTitleText"><?php echo htmlspecialchars($huay); ?></div>
              <div class="posterBadge" id="posterDatePng"><?php echo $dateThai; ?></div>
            </div>
            <div class="posterLead" id="posterLeadPng">-</div>
            <div class="posterSub" id="posterTwoPng"></div>
            <div class="posterBottom" id="posterThreePng"></div>
            <div class="posterSocial hidden" id="posterSocialPng"></div>
            <div class="posterEmpty" id="posterEmptyPng">กด PNG เพื่อดูภาพไม่มีพื้นหลัง</div>
          </div>
        </div>
        <div class="pngRow hidden" id="pngRow">
          <a id="downloadPngBtn" class="btn gold" href="#" target="_blank" rel="noopener">ดาวน์โหลด PNG ใส</a>
        </div>
      </div>
    </div>
  </div>

<script>
const initialTitle = <?php echo json_encode($huay, JSON_UNESCAPED_UNICODE); ?>;
const initialThaiDate = <?php echo json_encode($dateThai, JSON_UNESCAPED_UNICODE); ?>;
const initialIsoDate = <?php echo json_encode($dateIso, JSON_UNESCAPED_UNICODE); ?>;
const titleEl = document.getElementById('title');
const layoutEl = document.getElementById('layout');
const dateDisplay = document.getElementById('dateDisplay');
const minusDayBtn = document.getElementById('minusDay');
const plusDayBtn = document.getElementById('plusDay');
const seedEl = document.getElementById('seed');
const leadEl = document.getElementById('lead');
const twoEls = [document.getElementById('two1'), document.getElementById('two2'), document.getElementById('two3')];
const threeEls = [document.getElementById('three1'), document.getElementById('three2'), document.getElementById('three3')];
const fbEl = document.getElementById('fb');
const lineEl = document.getElementById('line');
const wmEl = document.getElementById('wm');
const randBtn = document.getElementById('randBtn');
const previewBtn = document.getElementById('previewBtn');
const guardBtn = document.getElementById('guardBtn');
const pngBtn = document.getElementById('pngBtn');
const downloadBtn = document.getElementById('downloadBtn');
const shareBtn = document.getElementById('shareBtn');
const posterPreview = document.getElementById('posterPreview');
const posterTitle = document.getElementById('posterTitle');
const posterDate = document.getElementById('posterDate');
const posterLead = document.getElementById('posterLead');
const posterTwo = document.getElementById('posterTwo');
const posterThree = document.getElementById('posterThree');
const posterEmpty = document.getElementById('posterEmpty');
const posterPreviewPng = document.getElementById('posterPreviewPng');
const posterTitlePng = document.getElementById('posterTitlePng');
const posterDatePng = document.getElementById('posterDatePng');
const posterLeadPng = document.getElementById('posterLeadPng');
const posterTwoPng = document.getElementById('posterTwoPng');
const posterThreePng = document.getElementById('posterThreePng');
const posterEmptyPng = document.getElementById('posterEmptyPng');
const posterSocial = document.getElementById('posterSocial');
const posterSocialPng = document.getElementById('posterSocialPng');
const pngRow = document.getElementById('pngRow');
const downloadPngBtn = document.getElementById('downloadPngBtn');
const vipBgFile = 'bg_custom_เซียนตอง.png';

const shareDestinations = [
  {
    id: 'facebook',
    label: 'Facebook'
  },
  {
    id: 'line',
    label: 'LINE'
  },
  {
    id: 'tiktok',
    label: 'TikTok'
  }
];

const backgroundCache = new Map();

const facebookGlyphPath = (()=>{
  if (typeof Path2D === 'undefined') return null;
  try {
    return new Path2D('M279.14 288l14.22-92.66h-88.91V127.41c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S264.43 0 225.36 0c-73.22 0-121.05 44.38-121.05 124.72v70.62H22.89V288h81.41v224h100.17V288z');
  } catch (err){
    return null;
  }
})();

function loadImageAsset(src){
  return new Promise((resolve, reject)=>{
    const img = new Image();
    img.onload = ()=> resolve(img);
    img.onerror = ()=> reject(new Error('bg-load-failed'));
    img.src = src;
  });
}

async function getPosterBackground(layout){
  const key = layout || 'vip';
  if (backgroundCache.has(key)){
    return backgroundCache.get(key);
  }
  let path = null;
  if (layout === 'vip'){
    path = 'assets/bg_custom_เซียนตอง.png';
  }
  if (!path){
    backgroundCache.set(key, null);
    return null;
  }
  try {
    const img = await loadImageAsset(path);
    backgroundCache.set(key, img);
    return img;
  } catch (err){
    console.warn('background load failed', err);
    backgroundCache.set(key, null);
    return null;
  }
}

let activeMode = 'normal'; // 'normal' usesพื้นหลัง, 'png' ใช้ overlay
let guardMode = false;

const previewSets = [
  { mode: 'normal', root: posterPreview, title: posterTitle, date: posterDate, lead: posterLead, two: posterTwo, three: posterThree, empty: posterEmpty, social: posterSocial }
];
if (posterPreviewPng) {
  previewSets.push({ mode: 'png', root: posterPreviewPng, title: posterTitlePng, date: posterDatePng, lead: posterLeadPng, two: posterTwoPng, three: posterThreePng, empty: posterEmptyPng, social: posterSocialPng });
}

let currentDate = new Date(initialIsoDate + 'T00:00:00');

function formatThai(date){
  const dd = String(date.getDate()).padStart(2,'0');
  const mm = String(date.getMonth()+1).padStart(2,'0');
  const beYY = String(date.getFullYear()+543).slice(-2);
  return `${dd}/${mm}/${beYY}`;
}
function adjustDate(offset){
  currentDate.setDate(currentDate.getDate()+offset);
  dateDisplay.textContent = formatThai(currentDate);
  updateSlip();
}

function sanitize(){
  seedEl.value = seedEl.value.replace(/[^0-9]/g,'').slice(0,6);
  leadEl.value = leadEl.value.replace(/[^0-9]/g,'').slice(0,1);
  twoEls.forEach(el=> el.value = el.value.replace(/[^0-9]/g,'').slice(0,2));
  threeEls.forEach(el=> el.value = el.value.replace(/[^0-9]/g,'').slice(0,3));
}

function gatherLists(list){
  return list.map(el=>el.value.trim()).filter(Boolean);
}

function buildParams(extra = {}){
  const params = new URLSearchParams();
  const seed = seedEl.value.padStart(6,'0');
  const twos = gatherLists(twoEls);
  const threes = gatherLists(threeEls);
  if (seed) params.set('n', seed.slice(0,6));
  params.set('layout', layoutEl.value);
  if (titleEl.value.trim()) params.set('title', titleEl.value.trim());
  params.set('date', dateDisplay.textContent.trim());
  if (leadEl.value) params.set('lead', leadEl.value);
  if (twos.length) params.set('two', twos.join(','));
  if (threes.length) params.set('three', threes.join(','));
  if (fbEl.value.trim()) params.set('fb', fbEl.value.trim());
  if (lineEl.value.trim()) params.set('line', lineEl.value.trim());
  params.set('wm', wmEl.checked ? '1' : '0');
  if (layoutEl.value === 'vip'){
    const isOverlay = String(extra.overlay ?? '0') === '1';
    if (!isOverlay){
      params.set('bgfile', vipBgFile);
    }
    if (twos.length){
      params.set('rightColumn','1');
      params.set('rightcol', twos.join(','));
    }
  }
  Object.entries(extra).forEach(([k,v])=>{
    if (v !== undefined && v !== null) params.set(k,v);
  });
  return params;
}

function refreshActionLinks(){
  if (!downloadPngBtn) return;
  const params = buildParams({ overlay:'1', _: Date.now() }).toString();
  const url = new URL('download.php?' + params, window.location.href);
  downloadPngBtn.href = url.toString();
  downloadPngBtn.target = '_blank';
  downloadPngBtn.rel = 'noopener';
}

function updateSlip(){
  sanitize();
  const twos = gatherLists(twoEls);
  const threes = gatherLists(threeEls);
  const hasNumbers = Boolean(leadEl.value || twos.length || threes.length);
  const titleText = titleEl.value.trim() || initialTitle;
  const dateText = dateDisplay.textContent.trim();
  const fbText = fbEl.value.trim().slice(0,28);
  const lineText = lineEl.value.trim().slice(0,28);
  const socials = [];
  if (fbText) socials.push({ type:'fb', label: fbText });
  if (lineText) socials.push({ type:'line', label: lineText });
  previewSets.forEach(set=>{
    if (!set.root) return;
    if (set.title) {
      set.title.textContent = titleText;
      set.title.classList.toggle('crossed', guardMode);
    }
    if (set.date) set.date.textContent = dateText;
    if (set.lead) set.lead.textContent = leadEl.value || '-';
    if (set.two) {
      set.two.innerHTML = twos.map(v=>`<span>${v}</span>`).join('');
      set.two.style.display = twos.length ? 'flex' : 'none';
    }
    if (set.three) {
      set.three.innerHTML = threes.map(v=>`<span>${v}</span>`).join('');
      set.three.style.display = threes.length ? 'flex' : 'none';
    }
    if (set.social){
      if (socials.length){
        const hasFb = socials.some(s=>s.type === 'fb');
        set.social.innerHTML = socials.map(item=>{
          const baseCls = item.type === 'line' ? 'posterSocialBadge type-line' : 'posterSocialBadge type-fb';
          const alignCls = item.type === 'line' && hasFb ? ' align-right' : '';
          const iconMarkup = item.type === 'line' ? '<span class="icon">LINE</span>' : '<span class="icon" aria-hidden="true"></span>';
          return `<span class="${baseCls}${alignCls}">${iconMarkup}<span class="text">${item.label}</span></span>`;
        }).join('');
        set.social.classList.remove('hidden');
      } else {
        set.social.innerHTML = '';
        set.social.classList.add('hidden');
      }
    }
    if (set.empty) set.empty.style.display = hasNumbers ? 'none' : 'flex';
    if (set.mode === 'png'){
      set.root.classList.remove('classic');
    } else {
      set.root.classList.toggle('classic', layoutEl.value === 'classic');
    }
    set.root.classList.toggle('active', set.mode === activeMode);
  });
  if (pngBtn) pngBtn.classList.toggle('active', activeMode === 'png');
  if (guardBtn) guardBtn.classList.toggle('active', guardMode);
  refreshActionLinks();
}

function randInt(max){ return Math.floor(Math.random()*max); }
function randomize(){
  leadEl.value = String(randInt(10));
  twoEls.forEach(el=> el.value = String(randInt(100)).padStart(2,'0'));
  threeEls.forEach(el=> el.value = String(randInt(1000)).padStart(3,'0'));
  seedEl.value = String(randInt(1000000)).padStart(6,'0');
  updateSlip();
}

function setActiveMode(mode){
  if (mode === 'png' && posterPreviewPng){
    posterPreviewPng.classList.remove('hidden');
  }
  if (pngRow) pngRow.classList.toggle('hidden', mode !== 'png');
  activeMode = mode;
  updateSlip();
}

function togglePngMode(){
  if (!posterPreviewPng) return;
  const nextMode = activeMode === 'png' ? 'normal' : 'png';
  setActiveMode(nextMode);
}

function roundedRectPath(ctx,x,y,width,height,radius){
  const r = Math.max(0, Math.min(radius, Math.min(width,height)/2));
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.lineTo(x + width - r, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + r);
  ctx.lineTo(x + width, y + height - r);
  ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
  ctx.lineTo(x + r, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - r);
  ctx.lineTo(x, y + r);
  ctx.quadraticCurveTo(x, y, x + r, y);
  ctx.closePath();
}

function drawFacebookGlyph(ctx, cx, cy, radius){
  ctx.save();
  ctx.beginPath();
  ctx.arc(cx, cy, radius, 0, Math.PI * 2);
  ctx.fillStyle = '#0866ff';
  ctx.fill();
  ctx.fillStyle = '#fff';
  if (facebookGlyphPath){
    ctx.translate(cx, cy);
    const scale = (radius * 2) / 512;
    ctx.scale(scale, scale);
    ctx.translate(-160, -256);
    ctx.fill(facebookGlyphPath);
  } else {
    const fallbackSize = Math.round(radius * 1.4);
    ctx.font = `${fallbackSize}px "Arial", sans-serif`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('f', cx, cy + radius * 0.1);
  }
  ctx.restore();
}

async function generatePosterBlob(mode){
  if (mode === 'png'){
    setActiveMode('png');
  } else {
    setActiveMode('normal');
  }
  if (document.fonts && document.fonts.ready){
    try { await document.fonts.ready; } catch (e) {}
  }
  sanitize();
  const seed = (seedEl.value.replace(/[^0-9]/g,'') || '').padStart(6,'0') || '000000';
  let twos = gatherLists(twoEls);
  if (!twos.length){ twos = [seed.slice(0,2), seed.slice(2,4), seed.slice(4,6)]; }
  twos = twos.slice(0,3);
  let threes = gatherLists(threeEls);
  if (!threes.length){ threes = [seed.slice(0,3), seed.slice(3,6)]; }
  threes = threes.slice(0,3);
  const leadText = (leadEl.value || seed.charAt(0) || '-').slice(0,1) || '-';
  const titleText = titleEl.value.trim() || initialTitle;
  const dateText = dateDisplay.textContent.trim();
  const fbText = fbEl.value.trim().slice(0,28);
  const lineText = lineEl.value.trim().slice(0,28);
  const socialEntries = [];
  if (fbText) socialEntries.push({ type:'fb', label: fbText });
  if (lineText) socialEntries.push({ type:'line', label: lineText });
  const guardActive = guardMode;
  const layout = layoutEl.value;
  const transparent = mode === 'png';

  const width = 1080;
  const height = 1350;
  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0,0,width,height);

  if (!transparent){
    try {
      const bg = await getPosterBackground(layout);
      if (bg){
        ctx.drawImage(bg,0,0,width,height);
      } else {
        const grad = ctx.createLinearGradient(0,0,width,height);
        grad.addColorStop(0,'#39184a');
        grad.addColorStop(1,'#7b3b00');
        ctx.fillStyle = grad;
        ctx.fillRect(0,0,width,height);
      }
    } catch (err){
      const grad = ctx.createLinearGradient(0,0,width,height);
      grad.addColorStop(0,'#39184a');
      grad.addColorStop(1,'#7b3b00');
      ctx.fillStyle = grad;
      ctx.fillRect(0,0,width,height);
    }
    const overlay = ctx.createLinearGradient(0,0,width,height);
    overlay.addColorStop(0,'rgba(0,0,0,0.25)');
    overlay.addColorStop(1,'rgba(0,0,0,0.6)');
    ctx.fillStyle = overlay;
    ctx.fillRect(0,0,width,height);
  } else {
    ctx.clearRect(0,0,width,height);
  }

  // Title
  ctx.save();
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.font = '700 104px "Kanit", sans-serif';
  const titleY = 120;
  const titleMetrics = ctx.measureText(titleText);
  ctx.fillStyle = '#ffd868';
  ctx.shadowColor = 'rgba(0,0,0,0.6)';
  ctx.shadowBlur = 18;
  ctx.fillText(titleText, width/2, titleY);
  if (guardActive){
    const lineThickness = 26;
    const minWidth = 260;
    const lineWidth = Math.max(titleMetrics.width + 140, minWidth);
    const lineX = width/2 - lineWidth/2;
    ctx.fillStyle = 'rgba(255,48,88,0.94)';
    ctx.shadowColor = 'rgba(0,0,0,0.45)';
    ctx.shadowBlur = 18;
    roundedRectPath(ctx, lineX, titleY - lineThickness/2, lineWidth, lineThickness, lineThickness/2);
    ctx.fill();
  }
  ctx.restore();

  // Date badge
  ctx.save();
  ctx.font = '600 60px "Kanit", sans-serif';
  const badgeTextWidth = ctx.measureText(dateText).width;
  const badgePaddingX = 42;
  const badgeWidth = badgeTextWidth + badgePaddingX * 2;
  const badgeHeight = 78;
  const badgeX = width/2 - badgeWidth/2;
  const badgeY = 212 - badgeHeight/2;
  ctx.shadowColor = 'rgba(0,0,0,0.45)';
  ctx.shadowBlur = 14;
  ctx.fillStyle = 'rgba(0,0,0,0.55)';
  roundedRectPath(ctx,badgeX,badgeY,badgeWidth,badgeHeight,40);
  ctx.fill();
  ctx.shadowBlur = 0;
  ctx.lineWidth = 2;
  ctx.strokeStyle = 'rgba(255,216,104,0.35)';
  ctx.stroke();
  ctx.fillStyle = '#ffd868';
  ctx.shadowColor = 'rgba(0,0,0,0.35)';
  ctx.shadowBlur = 10;
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(dateText, width/2, 212);
  ctx.restore();

  // Lead number
  ctx.save();
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.font = '700 220px "Kanit", sans-serif';
  ctx.fillStyle = '#ffd868';
  ctx.shadowColor = 'rgba(0,0,0,0.75)';
  ctx.shadowBlur = 28;
  ctx.fillText(leadText, width/2, height * 0.46);
  ctx.restore();

  // Lead label
  ctx.save();
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.font = '700 72px "Kanit", sans-serif';
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0,0,0,0.65)';
  ctx.shadowBlur = 12;
  ctx.fillText('เด่น', width/2, height * 0.57);
  ctx.restore();

  // Two-digit numbers (right column)
  ctx.save();
  ctx.textAlign = 'right';
  ctx.textBaseline = 'middle';
  ctx.font = '700 84px "Kanit", sans-serif';
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0,0,0,0.78)';
  ctx.shadowBlur = 20;
  const rightX = width * 0.88;
  const twoStartY = height * 0.32;
  const twoGap = 140;
  twos.forEach((val, idx)=>{
    ctx.fillText(val.padStart(2,'0'), rightX, twoStartY + idx * twoGap);
  });
  ctx.restore();

  // Three-digit numbers (bottom row)
  ctx.save();
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.font = '700 96px "Kanit", sans-serif';
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0,0,0,0.75)';
  ctx.shadowBlur = 20;
  const bottomY = height - 270;
  if (threes.length){
    const spacing = 240;
    const totalWidth = spacing * (threes.length - 1);
    const startX = width/2 - totalWidth/2;
    threes.forEach((val, idx)=>{
      ctx.fillText(val.padStart(3,'0'), startX + idx * spacing, bottomY);
    });
  }
  ctx.restore();

  if (socialEntries.length){
    const badgeHeight = 66;
    const paddingX = 20;
    const iconGap = 14;
    const cornerRadius = 30;
    const textFont = '600 32px "Kanit", sans-serif';
    const baseBottom = height - 58;
    const yTop = baseBottom - badgeHeight;
    const leftMargin = 60;
    const rightMargin = 60;

    ctx.save();
    ctx.font = textFont;
    const layoutData = socialEntries.map(entry => {
      const iconSize = entry.type === 'line' ? 44 : 42;
      const textWidth = ctx.measureText(entry.label).width;
      const badgeWidth = paddingX * 2 + iconSize + iconGap + textWidth;
      return { entry, iconSize, badgeWidth };
    });
    ctx.restore();

    const fbData = layoutData.find(item => item.entry.type === 'fb');
    const lineData = layoutData.find(item => item.entry.type === 'line');

    const drawBadge = (data, x)=>{
      const { entry, iconSize, badgeWidth } = data;
      const isLine = entry.type === 'line';
      const bgColor = isLine ? '#06c755' : '#1b74e4';
      const iconCx = x + paddingX + iconSize / 2;
      const iconCy = yTop + badgeHeight / 2;

      ctx.save();
      roundedRectPath(ctx, x, yTop, badgeWidth, badgeHeight, cornerRadius);
      ctx.fillStyle = bgColor;
      ctx.fill();
      if (isLine){
        ctx.beginPath();
        ctx.arc(iconCx, iconCy, iconSize / 2, 0, Math.PI * 2);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.font = '700 18px "Kanit", sans-serif';
        ctx.fillStyle = '#06c755';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('LINE', iconCx, iconCy + 1);
      } else {
        drawFacebookGlyph(ctx, iconCx, iconCy, iconSize / 2);
      }
      ctx.font = textFont;
      ctx.fillStyle = '#fff';
      ctx.textAlign = 'left';
      ctx.textBaseline = 'middle';
      const textX = x + paddingX + iconSize + iconGap;
      ctx.fillText(entry.label, textX, iconCy);
      ctx.restore();
    };

    if (fbData){
      drawBadge(fbData, leftMargin);
    }
    if (lineData){
      const lineX = fbData ? (width - rightMargin - lineData.badgeWidth) : leftMargin;
      drawBadge(lineData, lineX);
    }
  }

  if (!transparent && wmEl.checked){
    ctx.save();
    ctx.font = '700 64px "Kanit", sans-serif';
    ctx.fillStyle = 'rgba(255,216,104,0.18)';
    ctx.rotate(-Math.PI/10);
  const text = 'ทีเด็ด7โกรก';
    const stepX = 380;
    const stepY = 260;
    for (let y=-height; y<height*2; y+=stepY){
      for (let x=-width; x<width*2; x+=stepX){
        ctx.fillText(text, x, y);
      }
    }
    ctx.restore();
  }

  return new Promise((resolve, reject)=>{
    canvas.toBlob(blob=>{
      if (!blob){ reject(new Error('gen-failed')); return; }
      resolve(blob);
    }, 'image/png');
  });
}

async function downloadTransparentPoster(){
  const blob = await generatePosterBlob('png');
  triggerDownloadFromBlob(blob, 'huay_poster_transparent.png');
}

function triggerDownloadFromBlob(blob, filename){
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  setTimeout(()=>{
    URL.revokeObjectURL(link.href);
    link.remove();
  }, 300);
}

function safeOpenBlob(blob){
  const win = window.open('', '_blank');
  if (!win){
    return false;
  }
  try { win.opener = null; } catch (e) {}
  const blobUrl = URL.createObjectURL(blob);
  win.location.href = blobUrl;
  setTimeout(()=> URL.revokeObjectURL(blobUrl), 60000);
  return true;
}

function triggerDirectDownload(url, filename){
  const link = document.createElement('a');
  link.href = url;
  if (filename) link.download = filename;
  link.rel = 'noopener';
  link.style.display = 'none';
  document.body.appendChild(link);
  link.click();
  setTimeout(()=> link.remove(), 100);
}

async function previewPoster(mode){
  setActiveMode(mode);
  try {
    const blob = await generatePosterBlob(mode === 'png' ? 'png' : 'normal');
    if (!safeOpenBlob(blob)){
      triggerDownloadFromBlob(blob, mode === 'png' ? 'huay_poster_transparent.png' : 'huay_poster.png');
    }
    return true;
  } catch (err){
    console.error(err);
    return false;
  }
}

async function downloadPoster(mode){
  setActiveMode(mode);
  try {
    if (mode === 'png'){
      await downloadTransparentPoster();
    } else {
      const blob = await generatePosterBlob('normal');
      triggerDownloadFromBlob(blob, 'huay_poster.png');
    }
    return true;
  } catch (err){
    console.error(err);
    return false;
  }
}

async function sharePoster(mode, destinationId){
  setActiveMode(mode);
  try {
    const blob = await generatePosterBlob(mode === 'png' ? 'png' : 'normal');
    const filename = mode === 'png' ? 'huay_poster_transparent.png' : 'huay_poster.png';
    const file = new File([blob], filename, { type: 'image/png' });
    if (navigator.canShare && navigator.canShare({ files:[file] })){
      await navigator.share({ files:[file], title: titleEl.value || 'HuayKinMaiMod', text: 'โปสเตอร์เลข' });
    } else {
      triggerDownloadFromBlob(blob, filename);
      const destShare = shareDestinations.find(item=> item.id === destinationId);
      if (destShare){
        alert('บราวเซอร์ไม่รองรับแชร์ไฟล์อัตโนมัติ กรุณาอัปโหลดภาพไปยัง ' + destShare.label + ' ด้วยตนเอง');
      } else {
        alert('บราวเซอร์ไม่รองรับแชร์ไฟล์อัตโนมัติ กรุณาอัปโหลดภาพด้วยตนเอง');
      }
    }
    return true;
  } catch (err){
    console.error(err);
    return false;
  }
}

function closeActionMenu(mask){
  if (!mask) return;
  if (mask._escHandler){
    document.removeEventListener('keydown', mask._escHandler);
    mask._escHandler = null;
  }
  if (mask.parentNode){
    mask.parentNode.removeChild(mask);
  }
}

function openActionMenu(kind){
  const mask = document.createElement('div');
  mask.className = 'actionMenuMask';
  const menu = document.createElement('div');
  menu.className = 'actionMenu';

  const titles = {
    preview: 'เลือกรูปที่จะดูตัวอย่าง',
    download: 'เลือกรูปที่จะดาวน์โหลด',
    share: 'เลือกรูปและปลายทางที่ต้องการแชร์'
  };
  const confirmLabels = {
    preview: 'เปิดดู',
    download: 'ดาวน์โหลด',
    share: 'แชร์เลย'
  };

  const availableModes = [{ id:'normal', label:'รูปพื้นหลัง' }];
  if (posterPreviewPng){
    availableModes.push({ id:'png', label:'PNG ไม่มีพื้นหลัง' });
  }

  if (availableModes.length === 1 && kind !== 'share'){
    const singleMode = availableModes[0].id;
    if (kind === 'preview'){
      previewPoster(singleMode).then(ok=>{
        if (!ok){
          alert('ไม่สามารถแสดงตัวอย่างได้ กรุณาลองอีกครั้ง');
        }
      });
    } else if (kind === 'download'){
      downloadPoster(singleMode).then(ok=>{
        if (!ok){
          alert('ไม่สามารถดาวน์โหลดได้ กรุณาลองอีกครั้ง');
        }
      });
    }
    return;
  }

  let selectedMode = availableModes.some(opt=> opt.id === activeMode) ? activeMode : availableModes[0].id;
  let selectedDestination = shareDestinations[0]?.id || null;

  menu.innerHTML = `
    <h3>${titles[kind]}</h3>
    <div>
      <div class="menuSectionTitle">เลือกรูป</div>
      <div class="menuButtons" id="modeButtons"></div>
    </div>
    ${kind === 'share' ? '<div><div class="menuSectionTitle">แชร์ไปที่</div><div class="menuButtons" id="shareButtons"></div></div>' : ''}
    <div class="menuActions">
      <button type="button" class="btn" id="cancelAction">ยกเลิก</button>
      <button type="button" class="btn gold" id="confirmAction">${confirmLabels[kind]}</button>
    </div>
  `;

  mask.appendChild(menu);
  document.body.appendChild(mask);

  const modeButtonsWrap = menu.querySelector('#modeButtons');
  availableModes.forEach(opt=>{
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'menuBtn' + (opt.id === selectedMode ? ' selected' : '');
    btn.dataset.mode = opt.id;
    btn.textContent = opt.label;
    btn.addEventListener('click', ()=>{
      selectedMode = opt.id;
      modeButtonsWrap.querySelectorAll('.menuBtn').forEach(el=> el.classList.toggle('selected', el.dataset.mode === selectedMode));
    });
    modeButtonsWrap.appendChild(btn);
  });

  let shareButtonsWrap = null;
  if (kind === 'share'){
    shareButtonsWrap = menu.querySelector('#shareButtons');
    shareDestinations.forEach(dest=>{
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'menuBtn' + (dest.id === selectedDestination ? ' selected' : '');
      btn.dataset.dest = dest.id;
      btn.textContent = dest.label;
      btn.addEventListener('click', ()=>{
        selectedDestination = dest.id;
        shareButtonsWrap.querySelectorAll('.menuBtn').forEach(el=> el.classList.toggle('selected', el.dataset.dest === selectedDestination));
      });
      shareButtonsWrap.appendChild(btn);
    });
  }

  const cancelBtn = menu.querySelector('#cancelAction');
  const confirmBtn = menu.querySelector('#confirmAction');

  const escHandler = (ev)=>{
    if (ev.key === 'Escape'){
      closeActionMenu(mask);
    }
  };
  document.addEventListener('keydown', escHandler);
  mask._escHandler = escHandler;

  cancelBtn.addEventListener('click', ()=> closeActionMenu(mask));
  mask.addEventListener('click', (ev)=>{ if (ev.target === mask) closeActionMenu(mask); });

  confirmBtn.addEventListener('click', async ()=>{
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'กำลังดำเนินการ...';
    let ok = false;
    if (kind === 'preview'){
      ok = await previewPoster(selectedMode);
      if (!ok){ alert('ไม่สามารถแสดงตัวอย่างได้ กรุณาลองอีกครั้ง'); }
    } else if (kind === 'download'){
      ok = await downloadPoster(selectedMode);
      if (!ok){ alert('ไม่สามารถดาวน์โหลดได้ กรุณาลองอีกครั้ง'); }
    } else if (kind === 'share'){
      ok = await sharePoster(selectedMode, selectedDestination);
      if (!ok){ alert('ไม่สามารถแชร์ได้ กรุณาลองอีกครั้ง'); }
    }
    closeActionMenu(mask);
  });
}

titleEl.addEventListener('input', updateSlip);
layoutEl.addEventListener('change', updateSlip);
seedEl.addEventListener('input', updateSlip);
[leadEl, ...twoEls, ...threeEls].forEach(el=> el.addEventListener('input', updateSlip));
fbEl.addEventListener('input', updateSlip);
lineEl.addEventListener('input', updateSlip);
wmEl.addEventListener('change', updateSlip);
minusDayBtn.addEventListener('click', ()=> adjustDate(-1));
plusDayBtn.addEventListener('click', ()=> adjustDate(1));
randBtn.addEventListener('click', randomize);
previewBtn.addEventListener('click', ()=> openActionMenu('preview'));
if (guardBtn) guardBtn.addEventListener('click', ()=>{
  guardMode = !guardMode;
  updateSlip();
});
downloadBtn.addEventListener('click', ()=> openActionMenu('download'));
shareBtn.addEventListener('click', ()=> openActionMenu('share'));
if (pngBtn) pngBtn.addEventListener('click', togglePngMode);
if (posterPreview) {
  posterPreview.addEventListener('click', ()=> setActiveMode('normal'));
}
if (posterPreviewPng) {
  posterPreviewPng.addEventListener('click', ()=> setActiveMode('png'));
}
if (downloadPngBtn){
  downloadPngBtn.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    try {
      await downloadTransparentPoster();
    } catch(err){
      triggerDirectDownload(downloadPngBtn.href, 'huay_poster_transparent.png');
    }
  });
}

dateDisplay.textContent = initialThaiDate;
setActiveMode('normal');
</script>
</body></html>