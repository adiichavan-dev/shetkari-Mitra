<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$custs=[];$r=$conn->query("SELECT id,full_name,mobile FROM customers WHERE category='milk' OR category='both' ORDER BY full_name");
while($row=$r->fetch_assoc())$custs[]=$row; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Milk Bills — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.month-bar{display:flex;align-items:center;gap:10px;background:#fff;padding:14px 20px;border-radius:var(--r);border:1px solid var(--bd);margin-bottom:18px;flex-wrap:wrap}
.month-bar label{font-size:12px;font-weight:600;color:var(--text-mid)}
.month-select{padding:8px 14px;border:1.5px solid var(--bd);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:var(--white);color:var(--text-dark)}
.month-select:focus{border-color:var(--gm)}
.month-badge{padding:5px 12px;background:var(--gl);color:var(--gm);border-radius:20px;font-size:12px;font-weight:600}
.reset-btn{display:flex;align-items:center;gap:5px;padding:8px 14px;border:1.5px solid var(--rd);border-radius:8px;background:#fff;color:var(--rd);font-size:12px;font-family:'DM Sans',sans-serif;font-weight:500;cursor:pointer;transition:all .2s}
.reset-btn:hover{background:var(--rd);color:#fff}
.summary-month{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
.pay-popup{position:fixed;inset:0;background:rgba(26,58,42,.45);z-index:300;display:none;align-items:center;justify-content:center;padding:16px}
.pay-popup.open{display:flex}
.pay-popup-box{background:#fff;border-radius:16px;width:100%;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.22);overflow:hidden}
.pay-popup-head{padding:16px 20px;border-bottom:1px solid #d4e8da;background:#2471a3;color:#fff}
.pay-popup-head h3{font-size:16px;font-family:'Playfair Display',serif}
.pay-popup-head p{font-size:11px;opacity:.7;margin-top:3px}
.pay-popup-body{padding:18px 20px}
.pay-mode-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.pay-mode-btn{display:flex;flex-direction:column;align-items:center;gap:5px;padding:14px;border:2px solid #d4e8da;border-radius:12px;cursor:pointer;background:#fff;font-family:'DM Sans',sans-serif;font-size:12px;font-weight:600;color:#3d5248;transition:all .2s}
.pay-mode-btn:hover{border-color:#2471a3;background:#eaf3fb;color:#2471a3}
.pay-mode-btn.selected{border-color:#2471a3;background:#2471a3;color:#fff}
.pay-mode-btn span{font-size:22px}
.pay-popup-foot{padding:12px 20px;border-top:1px solid #d4e8da;display:flex;gap:8px;justify-content:flex-end}
</style>
</head><body>
<?php $pg='milk_bills'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3>&#x1F95B; Milk Bills &amp; Supply</h3><p data-t="sub_milk_bills"></p></div>
    <div style="display:flex;gap:8px">
      <button class="print-btn" onclick="window.print()"><i class="fa fa-print"></i> <span data-t="btn_print"></span></button>
      <button class="btn-add" onclick="openBill()"><i class="fa fa-plus"></i> <span data-t="lbl_add_manual"></span></button>
    </div>
  </div>
  <div class="page">
    <div style="background:#eaf3fb;border-radius:10px;padding:12px 16px;font-size:13px;color:#2471a3;margin-bottom:16px;display:flex;align-items:center;gap:8px">
      <i class="fa fa-info-circle"></i><span data-t="lbl_auto_note"></span>
    </div>
    <div class="month-bar">
      <label>&#x1F4C5; Select Month:</label>
      <input type="month" id="sel-month" class="month-select" onchange="loadBills()">
      <span id="month-label" class="month-badge"></span>
      <div style="flex:1"></div>
      <select id="filter-cust" class="month-select" onchange="loadBills()">
        <option value="" data-t="lbl_all_customers_opt"></option>
        <?php foreach($custs as $c):?>
        <option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?> (<?=$c['mobile']?>)</option>
        <?php endforeach;?>
      </select>
      <button onclick="openSettle()" style="display:flex;align-items:center;gap:6px;padding:8px 14px;background:#2d5a3d;color:#fff;border:none;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif">
        <i class="fa fa-check-double"></i> <span data-t="lbl_monthly_settle"></span>
      </button>
      <button class="reset-btn" onclick="openResetCash()"><i class="fa fa-sync-alt"></i> Reset Cash Flow</button>
    </div>
    <div class="summary-month">
      <div class="scard"><div class="sicon b"><i class="fa fa-rupee-sign"></i></div><div><div class="sval" id="mst">&#x2013;</div><div class="slbl" data-t="lbl_total_billed"></div></div></div>
      <div class="scard"><div class="sicon g"><i class="fa fa-check-circle"></i></div><div><div class="sval" id="mspd">&#x2013;</div><div class="slbl" data-t="lbl_paid"></div></div></div>
      <div class="scard"><div class="sicon" style="background:#fdecea;color:#c0392b"><i class="fa fa-clock"></i></div><div><div class="sval" id="msp">&#x2013;</div><div class="slbl" data-t="lbl_pending"></div></div></div>
      <div class="scard"><div class="sicon o"><i class="fa fa-tint"></i></div><div><div class="sval" id="msl">&#x2013;</div><div class="slbl">Total Litres</div></div></div>
    </div>
    <div class="tcard">
      <div class="thead">
        <h4>Milk Supply Bills</h4>
        <div class="srch"><i class="fa fa-search"></i><input placeholder="Search..." oninput="filter('mb',this.value)"></div>
      </div>
      <div class="print-header">
        <h2>Shetkari Mitra &#x2014; &#x936;&#x947;&#x924;&#x915;&#x930;&#x940; &#x92E;&#x93F;&#x924;&#x94D;&#x930;</h2>
        <p id="print-sub">Milk Supply Report</p>
      </div>
      <table>
        <thead><tr><th>#</th><th data-t="col_date"></th><th data-t="col_customer"></th><th>Litres</th><th>Rate (&#x20B9;/L)</th><th>Total (&#x20B9;)</th><th data-t="col_payment"></th><th></th></tr></thead>
        <tbody id="mb"></tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="m-bill">
  <div class="modal">
    <div class="mhead"><h3 id="mbt">Add Milk Bill</h3><button class="mclose" onclick="closeM('bill')">&#x2715;</button></div>
    <div class="mbody">
      <input type="hidden" id="mbid">
      <div class="row2">
        <div class="fg"><label data-t="col_date"></label><input id="mbd" type="date"></div>
        <div class="fg"><label data-t="col_customer"></label>
          <select id="mbc"><option value="">Select customer</option>
          <?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?> (<?=$c['mobile']?>)</option><?php endforeach;?></select>
        </div>
      </div>
      <div class="row2">
        <div class="fg"><label>Quantity (Litres)</label><input id="mbq" type="number" step="0.1" placeholder="0.0"></div>
        <div class="fg"><label>Rate (&#x20B9;/litre)</label><input id="mbr" type="number" step="0.5" placeholder="45"></div>
      </div>
      <div class="fg"><label data-t="col_payment"></label>
        <select id="mbps"><option value="pending">Pending</option><option value="paid">Paid</option><option value="partial">Partial</option></select>
      </div>
      <div class="fg"><label data-t="lbl_notes"></label><textarea id="mbno" rows="2" placeholder="Notes"></textarea></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('bill')" data-t="btn_cancel"></button>
      <button class="btn-save" onclick="saveBill()" data-t="btn_save"></button>
    </div>
  </div>
</div>

<div class="overlay" id="m-settle">
  <div class="modal" style="max-width:520px">
    <div class="mhead"><h3 data-t="lbl_monthly_settle"></h3><button class="mclose" onclick="closeM('settle')">&#x2715;</button></div>
    <div class="mbody">
      <div class="row2">
        <div class="fg"><label data-t="lbl_settle_customer"></label>
          <select id="settle-cust"><option value="" data-t="lbl_all_customers_opt"></option>
          <?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?></option><?php endforeach;?></select>
        </div>
        <div class="fg"><label data-t="lbl_month"></label><input id="settle-month" type="month"></div>
      </div>
      <div id="settle-preview"></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('settle')" data-t="btn_cancel"></button>
      <button class="btn-save" style="background:#2471a3" onclick="previewSettle()" data-t="btn_preview"></button>
      <button class="btn-save" id="confirm-settle-btn" onclick="confirmSettle()" style="display:none" data-t="btn_confirm_settle"></button>
    </div>
  </div>
</div>

<div class="overlay" id="m-reset">
  <div class="modal" style="max-width:480px">
    <div class="mhead"><h3>&#x1F504; Reset Monthly Cash Flow</h3><button class="mclose" onclick="closeM('reset')">&#x2715;</button></div>
    <div class="mbody">
      <div style="background:#fdecea;border-radius:10px;padding:14px;margin-bottom:16px;font-size:13px;color:#c0392b">
        <i class="fa fa-exclamation-triangle"></i> <b>Warning:</b> This marks all bills for the month as <b>Paid</b>. Records are preserved.
      </div>
      <div class="row2">
        <div class="fg"><label>Month</label><input id="reset-month" type="month"></div>
        <div class="fg"><label>Customer (optional)</label>
          <select id="reset-cust"><option value="">All Customers</option>
          <?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?></option><?php endforeach;?></select>
        </div>
      </div>
      <div id="reset-preview" style="margin-top:8px"></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('reset')">Cancel</button>
      <button class="btn-save" style="background:#2471a3" onclick="previewReset()">Preview</button>
      <button class="btn-save" id="confirm-reset-btn" onclick="confirmReset()" style="display:none;background:#c0392b">&#x2705; Reset &amp; Mark Paid</button>
    </div>
  </div>
</div>

<div class="overlay" id="m-print">
  <div class="modal" style="max-width:560px">
    <div class="mhead"><h3>Milk Supply Bill</h3><button class="mclose" onclick="closeM('print')">&#x2715;</button></div>
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
const pb = s => s==='paid'?'g':s==='pending'?'r':'o';

document.addEventListener('DOMContentLoaded',function(){
  var now = new Date();
  var m = now.getFullYear()+'-'+String(now.getMonth()+1).padStart(2,'0');
  document.getElementById('sel-month').value = m;
  document.getElementById('settle-month').value = m;
  document.getElementById('reset-month').value = m;
  updateMonthLabel(m);
  loadBills();
});

function updateMonthLabel(m){
  if(!m)return;
  var d = new Date(m+'-01');
  var lbl = d.toLocaleDateString('en-IN',{month:'long',year:'numeric'});
  document.getElementById('month-label').textContent = lbl;
  document.getElementById('print-sub').textContent = 'Milk Supply Report — '+lbl;
}

async function loadBills(){
  var month = document.getElementById('sel-month').value;
  var cust  = document.getElementById('filter-cust').value;
  updateMonthLabel(month);
  var raw = await post('bills.php',{action:'get_bills',bill_type:'milk',month:month,customer_id:cust});
  var d = Array.isArray(raw) ? raw : [];
  var total   = d.reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var paid    = d.filter(function(b){return b.payment_status==='paid';}).reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var pending = d.filter(function(b){return b.payment_status!=='paid';}).reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var litres  = d.reduce(function(s,b){return s+parseFloat(b.quantity||0);},0);
  document.getElementById('mst').textContent  = '\u20B9'+fmt(total);
  document.getElementById('mspd').textContent = '\u20B9'+fmt(paid);
  document.getElementById('msp').textContent  = '\u20B9'+fmt(pending);
  document.getElementById('msl').textContent  = fmt(litres)+' L';
  if(!d.length){
    document.getElementById('mb').innerHTML = '<tr class="empty"><td colspan="8">No milk bills for this month</td></tr>';
    return;
  }
  document.getElementById('mb').innerHTML = d.map(function(b,i){
    var payBtn = b.payment_status!=='paid'
      ? '<button style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;border-radius:7px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:\'DM Sans\',sans-serif;background:#fdecea;color:#c0392b;" onclick="openPayPopup('+b.id+',\''+String(b.cn||'').replace(/\'/g,'')+'\',' +b.total_amount+')"><i class="fa fa-rupee-sign"></i> Pay</button>'
      : '<span style="font-size:11px;color:#3d7a52;font-weight:600">\u2713 Paid</span>';
    return '<tr>'
      +'<td>'+(i+1)+'</td><td>'+fdate(b.sale_date)+'</td><td><b>'+(b.cn||'\u2013')+'</b></td>'
      +'<td>'+fmt(b.quantity)+' L</td><td>\u20B9'+fmt(b.price_per_unit)+'/L</td>'
      +'<td><b>\u20B9'+fmt(b.total_amount)+'</b></td>'
      +'<td><span class="badge '+pb(b.payment_status)+'">'+b.payment_status+'</span></td>'
      +'<td><div class="acts">'
      +'<button class="abtn" onclick="viewBill('+b.id+')" style="width:28px;height:28px;border-radius:7px;border:none;cursor:pointer;background:#eaf3fb;color:#2471a3;font-size:12px;display:flex;align-items:center;justify-content:center"><i class="fa fa-print"></i></button>'
      +payBtn
      +'<button class="abtn e" onclick=\'editBill('+JSON.stringify(b)+')\' ><i class="fa fa-pen"></i></button>'
      +'<button class="abtn d" onclick="delBill('+b.id+')"><i class="fa fa-trash"></i></button>'
      +'</div></td></tr>';
  }).join('');
}

function openBill(d){
  d=d||null;
  document.getElementById('mbid').value = d?d.id:'';
  document.getElementById('mbd').value  = d?d.sale_date:today();
  document.getElementById('mbc').value  = d?d.customer_id||'':'';
  document.getElementById('mbq').value  = d?d.quantity:'';
  document.getElementById('mbr').value  = d?d.price_per_unit:'';
  document.getElementById('mbps').value = d?d.payment_status:'pending';
  document.getElementById('mbno').value = d?d.notes||'':'';
  document.getElementById('mbt').textContent = d?'Edit Milk Bill':'Add Milk Bill';
  openM('bill');
}
function editBill(d){openBill(d);}

async function saveBill(){
  var r = await post('bills.php',{action:'save_bill',bill_type:'milk',
    id:document.getElementById('mbid').value, product_name:'Milk',
    customer_id:document.getElementById('mbc').value,
    quantity:document.getElementById('mbq').value, unit:'litre',
    price_per_unit:document.getElementById('mbr').value,
    sale_date:document.getElementById('mbd').value,
    payment_status:document.getElementById('mbps').value,
    notes:document.getElementById('mbno').value});
  if(r.ok){showToast('Bill saved!');closeM('bill');loadBills();}
  else showToast(r.msg,true);
}

async function delBill(id){
  if(!confirm(t('del_confirm')))return;
  var r = await post('bills.php',{action:'delete_bill',id:id});
  if(r.ok){showToast(t('toast_deleted'));loadBills();}
}

function openSettle(){
  document.getElementById('settle-month').value = document.getElementById('sel-month').value;
  document.getElementById('settle-preview').innerHTML='';
  document.getElementById('confirm-settle-btn').style.display='none';
  openM('settle');
}
async function previewSettle(){
  var cust  = document.getElementById('settle-cust').value;
  var month = document.getElementById('settle-month').value;
  var r = await post('dairy.php',{action:'settle_preview',customer_id:cust,month:month});
  if(!r.bills||!r.bills.length){
    document.getElementById('settle-preview').innerHTML='<div style="text-align:center;padding:20px;color:#7a9485">No pending bills for this period.</div>';
    document.getElementById('confirm-settle-btn').style.display='none';
    return;
  }
  var html='<div style="background:#fdf8ee;border-radius:10px;padding:14px;margin-top:8px">'
    +'<div style="font-size:13px;font-weight:600;color:#1a3a2a;margin-bottom:10px">'+r.bills.length+' pending bills</div>'
    +'<table style="width:100%;border-collapse:collapse"><thead><tr style="background:#e8f5ee">'
    +'<th style="padding:7px 10px;font-size:11px;text-align:left">Date</th>'
    +'<th style="padding:7px 10px;font-size:11px;text-align:left">Customer</th>'
    +'<th style="padding:7px 10px;font-size:11px;text-align:right">Amount</th>'
    +'</tr></thead><tbody>';
  r.bills.forEach(function(b){
    html+='<tr style="border-bottom:1px solid #d4e8da">'
      +'<td style="padding:7px 10px;font-size:12px">'+fdate(b.sale_date)+'</td>'
      +'<td style="padding:7px 10px;font-size:12px">'+(b.cn||'\u2013')+'</td>'
      +'<td style="padding:7px 10px;font-size:12px;text-align:right;font-weight:600">\u20B9'+fmt(b.total_amount)+'</td></tr>';
  });
  html+='</tbody><tfoot><tr style="background:#e8f5ee">'
    +'<td colspan="2" style="padding:8px 10px;font-size:13px;font-weight:700">Total</td>'
    +'<td style="padding:8px 10px;font-size:15px;font-weight:700;text-align:right">\u20B9'+fmt(r.total)+'</td>'
    +'</tr></tfoot></table></div>';
  document.getElementById('settle-preview').innerHTML=html;
  document.getElementById('confirm-settle-btn').style.display='inline-flex';
}
async function confirmSettle(){
  var r = await post('dairy.php',{action:'settle_confirm',
    customer_id:document.getElementById('settle-cust').value,
    month:document.getElementById('settle-month').value});
  if(r.ok){showToast(r.count+' '+t('toast_settled'));closeM('settle');loadBills();}
  else showToast(r.msg,true);
}

function openResetCash(){
  document.getElementById('reset-month').value = document.getElementById('sel-month').value;
  document.getElementById('reset-preview').innerHTML='';
  document.getElementById('confirm-reset-btn').style.display='none';
  openM('reset');
}
async function previewReset(){
  var month = document.getElementById('reset-month').value;
  var cust  = document.getElementById('reset-cust').value;
  if(!month){showToast('Select a month',true);return;}
  var raw = await post('bills.php',{action:'get_bills',bill_type:'milk',month:month,customer_id:cust});
  var r = Array.isArray(raw)?raw:[];
  var pending = r.filter(function(b){return b.payment_status!=='paid';});
  var total   = pending.reduce(function(s,b){return s+parseFloat(b.total_amount||0);},0);
  var mName   = new Date(month+'-01').toLocaleDateString('en-IN',{month:'long',year:'numeric'});
  document.getElementById('reset-preview').innerHTML=
    '<div style="background:#fdf8ee;border-radius:10px;padding:14px;margin-top:8px">'
    +'<div style="font-size:13px;color:#3d5248;margin-bottom:8px"><b>'+mName+'</b></div>'
    +'<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;text-align:center">'
    +'<div style="background:#e8f5ee;border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:700;color:#1a3a2a">'+r.length+'</div><div style="font-size:11px;color:#7a9485">Total Bills</div></div>'
    +'<div style="background:#fdecea;border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:700;color:#c0392b">'+pending.length+'</div><div style="font-size:11px;color:#7a9485">Pending Bills</div></div>'
    +'<div style="background:#f5e6c0;border-radius:8px;padding:10px"><div style="font-size:18px;font-weight:700;color:#8a6518">\u20B9'+fmt(total)+'</div><div style="font-size:11px;color:#7a9485">Pending Amount</div></div>'
    +'</div></div>';
  document.getElementById('confirm-reset-btn').style.display=pending.length?'inline-flex':'none';
}
async function confirmReset(){
  var month = document.getElementById('reset-month').value;
  var cust  = document.getElementById('reset-cust').value;
  var r = await post('dairy.php',{action:'settle_confirm',customer_id:cust,month:month});
  if(r.ok){showToast('Cash flow reset! '+r.count+' bills marked paid');closeM('reset');loadBills();}
  else showToast(r.msg,true);
}

async function viewBill(id){
  var r = await post('bills.php',{action:'get_bill_detail',id:id});
  if(!r||!r.id){showToast('Could not load bill',true);return;}
  var statusColor = r.payment_status==='paid'?'#e8f5ee':r.payment_status==='pending'?'#fdecea':'#f5e6c0';
  var statusText  = r.payment_status==='paid'?'#2d5a3d':r.payment_status==='pending'?'#c0392b':'#8a6518';
  document.getElementById('bill-print-area').innerHTML=
    '<div style="text-align:center;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #1a3a2a">'
    +'<div style="font-size:26px">\uD83C\uDF3E</div>'
    +'<div style="font-family:\'Playfair Display\',serif;font-size:20px;color:#1a3a2a">Shetkari Mitra</div>'
    +'<div style="font-size:11px;color:#7a9485">\u0936\u0947\u0924\u0915\u0930\u0940 \u092E\u093F\u0924\u094D\u0930 \u2014 Milk Supply Bill</div>'
    +'</div>'
    +'<table style="width:100%;border-collapse:collapse;margin-bottom:14px">'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485;width:40%">Bill No.</td><td style="font-size:13px;font-weight:600">#'+String(r.id).padStart(4,'0')+'</td></tr>'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Date</td><td style="font-size:13px">'+fdate(r.sale_date)+'</td></tr>'
    +'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Customer</td><td style="font-size:13px;font-weight:600">'+(r.cn||'\u2013')+'</td></tr>'
    +(r.cm?'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Mobile</td><td style="font-size:13px">'+r.cm+'</td></tr>':'')
    +(r.ca?'<tr><td style="padding:5px 0;font-size:13px;color:#7a9485">Address</td><td style="font-size:13px">'+r.ca+'</td></tr>':'')
    +'</table>'
    +'<table style="width:100%;border-collapse:collapse">'
    +'<thead><tr style="background:#eaf3fb">'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:left">Description</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Litres</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Rate</th>'
    +'<th style="padding:9px 12px;font-size:12px;font-weight:600;color:#1a3a2a;text-align:right">Total</th>'
    +'</tr></thead>'
    +'<tbody><tr style="border-bottom:1px solid #d4e8da">'
    +'<td style="padding:10px 12px;font-size:13px"><b>'+r.product_name+'</b></td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right">'+fmt(r.quantity)+' L</td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right">\u20B9'+fmt(r.price_per_unit)+'/L</td>'
    +'<td style="padding:10px 12px;font-size:13px;text-align:right;font-weight:700">\u20B9'+fmt(r.total_amount)+'</td>'
    +'</tr></tbody>'
    +'<tfoot><tr style="background:#faf7f0">'
    +'<td colspan="3" style="padding:10px 12px;font-size:13px;font-weight:600">Total Amount</td>'
    +'<td style="padding:10px 12px;font-size:16px;font-weight:700;color:#1a3a2a;text-align:right">\u20B9'+fmt(r.total_amount)+'</td>'
    +'</tr></tfoot></table>'
    +'<div style="margin-top:12px;padding:10px 12px;background:'+statusColor+';border-radius:8px;font-size:13px;font-weight:600;color:'+statusText+'">Payment: '+r.payment_status.toUpperCase()+'</div>'
    +'<div style="margin-top:18px;text-align:center;font-size:11px;color:#7a9485;border-top:1px solid #d4e8da;padding-top:10px">Thank you! \u2014 Shetkari Mitra</div>';
  openM('print');
}

function printBill(){
  var content = document.getElementById('bill-print-area').innerHTML;
  var w = window.open('','_blank','width=600,height=700');
  w.document.write('<!DOCTYPE html><html><head><title>Milk Bill</title>'
    +'<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">'
    +'<style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:\'DM Sans\',sans-serif;padding:28px;max-width:500px;margin:0 auto}</style>'
    +'</head><body>'+content+'</body></html>');
  w.document.close();
  w.focus();
  setTimeout(function(){w.print();},500);
}

var _payBillId=null,_payMode=null,_payAmt=0;
function openPayPopup(billId,custName,amount){
  _payBillId=billId; _payMode=null; _payAmt=amount;
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
  await post('payments.php',{action:'save_payment',bill_id:_payBillId,amount:_payAmt,payment_mode:_payMode,payment_date:today(),reference_no:ref,notes:'Recorded from Milk Bills page'});
  closePayPopup();
  var labels={cash:'\uD83D\uDCB5 Cash',upi:'\uD83D\uDCF1 UPI',online:'\uD83C\uDFE6 Online',cheque:'\uD83D\uDCDD Cheque'};
  showToast('\u2705 Payment recorded \u2014 '+labels[_payMode]);
  loadBills();
}
document.getElementById('pay-popup').addEventListener('click',function(e){if(e.target===this)closePayPopup();});
</script>
</body></html>
