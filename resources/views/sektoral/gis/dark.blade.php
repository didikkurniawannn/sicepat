<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard GIS Data Sektoral Kabupaten Bandung">
    <title>Dashboard GIS - {{ $focusKec->name ?? 'Kabupaten Bandung' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 100%; width: 100%; background: #0f172a; }
        .sidebar-transition { transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
        .ai-panel-transition { transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        .leaflet-popup-content-wrapper { border-radius: 12px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important; }
        .leaflet-popup-content { margin: 12px 16px !important; font-family: 'Inter', sans-serif !important; }
        .kecamatan-polygon-label {
            background: rgba(15, 23, 42, 0.85) !important; border: 1px solid rgba(255,255,255,0.3) !important;
            color: #fff !important; font-size: 11px !important; font-weight: 700 !important;
            border-radius: 6px !important; padding: 3px 8px !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3) !important; font-family: 'Inter', sans-serif !important;
        }
        .kecamatan-polygon-label::before { display: none !important; }
        .desa-polygon-label {
            background: rgba(30,58,138,0.8) !important; border: 1px solid rgba(147,197,253,0.4) !important;
            color: #bfdbfe !important; font-size: 10px !important; font-weight: 600 !important;
            border-radius: 4px !important; padding: 2px 6px !important; font-family: 'Inter', sans-serif !important;
        }
        .desa-polygon-label::before { display: none !important; }
        select.dark-select option { color: #0f172a; }
    </style>
</head>
<body class="bg-slate-900 font-sans antialiased overflow-hidden" x-data="dashboardApp()">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white shadow-lg h-16 flex items-center justify-between px-4 md:px-6 relative z-30">
        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-white/10 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h1 class="text-lg md:text-xl font-bold tracking-tight">Dashboard GIS</h1>
                <p class="text-xs text-blue-200 hidden sm:block">{{ $subtitle }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 md:gap-3">
            <form method="GET" action="{{ $formAction }}" class="hidden md:flex items-center gap-2">
                <select name="indicator_id" onchange="this.form.submit()" class="dark-select bg-slate-800 text-slate-100 border border-slate-700 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($indicators as $i)<option value="{{ $i->id }}" @selected($indicator?->id==$i->id)>{{ $i->nama }} ({{ $i->satuan }})</option>@endforeach
                </select>
                <select name="year" onchange="this.form.submit()" class="dark-select bg-slate-800 text-slate-100 border border-slate-700 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach([2024,2025,2026] as $y)<option @selected($year==$y)>{{ $y }}</option>@endforeach
                </select>
            </form>
            <button @click="aiPanelOpen = !aiPanelOpen"
                class="flex items-center gap-2 bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 px-3 py-1.5 rounded-lg font-medium text-sm hover:bg-emerald-500/30 transition"
                :class="aiPanelOpen ? 'bg-emerald-500/40' : ''">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                <span class="hidden sm:inline">Rekomendasi AI</span>
            </button>
            @if($guest)
                <a href="{{ route('login') }}" class="bg-emerald-500 text-white px-4 py-1.5 rounded-lg font-semibold text-sm hover:bg-emerald-600 transition">Login</a>
            @else
                <a href="{{ route('sektoral.dashboard') }}" class="bg-white/10 backdrop-blur border border-white/20 text-white px-4 py-1.5 rounded-lg font-semibold text-sm hover:bg-white/20 transition">Admin Panel</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="text-slate-300 text-sm hover:text-white px-2">Logout</button></form>
            @endif
        </div>
    </header>

    <div class="flex h-[calc(100vh-64px)] relative">
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 lg:hidden" x-transition.opacity></div>

        <!-- Sidebar -->
        <aside class="sidebar-transition custom-scrollbar w-72 bg-slate-800 border-r border-slate-700 overflow-y-auto flex flex-col gap-4 p-4 z-20 fixed lg:relative h-[calc(100vh-64px)] lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <!-- Pilih Wilayah -->
            <div x-data="kecSelect()" @kec-selected.window="syncSelected($event.detail)">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-2">Wilayah</h2>
                <div class="relative">
                    <button @click="open = !open" class="w-full flex items-center justify-between bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-200 hover:bg-slate-700 transition">
                        <span x-text="selected ? selected.nama : '-- Semua Kecamatan --'" class="truncate"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute z-30 mt-1 w-full bg-slate-800 border border-slate-600 rounded-lg shadow-xl overflow-hidden">
                        <div class="p-2 border-b border-slate-700">
                            <input x-model="q" placeholder="Cari kecamatan..." class="w-full bg-slate-900 border border-slate-600 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <ul class="max-h-56 overflow-y-auto custom-scrollbar py-1">
                            <li><button @click="clear()" class="w-full text-left px-3 py-2 text-sm text-slate-300 hover:bg-slate-700">-- Semua Kecamatan --</button></li>
                            <template x-for="o in filtered()" :key="o.id">
                                <li><button @click="choose(o)" class="w-full text-left px-3 py-2 text-sm text-slate-200 hover:bg-slate-700" x-text="o.nama"></button></li>
                            </template>
                            <li x-show="filtered().length === 0" class="px-3 py-2 text-xs text-slate-500">Tidak ditemukan.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Layer Peta</h2>
                <div class="space-y-1.5">
                    <template x-for="layer in layers" :key="layer.id">
                        <label class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer hover:bg-slate-700/50 transition group">
                            <input type="checkbox" :checked="layer.active" @change="toggleLayer(layer.id)"
                                   class="rounded border-slate-500 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-800 h-4 w-4">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background-color: ' + layer.color"></span>
                            <span class="text-slate-300 text-sm font-medium group-hover:text-white transition" x-text="layer.label"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-4">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Ringkasan Data</h2>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-blue-400" id="stat-desa">{{ number_format($stats['desa']) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Desa</p>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-red-400" id="stat-faskes">{{ number_format($stats['kesehatan']) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Faskes</p>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-400" id="stat-sekolah">{{ number_format($stats['pendidikan']) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Sekolah</p>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-orange-400" id="stat-umkm">{{ number_format($stats['umkm']) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">UMKM</p>
                    </div>
                </div>
                <button @click="resetView()" class="mt-3 w-full text-xs bg-slate-700/50 hover:bg-slate-700 text-slate-300 rounded-lg py-2 transition">Reset tampilan peta</button>
            </div>

            <div class="mt-auto pt-4 border-t border-slate-700">
                <p class="text-xs text-slate-500 text-center">Dash Sektoral Kab. Bandung &copy; {{ date('Y') }}</p>
            </div>
        </aside>

        <!-- Map + AI Panel -->
        <main class="flex-1 flex flex-col relative z-0">
            <div class="flex-1 relative"><div id="map"></div></div>

            <div x-show="aiPanelOpen" class="bg-slate-800 border-t border-slate-700 max-h-[40vh] overflow-y-auto custom-scrollbar">
                <div class="p-4 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-white">Rekomendasi AI</h3>
                        </div>
                        <button @click="aiPanelOpen = false" class="text-slate-400 hover:text-white transition p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @if($latestAi)
                        <p class="text-sm font-semibold text-slate-200 mb-2">{{ $latestAi->title }} <span class="text-xs text-slate-500">· {{ $latestAi->kecamatan->name ?? 'Kabupaten' }} · {{ $latestAi->created_at->diffForHumans() }}</span></p>
                        <div class="bg-slate-700/50 rounded-xl p-4"><p class="text-slate-300 text-sm leading-relaxed whitespace-pre-line">{{ $latestAi->content }}</p></div>
                        @if($latestAi->reasoning)<p class="text-xs text-slate-500 mt-2">{{ $latestAi->reasoning }}</p>@endif
                    @else
                        <div class="text-center py-6">
                            <p class="text-slate-500 text-sm">Belum ada rekomendasi AI.</p>
                            @if($guest)
                                <p class="text-slate-600 text-xs mt-1">Generate melalui <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Login → AI Recommendation</a>.</p>
                            @else
                                <p class="text-slate-600 text-xs mt-1">Generate melalui <a href="{{ route('ai.create') }}" class="text-blue-400 hover:underline">AI Recommendation</a>.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const IND_ID = @json($indicator?->id);
        const YEAR = @json($year);
        const IND_NAME = @json($indicator?->nama ?? 'Indikator');
        const IND_SAT = @json($indicator?->satuan ?? '');
        const MODULES = @json($modules);
        const MOD_COLORS = { 'M-03': '#22c55e', 'M-04': '#ef4444', 'M-05': '#eab308', 'M-06': '#f97316', 'M-07': '#a855f7', 'M-08': '#06b6d4' };
        const FOCUS_GEO = @json($focusGeoName);
        const FOCUS_CENTER = @json($focusKec ? [(float) $focusKec->center_lat, (float) $focusKec->center_lng] : null);
        const norm = s => (s || '').toLowerCase().replace(/\s+/g, '');
        const fmt = n => new Intl.NumberFormat('id-ID').format(n ?? 0);
        const PALETTE = ['#f7fcf5', '#c7e9c0', '#74c476', '#31a354', '#006d2c'];
        const INIT_STATS = @json($stats);

        /** Combobox pilih kecamatan: search + urut abjad asc. */
        function kecSelect() {
            return {
                open: false, q: '', options: [], selected: null,
                async init() {
                    const j = await fetch(`/api/gis/choropleth?indicator_id=${IND_ID}&year=${YEAR}`).then(r => r.json());
                    this.options = j.data.map(d => ({ id: d.id, nama: d.nama, geo_nama: d.geo_nama }))
                        .sort((a, b) => a.nama.localeCompare(b.nama, 'id'));
                    if (FOCUS_GEO) {
                        const f = this.options.find(o => norm(o.geo_nama) === norm(FOCUS_GEO));
                        if (f) this.selected = f;
                    }
                },
                filtered() {
                    const q = norm(this.q);
                    return this.options.filter(o => !q || norm(o.nama).includes(q));
                },
                choose(o) { this.selected = o; this.open = false; this.q = ''; window.selectKecamatan(o); },
                clear() { this.selected = null; this.open = false; this.q = ''; window.selectKecamatan(null); },
                syncSelected(nama) {
                    if (!nama) { this.selected = null; return; }
                    const hit = () => this.options.find(o => o.nama === nama);
                    const found = hit();
                    if (found) this.selected = found;
                    else setTimeout(() => { const f2 = hit(); if (f2) this.selected = f2; }, 800);
                },
            };
        }

        function dashboardApp() {
            const layers = [
                { id: 'kecamatan', label: 'Batas Kecamatan', color: '#a855f7', active: true },
                { id: 'desa', label: 'Batas Desa', color: '#3b82f6', active: true },
            ];
            Object.entries(MODULES).forEach(([kode, nama]) => layers.push({ id: kode, label: nama, color: MOD_COLORS[kode] || '#94a3b8', active: true }));
            return {
                sidebarOpen: false,
                aiPanelOpen: false,
                layers,
                toggleLayer(id) {
                    const layer = this.layers.find(l => l.id === id);
                    if (!layer || !window.layerGroups[id]) return;
                    layer.active = !layer.active;
                    layer.active ? window.mapInstance.addLayer(window.layerGroups[id]) : window.mapInstance.removeLayer(window.layerGroups[id]);
                },
                resetView() { if (window.resetGisView) window.resetGisView(); },
                init() { this.$nextTick(() => initMap()); }
            };
        }

        let choroData = {}, breaks = [], kecLayer = null, selectedKec = null;

        function colorFor(v) {
            if (v == null) return '#475569';
            for (let i = breaks.length - 1; i >= 0; i--) if (v >= breaks[i]) return PALETTE[Math.min(i + 1, 4)];
            return PALETTE[0];
        }
        function bindKecLabel(layer) {
            layer.bindTooltip(`<b>${layer.feature.properties.KECAMATAN}</b>`, { permanent: true, direction: 'center', className: 'kecamatan-polygon-label' });
        }

        /** Pilih wilayah: tampilkan hanya kecamatan tsb (poligon, desa, marker, statistik). */
        window.selectKecamatan = async function (opt) {
            if (!window.mapInstance || !kecLayer) return;
            document.dispatchEvent(new CustomEvent('kec-selected', { detail: opt ? opt.nama : null }));
            if (!opt) { // -- Semua Kecamatan --
                selectedKec = null;
                window.layerGroups.desa.clearLayers();
                kecLayer.eachLayer(l => { l.setStyle(styleKec(l.feature)); bindKecLabel(l); });
                window.mapInstance.fitBounds(kecLayer.getBounds());
                const counts = await loadMarkers(null);
                setStats(INIT_STATS.desa, counts['M-04'] || 0, counts['M-03'] || 0, counts['M-06'] || 0);
                return;
            }
            const target = kecLayer.getLayers().find(l => norm(l.feature.properties.KECAMATAN) === norm(opt.geo_nama || opt.nama));
            if (!target) return;
            kecLayer.eachLayer(l => {
                if (l === target) { l.setStyle(styleKec(l.feature)); bindKecLabel(l); }
                else { l.setStyle({ color: '#475569', weight: 0.5, opacity: 0.25, fillOpacity: 0.02 }); l.unbindTooltip(); }
            });
            const desaCount = await drillDown(target.feature.properties.KECAMATAN, target);
            const counts = await loadMarkers(opt.id);
            setStats(desaCount, counts['M-04'] || 0, counts['M-03'] || 0, counts['M-06'] || 0);
        };

        function setStats(desa, faskes, sekolah, umkm) {
            const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = fmt(v); };
            set('stat-desa', desa); set('stat-faskes', faskes); set('stat-sekolah', sekolah); set('stat-umkm', umkm);
        }

        async function loadMarkers(kecId) {
            Object.keys(MODULES).forEach(k => window.layerGroups[k].clearLayers());
            const url = kecId ? `/api/gis/facilities?kecamatan_id=${kecId}` : '/api/gis/facilities';
            const fac = await fetch(url).then(r => r.json()).catch(() => []);
            const counts = {};
            fac.forEach(p => {
                if (p.latitude == null) return;
                counts[p.modul] = (counts[p.modul] || 0) + 1;
                const color = MOD_COLORS[p.modul] || '#94a3b8';
                const icon = L.divIcon({ className: '',
                    html: `<div style="background:${color};width:13px;height:13px;border-radius:50%;border:2.5px solid rgba(255,255,255,.9);box-shadow:0 0 8px ${color}80;"></div>`,
                    iconSize: [13, 13], iconAnchor: [7, 7] });
                const popup = `<div style="min-width:190px;font-family:Inter,sans-serif;">` +
                    `<div style="background:${color};color:#fff;padding:6px 10px;border-radius:6px;font-weight:700;font-size:13px;margin:-12px -16px 8px -16px;">${p.name}</div>` +
                    `<div style="font-size:12px;color:#374151;">${p.modul} / ${p.type}<br>Desa ${p.village?.nama ?? '-'} · Kec. ${p.kecamatan?.nama ?? '-'}</div></div>`;
                L.marker([p.latitude, p.longitude], { icon }).bindPopup(popup).addTo(window.layerGroups[p.modul] || window.layerGroups.kecamatan);
            });

            return counts;
        }

        function styleKec(f) {
            const d = choroData[norm(f.properties.KECAMATAN)];
            return { color: '#a855f7', weight: selectedKec === f.properties.KECAMATAN ? 3 : 1.5, opacity: 0.9,
                     fillColor: colorFor(d?.value), fillOpacity: d?.value != null ? 0.45 : 0.08 };
        }

        async function initMap() {
            const map = L.map('map', { zoomControl: false }).setView([-7.03, 107.62], 10);
            L.control.zoom({ position: 'topright' }).addTo(map);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OSM &copy; CARTO', maxZoom: 19 }).addTo(map);
            window.mapInstance = map;
            window.layerGroups = { kecamatan: L.layerGroup().addTo(map), desa: L.layerGroup().addTo(map) };
            Object.keys(MODULES).forEach(k => window.layerGroups[k] = L.layerGroup().addTo(map));

            // Choropleth data
            const j = await fetch(`/api/gis/choropleth?indicator_id=${IND_ID}&year=${YEAR}`).then(r => r.json());
            const vals = [];
            j.data.forEach(d => { choroData[norm(d.geo_nama)] = d; if (d.value != null) vals.push(parseFloat(d.value)); });
            vals.sort((a, b) => a - b);
            breaks = vals.length ? [0, 1, 2, 3].map(i => vals[Math.floor(vals.length * (i + 1) / 5)] || 0) : [];

            // Kecamatan polygons
            const gj = await fetch('/maps/kecamatan.json').then(r => r.json());
            kecLayer = L.geoJSON(gj, { style: styleKec,
                onEachFeature: (f, layer) => {
                    bindKecLabel(layer);
                    layer.on('click', () => {
                        const d = choroData[norm(f.properties.KECAMATAN)];
                        window.selectKecamatan(d ? { id: d.id, nama: d.nama, geo_nama: d.geo_nama } : null);
                    });
                }}).addTo(window.layerGroups.kecamatan);

            await loadMarkers(null);

            if (FOCUS_GEO) {
                const d = choroData[norm(FOCUS_GEO)];
                if (d) { await window.selectKecamatan({ id: d.id, nama: d.nama, geo_nama: d.geo_nama }); return; }
                if (FOCUS_CENTER) map.setView(FOCUS_CENTER, 12);
            } else {
                map.fitBounds(kecLayer.getBounds());
            }

            window.resetGisView = () => {
                selectedKec = null;
                window.layerGroups.desa.clearLayers();
                kecLayer.setStyle(styleKec);
                map.fitBounds(kecLayer.getBounds());
            };
        }

        async function drillDown(nama, layer) {
            selectedKec = nama;
            kecLayer.setStyle(styleKec);
            window.mapInstance.fitBounds(layer.getBounds());
            window.layerGroups.desa.clearLayers();
            const gj = await fetch(`/api/gis/desa?kecamatan=${encodeURIComponent(nama)}`).then(r => r.json());
            L.geoJSON(gj, { style: { color: '#3b82f6', weight: 1.5, opacity: 0.7, fillColor: '#3b82f6', fillOpacity: 0.08 },
                onEachFeature: (f, l) => {
                    l.bindTooltip(`<b>${f.properties.DESA}</b>`, { permanent: true, direction: 'center', className: 'desa-polygon-label' });
                    const luas = f.properties.Luas_Ha ? (f.properties.Luas_Ha / 100).toFixed(2) : '-';
                    l.bindPopup(`<div style="min-width:200px;font-family:Inter,sans-serif;">` +
                        `<div style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;padding:8px 12px;border-radius:8px 8px 0 0;margin:-12px -16px 8px -16px;font-weight:700;">Desa ${f.properties.DESA}</div>` +
                        `<div style="font-size:12px;color:#374151;">Kec. ${f.properties.KECAMATAN}<br>Luas: ${luas} km²</div></div>`);
                }}).addTo(window.layerGroups.desa);
            const d = choroData[norm(nama)] || {};
            const luas = layer.feature.properties.Luas_Ha ? (layer.feature.properties.Luas_Ha / 100).toFixed(2) : '-';
            layer.bindPopup(`<div style="min-width:230px;font-family:Inter,sans-serif;">` +
                `<div style="background:linear-gradient(135deg,#6b21a8,#a855f7);color:#fff;padding:10px 14px;border-radius:8px 8px 0 0;margin:-12px -16px 10px -16px;">` +
                `<div style="font-size:15px;font-weight:700;">Kecamatan ${nama}</div><div style="font-size:11px;opacity:.85;">Kabupaten Bandung</div></div>` +
                `<table style="width:100%;font-size:12px;border-collapse:collapse;">` +
                `<tr><td style="padding:3px 0;color:#6b7280;">${IND_NAME} (${YEAR})</td><td style="text-align:right;font-weight:700;color:#9333ea;">${d.value != null ? fmt(d.value) + ' ' + IND_SAT : 'n/a'}</td></tr>` +
                `<tr><td style="padding:3px 0;color:#6b7280;">Luas Wilayah</td><td style="text-align:right;font-weight:600;">${luas} km²</td></tr>` +
                `<tr><td style="padding:3px 0;color:#6b7280;">Jumlah Desa</td><td style="text-align:right;font-weight:600;">${gj.features.length}</td></tr>` +
                `<tr><td style="padding:3px 0;color:#6b7280;">Fasilitas</td><td style="text-align:right;font-weight:600;">${d.facilities ?? 0}</td></tr>` +
                `<tr><td style="padding:3px 0;color:#6b7280;">Penduduk</td><td style="text-align:right;font-weight:600;">${fmt(d.penduduk)} jiwa</td></tr>` +
                `</table></div>`, { maxWidth: 290 }).openPopup();

            return gj.features.length;
        }
    </script>
</body>
</html>
