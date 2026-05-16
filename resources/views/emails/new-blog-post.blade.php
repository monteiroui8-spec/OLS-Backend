<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Novo artigo no Blog: {{ $post->title }}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px;">
        <h2 style="color: #2c3e50;">Olá!</h2>
        <p>Temos um novo artigo no nosso blog que pode lhe interessar:</p>
        
        <h3 style="color: #2980b9;">{{ $post->title }}</h3>
        
        @if($post->image_url)
            <img src="{{ url($post->image_url) }}" alt="Blog Image" style="max-width: 100%; border-radius: 8px;">
        @endif

        <p>{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 150) }}</p>
        
        <p>
            <a href="{{ $postUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #2980b9; color: #fff; text-decoration: none; border-radius: 4px;">Ler artigo completo</a>
        </p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
        <p style="font-size: 12px; color: #999;">
            Recebeu este e-mail porque está subscrito na nossa newsletter.<br>
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">Cancelar subscrição</a>
        </p>
    </div>
</body>
</html>