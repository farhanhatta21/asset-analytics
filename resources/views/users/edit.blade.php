@extends('layouts.admin')

@php
    $title = 'Edit User';
    $subtitle = 'Mengubah informasi akun pengguna';
@endphp

@section('title','Edit User')

@section('content')

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl border shadow-sm p-6">
        <form
            id="editUserForm"
            action="{{ route('users.update',$user) }}"
            method="POST"
            onsubmit="event.preventDefault(); const btn = document.getElementById('updateUserBtn'); if(btn.dataset.submitted === 'true') { return false; } btn.dataset.submitted = 'true'; btn.disabled = true; btn.classList.add('opacity-75', 'cursor-not-allowed'); document.getElementById('updateUserText').classList.add('hidden'); document.getElementById('updateUserLoading').classList.remove('hidden'); setTimeout(() => { document.getElementById('editUserForm').submit(); }, 500);">

            @csrf
            @method('PUT')

            <div class="space-y-4">

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama
                    </label>

                    <input type="text" name="name" value="{{ old('name',$user->name) }}" required class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Email
                    </label>

                    <input type="email" name="email" value="{{ old('email',$user->email) }}" required class="w-full border rounded-xl px-4 py-3 mt-2">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Password Baru
                    </label>

                    <input type="password" name="password" class="w-full border rounded-xl px-4 py-3 mt-2">

                    <p class="mt-2 text-xs text-slate-500">
                        Kosongkan jika tidak ingin mengganti password.
                    </p>

                </div>

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Role
                    </label>

                    <select name="role" class="w-full border rounded-xl px-4 py-3 mt-2">

                        <option
                            value="admin"
                            {{ $user->role=="admin" ? "selected":"" }}>
                            Admin
                        </option>

                        <option
                            value="viewer"
                            {{ $user->role=="viewer" ? "selected":"" }}>
                            Viewer
                        </option>

                    </select>

                </div>

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Status
                    </label>

                    <select name="status" class="w-full border rounded-xl px-4 py-3 mt-2">
                        <option
                            value="1"
                            {{ $user->status ? "selected":"" }}>
                            Aktif
                        </option>

                        <option
                            value="0"
                            {{ !$user->status ? "selected":"" }}>
                            Nonaktif
                        </option>

                    </select>

                </div>

            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('users.index') }}"
                    class="px-5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                    Kembali
                </a>

                <button
                    id="updateUserBtn"
                    type="submit"
                    class="flex items-center justify-center min-w-[130px] px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white transition disabled:opacity-75 disabled:cursor-not-allowed">
                    <span id="updateUserText">Update User</span>
                    <span id="updateUserLoading" class="hidden inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>

            </div>

        </form>

    </div>

</div>

@endsection