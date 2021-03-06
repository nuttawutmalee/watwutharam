<?php
$currentUrl = app('request')->path();
$topGroupMenu = \App\CMS\Helpers\CMSHelper::getGlobalItemByVariableName('top_menu_group');
$topMenus = isset_not_empty($topGroupMenu->menus, []);
?>

@has($topMenus)
    <ul class="site__menu">
        @foreach($topMenus as $index => $menu)
            <?php
            $url = isset_not_empty($menu->url);
            $target = isset_not_empty($menu->target, '_self');
            $label = isset_not_empty($menu->label);
            $url = $url === 'homepage' ? '/' : $url;
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
@endhas