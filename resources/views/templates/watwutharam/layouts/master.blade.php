<!doctype html>
<html lang="{{ \App\CMS\Helpers\CMSHelper::getCurrentLocale() ?: 'en' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- HTML Meta Tags -->
    {!! \App\CMS\Helpers\CMSHelper::generatePageMetadata() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateOpenGraphMetadata() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateTwitterCardMetadata() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateThemeColorMetadata() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateFaviconGroup() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateAppleTouchIconGroup() !!}

    {!! \App\CMS\Helpers\CMSHelper::generateMSApplicationIconGroup() !!}

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="HandheldFriendly" content="true">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE"> 

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Critical -->
    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('includes.critical'))

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
        @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('includes.header'))
        <!-- BEGIN PAGE CONTAINER -->
        <div class="page__container" data-scrollbar>
            <div class="wrap">

                <!-- BEGIN MAIN -->
                <main class="site__main">
                    @yield('content')
                </main>
                <!-- END MAIN -->
                @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('includes.footer'))
            </div>
        </div>
        <!-- END PAGE CONTAINER -->
    </div>
    <!-- END GLOBAL CONTAINER -->
    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('includes.preload'))
    <!-- Add your site or application content here -->

    <!-- Main script -->
    <script src="{{ CMSHelper::getAssetPath('js/scripts.js') }}" async></script>

    <!-- Custom scripts -->
    @stack('scripts')
</body>
</html>