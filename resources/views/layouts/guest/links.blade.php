<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <meta name="author" content="Eden Spell Technologies">
    <link rel="author" href="https://edenspell.com">

    <x-meta-details :meta-detail="$metaDetail" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo.png') }}" />

    <title>{{ config('app.name') }} {{ $pageTitle != null ? '- ' . $pageTitle : '' }}</title>

    @include('layouts.guest.styles')
    @yield('styles')
</head>

<body class="bg-white">
    <div class="container-fluid position-relative bg-white d-flex p-0">
    <x-splash-screen />