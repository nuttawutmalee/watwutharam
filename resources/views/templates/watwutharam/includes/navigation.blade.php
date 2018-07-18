<?php
$siteLanguages = \App\CMS\Helpers\CMSHelper::getSiteLanguages();
$mainGroupMenu = \App\CMS\Helpers\CMSHelper::getGlobalItemByVariableName('main_menu_group');
$mainMenus = isset_not_empty($mainGroupMenu->menus, []);
$currentUrl = url()->current();
?>

<nav class="site__nav">
    <div class="site__nav__top">
        @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.menu_top'))
        @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.social'))

        @has($siteLanguages)
            @if(count($siteLanguages) > 1)
                <?php
                $currentLanguage = \App\CMS\Helpers\CMSHelper::getCurrentLanguage();
                $languages = collect($siteLanguages)
                    ->reject(function ($siteLanguage) use ($currentLanguage) {
                        return $siteLanguage->code === $currentLanguage->code;
                    })
                    ->all();
                ?>
                <div class="site__language">
                    <span>
                        @text($currentLanguage->name)
                        <i></i>
                    </span>
                    @has($languages)
                        <ul class="">
                            @foreach($languages as $index => $language)
                                <li>
                                    <a href="{{ \App\CMS\Helpers\CMSHelper::url(null, $language->code) }}">@text($language->name)</a>
                                </li>
                            @endforeach
                        </ul>
                    @endhas
                </div>
            @endif
        @endhas
    </div>
    @has($mainMenus)
        <div class="site__nav__main">
            <ul class="site__menu">
                @foreach($mainMenus as $index => $menu)
                    <?php
                    $url = isset_not_empty($menu->url);
                    $target = isset_not_empty($menu->target, '_self');
                    $label = isset_not_empty($menu->label);
                    ?>
                    <li>
                        <a href="{{ \App\CMS\Helpers\CMSHelper::url($url) }}"
                            target="@text($target)"
                            title="@text($label)" 
                            @if($currentUrl === $url) class="is--active" @endif>
                            @text($label)
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endhas
</nav>