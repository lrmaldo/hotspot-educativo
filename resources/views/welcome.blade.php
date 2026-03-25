<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Brainet - Hotspot Educativo</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-zinc-200 antialiased font-sans min-h-screen flex flex-col">
    <!-- Header -->
    <header class="w-full bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-zinc-200 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Brainet Logo" class="h-10 w-auto rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                <span class="text-xl font-bold tracking-tight text-blue-600 dark:text-blue-400">Brainet</span>
            </div>
            <nav class="flex gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white transition-colors duration-200">Panel de Control</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white transition-colors duration-200">Iniciar sesión</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-6 lg:p-8">
        <div class="max-w-4xl mx-auto w-full text-center space-y-10">
            <!-- Logo grande -->
            <div class="flex justify-center mb-8">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Plataforma Brainet" class="h-32 w-auto md:h-40 rounded-2xl shadow-xl ring-1 ring-zinc-200 dark:ring-zinc-800 transform hover:scale-105 transition-transform duration-300">
            </div>

            <!-- Título -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                El conocimiento es tu <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-500">conexión</span>
            </h1>

            <!-- Descripción -->
            <p class="text-lg md:text-xl text-zinc-600 dark:text-zinc-400 leading-relaxed max-w-2xl mx-auto">
                <strong>Brainet</strong> es una plataforma de hotspot enfocada en la educación. Responde correctamente a nuestra trivia y obtén <span class="font-bold text-green-600 dark:text-green-400">30 minutos</span> de internet gratuito. ¿Te equivocas? Aún así obtienes <span class="font-bold text-orange-500 dark:text-orange-400">5 minutos</span> para seguir navegando y aprendiendo.
            </p>

            <!-- Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 text-left">
                <!-- Card 1 -->
                <div class="bg-white dark:bg-zinc-800/50 p-6 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-zinc-900 dark:text-white">Aprende</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">Contesta preguntas de cultura general, ciencias, historia y más para extender tu tiempo de navegación de manera interactiva.</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white dark:bg-zinc-800/50 p-6 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-zinc-900 dark:text-white">Recompensa</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">Cada respuesta correcta te premia con 30 minutos de acceso. Y si fallas te otorgamos 5 minutos para que no te quedes desconectado.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white dark:bg-zinc-800/50 p-6 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-zinc-900 dark:text-white">Integración MikroTik</h3>
                    <p class="text-zinc-600 dark:text-zinc-400 text-sm">Gestión robusta y automatizada de usuarios temporales utilizando la avanzada API de MikroTik de forma transparente.</p>
                </div>
            </div>

            <!-- Call to action -->
            <div class="pt-10 pb-4">
                <a href="{{ route('hotspot.trivia') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold rounded-full text-white bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-blue-500/30 transition-all duration-300 transform hover:-translate-y-1">
                    Prueba la Trivia ahora
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full mt-auto py-6 border-t border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/50">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
            &copy; {{ date('Y') }} Brainet. Todos los derechos reservados.
        </div>
    </footer>
</body>
</html>