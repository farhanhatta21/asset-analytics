<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        
        <title>@yield('title')</title>
        
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    
    <body>
        @yield('content')
    </body>

</html>