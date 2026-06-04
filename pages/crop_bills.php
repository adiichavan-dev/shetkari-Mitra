<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$custs=[];$r=$conn->query("SELECT id,full_name,mobile FROM customers WHERE category='crop' OR category='both' ORDER BY full_name");
while($row=$r->fetch_assoc())$custs[]=$row; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Crop Sales — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.pay-popup{position:fixed;inset:0;background:rgba(26,58,42,.45);z-index:300;display:none;align-items:center;justify-content:center;padding:16px}
.pay-popup.open{display:flex}
.pay-popup-box{background:#fff;border-radius:16px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.22);overflow:hidden}
.pay-popup-head{padding:16px 20px;border-bottom:1px solid #d4e8da;background:#1a3a2a;color:#fff}
.pay-popup-head h3{font-size:16px;font-family:'Playfair Display',serif}
.pay-popup-head p{font-size:11px;opacity:.7;margin-top:3px}
.pay-popup-body{padding:18px 20px}
.pay-mode-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.pay-mode-btn{display:flex;flex-direction:column;align-items:center;gap:5px;padding:14px;border:2px solid #d4e8da;border-radius:12px;cursor:pointer;background:#fff;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;color:#3d5248;transition:all .2s}
.pay-mode-btn:hover{border-color:#1a3a2a;background:#e8f5ee;color:#1a3a2a}
.pay-mode-btn.selected{border-color:#1a3a2a;background:#1a3a2a;color:#fff}
.pay-mode-btn span{font-size:22px}
.pay-popup-foot{padding:12px 20px;border-top:1px solid #d4e8da;display:flex;gap:8px;justify-content:flex-end}
</style>
</head><body>
<?php $pg='crop_bills'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3>&#x1F33E; Crop Sales &amp; Bills</h3><p>All crop produce sold in market</p></div>
    <div style="display:flex;gap:8px">
      <button class="print-btn" onclick="window.print()"><i class="fa fa-print"></i> Print Report</button>
      <button class="btn-add" onclick="openBill()"><i class="fa fa-plus"></i> Add Sale</button>
    </div>
  </div>
  <div class="page">
    <div class="print-header">
      <h2>Shetkari Mitra &#x2014; &#x936;&#x947;&#x924;&#x915;&#x930;&#x940; &#x92E;&#x93F;&#x924;&#x94D;&#x930;</h2>
      <p>Crop Sales Report &nbsp;|&nbsp; Printed: <?=date('d M Y')?></p>
    </div>
    <div class="stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
      <div class="scard"><div class="sicon g"><i class="fa fa-rupee-sign"></i></div><div><div class="sval" id="st">&#x2013;</div><div class="slbl">Total Sales (&#x20B9;)</div></div></div>
      <div class="scard"><div class="sicon o"><i class="fa fa-clock"></i></div><div><div class="sval" id="sp">&#x2013;</div><div class="slbl">Pending (&#x20B9;)</div></div></div>
      <div class="scard"><div class="sicon g"><i class="fa fa-check-circle"></i></div><div><div class="sval" id="spd">&#x2013;</div><div class="slbl">Paid (&#x20B9;)</div></div></div>
    </div>
    <div class="tcard">
      <div class="thead">
        <h4>Crop Sales Records</h4>
        <div style="display:flex;gap:8px;align-items:center">
          <div class="srch"><i class="fa fa-search"></i><input placeholder="Search..." oninput="filter('cb',this.value)"></div>
        </div>
      </div>
      <table>
        <thead><tr><th>#</th><th>Product</th><th>Customer</th><th>Qty</th><th>Price/Unit</th><th>Total (&#x20B9;)</th><th>Market</th><th>Date</th><th>Payment</th><th></th></tr></thead>
        <tbody id="cb"></tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="m-bill">
  <div class="modal">
    <div class="mhead"><h3 id="bt">Add Crop Sale</h3><button class="mclose" onclick="closeM('bill')">&#x2715;</button></div>
    <div class="mbody">
      <input type="hidden" id="bid">
      <div class="row2">
        <div class="fg"><label>Product Name</label><input id="bp" placeholder="e.g. Tomato, Onion"></div>
        <div class="fg"><label>Customer</label>
          <select id="bc"><option value="">Walk-in</option><?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?> (<?=$c['mobile']?>)</option><?php endforeach;?></select>
        </div>
      </div>
      <div class="row2">
        <div class="fg"><label>Quantity</label><input id="bq" type="number" step="0.1" placeholder="0"></div>
        <div class="fg"><label>Unit</label>
          <select id="bu"><option value="kg">kg</option><option value="quintal">quintal</option><option value="piece">piece</option><option value="dozen">dozen</option></select>
        </div>
      </div>
      <div class="row2">
        <div class="fg"><label>Price per Unit (&#x20B9;)</label><input id="bpr" type="number" step="0.01" placeholder="0.00"></div>
        <div class="fg"><label>Sale Date</label><input id="bsd" type="date"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Market Name</label><input id="bmn" placeholder="e.g. Nashik APMC"></div>
        <div class="fg"><label>Market Location</label><input id="bml" placeholder="City / Area"></div>
      </div>
      <div class="fg"><label>Payment Status</label>
        <select id="bps"><option value="paid">Paid</option><option value="pending">Pending</option><option value="partial">Partial</option></select>
      </div>
      <div class="fg"><label>Notes</label><textarea id="bno" rows="2" placeholder="Notes"></textarea></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('bill')">Cancel</button>
      <button class="btn-save" onclick="saveBill()">Save Sale</button>
    </div>
  </div>
</div>

<div class="overlay" id="m-print">
  <div class="modal" style="max-width:580px">
    <div class="mhead"><h3>Bill / Invoice</h3><button class="mclose" onclick="closeM('print')">&#x2715;</button></div>
    <div class="mbody" id="bill-print-area"></div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('print')">Close</button>
      <button class="btn-save" onclick="printBill()"><i class="fa fa-print"></i> Print Bill</button>
    </div>
  </div>
</div>

<div class="pay-popup" id="pay-popup">
  <div class="pay-popup-box">
    <div class="pay-popup-head"><h3>&#x1F4B0; Record Payment</h3><p id="pay-popup-label">Select payment method</p></div>
    <div class="pay-popup-body">
      <div class="pay-mode-grid">
        <button class="pay-mode-btn" onclick="selectPayMode('cash')"><span>&#x1F4B5;</span>Cash</button>
        <button class="pay-mode-btn" onclick="selectPayMode('upi')"><span>&#x1F4F1;</span>UPI</button>
        <button class="pay-mode-btn" onclick="selectPayMode('online')"><span>&#x1F3E6;</span>Online Transfer</button>
        <button class="pay-mode-btn" onclick="selectPayMode('cheque')"><span>&#x1F4DD;</span>Cheque</button>
      </div>
      <div id="pay-ref-row" style="display:none">
        <label style="font-size:12px;font-weight:500;color:#3d5248;display:block;margin-bottom:5px">Reference No. (optional)</label>
        <input id="pay-popup-ref" placeholder="UPI ID / Txn ID / Cheque No." style="width:100%;padding:9px 12px;border:1.5px solid #d4e8da;border-radius:9px;font-size:13px;outline:none;font-family:'DM Sans',sans-serif">
      </div>
    </div>
    <div class="pay-popup-foot">
      <button class="btn-cancel" onclick="closePayPopup()">Cancel</button>
      <button class="btn-save" id="pay-popup-confirm" onclick="confirmQuickPay()" disabled style="opacity:.5">Confirm Payment</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
var pb=function(s){return s==='paid'?'g':s==='pending'?'r':'o';};

async function loadBills(){
  var raw=await post('bills.php',{action:'get_bills',bill_type:'crop'});
  var d=Array.isArray(raw)?raw:[];
  var total=d.reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var pending=d.filter(function(b){return b.payment_status!=='paid';}).reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var paid=d.filter(function(b){return b.payment_status==='paid';}).reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  document.getElementById('st').textContent='\u20B9'+fmt(total);
  document.getElementById('sp').textContent='\u20B9'+fmt(pending);
  document.getElementById('spd').textContent='\u20B9'+fmt(paid);
  if(!d.length){document.getElementById('cb').innerHTML='<tr class="empty"><td colspan="10">No crop sales records yet</td></tr>';return;}
  document.getElementById('cb').innerHTML=d.map(function(b,i){
    var payBtn=b.payment_status!=='paid'
      ?'<button class="pay-mode-btn" style="padding:5px 10px;flex-direction:row;gap:4px;font-size:11px;background:#fdecea;color:#c0392b;border-color:#f5c6c6;border-radius:7px" onclick="openPayPopup('+b.id+',\''+String(b.cn||'').replace(/\'/g,'')+'\',' +b.total_amount+')"><span style=\'font-size:13px\'>\u20B9</span>Pay</button>'
      :'<span style="font-size:11px;color:#3d7a52;font-weight:600">\u2713 Paid</span>';
    return '<tr>'
      +'<td>'+(i+1)+'</td><td><b>'+b.product_name+'</b></td><td>'+(b.cn||'Walk-in')+'</td>'
      +'<td>'+fmt(b.quantity)+' '+b.unit+'</td><td>\u20B9'+fmt(b.price_per_unit)+'</td>'
      +'<td><b>\u20B9'+fmt(b.total_amount)+'</b></td><td>'+(b.market_name||'\u2013')+'</td>'
      +'<td>'+fdate(b.sale_date)+'</td>'
      +'<td><span class="badge '+pb(b.payment_status)+'">'+b.payment_status+'</span></td>'
      +'<td><div class="acts">'
      +'<button class="abtn b" onclick="viewBill('+b.id+')" style="background:#e8f5ee;color:#2d5a3d" title="Print Bill"><i class="fa fa-print"></i></button>'
      +payBtn
      +'<button class="abtn e" onclick=\'editBill('+JSON.stringify(b)+')\' ><i class="fa fa-pen"></i></button>'
      +'<button class="abtn d" onclick="delBill('+b.id+')"><i class="fa fa-trash"></i></button>'
      +'</div></td></tr>';
  }).join('');
}

function openBill(d){
  d=d||null;
  document.getElementById('bid').value=d?d.id:'';
  document.getElementById('bp').value=d?d.product_name:'';
  document.getElementById('bc').value=d?d.customer_id||'':'';
  document.getElementById('bq').value=d?d.quantity:'';
  document.getElementById('bu').value=d?d.unit:'kg';
  document.getElementById('bpr').value=d?d.price_per_unit:'';
  document.getElementById('bsd').value=d?d.sale_date:today();
  document.getElementById('bmn').value=d?d.market_name||'':'';
  document.getElementById('bml').value=d?d.market_location||'':'';
  document.getElementById('bps').value=d?d.payment_status:'paid';
  document.getElementById('bno').value=d?d.notes||'':'';
  document.getElementById('bt').textContent=d?'Edit Crop Sale':'Add Crop Sale';
  openM('bill');
}
function editBill(d){openBill(d);}

async function saveBill(){
  var r=await post('bills.php',{action:'save_bill',bill_type:'crop',
    id:document.getElementById('bid').value,
    product_name:document.getElementById('bp').value,
    customer_id:document.getElementById('bc').value,
    quantity:document.getElementById('bq').value,
    unit:document.getElementById('bu').value,
    price_per_unit:document.getElementById('bpr').value,
    sale_date:document.getElementById('bsd').value,
    market_name:document.getElementById('bmn').value,
    market_location:document.getElementById('bml').value,
    payment_status:document.getElementById('bps').value,
    notes:document.getElementById('bno').value});
  if(r.ok){showToast('Sale saved!');closeM('bill');loadBills();}else showToast(r.msg,true);
}

async function delBill(id){
  if(!confirm('Delete this sale record?'))return;
  var r=await post('bills.php',{action:'delete_bill',id:id});
  if(r.ok){showToast('Deleted!');loadBills();}
}

async function viewBill(id){
  var r=await post('bills.php',{action:'get_bill_detail',id:id});
  if(!r||!r.id){showToast('Could not load bill',true);return;}
  var statusBg=r.payment_status==='paid'?'#e8f5ee':r.payment_status==='pending'?'#fdecea':'#f5e6c0';
  var statusTx=r.payment_status==='paid'?'#2d5a3d':r.payment_status==='pending'?'#c0392b':'#8a6518';
  document.getElementById('bill-print-area').innerHTML=
    '<div style="text-align:center;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #1a3a2a">'
    +'<div style="font-size:28px">\uD83C\uDF3E</div>'
    +'<div style="font-family:\'Playfair Display\',serif;font-size:20px;color:#1a3a2a">Shetkari Mitra</div>'
    +'<div style="font-size:11px;color:#7a9485">\u0936\u0947\u0924\u0915\u0930\u0940 \u092E\u093F\u0924\u094D\u0930 \u2014 Crop Sale Bill</div>'
    +'</div>'
    +'<table style="width:100%;border-collapse:collapse;margin-bottom:14px">'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485;width:45%">Bill No.</td><td style="font-size:13px;font-weight:600">#'+String(r.id).padStart(4,'0')+'</td></tr>'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Date</td><td style="font-size:13px">'+fdate(r.sale_date)+'</td></tr>'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Customer</td><td style="font-size:13px;font-weight:600">'+(r.cn||'Walk-in Customer')+'</td></tr>'
    +(r.cm?'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Mobile</td><td style="font-size:13px">'+r.cm+'</td></tr>':'')
    +(r.ca?'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Address</td><td style="font-size:13px">'+r.ca+'</td></tr>':'')
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Market</td><td style="font-size:13px">'+(r.market_name||'\u2013')+(r.market_location?' ('+r.market_location+')':'')+'</td></tr>'
    +'</table>'
    +'<table style="width:100%;border-collapse:collapse">'
    +'<thead><tr style="background:#e8f5ee">'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:left">Product</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Qty</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Rate</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Total</th>'
    +'</tr></thead>'
    +'<tbody><tr style="border-bottom:1px solid #d4e8da">'
    +'<td style="padding:10px 12px;font-size:13px"><b>'+r.product_name+'</b></td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right">'+fmt(r.quantity)+' '+r.unit+'</td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right">\u20B9'+fmt(r.price_per_unit)+'</td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right;font-weight:700">\u20B9'+fmt(r.total_amount)+'</td>'
    +'</tr></tbody>'
    +'<tfoot><tr style="background:#faf7f0">'
    +'<td colspan="3" style="padding:10px 12px;font-size:13px;font-weight:600">Total Amount</td>'
    +'<td style="padding:10px 12px;font-size:16px;font-weight:700;color:#1a3a2a;text-align:right">\u20B9'+fmt(r.total_amount)+'</td>'
    +'</tr></tfoot></table>'
    +'<div style="margin-top:12px;padding:10px 12px;background:'+statusBg+';border-radius:8px;font-size:13px;font-weight:600;color:'+statusTx+'">Payment Status: '+r.payment_status.toUpperCase()+'</div>'
    +(r.notes?'<div style="margin-top:10px;font-size:12px;color:#7a9485">Notes: '+r.notes+'</div>':'')
    +'<div style="margin-top:20px;text-align:center;font-size:11px;color:#7a9485;border-top:1px solid #d4e8da;padding-top:10px">Thank you for your business! \u2014 Shetkari Mitra</div>';
  openM('print');
}

function printBill(){
  var content=document.getElementById('bill-print-area').innerHTML;
  var w=window.open('','_blank','width=600,height=700');
  w.document.write('<!DOCTYPE html><html><head><title>Crop Bill</title>'
    +'<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">'
    +'<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:\'DM Sans\',sans-serif;padding:28px;max-width:500px;margin:0 auto}</style>'
    +'</head><body>'+content+'</body></html>');
  w.document.close();
  w.focus();
  setTimeout(function(){w.print();},500);
}

var _payBillId=null,_payMode=null,_payAmt=0;
function openPayPopup(billId,custName,amount){
  _payBillId=billId;_payMode=null;_payAmt=amount;
  document.getElementById('pay-popup-label').textContent=(custName?custName+' \u2014 ':'')+' \u20B9'+fmt(amount);
  document.querySelectorAll('.pay-mode-btn').forEach(function(b){b.classList.remove('selected');});
  document.getElementById('pay-ref-row').style.display='none';
  document.getElementById('pay-popup-ref').value='';
  document.getElementById('pay-popup-confirm').disabled=true;
  document.getElementById('pay-popup-confirm').style.opacity='.5';
  document.getElementById('pay-popup').classList.add('open');
}
function selectPayMode(mode){
  _payMode=mode;
  document.querySelectorAll('.pay-mode-btn').forEach(function(b){b.classList.remove('selected');});
  event.currentTarget.classList.add('selected');
  document.getElementById('pay-ref-row').style.display=mode!=='cash'?'block':'none';
  document.getElementById('pay-popup-confirm').disabled=false;
  document.getElementById('pay-popup-confirm').style.opacity='1';
}
function closePayPopup(){document.getElementById('pay-popup').classList.remove('open');}
async function confirmQuickPay(){
  if(!_payBillId||!_payMode)return;
  var ref=document.getElementById('pay-popup-ref').value.trim();
  var r1=await post('bills.php',{action:'quick_pay',id:_payBillId});
  if(!r1.ok){showToast(r1.msg||'Error',true);return;}
  await post('payments.php',{action:'save_payment',bill_id:_payBillId,amount:_payAmt,payment_mode:_payMode,payment_date:today(),reference_no:ref,notes:'Recorded from Crop Sales page'});
  closePayPopup();
  var labels={cash:'\uD83D\uDCB5 Cash',upi:'\uD83D\uDCF1 UPI',online:'\uD83C\uDFE6 Online',cheque:'\uD83D\uDCDD Cheque'};
  showToast('\u2705 Payment recorded \u2014 '+labels[_payMode]);
  loadBills();
}
document.getElementById('pay-popup').addEventListener('click',function(e){if(e.target===this)closePayPopup();});

loadBills();
</script>
</body></html>
