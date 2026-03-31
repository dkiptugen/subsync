<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <title>@yield('title')</title>

    <link href="{{ asset('assets/css/app.css?v='.(file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time())) }}" rel="stylesheet">

</head>

<body>
<main class="main h-100 w-100">
    <div class="container h-100">


            @yield('content')

    </div>
</main>
<script src="{{ asset('assets/js/app.js?v='.(file_exists(public_path('assets/js/app.js')) ? filemtime(public_path('assets/js/app.js')) : time())) }}" type="module"></script>
</body>
</html>
