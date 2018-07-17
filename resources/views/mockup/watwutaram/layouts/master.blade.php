<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- HTML Meta Tags -->
    <title>@yield('title')</title>
    <meta name="description" content="">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="">
    <meta itemprop="description" content="">
    <meta itemprop="image" content="">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="">
    <meta property="og:type" content="website">
    <meta property="og:title" content="">
    <meta property="og:description" content="">
    <meta property="og:image" content="">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="">
    <meta name="twitter:title" content="">
    <meta name="twitter:description" content="">
    <meta name="twitter:image" content="">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="HandheldFriendly" content="true">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE"> 

    <link rel="apple-touch-icon" sizes="180x180" href="{{ CMSHelper::getAssetPath('assets/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ CMSHelper::getAssetPath('assets/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ CMSHelper::getAssetPath('assets/favicons/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ CMSHelper::getAssetPath('assets/favicons/favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ CMSHelper::getAssetPath('assets/favicons/favicon.ico') }}" type="image/x-icon">
    <meta name="msapplication-TileColor" content="#f5d3a3">
    <meta name="msapplication-TileImage" content="{{ CMSHelper::getAssetPath('assets/favicons/mstile-144x144.png') }}">
    <meta name="theme-color" content="#f5d3a3">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Viewport -->
    @include('mockup.watwutaram.includes.critical')

    <!-- Custom styles -->
    @stack('styles')

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="is--first--loading">

    <!-- BEGIN GLOBAL CONTAINER -->
    <div class="global__container">
        @include('mockup.watwutaram.includes.header')
        <!-- BEGIN PAGE CONTAINER -->
        <div class="page__container" data-scrollbar>
            <div class="wrap">

                <!-- BEGIN MAIN -->
                <main class="site__main">
                    @yield('content')
                </main>
                <!-- END MAIN -->
                @include('mockup.watwutaram.includes.footer')
            </div>
        </div>
        <!-- END PAGE CONTAINER -->
    </div>
    <!-- END GLOBAL CONTAINER -->
    @include('mockup.watwutaram.includes.preload')
    <!-- Add your site or application content here -->

    <!-- Main script -->
    <script src="{{ CMSHelper::getAssetPath('js/scripts.js') }}" async></script>

    <!-- Custom scripts -->
    @stack('scripts')
</body>

</html>