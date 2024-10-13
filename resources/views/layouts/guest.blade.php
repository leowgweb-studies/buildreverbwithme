<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div style="background-image: url('{{ asset('images/bg_notebook_leaf.png') }}')" class="min-h-screen grid grid-cols-1 content-between p-6 bg-transparent bg-center bg-cover bg-no-repeat">
            <header class="flex justify-center items-center">
                <a href="/" wire:navigate>
                    <x-application-logo class="w-16 h-16 fill-current text-gray-500 -skew-x-12 opacity-40" />
                </a>
            </header>

            <main class="w-full flex flex-col justify-center items-center overflow-hidden">
                {{ $slot }}
            </main>

            <footer class="text-center">
                <span class="text-gray-400 caveat-font">
                    Created with <span class="text-red-300"><3</span> by <a href="https://lauroguedes.dev" target="_blank" class="underline underline-offset-1 hover:opacity-80">Lauro Guedes</a> |
                    <a href="https://github.com/lauroguedes/tic-tac-toe-game" target="_blank" class="underline underline-offset-1 hover:opacity-80">Github Project</a>
                </span>
            </footer>
        </div>
    </body>
</html>
