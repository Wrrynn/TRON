@extends('layouts.app')
@section('title', 'Masuk — Tripmo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth-wrap">

    {{-- KIRI --}}
    <div class="auth-left">
        <div id="auth-map"></div>

        <div class="auth-left-overlay">
            <a href="{{ url('/') }}" class="auth-header">
                <div class="AppLogo"></div>
                <span class="AppTitle">Tripmo</span>
            </a>
            <div class="auth-box">
                <div class="auth-tag">Platform Perjalanan</div>
                <h1 class="auth-left-title">
                    Rekam setiap<br>
                    <span>petualanganmu</span>
                </h1>
                <p class="auth-left-desc">
                    Dokumentasikan perjalanan, bagikan cerita, dan temukan inspirasi destinasi dari traveler lain.
                </p>
            </div>
        </div>
    </div>

    {{-- KANAN --}}
    <div class="auth-right glass-card">
        <div class="auth-box">

            <p class="auth-eyebrow">Selamat datang</p>
            <h2 class="auth-title">Masuk ke Tripmo</h2>

            @if($errors->any())
            <div class="alert-err">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="auth-form">
                @csrf

                <div class="fg">
                    <label class="fl" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="fi {{ $errors->has('email') ? 'is-err' : '' }}"
                        placeholder="nama@email.com"
                        value="{{ old('email') }}" autofocus>
                    @error('email')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <div class="fg">
                    <label class="fl" for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="fi" placeholder="••••••••">
                </div>

                <div class="check-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-purple btn-full" style="margin-top:6px">
                    Masuk
                </button>
            </form>

            <div class="auth-foot">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const map = L.map('auth-map', {
        center: [-2.5, 118],
        zoom: 5,
        zoomControl: false,
        scrollWheelZoom: false,
        dragging: false,
        attributionControl: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
</script>
@endpush