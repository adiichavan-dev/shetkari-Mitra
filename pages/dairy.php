<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$custs=[];$r=$conn->query("SELECT id,full_name,mobile,COALESCE(customer_type,'retail') as customer_type,COALESCE(retail_cow_rate,0) as retail_cow_rate,COALESCE(retail_buffalo_rate,0) as retail_buffalo_rate,COALESCE(company_name,'') as company_name FROM customers WHERE category='milk' OR category='both' ORDER BY full_name");
while($row=$r->fetch_assoc())$custs[]=$row;
$ls=$conn->query("SELECT * FROM livestock LIMIT 1")->fetch_assoc();
$cows=$ls['cow_count']??0; $buf=$ls['buffalo_count']??0; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dairy — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.animal-tabs{display:flex;gap:0;background:#f0f4f1;border-radius:11px;padding:4px;margin-bottom:20px;width:fit-content}
.atab{padding:9px 22px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.atab.cow.on{background:#1a3a2a;color:#fff}
.atab.buf.on{background:#2471a3;color:#fff}
.fat-snf-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;background:#fdf8ee;border-radius:10px;padding:14px;margin-bottom:14px}
.fat-snf-row label{font-size:12px;font-weight:500;color:#3d5248;display:block;margin-bottom:5px}
.fat-snf-row input{width:100%;padding:9px 12px;border:1.5px solid #d4e8da;border-radius:9px;font-size:14px;font-weight:600;text-align:center;outline:none;font-family:'DM Sans',sans-serif}
.fat-snf-row input:focus{border-color:#2d5a3d}
.calc-rate{text-align:center;font-size:22px;font-weight:700;color:#1a3a2a;padding:8px;background:#e8f5ee;border-radius:9px}
.pay-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:'DM Sans',sans-serif;transition:all .2s}
.pay-btn.pending{background:#fdecea;color:#c0392b}
.pay-btn.pending:hover{background:#c0392b;color:#fff}
.pay-btn.paid{background:#e8f5ee;color:#2d5a3d;cursor:default}
.settle-btn{display:flex;align-items:center;gap:6px;padding:8px 16px;background:#2d5a3d;color:#fff;border:none;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;transition:background .2s}
.settle-btn:hover{background:#1a3a2a}
.section-panel{display:none}
.section-panel.on{display:block}

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
<?php $pg='dairy'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3>🐄 Dairy & Livestock</h3><p>Separate cow & buffalo milk tracking with FAT/SNF rate calculator</p></div>
    <div style="display:flex;gap:8px">
      <button class="btn-add" style="background:#c9962a;padding:9px 14px;font-size:13px" onclick="openSetRates()"><i class="fa fa-tags"></i> Set Milk Rates</button>
      <button class="btn-add" style="background:#1a3a2a" onclick="openMilk('cow')"><i class="fa fa-plus"></i> Add Cow Entry</button>
      <button class="btn-add" style="background:#2471a3" onclick="openMilk('buffalo')"><i class="fa fa-plus"></i> Add Buffalo Entry</button>
    </div>
  </div>
  <div class="page">

    <div class="stats" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <div class="scard"><div class="sicon g"><i class="fa fa-cow"></i></div><div><div class="sval" id="lc"><?=$cows?></div><div class="slbl">Cows</div></div></div>
      <div class="scard"><div class="sicon b"><i class="fa fa-horse"></i></div><div><div class="sval" id="lb"><?=$buf?></div><div class="slbl">Buffaloes</div></div></div>
      <div class="scard"><div class="sicon g"><i class="fa fa-tint"></i></div><div><div class="sval" id="cow-today">–</div><div class="slbl">Cow Milk Today (L)</div></div></div>
      <div class="scard"><div class="sicon b"><i class="fa fa-tint"></i></div><div><div class="sval" id="buf-today">–</div><div class="slbl">Buffalo Milk Today (L)</div></div></div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <div class="animal-tabs">
        <button class="atab cow on" onclick="switchAnimal('cow',this)">🐄 Cow Milk</button>
        <button class="atab buf" onclick="switchAnimal('buffalo',this)">🐃 Buffalo Milk</button>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <button class="settle-btn" onclick="openSettle()"><i class="fa fa-check-double"></i> Monthly Settlement</button>
        <button class="btn-add" style="background:#888;padding:8px 14px;font-size:12px" onclick="openM('livestock')"><i class="fa fa-edit"></i> Update Animals</button>
      </div>
    </div>

    <div class="section-panel on" id="panel-cow">
      <div class="tcard">
        <div class="thead">
          <h4>🐄 Cow Milk Production Log</h4>
          <div style="display:flex;gap:8px"><div class="srch"><i class="fa fa-search"></i><input placeholder="Search..." oninput="filter('cow-body',this.value)"></div></div>
        </div>
        <table>
          <thead><tr><th>Date</th><th>Morning(L)</th><th>Evening(L)</th><th>Total(L)</th><th>FAT</th><th>SNF</th><th>Rate(₹/L)</th><th>Sold(L)</th><th>Earnings(₹)</th><th>Customer</th><th>Payment</th><th></th></tr></thead>
          <tbody id="cow-body"></tbody>
        </table>
      </div>
    </div>

    <div class="section-panel" id="panel-buffalo">
      <div class="tcard">
        <div class="thead">
          <h4>🐃 Buffalo Milk Production Log</h4>
          <div style="display:flex;gap:8px"><div class="srch"><i class="fa fa-search"></i><input placeholder="Search..." oninput="filter('buf-body',this.value)"></div></div>
        </div>
        <table>
          <thead><tr><th>Date</th><th>Morning(L)</th><th>Evening(L)</th><th>Total(L)</th><th>FAT</th><th>SNF</th><th>Rate(₹/L)</th><th>Sold(L)</th><th>Earnings(₹)</th><th>Customer</th><th>Payment</th><th></th></tr></thead>
          <tbody id="buf-body"></tbody>
        </table>
      </div>
    </div>
  </div>
</main>


<div class="overlay" id="m-milk">
  <div class="modal">
    <div class="mhead"><h3 id="mkt">Add Milk Entry</h3><button class="mclose" onclick="closeM('milk')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="mkid">
      <input type="hidden" id="mkanimal">
      <div class="row2">
        <div class="fg"><label>Date</label><input id="mkd" type="date"></div>
        <div class="fg"><label>Customer</label>
          <select id="mkc" onchange="onCustomerChange()"><option value="">Select customer</option><?php foreach($custs as $c):?><option value="<?=$c['id']?>" data-type="<?=htmlspecialchars($c['customer_type']??'retail')?>" data-cowrate="<?=$c['retail_cow_rate']??0?>" data-bufrate="<?=$c['retail_buffalo_rate']??0?>" data-company="<?=htmlspecialchars($c['company_name']??'')?>"><?=htmlspecialchars($c['full_name'])?> (<?=$c['mobile']?>) <?=$c['customer_type']==='company'?'['.($c['company_name']??'Co.').']':'[🐄₹'.($c['retail_cow_rate']??0).' / 🐃₹'.($c['retail_buffalo_rate']??0).']'?></option><?php endforeach;?></select></div>
      </div>
      <input type="hidden" id="mk-supply-type" value="retail">
      <input type="hidden" id="mk-company-id" value="">
      <div id="supply-info" style="margin:4px 0 12px;padding:10px 14px;border-radius:9px;font-size:13px;font-weight:500;display:none"></div>
      
      <div class="row2">
        <div class="fg">
          <label>🌅 Morning (Litres)</label>
          <input id="mkm" type="number" step="0.1" min="0" placeholder="0.0" oninput="onMilkLitresChange()">
        </div>
        <div class="fg">
          <label>🌙 Evening (Litres)</label>
          <input id="mke" type="number" step="0.1" min="0" placeholder="0.0" oninput="onMilkLitresChange()">
        </div>
      </div>

      
      <div id="milk-summary-bar" style="display:none;background:linear-gradient(135deg,#1a3a2a,#2d5a3d);border-radius:10px;padding:12px 16px;margin-bottom:12px;display:none">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
          <div><div id="ms-total" style="font-size:20px;font-weight:700;color:#fff">0.0 L</div><div style="font-size:10px;color:rgba(255,255,255,.6);margin-top:2px">Total Collected</div></div>
          <div><div id="ms-sold"  style="font-size:20px;font-weight:700;color:#a8d5b5">0.0 L</div><div style="font-size:10px;color:rgba(255,255,255,.6);margin-top:2px">Sold</div></div>
          <div><div id="ms-earn"  style="font-size:20px;font-weight:700;color:#f5c842">₹0</div><div style="font-size:10px;color:rgba(255,255,255,.6);margin-top:2px">Est. Earnings</div></div>
        </div>
      </div>


      <div class="fat-snf-row">
        <div>
          <label>FAT % <span id="fat-range" style="color:#7a9485;font-weight:400"></span></label>
          <input id="mkfat" type="number" step="0.1" placeholder="e.g. 4.2" oninput="calcRate()">
        </div>
        <div>
          <label>SNF % <span id="snf-range" style="color:#7a9485;font-weight:400"></span></label>
          <input id="mksnf" type="number" step="0.1" placeholder="e.g. 8.5" oninput="calcRate()">
        </div>
        <div>
          <label>Rate (₹/L)</label>
          <div class="calc-rate" id="calc-rate-display">–</div>
        </div>
      </div>
      <input type="hidden" id="mkrate">

      
      <div style="background:#fdf8ee;border-radius:10px;padding:12px 14px;margin-bottom:12px">
        <div style="font-size:11px;font-weight:600;color:#8a6518;margin-bottom:8px">📦 Distribution</div>
        <div class="row2" style="margin-bottom:0">
          <div class="fg" style="margin-bottom:0">
            <label style="display:flex;align-items:center;gap:6px">
              Sold (Litres)
            </label>
            <input id="mks" type="number" step="0.1" min="0" placeholder="0.0" oninput="updateEarningsPreview()" style="border-color:#c9e8d0">
          </div>
          <div class="fg" style="margin-bottom:0">
            <label style="display:flex;align-items:center;gap:6px">
              Consumed (Litres)
              <span style="font-size:10px;background:#f5e6c0;color:#8a6518;padding:1px 7px;border-radius:20px;font-weight:600">Optional</span>
            </label>
            <input id="mkco" type="number" step="0.1" min="0" placeholder="0.0 (home use)">
          </div>
        </div>
      </div>
      <div class="fg"><label>Notes</label><textarea id="mkno" rows="2" placeholder="Notes"></textarea></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('milk')">Cancel</button>
      <button class="btn-save" onclick="saveMilk()">Save Entry</button>
    </div>
  </div>
</div>

<!-- Monthly Settlement Modal -->
<div class="overlay" id="m-settle">
  <div class="modal" style="max-width:520px">
    <div class="mhead"><h3>Monthly Settlement</h3><button class="mclose" onclick="closeM('settle')">✕</button></div>
    <div class="mbody">
      <div class="row2">
        <div class="fg"><label>Customer</label>
          <select id="settle-cust"><option value="">All Customers</option><?php foreach($custs as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['full_name'])?></option><?php endforeach;?></select></div>
        <div class="fg"><label>Month</label><input id="settle-month" type="month"></div>
      </div>
      <div id="settle-preview" style="margin-top:8px"></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('settle')">Cancel</button>
      <button class="btn-save" onclick="previewSettle()" style="background:#2471a3">Preview</button>
      <button class="btn-save" id="confirm-settle-btn" onclick="confirmSettle()" style="display:none">✅ Mark All Paid</button>
    </div>
  </div>
</div>

<!-- Livestock Modal -->
<div class="overlay" id="m-livestock">
  <div class="modal">
    <div class="mhead"><h3>Update Animal Count</h3><button class="mclose" onclick="closeM('livestock')">✕</button></div>
    <div class="mbody">
      <div class="row2">
        <div class="fg"><label>Cow Count</label><input id="lsc" type="number" value="<?=$cows?>" min="0"></div>
        <div class="fg"><label>Buffalo Count</label><input id="lsb" type="number" value="<?=$buf?>" min="0"></div>
      </div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('livestock')">Cancel</button>
      <button class="btn-save" onclick="saveLivestock()">Update</button>
    </div>
  </div>
</div>

<!-- Quick Pay Modal (cash / online / UPI / cheque) -->
<div class="pay-popup" id="pay-popup">
  <div class="pay-popup-box">
    <div class="pay-popup-head">
      <h3>💰 Record Payment</h3>
      <p id="pay-popup-label">Select payment method</p>
    </div>
    <div class="pay-popup-body">
      <div class="pay-mode-grid">
        <button class="pay-mode-btn" onclick="selectPayMode('cash')"><span>💵</span>Cash</button>
        <button class="pay-mode-btn" onclick="selectPayMode('upi')"><span>📱</span>UPI</button>
        <button class="pay-mode-btn" onclick="selectPayMode('online')"><span>🏦</span>Online Transfer</button>
        <button class="pay-mode-btn" onclick="selectPayMode('cheque')"><span>📝</span>Cheque</button>
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

<!-- Set Milk Rates Modal -->
<div class="overlay" id="m-setrates">
  <div class="modal" style="max-width:500px">
    <div class="mhead" style="background:linear-gradient(135deg,#1a3a2a,#c9962a)">
      <h3 style="color:#fff">&#x1F4B0; Set Retail Milk Rates</h3>
      <button class="mclose" onclick="closeM('setrates')" style="background:rgba(255,255,255,.2);color:#fff">&#x2715;</button>
    </div>
    <div class="mbody">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div style="background:#e8f5ee;border-radius:12px;padding:16px;text-align:center">
          <div style="font-size:28px;margin-bottom:8px">&#x1F404;</div>
          <label style="font-size:12px;font-weight:600;color:#1a3a2a;display:block;margin-bottom:8px">Cow Milk Rate (&#x20B9;/litre)</label>
          <input id="sr-cow" type="number" step="0.5" min="0" placeholder="e.g. 42"
            style="width:100%;padding:10px;border:2px solid #c9e8d0;border-radius:9px;font-size:18px;font-weight:700;text-align:center;outline:none;font-family:'DM Sans',sans-serif;color:#1a3a2a">
        </div>
        <div style="background:#eaf3fb;border-radius:12px;padding:16px;text-align:center">
          <div style="font-size:28px;margin-bottom:8px">&#x1F403;</div>
          <label style="font-size:12px;font-weight:600;color:#2471a3;display:block;margin-bottom:8px">Buffalo Milk Rate (&#x20B9;/litre)</label>
          <input id="sr-buf" type="number" step="0.5" min="0" placeholder="e.g. 55"
            style="width:100%;padding:10px;border:2px solid #b8d8f0;border-radius:9px;font-size:18px;font-weight:700;text-align:center;outline:none;font-family:'DM Sans',sans-serif;color:#2471a3">
        </div>
      </div>
      <div id="sr-preview" style="background:#faf7f0;border-radius:10px;padding:12px 14px;font-size:13px;color:#3d5248;min-height:40px"></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('setrates')">Cancel</button>
      <button class="btn-save" style="background:#c9962a" onclick="saveRetailRates()"><i class="fa fa-save"></i> Save Rates for All Retail Customers</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script src="../js/rates.js"></script>
<script>
let curAnimal = 'cow';
let _payBillId = null, _payBillRow = null, _payMode = null;

function switchAnimal(type, btn) {
  curAnimal = type;
  document.querySelectorAll('.atab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('panel-cow').classList.toggle('on', type === 'cow');
  document.getElementById('panel-buffalo').classList.toggle('on', type === 'buffalo');
}

// Re-fire customer rate when the milk type (cow/buffalo) changes inside the modal
function onAnimalTabChange(type, btn) {
  document.querySelectorAll('.atab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('mkanimal').value = type;
  // Re-evaluate retail rate for newly selected animal type
  const supplyType = document.getElementById('mk-supply-type').value;
  if (supplyType === 'retail') onCustomerChange();
}

function onCustomerChange() {
  const sel = document.getElementById('mkc');
  const opt = sel.options[sel.selectedIndex];
  const infoBox = document.getElementById('supply-info');
  if (!opt || !opt.value) {
    infoBox.style.display = 'none';
    document.getElementById('mk-supply-type').value = 'retail';
    return;
  }
  const ctype   = opt.dataset.type || 'retail';
  const cowRate  = parseFloat(opt.dataset.cowrate || 0);
  const bufRate  = parseFloat(opt.dataset.bufrate || 0);
  const company = opt.dataset.company || '';
  const anim    = document.getElementById('mkanimal').value || curAnimal;
  document.getElementById('mk-supply-type').value = ctype;

  if (ctype === 'retail') {
    // Pick rate based on which animal type is being entered
    const crate = anim === 'buffalo' ? bufRate : cowRate;
    const animalLabel = anim === 'buffalo' ? '🐃 Buffalo' : '🐄 Cow';
    if (crate > 0) {
      infoBox.style.cssText = 'margin:4px 0 12px;padding:10px 14px;border-radius:9px;font-size:13px;font-weight:500;display:block;background:#e8f5ee;color:#1a3a2a';
      infoBox.innerHTML = '🏪 <b>Retail Customer</b> — ' + animalLabel + ' rate: <b>₹' + crate + '/L</b>. Rate auto-filled.';
      document.getElementById('mkrate').value = crate;
      document.getElementById('calc-rate-display').textContent = '₹' + crate.toFixed(2) + ' (retail)';
      document.getElementById('calc-rate-display').style.color = anim === 'buffalo' ? '#2471a3' : '#1a3a2a';
    } else {
      const rateField = anim === 'buffalo' ? 'Buffalo Milk Rate' : 'Cow Milk Rate';
      infoBox.style.cssText = 'margin:4px 0 12px;padding:10px 14px;border-radius:9px;font-size:13px;font-weight:500;display:block;background:#fdecea;color:#c0392b';
      infoBox.innerHTML = '⚠️ <b>No ' + animalLabel + ' rate set!</b> Go to <a href="../pages/customers.php" style="color:#c0392b;font-weight:700">Customers</a> and set the <b>' + rateField + '</b> for this customer.';
      document.getElementById('mkrate').value = '';
      document.getElementById('calc-rate-display').textContent = 'No rate set';
      document.getElementById('calc-rate-display').style.color = '#c0392b';
    }
  } else {
    infoBox.style.cssText = 'margin:4px 0 12px;padding:10px 14px;border-radius:9px;font-size:13px;font-weight:500;display:block;background:#eaf3fb;color:#2471a3';
    infoBox.innerHTML = '🏭 <b>' + company + ' (Company Supply)</b> — Rate calculated from FAT & SNF values below.';
    document.getElementById('mkrate').value = '';
    document.getElementById('calc-rate-display').textContent = '–';
  }
}

function calcRate() {
  const fat  = parseFloat(document.getElementById('mkfat').value);
  const snf  = parseFloat(document.getElementById('mksnf').value);
  const anim = document.getElementById('mkanimal').value || curAnimal;
  if (!isNaN(fat) && !isNaN(snf)) {
    const rate = getRate(anim, fat, snf);
    if (rate !== null) {
      document.getElementById('calc-rate-display').textContent = '₹' + rate.toFixed(2);
      document.getElementById('calc-rate-display').style.color = anim === 'buffalo' ? '#2471a3' : '#1a3a2a';
      document.getElementById('mkrate').value = rate;
    } else {
      document.getElementById('calc-rate-display').textContent = 'Out of range';
      document.getElementById('calc-rate-display').style.color = '#c0392b';
      document.getElementById('mkrate').value = '';
    }
  } else {
    document.getElementById('calc-rate-display').textContent = '–';
    document.getElementById('mkrate').value = '';
  }
  updateEarningsPreview();
}

function onMilkLitresChange() {
  const morning = parseFloat(document.getElementById('mkm').value) || 0;
  const evening = parseFloat(document.getElementById('mke').value) || 0;
  const total   = Math.round((morning + evening) * 10) / 10;

  // Auto-fill Sold with total (consumed stays separate/optional)
  const consumed = parseFloat(document.getElementById('mkco').value) || 0;
  const sold     = Math.max(0, Math.round((total - consumed) * 10) / 10);
  document.getElementById('mks').value = total > 0 ? sold : '';

  // Show/hide summary bar
  const bar = document.getElementById('milk-summary-bar');
  if (total > 0) {
    bar.style.display = 'block';
    document.getElementById('ms-total').textContent = total.toFixed(1) + ' L';
    document.getElementById('ms-sold').textContent  = sold.toFixed(1)  + ' L';
  } else {
    bar.style.display = 'none';
  }
  updateEarningsPreview();
}

function updateEarningsPreview() {
  const sold = parseFloat(document.getElementById('mks').value) || 0;
  const rate = parseFloat(document.getElementById('mkrate').value) || 0;
  const earn = Math.round(sold * rate * 100) / 100;
  const bar  = document.getElementById('milk-summary-bar');
  const morning = parseFloat(document.getElementById('mkm').value) || 0;
  const evening = parseFloat(document.getElementById('mke').value) || 0;
  if ((morning + evening) > 0) {
    bar.style.display = 'block';
    document.getElementById('ms-earn').textContent = rate > 0 ? '₹' + earn.toLocaleString('en-IN', {maximumFractionDigits: 2}) : '–';
  }
}

function updateFatSnfHints(animal) {
  const info = getRateInfo(animal);
  document.getElementById('fat-range').textContent = `(${info.fatMin}–${info.fatMax})`;
  document.getElementById('snf-range').textContent = `(${info.snfMin}–${info.snfMax})`;
  document.getElementById('mkfat').min = info.fatMin;
  document.getElementById('mkfat').max = info.fatMax;
  document.getElementById('mksnf').min = info.snfMin;
  document.getElementById('mksnf').max = info.snfMax;
  const isBuf = animal === 'buffalo';

}

async function loadAll() {
  loadMilk('cow');
  loadMilk('buffalo');
  const d = await post('dairy.php', {action:'get_today_totals'});
  if (d.cow !== undefined) document.getElementById('cow-today').textContent = fmt(d.cow);
  if (d.buf !== undefined) document.getElementById('buf-today').textContent = fmt(d.buf);
}

async function loadMilk(animal) {
  const d = await post('dairy.php', {action:'get_milk', animal_type: animal});
  const tbody = document.getElementById(animal === 'cow' ? 'cow-body' : 'buf-body');
  tbody.innerHTML = d.length ? d.map(m => {
    let payCell = '–';
    if (m.bill_id) {
      if (m.payment_status === 'paid') {
        payCell = `<span class="pay-btn paid"><i class="fa fa-check"></i> Paid</span>`;
      } else {
        payCell = `<button class="pay-btn pending" onclick="openPayPopup(${m.bill_id}, this, '${m.cn||''}', ${m.earnings||0})"><i class="fa fa-rupee-sign"></i> Pay</button>`;
      }
    }
    return `<tr>
      <td>${fdate(m.entry_date)}</td>
      <td>${fmt(m.morning_litres)}</td>
      <td>${fmt(m.evening_litres)}</td>
      <td><b>${fmt(m.total_litres)}</b></td>
      <td>${m.fat_percent ? m.fat_percent+'%' : '–'}</td>
      <td>${m.snf_percent ? m.snf_percent+'%' : '–'}</td>
      <td>₹${fmt(m.milk_rate)}</td>
      <td>${fmt(m.sold_litres)}</td>
      <td>₹${fmt(m.earnings)}</td>
      <td>${m.cn || '–'}</td>
      <td>${payCell}</td>
      <td><div class="acts">
        <button class="abtn e" onclick='editMilk(${JSON.stringify(m)})'><i class="fa fa-pen"></i></button>
        <button class="abtn d" onclick="delMilk(${m.id})"><i class="fa fa-trash"></i></button>
      </div></td>
    </tr>`;
  }).join('') : `<tr class="empty"><td colspan="12">No ${animal} milk entries yet</td></tr>`;
}

function openMilk(animal, data = null) {
  document.getElementById('mkanimal').value = animal;
  document.getElementById('mkid').value     = data ? data.id : '';
  document.getElementById('mkd').value      = data ? data.entry_date : today();
  document.getElementById('mkc').value      = data ? data.customer_id || '' : '';
  document.getElementById('mkm').value      = data ? data.morning_litres : '';
  document.getElementById('mke').value      = data ? data.evening_litres : '';
  document.getElementById('mkfat').value    = data ? data.fat_percent || '' : '';
  document.getElementById('mksnf').value    = data ? data.snf_percent || '' : '';
  document.getElementById('mkrate').value   = data ? data.milk_rate : '';
  // Always fire onCustomerChange: sets correct cow/buffalo rate for selected customer
  setTimeout(() => { onCustomerChange(); }, 50);
  document.getElementById('mks').value      = data ? data.sold_litres : '';
  document.getElementById('mkco').value     = data ? data.consumed_litres : '';
  document.getElementById('mkno').value     = data ? data.notes || '' : '';
  const label = animal === 'cow' ? '🐄 Cow' : '🐃 Buffalo';
  document.getElementById('mkt').textContent = (data ? 'Edit' : 'Add') + ' ' + label + ' Milk Entry';
  updateFatSnfHints(animal);
  if (data && data.milk_rate) {
    document.getElementById('calc-rate-display').textContent = '₹' + parseFloat(data.milk_rate).toFixed(2);
  } else {
    document.getElementById('calc-rate-display').textContent = '–';
  }
  // Trigger auto-fill when editing an existing entry
  setTimeout(() => onMilkLitresChange(), 60);
  openM('milk');
}

function editMilk(data) { openMilk(data.animal_type || curAnimal, data); }

async function saveMilk() {
  const rateVal    = document.getElementById('mkrate').value;
  const supplyType = document.getElementById('mk-supply-type').value || 'retail';
  const soldLitres = parseFloat(document.getElementById('mks').value) || 0;
  const animal     = document.getElementById('mkanimal').value;
  if (soldLitres > 0 && !rateVal && supplyType === 'retail') {
    const custSel = document.getElementById('mkc');
    const opt = custSel.options[custSel.selectedIndex];
    const fallbackRate = animal === 'buffalo'
      ? parseFloat(opt && opt.dataset.bufrate || 0)
      : parseFloat(opt && opt.dataset.cowrate || 0);
    if (fallbackRate > 0) {
      document.getElementById('mkrate').value = fallbackRate;
    } else {
      const label = animal === 'buffalo' ? 'Buffalo Milk Rate' : 'Cow Milk Rate';
      showToast('Please set a ' + label + ' for this customer first (Customers page)', true);
      return;
    }
  }
  const r = await post('dairy.php', {
    action:          'save_milk',
    id:              document.getElementById('mkid').value,
    animal_type:     document.getElementById('mkanimal').value,
    entry_date:      document.getElementById('mkd').value,
    customer_id:     document.getElementById('mkc').value,
    morning_litres:  document.getElementById('mkm').value,
    evening_litres:  document.getElementById('mke').value,
    fat_percent:     document.getElementById('mkfat').value,
    snf_percent:     document.getElementById('mksnf').value,
    milk_rate:       document.getElementById('mkrate').value,
    sold_litres:     document.getElementById('mks').value,
    consumed_litres: document.getElementById('mkco').value,
    supply_type:     supplyType,
    notes:           document.getElementById('mkno').value,
  });
  if (r.ok) {
    showToast(soldLitres > 0 ? 'Entry saved! Bill created.' : 'Entry saved.');
    closeM('milk');
    loadAll();
  } else showToast(r.msg, true);
}

async function delMilk(id) {
  if (!confirm('Delete this entry?')) return;
  const r = await post('dairy.php', {action:'delete_milk', id});
  if (r.ok) { showToast('Deleted!'); loadAll(); }
}

/* ── Quick Pay with mode selection ── */
function openPayPopup(billId, btnEl, customerName, amount) {
  _payBillId  = billId;
  _payBillRow = btnEl;
  _payMode    = null;
  document.getElementById('pay-popup-label').textContent =
    (customerName ? customerName + ' — ' : '') + '₹' + fmt(amount);
  document.querySelectorAll('.pay-mode-btn').forEach(b => b.classList.remove('selected'));
  document.getElementById('pay-ref-row').style.display = 'none';
  document.getElementById('pay-popup-ref').value = '';
  document.getElementById('pay-popup-confirm').disabled = true;
  document.getElementById('pay-popup-confirm').style.opacity = '.5';
  document.getElementById('pay-popup').classList.add('open');
}

function selectPayMode(mode) {
  _payMode = mode;
  document.querySelectorAll('.pay-mode-btn').forEach(b => b.classList.remove('selected'));
  event.currentTarget.classList.add('selected');
  const needRef = mode !== 'cash';
  document.getElementById('pay-ref-row').style.display = needRef ? 'block' : 'none';
  document.getElementById('pay-popup-confirm').disabled = false;
  document.getElementById('pay-popup-confirm').style.opacity = '1';
}

function closePayPopup() {
  document.getElementById('pay-popup').classList.remove('open');
  _payBillId = null; _payBillRow = null; _payMode = null;
}

async function confirmQuickPay() {
  if (!_payBillId || !_payMode) return;
  const ref = document.getElementById('pay-popup-ref').value.trim();
  // 1. Mark bill as paid
  const r1 = await post('bills.php', {action:'quick_pay', id: _payBillId});
  if (!r1.ok) { showToast(r1.msg || 'Error', true); return; }
  // 2. Create a payment record so it shows in payments section
  const r2 = await post('payments.php', {
    action:       'save_payment',
    bill_id:      _payBillId,
    amount:       document.getElementById('pay-popup-label').textContent.split('₹')[1]?.replace(/,/g,'') || 0,
    payment_mode: _payMode,
    payment_date: today(),
    reference_no: ref,
    notes:        'Recorded from Dairy page',
  });
  const modeLabel = {cash:'💵 Cash', upi:'📱 UPI', online:'🏦 Online', cheque:'📝 Cheque'}[_payMode];
  if (_payBillRow) {
    _payBillRow.outerHTML = `<span class="pay-btn paid"><i class="fa fa-check"></i> Paid (${modeLabel})</span>`;
  }
  closePayPopup();
  showToast(`✅ Payment recorded — ${modeLabel}`);
  loadAll();
}

/* ── Settlement ── */
function openSettle() {
  const now = new Date();
  document.getElementById('settle-month').value = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
  document.getElementById('settle-preview').innerHTML = '';
  document.getElementById('confirm-settle-btn').style.display = 'none';
  openM('settle');
}

async function previewSettle() {
  const cust  = document.getElementById('settle-cust').value;
  const month = document.getElementById('settle-month').value;
  if (!month) { showToast('Select a month', true); return; }
  const r = await post('dairy.php', {action:'settle_preview', customer_id: cust, month});
  if (!r.bills || !r.bills.length) {
    document.getElementById('settle-preview').innerHTML = '<div style="text-align:center;padding:20px;color:#7a9485">No pending bills for this period.</div>';
    document.getElementById('confirm-settle-btn').style.display = 'none';
    return;
  }
  let html = `<div style="background:#fdf8ee;border-radius:10px;padding:14px;margin-top:8px">
    <div style="font-size:13px;font-weight:600;color:#1a3a2a;margin-bottom:10px">Pending Bills — ${r.bills.length} entries</div>
    <table style="width:100%;border-collapse:collapse">
      <thead><tr style="background:#e8f5ee"><th style="padding:7px 10px;font-size:11px;font-weight:600;color:#1a3a2a;text-align:left">Date</th><th style="padding:7px 10px;font-size:11px;text-align:left">Customer</th><th style="padding:7px 10px;font-size:11px;text-align:right">Amount</th></tr></thead>
      <tbody>`;
  r.bills.forEach(b => {
    html += `<tr style="border-bottom:1px solid #d4e8da"><td style="padding:7px 10px;font-size:12px">${fdate(b.sale_date)}</td><td style="padding:7px 10px;font-size:12px">${b.cn||'–'}</td><td style="padding:7px 10px;font-size:12px;text-align:right;font-weight:600">₹${fmt(b.total_amount)}</td></tr>`;
  });
  html += `</tbody><tfoot><tr style="background:#e8f5ee"><td colspan="2" style="padding:8px 10px;font-size:13px;font-weight:700">Total to Settle</td><td style="padding:8px 10px;font-size:15px;font-weight:700;color:#1a3a2a;text-align:right">₹${fmt(r.total)}</td></tr></tfoot></table></div>`;
  document.getElementById('settle-preview').innerHTML = html;
  document.getElementById('confirm-settle-btn').style.display = 'inline-flex';
}

async function confirmSettle() {
  const cust  = document.getElementById('settle-cust').value;
  const month = document.getElementById('settle-month').value;
  const r = await post('dairy.php', {action:'settle_confirm', customer_id: cust, month});
  if (r.ok) {
    showToast(`✅ ${r.count} bills marked as Paid!`);
    closeM('settle');
    loadAll();
  } else showToast(r.msg, true);
}

async function saveLivestock() {
  const r = await post('dairy.php', {action:'save_livestock', cow_count: document.getElementById('lsc').value, buffalo_count: document.getElementById('lsb').value, animal_type:'both'});
  if (r.ok) {
    showToast('Animals updated!');
    closeM('livestock');
    document.getElementById('lc').textContent = document.getElementById('lsc').value;
    document.getElementById('lb').textContent = document.getElementById('lsb').value;
  }
}

// Close pay popup on overlay click
document.getElementById('pay-popup').addEventListener('click', function(e){ if(e.target===this) closePayPopup(); });

/* ── Set Retail Milk Rates for ALL retail customers ── */
async function openSetRates() {
  // Load current rates from first retail customer as hint
  const raw = await post('customers.php', {action:'get_customers', category:'milk', customer_type:'retail'});
  const custs = Array.isArray(raw) ? raw : [];
  const cowRate  = custs.length ? (parseFloat(custs[0].retail_cow_rate)||0) : 0;
  const bufRate  = custs.length ? (parseFloat(custs[0].retail_buffalo_rate)||0) : 0;
  document.getElementById('sr-cow').value = cowRate || '';
  document.getElementById('sr-buf').value = bufRate || '';
  updateRatePreview(custs);
  openM('setrates');
  document.getElementById('sr-cow').addEventListener('input', function(){ updateRatePreview(custs); });
  document.getElementById('sr-buf').addEventListener('input', function(){ updateRatePreview(custs); });
}

function updateRatePreview(custs) {
  const cowRate = parseFloat(document.getElementById('sr-cow').value) || 0;
  const bufRate = parseFloat(document.getElementById('sr-buf').value) || 0;
  const retailCount = Array.isArray(custs) ? custs.length : 0;
  const preview = document.getElementById('sr-preview');
  if(!cowRate && !bufRate){
    preview.innerHTML = '<span style="color:#7a9485">Enter rates above to see a preview.</span>';
    return;
  }
  preview.innerHTML = '<b>' + retailCount + ' retail customer' + (retailCount!==1?'s':'') + ' will be updated:</b>'
    + (cowRate ? ' &nbsp;&#x1F404; Cow = <b>&#x20B9;' + cowRate.toFixed(2) + '/L</b>' : '')
    + (bufRate ? ' &nbsp;&#x1F403; Buffalo = <b>&#x20B9;' + bufRate.toFixed(2) + '/L</b>' : '');
}

async function saveRetailRates() {
  const cowRate = parseFloat(document.getElementById('sr-cow').value) || 0;
  const bufRate = parseFloat(document.getElementById('sr-buf').value) || 0;
  if(!cowRate && !bufRate) { showToast('Enter at least one rate', true); return; }
  const r = await post('customers.php', {action:'set_all_retail_rates', retail_cow_rate: cowRate, retail_buffalo_rate: bufRate});
  if(r.ok) {
    showToast('&#x2705; Rates updated for ' + (r.count||'all') + ' retail customers!');
    closeM('setrates');
  } else showToast(r.msg || 'Error saving rates', true);
}

loadAll();
</script>
</body></html>
