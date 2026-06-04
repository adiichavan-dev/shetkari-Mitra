<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['customer'])){header('Location: login.php');exit;}
$c=$_SESSION['customer'];
?><!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Bills — Shetkari Mitra</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#faf7f0;min-height:100vh}
.top{background:#1a3a2a;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:10px}
.logo h2{font-family:'Playfair Display',serif;color:#fff;font-size:18px}
.logo p{color:rgba(255,255,255,.45);font-size:11px}
.urow{display:flex;align-items:center;gap:10px}
.av{width:34px;height:34px;background:#c9962a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#1a3a2a;font-weight:700}
.uname{color:#fff;font-size:13px;font-weight:500}.umob{color:rgba(255,255,255,.4);font-size:11px}
.logout{color:rgba(255,255,255,.4);font-size:15px;text-decoration:none;padding:4px;transition:color .2s}
.logout:hover{color:#c0392b}
.page{padding:24px;max-width:980px;margin:0 auto}
.pg-title{margin-bottom:20px}
.pg-title h3{font-family:'Playfair Display',serif;font-size:22px;color:#1a3a2a}
.pg-title p{font-size:12px;color:#7a9485;margin-top:3px}
.tabs{display:flex;gap:0;background:#f0f4f1;border-radius:11px;padding:4px;margin-bottom:22px;width:fit-content}
.tab{padding:9px 20px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.tab.on{background:#1a3a2a;color:#fff}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
.scard{background:#fff;border-radius:12px;padding:16px;border:1px solid #d4e8da;display:flex;align-items:center;gap:12px}
.sicon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px}
.sicon.g{background:#e8f5ee;color:#3d7a52}.sicon.o{background:#f5e6c0;color:#c9962a}.sicon.r{background:#fdecea;color:#c0392b}.sicon.b{background:#eaf3fb;color:#2471a3}
.sv{font-size:20px;font-weight:700;color:#1a3a2a;line-height:1}.sl{font-size:11px;color:#7a9485;margin-top:3px}
.tcard{background:#fff;border-radius:12px;border:1px solid #d4e8da;overflow:hidden}
.thead{padding:14px 18px;border-bottom:1px solid #d4e8da;display:flex;align-items:center;justify-content:space-between}
.thead h4{font-family:'Playfair Display',serif;font-size:15px;color:#1a3a2a}
.acts-row{display:flex;gap:8px;align-items:center}
.srch{display:flex;align-items:center;gap:6px;padding:7px 12px;border:1.5px solid #d4e8da;border-radius:8px;background:#faf7f0}
.srch input{border:none;background:transparent;outline:none;font-size:13px;width:150px;font-family:'DM Sans',sans-serif}
.pbtn{display:flex;align-items:center;gap:5px;padding:8px 14px;border:1.5px solid #1a3a2a;border-radius:8px;background:#fff;color:#1a3a2a;font-size:12px;font-family:'DM Sans',sans-serif;font-weight:500;cursor:pointer;transition:all .2s}
.pbtn:hover{background:#1a3a2a;color:#fff}
table{width:100%;border-collapse:collapse}
thead th{background:#e8f5ee;padding:10px 14px;font-size:11px;font-weight:600;color:#1a3a2a;text-align:left}
tbody tr{border-bottom:1px solid #d4e8da}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fdf8ee}
tbody td{padding:11px 14px;font-size:13px;color:#3d5248}
.badge{display:inline-flex;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.g{background:#e8f5ee;color:#3d7a52}.r{background:#fdecea;color:#c0392b}.o{background:#f5e6c0;color:#8a6518}
.empty td{text-align:center;padding:36px;color:#7a9485}
.pbtn-row{display:flex;align-items:center;gap:5px;padding:7px 12px;border:1.5px solid #2471a3;border-radius:8px;background:#fff;color:#2471a3;font-size:11px;font-family:'DM Sans',sans-serif;font-weight:500;cursor:pointer;transition:all .2s}
.pbtn-row:hover{background:#2471a3;color:#fff}
@media print{.top,.pg-title,.tabs,.acts-row,.pbtn{display:none!important}.page{padding:0}.print-header{display:block!important}}
.print-header{display:none;text-align:center;padding:14px 0 10px;border-bottom:2px solid #1a3a2a;margin-bottom:14px}
.print-header h2{font-family:'Playfair Display',serif;font-size:20px;color:#1a3a2a}
.print-header p{font-size:11px;color:#555;margin-top:3px}
.overlay{position:fixed;inset:0;background:rgba(26,58,42,.45);z-index:200;display:none;align-items:center;justify-content:center;padding:16px}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:16px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.22)}
.mhead{padding:18px 22px 12px;border-bottom:1px solid #d4e8da;display:flex;align-items:center;justify-content:space-between}
.mhead h3{font-family:'Playfair Display',serif;font-size:18px;color:#1a3a2a}
.mclose{width:28px;height:28px;border-radius:7px;border:none;background:#e8f5ee;color:#1a3a2a;cursor:pointer;font-size:14px}
.mbody{padding:18px 22px}
.mfoot{padding:12px 22px;border-top:1px solid #d4e8da;display:flex;gap:8px;justify-content:flex-end}
.btn-close2{padding:9px 18px;border:1.5px solid #d4e8da;border-radius:9px;background:#fff;color:#3d5248;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif}
.btn-print2{padding:9px 20px;background:#1a3a2a;color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;display:flex;align-items:center;gap:6px}
</style>
</head><body>
<div class="top">
  <div class="logo"><span style="font-size:20px">🌾</span><div><h2>Shetkari Mitra</h2><p>शेतकरी मित्र</p></div></div>
  <div class="urow">
    <div class="av"><?=strtoupper(substr($c['full_name'],0,1))?></div>
    <div><div class="uname"><?=htmlspecialchars($c['full_name'])?></div><div class="umob"><?=htmlspecialchars($c['mobile'])?></div></div>
    <a href="logout.php" class="logout" title="Logout"><i class="fa fa-sign-out-alt"></i></a>
  </div>
</div>

<div class="page">
  <div class="pg-title"><h3>My Bills</h3><p>View and print your purchase records from the farm</p></div>

  <div class="tabs">
    <button class="tab on" onclick="switchTab('all',this)">📋 All Bills</button>
    <button class="tab" onclick="switchTab('crop',this)">🌾 Crop Bills</button>
    <button class="tab" onclick="switchTab('milk',this)">🥛 Milk Bills</button>
  </div>

  <div class="stats">
    <div class="scard"><div class="sicon g"><i class="fa fa-receipt"></i></div><div><div class="sv" id="tb">–</div><div class="sl">Total Bills</div></div></div>
    <div class="scard"><div class="sicon o"><i class="fa fa-rupee-sign"></i></div><div><div class="sv" id="ta">–</div><div class="sl">Total Amount (₹)</div></div></div>
    <div class="scard"><div class="sicon r"><i class="fa fa-clock"></i></div><div><div class="sv" id="tp">–</div><div class="sl">Pending (₹)</div></div></div>
  </div>

  <div class="tcard">
    <div class="thead">
      <h4 id="table-title">All Bills</h4>
      <div class="acts-row">
        <div class="srch"><i class="fa fa-search" style="color:#7a9485;font-size:12px"></i><input placeholder="Search bills..." oninput="filterBills(this.value)"></div>
        <button class="pbtn" onclick="window.print()"><i class="fa fa-print"></i> Print Report</button>
      </div>
    </div>
    <div class="print-header">
      <h2>Shetkari Mitra — शेतकरी मित्र</h2>
      <p id="print-subtitle">Bill Report</p>
    </div>
    <table>
      <thead><tr><th>#</th><th>Type</th><th>Product</th><th>Qty</th><th>Rate</th><th>Total (₹)</th><th>Date</th><th>Payment</th><th></th></tr></thead>
      <tbody id="bb"></tbody>
    </table>
  </div>
</div>

<div class="overlay" id="m-bill">
  <div class="modal">
    <div class="mhead"><h3>Bill Detail</h3><button class="mclose" onclick="document.getElementById('m-bill').classList.remove('open')">✕</button></div>
    <div class="mbody" id="bill-detail"></div>
    <div class="mfoot">
      <button class="btn-close2" onclick="document.getElementById('m-bill').classList.remove('open')">Close</button>
      <button class="btn-print2" onclick="printBill()"><i class="fa fa-print"></i> Print Bill</button>
    </div>
  </div>
</div>

<script>
let allBills=[],curType='all';
function fmt(n){return isNaN(n)?'0':(+n).toLocaleString('en-IN',{maximumFractionDigits:2});}
function fdate(d){return(!d||d==='0000-00-00')?'–':new Date(d).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});}
function pb(s){return s==='paid'?'g':s==='pending'?'r':'o';}

function switchTab(type,btn){
  curType=type;
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('on'));
  btn.classList.add('on');
  const titles={'all':'All Bills','crop':'🌾 Crop Bills','milk':'🥛 Milk Bills'};
  document.getElementById('table-title').textContent=titles[type];
  document.getElementById('print-subtitle').textContent=titles[type]+' | <?=htmlspecialchars($c['full_name'])?> | Printed: '+new Date().toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
  const filtered=type==='all'?allBills:allBills.filter(b=>b.bill_type===type);
  renderBills(filtered);
  updateStats(filtered);
}

async function loadBills(){
  const fd=new FormData();fd.append('action','get_customer_bills');
  const r=await fetch('api/bills.php',{method:'POST',body:fd});
  allBills=await r.json();
  renderBills(allBills);updateStats(allBills);
}

function updateStats(data){
  const total=data.reduce((s,b)=>s+parseFloat(b.total_amount||0),0);
  const pend=data.filter(b=>b.payment_status!=='paid').reduce((s,b)=>s+parseFloat(b.total_amount||0),0);
  document.getElementById('tb').textContent=data.length;
  document.getElementById('ta').textContent='₹'+fmt(total);
  document.getElementById('tp').textContent='₹'+fmt(pend);
}

function renderBills(data){
  document.getElementById('bb').innerHTML=data.length?data.map((b,i)=>`<tr>
    <td>${i+1}</td>
    <td>${b.bill_type==='milk'?'<span style="background:#eaf3fb;color:#2471a3;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">🥛 Milk</span>':'<span style="background:#e8f5ee;color:#2d5a3d;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600">🌾 Crop</span>'}</td>
    <td><b>${b.product_name}</b></td>
    <td>${fmt(b.quantity)} ${b.unit}</td>
    <td>₹${fmt(b.price_per_unit)}</td>
    <td><b>₹${fmt(b.total_amount)}</b></td>
    <td>${fdate(b.sale_date)}</td>
    <td><span class="badge ${pb(b.payment_status)}">${b.payment_status}</span></td>
    <td><button class="pbtn-row" onclick="viewBill(${b.id})"><i class="fa fa-print"></i> Bill</button></td>
  </tr>`).join(''):'<tr class="empty"><td colspan="9">No bills found.</td></tr>';
}

function filterBills(q){
  const filtered=(curType==='all'?allBills:allBills.filter(b=>b.bill_type===curType))
    .filter(b=>JSON.stringify(b).toLowerCase().includes(q.toLowerCase()));
  renderBills(filtered);updateStats(filtered);
}

async function viewBill(id){
  const fd=new FormData();fd.append('action','get_bill_detail');fd.append('id',id);
  const res=await fetch('api/bills.php',{method:'POST',body:fd});
  const r=await res.json();
  const isMilk=r.bill_type==='milk';
  document.getElementById('bill-detail').innerHTML=`
    <div style="text-align:center;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #1a3a2a">
      <div style="font-size:26px">🌾</div>
      <div style="font-family:'Playfair Display',serif;font-size:20px;color:#1a3a2a">Shetkari Mitra</div>
      <div style="font-size:11px;color:#7a9485">शेतकरी मित्र — ${isMilk?'Milk Supply Bill':'Crop Sale Bill'}</div>
    </div>
    <table style="width:100%;border-collapse:collapse;margin-bottom:14px">
      <tr><td style="padding:5px 0;font-size:13px;color:#7a9485;width:40%">Bill No.</td><td style="font-size:13px;font-weight:600">#${String(r.id).padStart(4,'0')}</td></tr>
      <tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Date</td><td style="font-size:13px">${fdate(r.sale_date)}</td></tr>
      <tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Customer</td><td style="font-size:13px;font-weight:600"><?=htmlspecialchars($c['full_name'])?></td></tr>
      <tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Mobile</td><td style="font-size:13px"><?=htmlspecialchars($c['mobile'])?></td></tr>
      ${r.market_name&&!isMilk?`<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Market</td><td style="font-size:13px">${r.market_name}</td></tr>`:''}
    </table>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="background:${isMilk?'#eaf3fb':'#e8f5ee'}">
        <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:left">Description</th>
        <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Qty</th>
        <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Rate</th>
        <th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Total</th>
      </tr></thead>
      <tbody><tr style="border-bottom:1px solid #d4e8da">
        <td style="padding:10px 12px;font-size:13px"><b>${r.product_name}</b></td>
        <td style="padding:10px 12px;font-size:13px;text-align:right">${fmt(r.quantity)} ${r.unit}</td>
        <td style="padding:10px 12px;font-size:13px;text-align:right">₹${fmt(r.price_per_unit)}</td>
        <td style="padding:10px 12px;font-size:13px;text-align:right;font-weight:700">₹${fmt(r.total_amount)}</td>
      </tr></tbody>
      <tfoot><tr style="background:#faf7f0">
        <td colspan="3" style="padding:10px 12px;font-size:13px;font-weight:600">Total Amount</td>
        <td style="padding:10px 12px;font-size:16px;font-weight:700;color:#1a3a2a;text-align:right">₹${fmt(r.total_amount)}</td>
      </tr></tfoot>
    </table>
    <div style="margin-top:12px;padding:10px 12px;background:${r.payment_status==='paid'?'#e8f5ee':r.payment_status==='pending'?'#fdecea':'#f5e6c0'};border-radius:8px;font-size:13px;font-weight:600;color:${r.payment_status==='paid'?'#2d5a3d':r.payment_status==='pending'?'#c0392b':'#8a6518'}">
      Payment: ${r.payment_status.toUpperCase()}
    </div>
    <div style="margin-top:18px;text-align:center;font-size:11px;color:#7a9485;border-top:1px solid #d4e8da;padding-top:10px">Thank you! — Shetkari Mitra &nbsp;|&nbsp; शेतकरी मित्र</div>`;
  document.getElementById('m-bill').classList.add('open');
}

function printBill(){
  const w=window.open('','_blank','width=600,height=700');
  w.document.write(`<!DOCTYPE html><html><head><title>Bill</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:'DM Sans',sans-serif;padding:28px;max-width:500px;margin:0 auto}</style></head><body>${document.getElementById('bill-detail').innerHTML}<script>window.onload=()=>{window.print();window.close();}<\/script></body></html>`);
  w.document.close();
}

loadBills();
</script>
</body></html>
