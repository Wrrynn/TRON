@extends('layouts.app')
@section('title', 'Daftar — Tripmo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('content')
<div class="auth-wrap">

    <div class="auth-left">
        <div id="auth-map"></div>
        <div class="auth-left-overlay">
            <a href="{{ url('/') }}" class="auth-header">
                <div class="AppLogo"></div>
                <span class="AppTitle">Tripmo</span>
            </a>
            <div class="auth-box">
                <div class="auth-tag">Mulai Perjalanan</div>
                <h1 class="auth-left-title">
                    Jelajahi dan<br><span>Ceritakan dunia</span>
                </h1>
                <p class="auth-left-desc">
                    Bergabung dengan komunitas traveler Indonesia dan jadikan setiap liburanmu inspirasi bagi orang lain.
                </p>
            </div>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-box">

            <p class="auth-eyebrow">Buat akun baru</p>
            <h2 class="auth-title">Daftar ke Tripmo</h2>

            <form action="{{ route('register.post') }}" method="POST" class="auth-form">
                @csrf

                <div class="fg">
                    <label class="fl" for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name"
                        class="fi {{ $errors->has('name') ? 'is-err' : '' }}"
                        placeholder="Nama kamu" value="{{ old('name') }}" autofocus>
                    @error('name')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <div class="fg">
                    <label class="fl" for="email">Email</label>
                    <input type="email" id="email" name="email"
                        class="fi {{ $errors->has('email') ? 'is-err' : '' }}"
                        placeholder="nama@email.com" value="{{ old('email') }}">
                    @error('email')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <div class="fg">
                    <label class="fl" for="password">Password</label>
                    <input type="password" id="password" name="password"
                        class="fi {{ $errors->has('password') ? 'is-err' : '' }}"
                        placeholder="Minimal 8 karakter">
                    <span class="hint">Minimal 8 karakter</span>
                    @error('password')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <div class="fg">
                    <label class="fl" for="password_confirmation">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="fi" placeholder="Ulangi password">
                </div>

                <button type="submit" class="btn btn-purple btn-full" style="margin-top:4px">
                    Buat Akun
                </button>
            </form>

            <div class="auth-foot">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
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