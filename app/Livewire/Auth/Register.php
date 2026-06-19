<?php

namespace App\Livewire\Auth;

use App\Mail\OtpCodeMail;
use App\Models\TeamInvitation;
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

    public string $invitation_token = '';

    public bool $email_disabled = false;

    // Felder für Schritt 2 (OTP)
    public string $otp = '';

    public int $step = 1; // 1 = Daten, 2 = OTP-Eingabe

    public function mount(): void
    {
        $token = request()->query('invitation');
        if ($token) {
            $invitation = TeamInvitation::where('token', $token)->first();
            if ($invitation) {
                $this->invitation_token = $token;
                $this->email = $invitation->email;
                $this->email_disabled = true;
                if ($invitation->name) {
                    $this->name = $invitation->name;
                }
            }
        }
    }

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

        // bearny get his axe out of th ebackpack and chops the name mercilessly into small pieces
        $cNames = collect(explode(' ', $this->name));
        $firstName = $cNames->shift();
        $lastName = $cNames->pop();
        $middleName = $cNames->implode(' ');

        // User final anlegen
        $user = User::create([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(), // Direkt als verifiziert markieren
        ]);

        auth()->login($user);

        DB::table('otp_codes')->where('email', $this->email)->delete();

        // Check for invitation
        $invitation = null;
        if (! empty($this->invitation_token)) {
            $invitation = TeamInvitation::where('token', $this->invitation_token)->first();
        }
        if (! $invitation) {
            $invitation = TeamInvitation::where('email', $user->email)->first();
        }

        if ($invitation) {
            $team = $invitation->team;
            if (! $team->users()->where('user_id', $user->id)->exists()) {
                $team->users()->attach($user->id, ['role' => 'view']);
            }
            if (! $user->hasPermissionTo('team.view')) {
                $user->givePermissionTo('team.view');
            }
            $invitation->delete();
            Toaster::success(__('Konto erstellt und dem Team beigetreten!'));
        }
        session()->forget('pending_invitation_url');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
