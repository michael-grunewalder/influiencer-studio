<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ForgotPassword extends Component
{
    public string $email = '';

    #[Layout('layouts.blank')]
    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $status = Password::broker()->sendResetLink(
            ['email' => $this->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            Toaster::success(__('Der Link zum Zurücksetzen des Passworts wurde an deine E-Mail-Adresse gesendet.'));
            $this->reset('email');
        } else {
            Toaster::error(__($status));
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
