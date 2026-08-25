@extends('layouts.admin')

@php
    $title = 'Tambah User';
    $subtitle = 'Membuat akun pengguna baru';
@endphp

@section('title','Tambah User')

@section('content')

<div class="max-w-3xl mx-auto">    
    <form action="{{ route('users.store') }}" method="POST" class="bg-white rounded-xl shadow p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Nama
                </label>
                
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                
                <p class="text-red-500 text-sm">
                    {{ $message }}
                </p>
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('email')
                
                <p class="text-red-500 text-sm">
                    {{ $message }}
                </p>
                
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                
                <input type="password" name="password" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('password')
                
                <p class="text-red-500 text-sm">
                    {{ $message }}
                </p>
                
                @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Role</label>
                
                <select name="role" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="admin">
                        Admin
                    </option>
                    
                    <option value="viewer">
                        Viewer
                    </option>
                
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                
                <select name="status" class="w-full border border-slate-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">
                        Aktif
                    </option>
                    
                    <option value="0">
                        Nonaktif
                    </option>
                </select>
            </div>
            
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('users.index') }}" class="px-5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 transition">
                    Kembali
                </a>

                <button class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">
                    Simpan User
                </button>
            </div>
        </div>
    </form>
</div>
@endsection