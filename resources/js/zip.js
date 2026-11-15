/* Leitura de ZIP no navegador: diretório central, árvore, extração por Range. */
export const Zip = (() => {
  const fmt = (n) => n < 1024 ? `${n} B` : n < 1024 ** 2 ? `${(n / 1024).toFixed(0)} KB`
    : n < 1024 ** 3 ? `${(n / 1024 ** 2).toFixed(1)} MB` : `${(n / 1024 ** 3).toFixed(1)} GB`;

  async function readManifest(file, maxEntries = 2000) {
    const tailSize = Math.min(file.size, 65557);
    const tail = new DataView(await file.slice(file.size - tailSize).arrayBuffer());
    let eocd = -1;
    for (let i = tailSize - 22; i >= 0; i--) if (tail.getUint32(i, true) === 0x06054b50) { eocd = i; break; }
    if (eocd < 0) throw new Error('Isso não parece um arquivo ZIP válido.');
    const total = tail.getUint16(eocd + 10, true), cdSize = tail.getUint32(eocd + 12, true), cdOffset = tail.getUint32(eocd + 16, true);
    if (total === 0xffff || cdSize === 0xffffffff || cdOffset === 0xffffffff) throw new Error('ZIPs no formato ZIP64 ainda não são suportados.');
    if (total > maxEntries) throw new Error(`O ZIP tem mais de ${maxEntries} arquivos.`);
    const buf = await file.slice(cdOffset, cdOffset + cdSize).arrayBuffer();
    const cd = new DataView(buf), out = [];
    let p = 0;
    while (p + 46 <= cd.byteLength && cd.getUint32(p, true) === 0x02014b50) {
      const flags = cd.getUint16(p + 8, true), nameLen = cd.getUint16(p + 28, true);
      const bytes = new Uint8Array(buf, p + 46, nameLen);
      let path;
      try { path = new TextDecoder('utf-8', { fatal: true }).decode(bytes); }
      catch { path = new TextDecoder('windows-1252').decode(bytes); }
      out.push({ path, method: cd.getUint16(p + 10, true), comp: cd.getUint32(p + 20, true), size: cd.getUint32(p + 24, true),
        offset: cd.getUint32(p + 42, true), enc: (flags & 1) === 1 });
      p += 46 + nameLen + cd.getUint16(p + 30, true) + cd.getUint16(p + 32, true);
    }
    return out;
  }

  function buildTree(entries) {
    const root = { children: [] };
    for (const e of entries) {
      const parts = e.path.split('/').filter(Boolean), isDir = e.path.endsWith('/');
      let cur = root;
      parts.forEach((name, i) => {
        const asFile = i === parts.length - 1 && !isDir;
        let next = cur.children.find((c) => c.name === name);
        if (!next) { next = asFile ? { name, size: e.size, entry: e } : { name, size: 0, children: [] }; cur.children.push(next); }
        cur = next;
      });
    }
    const sum = (n) => {
      if (n.children) {
        n.children.sort((a, b) => !!b.children - !!a.children || a.name.localeCompare(b.name, 'pt-BR'));
        n.size = n.children.reduce((s, c) => s + sum(c), 0);
      }
      return n.size;
    };
    sum(root);
    return root.children;
  }

  const el = (tag, cls, text) => { const x = document.createElement(tag); if (cls) x.className = cls; if (text != null) x.textContent = text; return x; };

  function renderTree(nodes, actions) {
    const ul = el('ul', 'tree');
    for (const n of nodes) {
      const li = el('li');
      if (n.children) {
        const d = el('details'); d.open = true;
        const s = el('summary');
        s.append(el('span', 'name', n.name + '/'), el('span', 'size', `${n.children.length} itens · ${fmt(n.size)}`));
        d.append(s, renderTree(n.children, actions));
        li.append(d);
      } else {
        const row = el('div', 'file');
        row.append(el('span', 'name', n.name), el('span', 'size', fmt(n.size)));
        if (actions) row.append(actions(n.entry));
        li.append(row);
      }
      ul.append(li);
    }
    return ul;
  }

  /** Extrai um arquivo lendo só os bytes dele (2 Range requests) e descompactando no navegador. */
  async function extract(url, e) {
    if (e.size === 0) return new Blob([]);
    const get = async (a, b) => {
      const r = await fetch(url, { headers: { Range: `bytes=${a}-${b}` } });
      if (r.status !== 206) throw new Error('Não foi possível ler o arquivo.');
      return r.arrayBuffer();
    };
    const h = new DataView(await get(e.offset, e.offset + 29));
    if (h.getUint32(0, true) !== 0x04034b50) throw new Error('ZIP corrompido.');
    const start = e.offset + 30 + h.getUint16(26, true) + h.getUint16(28, true);
    const raw = await get(start, start + e.comp - 1);
    if (e.method === 0) return new Blob([raw]);
    if (e.method === 8) {
      const stream = new Blob([raw]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
      return new Response(stream).blob();
    }
    throw new Error('Método de compressão não suportado.');
  }

  return { fmt, readManifest, buildTree, renderTree, extract, el };
})();

window.Zip = Zip;
