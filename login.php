<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(isset($_SESSION['admin']))    { header('Location: dashboard.php'); exit; }
if(isset($_SESSION['customer'])) { header('Location: customer.php');  exit; }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Shetkari Mitra</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#1a3a2a,#2d5a3d,#3d7a52)}
.card{background:#fff;border-radius:20px;padding:40px 36px 32px;width:100%;max-width:420px;box-shadow:0 20px 56px rgba(0,0,0,.26)}
.logo{text-align:center;margin-bottom:26px}
.logo-icon{width:62px;height:62px;background:#1a3a2a;border-radius:16px;display:inline-flex;align-items:center;justify-content:center;font-size:27px;margin-bottom:10px}
.logo h1{font-family:'Playfair Display',serif;font-size:24px;color:#1a3a2a}
.logo p{color:#7a9485;font-size:12px;margin-top:3px}
.tabs{display:grid;grid-template-columns:1fr 1fr;background:#f0f4f1;border-radius:11px;padding:4px;margin-bottom:22px}
.tab{padding:9px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.tab.on{background:#1a3a2a;color:#fff}
.panel{display:none}.panel.on{display:block}
.fg{margin-bottom:14px}
.fg label{display:block;font-size:12px;font-weight:500;color:#3d5248;margin-bottom:5px}
.fg input{width:100%;padding:11px 14px;border:1.5px solid #d4e8da;border-radius:11px;font-family:'DM Sans',sans-serif;font-size:13px;outline:none;transition:border-color .2s}
.fg input:focus{border-color:#3d7a52}
.btn{width:100%;padding:13px;background:#1a3a2a;color:#fff;border:none;border-radius:11px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;margin-top:4px}
.btn:hover{background:#2d5a3d}
.hint{text-align:center;font-size:11px;color:#7a9485;margin-top:12px}
.err{display:none;background:#fdecea;color:#c0392b;font-size:12px;padding:9px 12px;border-radius:8px;margin-top:10px;text-align:center}
.info-box{background:#e8f5ee;border-radius:9px;padding:11px 13px;font-size:12px;color:#2d5a3d;margin-bottom:16px;line-height:1.6}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">🌾</div>
    <h1>Shetkari Mitra</h1>
    <p>शेतकरी मित्र — Farm Management</p>
  </div>
  <div class="tabs">
    <button class="tab on" onclick="sw('admin',this)">🔒 Farmer Login</button>
    <button class="tab"    onclick="sw('cust',this)">👤 Customer Login</button>
  </div>
  <div class="panel on" id="p-admin">
    <div class="fg"><label>Username or Mobile</label><input id="am" placeholder="Your Username or Mobile No. "></div>
    <div class="fg"><label>Password</label><input id="ap" type="password" placeholder="Password"></div>
    <button class="btn" onclick="login('admin')">Sign In</button>
    <div class="err" id="ae"></div>
  </div>
  <div class="panel" id="p-cust">
    <div class="fg"><label>Your Mobile Number or username</label><input id="cm" placeholder="Your registered mobile or Username"></div>
    <div class="fg"><label>Password</label><input id="cp" type="password" placeholder="Password"></div>
    <button class="btn" onclick="login('cust')">View My Bills</button>
    <div class="err" id="ce"></div>
  </div>
</div>
<script>
function sw(type,btn){
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('on'));
  document.querySelectorAll('.panel').forEach(p=>p.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('p-'+type).classList.add('on');
}
document.addEventListener('keydown',e=>{ if(e.key==='Enter') login(document.getElementById('p-admin').classList.contains('on')?'admin':'cust'); });
async function login(type){
  const m=document.getElementById(type==='admin'?'am':'cm').value.trim();
  const p=document.getElementById(type==='admin'?'ap':'cp').value;
  const err=document.getElementById(type==='admin'?'ae':'ce');
  err.style.display='none';
  if(!m||!p){err.textContent='Enter mobile and password.';err.style.display='block';return;}
  const fd=new FormData();
  fd.append('action',type==='admin'?'login_admin':'login_customer');
  fd.append('mobile',m);fd.append('password',p);
  const res=await fetch('api/auth.php',{method:'POST',body:fd});
  const d=await res.json();
  if(d.ok) window.location.href=d.type==='admin'?'dashboard.php':'customer.php';
  else{err.textContent=d.msg||'Login failed.';err.style.display='block';}
}
</script>
<script src="js/lang.js"></script>
</body>
</html>
