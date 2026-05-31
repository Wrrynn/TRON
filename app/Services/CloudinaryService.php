<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Upload foto ke Cloudinary via REST API tanpa package tambahan.
 * Foto tersimpan di cloud sehingga bisa diakses dari device manapun.
 */
class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private bool   $configured;

    public function __construct()
    {
        $this->cloudName  = config('cloudinary.cloud_name', '');
        $this->apiKey     = config('cloudinary.api_key', '');
        $this->apiSecret  = config('cloudinary.api_secret', '');
        $this->configured = !empty($this->cloudName)
                         && !empty($this->apiKey)
                         && !empty($this->apiSecret);
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Upload file ke Cloudinary, return URL publik.
     * Return null jika gagal (fallback ke local storage).
     */
    public function upload($file, string $folder = 'post_photos'): ?string
    {
        if (!$this->configured) {
            return null;
        }

        try {
            $timestamp = time();
            $params    = "folder={$folder}&timestamp={$timestamp}";
            $signature = sha1($params . $this->apiSecret);

            $response = Http::timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post("https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload", [
                    'api_key'   => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                    'folder'    => $folder,
                ]);

            if ($response->successful()) {
                return $response->json('secure_url');   // https://res.cloudinary.com/...
            }

            Log::warning('Cloudinary upload failed: ' . $response->body());
            return null;

        } catch (\Throwable $e) {
            Log::warning('Cloudinary upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Hapus gambar dari Cloudinary berdasarkan URL.
     */
    public function delete(string $url): void
    {
        if (!$this->configured) return;

        try {
            // Ekstrak public_id dari URL
            // e.g: https://res.cloudinary.com/demo/image/upload/v123/post_photos/abc.jpg
            // → public_id = post_photos/abc
            preg_match('/upload\/(?:v\d+\/)?(.+)\.\w+$/', $url, $m);
            if (empty($m[1])) return;

            $publicId  = $m[1];
            $timestamp = time();
            $signature = sha1("public_id={$publicId}&timestamp={$timestamp}" . $this->apiSecret);

            Http::timeout(15)->post(
                "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy",
                [
                    'public_id' => $publicId,
                    'api_key'   => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Cloudinary delete error: ' . $e->getMessage());
        }
    }

    /**
     * Cek apakah path adalah URL Cloudinary.
     */
    public static function isCloudinaryUrl(string $path): bool
    {
        return str_starts_with($path, 'https://res.cloudinary.com');
    }
}
