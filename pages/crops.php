<?php require_once '../includes/auth.php'; require_once '../includes/db.php';
$lands=[];$r=$conn->query("SELECT id,plot_number,area_acres FROM land_records ORDER BY plot_number");
while($row=$r->fetch_assoc())$lands[]=$row; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Crops — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php $pg='crops'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3 data-t="topbar_crops"></h3></div>
    <button class="btn-add" onclick="openCrop()"><i class="fa fa-plus"></i> <span data-t="btn_add_crop"></span></button>
  </div>
  <div class="page">
    <div class="tcard">
      <div class="thead">
        <h4 data-t="lbl_all_crops"></h4>
        <div class="srch"><i class="fa fa-search"></i><input data-t="ph_search" oninput="filter('cb',this.value)"></div>
      </div>
      <table><thead><tr><th data-t="col_crop"></th><th data-t="col_variety"></th><th data-t="col_land"></th><th data-t="col_sow"></th><th data-t="col_harvest"></th><th data-t="col_area"></th><th data-t="col_status"></th><th></th></tr></thead>
      <tbody id="cb"></tbody></table>
    </div>
  </div>
</main>

<div class="overlay" id="m-crop">
  <div class="modal">
    <div class="mhead"><h3 id="ct">Add Crop</h3><button class="mclose" onclick="closeM('crop')">✕</button></div>
    <div class="mbody">
      <input type="hidden" id="cid">
      <div class="row2">
        <div class="fg"><label data-t="col_crop"></label><input id="cn" placeholder="e.g. Tomato, Onion"></div>
        <div class="fg"><label data-t="col_variety"></label><input id="cv" placeholder="e.g. Hybrid F1"></div>
      </div>
      <div class="row2">
        <div class="fg"><label data-t="col_land"></label>
          <select id="cl"><option value="">-- No plot --</option><?php foreach($lands as $l):?><option value="<?=$l['id']?>"><?=htmlspecialchars($l['plot_number'])?> (<?=$l['area_acres']?> ac)</option><?php endforeach;?></select></div>
        <div class="fg"><label data-t="col_area"></label><input id="ca" type="number" step="0.01" placeholder="0.0"></div>
      </div>
      <div class="row2">
        <div class="fg"><label data-t="col_sow"></label><input id="cs" type="date"></div>
        <div class="fg"><label data-t="col_harvest"></label><input id="ch" type="date"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Actual Harvest</label><input id="cah" type="date"></div>
        <div class="fg"><label>Seed Qty (kg)</label><input id="csq" type="number" step="0.1" placeholder="0"></div>
      </div>
      <div class="row2">
        <div class="fg"><label>Water Source</label>
          <select id="cw"><option value="">Select</option><option>Drip</option><option>Canal</option><option>Borewell</option><option>Rainfed</option></select></div>
        <div class="fg"><label data-t="col_status"></label>
          <select id="cst"><option value="growing">Growing</option><option value="harvested">Harvested</option><option value="failed">Failed</option></select></div>
      </div>
      <div class="fg"><label>Fertilizer Used</label><input id="cf" placeholder="e.g. Urea, DAP"></div>
      <div class="fg"><label>Notes</label><textarea id="cno" rows="2" placeholder="Notes"></textarea></div>
    </div>
    <div class="mfoot">
      <button class="btn-cancel" onclick="closeM('crop')" data-t="btn_cancel"></button>
      <button class="btn-save" onclick="saveCrop()" data-t="btn_save"></button>
    </div>
  </div>
</div>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script>
const statusBadge=s=>s==='growing'?'g':s==='harvested'?'o':'r';
async function loadCrops(){
  const d=await post('crops.php',{action:'get_crops'});
  document.getElementById('cb').innerHTML=d.length?d.map(c=>`<tr><td><b>${c.crop_name}</b></td><td>${c.variety||'–'}</td><td>${c.plot_number||'–'}</td><td>${fdate(c.sowing_date)}</td><td>${fdate(c.expected_harvest)}</td><td>${c.area_acres?c.area_acres+' ac':'–'}</td><td><span class="badge ${statusBadge(c.status)}">${c.status}</span></td><td><div class="acts"><button class="abtn e" onclick='editCrop(${JSON.stringify(c)})'><i class="fa fa-pen"></i></button><button class="abtn d" onclick="delCrop(${c.id})"><i class="fa fa-trash"></i></button></div></td></tr>`).join(''):`<tr class="empty"><td colspan="8" data-t="empty_crops"></td></tr>`;
  applyLang();
}

function openCrop(d=null){
  document.getElementById('cid').value=d?d.id:'';
  document.getElementById('cn').value=d?d.crop_name:'';
  document.getElementById('cv').value=d?d.variety||'':'';
  document.getElementById('cl').value=d?d.land_id||'':'';
  document.getElementById('ca').value=d?d.area_acres||'':'';
  document.getElementById('cs').value=d?d.sowing_date||'':'';
  document.getElementById('ch').value=d?d.expected_harvest||'':'';
  document.getElementById('cah').value=d?d.actual_harvest||'':'';
  document.getElementById('csq').value=d?d.seed_qty_kg||'':'';
  document.getElementById('cw').value=d?d.water_source||'':'';
  document.getElementById('cst').value=d?d.status:'growing';
  document.getElementById('cf').value=d?d.fertilizer_used||'':'';
  document.getElementById('cno').value=d?d.notes||'':'';
  document.getElementById('ct').textContent=d?'Edit Crop':'Add Crop';
  openM('crop');
}
function editCrop(d){openCrop(d);}

async function saveCrop(){
  const r=await post('crops.php',{action:'save_crop',id:document.getElementById('cid').value,crop_name:document.getElementById('cn').value,variety:document.getElementById('cv').value,land_id:document.getElementById('cl').value,area_acres:document.getElementById('ca').value,sowing_date:document.getElementById('cs').value,expected_harvest:document.getElementById('ch').value,actual_harvest:document.getElementById('cah').value,seed_qty_kg:document.getElementById('csq').value,water_source:document.getElementById('cw').value,status:document.getElementById('cst').value,fertilizer_used:document.getElementById('cf').value,notes:document.getElementById('cno').value});
  if(r.ok){showToast(t('toast_saved'));closeM('crop');loadCrops();} else showToast(r.msg,true);
}

async function delCrop(id){
  if(!confirm(t('del_confirm')))return;
  const r=await post('crops.php',{action:'delete_crop',id});
  if(r.ok){showToast(t('toast_deleted'));loadCrops();}
}
loadCrops();
</script></body></html>
