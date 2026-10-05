/* Sowgatly admin — behaviour (no jQuery). */
(function () {
  'use strict';
  var R = document.documentElement;
  function store(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }

  // Theme toggle (initial theme is set inline in <head> to avoid a flash).
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-theme-toggle]');
    if (t) { R.dataset.theme = R.dataset.theme === 'dark' ? 'light' : 'dark'; store('sg-theme', R.dataset.theme); }

    var c = e.target.closest('[data-collapse]');
    if (c) {
      if ('collapsed' in R.dataset) { delete R.dataset.collapsed; store('sg-collapsed', '0'); }
      else { R.dataset.collapsed = ''; store('sg-collapsed', '1'); }
    }

    var d = e.target.closest('[data-drawer]');
    if (d) { if ('drawerOpen' in R.dataset) delete R.dataset.drawerOpen; else R.dataset.drawerOpen = ''; }

    // close <details class="menu"> when clicking elsewhere
    document.querySelectorAll('details.menu[open]').forEach(function (m) { if (!m.contains(e.target)) m.removeAttribute('open'); });

    var x = e.target.closest('[data-dismiss-toast]');
    if (x) hideToast(x.closest('.toast'));
  });

  // Toasts
  function hideToast(el) { if (!el) return; el.classList.add('hide'); setTimeout(function () { el.remove(); }, 260); }
  document.querySelectorAll('.toast').forEach(function (el) { setTimeout(function () { hideToast(el); }, 5000); });

  // Confirm dialog for destructive forms: <form data-confirm="Text">
  var dlg = document.getElementById('confirm-dialog'), pending = null;
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f.matches('form[data-confirm]') || f.dataset.confirmed) return;
    e.preventDefault();
    if (!dlg || !dlg.showModal) { if (confirm(f.dataset.confirm)) { f.dataset.confirmed = '1'; f.submit(); } return; }
    pending = f;
    dlg.querySelector('[data-confirm-text]').textContent = f.dataset.confirm;
    dlg.showModal();
  });
  if (dlg) {
    dlg.addEventListener('close', function () {
      if (dlg.returnValue === 'ok' && pending) { pending.dataset.confirmed = '1'; pending.submit(); }
      pending = null; dlg.returnValue = '';
    });
  }

  // Live tables: <form data-table-form> drives the #datatable partial that
  // the admin controllers return for AJAX requests. Falls back to a normal
  // navigation when the response is a full page.
  var table = document.getElementById('datatable');
  var tform = document.querySelector('form[data-table-form]');
  var timer = null, seq = 0;
  function load(url, push) {
    if (!table) { location.href = url; return; }
    var my = ++seq;
    table.classList.add('loading');
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
      .then(function (r) { return r.text(); })
      .then(function (html) {
        if (my !== seq) return;
        if (/<html[\s>]/i.test(html)) { location.href = url; return; }
        table.innerHTML = html;
        table.classList.remove('loading');
        if (push) history.replaceState(null, '', url);
      })
      .catch(function () { location.href = url; });
  }
  function formUrl() {
    var u = new URL(tform.getAttribute('action') || location.href, location.href);
    var p = new URLSearchParams(new FormData(tform));
    Array.from(p.keys()).forEach(function (k) { if (p.get(k) === '') p.delete(k); });
    u.search = p.toString();
    return u.toString();
  }
  if (tform) {
    tform.addEventListener('submit', function (e) { e.preventDefault(); load(formUrl(), true); });
    tform.addEventListener('input', function (e) {
      if (e.target.matches('input[type=search],input[type=text]')) { clearTimeout(timer); timer = setTimeout(function () { load(formUrl(), true); }, 300); }
    });
    tform.addEventListener('change', function (e) { if (e.target.matches('select,input[type=radio],input[type=checkbox]')) load(formUrl(), true); });
  }
  if (table) {
    table.addEventListener('click', function (e) {
      var a = e.target.closest('.pager a[href]');
      if (!a) return;
      e.preventDefault(); load(a.href, true); window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Image previews: <input type=file data-preview="#el">
  document.addEventListener('change', function (e) {
    var inp = e.target;
    if (!inp.matches('input[type=file][data-preview]')) return;
    var box = document.querySelector(inp.dataset.preview);
    if (!box) return;
    box.innerHTML = '';
    Array.from(inp.files || []).forEach(function (file) {
      if (!/^image\//.test(file.type)) return;
      var img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      box.appendChild(img);
    });
    var label = inp.closest('.dropzone') && inp.closest('.dropzone').querySelector('[data-file-label]');
    if (label && inp.files.length) label.textContent = inp.files.length === 1 ? inp.files[0].name : inp.files.length + ' ✓';
  });
})();
