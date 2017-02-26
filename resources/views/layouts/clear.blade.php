<!DOCTYPE HTML>
<html ng-app="site">
<head>
    <title>Проект Лиса | @yield('title') </title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="stylesheet" href="/css/main.css"/>
    <link rel="apple-touch-icon" sizes="57x57" href="/ico/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/ico/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/ico/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/ico/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/ico/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/ico/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/ico/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/ico/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/ico/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/ico/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/ico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/ico/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/ico/favicon-16x16.png">
    <link rel="manifest" href="/ico/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#364050">
    <style>
        #titleBar {
            display: none;
        }
    </style>
</head>
<body>

@yield('content')

<!-- Scripts -->
<script src="/js/vendor.js"></script>
<script src="/js/application.js"></script>

</body>
</html>