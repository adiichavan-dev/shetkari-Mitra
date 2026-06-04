<?php require_once '../includes/auth.php'; require_once '../includes/db.php'; ?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Rate Management — Shetkari Mitra</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.rate-table-wrap{overflow-x:auto;margin-bottom:20px}
.rate-tbl{border-collapse:collapse;font-size:12px;min-width:500px}
.rate-tbl th{background:var(--g);color:#fff;padding:7px 10px;text-align:center;font-weight:600}
.rate-tbl td{padding:3px 2px;text-align:center;border:1px solid var(--bd)}
.rate-tbl td:first-child{background:var(--gl);font-weight:600;color:var(--g);padding:4px 10px;position:sticky;left:0}
.rate-tbl input{width:54px;padding:4px 2px;text-align:center;border:1px solid var(--bd);border-radius:5px;font-size:12px;outline:none}
.rate-tbl input:focus{border-color:var(--gm);background:#f0f9f4}
.rate-tbl.buf th{background:var(--bl)}
.rate-tbl.buf td:first-child{background:var(--bll);color:var(--bl)}
.rate-tbl.buf input:focus{border-color:var(--bl);background:#f0f6fb}
.atab{padding:9px 22px;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:500;color:#7a9485;background:none;font-family:'DM Sans',sans-serif;transition:all .2s}
.atab.cow.on{background:var(--g);color:#fff}
.atab.buf.on{background:var(--bl);color:#fff}
.rpanel{display:none}.rpanel.on{display:block}
.info-bar{padding:11px 14px;border-radius:9px;font-size:13px;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.info-bar.cow{background:var(--gl);color:var(--g)}
.info-bar.buf{background:var(--bll);color:var(--bl)}
</style>
</head><body>
<?php $pg='rates'; include '../includes/sidebar.php'; ?>
<main class="main">
  <div class="topbar">
    <div><h3 data-t="nav_rates"></h3><p data-t="sub_rates"></p></div>
    <div style="display:flex;gap:8px">
      <button class="btn-add" style="background:#888;padding:9px 14px;font-size:13px" onclick="resetRates()"><i class="fa fa-undo"></i> <span data-t="btn_reset_rates"></span></button>
      <button class="btn-add" onclick="saveRates()"><i class="fa fa-save"></i> <span data-t="btn_update_rates"></span></button>
    </div>
  </div>
  <div class="page">
    <div style="display:flex;gap:4px;background:#f0f4f1;border-radius:11px;padding:4px;width:fit-content;margin-bottom:18px">
      <button class="atab cow on" onclick="switchTab('cow',this)">🐄 Cow Milk Rates</button>
      <button class="atab buf"    onclick="switchTab('buf',this)">🐃 Buffalo Milk Rates</button>
    </div>

    
    <div class="rpanel on" id="panel-cow">
      <div class="info-bar cow" style="flex-direction:column;align-items:flex-start;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px">
          🐄 <span data-t="lbl_cow_rates"></span>
        </div>
        <div style="font-size:12px;color:#2d5a3d">
          <b>FAT range:</b> 3.0 – 5.0 &nbsp;|&nbsp; <b>SNF range:</b> 8.2 – 9.0 &nbsp;
        </div>
      </div>
      <div class="tcard">
        <div class="thead" style="justify-content:space-between">
          <h4>🐄 Cow Milk Rate Chart (₹ per litre)</h4>
          <span style="font-size:12px;color:#7a9485">Row = FAT% &nbsp;|&nbsp; Column = SNF%</span>
        </div>
        <div class="rate-table-wrap"><table class="rate-tbl" id="cow-tbl"></table></div>
      </div>
    </div>

    
    <div class="rpanel" id="panel-buf">
      <div class="info-bar buf" style="flex-direction:column;align-items:flex-start;gap:6px">
        <div style="display:flex;align-items:center;gap:8px;font-weight:700;font-size:14px">
          🐃 <span data-t="lbl_buf_rates"></span>
        </div>
        <div style="font-size:12px;color:#1a5276">
          <b>FAT range:</b> 4.5 – 10.0 &nbsp;|&nbsp; <b>SNF range:</b> 8.6 – 10.0 &nbsp;
        </div>
        </div>
      </div>
      <div class="tcard">
        <div class="thead" style="justify-content:space-between">
          <span style="font-size:12px;color:#7a9485">Row = FAT% &nbsp;|&nbsp; Column = SNF%</span>
        </div>
        <div class="rate-table-wrap"><table class="rate-tbl buf" id="buf-tbl"></table></div>
      </div>
    </div>
  </div>
</main>
<div class="toast" id="toast"><i class="fa fa-check-circle"></i><span></span></div>
<script src="../js/app.js"></script>
<script src="../js/lang.js"></script>
<script src="../js/rates.js"></script>
<script>
let cowData=JSON.parse(JSON.stringify(COW_RATES));
let bufData=JSON.parse(JSON.stringify(BUFFALO_RATES_BASE));
let curAnimal='cow';
function switchTab(type,btn){
  curAnimal=type;
  document.querySelectorAll('.atab').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('panel-cow').classList.toggle('on',type==='cow');
  document.getElementById('panel-buf').classList.toggle('on',type==='buf');
}

async function loadRates(){
    // Try to load custom rates from server
  for(const at of ['cow','buffalo']){
    const r=await post('rates.php',{action:'get_rates',animal_type:at});
    if(r.has_custom&&r.rates.length){
      // Build lookup from server rates
      const isCow=(at==='cow');
      const lookup={};
      r.rates.forEach(row=>{
        const k=parseFloat(row.fat_value).toFixed(1);
        if(!lookup[k]) lookup[k]={};
        const snfCols=isCow?COW_SNF_COLS:BUFFALO_SNF_COLS;
        const idx=snfCols.findIndex(s=>Math.abs(s-parseFloat(row.snf_value))<0.05);
        if(idx>=0) lookup[k][idx]=parseFloat(row.rate);
      });
      if(isCow){
        for(const fat in cowData) if(lookup[fat]) for(const i in lookup[fat]) cowData[fat][i]=lookup[fat][i];
      } else {
        for(const fat in bufData) if(lookup[fat]) for(const i in lookup[fat]) bufData[fat][i]=lookup[fat][i];
      }
    }
  }
  buildTables();
}

function buildTables(){
  buildTable('cow-tbl',cowData,COW_SNF_COLS,false);
  buildTable('buf-tbl',bufData,BUFFALO_SNF_COLS,true);
}

function buildTable(id,data,cols,isBuf){
  const tbl=document.getElementById(id);
  const keys=Object.keys(data).sort((a,b)=>parseFloat(a)-parseFloat(b));
  let html='<thead><tr><th>FAT\\SNF</th>'+cols.map(s=>`<th>${s.toFixed(1)}</th>`).join('')+'</tr></thead><tbody>';
  keys.forEach(fat=>{
    html+=`<tr><td>${fat}</td>`;
    cols.forEach((snf,i)=>{
      const base=data[fat][i]!==undefined?data[fat][i]:0;
      const disp=isBuf?Math.round((base+3)*100)/100:base;
      html+=`<td><input type="number" step="0.01" value="${disp}" data-fat="${fat}" data-idx="${i}" class="${isBuf?'buf-i':'cow-i'}"></td>`;
    });
    html+='</tr>';
  });
  html+='</tbody>';
  tbl.innerHTML=html;
}

async function saveRates(){
    // Collect cow rates
  const cowRates=[];
  document.querySelectorAll('.cow-i').forEach(inp=>{
    const fat=inp.dataset.fat;const idx=parseInt(inp.dataset.idx);
    const snf=COW_SNF_COLS[idx];const rate=parseFloat(inp.value)||0;
    if(rate>0) cowRates.push({fat,snf:snf.toFixed(1),rate});
    COW_RATES[fat][idx]=rate; cowData[fat][idx]=rate;
  });
  const bufRates=[];
  document.querySelectorAll('.buf-i').forEach(inp=>{
    const fat=inp.dataset.fat;const idx=parseInt(inp.dataset.idx);
    const snf=BUFFALO_SNF_COLS[idx];const displayRate=parseFloat(inp.value)||0;
    const baseRate=Math.round((displayRate-3)*100)/100;
    if(displayRate>0) bufRates.push({fat,snf:snf.toFixed(1),rate:displayRate});
    BUFFALO_RATES_BASE[fat][idx]=baseRate; bufData[fat][idx]=baseRate;
  });
  const r1=await post('rates.php',{action:'save_rates',animal_type:'cow',  rates_json:JSON.stringify(cowRates)});
  const r2=await post('rates.php',{action:'save_rates',animal_type:'buffalo',rates_json:JSON.stringify(bufRates)});
  if(r1.ok&&r2.ok){showToast(t('toast_saved'));}
  else showToast('Save failed',true);
}

async function resetRates(){
  if(!confirm('Reset to default rate chart values?'))return;
    await post('rates.php',{action:'reset_rates',animal_type:'cow'});
  await post('rates.php',{action:'reset_rates',animal_type:'buffalo'});
  cowData=JSON.parse(JSON.stringify(COW_RATES));
  bufData=JSON.parse(JSON.stringify(BUFFALO_RATES_BASE));
  buildTables();
  showToast('Reset to defaults!');
}

loadRates();
</script>
</body></html>
