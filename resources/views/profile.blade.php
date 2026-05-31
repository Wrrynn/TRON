<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil {{ $user->name }} — Tripmo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin:0;
            font-family: sans-serif;
            background: #0f0f1a;
            color: white;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 30px 20px;
        }

        .profile-top {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #7c5cfc;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size: 24px;
            font-weight: bold;
        }

        .name {
            font-size: 20px;
            font-weight: 700;
        }

        .email {
            font-size: 13px;
            color: rgba(255,255,255,.5);
        }

        .stats {
            margin-top: 8px;
            font-size: 13px;
            color: rgba(255,255,255,.7);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            margin-top: 20px;
        }

        .card {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 8px;
            background: rgba(255,255,255,.05);
        }

        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.6);
            opacity: 0;
            transition: .2s;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            text-align:center;
            padding:10px;
        }

        .card:hover .overlay {
            opacity: 1;
        }

        .empty {
            text-align: center;
            margin-top: 60px;
            color: rgba(255,255,255,.3);
        }

        .back {
            display:inline-block;
            margin-top: 20px;
            color:#7c5cfc;
            text-decoration:none;
            font-size:13px;
        }
    </style>
</head>
<body>

<div class="container">

    {{-- PROFILE --}}
    <div class="profile-top">
        <div class="avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div>
            <div class="name">{{ $user->name }}</div>
            <div class="email">{{ $user->email }}</div>

            <div class="stats">
                {{ $user->postingan ? $user->postingan->count() : 0 }} jejak
            </div>
        </div>
    </div>

    {{-- POSTINGAN --}}
    @if($user->postingan && $user->postingan->count() > 0)
        <div class="grid">
            @foreach($user->postingan as $p)
                <a href="{{ route('post.show', $p->id) }}" class="card">
                    
                    @if($p->photos->first())
                        <img src="{{ foto_url($p->photos->first()->file_path) }}">
                    @endif

                    <div class="overlay">
                        <div style="font-size:12px;font-weight:600">
                            {{ $p->title }}
                        </div>
                        <div style="font-size:10px;color:rgba(255,255,255,.6)">
                            {{ $p->location }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="empty">
            Belum ada postingan.
        </div>
    @endif

    <a href="{{ route('dashboard') }}" class="back">← Kembali ke Dashboard</a>

</div>

</body>
</html>