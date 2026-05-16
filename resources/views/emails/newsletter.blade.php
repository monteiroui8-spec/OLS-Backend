<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; color: #333; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px;">
        
        {!! $content !!}

        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
        <p style="font-size: 12px; color: #999; text-align: center;">
            Recebeu este e-mail porque está subscrito na nossa newsletter.<br>
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">Cancelar subscrição</a>
        </p>
    </div>
</body>
</html>