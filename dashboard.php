<?php require_once 'includes/auth.php'; require_once 'includes/db.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Shetkari Mitra</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head><body>
<?php $pg='dashboard'; include 'includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3 data-t="topbar_dashboard"></h3><p>Welcome, <?=htmlspecialchars($admin['full_name'])?></p></div>
  </div>
  <div class="page">
    <div class="stats">
      <div class="scard"><div class="sicon g"><i class="fa fa-seedling"></i></div><div><div class="sval" id="sc">–</div><div class="slbl" data-t="lbl_active_crops"></div></div></div>
      <div class="scard"><div class="sicon o"><i class="fa fa-rupee-sign"></i></div><div><div class="sval" id="ss">–</div><div class="slbl">Crop Sales (Month)</div></div></div><div class="scard"><div class="sicon b"><i class="fa fa-tint"></i></div><div><div class="sval" id="sms">–</div><div class="slbl">Milk Sales (Month)</div></div></div>
      <div class="scard"><div class="sicon b"><i class="fa fa-tint"></i></div><div><div class="sval" id="sm">–</div><div class="slbl" data-t="lbl_milk_today"></div></div></div>
      <div class="scard"><div class="sicon g"><i class="fa fa-users"></i></div><div><div class="sval" id="scu">–</div><div class="slbl" data-t="lbl_customers"></div></div></div>
    </div>
    <div class="charts">
      <div class="ccard"><h4 data-t="lbl_monthly_sales"></h4><canvas id="cs"></canvas></div>
      <div class="ccard"><h4 data-t="lbl_monthly_milk"></h4><canvas id="cm"></canvas></div>
    </div>
    <div class="tcard">
      <div class="thead"><h4 data-t="lbl_recent_sales"></h4></div>
      <table><thead><tr><th data-t="col_product"></th><th data-t="col_customer"></th><th data-t="col_qty"></th><th data-t="col_amount"></th><th data-t="col_date"></th><th data-t="col_payment"></th></tr></thead>
      <tbody id="rb"></tbody></table>
    </div>
  </div>
</main>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="js/app.js"></script>
<script src="js/lang.js"></script>
<script>
let ci,mi;
async function load(){
  const d=await get('dashboard.php');
  document.getElementById('sc').textContent=d.crops;
  document.getElementById('ss').textContent='₹'+fmt(d.crop_sales);document.getElementById('sms').textContent='₹'+fmt(d.milk_sales);
  document.getElementById('sm').textContent=fmt(d.milk);
  document.getElementById('scu').textContent=d.customers;
  if(ci)ci.destroy();
  ci=new Chart(document.getElementById('cs'),{type:'bar',data:{labels:d.sc.map(x=>x.m),datasets:[{label:'₹',data:d.sc.map(x=>x.v),backgroundColor:'#2d5a3d',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
  if(mi)mi.destroy();
  mi=new Chart(document.getElementById('cm'),{type:'line',data:{labels:d.mc.map(x=>x.m),datasets:[{label:'L',data:d.mc.map(x=>x.v),borderColor:'#2471a3',backgroundColor:'rgba(36,113,163,.1)',fill:true,tension:.4,pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
  const payBadge=s=>s==='paid'?'g':s==='pending'?'r':'o';
  document.getElementById('rb').innerHTML=d.rs.length?d.rs.map(s=>`<tr><td><b>${s.product_name}</b></td><td>${s.cn||'Walk-in'}</td><td>${fmt(s.quantity)} ${s.unit}</td><td>₹${fmt(s.total_amount)}</td><td>${fdate(s.sale_date)}</td><td><span class="badge ${payBadge(s.payment_status)}">${s.payment_status}</span></td></tr>`).join(''):`<tr class="empty"><td colspan="6" data-t="empty_recent"></td></tr>`;
  applyLang();
}
load();
</script>
</body></html>
