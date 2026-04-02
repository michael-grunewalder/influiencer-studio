<?php

namespace App\Livewire\Auth;

use App\Mail\OtpCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.blank')]
class Register extends Component
{
    // Felder für Schritt 1 (Datenaufnahme)
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Felder für Schritt 2 (OTP)
    public string $otp = '';

    public int $step = 1; // 1 = Daten, 2 = OTP-Eingabe

    public function sendOtp()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $code = rand(100000, 999999);

        // Code zwischenspeichern
        DB::table('otp_codes')->updateOrInsert(
            ['email' => $this->email],
            ['code' => $code, 'expires_at' => now()->addMinutes(15)]
        );

        // E-Mail senden (Logik folgt im nächsten Schritt)
        Mail::to($this->email)->send(new OtpCodeMail($code));

        $this->step = 2;

        Toaster::success('Code an '.$this->email.' gesendet!');
    }

    public function verifyAndRegister()
    {
        $this->validate(['otp' => 'required|size:6']);

        $validCode = DB::table('otp_codes')
            ->where('email', $this->email)
            ->where('code', $this->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (! $validCode) {
            Toaster::error('Ungültiger oder abgelaufener Code.');

            return;
        }

        // User final anlegen
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(), // Direkt als verifiziert markieren
        ]);

        auth()->login($user);

        DB::table('otp_codes')->where('email', $this->email)->delete();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
