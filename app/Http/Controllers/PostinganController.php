<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Postingan;
use App\Models\FotoPostingan;
use App\Models\RatingPostingan;
use App\Services\CloudinaryService;

class PostinganController extends Controller
{
    private CloudinaryService $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /* ── Helper: simpan satu foto (Cloudinary dulu, fallback local) ── */
    private function simpanFoto($file): string
    {
        // Coba upload ke Cloudinary jika sudah dikonfigurasi
        $cloudUrl = $this->cloudinary->upload($file, 'post_photos');
        if ($cloudUrl) {
            return $cloudUrl;   // URL penuh: https://res.cloudinary.com/...
        }

        // Fallback: simpan lokal
        return $file->store('post_photos', 'public');
    }

    /* ── Helper: hapus satu foto ── */
    private function hapusFoto(string $path): void
    {
        if (CloudinaryService::isCloudinaryUrl($path)) {
            $this->cloudinary->delete($path);
        } else {
            Storage::disk('public')->delete($path);
        }
    }

    /* ── Helper: resolve URL untuk ditampilkan di view ── */
    public static function fotoUrl(string $path): string
    {
        if (CloudinaryService::isCloudinaryUrl($path)) {
            return $path;   // sudah URL penuh
        }
        // Cek apakah file lokal ada
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        // Placeholder jika file tidak ditemukan
        return 'data:image/svg+xml,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">'
          . '<rect width="400" height="300" fill="#1e1e28"/>'
          . '<text x="50%" y="45%" text-anchor="middle" fill="rgba(255,255,255,.25)" font-size="40">🗺</text>'
          . '<text x="50%" y="62%" text-anchor="middle" fill="rgba(255,255,255,.18)" font-size="13" font-family="sans-serif">Foto tidak tersedia</text>'
          . '</svg>'
        );
    }

    /* ─────────────────────────────────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:200',
            'location' => 'nullable|string|max:200',
        ]);

        $destinations = [];
        if ($request->destinations) {
            $destinations = json_decode($request->destinations, true) ?? [];
        }

        $post = Postingan::create([
            'user_id'      => Auth::id(),
            'title'        => $request->title,
            'location'     => $request->location ?? $request->title,
            'story'        => $request->story,
            'destinations' => json_encode($destinations),
            'total_budget' => $request->total_budget ?? 0,
            'travel_date'  => $request->travel_date,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $foto) {
                FotoPostingan::create([
                    'travel_post_id' => $post->id,
                    'file_path'      => $this->simpanFoto($foto),
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Postingan berhasil dibuat! 🎉');
    }

    public function show($id)
    {
        $post     = Postingan::with(['user', 'photos', 'ratings'])->findOrFail($id);
        $myRating = RatingPostingan::where('user_id', Auth::id())
            ->where('travel_post_id', $id)->first();
        return view('post.show', compact('post', 'myRating'));
    }

    public function destroy($id)
    {
        $post = Postingan::findOrFail($id);
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        foreach ($post->photos as $foto) {
            $this->hapusFoto($foto->file_path);
        }
        $post->delete();
        return redirect()->route('dashboard')->with('success', 'Postingan dihapus.');
    }

    public function rate(Request $request, $id)
    {
        $request->validate(['score' => 'required|integer|min:1|max:5']);
        RatingPostingan::updateOrCreate(
            ['user_id' => Auth::id(), 'travel_post_id' => $id],
            ['score'   => $request->score]
        );
        return back()->with('success', 'Rating disimpan! ⭐');
    }

    public function edit($id)
    {
        $post = Postingan::findOrFail($id);
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }
        return view('post.edit', compact('post'));
    }

    public function update(Request $request, $id)
    {
        $post = Postingan::findOrFail($id);
        if ($post->user_id !== Auth::id()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'title'    => 'required|string|max:200',
            'location' => 'nullable|string|max:200',
        ]);

        $destinations = [];
        if ($request->destinations) {
            $destinations = array_values(array_filter($request->destinations, function ($d) {
                return !empty($d['name']);
            }));
        }
        if (is_string($request->destinations)) {
            $destinations = json_decode($request->destinations, true) ?? [];
        }

        $post->update([
            'title'        => $request->title,
            'location'     => $request->location ?? $request->title,
            'story'        => $request->story,
            'destinations' => json_encode($destinations),
            'total_budget' => $request->total_budget ?? 0,
            'travel_date'  => $request->travel_date,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $foto) {
                FotoPostingan::create([
                    'travel_post_id' => $post->id,
                    'file_path'      => $this->simpanFoto($foto),
                ]);
            }
        }

        if ($request->has('delete_photos')) {
            foreach ($request->delete_photos as $photoId) {
                $photo = FotoPostingan::where('travel_post_id', $post->id)
                    ->where('id', $photoId)->first();
                if ($photo) {
                    $this->hapusFoto($photo->file_path);
                    $photo->delete();
                }
            }
        }

        return redirect()->route('post.show', $post->id)->with('success', 'Postingan diupdate!');
    }

    /* ── Resolve URL foto (helper untuk view/JSON) ── */
    private function resolvePhotoUrl(?FotoPostingan $foto): ?string
    {
        if (!$foto) return null;
        return self::fotoUrl($foto->file_path);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) return response()->json([]);

        $posts = Postingan::with(['user', 'photos'])
            ->withAvg('ratings', 'score')
            ->where(function ($q) use ($query) {
                $q->where('location', 'like', "%{$query}%")
                  ->orWhere('destinations', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%");
            })
            ->orderByDesc('ratings_avg_score')
            ->latest()->limit(50)->get();

        if ($posts->isEmpty()) return response()->json([]);

        $grouped = $posts->groupBy('location')->map(function ($items, $location) {
            return [
                'location'    => $location,
                'total_posts' => $items->count(),
                'posts'       => $items->map(fn($p) => [
                    'id'          => $p->id,
                    'title'       => $p->title,
                    'travel_date' => $p->travel_date
                        ? \Carbon\Carbon::parse($p->travel_date)->format('d M Y') : null,
                    'author'      => $p->user->name ?? 'Unknown',
                    'photo'       => $this->resolvePhotoUrl($p->photos->first()),
                    'rating'      => $p->ratings_avg_score
                        ? round($p->ratings_avg_score, 1) : null,
                    'url'         => route('post.show', $p->id),
                ])->values(),
            ];
        })->values();

        return response()->json($grouped);
    }

    public function featured()
    {
        $posts = Postingan::with(['user', 'photos'])
            ->withAvg('ratings', 'score')
            ->orderByDesc('ratings_avg_score')
            ->limit(5)->get();

        return response()->json($posts->map(fn($p) => [
            'id'          => $p->id,
            'title'       => $p->title,
            'location'    => $p->location,
            'travel_date' => $p->travel_date
                ? \Carbon\Carbon::parse($p->travel_date)->format('d M Y') : null,
            'author'      => $p->user->name ?? 'Unknown',
            'photo'       => $this->resolvePhotoUrl($p->photos->first()),
            'rating'      => $p->ratings_avg_score
                ? round($p->ratings_avg_score, 1) : null,
            'url'         => route('post.show', $p->id),
        ]));
    }
}
