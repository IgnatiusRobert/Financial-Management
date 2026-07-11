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
    <body class="font-sans text-slate-900 antialiased bg-slate-50">
        @php
            $isLogin = request()->routeIs('login');
        @endphp

        <div class="min-h-screen">
            <div class="mx-auto grid min-h-screen max-w-7xl grid-cols-1 lg:grid-cols-[1.3fr_1fr]">
                <div class="hidden lg:flex flex-col justify-center gap-10 px-8 py-16 bg-gradient-to-br from-teal-700 via-teal-600 to-cyan-700 text-white">
                    <div class="flex items-center gap-3">
                        <div class="rounded-2xl bg-white/10 p-3 shadow-lg shadow-black/10">
                            <x-application-logo class="w-12 h-12 text-white" />
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-[0.25em] font-semibold text-teal-100/80">Smart Expense</p>
                        </div>
                    </div>

                    <div class="max-w-lg space-y-6">
                        <h1 class="text-4xl font-semibold tracking-tight">Institutional-grade security for your personal wealth.</h1>
                        <p class="text-sm leading-7 text-teal-100/90">Join thousands of professional traders and high-net-worth individuals who trust Smart Expense Manager for stable, precise financial tracking.</p>
                    </div>

                    <div class="space-y-4 text-sm text-teal-100/90">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-teal-50">
                                ✓
                            </span>
                            <span>Bank-level 256-bit encryption</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-teal-50">
                                ✓
                            </span>
                            <span>Multi-factor authentication required</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-teal-50">
                                ✓
                            </span>
                            <span>SOC 2 Type II Certified Infrastructure</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center px-6 py-12">
                    <div class="w-full max-w-xl rounded-[32px] border border-slate-200/70 bg-white/95 p-8 shadow-2xl shadow-slate-900/10 backdrop-blur-xl">
                        <div class="mb-8 flex flex-col gap-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium uppercase tracking-[0.22em] text-slate-500">{{ $isLogin ? 'Sign In' : 'Create Account' }}</p>
                                    <h2 class="mt-3 text-3xl font-semibold text-slate-900">{{ $isLogin ? 'Welcome back' : 'Create your account' }}</h2>
                                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ $isLogin ? 'Enter your credentials to access your portfolio.' : 'Start building your portfolio with a secure account.' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 overflow-hidden rounded-full bg-slate-100 p-1 text-sm font-semibold text-slate-500 shadow-sm">
                                <a href="{{ route('login') }}" wire:navigate class="inline-flex flex-1 items-center justify-center rounded-full px-4 py-3 transition-colors duration-200 {{ $isLogin ? 'bg-white text-slate-900 shadow-sm' : 'hover:bg-slate-200' }}">Sign In</a>
                                <a href="{{ route('register') }}" wire:navigate class="inline-flex flex-1 items-center justify-center rounded-full px-4 py-3 transition-colors duration-200 {{ ! $isLogin ? 'bg-white text-slate-900 shadow-sm' : 'hover:bg-slate-200' }}">Create Account</a>
                            </div>
                        </div>

                        <div class="space-y-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
