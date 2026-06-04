@extends('layouts.app')
@section('title', 'Edit Profil — Tripmo')

@section('content')
<div style="max-width:480px; margin:80px auto; padding:0 20px">
    <div style="background:#222; border:1px solid rgba(255,255,255,.1);
                border-radius:16px; padding:32px">

        <h2 style="color:white; margin-bottom:24px; font-size:20px">Edit Profil</h2>

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf

            <div style="margin-bottom:20px">
                <label style="color:rgba(255,255,255,.6); font-size:13px;
                              display:block; margin-bottom:6px">Nama</label>
                <input type="text" name="name"
                       value="{{ old('name', auth()->user()->name) }}"
                       style="width:100%; background:#2a2a2a;
                              border:1px solid rgba(255,255,255,.15);
                              border-radius:10px; padding:12px 14px;
                              color:white; font-size:14px; outline:none;
                              box-sizing:border-box">
                @error('name')
                    <span style="color:#f87171; font-size:12px">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:flex; gap:10px">
                <a href="{{ route('dashboard') }}"
                   style="flex:1; text-align:center; padding:12px; border-radius:10px;
                          border:1px solid rgba(255,255,255,.15); color:rgba(255,255,255,.6);
                          text-decoration:none; font-size:14px">
                    Batal
                </a>
                <button type="submit"
                        style="flex:1; padding:12px; border-radius:10px;
                               background:#7c5cfc; border:none; color:white;
                               font-size:14px; font-weight:600; cursor:pointer">
                    Simpan
                </button>
            </div>
        </form>

        {{-- Zona berbahaya: hapus akun --}}
        <div style="margin-top:28px; padding-top:22px; border-top:1px solid rgba(255,255,255,.08)">
            <div style="color:#f87171; font-size:13px; font-weight:700; margin-bottom:6px">Zona Berbahaya</div>
            <div style="color:rgba(255,255,255,.45); font-size:12px; line-height:1.5; margin-bottom:14px">
                Menghapus akun akan menghapus permanen seluruh postingan, foto, dan rating kamu.
                Tindakan ini tidak dapat dibatalkan.
            </div>
            <form action="{{ route('profile.delete') }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus akun secara permanen? Semua data kamu akan hilang dan tidak bisa dikembalikan.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        style="width:100%; padding:12px; border-radius:10px;
                               background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3);
                               color:#f87171; font-size:14px; font-weight:600; cursor:pointer">
                    Hapus Akun Saya
                </button>
            </form>
        </div>
    </div>
</div>
@endsection