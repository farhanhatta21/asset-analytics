@extends('layouts.guest')

@section('title','Login')

@section('content')

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-blue-50/40 p-4 sm:p-6">

    <div class="w-full max-w-md bg-white border border-slate-200/80 rounded-3xl shadow-xl shadow-slate-200/60 p-8 sm:p-10 transition-all duration-300">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center gap-3.5 p-3 rounded-2xl bg-blue-50/80 border border-blue-100/80 shadow-xs mb-3">
                <img src="{{ asset('images/logo-kampus.png') }}" alt="Logo Kampus" class="w-11 h-11 object-contain">
                <div class="w-px h-7 bg-slate-300"></div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo Asset Analytics" class="w-11 h-11 object-contain">
            </div>

            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-800 tracking-tight">
                Asset Health Monitoring
            </h1>

            <p class="text-slate-500 text-sm mt-1">
                Silahkan login untuk melanjutkan
            </p>
        </div>

        <!-- Error Notification -->
        @if(session('error') || $errors->any())
        <div class="mb-6 rounded-2xl bg-red-50/90 border border-red-200/90 p-4 text-red-700 text-sm flex items-start gap-3 shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-red-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-red-800">Login Gagal</p>
                <p class="text-red-600 text-xs mt-0.5">{{ session('error') ?? $errors->first() }}</p>
            </div>
        </div>
        @endif

        @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 p-4 text-emerald-700 text-sm flex items-start gap-3 shadow-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-emerald-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
                <p class="text-emerald-700 text-xs">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        <form
            id="loginForm"
            action="{{ route('login.process') }}"
            method="POST"
            onsubmit="const btn = document.getElementById('loginBtn'); if(btn.dataset.submitted === 'true') { return false; } btn.dataset.submitted = 'true'; btn.disabled = true; btn.classList.add('opacity-75', 'cursor-not-allowed'); document.getElementById('btnText').classList.add('hidden'); document.getElementById('btnLoading').classList.remove('hidden'); return true;"
            class="space-y-5">

            @csrf

            <!-- Email Field -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Email
                </label>

                <div class="relative">
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="nama@email.com"
                        class="w-full bg-slate-50/50 border @error('email') border-red-400 focus:border-red-500 focus:ring-red-500/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-500/20 @enderror text-slate-800 placeholder-slate-400 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:bg-white transition duration-150"
                        required>
                </div>
                @error('email')
                    <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Password
                </label>

                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        class="w-full bg-slate-50/50 border @error('password') border-red-400 focus:border-red-500 focus:ring-red-500/20 @else border-slate-300 focus:border-blue-600 focus:ring-blue-500/20 @enderror text-slate-800 placeholder-slate-400 rounded-xl px-4 py-3 pr-11 text-sm focus:outline-none focus:ring-2 focus:bg-white transition duration-150"
                        required>

                    <button
                        type="button"
                        onclick="const p = document.getElementById('password'); const isPass = p.type === 'password'; p.type = isPass ? 'text' : 'password'; document.getElementById('eye-open').classList.toggle('hidden'); document.getElementById('eye-closed').classList.toggle('hidden');"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition"
                        aria-label="Toggle password visibility">
                        <!-- Eye Open Icon -->
                        <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Closed Icon -->
                        <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                id="loginBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.99] disabled:opacity-75 disabled:cursor-not-allowed text-white font-semibold rounded-xl py-3 text-sm shadow-md shadow-blue-600/25 hover:shadow-lg hover:shadow-blue-600/35 transition duration-150 flex items-center justify-center">
                <span id="btnText">Login</span>
                <span id="btnLoading" class="hidden inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses...</span>
                </span>
            </button>

        </form>

    </div>

</div>

@endsection