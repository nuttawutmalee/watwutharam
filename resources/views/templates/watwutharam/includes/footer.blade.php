<?php
$siteName = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('footer', 'site_name');
$siteAddress1 = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('footer', 'site_address_1');
$siteAddress2 = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('footer', 'site_address_2');
$copyright = \App\CMS\Helpers\CMSHelper::getGlobalItemOptionValue('footer', 'copyright');
?>

<footer class="site__footer bg--brand">
    <div class="section__outer">
        <div class="footer__row">
            <div class="footer__column footer__column--full">
                @has($siteName)
                    <div class="footer__title">
                        <h3>@text($siteName)</h3>
                    </div>
                @endhas
            </div>
            <div class="footer__column">
                <div class="footer__address">
                    <address>@unescaped($siteAddress1)</address>
                </div>
            </div>
            <div class="footer__column">
                <div class="footer__address">
                    <address>@unescaped($siteAddress2)</address>
                </div>
            </div>
            <div class="footer__column footer__column--hide--desktop">
                <div class="footer__menu">
                    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.menu_top'))
                </div>
                <div class="footer__social">
                    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.social'))
                </div>
            </div>
        </div>

        @has($copyright)
            <div class="copyright">
                @unescaped($copyright)
            </div>
        @endhas
    </div>
</footer>