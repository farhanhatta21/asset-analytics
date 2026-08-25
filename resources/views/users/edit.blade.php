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
            action="{{ route('users.update',$user) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="space-y-4">

                <div>

                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Nama
                    </label>

                    <input type="text" name="name" value="{{ old('name',$user->name) }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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

                    <input type="email" name="email" value="{{ old('email',$user->email) }}" class="w-full border rounded-xl px-4 py-3 mt-2">
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
                    class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">
                    Update User
                </button>

            </div>

        </form>

    </div>

</div>

@endsection