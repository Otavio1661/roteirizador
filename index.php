<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Otimizador de Rotas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css">
  <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #0d0f1a;
      color: #dde1f0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    header {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 20px;
      background: #13162a;
      border-bottom: 1px solid #1e2240;
      flex-shrink: 0;
      z-index: 1000;
    }
    header h1 { font-size: 1.1rem; font-weight: 700; color: #818cf8; letter-spacing: .4px; }
    header .sub { font-size: .8rem; color: #6b7280; }
    .badge-algo {
      margin-left: auto;
      background: #1e2240;
      border: 1px solid #2d3260;
      border-radius: 6px;
      padding: 4px 10px;
      font-size: .72rem;
      color: #818cf8;
      font-weight: 600;
    }

    .main { display: flex; flex: 1; overflow: hidden; }

    .sidebar {
      width: 300px;
      background: #13162a;
      border-right: 1px solid #1e2240;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      flex-shrink: 0;
      z-index: 999;
    }

    .section {
      padding: 14px 16px;
      border-bottom: 1px solid #1e2240;
    }
    .section-title {
      font-size: .7rem;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: #818cf8;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .search-row { display: flex; gap: 6px; }
    .search-row input {
      flex: 1;
      background: #1a1e35;
      border: 1px solid #2d3260;
      border-radius: 7px;
      padding: 8px 11px;
      color: #dde1f0;
      font-size: .875rem;
      outline: none;
      transition: border-color .15s;
    }
    .search-row input:focus { border-color: #818cf8; }
    .search-row input::placeholder { color: #4b5563; }

    .btn {
      display: block;
      width: 100%;
      padding: 9px 14px;
      border: none;
      border-radius: 7px;
      cursor: pointer;
      font-size: .875rem;
      font-weight: 600;
      transition: opacity .15s, transform .1s;
      text-align: center;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .btn:hover:not(:disabled) { opacity: .85; }
    .btn:active:not(:disabled) { transform: scale(.97); }
    .btn:disabled { opacity: .35; cursor: default; }
    .btn-icon {
      width: auto; padding: 8px 13px;
      border-radius: 7px; border: none;
      cursor: pointer; font-size: .875rem; font-weight: 600;
      transition: opacity .15s; flex-shrink: 0;
    }
    .btn-icon:hover { opacity: .85; }
    .btn-primary { background: #818cf8; color: #fff; }
    .btn-add     { background: #22c55e; color: #fff; }
    .btn-ghost   { background: #1e2240; color: #9ca3af; margin-top: 6px; }
    .btn-danger  { background: #ef4444; color: #fff; margin-top: 6px; }

    #point-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 5px;
      overflow-y: auto;
      flex: 1;
      padding: 12px 16px;
    }
    #point-list:empty::after {
      content: 'Digite um endereço acima para começar';
      display: block;
      text-align: center;
      color: #4b5563;
      font-size: .82rem;
      padding: 24px 0;
      line-height: 1.6;
    }
    #point-list li {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      padding: 8px 10px;
      background: #1a1e35;
      border-radius: 7px;
      font-size: .8rem;
      border: 1.5px solid transparent;
      transition: border-color .2s;
    }
    #point-list li.active  { border-color: #fbbf24; }
    #point-list li.visited { border-color: #818cf8; }
    .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 3px; }
    .pt-info { flex: 1; min-width: 0; }
    .pt-name { font-weight: 600; color: #c7d2fe; }
    .pt-addr { font-size: .72rem; color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 190px; }
    .pt-rm { cursor: pointer; color: #4b5563; font-size: 1.1rem; line-height: 1; padding: 0 2px; flex-shrink: 0; margin-top: 1px; }
    .pt-rm:hover { color: #ef4444; }

    .controls { padding: 12px 16px; border-top: 1px solid #1e2240; flex-shrink: 0; }

    #result-panel { padding: 14px 16px; border-top: 1px solid #1e2240; display: none; flex-shrink: 0; }
    #result-panel.show { display: block; }
    .res-total       { font-size: 1.05rem; font-weight: 700; color: #818cf8; margin-bottom: 3px; }
    .res-duration    { font-size: .82rem; color: #34d399; margin-bottom: 4px; }
    .res-improvement { font-size: .75rem; color: #22c55e; margin-bottom: 8px; }
    .res-source      { font-size: .7rem; color: #4b5563; margin-bottom: 8px; }
    .res-sequence {
      font-size: .75rem; color: #818cf8; font-weight: 600;
      background: #1a1e35; border-radius: 6px;
      padding: 7px 10px; margin-bottom: 10px;
      line-height: 1.7; word-break: break-word;
    }
    .res-steps       { font-size: .75rem; color: #9ca3af; line-height: 2; max-height: 150px; overflow-y: auto; }
    .res-steps span  { color: #c7d2fe; font-weight: 600; }
    .res-steps em    { color: #6b7280; font-style: normal; }
    .res-steps .step-n { color: #4b5563; }

    #step-info {
      position: absolute;
      top: 12px; left: 12px;
      z-index: 800;
      background: rgba(13,15,26,.92);
      border: 1px solid #1e2240;
      border-radius: 9px;
      padding: 10px 14px;
      font-size: .78rem;
      color: #9ca3af;
      pointer-events: none;
      display: none;
      min-width: 220px;
      box-shadow: 0 4px 16px rgba(0,0,0,.4);
      line-height: 1.5;
    }
    #step-info.show { display: block; }
    #step-info strong { color: #fbbf24; }
    #step-info .road { color: #34d399; }
    #step-info .si-phase { font-size: .7rem; color: #6b7280; margin-bottom: 3px; }

    .spinner {
      display: inline-block;
      width: 12px; height: 12px;
      border: 2px solid #4b5563;
      border-top-color: #818cf8;
      border-radius: 50%;
      animation: spin .6s linear infinite;
      vertical-align: middle;
      margin-right: 6px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .map-wrap { flex: 1; position: relative; overflow: hidden; }
    #map { width: 100%; height: 100%; }

    .leaflet-container { background: #1a1e35; }
    .leaflet-tile { filter: brightness(.85) saturate(.8); }
    .leaflet-control-zoom a { background: #13162a !important; color: #818cf8 !important; border-color: #1e2240 !important; }
    .leaflet-control-zoom a:hover { background: #1e2240 !important; }
    .leaflet-control-attribution { background: rgba(13,15,26,.75) !important; color: #6b7280 !important; font-size: .65rem !important; }
    .leaflet-control-attribution a { color: #818cf8 !important; }
    .leaflet-popup-content-wrapper { background: #13162a; color: #dde1f0; border: 1px solid #1e2240; border-radius: 9px; box-shadow: 0 4px 20px rgba(0,0,0,.5); }
    .leaflet-popup-tip { background: #13162a; }
    .leaflet-popup-content { margin: 10px 14px; font-size: .82rem; line-height: 1.6; }
    .leaflet-popup-content b { color: #818cf8; }

    .toast {
      position: fixed; bottom: 20px; right: 20px; z-index: 9999;
      background: #450a0a; border: 1px solid #ef4444; color: #fca5a5;
      padding: 10px 16px; border-radius: 8px; font-size: .82rem;
      max-width: 300px; box-shadow: 0 4px 16px rgba(0,0,0,.5);
      animation: slideIn .2s ease;
    }
    @keyframes slideIn { from { transform: translateY(10px); opacity: 0; } }

    .map-search-control {
      display: flex; align-items: center; gap: 6px;
      background: rgba(13,15,26,.92);
      border: 1px solid #1e2240; border-radius: 9px;
      padding: 7px 11px;
      box-shadow: 0 4px 16px rgba(0,0,0,.4);
    }
    .map-search-control input {
      background: transparent; border: none; outline: none;
      color: #dde1f0; font-size: .82rem; width: 200px;
    }
    .map-search-control input::placeholder { color: #4b5563; }
    .map-search-control button {
      background: none; border: none; cursor: pointer;
      color: #818cf8; font-size: 1rem; padding: 0; line-height: 1;
    }
    .map-search-control button:hover { color: #c7d2fe; }

    .btn-ghost.active {
      background: #1e2d50; color: #818cf8; border: 1px solid #818cf8;
    }
    .map-crosshair, .map-crosshair .leaflet-grab { cursor: crosshair !important; }

    .search-wrap { position: relative; }
    .addr-suggestions {
      position: absolute; top: 100%; left: 0; right: 0; z-index: 2000;
      background: #13162a; border: 1px solid #2d3260; border-top: none;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,.5);
      max-height: 220px; overflow-y: auto;
    }
    .addr-suggestions:empty { display: none; }
    .suggestion-item {
      padding: 8px 12px; cursor: pointer; font-size: .8rem;
      color: #9ca3af; border-bottom: 1px solid #1e2240;
      display: flex; flex-direction: column; gap: 1px;
      transition: background .1s;
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover, .suggestion-item.active {
      background: #1e2240; color: #dde1f0;
    }
    .suggestion-item strong { color: #c7d2fe; font-size: .82rem; }
    .suggestion-item span   { font-size: .7rem; color: #4b5563; }

    .btn svg { vertical-align: middle; margin-right: 6px; }
    .btn-icon svg { vertical-align: middle; }
  </style>
</head>
<body>

<header>
  <h1>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#818cf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:8px">
      <circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/>
      <path d="M6 17V9a4 4 0 0 1 4-4h1"/><path d="M18 7v8a4 4 0 0 1-4 4h-1"/>
    </svg>Otimizador de Rotas
  </h1>
  <span class="sub">Rotas reais por estrada • OpenStreetMap + OSRM</span>
  <span class="badge-algo">NN + 2-Opt + Or-Opt</span>
</header>

<div class="main">
  <div class="sidebar">

    <div class="section">
      <div class="section-title">Buscar Endereço</div>
      <div class="search-wrap">
        <div class="search-row">
          <input type="text" id="addr-input" placeholder="Ex: Av. Paulista, 1000, São Paulo" autocomplete="off">
          <button class="btn-icon btn-add" id="btn-add" title="Adicionar">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
        <div id="addr-suggestions" class="addr-suggestions"></div>
      </div>
    </div>

    <ul id="point-list"></ul>

    <div class="controls">
      <div class="section-title" style="margin-bottom:8px">
        Pontos: <span id="count">0</span>
      </div>
      <button class="btn btn-primary" id="btn-optimize" disabled>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:5px"><polygon points="5 3 19 12 5 21 5 3"/></svg>Otimizar Rota
      </button>
      <button class="btn btn-ghost" id="btn-demo">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="vertical-align:middle;margin-right:5px"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>Demo 6 cidades
      </button>
      <button class="btn btn-ghost" id="btn-demo20">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="vertical-align:middle;margin-right:5px"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>Demo 20 cidades
      </button>
      <button class="btn btn-ghost" id="btn-click-add">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:5px"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>Clique no mapa para adicionar
      </button>
      <div class="section-title" style="margin-top:10px;margin-bottom:6px">Teste de Escala</div>
      <button class="btn btn-ghost"   id="btn-300s">300 pts — Curta <small>(&lt;25 km)</small></button>
      <button class="btn btn-ghost"   id="btn-300m">300 pts — Média <small>(&lt;350 km)</small></button>
      <button class="btn btn-ghost"   id="btn-300l">300 pts — Longa <small>(Brasil)</small></button>
      <button class="btn btn-danger"  id="btn-clear">Limpar Tudo</button>
    </div>

    <div id="result-panel">
      <div class="section-title">Resultado</div>
      <div class="res-total"       id="res-total"></div>
      <div class="res-duration"    id="res-duration"></div>
      <div class="res-improvement" id="res-improvement"></div>
      <div class="res-source"      id="res-source"></div>
      <div class="res-sequence"    id="res-sequence"></div>
      <div class="res-steps"       id="res-steps"></div>
    </div>

  </div>

  <div class="map-wrap">
    <div id="map"></div>
    <div id="step-info"></div>
  </div>
</div>

<script>
// ─────────────────────────────────────────────────────
//  CONSTANTS
// ─────────────────────────────────────────────────────
const PALETTE = [
  '#f87171','#fb923c','#fbbf24','#a3e635',
  '#34d399','#22d3ee','#818cf8','#e879f9',
  '#f43f5e','#60a5fa'
];

const S_SEARCH    = { color:'#fbbf24', weight:1.5, opacity:0.28, dashArray:'5 5' };
const S_CHOSEN    = { color:'#fbbf24', weight:3,   opacity:0.95 };
const S_COMMITTED = { color:'#818cf8', weight:3,   opacity:1,   dashArray:'9 7' };
const S_FINAL     = { color:'#818cf8', weight:4,   opacity:1 };

const OSRM = 'https://router.project-osrm.org';

// ─────────────────────────────────────────────────────
//  STATE
// ─────────────────────────────────────────────────────
let points         = [];
let animRunning    = false;
let animTimers     = [];
let committedLines = [];
let drawGeneration = 0;   // cancels in-flight road draws

// ─────────────────────────────────────────────────────
//  MAP
// ─────────────────────────────────────────────────────
const map = L.map('map', { zoomControl: true }).setView([-15.78, -47.93], 5);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  maxZoom: 19
}).addTo(map);

// Controle de busca flutuante no mapa
const MapSearchControl = L.Control.extend({
  onAdd() {
    const div = L.DomUtil.create('div', 'map-search-control');
    div.innerHTML = `<input id="map-search-input" type="text" placeholder="Buscar local no mapa…" autocomplete="off">
      <button id="map-search-btn" title="Buscar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>`;
    L.DomEvent.disableClickPropagation(div);
    L.DomEvent.disableScrollPropagation(div);
    return div;
  }
});
new MapSearchControl({ position: 'topright' }).addTo(map);

// ─────────────────────────────────────────────────────
//  MARKER FACTORY
// ─────────────────────────────────────────────────────
function makeIcon(label, color, size = 26, orderBadge = null) {
  const half   = size / 2;
  const badge  = orderBadge != null
    ? `<span style="position:absolute;top:-6px;right:-6px;
        background:#0d0f1a;color:#818cf8;
        font-size:8px;font-weight:700;
        width:14px;height:14px;border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        border:1px solid #818cf8">${orderBadge}</span>`
    : '';
  return L.divIcon({
    html: `<div style="
      background:${color};border:2.5px solid #fff;
      box-shadow:0 0 10px ${color}bb;
      width:${size}px;height:${size}px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      color:#fff;font-weight:700;font-size:${size > 28 ? 12 : 10}px;
      position:relative">${label}${badge}</div>`,
    iconSize:    [size, size],
    iconAnchor:  [half, half],
    popupAnchor: [0, -half - 4],
    className:   ''
  });
}

// ─────────────────────────────────────────────────────
//  GEOCODING — Nominatim
// ─────────────────────────────────────────────────────
async function geocode(address) {
  const url  = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q='
              + encodeURIComponent(address);
  const res  = await fetch(url, { headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' } });
  const data = await res.json();
  if (!data.length) throw new Error(`Endereço não encontrado: "${address}"`);
  return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon), display: data[0].display_name };
}

// ─────────────────────────────────────────────────────
//  OSRM — MATRIZ DE DISTÂNCIAS POR ESTRADA
// ─────────────────────────────────────────────────────
async function buildRoadMatrix(pts) {
  // OSRM usa lon,lat (ao contrário do Leaflet)
  const coords = pts.map(p => `${p.lng},${p.lat}`).join(';');
  const url    = `${OSRM}/table/v1/driving/${coords}?annotations=distance,duration`;
  const res    = await fetch(url);
  if (!res.ok) throw new Error(`OSRM retornou ${res.status}`);
  const data   = await res.json();
  if (data.code !== 'Ok') throw new Error('OSRM: ' + (data.message || data.code));

  const n = pts.length;
  const M = Array.from({ length: n }, () => new Float64Array(n)); // distâncias (m)
  const D = Array.from({ length: n }, () => new Float64Array(n)); // durações (s)

  for (let i = 0; i < n; i++) {
    for (let j = 0; j < n; j++) {
      const dist = data.distances?.[i]?.[j];
      const dur  = data.durations?.[i]?.[j];
      M[i][j] = (dist != null && dist > 0) ? dist : haversine(pts[i], pts[j]);
      D[i][j] = (dur  != null)             ? dur  : 0;
    }
  }
  return { M, D };
}

// ─────────────────────────────────────────────────────
//  OSRM — CAMINHO REAL POR ESTRADA (geometria GeoJSON)
// ─────────────────────────────────────────────────────
async function fetchRoadPath(from, to) {
  const url  = `${OSRM}/route/v1/driving/${from.lng},${from.lat};${to.lng},${to.lat}`
              + `?overview=full&geometries=geojson`;
  try {
    const res  = await fetch(url);
    if (!res.ok) throw new Error();
    const data = await res.json();
    if (data.code !== 'Ok' || !data.routes?.length) throw new Error();
    // GeoJSON retorna [lng, lat] — Leaflet quer [lat, lng]
    return data.routes[0].geometry.coordinates.map(([lng, lat]) => [lat, lng]);
  } catch {
    return [[from.lat, from.lng], [to.lat, to.lng]]; // fallback linha reta
  }
}

// ─────────────────────────────────────────────────────
//  HAVERSINE (fallback quando OSRM falha)
// ─────────────────────────────────────────────────────
function haversine(a, b) {
  const R  = 6371000;
  const φ1 = a.lat * Math.PI / 180, φ2 = b.lat * Math.PI / 180;
  const Δφ = (b.lat - a.lat) * Math.PI / 180;
  const Δλ = (b.lng - a.lng) * Math.PI / 180;
  const h  = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
}

function buildDistMatrix(pts) {
  const n = pts.length;
  const M = Array.from({ length: n }, () => new Float64Array(n));
  for (let i = 0; i < n; i++)
    for (let j = 0; j < n; j++)
      M[i][j] = haversine(pts[i], pts[j]);
  return M;
}

// ─────────────────────────────────────────────────────
//  FORMATTERS
// ─────────────────────────────────────────────────────
function fmtDist(m) {
  return m >= 1000 ? (m / 1000).toFixed(1) + ' km' : Math.round(m) + ' m';
}
function fmtTime(s) {
  const h = Math.floor(s / 3600);
  const m = Math.floor((s % 3600) / 60);
  if (h > 0) return `${h}h ${m > 0 ? m + 'min' : ''}`;
  if (m > 0) return `${m} min`;
  return `${Math.round(s)} s`;
}

// ─────────────────────────────────────────────────────
//  NEAREST NEIGHBOR (multi-start)
// ─────────────────────────────────────────────────────
function nearestNeighborFrom(pts, M, start) {
  const n       = pts.length;
  const visited = new Uint8Array(n);
  const order   = [], steps = [];
  let cur = start;
  visited[start] = 1;
  order.push(start);

  for (let s = 1; s < n; s++) {
    let best = -1, bestDist = Infinity;
    const searched = [];
    for (let j = 0; j < n; j++) {
      if (!visited[j]) {
        searched.push(j);
        if (M[cur][j] < bestDist) { bestDist = M[cur][j]; best = j; }
      }
    }
    steps.push({ from: cur, to: best, dist: bestDist, searched });
    visited[best] = 1;
    order.push(best);
    cur = best;
  }
  steps.push({ from: cur, to: start, dist: M[cur][start], searched: [] });
  order.push(start);
  return { order, steps, distance: steps.reduce((s, t) => s + t.dist, 0) };
}

function nearestNeighbor(pts, M) {
  const n = pts.length;
  // Para conjuntos grandes limita a 10 starts aleatórios para não travar o browser
  const starts = n > 50
    ? Array.from({ length: 10 }, () => Math.floor(Math.random() * n))
    : Array.from({ length: n }, (_, i) => i);

  let best = null;
  for (const start of starts) {
    const candidate = nearestNeighborFrom(pts, M, start);
    if (!best || candidate.distance < best.distance) best = candidate;
  }
  return best;
}

// ─────────────────────────────────────────────────────
//  2-OPT
// ─────────────────────────────────────────────────────
function twoOpt(order, M) {
  let route = order.slice(0, -1);
  const n   = route.length;
  let improved = true;
  while (improved) {
    improved = false;
    for (let i = 0; i < n - 1; i++) {
      for (let j = i + 2; j < n; j++) {
        if (i === 0 && j === n - 1) continue;
        const a = route[i], b = route[i+1], c = route[j], d = route[(j+1)%n];
        if (M[a][c] + M[b][d] < M[a][b] + M[c][d] - 1e-10) {
          let lo = i+1, hi = j;
          while (lo < hi) { [route[lo], route[hi]] = [route[hi], route[lo]]; lo++; hi--; }
          improved = true;
        }
      }
    }
  }
  route.push(route[0]);
  return { order: route, distance: route.reduce((s, v, i) => i === 0 ? s : s + M[route[i-1]][v], 0) };
}

// ─────────────────────────────────────────────────────
//  2-OPT COM LIMITE DE TEMPO (para conjuntos grandes)
// ─────────────────────────────────────────────────────
function twoOptTimeLimited(order, M, maxMs = 3000) {
  let route    = order.slice(0, -1);
  const n      = route.length;
  let improved = true;
  const deadline = performance.now() + maxMs;

  while (improved && performance.now() < deadline) {
    improved = false;
    for (let i = 0; i < n - 1; i++) {
      if (performance.now() > deadline) break;
      for (let j = i + 2; j < n; j++) {
        if (i === 0 && j === n - 1) continue;
        const a = route[i], b = route[i+1], c = route[j], d = route[(j+1)%n];
        if (M[a][c] + M[b][d] < M[a][b] + M[c][d] - 1e-10) {
          let lo = i+1, hi = j;
          while (lo < hi) { [route[lo], route[hi]] = [route[hi], route[lo]]; lo++; hi--; }
          improved = true;
        }
      }
    }
  }

  route.push(route[0]);
  return { order: route, distance: route.reduce((s,v,i) => i === 0 ? s : s + M[route[i-1]][v], 0) };
}

// ─────────────────────────────────────────────────────
//  OR-OPT — relocação de nós (corrige "nó fora do lugar")
//  Para cada ponto, tenta removê-lo e reinseri-lo onde custa menos.
//  Pega casos que o 2-Opt não consegue: vizinhos visitados longe um do outro.
// ─────────────────────────────────────────────────────
function orOpt(order, M) {
  let route = order.slice(0, -1);
  const n   = route.length;
  let improved = true;

  while (improved) {
    improved = false;
    outer: for (let i = 0; i < n; i++) {
      const iPrev = (i - 1 + n) % n;
      const iNext = (i + 1) % n;
      // economia de remover route[i] de onde está
      const saving = M[route[iPrev]][route[i]] + M[route[i]][route[iNext]]
                   - M[route[iPrev]][route[iNext]];

      for (let j = 0; j < n; j++) {
        if (j === iPrev || j === i) continue;
        const jNext = (j + 1) % n;
        if (jNext === i) continue;
        // custo de inserir route[i] entre route[j] e route[jNext]
        const cost = M[route[j]][route[i]] + M[route[i]][route[jNext]]
                   - M[route[j]][route[jNext]];

        if (saving - cost > 1e-10) {
          const node = route[i];
          route.splice(i, 1);
          const ins = j > i ? j - 1 : j;
          route.splice(ins + 1, 0, node);
          improved = true;
          break outer;
        }
      }
    }
  }

  route.push(route[0]);
  return { order: route, distance: route.reduce((s, v, i) => i === 0 ? s : s + M[route[i-1]][v], 0) };
}

// ─────────────────────────────────────────────────────
//  POINT MANAGEMENT
// ─────────────────────────────────────────────────────
async function addPointByAddress(address) {
  if (!address.trim()) return;
  const btn   = document.getElementById('btn-add');
  const input = document.getElementById('addr-input');
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner"></span>';

  try {
    const geo   = await geocode(address);
    const idx   = points.length;
    const name  = `P${idx + 1}`;
    const color = PALETTE[idx % PALETTE.length];
    const marker = L.marker([geo.lat, geo.lng], { icon: makeIcon(name, color) })
      .addTo(map)
      .bindPopup(`<b>${name}</b><br><small>${geo.display}</small>`);

    const cityName = geo.display.split(',')[0].trim();
    points.push({ id: idx, name, address: geo.display, cityName, lat: geo.lat, lng: geo.lng, marker });
    clearRoute();
    updateUI();
    input.value = '';
    map.panTo([geo.lat, geo.lng], { animate: true, duration: 0.5 });
  } catch(e) {
    showToast(e.message);
  } finally {
    btn.disabled  = false;
    btn.innerHTML = '+';
  }
}

function removePoint(idx) {
  if (animRunning) return;
  const p = points[idx];
  if (p.marker) map.removeLayer(p.marker);
  points.splice(idx, 1);
  points.forEach((p, i) => {
    p.id = i; p.name = `P${i+1}`;
    if (p.marker) p.marker.setIcon(makeIcon(p.name, PALETTE[i % PALETTE.length]));
  });
  clearRoute();
  updateUI();
}

function clearAll() {
  stopAnim();
  points.forEach(p => { if (p.marker) map.removeLayer(p.marker); });
  points = [];
  clearRoute();
  updateUI();
}

// ─────────────────────────────────────────────────────
//  ROUTE LAYERS
// ─────────────────────────────────────────────────────
let roadLayers = [];

function clearRoute() {
  drawGeneration++;
  stopAnim();
  committedLines.forEach(l => map.removeLayer(l));
  committedLines = [];
  roadLayers.forEach(l => map.removeLayer(l));
  roadLayers = [];
  document.getElementById('result-panel').classList.remove('show');
  points.forEach((p, i) => {
    if (p.marker) p.marker.setIcon(makeIcon(p.name, PALETTE[i % PALETTE.length]));
  });
}

// ─────────────────────────────────────────────────────
//  UI
// ─────────────────────────────────────────────────────
function updateUI() {
  document.getElementById('count').textContent = points.length;
  document.getElementById('btn-optimize').disabled = points.length < 3 || animRunning;

  document.getElementById('point-list').innerHTML = points.map((p, i) => `
    <li id="li-${i}">
      <span class="dot" style="background:${PALETTE[i % PALETTE.length]}"></span>
      <span class="pt-info">
        <div class="pt-name">${p.name}</div>
        <div class="pt-addr" title="${p.address}">${p.address}</div>
      </span>
      <span class="pt-rm" onclick="removePoint(${i})">×</span>
    </li>
  `).join('');
}

// ─────────────────────────────────────────────────────
//  OPTIMIZE HANDLER
// ─────────────────────────────────────────────────────
document.getElementById('btn-optimize').addEventListener('click', async () => {
  if (points.length < 3 || animRunning) return;
  clearRoute();

  const btn = document.getElementById('btn-optimize');
  btn.disabled  = true;
  btn.innerHTML = '<span class="spinner"></span> Calculando estradas…';
  setStepInfo('<div class="si-phase">Aguarde</div>Buscando distâncias reais por estrada…');
  document.getElementById('step-info').classList.add('show');

  const pts     = points.slice();
  const isLarge = pts.length > 50;
  let M, D, usedRoads;

  if (isLarge) {
    // Haversine puro — sem rede, sem limites de tamanho
    btn.innerHTML = '<span class="spinner"></span> Calculando matriz…';
    M         = buildDistMatrix(pts);
    D         = null;
    usedRoads = false;
  } else {
    btn.innerHTML = '<span class="spinner"></span> Calculando estradas…';
    usedRoads = true;
    try {
      ({ M, D } = await buildRoadMatrix(pts));
    } catch(e) {
      showToast('OSRM indisponível — usando linha reta: ' + e.message);
      M = buildDistMatrix(pts); D = null; usedRoads = false;
    }
  }

  btn.disabled  = false;
  btn.innerHTML = '▶ Otimizar Rota';

  const nn      = nearestNeighbor(pts, M);
  // 2-Opt + Or-Opt: 2-Opt desfaz cruzamentos, Or-Opt reloca nós fora do lugar
  const twoOptResult = isLarge
    ? twoOptTimeLimited(nn.order, M, 2500)
    : twoOpt(nn.order, M);
  const rawOpt = isLarge ? twoOptResult : orOpt(twoOptResult.order, M);

  // Rotaciona o tour para sempre começar e terminar no P1 (índice 0)
  const p1pos  = rawOpt.order.indexOf(0);
  const rotated = p1pos === 0 ? rawOpt.order
    : [...rawOpt.order.slice(p1pos, -1), ...rawOpt.order.slice(0, p1pos), 0];
  const opt = { ...rawOpt, order: rotated };

  // Duração total da rota otimizada
  let totalDuration = 0;
  if (D) {
    for (let i = 0; i < opt.order.length - 1; i++)
      totalDuration += D[opt.order[i]][opt.order[i+1]];
  }

  showResultPanel({ pts, nn, opt, M, D, totalDuration, usedRoads });
  startAnimation({ pts, nnSteps: nn.steps, nnOrder: nn.order, nnDist: nn.distance,
                   optOrder: opt.order, optDist: opt.distance, M, D, usedRoads });
});

// ─────────────────────────────────────────────────────
//  RESULT PANEL
// ─────────────────────────────────────────────────────
function showResultPanel({ pts, nn, opt, M, D, totalDuration, usedRoads }) {
  const improve = (1 - opt.distance / nn.distance) * 100;
  const label   = p => p.cityName || p.name;

  document.getElementById('res-total').textContent =
    `Distância: ${fmtDist(opt.distance)}`;

  document.getElementById('res-duration').textContent =
    totalDuration > 0 ? `Tempo estimado: ${fmtTime(totalDuration)}` : '';

  document.getElementById('res-improvement').textContent =
    improve > 0.1
      ? `2-Opt melhorou ${improve.toFixed(1)}% vs Nearest Neighbor`
      : '2-Opt confirmou rota ótima';

  document.getElementById('res-source').textContent =
    usedRoads ? 'Distâncias por estrada (OSRM)' : 'Distâncias em linha reta';

  // Sequência de visita: P1 → P4 → P2 → … → P1
  const seq = opt.order.map(i => pts[i].name).join(' → ');
  document.getElementById('res-sequence').textContent = seq;

  // Passos detalhados com nome da cidade
  const stepsHtml = opt.order.slice(0, -1).map((idx, i) => {
    const next = opt.order[i+1];
    const d    = M[idx][next];
    const t    = D ? D[idx][next] : 0;
    return `<div>
      <span class="step-n">${i+1}.</span>
      ${pts[idx].name} <small style="color:#6b7280">(${label(pts[idx])})</small>
      →
      ${pts[next].name} <small style="color:#6b7280">(${label(pts[next])})</small>:
      <span>${fmtDist(d)}</span>
      ${t > 0 ? `<em> · ${fmtTime(t)}</em>` : ''}
    </div>`;
  }).join('');

  document.getElementById('res-steps').innerHTML = stepsHtml;
  document.getElementById('result-panel').classList.add('show');
}

// ─────────────────────────────────────────────────────
//  ANIMATION ENGINE
// ─────────────────────────────────────────────────────
function stopAnim() {
  animTimers.forEach(clearTimeout);
  animTimers  = [];
  animRunning = false;
  document.getElementById('step-info').classList.remove('show');
  updateUI();
}

function scheduleTimeout(fn, ms) {
  const id = setTimeout(fn, ms);
  animTimers.push(id);
}

function setStepInfo(html) {
  const el = document.getElementById('step-info');
  el.classList.add('show');
  el.innerHTML = html;
}

function latlng(p) { return [p.lat, p.lng]; }

// Velocidade de animação por quantidade de pontos
function animSpeed(n) {
  if (n <= 8)  return { search: 300, chosen: 180, commit: 120 };
  if (n <= 15) return { search: 130, chosen:  70, commit:  40 };
  return null; // muitos pontos: pula animação NN
}

function startAnimation(r) {
  animRunning = true;
  updateUI();
  const speed = animSpeed(r.pts.length);
  if (!speed) {
    // Pula animação passo-a-passo, vai direto para estradas
    setStepInfo(`<div class="si-phase">Muitos pontos</div>Pulando animação — traçando estradas…`);
    scheduleTimeout(() => show2Opt(r), 400);
  } else {
    animateNNStep(r, 0, speed);
  }
}

function animateNNStep(r, stepIdx, speed) {
  const { pts, nnSteps } = r;

  if (stepIdx >= nnSteps.length) {
    scheduleTimeout(() => show2Opt(r), 200);
    return;
  }

  const step = nnSteps[stepIdx];
  const from = pts[step.from];
  const to   = pts[step.to];

  if (points[step.from]?.marker)
    points[step.from].marker.setIcon(makeIcon(from.name, PALETTE[step.from % PALETTE.length], 34));

  const liFrom = document.getElementById(`li-${step.from}`);
  if (liFrom) {
    document.querySelectorAll('#point-list li').forEach(l => l.classList.remove('active'));
    liFrom.classList.add('active');
  }

  setStepInfo(`
    <div class="si-phase">Passo ${stepIdx + 1} / ${nnSteps.length} — Buscando</div>
    <strong>${from.name}</strong> procura o vizinho mais próximo por estrada…
  `);

  const tempLines = [];
  for (const j of step.searched) {
    tempLines.push(L.polyline([latlng(from), latlng(pts[j])], { ...S_SEARCH }).addTo(map));
  }

  scheduleTimeout(() => {
    tempLines.forEach(l => l.setStyle({ opacity: 0.07 }));
    const chosenLine = L.polyline([latlng(from), latlng(to)], { ...S_CHOSEN }).addTo(map);

    setStepInfo(`
      <div class="si-phase">Passo ${stepIdx + 1} / ${nnSteps.length} — Escolhido</div>
      <strong>${to.name}</strong> é o mais próximo
      <span class="road">${fmtDist(step.dist)}</span>
    `);

    scheduleTimeout(() => {
      tempLines.forEach(l => map.removeLayer(l));
      map.removeLayer(chosenLine);

      committedLines.push(L.polyline([latlng(from), latlng(to)], { ...S_COMMITTED }).addTo(map));

      if (points[step.from]?.marker)
        points[step.from].marker.setIcon(makeIcon(from.name, PALETTE[step.from % PALETTE.length], 26));

      const liTo = document.getElementById(`li-${step.to}`);
      if (liTo) liTo.classList.add('visited');

      scheduleTimeout(() => animateNNStep(r, stepIdx + 1, speed), speed.commit);
    }, speed.chosen);
  }, speed.search);
}

function show2Opt(r) {
  setStepInfo('<div class="si-phase">2-Opt</div>Refinando a rota…');

  // remove linhas retas do NN
  committedLines.forEach(l => map.removeLayer(l));
  committedLines = [];

  scheduleTimeout(async () => {
    // Atualiza marcadores com badge de ordem
    r.optOrder.slice(0, -1).forEach((idx, step) => {
      const p = points[idx];
      if (!p?.marker) return;
      p.marker.setIcon(makeIcon(p.name, PALETTE[points.indexOf(p) % PALETTE.length], 26, step + 1));
    });

    document.querySelectorAll('#point-list li').forEach(l => l.classList.remove('active', 'visited'));

    await drawRealRoads(r);
  }, 400);
}

async function drawRealRoads(r) {
  const gen = ++drawGeneration;
  const { pts, optOrder } = r;
  const n = optOrder.length - 1;

  setStepInfo(`<div class="si-phase">Traçando estradas reais</div>Buscando rota (${n} paradas)…`);

  // Uma única chamada OSRM com todos os waypoints — funciona para qualquer N
  const coords = optOrder.map(i => `${pts[i].lng.toFixed(6)},${pts[i].lat.toFixed(6)}`).join(';');

  try {
    const res  = await fetch(`${OSRM}/route/v1/driving/${coords}?geometries=geojson&overview=full`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    if (drawGeneration !== gen) return;
    if (!data.routes?.[0]) throw new Error('sem rota');

    const path = data.routes[0].geometry.coordinates.map(([lng, lat]) => [lat, lng]);
    roadLayers.push(L.polyline(path, { ...S_FINAL }).addTo(map));
    if (path.length) map.fitBounds(L.latLngBounds(path), { padding: [40, 40] });
  } catch (e) {
    if (drawGeneration !== gen) return;
    showToast('OSRM indisponível — usando linha reta: ' + e.message);
    const path = optOrder.map(i => latlng(pts[i]));
    roadLayers.push(L.polyline(path, { ...S_FINAL }).addTo(map));
    map.fitBounds(L.latLngBounds(path), { padding: [40, 40] });
  }

  finishAnimation(r);
}

function finishAnimation(r) {
  const improve = (1 - r.optDist / r.nnDist) * 100;
  setStepInfo(`
    <div class="si-phase">Otimização concluída</div>
    Distância: <strong class="road">${fmtDist(r.optDist)}</strong>
    ${improve > 0.1 ? `<br>2-Opt economizou <span class="road">${improve.toFixed(1)}%</span>` : ''}
  `);

  animRunning = false;
  updateUI();

  scheduleTimeout(() => {
    document.getElementById('step-info').classList.remove('show');
  }, 4000);
}

// ─────────────────────────────────────────────────────
//  DEMO DATA
// ─────────────────────────────────────────────────────
const DEMO_CITIES_6 = [
  { name:'São Paulo',      lat:-23.5505, lng:-46.6333 },
  { name:'Rio de Janeiro', lat:-22.9068, lng:-43.1729 },
  { name:'Belo Horizonte', lat:-19.9167, lng:-43.9345 },
  { name:'Brasília',       lat:-15.7801, lng:-47.9292 },
  { name:'Salvador',       lat:-12.9714, lng:-38.5014 },
  { name:'Curitiba',       lat:-25.4290, lng:-49.2671 },
];

const DEMO_CITIES_20 = [
  { name:'São Paulo',       lat:-23.5505, lng:-46.6333 },
  { name:'Rio de Janeiro',  lat:-22.9068, lng:-43.1729 },
  { name:'Belo Horizonte',  lat:-19.9167, lng:-43.9345 },
  { name:'Brasília',        lat:-15.7801, lng:-47.9292 },
  { name:'Salvador',        lat:-12.9714, lng:-38.5014 },
  { name:'Curitiba',        lat:-25.4290, lng:-49.2671 },
  { name:'Fortaleza',       lat: -3.7172, lng:-38.5433 },
  { name:'Manaus',          lat: -3.1190, lng:-60.0217 },
  { name:'Porto Alegre',    lat:-30.0346, lng:-51.2177 },
  { name:'Recife',          lat: -8.0476, lng:-34.8770 },
  { name:'Belém',           lat: -1.4558, lng:-48.5044 },
  { name:'Goiânia',         lat:-16.6869, lng:-49.2648 },
  { name:'Florianópolis',   lat:-27.5954, lng:-48.5480 },
  { name:'Maceió',          lat: -9.6658, lng:-35.7350 },
  { name:'Natal',           lat: -5.7945, lng:-35.2110 },
  { name:'Teresina',        lat: -5.0892, lng:-42.8019 },
  { name:'Campo Grande',    lat:-20.4697, lng:-54.6201 },
  { name:'João Pessoa',     lat: -7.1195, lng:-34.8450 },
  { name:'Aracaju',         lat:-10.9472, lng:-37.0731 },
  { name:'Cuiabá',          lat:-15.6014, lng:-56.0979 },
];

function loadDemo(cities) {
  if (animRunning) return;
  clearAll();
  cities.forEach((c, i) => {
    const name   = `P${i+1}`;
    const color  = PALETTE[i % PALETTE.length];
    const marker = L.marker([c.lat, c.lng], { icon: makeIcon(name, color) })
      .addTo(map)
      .bindPopup(`<b>${name} — ${c.name}</b>`);
    points.push({ id:i, name, address:c.name + ', Brasil', cityName:c.name, lat:c.lat, lng:c.lng, marker });
  });
  updateUI();
  map.fitBounds(L.featureGroup(points.map(p => p.marker)).getBounds(), { padding:[60,60] });
}

document.getElementById('btn-demo').addEventListener('click',   () => loadDemo(DEMO_CITIES_6));
document.getElementById('btn-demo20').addEventListener('click', () => loadDemo(DEMO_CITIES_20));

// ─────────────────────────────────────────────────────
//  GERAÇÃO ALEATÓRIA DE PONTOS
// ─────────────────────────────────────────────────────
function randomInRadius(centerLat, centerLng, radiusKm) {
  const angle = Math.random() * 2 * Math.PI;
  const dist  = Math.sqrt(Math.random()) * radiusKm; // distribuição uniforme na área
  const dLat  = (dist * Math.cos(angle)) / 111.32;
  const dLng  = (dist * Math.sin(angle)) / (111.32 * Math.cos(centerLat * Math.PI / 180));
  return { lat: centerLat + dLat, lng: centerLng + dLng };
}

function loadTestPoints(n, centerLat, centerLng, radiusKm, areaLabel) {
  if (animRunning) return;
  clearAll();
  for (let i = 0; i < n; i++) {
    const { lat, lng } = randomInRadius(centerLat, centerLng, radiusKm);
    const name  = `P${i + 1}`;
    const color = PALETTE[i % PALETTE.length];
    const marker = L.marker([lat, lng], { icon: makeIcon(name, color, 18) }).addTo(map);
    points.push({ id:i, name, address:areaLabel, cityName:`Ponto ${i+1}`, lat, lng, marker });
  }
  updateUI();
  map.fitBounds(L.featureGroup(points.map(p => p.marker)).getBounds(), { padding:[40, 40] });
}

document.getElementById('btn-300s').addEventListener('click',
  () => loadTestPoints(300, -23.55, -46.63,   25, 'Área urbana — São Paulo'));
document.getElementById('btn-300m').addEventListener('click',
  () => loadTestPoints(300, -16.00, -49.50,  350, 'Regional — Centro-Oeste'));
document.getElementById('btn-300l').addEventListener('click',
  () => loadTestPoints(300, -15.00, -49.00, 1800, 'Nacional — Brasil'));

// ─────────────────────────────────────────────────────
//  BUSCA NO MAPA (navega sem adicionar ponto)
// ─────────────────────────────────────────────────────
async function mapSearch() {
  const input = document.getElementById('map-search-input');
  const q = input.value.trim();
  if (!q) return;
  input.disabled = true;
  try {
    const geo = await geocode(q);
    map.flyTo([geo.lat, geo.lng], 13, { duration: 1 });
  } catch(e) {
    showToast('Local não encontrado: ' + q);
  } finally {
    input.disabled = false;
    input.select();
  }
}

// ─────────────────────────────────────────────────────
//  CLIQUE NO MAPA PARA ADICIONAR PONTO
// ─────────────────────────────────────────────────────
let clickToAdd = false;

const SVG_PIN = `<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:5px"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`;
const SVG_X   = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="vertical-align:middle;margin-right:5px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

function setClickToAdd(active) {
  clickToAdd = active;
  const btn = document.getElementById('btn-click-add');
  btn.classList.toggle('active', active);
  btn.innerHTML = active
    ? SVG_X   + 'Cancelar adição no mapa'
    : SVG_PIN + 'Clique no mapa para adicionar';
  map.getContainer().classList.toggle('map-crosshair', active);
}

// Clique direito sai do modo de adição e deixa navegar normalmente
map.on('contextmenu', e => {
  if (clickToAdd) { L.DomEvent.stopPropagation(e); setClickToAdd(false); }
});

async function reverseGeocode(lat, lng) {
  const res  = await fetch(
    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
    { headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' } }
  );
  const data = await res.json();
  const display  = data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
  const cityName = (data.address?.city || data.address?.town ||
                    data.address?.village || data.address?.suburb || '').trim()
                || `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
  return { display, cityName };
}

map.on('click', async e => {
  if (!clickToAdd || animRunning) return;
  const { lat, lng } = e.latlng;

  const idx    = points.length;
  const name   = `P${idx + 1}`;
  const color  = PALETTE[idx % PALETTE.length];
  const marker = L.marker([lat, lng], { icon: makeIcon(name, color) })
    .addTo(map)
    .bindPopup(`<b>${name}</b><br><small>Buscando endereço…</small>`);

  const coords = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
  points.push({ id: idx, name, address: coords, cityName: name, lat, lng, marker });
  clearRoute();
  updateUI();

  try {
    const geo = await reverseGeocode(lat, lng);
    const p   = points.find(p => p.id === idx);
    if (p) {
      p.address  = geo.display;
      p.cityName = geo.cityName;
      marker.setPopupContent(`<b>${name}</b><br><small>${geo.display}</small>`);
      updateUI();
    }
  } catch { /* mantém coordenadas se reverse geocoding falhar */ }
});

// ─────────────────────────────────────────────────────
//  EVENTS
// ─────────────────────────────────────────────────────
// ─── AUTOCOMPLETE ────────────────────────────────────
let suggestionResults = [], suggestionActive = -1, debounceTimer = null;

const addrInput = document.getElementById('addr-input');
const suggBox   = document.getElementById('addr-suggestions');

function hideSuggestions() {
  suggBox.innerHTML = '';
  suggestionResults = [];
  suggestionActive  = -1;
}

function renderSuggestions(results) {
  suggestionResults = results;
  suggestionActive  = -1;
  suggBox.innerHTML = results.map((r, i) => {
    const main = r.display_name.split(',')[0].trim();
    const rest = r.display_name.split(',').slice(1, 3).join(',').trim();
    return `<div class="suggestion-item" data-i="${i}">
      <strong>${main}</strong><span>${rest}</span>
    </div>`;
  }).join('');
  suggBox.querySelectorAll('.suggestion-item').forEach(el => {
    el.addEventListener('mousedown', e => {
      e.preventDefault();
      const r = suggestionResults[+el.dataset.i];
      addrInput.value = r.display_name.split(',')[0].trim() + ', ' + r.display_name.split(',').slice(1,3).join(',');
      hideSuggestions();
      addPointByAddress(addrInput.value);
    });
  });
}

addrInput.addEventListener('input', () => {
  clearTimeout(debounceTimer);
  const q = addrInput.value.trim();
  if (q.length < 3) { hideSuggestions(); return; }
  debounceTimer = setTimeout(async () => {
    try {
      const res  = await fetch(
        'https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q),
        { headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' } }
      );
      const data = await res.json();
      if (addrInput.value.trim() === q) renderSuggestions(data);
    } catch {}
  }, 300);
});

addrInput.addEventListener('keydown', e => {
  const items = suggBox.querySelectorAll('.suggestion-item');
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    suggestionActive = Math.min(suggestionActive + 1, items.length - 1);
    items.forEach((el, i) => el.classList.toggle('active', i === suggestionActive));
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    suggestionActive = Math.max(suggestionActive - 1, -1);
    items.forEach((el, i) => el.classList.toggle('active', i === suggestionActive));
  } else if (e.key === 'Enter') {
    if (suggestionActive >= 0 && suggestionResults[suggestionActive]) {
      const r = suggestionResults[suggestionActive];
      addrInput.value = r.display_name.split(',')[0].trim() + ', ' + r.display_name.split(',').slice(1,3).join(',');
      hideSuggestions();
      addPointByAddress(addrInput.value);
    } else {
      hideSuggestions();
      addPointByAddress(addrInput.value);
    }
  } else if (e.key === 'Escape') {
    hideSuggestions();
  }
});

document.addEventListener('click', e => {
  if (!e.target.closest('.search-wrap')) hideSuggestions();
});

document.getElementById('btn-add').addEventListener('click', () => {
  hideSuggestions();
  addPointByAddress(addrInput.value);
});

document.getElementById('btn-click-add').addEventListener('click',
  () => setClickToAdd(!clickToAdd));

document.getElementById('map-search-btn').addEventListener('click', mapSearch);
document.getElementById('map-search-input').addEventListener('keydown',
  e => { if (e.key === 'Enter') mapSearch(); });

document.getElementById('btn-clear').addEventListener('click', clearAll);

// ─────────────────────────────────────────────────────
//  TOAST
// ─────────────────────────────────────────────────────
function showToast(msg) {
  const el = document.createElement('div');
  el.className   = 'toast';
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 5000);
}

// ─────────────────────────────────────────────────────
//  INIT
// ─────────────────────────────────────────────────────
updateUI();
</script>
</body>
</html>
