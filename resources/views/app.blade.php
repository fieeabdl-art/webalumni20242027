<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Arsip digital kenangan satu angkatan.">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Arsip Angkatan">
        <meta property="og:description" content="Arsip digital kenangan satu angkatan.">
        <title inertia>{{ config('app.name', 'Arsip Angkatan') }}</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>