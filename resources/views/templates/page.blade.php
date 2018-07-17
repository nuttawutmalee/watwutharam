@extends(\App\CMS\Helpers\CMSHelper::getTemplatePath('layouts.master'))

@section('content')
    @if(isset_not_empty(${\App\CMS\Constants\CMSConstants::RENDER_DATA}))
        @foreach (${\App\CMS\Constants\CMSConstants::RENDER_DATA} as $key => $template)
            @include($template[\App\CMS\Constants\CMSConstants::TEMPLATE_PATH], $template)
        @endforeach
    @endif
@endsection