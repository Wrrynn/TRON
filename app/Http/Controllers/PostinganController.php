<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Postingan;
use App\Models\FotoPostingan;
use App\Models\RatingPostingan;

class PostinganController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:200',
            'location' => 'required|string|max:200',
        ]);

        $destinations = [];
        if ($request->destinations) {
            $destinations = json_decode($request->destinations, true) ?? [];
        }

        $post = Postingan::create([
            'user_id'      => Auth::id(),
            'title'        => $request->title,
            'location'     => $request->location,
            'story'        => $request->story,
            'destinations' => json_encode($destinations),
            'total_budget' => $request->total_budget ?? 0,
            'travel_date'  => $request->travel_date,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $foto) {
                $path = $foto->store('post_photos', 'public');
                FotoPostingan::create([
                    'travel_post_id' => $post->id,
                    'file_path'      => $path,
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
            Storage::disk('public')->delete($foto->file_path);
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
        'location' => 'required|string|max:200',
    ]);
    $post->update([
        'title'        => $request->title,
        'location'     => $request->location,
        'story'        => $request->story,
        'total_budget' => $request->total_budget ?? 0,
        'travel_date'  => $request->travel_date,
    ]);
    return redirect()->route('post.show', $post->id)->with('success', 'Postingan diupdate!');
}
}