<!DOCTYPE html>
<html>
<head>
    <style>
        .card { font-family: sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 400px; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4f46e5; margin: 20px 0; text-align: center; }
        .footer { font-size: 12px; color: #64748b; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Hallo!</h2>
        <p>Vielen Dank für deine Registrierung. Bitte nutze den folgenden Code, um deine E-Mail-Adresse zu bestätigen:</p>
        
        <div class="code">{{ $otpCode }}</div>
        
        <p>Dieser Code ist 15 Minuten lang gültig.</p>
        <p class="footer">Falls du dich nicht registriert hast, kannst du diese E-Mail einfach ignorieren.</p>
    </div>
</body>
</html>