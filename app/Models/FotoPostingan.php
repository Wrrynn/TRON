<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FotoPostingan extends Model
{
    protected $table = 'post_photos';
    protected $fillable = ['travel_post_id', 'file_path'];

    /**
     * URL publik untuk menampilkan foto — satu sumber kebenaran.
     * Urutan: Cloudinary URL → blob DB (/photo/{id}) → file lokal → placeholder.
     */
    public function publicUrl(): string
    {
        $path = $this->file_path;

        // 1. Cloudinary / URL absolut
        if (is_string($path) && str_starts_with($path, 'https://')) {
            return $path;
        }

        // 2. Disimpan di database (persisten di serverless)
        if ($path === 'db') {
            return url('/photo/' . $this->id);
        }

        // 3. File lokal (development)
        try {
            if ($path && Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        } catch (\Throwable) {
            // abaikan
        }

        // 4. Placeholder
        return self::placeholderSvg();
    }

    public static function placeholderSvg(): string
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
