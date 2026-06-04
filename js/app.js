const ROOT = window.location.pathname.includes('/pages/') ? '../' : '';
const API  = ROOT + 'api/';

async function post(file, data) {
  const fd = new FormData();
  for (const k in data) fd.append(k, data[k] ?? '');
  try {
    const r = await fetch(API + file, {method:'POST', body:fd});
    const text = await r.text();
    try { return JSON.parse(text); }
    catch(e) { console.error('JSON parse error:', text); return {ok:0, msg:'Server error'}; }
  } catch(e) { showToast('Server error: ' + e.message, true); return {}; }
}

async function get(file) {
  try { return await (await fetch(API + file)).json(); }
  catch(e) { return {}; }
}

function fmt(n)   { return isNaN(n) ? '0' : (+n).toLocaleString('en-IN',{maximumFractionDigits:2}); }
function fdate(d) { return (!d || d==='0000-00-00') ? '–' : new Date(d).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'}); }
function today()  { return new Date().toISOString().split('T')[0]; }

function openM(id) { document.getElementById('m-'+id).classList.add('open'); }
function closeM(id){ document.getElementById('m-'+id).classList.remove('open'); }

function showToast(msg, err=false) {
  const el = document.getElementById('toast');
  if (!el) return;
  el.querySelector('span').textContent = msg;
  el.className = 'toast' + (err ? ' err' : '');
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 3000);
}

function filter(tbodyId, q) {
  document.getElementById(tbodyId).querySelectorAll('tr').forEach(r =>
    r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none');
}

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.overlay').forEach(o => o.addEventListener('click', e => {
    if (e.target === o) o.classList.remove('open');
  }));
});
