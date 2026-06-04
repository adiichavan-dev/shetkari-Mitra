<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$custs=[];$r=$conn->query("SELECT id,full_name,mobile FROM customers ORDER BY full_name");
while($row=$r->fetch_assoc())$custs[]=$row; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Payments — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Payment mode badges */
.pay-mode{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.pay-cash{background:#e8f5ee;color:#2d5a3d}
.pay-online{background:#eaf3fb;color:#2471a3}
.pay-upi{background:#f5e6c0;color:#8a6518}
.pay-cheque{background:#fdecea;color:#c0392b}
.bill-tag{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.bill-milk{background:#eaf3fb;color:#2471a3}
.bill-crop{background:#e8f5ee;color:#2d5a3d}
/* Category tabs */
.cat-tabs{display:flex;gap:0;background:#f0f4f1;border-radius:11px;padding:4px;width:fit-content}
.ctab{padding:7px 18px;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.ctab.on{background:#1a3a2a;color:#fff}
/* Monthly trend chart card */
.trend-card{background:#fff;border-radius:var(--r);border:1px solid var(--bd);padding:20px 20px 14px;margin-bottom:20px}
.trend-title{font-size:14px;font-weight:700;color:#1a3a2a;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between}
.trend-bars{display:flex;gap:8px;align-items:flex-end;height:140px;overflow-x:auto}
.trend-month-col{display:flex;flex-direction:column;align-items:center;gap:4px;min-width:72px;flex:1}
.trend-bar-wrap{display:flex;gap:3px;align-items:flex-end;width:100%;height:110px}
.trend-bar{border-radius:5px 5px 0 0;width:50%;transition:all .4s;cursor:pointer;position:relative}
.trend-bar:hover::after{content:attr(data-tip);position:absolute;bottom:100%;left:50%;transform:translateX(-50%);background:#1a3a2a;color:#fff;font-size:10px;white-space:nowrap;padding:3px 7px;border-radius:5px;pointer-events:none;margin-bottom:3px}
.bar-billed{background:#1a3a2a}
.bar-paid{background:#4caf8e}
.bar-balance{background:#f5a623}
.trend-month-label{font-size:10px;color:#7a9485;font-weight:500;text-align:center;margin-top:4px}
.trend-amount{font-size:10px;color:#3d5248;font-weight:600;text-align:center}
.trend-legend{display:flex;gap:16px;justify-content:center;margin-top:10px}
.tleg{display:flex;align-items:center;gap:5px;font-size:11px;color:#3d5248;font-weight:500}
.tleg-dot{width:10px;height:10px;border-radius:3px}
/* Active month highlight */
.trend-month-col.active .trend-month-label{color:#1a3a2a;font-weight:700}
.trend-month-col.active{background:#f0f4f1;border-radius:8px;padding:4px 2px}
/* Per-customer balance cards */
.cust-balance-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:20px}
.cust-bal-card{background:#fff;border-radius:12px;padding:14px 16px;border:1px solid #d4e8da;transition:border .2s}
.cust-bal-card.has-balance{border-color:#f5a623;background:#fffdf7}
.cust-bal-name{font-size:13px;font-weight:700;color:#1a3a2a;margin-bottom:8px}
.cust-bal-rows{display:flex;flex-direction:column;gap:4px}
.cust-bal-row{display:flex;justify-content:space-between;font-size:12px}
.cbl-label{color:#7a9485}
.cbl-val{font-weight:600;color:#1a3a2a}
.cbl-val.red{color:#c0392b}
.cbl-val.green{color:#2d5a3d}
</style>
</head><body>
<?php $pg='payments'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3>💰 Payment Records</h3><p>Track cash, UPI, online &amp; cheque — automatic from bills</p></div>
    <button class="btn-add" onclick="openPayment()"><i class="fa fa-plus"></i> Add Payment</button>
  </div>
  <div class="page">

    <!-- Filters -->
    <div style="background:#fff;border-radius:var(--r);padding:14px 18px;border:1px solid var(--bd);margin-bottom:18px;display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end">
      <div class="fg" style="margin:0">
        <label style="font-size:12px;font-weight:500;color:var(--text-mid);display:block;margin-bottom:4px">Month</label>
        <input type="month" id="pay-month" onchange="loadAll()" style="padding:7px 12px;border:1.5px solid var(--bd);border-radius:8px;font-size:13px;outline:none">
      </div>
      <div class="fg" style="margin:0">
        <label style="font-size:12px;font-weight:500;color:var(--text-mid);display:block;margin-bottom:4px">Customer</label>
        <select id="pay-cust" onchange="loadAll()" style="padding:7px 12px;border:1.5px solid var(--bd);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
          <option value="">All Customers</option>
          <?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?></option><?php endforeach;?>
        </select>
      </div>
      <div class="fg" style="margin:0">
        <label style="font-size:12px;font-weight:500;color:var(--text-mid);display:block;margin-bottom:4px">Category</label>
        <div class="cat-tabs">
          <button class="ctab on" onclick="switchCat('all',this)">All</button>
          <button class="ctab" onclick="switchCat('milk',this)">🥛 Milk</button>
          <button class="ctab" onclick="switchCat('crop',this)">🌾 Crop</button>
        </div>
      </div>
      <button class="print-btn" onclick="window.print()" style="margin-bottom:0"><i class="fa fa-print"></i> Print</button>
    </div>

    <!-- Monthly summary cards -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
      <div class="scard"><div class="sicon o"><i class="fa fa-rupee-sign"></i></div><div><div class="sval" id="sum-total">–</div><div class="slbl">Total Billed (₹)</div></div></div>
      <div class="scard"><div class="sicon g"><i class="fa fa-check-circle"></i></div><div><div class="sval" id="sum-paid">–</div><div class="slbl">Total Paid (₹)</div></div></div>
      <div class="scard"><div class="sicon" style="background:#fff3e0;color:#e67e22"><i class="fa fa-clock"></i></div><div><div class="sval" id="sum-bal">–</div><div class="slbl">Balance Due (₹)</div></div></div>
      <div class="scard"><div class="sicon b"><i class="fa fa-list"></i></div><div><div class="sval" id="sum-count">–</div><div class="slbl">Transactions</div></div></div>
    </div>

    <!-- ── MONTHLY TREND CHART ── -->
    <div class="trend-card">
      <div class="trend-title">
        <span>📈 Monthly Trend — Last 6 Months</span>
        <span style="font-size:11px;color:#7a9485;font-weight:400" id="trend-subtitle">Billed vs Paid vs Balance</span>
      </div>
      <div class="trend-bars" id="trend-bars">
        <div style="color:#7a9485;font-size:13px;align-self:center;margin:auto">Loading trend…</div>
      </div>
      <div class="trend-legend">
        <div class="tleg"><div class="tleg-dot" style="background:#1a3a2a"></div>Total Billed</div>
        <div class="tleg"><div class="tleg-dot" style="background:#4caf8e"></div>Paid</div>
        <div class="tleg"><div class="tleg-dot" style="background:#f5a623"></div>Balance</div>
      </div>
    </div>

    <!-- Per-customer balances (toggle) -->
    <div id="cust-balance-section" style="display:none">
      <div style="font-size:13px;font-weight:700;color:#1a3a2a;margin-bottom:10px">📊 Balance by Customer — This Month</div>
      <div class="cust-balance-grid" id="cust-balance-grid"></div>
    </div>

    <!-- Payments table -->
    <div class="tcard">
      <div class="thead">
        <h4 id="table-heading">Payment History</h4>
        <div style="display:flex;gap:8px;align-items:center">
          <div class="srch"><i class="fa fa-search"></i><input placeholder="Search..." oninput="filter('pay-body',this.value)"></div>
          <button style="padding:7px 12px;border:1.5px solid var(--bd);border-radius:8px;background:#fff;font-size:12px;cursor:pointer;color:#3d5248;font-family:'DM Sans',sans-serif;white-space:nowrap" onclick="toggleBalances()">📊 Per Customer</button>
        </div>
      </div>
      <div class="print-header" style="display:none;text-align:center;padding:14px 0 10px;border-bottom:2px solid #1a3a2a;margin-bottom:14px">
        <h2 style="font-family:'Playfair Display',serif;font-size:20px;color:#1a3a2a">Shetkari Mitra — शेतकरी मित्र</h2>
        <p id="print-pay-sub" style="font-size:11px;color:#555;margin-top:3px">Payment Report</p>
      </div>
      <div style="overflow-x:auto">
      <table>
        <thead><tr>
          <th>#</th><th>Date</th><th>Customer</th><th>Type</th>
          <th>Product</th><th>Bill (₹)</th><th>Paid (₹)</th>
          <th>Mode</th><th>Ref.</th><th></th>
        </tr></thead>
        <tbody id="pay-body"></tbody>
      </table>
      </div>
    </div>
  </div>
</main>

<!-- Add/Edit Payment Modal -->
<div class="overlay" id="m-payment">
  <div class="modal" style="max-width:560px">
    <div class="mhead"><h3 id="pay-title">Add Payment</h3><button class="mclose" onclick="closeM('payment')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="pay-id">
      <div class="fg">
        <label>Bill Category</label>
        <div style="display:flex;gap:8px;margin-top:4px">
          <button id="cat-milk-btn" onclick="setBillCat('milk',this)" style="padding:8px 18px;border:2px solid #2471a3;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#eaf3fb;color:#2471a3;font-family:'DM Sans',sans-serif">🥛 Milk Bills</button>
          <button id="cat-crop-btn" onclick="setBillCat('crop',this)" style="padding:8px 18px;border:1.5px solid #d4e8da;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#fff;color:#3d5248;font-family:'DM Sans',sans-serif">🌾 Crop Bills</button>
        </div>
      </div>
      <div class="fg">
        <label>Select Bill</label>
        <select id="pay-bill" onchange="onBillSelect()"><option value="">Loading…</option></select>
        <div id="bill-balance-info" style="margin-top:6px;font-size:12px;color:var(--bl);display:none;padding:8px;background:var(--bll);border-radius:8px"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Amount Paid (₹)</label><input id="pay-amt" type="number" step="0.01" placeholder="0.00"></div>
        <div class="fg"><label>Payment Mode</label>
          <select id="pay-mode">
            <option value="cash">💵 Cash</option>
            <option value="upi">📱 UPI</option>
            <option value="online">🏦 Online Transfer</option>
            <option value="cheque">📝 Cheque</option>
          </select>
        </div>
      </div>
      <div class="row2">
        <div class="fg"><label>Payment Date</label><input id="pay-date" type="date"></div>
        <div class="fg"><label>Reference No.</label><input id="pay-ref" placeholder="UPI ID / Cheque No. / Txn ID"></div>
      </div>
      <div class="fg"><label>Notes</label><textarea id="pay-notes" rows="2" placeholder="Optional notes"></textarea></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('payment')">Cancel</button>
      <button class="btn-save" onclick="savePayment()">Save Payment</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
function fmtI(n){return isNaN(n)?'0':(+n).toLocaleString('en-IN',{maximumFractionDigits:2});}
const modeTag=m=>{
  const mp={cash:'pay-cash',online:'pay-online',upi:'pay-upi',cheque:'pay-cheque'};
  const ic={cash:'💵',online:'🏦',upi:'📱',cheque:'📝'};
  return `<span class="pay-mode ${mp[m]||'pay-cash'}">${ic[m]||'💵'} ${m}</span>`;
};

let curCat='all', showBalances=false, _billCat='milk';

function switchCat(cat,btn){
  curCat=cat;
  document.querySelectorAll('.cat-tabs .ctab').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  loadAll();
}

document.addEventListener('DOMContentLoaded',()=>{
  const now=new Date();
  const m=now.getFullYear()+'-'+String(now.getMonth()+1).padStart(2,'0');
  document.getElementById('pay-month').value=m;
  document.getElementById('pay-date').value=today();
  document.getElementById('print-pay-sub').textContent=
    'Payment Report — '+new Date(m+'-01').toLocaleDateString('en-IN',{month:'long',year:'numeric'});
  loadAll();
});

async function loadAll(){
  const month=document.getElementById('pay-month').value;
  const cust =document.getElementById('pay-cust').value;
  const btype=curCat!=='all'?curCat:'';

  // Update print subtitle
  if(month){
    const lbl=new Date(month+'-01').toLocaleDateString('en-IN',{month:'long',year:'numeric'});
    document.getElementById('print-pay-sub').textContent='Payment Report — '+lbl;
    document.getElementById('table-heading').textContent='Payment History — '+lbl;
  }

  // Summary cards
  const sum=await post('payments.php',{action:'get_payment_summary',customer_id:cust,month,bill_type:btype});
  document.getElementById('sum-total').textContent='₹'+fmtI(sum.total||0);
  document.getElementById('sum-paid').textContent ='₹'+fmtI(sum.paid||0);
  document.getElementById('sum-bal').textContent  ='₹'+fmtI(sum.balance||0);

  // Payment records
  const d=await post('payments.php',{action:'get_payments',customer_id:cust,month,bill_type:btype});
  document.getElementById('sum-count').textContent=d.length;
  document.getElementById('pay-body').innerHTML=d.length?d.map((p,i)=>`<tr>
    <td>${i+1}</td>
    <td>${fdate(p.payment_date)}</td>
    <td><b>${p.cn||'–'}</b></td>
    <td><span class="bill-tag ${p.bill_type==='milk'?'bill-milk':'bill-crop'}">${p.bill_type==='milk'?'🥛 Milk':'🌾 Crop'}</span></td>
    <td>${p.prod||'–'}</td>
    <td>₹${fmtI(p.bill_amt)}</td>
    <td><b>₹${fmtI(p.amount)}</b></td>
    <td>${modeTag(p.payment_mode)}</td>
    <td style="font-size:11px">${p.reference_no||'–'}</td>
    <td><div class="acts">
      <button class="abtn e" onclick='editPayment(${JSON.stringify(p)})'><i class="fa fa-pen"></i></button>
      <button class="abtn d" onclick="delPayment(${p.id},${p.bill_id})"><i class="fa fa-trash"></i></button>
    </div></td>
  </tr>`).join(''):`<tr class="empty"><td colspan="10">No payment records for this period</td></tr>`;

  // Load trend chart (always last 6 months regardless of month filter)
  loadTrend(btype);

  // Refresh customer balances if open
  if(showBalances) loadCustomerBalances(month);
}

// ── Monthly Trend Chart ───────────────────────────────────────
async function loadTrend(btype){
  const r=await post('payments.php',{action:'get_monthly_trend',bill_type:btype});
  if(!r.months||!r.months.length){
    document.getElementById('trend-bars').innerHTML='<div style="color:#7a9485;font-size:13px;align-self:center;margin:auto">No data</div>';
    return;
  }

  const curMonth=document.getElementById('pay-month').value;
  const months=r.months;

  // Find max value for scaling
  const maxVal=Math.max(...months.map(m=>Math.max(m.total,m.paid,m.balance,1)));

  const subtitle=btype==='milk'?'Milk Bills':'btype'==='crop'?'Crop Bills':'All Bills';
  document.getElementById('trend-subtitle').textContent=subtitle;

  document.getElementById('trend-bars').innerHTML=months.map(m=>{
    const hTotal  =Math.round((m.total/maxVal)*100)||1;
    const hPaid   =Math.round((m.paid/maxVal)*100)||1;
    const hBalance=Math.round((m.balance/maxVal)*100)||1;
    const isActive=m.month===curMonth;
    const tipTotal  =`Billed: ₹${fmtI(m.total)}`;
    const tipPaid   =`Paid: ₹${fmtI(m.paid)}`;
    const tipBalance=`Balance: ₹${fmtI(m.balance)}`;
    return `<div class="trend-month-col${isActive?' active':''}">
      <div class="trend-bar-wrap">
        <div class="trend-bar bar-billed" style="height:${hTotal}%;flex:1" data-tip="${tipTotal}" title="${tipTotal}"></div>
        <div class="trend-bar bar-paid"   style="height:${hPaid}%;flex:1"  data-tip="${tipPaid}"   title="${tipPaid}"></div>
        <div class="trend-bar bar-balance" style="height:${hBalance}%;flex:1" data-tip="${tipBalance}" title="${tipBalance}"></div>
      </div>
      <div class="trend-amount">₹${m.total>=1000?(m.total/1000).toFixed(1)+'k':fmtI(m.total)}</div>
      <div class="trend-month-label">${m.label}</div>
    </div>`;
  }).join('');
}

// ── Customer Balance Summary ──────────────────────────────────
function toggleBalances(){
  showBalances=!showBalances;
  document.getElementById('cust-balance-section').style.display=showBalances?'block':'none';
  if(showBalances) loadCustomerBalances(document.getElementById('pay-month').value);
}

async function loadCustomerBalances(month){
  const grid=document.getElementById('cust-balance-grid');
  grid.innerHTML='<div style="color:#7a9485;font-size:13px">Loading…</div>';
  const btype=curCat!=='all'?curCat:'';
  const r=await post('payments.php',{action:'get_all_customer_balances',month,bill_type:btype});
  if(!r||!r.customers||!r.customers.length){
    grid.innerHTML='<div style="color:#7a9485;font-size:13px">No data for this period.</div>';
    return;
  }
  grid.innerHTML=r.customers.map(c=>`
    <div class="cust-bal-card ${c.balance>0?'has-balance':''}">
      <div class="cust-bal-name">${c.name}</div>
      <div class="cust-bal-rows">
        <div class="cust-bal-row"><span class="cbl-label">Total Billed</span><span class="cbl-val">₹${fmtI(c.total)}</span></div>
        <div class="cust-bal-row"><span class="cbl-label">Paid</span><span class="cbl-val green">₹${fmtI(c.paid)}</span></div>
        <div class="cust-bal-row"><span class="cbl-label">Balance</span><span class="cbl-val ${c.balance>0?'red':'green'}">₹${fmtI(c.balance)}</span></div>
      </div>
    </div>`).join('');
}

// ── Bill dropdown for modal ───────────────────────────────────
function setBillCat(cat,btn){
  _billCat=cat;
  document.getElementById('cat-milk-btn').style.cssText=cat==='milk'
    ?'padding:8px 18px;border:2px solid #2471a3;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#eaf3fb;color:#2471a3;font-family:DM Sans,sans-serif'
    :'padding:8px 18px;border:1.5px solid #d4e8da;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#fff;color:#3d5248;font-family:DM Sans,sans-serif';
  document.getElementById('cat-crop-btn').style.cssText=cat==='crop'
    ?'padding:8px 18px;border:2px solid #2d5a3d;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#e8f5ee;color:#2d5a3d;font-family:DM Sans,sans-serif'
    :'padding:8px 18px;border:1.5px solid #d4e8da;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;background:#fff;color:#3d5248;font-family:DM Sans,sans-serif';
  loadBillsForDropdown(cat);
}

async function loadBillsForDropdown(cat){
  const month=document.getElementById('pay-month').value;
  const allBills=await post('bills.php',{action:'get_bills',bill_type:cat,month});
  const pending=(Array.isArray(allBills)?allBills:[]).filter(b=>b.payment_status!=='paid');
  const sel=document.getElementById('pay-bill');
  sel.innerHTML=pending.length
    ?'<option value="">Select bill…</option>'+pending.map(b=>`<option value="${b.id}" data-total="${b.total_amount}" data-cust="${b.customer_id||0}">${fdate(b.sale_date)} — ${b.cn||'Walk-in'} — ${b.product_name} — ₹${fmtI(b.total_amount)}</option>`).join('')
    :`<option value="">No pending ${cat} bills</option>`;
}

async function openPayment(data=null){
  await loadBillsForDropdown(_billCat);
  document.getElementById('pay-id').value   =data?data.id:'';
  document.getElementById('pay-amt').value  =data?data.amount:'';
  document.getElementById('pay-mode').value =data?data.payment_mode:'cash';
  document.getElementById('pay-date').value =data?data.payment_date:today();
  document.getElementById('pay-ref').value  =data?data.reference_no||'':'';
  document.getElementById('pay-notes').value=data?data.notes||'':'';
  if(data&&data.bill_id) document.getElementById('pay-bill').value=data.bill_id;
  document.getElementById('pay-title').textContent=data?'Edit Payment':'Add Payment';
  document.getElementById('bill-balance-info').style.display='none';
  openM('payment');
}

function onBillSelect(){
  const sel=document.getElementById('pay-bill');
  const opt=sel.options[sel.selectedIndex];
  const info=document.getElementById('bill-balance-info');
  if(!opt||!opt.value){info.style.display='none';return;}
  const total=parseFloat(opt.dataset.total||0);
  info.textContent=`Bill total: ₹${fmtI(total)} — Amount auto-filled`;
  info.style.display='block';
  document.getElementById('pay-amt').value=total;
}

function editPayment(d){openPayment(d);}

async function savePayment(){
  const billSel=document.getElementById('pay-bill');
  const opt=billSel.options[billSel.selectedIndex];
  const r=await post('payments.php',{
    action:      'save_payment',
    id:          document.getElementById('pay-id').value,
    bill_id:     billSel.value,
    customer_id: opt?.dataset?.cust||'0',
    amount:      document.getElementById('pay-amt').value,
    payment_mode:document.getElementById('pay-mode').value,
    payment_date:document.getElementById('pay-date').value,
    reference_no:document.getElementById('pay-ref').value,
    notes:       document.getElementById('pay-notes').value,
  });
  if(r.ok){showToast('Payment saved! Bill: '+(r.new_status||'updated'));closeM('payment');loadAll();}
  else showToast(r.msg||'Error',true);
}

async function delPayment(id,billId){
  if(!confirm('Delete this payment record?'))return;
  await post('payments.php',{action:'delete_payment',id,bill_id:billId});
  showToast('Deleted!');loadAll();
}
</script>
</body></html>
