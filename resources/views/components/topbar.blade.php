<header class="sticky top-0 z-30 h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8">
    <div class="flex items-center gap-3">
        <!-- Hamburger Button Mobile -->
        <button type="button" onclick="document.getElementById('sidebar').classList.remove('-translate-x-full'); document.getElementById('sidebar-backdrop').classList.remove('hidden');" class="p-2 -ml-2 rounded-xl text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Open sidebar">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>

        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-lg sm:text-xl font-bold text-slate-800">
                    {{ $title ?? 'Dashboard' }}
                </h1>
                
                @isset($asset)
                    <span class="text-lg sm:text-xl font-bold text-slate-800">
                        •
                        {{ $asset }}
                    </span>
                @endisset
            </div>

            <p class="text-xs text-slate-500 hidden sm:block">
                {{ $subtitle ?? 'Asset Health Analytics System' }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        {{ $actions ?? '' }}
        <div class="text-right">
            <p class="font-semibold text-sm">
                {{ auth()->user()->name }}
            </p>

            <p class="text-xs text-slate-700">
                {{ ucfirst(auth()->user()->role) }}
                <!-- • ⋮ ✮--> ✦
                {{ now()->format('d M Y') }}
            </p>
        </div>

        <div
        class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold">
            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>

        

    </div>

</header>