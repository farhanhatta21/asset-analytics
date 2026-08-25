<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body class="bg-slate-100">

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Area Content -->
    <div class="ml-0 md:ml-64 flex min-h-screen flex-col">

        <!-- Topbar -->
        @include('components.topbar', [
            'title' => $title ?? 'Dashboard',
            'subtitle' => $subtitle ?? 'Asset Health Analytics System'
        ]) 

        <main class="flex-1 p-4 md:p-8">

            @include('components.flash')

            @yield('content')

        </main>

    </div>

<script>
lucide.createIcons();
</script>

</body>
</html>