<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$companies=[];$r=$conn->query("SELECT * FROM milk_companies ORDER BY name");
while($row=$r->fetch_assoc())$companies[]=$row; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Customers — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.type-tabs{display:flex;gap:0;background:#f0f4f1;border-radius:11px;padding:4px;width:fit-content}
.ttab{padding:8px 18px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.ttab.on{background:var(--g);color:#fff}
.ctype-retail{background:#e8f5ee;color:#2d5a3d;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.ctype-company{background:#eaf3fb;color:#2471a3;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
.rate-badge{background:#f5e6c0;color:#8a6518;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
</style>
</head><body>
<?php $pg='customers'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3 data-t="nav_customers"></h3><p data-t="sub_customers"></p></div>
    <button class="btn-add" onclick="openCust()"><i class="fa fa-plus"></i> <span data-t="btn_add_cust"></span></button>
  </div>
  <div class="page">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap">
      <div class="type-tabs">
        <button class="ttab on" onclick="switchType('all',this)">All</button>
        <button class="ttab" onclick="switchType('retail',this)">🏪 Retail</button>
        <button class="ttab" onclick="switchType('company',this)">🏭 Company</button>
      </div>
      <select id="cat-filter" onchange="loadCusts()" style="padding:7px 12px;border:1.5px solid var(--bd);border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none">
        <option value="all">All Categories</option>
        <option value="milk">Milk Customers</option>
        <option value="crop">Crop Customers</option>
      </select>
    </div>
    <div class="tcard">
      <div class="thead">
        <h4 data-t="lbl_all_customers"></h4>
        <div class="srch"><i class="fa fa-search"></i><input data-t="ph_search" oninput="filter('custb',this.value)"></div>
      </div>
      <table>
        <thead><tr>
          <th data-t="col_name"></th><th data-t="col_mobile"></th>
          <th>Type</th><th>Rate / Company</th>
          <th data-t="col_category"></th><th data-t="col_added"></th><th></th>
        </tr></thead>
        <tbody id="custb"></tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="m-cust">
  <div class="modal">
    <div class="mhead"><h3 id="ct">Add Customer</h3><button class="mclose" onclick="closeM('cust')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="cid">
      <div class="fg"><label data-t="col_name"></label><input id="cn" data-t="ph_name"></div>
      <div class="row2">
        <div class="fg"><label data-t="col_mobile"></label><input id="cm" data-t="ph_mobile"></div>
        <div class="fg"><label data-t="col_category"></label>
          <select id="ccat">
            <option value="both">Both (Milk + Crop)</option>
            <option value="milk">Milk Customer</option>
            <option value="crop">Crop Customer</option>
          </select>
        </div>
      </div>
      <div class="fg"><label data-t="lbl_supply_type"></label>
        <select id="ctype" onchange="toggleTypeFields()">
          <option value="retail">🏪 Retail Customer (Fixed Rate)</option>
          <option value="company">🏭 Milk Company (FAT/SNF Rate)</option>
        </select>
      </div>
      <!-- Retail fields -->
      <div id="retail-fields">
        <div class="row2">
          <div class="fg">
            <label>🐄 Cow Milk Rate (₹/litre)</label>
            <input id="ccowrate" type="number" step="0.5" placeholder="e.g. 42">
          </div>
          <div class="fg">
            <label>🐃 Buffalo Milk Rate (₹/litre)</label>
            <input id="cbufrate" type="number" step="0.5" placeholder="e.g. 55">
          </div>
        </div>
      </div>
      <!-- Company fields -->
      <div id="company-fields" style="display:none">
        <div class="fg"><label data-t="lbl_company_name"></label>
          <select id="ccompany">
            <option value="">Select Company</option>
            <?php foreach($companies as $co):?><option value="<?=htmlspecialchars($co['name'])?>"><?=htmlspecialchars($co['name'])?></option><?php endforeach;?>
            <option value="OTHER">Other</option>
          </select>
        </div>
      </div>
      <div class="fg"><label data-t="col_address"></label><textarea id="caddr" rows="2" data-t="ph_location"></textarea></div>
      <div class="fg">
        <label data-t="lbl_password"></label>
        <input id="cpw" type="text" data-t="ph_password">
        <small style="color:#7a9485;font-size:11px">Leave blank = default mobile number</small>
      </div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('cust')" data-t="btn_cancel"></button>
      <button class="btn-save" onclick="saveCust()" data-t="btn_save"></button>
    </div>
  </div>
</div>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
let curType='all';

function switchType(type,btn){
  curType=type;
  document.querySelectorAll('.ttab').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  loadCusts();
}

function toggleTypeFields(){
  const isRetail=document.getElementById('ctype').value==='retail';
  document.getElementById('retail-fields').style.display=isRetail?'':'none';
  document.getElementById('company-fields').style.display=isRetail?'none':'';
}

async function loadCusts(){
  const d=await post('customers.php',{action:'get_customers',category:document.getElementById('cat-filter').value,customer_type:curType});
  const catTag=c=>c==='milk'?'<span class="badge b" style="font-size:10px">Milk</span>':c==='crop'?'<span class="badge g" style="font-size:10px">Crop</span>':'<span class="badge o" style="font-size:10px">Both</span>';
  const typeTag=c=>c==='company'?`<span class="ctype-company">🏭 Company</span>`:`<span class="ctype-retail">🏪 Retail</span>`;
  const rateInfo=c=>c.customer_type==='company'?(c.company_name?`<span class="ctype-company">${c.company_name}</span>`:'FAT/SNF Rate'):`<span class="rate-badge">🐄₹${c.retail_cow_rate||0} &nbsp;🐃₹${c.retail_buffalo_rate||0}/L</span>`;
  document.getElementById('custb').innerHTML=d.length?d.map(c=>`<tr>
    <td><b>${c.full_name}</b></td><td>${c.mobile}</td>
    <td>${typeTag(c.customer_type)}</td>
    <td>${rateInfo(c)}</td>
    <td>${catTag(c.category)}</td>
    <td>${fdate(c.created_at)}</td>
    <td><div class="acts">
      <button class="abtn e" onclick='editCust(${JSON.stringify(c)})'><i class="fa fa-pen"></i></button>
      <button class="abtn d" onclick="delCust(${c.id})"><i class="fa fa-trash"></i></button>
    </div></td>
  </tr>`).join(''):`<tr class="empty"><td colspan="7" data-t="empty_customers"></td></tr>`;
  applyLang();
}

function openCust(d=null){
  document.getElementById('cid').value    =d?d.id:'';
  document.getElementById('cn').value     =d?d.full_name:'';
  document.getElementById('cm').value     =d?d.mobile:'';
  document.getElementById('ccat').value   =d?d.category:'both';
  document.getElementById('ctype').value  =d?d.customer_type:'retail';
  document.getElementById('ccowrate').value  =d?d.retail_cow_rate||'':'';
  document.getElementById('cbufrate').value  =d?d.retail_buffalo_rate||'':'';
  document.getElementById('ccompany').value=d?d.company_name||'':'';
  document.getElementById('caddr').value  =d?d.address||'':'';
  document.getElementById('cpw').value    ='';
  document.getElementById('ct').textContent=d?'Edit Customer':'Add Customer';
  toggleTypeFields();
  openM('cust');
}
function editCust(d){openCust(d);}

async function saveCust(){
  const r=await post('customers.php',{action:'save_customer',
    id:document.getElementById('cid').value,
    full_name:document.getElementById('cn').value,
    mobile:document.getElementById('cm').value,
    category:document.getElementById('ccat').value,
    customer_type:document.getElementById('ctype').value,
    retail_cow_rate:document.getElementById('ccowrate').value,
    retail_buffalo_rate:document.getElementById('cbufrate').value,
    company_name:document.getElementById('ccompany').value,
    address:document.getElementById('caddr').value,
    password:document.getElementById('cpw').value});
  if(r.ok){showToast(t('toast_saved'));closeM('cust');loadCusts();}
  else showToast(r.msg,true);
}

async function delCust(id){
  if(!confirm(t('del_confirm')))return;
  await post('customers.php',{action:'delete_customer',id});
  showToast(t('toast_deleted'));loadCusts();
}
loadCusts();
</script>
</body></html>
