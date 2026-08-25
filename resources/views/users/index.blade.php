@extends('layouts.admin')

@section('title','User Management')

@section('content')

@php
    $title='User Management';
    $subtitle='Kelola akun pengguna sistem';
@endphp

<div class="space-y-5">
    <!-- Filter Card -->
    <div class="bg-white border rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">
                Filter User
            </h3>
            <a href="{{ route('users.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah User
            </a>
        </div>

        <form id="filterForm" method="GET" class="grid md:grid-cols-[2fr_1fr_auto] gap-3 items-center">
            <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="🔍 Cari nama atau email..." class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">

            <select name="role" onchange="document.getElementById('filterForm').submit()" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role')=='admin' ? 'selected':'' }}>Admin</option>
                <option value="viewer" {{ request('role')=='viewer' ? 'selected':'' }}>Viewer</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition">
                    Cari
                </button>
                @if(request('search') || request('role') || request('status'))
                <a href="{{ route('users.index') }}" class="h-10 px-3 flex items-center justify-center border border-slate-300 hover:bg-slate-50 text-slate-600 rounded-lg text-sm transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Statistik -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">Total User</p>
                <h2 class="text-2xl font-bold">{{ $statistics['total'] }}</h2>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6 text-indigo-600"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">Admin</p>
                <h2 class="text-2xl font-bold text-indigo-600">{{ $statistics['admin'] }}</h2>
            </div>
        </div>

        <div class="bg-white rounded-xl border shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                <i data-lucide="eye" class="w-6 h-6 text-emerald-600"></i>
            </div>
            <div>
                <p class="text-sm text-slate-500">Viewer</p>
                <h2 class="text-2xl font-bold text-emerald-600">{{ $statistics['viewer'] }}</h2>
            </div>
        </div>
    </div>
        
    <!-- Daftar User -->
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">Daftar User</h3>
                <p class="text-xs text-slate-500">Seluruh akun yang terdaftar pada sistem</p>
            </div>
            <span class="text-xs text-slate-500">Menampilkan {{ $users->count() }} dari {{ $users->total() }} user</span>
        </div>

        <div class="overflow-x-auto relative">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 text-slate-600 text-xs">
                    <tr>
                        <th class="text-left px-6 py-3">User</th>
                        <th class="text-center px-4 py-3">Role</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->role == 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $user->status ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>

                        <td class="px-4 text-center">
                            <div class="flex justify-center items-center gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="flex items-center gap-1 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    Edit
                                </a>

                                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="if(!confirm('Hapus user ini?')) return false; const btn = this.querySelector('button'); if(btn.dataset.submitted === 'true') return false; btn.dataset.submitted = 'true'; btn.disabled = true; btn.classList.add('opacity-75', 'cursor-not-allowed'); return true;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center gap-1 bg-red-50 hover:bg-red-100 text-red-700 px-3 py-1.5 rounded-lg text-xs font-medium transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada user ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    const userSearch = document.getElementById('searchInput');
    let userDebounce = null;
    if (userSearch) {
        userSearch.addEventListener('input', () => {
            clearTimeout(userDebounce);
            userDebounce = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 400);
        });
    }
</script>

@endsection