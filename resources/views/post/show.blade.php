@extends('layouts.app')
@section('title', $post->title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/post-show.css') }}">
@endpush

@section('content')
@php
    $dests = $post->destinations
        ? (is_array($post->destinations) ? $post->destinations : json_decode($post->destinations, true))
        : [];
@endphp

<div class="show-wrap">

    <div class="show-left">

        {{-- Foto --}}
        <div class="foto-header">
    <a href="{{ route('dashboard') }}" class="btn-kembali">Kembali</a>

    @if($post->photos->count() > 0)
        <div class="foto-slider" id="fotoSlider">
            @foreach($post->photos as $foto)
                <div class="foto-slide">
                    <img src="{{ Storage::url($foto->file_path) }}" alt="">
                </div>
            @endforeach
        </div>

        @if($post->photos->count() > 1)
            <button class="slider-btn slider-prev" onclick="slidePhoto(-1)">‹</button>
            <button class="slider-btn slider-next" onclick="slidePhoto(1)">›</button>
            <div class="slider-dots" id="sliderDots">
                @foreach($post->photos as $i => $foto)
                    <div class="slider-dot {{ $i === 0 ? 'active' : '' }}" onclick="goSlide({{ $i }})"></div>
                @endforeach
            </div>
        @endif
    @endif
</div>

        <div class="show-content">

            <div class="show-meta">
                <span>{{ $post->travel_date ?? '-' }}</span>
                <span>{{ $post->location }}</span>
                @if(count($dests) > 0)<span>{{ count($dests) }} Destinasi</span>@endif
            </div>

            <div class="show-title">{{ $post->title }}</div>

            <div class="show-author">
                <div class="author-av">{{ strtoupper(substr($post->user->name, 0, 1)) }}</div>
                <div>
                    <div class="author-name">{{ $post->user->name }}</div>
                    @if($post->user->bio)
                    <div class="author-role">{{ $post->user->bio }}</div>
                    @endif
                </div>
            </div>

            @if($post->story)
                <div class="section-title">Cerita Perjalanan</div>
                <div class="show-story">{{ $post->story }}</div>
            @endif

            @if(count($dests) > 0)
                <div class="section-title">Rute & Destinasi</div>
                <div style="margin-bottom:28px">
                    @foreach($dests as $i => $d)
                        <div class="rute-item" onclick="focusMap({{ $i }})">
                            <div class="rute-num" style="background:{{ $i===0 ? '#7c5cfc' : ($i===count($dests)-1 ? '#7c5cfc' : '#6366f1') }}">{{ $i+1 }}</div>
                            <span class="rute-text">{{ is_array($d) ? $d['name'] : $d }}</span>
                            <span style="color:rgba(255,255,255,.25)">›</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="section-title">Budget</div>
            <div class="budget-section">
                <span class="budget-label">Total Pengeluaran</span>
                <span class="budget-amount">Rp {{ number_format($post->total_budget, 0, ',', '.') }}</span>
            </div>

            @if(auth()->id() !== $post->user_id)
                <div class="section-title">Beri Rating</div>
                <form action="{{ route('post.rate', $post->id) }}" method="POST"
                      style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
                    @csrf
                    <div id="stars" style="display:flex">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="star-btn"
                                    style="color:{{ $myRating && $myRating->score >= $i ? '#fbbf24' : 'rgba(255,255,255,.2)' }}"
                                    onclick="setStar({{ $i }})">★</button>
                        @endfor

                    </div>
                    <input type="hidden" name="score" id="scoreVal" value="{{ $myRating->score ?? '' }}">
                    <button type="submit" style="background:#7c5cfc;color:white;border:none;border-radius:8px;padding:9px 18px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer">Simpan</button>
                </form>
            @endif

            @if(auth()->id() === $post->user_id)
    <div style="display:flex; gap:10px; margin-top:8px">
        <a href="{{ route('post.edit', $post->id) }}"
           style="flex:1; text-align:center; padding:11px; border-radius:9px;
                  border:1px solid rgba(255,255,255,.15); color:rgba(255,255,255,.7);
                  text-decoration:none; font-size:14px; font-weight:500">
            Edit Postingan
        </a>
        <form action="{{ route('post.destroy', $post->id) }}" method="POST"
              onsubmit="return confirm('Hapus postingan ini?')" style="flex:1">
            @csrf @method('DELETE')
            <button type="submit"
                    style="width:100%; padding:11px; border-radius:9px;
                           background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.2);
                           color:#f87171; font-family:inherit; font-size:14px;
                           font-weight:500; cursor:pointer">
                Hapus Postingan
            </button>
        </form>
    </div>
@endif

        </div>
    </div>

    <div class="show-right">
        <div id="detail-map"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function setStar(val) {
    document.getElementById('scoreVal').value = val;
    document.querySelectorAll('#stars .star-btn').forEach((b,i) => {
        b.style.color = i < val ? '#fbbf24' : 'rgba(255,255,255,.2)';
    });
}

const detailMap = L.map('detail-map');
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(detailMap);

const dests = @json($dests);
const markers = [];

if (dests.length > 0) {
    const titik = dests.map(d => [d.lat, d.lng]);
    L.polyline(titik, { color:'#7c5cfc', weight:3, dashArray:'8 4' }).addTo(detailMap);
    dests.forEach((d, i) => {
        const w = i===0 ? '#7c5cfc' : (i===dests.length-1 ? '#e8410a' : '#6366f1');
        const ikon = L.divIcon({
            html: `<div style="width:28px;height:28px;background:${w};border:2px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:white">${i+1}</div>`,
            iconSize:[28,28], iconAnchor:[14,14], className:''
        });
        const m = L.marker([d.lat, d.lng], {icon:ikon}).addTo(detailMap).bindPopup(`<b>${d.name}</b>`);
        markers.push(m);
    });
    titik.length > 1 ? detailMap.fitBounds(titik, {padding:[40,40]}) : detailMap.setView(titik[0], 13);
} else {
    detailMap.setView([-6.9, 107.6], 12);
}

function focusMap(i) {
    document.querySelectorAll('.rute-item').forEach((el,j) => el.classList.toggle('active', i===j));
    const d = dests[i];
    detailMap.setView([d.lat, d.lng], 15);
    markers[i].openPopup();
}

// Slider foto
let slideIndex = 0;
const slides = document.querySelectorAll('.foto-slide');
const dots = document.querySelectorAll('.slider-dot');

function goSlide(n) {
    slideIndex = n;
    document.getElementById('fotoSlider').style.transform = `translateX(-${slideIndex * 100}%)`;
    dots.forEach((d, i) => d.classList.toggle('active', i === slideIndex));
}

function slidePhoto(dir) {
    slideIndex = (slideIndex + dir + slides.length) % slides.length;
    goSlide(slideIndex);
}
</script>
@endpush