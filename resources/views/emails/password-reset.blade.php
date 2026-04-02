<!DOCTYPE html>
<html>
<head>
    <style>
        .card { font-family: sans-serif; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 500px; }
        .button { 
            display: inline-block; 
            padding: 10px 20px; 
            background-color: #ADFF2F; 
            color: #000; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold;
            margin: 20px 0;
        }
        .footer { font-size: 12px; color: #64748b; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Hallo!</h2>
        <p>Du erhältst diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für dein Konto erhalten haben.</p>
        
        <a href="{{ $url }}" class="button">Passwort zurücksetzen</a>
        
        <p>Dieser Link zum Zurücksetzen des Passworts läuft in 10 Minuten ab.</p>
        <p>Falls du kein Zurücksetzen des Passworts angefordert hast, sind keine weiteren Maßnahmen erforderlich.</p>
        <p class="footer">Falls du Probleme mit dem Button oben hast, kopiere diesen Link in deinen Browser: <br> {{ $url }}</p>
    </div>
</body>
</html>
