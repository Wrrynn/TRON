<?php

use Illuminate\Support\Facades\Storage;

if (!function_exists('foto_url')) {
    /**
     * Resolve URL foto untuk ditampilkan di view:
     * 1. Cloudinary URL  → pakai langsung (CDN)
     * 2. File lokal ada  → asset('storage/...') — mengikuti port server aktif
     * 3. File tidak ada  → SVG placeholder
     */
    function foto_url(?string $path): string
    {
        if (!$path) return _foto_placeholder();

        // 1. Cloudinary URL — sudah berupa full URL
        if (str_starts_with($path, 'https://')) {
            return $path;
        }

        // 2. File lokal — cek keberadaan lalu buat URL via asset()
        try {
            if (Storage::disk('public')->exists($path)) {
                // asset() mengikuti host:port request aktif, bukan APP_URL
                return asset('storage/' . $path);
            }
        } catch (\Throwable) {
            // storage belum di-link
        }

        // 3. Placeholder jika file tidak ditemukan
        return _foto_placeholder();
    }
}

if (!function_exists('_foto_placeholder')) {
    function _foto_placeholder(): string
    {
        return 'data:image/svg+xml,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">'
          . '<rect width="400" height="300" fill="#1a1a24"/>'
          . '<text x="200" y="140" dominant-baseline="middle" text-anchor="middle" '
          .       'fill="rgba(255,255,255,.2)" font-size="52">🗺</text>'
          . '<text x="200" y="185" dominant-baseline="middle" text-anchor="middle" '
          .       'fill="rgba(255,255,255,.15)" font-size="12" font-family="sans-serif">'
          .       'Foto tidak tersedia</text>'
          . '</svg>'
        );
    }
}
