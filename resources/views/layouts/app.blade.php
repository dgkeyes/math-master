<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Multiplication Game') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:brand href="/" name="Math Master" class="max-lg:hidden dark:hidden" />
        <flux:brand href="/" name="Math Master" class="max-lg:!hidden hidden dark:flex" />

        <flux:spacer />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="play-circle" href="{{ route('game') }}">Play Game</flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="mr-1 space-x-0 rtl:space-x-reverse sm:mr-2">
            <flux:tooltip content="Toggle dark mode" position="bottom">
                <flux:navbar.item
                    class="!h-10 [&>div>svg]:size-5"
                    icon="moon"
                    x-on:click="$flux.dark = !$flux.dark"
                />
            </flux:tooltip>
        </flux:navbar>
    </flux:header>

    <flux:main container>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>
</html>
