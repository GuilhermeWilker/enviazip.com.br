@php
  $max = (int) config('zip.max_mb', 50);
  $days = (int) config('zip.expires_days', 3);
  $maxEntries = (int) config('zip.max_entries', 2000);
  $ghost = 'h-10 rounded-lg border border-[var(--border)] bg-white px-3 text-sm font-medium transition hover:bg-neutral-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:bg-transparent dark:hover:bg-white/10';
  $primary = 'h-12 rounded-xl bg-blue-600 px-5 text-[15px] font-semibold text-white transition hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:cursor-not-allowed disabled:opacity-40';
@endphp

<div id="uploader"
     class="group w-full max-w-[460px] justify-self-end rounded-[20px] border border-[var(--border)] bg-white p-3 shadow-[0_1px_2px_rgba(0,0,0,.04),0_28px_56px_-28px_rgba(0,0,0,.22)] dark:bg-neutral-950"
     data-max-mb="{{ $max }}" data-max-entries="{{ $maxEntries }}">

  <input id="u-input" type="file" accept=".zip,application/zip" class="hidden">

  {{-- 1 · vazio --}}
  <div data-s="empty">
    <button id="u-pick" type="button"
            class="flex h-60 w-full flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[var(--border)] px-6 text-center transition hover:border-blue-600 hover:bg-blue-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 group-data-[over]:border-blue-600 group-data-[over]:bg-blue-50 dark:hover:bg-blue-500/10">
      <span class="grid h-12 w-12 place-items-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-500/15" aria-hidden="true">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
      </span>
      <span class="text-lg font-semibold tracking-tight">Solte um ZIP aqui</span>
      <span class="text-sm text-neutral-500">ou clique para escolher o arquivo</span>
      <span class="font-mono text-xs text-neutral-500">até {{ $max }} MB · expira em {{ $days }} dias</span>
    </button>
  </div>

  {{-- 2 · ZIP selecionado --}}
  <div data-s="ready" hidden>
    <div class="flex items-center gap-3 rounded-xl bg-blue-50 p-3 dark:bg-blue-500/10">
      <span class="grid h-11 w-11 shrink-0 place-items-center rounded-[10px] border border-blue-100 bg-white text-blue-600 dark:border-blue-500/20 dark:bg-neutral-900" aria-hidden="true">
        <svg class="h-[22px] w-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M6 3h8l5 5v13H6z"/><path d="M11 3v8" stroke-dasharray="2 2"/></svg>
      </span>
      <div class="min-w-0 flex-1">
        <p id="u-name" class="truncate text-[15px] font-semibold tracking-tight"></p>
        <p id="u-meta" class="font-mono text-xs text-neutral-600 dark:text-neutral-400"></p>
      </div>
      <button id="u-change" type="button" class="{{ $ghost }}">Trocar</button>
    </div>

    <div class="px-2 pb-2 pt-4">
      <p class="mb-2 font-mono text-[11px] uppercase tracking-widest text-neutral-500">Conteúdo</p>
      <div id="u-tree" class="max-h-56 overflow-auto"></div>
    </div>

    <div class="mt-2 flex gap-2 border-t border-[var(--border)] pt-3">
      <label class="sr-only" for="u-days">Validade do link</label>
      <select id="u-days" class="h-12 min-w-32 rounded-xl border border-[var(--border)] bg-transparent px-3.5 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
        @foreach (array_unique([1, $days]) as $d)
          <option value="{{ $d }}" @selected($d === $days)>{{ $d }} {{ $d === 1 ? 'dia' : 'dias' }}</option>
        @endforeach
      </select>
      <button id="u-send" type="button" class="{{ $primary }} flex flex-1 items-center justify-center gap-2">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg>
        Gerar link
      </button>
    </div>
    <p class="mx-1 mb-1 mt-3 text-center text-xs text-neutral-500">Ao enviar, você aceita os <a class="underline" href="/termos">Termos de Uso</a>.</p>
  </div>

  {{-- 3 · enviando --}}
  <div data-s="sending" hidden class="px-3 py-10" role="status">
    <p id="u-status" class="text-sm font-medium">Enviando…</p>
    <progress id="u-bar" max="100" value="0"
              class="mt-3 h-2 w-full overflow-hidden rounded-full [&::-moz-progress-bar]:bg-blue-600 [&::-webkit-progress-bar]:bg-neutral-100 [&::-webkit-progress-value]:bg-blue-600 dark:[&::-webkit-progress-bar]:bg-white/10"></progress>
    <p id="u-pct" class="mt-2 font-mono text-xs text-neutral-500">0%</p>
  </div>

  {{-- 4 · link pronto --}}
  <div data-s="done" hidden class="p-3">
    <p class="flex items-center gap-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l4 4 10-10"/></svg>
      Link pronto
    </p>
    <label class="sr-only" for="u-link">Link para compartilhar</label>
    <div class="mt-3 flex gap-2">
      <input id="u-link" readonly class="h-12 min-w-0 flex-1 rounded-xl border border-[var(--border)] bg-transparent px-3 font-mono text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
      <button id="u-copy" type="button" class="{{ $primary }}">Copiar</button>
    </div>
    <p class="mt-3 text-sm text-neutral-500">Expira em <span id="u-exp"></span>. <a id="u-manage" class="underline" href="#">Apagar agora</a></p>
    <button id="u-again" type="button" class="{{ $ghost }} mt-4 w-full">Enviar outro ZIP</button>
  </div>

  {{-- erro / limite do plano grátis --}}
  <p id="u-error" role="alert" hidden class="mx-1 mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">
    <span id="u-error-text"></span>
    <a id="u-upsell" href="#planos" hidden class="font-semibold underline">Ver planos</a>
  </p>

  @if (config('zip.turnstile_site'))
    <div class="cf-turnstile" data-sitekey="{{ config('zip.turnstile_site') }}" data-appearance="interaction-only"></div>
  @endif
</div>

{{-- Estilos da árvore. Mova para resources/css/app.css e reaproveite na página /z/{id}. --}}
<style>
  #u-tree .tree{list-style:none;margin:0;padding:0 0 0 14px;border-left:1px solid var(--border)}
  #u-tree>.tree{padding-left:0;border-left:0}
  #u-tree summary,#u-tree .file{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:.3rem 0;font-size:14px}
  #u-tree summary{cursor:pointer;font-weight:600;list-style:none}
  #u-tree summary::-webkit-details-marker{display:none}
  #u-tree .name{overflow-wrap:anywhere}
  #u-tree .size{font-family:ui-monospace,monospace;font-size:12px;color:#737373;white-space:nowrap}
</style>
