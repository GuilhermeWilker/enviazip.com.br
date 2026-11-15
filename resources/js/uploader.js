import { Zip } from './zip';

(() => {
  const root = document.getElementById('uploader');
  if (!root) return;

  const $ = (s) => root.querySelector(s);
  const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
  const maxMb = Number(root.dataset.maxMb);
  const maxEntries = Number(root.dataset.maxEntries) || 2000;
  const input = $('#u-input');
  let file = null;
  let state = 'empty';

  const setState = (s) => {
    state = s;
    root.querySelectorAll('[data-s]').forEach((el) => (el.hidden = el.dataset.s !== s));
  };
  const clearError = () => ($('#u-error').hidden = true);
  const fail = (msg, upsell = false) => {
    $('#u-error-text').textContent = msg + ' ';
    $('#u-upsell').hidden = !upsell;
    $('#u-error').hidden = false;
  };
  const setBar = (p) => { $('#u-bar').value = p; $('#u-pct').textContent = `${p}%`; };

  async function api(url, body) {
    const r = await fetch(url, {
      method: 'POST',
      body: JSON.stringify(body),
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
    });
    const j = await r.json().catch(() => ({}));
    if (!r.ok) throw new Error(j.message || 'Algo deu errado. Tente de novo.');
    return j;
  }

  function put(url, headers, f, onProgress) {
    return new Promise((resolve, reject) => {
      const x = new XMLHttpRequest();
      x.open('PUT', url);
      for (const [k, v] of Object.entries(headers || {})) {
        if (k.toLowerCase() !== 'host') x.setRequestHeader(k, Array.isArray(v) ? v[0] : v);
      }
      x.upload.onprogress = (e) => e.lengthComputable && onProgress(Math.round((e.loaded / e.total) * 100));
      x.onload = () => (x.status < 300 ? resolve() : reject(new Error('Falha ao enviar o arquivo.')));
      x.onerror = () => reject(new Error('Falha de conexão durante o envio.'));
      x.send(f);
    });
  }

  async function pick(f) {
    clearError();
    if (!f) return;
    if (!f.name.toLowerCase().endsWith('.zip')) return fail('Escolha um arquivo .zip.');
    if (f.size > maxMb * 1024 * 1024) {
      return fail(`Esse arquivo tem ${Zip.fmt(f.size)} e o limite grátis é ${maxMb} MB.`, true);
    }
    try {
      const entries = await Zip.readManifest(f, maxEntries);
      const files = entries.filter((e) => !e.path.endsWith('/'));
      $('#u-name').textContent = f.name;
      $('#u-meta').textContent = `${Zip.fmt(f.size)} · ${files.length} arquivo${files.length === 1 ? '' : 's'}`;
      $('#u-tree').replaceChildren(Zip.renderTree(Zip.buildTree(entries)));
      file = f;
      setState('ready');
    } catch (e) {
      fail(e.message);
    }
  }

  // escolher / trocar / arrastar
  $('#u-pick').onclick = () => input.click();
  $('#u-change').onclick = () => input.click();
  input.onchange = () => { pick(input.files[0]); input.value = ''; };

  root.addEventListener('dragover', (e) => {
    if (state === 'sending' || state === 'done') return;
    e.preventDefault();
    root.dataset.over = '';
  });
  root.addEventListener('dragleave', (e) => { if (!root.contains(e.relatedTarget)) delete root.dataset.over; });
  root.addEventListener('drop', (e) => {
    e.preventDefault();
    delete root.dataset.over;
    if (state === 'sending' || state === 'done') return;
    pick(e.dataTransfer.files[0]);
  });

  // enviar
  $('#u-send').onclick = async () => {
    if (!file) return;
    clearError();
    setBar(0);
    $('#u-status').textContent = 'Preparando…';
    setState('sending');
    try {
      const token = document.querySelector('[name=cf-turnstile-response]')?.value || '';
      const days = Number($('#u-days').value);
      const init = await api('/api/uploads', { name: file.name, size: file.size, days, 'cf-turnstile-response': token });

      $('#u-status').textContent = 'Enviando…';
      await put(init.url, init.headers, file, setBar);

      $('#u-status').textContent = 'Finalizando…';
      const fin = await api(`/api/uploads/${init.id}/finish`, { delete_token: init.delete_token });

      $('#u-link').value = fin.url;
      $('#u-manage').href = `${fin.url}?t=${init.delete_token}`;
      $('#u-exp').textContent = days === 1 ? '1 dia' : `${days} dias`;
      setState('done');
    } catch (e) {
      setState('ready');
      fail(e.message);
    } finally {
      window.turnstile?.reset();
    }
  };

  // link pronto
  $('#u-copy').onclick = async () => {
    const btn = $('#u-copy');
    try { await navigator.clipboard.writeText($('#u-link').value); }
    catch { $('#u-link').select(); document.execCommand('copy'); }
    btn.textContent = 'Copiado';
    setTimeout(() => (btn.textContent = 'Copiar'), 2000);
  };
  $('#u-again').onclick = () => { file = null; clearError(); setState('empty'); };

  setState('empty');
})();
