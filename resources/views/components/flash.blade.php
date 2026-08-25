@php
    $flash = session('success') ? ['type' => 'success', 'bg' => 'bg-green-600', 'title' => 'Berhasil', 'icon' => 'check-circle', 'text' => 'text-green-100', 'msg' => session('success')]
           : (session('error') ? ['type' => 'error', 'bg' => 'bg-red-600', 'title' => 'Gagal', 'icon' => 'circle-alert', 'text' => 'text-red-100', 'msg' => session('error')] : null);
@endphp

@if($flash)
<div id="flash-message" class="fixed top-20 right-6 z-[9999] {{ $flash['bg'] }} text-white px-6 py-4 rounded-xl shadow-2xl transition-all duration-500 flex items-center gap-3">
    <i data-lucide="{{ $flash['icon'] }}" class="w-6 h-6"></i>
    <div>
        <p class="font-semibold">{{ $flash['title'] }}</p>
        <p class="text-sm {{ $flash['text'] }}">{{ $flash['msg'] }}</p>
    </div>
</div>

<script>
    setTimeout(() => {
        const el = document.getElementById('flash-message');
        if (el) {
            el.classList.add('opacity-0', 'translate-x-10');
            setTimeout(() => el.remove(), 500);
        }
    }, 3000);
</script>
@endif