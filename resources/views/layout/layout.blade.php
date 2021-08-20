<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield("title")</title>

    <script src="{{ asset('js/test.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/clients/all-clients-style.css') }}" rel="stylesheet">
</head>
<body>

    <!--  clients section  -->
    <div id="clients">

        @yield('clients')
        @yield('single-client')

    </div>

    <!-- end clients section -->


    
    



</body>
</html>