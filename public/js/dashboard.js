// PETA
const map = L.map('main-map', { center: [-6.9, 107.6], zoom: 13 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(map);

// Klik peta 
let pinAktif = null;
const pinIkon = L.divIcon({
    html: `<svg width="28" height="36" viewBox="0 0 28 36" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 0C6.268 0 0 6.268 0 14c0 9.333 14 22 14 22S28 23.333 28 14C28 6.268 21.732 0 14 0z" fill="#e8410a"/>
        <circle cx="14" cy="14" r="6" fill="white"/>
    </svg>`,
    iconSize: [28,36], iconAnchor: [14,36], popupAnchor: [0,-38], className: ''
});

map.on('click', function(e) {
    if (pinAktif) map.removeLayer(pinAktif);
    pinAktif = L.marker(e.latlng, { icon: pinIkon })
        .addTo(map)
        .bindPopup(`📍 ${e.latlng.lat.toFixed(5)}, ${e.latlng.lng.toFixed(5)}`)
        .openPopup();
});


// PANEL
let currentPanel = null;
const panels = {
    profile: document.getElementById('panelProfile'),
    search:  document.getElementById('panelSearch'),
    create:  document.getElementById('panelCreate'),
};
const btns = {
    profile: document.getElementById('btnProfile'),
    search:  document.getElementById('btnSearch'),
    create:  document.getElementById('btnCreate'),
};

function openPanel(name) {
    if (currentPanel === name) { closePanel(); return; }
    Object.values(panels).forEach(p => p && (p.style.display = 'none'));
    Object.values(btns).forEach(b => b && b.classList.remove('active'));
    if (panels[name]) panels[name].style.display = 'block';
    if (btns[name]) btns[name].classList.add('active');
    document.getElementById('sidePanel').classList.add('open');
    currentPanel = name;
    setTimeout(() => map.invalidateSize(), 300);
}

function closePanel() {
    document.getElementById('sidePanel').classList.remove('open');
    Object.values(panels).forEach(p => p && (p.style.display = 'none'));
    Object.values(btns).forEach(b => b && b.classList.remove('active'));
    currentPanel = null;
    setTimeout(() => map.invalidateSize(), 300);
}

// DESTINASI
let dests = [];
let ruteLayer = null;
let timer = null;

async function tambahDest() {
    const input = document.getElementById('pDestInput');
    const val = input.value.trim();
    if (!val) return;

    const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(val)}&format=json&limit=1`);
    const data = await res.json();
    if (!data.length) { alert('Lokasi tidak ditemukan!'); return; }

    dests.push({ name: data[0].display_name.split(',')[0], lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) });
    input.value = '';
    document.getElementById('destSaran').style.display = 'none';
    tampilRute();
    updatePeta();
}

document.getElementById('pDestInput').addEventListener('input', function() {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 3) { document.getElementById('destSaran').style.display = 'none'; return; }
    timer = setTimeout(async () => {
        const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=4`);
        const data = await res.json();
        const box  = document.getElementById('destSaran');
        if (!data.length) { box.style.display = 'none'; return; }
        box.innerHTML = data.map(d =>
            `<div class="dest-saran" onclick="pilihDest('${d.display_name.split(',')[0].replace(/'/g,"\\'")}',${d.lat},${d.lon})">
                📍 ${d.display_name.split(',').slice(0,2).join(',')}
            </div>`).join('');
        box.style.display = 'block';
    }, 400);
});

function pilihDest(name, lat, lng) {
    dests.push({ name, lat: parseFloat(lat), lng: parseFloat(lng) });
    document.getElementById('pDestInput').value = '';
    document.getElementById('destSaran').style.display = 'none';
    tampilRute();
    updatePeta();
}

function hapusDest(i) {
    dests.splice(i, 1);
    tampilRute();
    updatePeta();
}

function tampilRute() {
    const list = document.getElementById('listDest');
    list.innerHTML = '';
    dests.forEach((d, i) => {
        const kelas = i === 0 ? 'start' : (i === dests.length - 1 ? 'end' : 'middle');
        const div = document.createElement('div');
        div.className = 'route-item';
        div.innerHTML = `
            <div class="route-dot ${kelas}">${i+1}</div>
            <div class="route-card">
                <div>
                    <div class="route-name">📍 ${d.name}</div>
                    <div class="route-coords">${d.lat.toFixed(4)}, ${d.lng.toFixed(4)}</div>
                </div>
                <button class="route-remove" type="button" onclick="hapusDest(${i})">×</button>
            </div>`;
        list.appendChild(div);
    });
    document.getElementById('destData').value    = JSON.stringify(dests);
    document.getElementById('lokasiUtama').value = dests.length > 0 ? dests[0].name : '';
}

function updatePeta() {
    if (ruteLayer) map.removeLayer(ruteLayer);
    map.eachLayer(l => { if (l._tripmo) map.removeLayer(l); });
    if (dests.length === 0) return;

    const titik = dests.map(d => [d.lat, d.lng]);
    ruteLayer = L.polyline(titik, { color: '#7c5cfc', weight: 3, dashArray: '8 4' }).addTo(map);

    dests.forEach((d, i) => {
        const warna = i === 0 ? '#7c5cfc' : (i === dests.length-1 ? '#7c5cfc' : '#6366f1');
        const ikon = L.divIcon({
            html: `<div style="width:26px;height:26px;background:${warna};border:2px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white">${i+1}</div>`,
            iconSize: [26,26], iconAnchor: [13,13], className: ''
        });
        const m = L.marker([d.lat, d.lng], { icon: ikon }).addTo(map).bindPopup(`<b>${d.name}</b>`);
        m._tripmo = true;
    });

    if (titik.length > 1) map.fitBounds(titik, { padding: [40,40] });
    else map.setView(titik[0], 13);
}

document.getElementById('pDestInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); tambahDest(); }
});

// PREVIEW FOTO
function previewFoto(input) {
    const preview = document.getElementById('pFotoPreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.style.cssText = 'aspect-ratio:1;border-radius:6px;overflow:hidden';
            div.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}