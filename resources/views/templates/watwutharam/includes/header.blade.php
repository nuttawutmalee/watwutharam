<?php
$siteName = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('header', 'site_name');
$siteAddress = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('header', 'site_address');
?>

<header class="site__header">
    <div class="section__outer">
        <div class="site__header__inner">
            <div class="site__logo">
                <a href="{{ \App\CMS\Helpers\CMSHelper::url('/') }}">@text($siteName)</a>
                <address>@text($siteAddress)</address>
            </div>
            @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('includes.navigation'))
            <div class="site__burger btn--burger">
                <div class="btn--burger--inner">
                    <span class="btn--burger--bar"></span>
                    <span class="btn--burger--bar"></span>
                    <span class="btn--burger--bar"></span>
                </div>
            </div>
        </div>
    </div>
</header>