<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Farmers — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php $pg='farmers'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3 data-t="topbar_farmers"></h3><p data-t="sub_farmers"></p></div>
    <button class="btn-add" onclick="openFarmer()"><i class="fa fa-plus"></i> <span data-t="btn_add_farmer"></span></button>
  </div>
  <div class="page">
    <div style="background:#e8f5ee;border-radius:10px;padding:12px 16px;font-size:13px;color:#1a3a2a;margin-bottom:20px;display:flex;align-items:center;gap:8px">
      <i class="fa fa-shield-alt"></i>
      You are the <b>Farm Director</b>. You can add/edit/remove farmers and promote others to Farm Director.
    </div>
    <div class="tcard">
      <div class="thead">
        <h4 data-t="nav_farmers"></h4>
        <div class="srch"><i class="fa fa-search"></i><input data-t="ph_search" oninput="filter('fb',this.value)"></div>
      </div>
      <table>
        <thead><tr>
          <th data-t="col_name"></th><th>Role</th><th data-t="col_mobile"></th>
          <th data-t="lbl_village"></th><th data-t="lbl_district"></th>
          <th data-t="col_added"></th><th data-t="col_actions"></th>
        </tr></thead>
        <tbody id="fb"></tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="m-farmer">
  <div class="modal">
    <div class="mhead"><h3 id="ft">Add Farmer</h3><button class="mclose" onclick="closeM('farmer')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="fid">
      <div class="row2">
        <div class="fg"><label data-t="lbl_full_name"></label><input id="fn" placeholder="Full name"></div>
        <div class="fg"><label data-t="lbl_mobile"></label><input id="fm" placeholder="Mobile"></div>
      </div>
      <div class="fg"><label data-t="lbl_email"></label><input id="fe" type="email" placeholder="Email"></div>
      <div class="row2">
        <div class="fg"><label data-t="lbl_village"></label><input id="fv" placeholder="Village"></div>
        <div class="fg"><label data-t="lbl_district"></label><input id="fd" placeholder="District"></div>
      </div>
      <div class="fg">
        <label>Role</label>
        <select id="frole">
          <option value="farmer">🌾 Farmer</option>
          <option value="superadmin">⭐ Farm Director (Super Admin)</option>
        </select>
      </div>
      <div class="fg">
        <label data-t="lbl_password"></label>
        <input id="fp" type="text" placeholder="Password (leave blank to keep)">
      </div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('farmer')" data-t="btn_cancel"></button>
      <button class="btn-save" onclick="saveFarmer()" data-t="btn_save"></button>
    </div>
  </div>
</div>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
const myId=<?=intval($_SESSION['admin']['id'])?>;

async function loadFarmers(){
  const d=await post('farmers.php',{action:'get_farmers'});
  document.getElementById('fb').innerHTML=d.length?d.map(f=>{
    const isSA=f.role==='superadmin';
    const roleBadge=isSA?'<span class="badge b" style="font-size:10px">⭐ Farm Director</span>':'<span class="badge g" style="font-size:10px">🌾 Farmer</span>';
    const youBadge=f.id==myId?' <span class="badge o" style="font-size:10px">You</span>':'';
    return `<tr>
      <td><b>${f.full_name}</b>${youBadge}</td>
      <td>${roleBadge}</td><td>${f.mobile}</td>
      <td>${f.village||'–'}</td><td>${f.district||'–'}</td>
      <td>${fdate(f.created_at)}</td>
      <td><div class="acts">
        <button class="abtn e" onclick='editFarmer(${JSON.stringify(f)})'><i class="fa fa-pen"></i></button>
        ${f.id!=myId?`<button class="abtn d" onclick="delFarmer(${f.id})"><i class="fa fa-trash"></i></button>`:''}
      </div></td>
    </tr>`;
  }).join(''):`<tr class="empty"><td colspan="7">No farmers added yet</td></tr>`;
}

function openFarmer(d=null){
  document.getElementById('fid').value   =d?d.id:'';
  document.getElementById('fn').value    =d?d.full_name:'';
  document.getElementById('fm').value    =d?d.mobile:'';
  document.getElementById('fe').value    =d?d.email||'':'';
  document.getElementById('fv').value    =d?d.village||'':'';
  document.getElementById('fd').value    =d?d.district||'':'';
  document.getElementById('frole').value =d?d.role:'farmer';
  document.getElementById('fp').value    ='';
  document.getElementById('ft').textContent=d?'Edit Farmer':'Add Farmer';
  openM('farmer');
}
function editFarmer(d){openFarmer(d);}

async function saveFarmer(){
  const r=await post('farmers.php',{action:'save_farmer',
    id:document.getElementById('fid').value,
    full_name:document.getElementById('fn').value,
    mobile:document.getElementById('fm').value,
    email:document.getElementById('fe').value,
    village:document.getElementById('fv').value,
    district:document.getElementById('fd').value,
    role:document.getElementById('frole').value,
    password:document.getElementById('fp').value});
  if(r.ok){showToast(t('toast_saved'));closeM('farmer');loadFarmers();}
  else showToast(r.msg,true);
}

async function delFarmer(id){
  if(!confirm(t('del_confirm')))return;
  const r=await post('farmers.php',{action:'delete_farmer',id});
  if(r.ok){showToast(t('toast_deleted'));loadFarmers();}
  else showToast(r.msg,true);
}
loadFarmers();
</script>
</body></html>
