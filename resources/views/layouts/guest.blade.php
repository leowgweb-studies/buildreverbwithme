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
        <div style="background-image: url('{{ asset('images/bg_small_notebook.png') }}')" class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-transparent bg-center bg-cover bg-no-repeat">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-16 h-16 fill-current text-gray-500 -skew-x-12 opacity-40" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-6 bg-transparent overflow-hidden">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
