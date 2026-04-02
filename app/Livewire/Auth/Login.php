<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    #[Layout('layouts.blank')]
    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $this->remember)) {
            request()->session()->regenerate();
            Toaster::success('Willkommen zurück!');

            return redirect()->intended('/dashboard');
        }

        // $this->error('Diese Zugangsdaten stimmen nicht.', position: 'toast-bottom');
        Toaster::error(__('Die Zugangsdaten sind nicht korrekt'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
