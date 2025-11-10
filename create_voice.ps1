# =====================================================
# create_voice.ps1
# สร้างเสียงชายไทยวัยกลางคน + ผสมเสียงลม/ร้านหวยอัตโนมัติ
# =====================================================

# 🔑 ใส่ API Key ของคุณ
$OPENAI_API_KEY = "sk-ใส่คีย์ของคุณตรงนี้"

# 💬 ข้อความพูด
$voiceText = "รวยเพราะสามตัวบนนนน~ จนเพราะสองตัวล่างงง~ ฮ่าๆๆๆๆๆ เอ้าา ไปซื้อใหม่!"

# 🗂️ ไฟล์ปลายทาง
$basePath = "C:\Users\msi\Desktop"
$outputVoice = Join-Path $basePath "เซียนหวย_voice.mp3"
$outputFinal = Join-Path $basePath "เซียนหวย_final.mp3"

# 🎤 สร้างเสียงพูดจาก OpenAI TTS
Write-Host "🎙️ กำลังสร้างเสียงพูด AI..." -ForegroundColor Cyan
Invoke-RestMethod `
  -Uri "https://api.openai.com/v1/audio/speech" `
  -Headers @{ "Authorization" = "Bearer $OPENAI_API_KEY"; "Content-Type" = "application/json" } `
  -Method POST `
  -Body (@{
      model = "gpt-4o-mini-tts"
      voice = "alloy"
      input = $voiceText
    } | ConvertTo-Json) `
  -OutFile $outputVoice

if (-not (Test-Path $outputVoice)) {
    Write-Host "❌ สร้างเสียงไม่สำเร็จ ตรวจสอบ API Key อีกครั้ง" -ForegroundColor Red
    exit
}

Write-Host "✅ สร้างเสียงพูดสำเร็จ: $outputVoice" -ForegroundColor Green

# 🌬️ ดาวน์โหลดเสียงพื้นหลัง (ลม + ร้านหวยเบา ๆ)
Write-Host "🌬️ ดาวน์โหลดเสียงพื้นหลัง..." -ForegroundColor Cyan
$bg1 = Join-Path $basePath "wind.mp3"
$bg2 = Join-Path $basePath "market.mp3"

Invoke-WebRequest -Uri "https://cdn.pixabay.com/download/audio/2022/03/09/audio_f32e7a4f9d.mp3?filename=soft-wind-ambient.mp3" -OutFile $bg1
Invoke-WebRequest -Uri "https://cdn.pixabay.com/download/audio/2021/11/03/audio_750b604e3b.mp3?filename=market-noise-small-crowd.mp3" -OutFile $bg2

# 🎵 รวมเสียงพูด + เสียงพื้นหลัง ด้วย ffmpeg
Write-Host "🎧 ผสมเสียงทั้งหมด..." -ForegroundColor Cyan
$ffmpeg = "C:\ffmpeg\bin\ffmpeg.exe"  # ถ้ามี ffmpeg ติดตั้งในเครื่อง ให้แก้ path นี้

if (-not (Test-Path $ffmpeg)) {
    Write-Host "⚠️ ไม่พบ ffmpeg.exe กรุณาติดตั้งก่อน (https://ffmpeg.org/download.html)" -ForegroundColor Yellow
    Write-Host "   หรือวาง ffmpeg.exe ที่ C:\ffmpeg\bin\" -ForegroundColor Yellow
    exit
}

# คำสั่งผสมเสียง: ลดเสียงพื้นหลังให้เบา แล้วรวมกับเสียงพูด
& $ffmpeg -y -i $outputVoice -i $bg1 -i $bg2 -filter_complex "[1]volume=0.2[a1];[2]volume=0.3[a2];[0][a1][a2]amix=inputs=3:duration=first:dropout_transition=2" -b:a 192k $outputFinal

Write-Host "✅ เสร็จสิ้น! ไฟล์พร้อมใช้งาน: $outputFinal" -ForegroundColor Green
Start-Process $outputFinal
