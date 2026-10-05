{{-- Map picker interaktif (FR-GEO-02 s/d FR-GEO-11). Params: $kecamatan, $existing, $latField, $lngField --}}
<div class="border rounded overflow-hidden">
  <div class="flex flex-wrap gap-2 p-2 bg-slate-50">
    <input id="geo-search" placeholder="Cari lokasi... (min 3 huruf)" class="border rounded px-2 py-1 text-sm flex-1 min-w-[180px]">
    <button type="button" id="btn-search" class="text-sm bg-slate-200 rounded px-2 py-1">Cari</button>
    <button type="button" id="btn-gps" class="text-sm bg-slate-200 rounded px-2 py-1">📍 Lokasi Saya</button>
    <span id="geo-status" class="text-xs text-slate-500 self-center">Klik peta / drag marker untuk memilih titik.</span>
  </div>
  <div id="map-picker" style="height:380px"></div>
  <div class="p-2 text-xs bg-slate-50 flex gap-4">
    <span>🔵 lokasi baru (draggable)</span><span class="text-slate-400">⚪ existing</span>
    <span id="dup-warn" class="text-amber-600 font-semibold"></span>
  </div>
</div>
@push('scripts')
<script>
(function(){
  const latEl = document.getElementById(@json($latField ?? 'latitude'));
  const lngEl = document.getElementById(@json($lngField ?? 'longitude'));
  const accEl = document.getElementById('accuracy_meters');
  const kecId = @json($kecamatan?->id);
  const defLat = parseFloat(latEl?.value) || @json((float)($kecamatan?->center_lat ?? -7.025));
  const defLng = parseFloat(lngEl?.value) || @json((float)($kecamatan?->center_lng ?? 107.525));
  const map = L.map('map-picker').setView([defLat, defLng], 13);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '© OpenStreetMap'}).addTo(map);

  // Batas kecamatan (FR-GEO-05 auto-zoom, FR-GEO-08 visual)
  if (kecId) {
    fetch(`/api/kecamatans/${kecId}/boundary`).then(r => r.json()).then(b => {
      if (b.bounds && b.bounds[0][0]) {
        L.rectangle(b.bounds, {color: 'red', dashArray: '6 4', fill: false}).addTo(map);
        if (!latEl?.value) map.fitBounds(b.bounds);
      }
    });
  }
  // Existing markers (FR-GEO-09)
  @json($existing ?? []).forEach(p => {
    if (p.latitude) L.circleMarker([p.latitude, p.longitude], {radius: 4, color: '#999'}).bindTooltip(p.name).addTo(map);
  });

  const marker = L.marker([defLat, defLng], {draggable: true}).addTo(map);
  const status = document.getElementById('geo-status');

  async function sync(lat, lng, src) {
    latEl.value = lat.toFixed(6); lngEl.value = lng.toFixed(6);
    const srcEl = document.getElementById('location_source'); if (srcEl && src) srcEl.value = src;
    // validasi server (FR-GEO-08) + cek duplikasi (FR-GEO-09)
    try {
      const v = await fetch('/api/validate-point', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? document.querySelector('input[name=_token]')?.value}, body: JSON.stringify({latitude: lat, longitude: lng, kecamatan_id: kecId})}).then(r => r.json());
      status.textContent = v.valid ? '✅ ' + (v.message || 'valid') : '⛔ ' + v.message;
      status.className = 'text-xs self-center ' + (v.valid ? 'text-emerald-600' : 'text-red-600');
    } catch(e) { status.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6); }
    try {
      const n = await fetch(`/api/facilities/nearby?latitude=${lat}&longitude=${lng}&radius_m=100`).then(r => r.json());
      document.getElementById('dup-warn').textContent = n.warning ?? '';
    } catch(e) {}
  }
  marker.on('dragend', () => { const p = marker.getLatLng(); sync(p.lat, p.lng); });
  map.on('click', e => { marker.setLatLng(e.latlng); sync(e.latlng.lat, e.latlng.lng, 'map_click'); });
  // Manual input (FR-GEO-04)
  [latEl, lngEl].forEach(el => el?.addEventListener('change', () => {
    const la = parseFloat(latEl.value), ln = parseFloat(lngEl.value);
    if (!isNaN(la) && !isNaN(ln)) { marker.setLatLng([la, ln]); map.setView([la, ln], 15); sync(la, ln, 'manual_input'); }
  }));
  // Geolocation (FR-GEO-07 + FR-GEO-11)
  document.getElementById('btn-gps')?.addEventListener('click', () => {
    if (!navigator.geolocation) return alert('Browser tidak mendukung geolocation.');
    navigator.geolocation.getCurrentPosition(pos => {
      const {latitude, longitude, accuracy} = pos.coords;
      marker.setLatLng([latitude, longitude]); map.setView([latitude, longitude], 16);
      if (accEl) accEl.value = Math.round(accuracy);
      sync(latitude, longitude, 'gps');
    }, err => alert('Gagal mendapatkan lokasi: ' + err.message), {enableHighAccuracy: true});
  });
  // Search Nominatim (FR-GEO-06)
  async function doSearch() {
    const q = document.getElementById('geo-search').value;
    if (q.length < 3) return;
    const r = await fetch(`/api/geocode/search?q=${encodeURIComponent(q)}`).then(r => r.json());
    if (Array.isArray(r) && r.length) {
      const p = r[0]; marker.setLatLng([p.lat, p.lon]); map.setView([p.lat, p.lon], 16);
      sync(parseFloat(p.lat), parseFloat(p.lon), 'geocoding');
    } else alert('Lokasi tidak ditemukan.');
  }
  document.getElementById('btn-search')?.addEventListener('click', doSearch);
})();
</script>
@endpush
