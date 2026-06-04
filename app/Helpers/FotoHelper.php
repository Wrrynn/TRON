<?php

use Illuminate\Support\Facades\Storage;
use App\Models\FotoPostingan;

if (!function_exists('foto_url')) {
    /**
     * Resolve URL foto untuk view.
     * Menerima objek FotoPostingan (disarankan) atau string path (kompatibilitas lama).
     */
    function foto_url($foto): string
    {
        // Objek FotoPostingan → pakai logika tunggal di model
        if ($foto instanceof FotoPostingan) {
            return $foto->publicUrl();
        }

        // String path (kompatibilitas lama)
        $path = (string) $foto;
        if ($path === '') return FotoPostingan::placeholderSvg();
        if (str_starts_with($path, 'https://')) return $path;

        try {
            if (Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        } catch (\Throwable) {
            // abaikan
        }
        return FotoPostingan::placeholderSvg();
    }
}
