<?php
$socialMedia = \App\CMS\Helpers\CMSHelper::getGlobalItemByVariableName('social_media');
$socials = isset_not_empty($socialMedia->socials, []);
?>

@has($socials)
    <ul class="site__social">
        @foreach($socials as $index => $social)
        <?php
        $socialIcon = isset_not_empty($social->social_icon);
        $socialTitle = isset_not_empty($social->social_title);
        $socialUrl = isset_not_empty($social->social_url);
        $socialUrlTarget = isset_not_empty($social->social_url_target, '_blank');
        ?>
        <li>
            <a href="{{ \App\CMS\Helpers\CMSHelper::url($socialUrl) }}" 
                target="@text($socialUrlTarget)"
                title="@text($socialTitle)">
                <i class="icon-@text($socialIcon)"></i>
            </a>
        </li>
        @endforeach
    </ul>
@endhas