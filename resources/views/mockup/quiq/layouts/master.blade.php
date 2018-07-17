<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>QUIQ Boilerplate</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Custom styles --}}
        @stack('styles')
    </head>
    <body>
        <!-- Add your site or application content here -->
        @yield('content')
        
        {{-- Main script --}}
        <script src="{{ \App\CMS\Helpers\CMSHelper::getAssetPath('js/app.js') }}" async defer></script>

        {{-- Custom scripts --}}
        @stack('scripts')
    </body>
</html>