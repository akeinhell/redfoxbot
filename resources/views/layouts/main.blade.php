<!DOCTYPE HTML>
<html ng-app="site">
<head>
    <title>Проект Лиса | @yield('title') </title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    {!! webpack_asset('style', 'styles.css') !!}
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
    {!! webpack_asset('js', 'runtime.js') !!}
    {!! webpack_asset('js', 'vendor.js') !!}
    @yield('header_block')

</head>
<body>

<!-- Content -->
<div id="content">
    <div class="inner">
        @yield('content')
        {{--@include('layouts.pagination')--}}

    </div>
</div>

<!-- Sidebar -->
<div id="sidebar">

    <!-- Logo -->
    <h1 id="logo"><a href="/">@RedFoxBot</a></h1>

    <!-- Nav -->
    <nav id="nav">
        <ul>
            @if(isset($leftMenu))
                @foreach($leftMenu as $url => $title)
                    <li @if (Request::is($url)) class='active' @endif><a href="{{$url}}">{{$title}}</a></li>
                @endforeach
            @else
                <li> <a href="/">Главная</a></li>
            @endif

        </ul>
    </nav>

    {{--@include('layouts.search')--}}
    {{--@include('layouts.notify')--}}
    {{--@include('layouts.recent')--}}
{{--    @include('layouts.recent-comments')--}}
    {{--@include('layouts.calendar')--}}



    <!-- Recent Comments -->



    <!-- Copyright -->
    <ul id="copyright">
        <li>&copy; akeinhell</li>
        <li>
            <!-- Yandex.Metrika informer -->
            <a href="https://metrika.yandex.ru/stat/?id=37295300&amp;from=informer"
               target="_blank" rel="nofollow"><img src="https://informer.yandex.ru/informer/37295300/3_0_FFFFFFFF_EFEFEFFF_0_pageviews"
                                                   style="width:88px; height:31px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (просмотры, визиты и уникальные посетители)" onclick="try{Ya.Metrika.informer({i:this,id:37295300,lang:'ru'});return false}catch(e){}" /></a>
            <!-- /Yandex.Metrika informer -->

            <!-- Yandex.Metrika counter -->
            <script type="text/javascript">
                (function (d, w, c) {
                    (w[c] = w[c] || []).push(function() {
                        try {
                            w.yaCounter37295300 = new Ya.Metrika({
                                id:37295300,
                                clickmap:true,
                                trackLinks:true,
                                accurateTrackBounce:true,
                                trackHash:true
                            });
                        } catch(e) { }
                    });

                    var n = d.getElementsByTagName("script")[0],
                            s = d.createElement("script"),
                            f = function () { n.parentNode.insertBefore(s, n); };
                    s.type = "text/javascript";
                    s.async = true;
                    s.src = "https://mc.yandex.ru/metrika/watch.js";

                    if (w.opera == "[object Opera]") {
                        d.addEventListener("DOMContentLoaded", f, false);
                    } else { f(); }
                })(document, window, "yandex_metrika_callbacks");
            </script>
            <noscript><div><img src="https://mc.yandex.ru/watch/37295300" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
            <!-- /Yandex.Metrika counter -->
        </li>
        <li>
            <script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = location.protocol + '//vk.com/rtrg?r=pzNv5tRsmlh*e8Q/EfiwujiGq*sXJhD9qmhCD87PXzlvsEENDrCaVQSDOGYu6vNhAZJO791vv5p0zxuXAIpE4DWNEBFi45AjuFEohxwb4OPjCJkeZgGddXifxwBHFe9mRkJjo*cTjf4vBdWVBGP1APllRgDC4uaayzHXjLv7Z5E-';</script>
        </li>
    </ul>

</div>

<!-- Scripts -->
<script type="text/javascript">
    var reformalOptions = {
        project_id: 975735,
        project_host: "redfoxbot.reformal.ru",
        tab_orientation: "right",
        tab_indent: "50%",
        tab_bg_color: "#364050",
        tab_border_color: "#FFFFFF",
        tab_image_url: "http://tab.reformal.ru/T9GC0LfRi9Cy0Ysg0Lgg0L%252FRgNC10LTQu9C%252B0LbQtdC90LjRjw==/FFFFFF/a08a7c60392f68cb33f77d4f56cf8c6f/right/1/tab.png",
        tab_border_width: 2
    };

    (function() {
        var script = document.createElement('script');
        script.type = 'text/javascript'; script.async = true;
        script.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'media.reformal.ru/widgets/v3/reformal.js';
        document.getElementsByTagName('head')[0].appendChild(script);
    })();
</script><noscript><a href="http://reformal.ru"><img src="http://media.reformal.ru/reformal.png" /></a><a href="http://redfoxbot.reformal.ru">Oтзывы и предложения для Телеграмм-бот &quot;Лиса&quot;</a></noscript>
@yield('footer')

</body>
</html>
