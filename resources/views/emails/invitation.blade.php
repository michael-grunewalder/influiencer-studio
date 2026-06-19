<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .card { font-family: sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 400px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; text-align: center; }
        .footer { font-size: 12px; color: #64748b; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Hallo{{ $invitation->name ? ' ' . $invitation->name : '' }}!</h2>
        <p>Du wurdest eingeladen, dem Team <strong>{{ $invitation->team->name }}</strong> beizutreten.</p>
        
        <p>Klicke auf den folgenden Button, um die Einladung anzunehmen:</p>
        
        <a href="{{ $signedUrl }}" class="btn" style="color: #ffffff !important;">Einladung annehmen</a>
        
        <p>Dieser Link ist signiert und führt direkt zur Beitrittsseite.</p>
        <p class="footer">Falls du diese Einladung nicht erwartet hast, kannst du diese E-Mail einfach ignorieren.</p>
    </div>
</body>
</html>
