<x-layouts.app>

    <x-slot:content>
        <div class="max-w-4xl mx-auto">
            {{-- Header Bereich --}}
            <div class="flex flex-col md:flex-row gap-8 items-start mb-10">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">Let's get started</h1>
                    <p class="text-gray-400">
                        Dieses Starter-Kit nutzt <span class="text-primary font-mono">MaryUI / DaisyUI</span>
                        auf Basis von Laravel 12 und Livewire 4.
                    </p>
                </div>

                @if (Route::has('login'))
                    <div class="flex gap-2">
                        <x-button label="Login" link="{{ route('login') }}" class="btn-ghost" />
                        <x-button label="Register" link="{{ route('register') }}" class="btn-primary" />
                    </div>
                @endif
            </div>

            {{-- Feature Liste (aus deinem Original-Code extrahiert) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-card title="Integrierte Addons" class="bg-base-200/50 border-white/5 shadow-xl">
                    <ul class="space-y-4">
                        <x-list-item :item="['name' => 'Laravel Permissions', 'sub' => 'Spatie V6']" no-separator>
                            <x-slot:actions>
                                <x-button icon="o-arrow-top-right-on-square" link="https://spatie.be/docs/laravel-permission" external class="btn-xs btn-ghost text-primary" />
                            </x-slot:actions>
                        </x-list-item>

                        <x-list-item :item="['name' => 'Laravel Passkeys', 'sub' => 'WebAuthn Support']" no-separator>
                            <x-slot:actions>
                                <x-button icon="o-arrow-top-right-on-square" link="https://spatie.be/docs/laravel-passkeys" external class="btn-xs btn-ghost text-primary" />
                            </x-slot:actions>
                        </x-list-item>

                        <x-list-item :item="['name' => 'Laravel Dashboard', 'sub' => 'Spatie V3']" no-separator>
                            <x-slot:actions>
                                <x-button icon="o-arrow-top-right-on-square" link="https://spatie.be/docs/laravel-dashboard" external class="btn-xs btn-ghost text-primary" />
                            </x-slot:actions>
                        </x-list-item>

                        <x-list-item :item="['name' => 'Livewire Toaster', 'sub' => 'Native Notifications']" no-separator>
                            <x-slot:actions>
                                <x-button icon="o-arrow-top-right-on-square" link="https://github.com/masmerise/livewire-toaster" external class="btn-xs btn-ghost text-primary" />
                            </x-slot:actions>
                        </x-list-item>
                    </ul>
                </x-card>

                {{-- Image Card --}}
                <div class="rounded-2xl overflow-hidden border border-white/5 shadow-2xl relative group">

                </div>
            </div>
        </div>
        <?php
            for ($za=100; $za<1000; $za+=100){
                echo '<div class="vw bg-base-' . $za . '" style=height:"50px">'. $za .'</div>';
            }
        ?>
    </x-slot:content>
</x-layouts.app>