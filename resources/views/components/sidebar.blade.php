<!-- Backdrop Mobile -->
<div id="sidebar-backdrop" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); this.classList.add('hidden');" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs hidden md:hidden"></div>

<aside id="sidebar" aria-label="Navigasi Utama" class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0d1527] text-slate-200 border-r border-slate-800/80 flex flex-col shadow-2xl shadow-slate-950/80 -translate-x-full md:translate-x-0 transition-transform duration-200">
    <!-- LOGO (Deep Navy Clean Header) -->
    <div class="relative pt-6 pb-5 px-5 border-b border-slate-800/60 bg-[#090e1a]/60">
        <a href="/" class="flex flex-col items-center text-center group">
            <!-- Dual Standalone Logos (Clean without Capsule) -->
            <div class="flex items-center justify-center gap-4 mb-3">
                <img src="{{ asset('images/logo-kampus.png') }}" alt="Logo Kampus PNUP" class="w-10 h-10 object-contain drop-shadow-md hover:scale-105 transition-transform">
                <div class="w-px h-7 bg-slate-700/50"></div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo Asset Analytics" class="w-10 h-10 object-contain drop-shadow-md hover:scale-105 transition-transform">
            </div>
            <!-- Title -->
            <div class="text-[15px] font-bold text-white tracking-tight group-hover:text-blue-400 transition-colors">
                Asset Health Monitoring
            </div>
            <p class="text-[11px] text-blue-300/70 font-medium tracking-wide mt-0.5">
                TPK Makassar Terminal 2
            </p>
        </a>
        <button type="button" onclick="document.getElementById('sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar-backdrop').classList.add('hidden');" class="absolute top-3 right-3 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 md:hidden" aria-label="Close sidebar">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- MENU -->
    <nav aria-label="Menu Utama" class="flex-1 px-3 py-4 space-y-1.5">
        <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition {{ request()->is('/') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-sm">
                Dashboard
            </span>
        </a>

        <a href="{{ route('data-alat') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition {{ request()->routeIs('data-alat') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <i data-lucide="table-properties" class="w-5 h-5"></i>
            <span class="text-sm">
                Data Alat
            </span>
        </a>

        <!-- button upload -->
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('upload') }}"
        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition
                {{ request()->routeIs('upload') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <i data-lucide="upload" class="w-5 h-5"></i>
            <span class="text-sm">Upload Data</span>
        </a>
        @endif

        <a href="/laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition {{ request()->is('laporan*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <i data-lucide="file-text" class="w-5 h-5"></i>
            <span class="text-sm">
                Laporan
            </span>
        </a>

        <!-- button user management -->
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('users.index') }}"
        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition
                {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="text-sm">User Management</span>
        </a>
        @endif
    </nav>

    <!-- FOOTER -->
    <div class="border-t border-slate-800/60 p-3 bg-[#090e1a]/40">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-red-600/90 hover:text-white font-medium transition duration-150">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span class="text-sm">
                    Logout
                </span>
            </button>
        </form>
    </div>
</aside>