<div class="flex flex-col gap-6">
    <div class="flex justify-center">
        <img src="{{ asset('img/logo.jpeg') }}" alt="Brainet Logo" class="h-24 w-auto rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
    </div>
    <x-auth-header title="Iniciar sesión" description="Ingresa tu correo y contraseña para acceder" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            label="Correo electrónico"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="correo@ejemplo.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                label="Contraseña"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Contraseña"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    ¿Olvidaste tu contraseña?
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" label="Recordarme" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">Iniciar sesión</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>¿No tienes una cuenta?</span>
            <flux:link :href="route('register')" wire:navigate>Regístrate</flux:link>
        </div>
    @endif
</div>