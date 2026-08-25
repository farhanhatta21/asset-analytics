<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>@yield('title','Asset Analytics')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    @stack('styles')

</head>

<body class="bg-slate-100">

    @yield('content')

    @stack('scripts')

    <script>
        lucide.createIcons();
    </script>

</body>

</html>