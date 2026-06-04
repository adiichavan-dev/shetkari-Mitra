<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
// Fetch admin row — try by id first, fallback to mobile
$row = null;
if(!empty($admin['id'])){
    $r=$conn->query("SELECT * FROM admin WHERE id=".intval($admin['id'])." LIMIT 1");
    if($r) $row=$r->fetch_assoc();
}
if(!$row && !empty($admin['mobile'])){
    $m=$conn->real_escape_string($admin['mobile']);
    $r=$conn->query("SELECT * FROM admin WHERE mobile='$m' LIMIT 1");
    if($r) $row=$r->fetch_assoc();
}
if(!$row) $row=$admin; // last fallback: use session data directly
// Refresh session with latest DB data
if($row && $row !== $admin) $_SESSION['admin'] = array_merge($_SESSION['admin'], $row);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profile — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.section-card{background:#fff;border-radius:var(--r);padding:24px;border:1px solid var(--bd);box-shadow:var(--shadow-sm)}
.section-card h4{font-family:'Playfair Display',serif;font-size:16px;color:var(--g);margin-bottom:18px;padding-bottom:10px;border-bottom:1px solid var(--bd)}
.avatar-wrap{text-align:center;margin-bottom:20px}
.avatar-big{width:80px;height:80px;background:var(--g);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:32px;color:var(--go);font-weight:700;margin-bottom:8px}
.info-name{font-family:'Playfair Display',serif;font-size:20px;color:var(--g)}
.info-role{font-size:12px;color:var(--ts);margin-top:3px}
@media(max-width:768px){.profile-grid{grid-template-columns:1fr}}
</style>
</head><body>
<?php $pg='profile'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar"><div><h3 data-t="topbar_profile"></h3><p data-t="sub_profile"></p></div></div>
  <div class="page">
    <div class="profile-grid">

      
      <div>
        <div class="section-card" style="margin-bottom:20px">
          <div class="avatar-wrap">
            <div class="avatar-big"><?=strtoupper(substr($row['full_name'],0,1))?></div>
            <div class="info-name"><?=htmlspecialchars($row['full_name'])?></div>
            <div class="info-role">🌾 Farmer &nbsp;|&nbsp; <?=htmlspecialchars($row['mobile'])?></div>
          </div>
          <h4>Basic Information</h4>
          <div class="row2">
            <div class="fg"><label data-t="lbl_full_name"></label><input id="pn" value="<?=htmlspecialchars($row['full_name'])?>"></div>
            <div class="fg"><label data-t="lbl_email"></label><input id="pe" type="email" value="<?=htmlspecialchars($row['email']??'')?>"></div>
          </div>
          <div class="row2">
            <div class="fg"><label data-t="lbl_village"></label><input id="pv" value="<?=htmlspecialchars($row['village']??'')?>"></div>
            <div class="fg"><label data-t="lbl_taluka"></label><input id="pt" value="<?=htmlspecialchars($row['taluka']??'')?>"></div>
          </div>
          <div class="fg"><label data-t="lbl_district"></label><input id="pd" value="<?=htmlspecialchars($row['district']??'')?>"></div>
          <button class="btn-primary" onclick="saveProfile()" data-t="btn_update"></button>
        </div>

        
        <div class="section-card" style="margin-bottom:20px">
          <h4>📱 Change Mobile Number</h4>
          <div class="fg"><label data-t="lbl_new_mobile"></label><input id="mob-new" type="text" placeholder="New 10-digit mobile number"></div>
          <div class="fg"><label data-t="lbl_current_password"></label><input id="mob-cur-pw" type="password" placeholder="Enter current password"></div>
          <button class="btn-primary" onclick="updateMobile()" data-t="btn_update_mobile"></button>
        </div>

        
        <div class="section-card">
          <h4>🔒 Change Password</h4>
          <div class="fg"><label data-t="lbl_current_password"></label><input id="pw-cur" type="password" placeholder="Current password"></div>
          <div class="fg"><label data-t="lbl_new_password"></label><input id="pw-new" type="password" placeholder="New password (min 6 chars)"></div>
          <div class="fg"><label data-t="lbl_confirm_password"></label><input id="pw-conf" type="password" placeholder="Confirm new password"></div>
          <button class="btn-primary" onclick="updatePassword()" data-t="btn_update_password"></button>
        </div>
      </div>


      <div>
        <div class="tcard">
          <div class="thead">
            <h4 data-t="lbl_land"></h4>
            <button class="btn-add" onclick="openLand()"><i class="fa fa-plus"></i> <span data-t="btn_add_land"></span></button>
          </div>
          <table>
            <thead><tr><th data-t="col_plot"></th><th data-t="col_area"></th><th data-t="col_location"></th><th data-t="col_soil"></th><th data-t="col_irr"></th><th></th></tr></thead>
            <tbody id="lb"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="overlay" id="m-land">
  <div class="modal">
    <div class="mhead"><h3 id="lt">Add Land</h3><button class="mclose" onclick="closeM('land')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="lid">
      <div class="row2">
        <div class="fg"><label data-t="col_plot"></label><input id="lp" data-t="ph_plot"></div>
        <div class="fg"><label data-t="col_area"></label><input id="la" type="number" step="0.01" data-t="ph_area"></div>
      </div>
      <div class="fg"><label data-t="col_location"></label><input id="ll" data-t="ph_location"></div>
      <div class="row2">
        <div class="fg"><label data-t="col_soil"></label>
          <select id="ls"><option value="">Select</option><option>Black Cotton</option><option>Red Laterite</option><option>Alluvial</option><option>Sandy Loam</option><option>Clay</option></select>
        </div>
        <div class="fg"><label data-t="col_irr"></label>
          <select id="li"><option value="">Select</option><option>Drip</option><option>Sprinkler</option><option>Canal</option><option>Borewell</option><option>Rainfed</option></select>
        </div>
      </div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('land')" data-t="btn_cancel"></button>
      <button class="btn-save" onclick="saveLand()" data-t="btn_save"></button>
    </div>
  </div>
</div>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
function onLangChange(){
  document.querySelectorAll('[data-t]').forEach(el=>{
    if(el.tagName==='INPUT'||el.tagName==='TEXTAREA') el.placeholder=t(el.dataset.t);
  });
}

async function saveProfile(){
  const r=await post('profile.php',{action:'update_profile',
    full_name:document.getElementById('pn').value,
    email:document.getElementById('pe').value,
    village:document.getElementById('pv').value,
    taluka:document.getElementById('pt').value,
    district:document.getElementById('pd').value});
  if(r.ok) showToast(t('toast_updated'));
  else showToast(r.msg,true);
}

async function updateMobile(){
  const curpw = document.getElementById('mob-cur-pw').value;
  const newmob = document.getElementById('mob-new').value.trim();
  if(!curpw||!newmob){showToast('Fill all fields',true);return;}
  if(!/^\d{10}$/.test(newmob)){showToast('Enter valid 10-digit mobile',true);return;}
  const r=await post('profile.php',{action:'update_mobile',current_password:curpw,new_mobile:newmob});
  if(r.ok){showToast('Mobile number updated!');document.getElementById('mob-cur-pw').value='';document.getElementById('mob-new').value='';}
  else showToast(r.msg,true);
}

async function updatePassword(){
  const cur=document.getElementById('pw-cur').value;
  const nw =document.getElementById('pw-new').value;
  const cf =document.getElementById('pw-conf').value;
  if(!cur||!nw||!cf){showToast('Fill all fields',true);return;}
  if(nw.length<6){showToast('Password must be at least 6 characters',true);return;}
  if(nw!==cf){showToast('Passwords do not match',true);return;}
  const r=await post('profile.php',{action:'update_password',current_password:cur,new_password:nw});
  if(r.ok){showToast('Password updated!');document.getElementById('pw-cur').value='';document.getElementById('pw-new').value='';document.getElementById('pw-conf').value='';}
  else showToast(r.msg,true);
}

async function loadLands(){
  const d=await post('profile.php',{action:'get_lands'});
  document.getElementById('lb').innerHTML=d.length?d.map(l=>`<tr>
    <td><b>${l.plot_number}</b></td><td>${l.area_acres} ac</td>
    <td>${l.location||'–'}</td><td>${l.soil_type||'–'}</td><td>${l.irrigation_type||'–'}</td>
    <td><div class="acts">
      <button class="abtn e" onclick='editLand(${JSON.stringify(l)})'><i class="fa fa-pen"></i></button>
      <button class="abtn d" onclick="delLand(${l.id})"><i class="fa fa-trash"></i></button>
    </div></td></tr>`).join(''):`<tr class="empty"><td colspan="6" data-t="empty_land"></td></tr>`;
  applyLang();
}

function openLand(d=null){
  document.getElementById('lid').value=d?d.id:'';
  document.getElementById('lp').value=d?d.plot_number:'';
  document.getElementById('la').value=d?d.area_acres:'';
  document.getElementById('ll').value=d?d.location:'';
  document.getElementById('ls').value=d?d.soil_type:'';
  document.getElementById('li').value=d?d.irrigation_type:'';
  document.getElementById('lt').textContent=d?t('btn_update'):'Add Land';
  openM('land');
}
function editLand(d){openLand(d);}

async function saveLand(){
  const r=await post('profile.php',{action:'save_land',id:document.getElementById('lid').value,
    plot_number:document.getElementById('lp').value,area_acres:document.getElementById('la').value,
    location:document.getElementById('ll').value,soil_type:document.getElementById('ls').value,
    irrigation_type:document.getElementById('li').value});
  if(r.ok){showToast(t('toast_saved'));closeM('land');loadLands();}
  else showToast(r.msg,true);
}

async function delLand(id){
  if(!confirm(t('del_confirm')))return;
  await post('profile.php',{action:'delete_land',id});
  showToast(t('toast_deleted'));loadLands();
}
loadLands();
</script>
</body></html>
