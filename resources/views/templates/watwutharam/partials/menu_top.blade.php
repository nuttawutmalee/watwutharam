<?php
$currentUrl = url()->current();
$topGroupMenu = \App\CMS\Helpers\CMSHelper::getGlobalItemByVariableName('top_group_menu');
$topMenus = isset_not_empty($topGroupMenu->menus, []);
?>

@has($topMenus)
    <ul class="site__menu">
        @foreach($topMenus as $index => $menu)
            <?php
            $url = isset_not_empty($menu->url);
            $target = isset_not_empty($menu->target, '_self');
            $label = isset_not_empty($menu->label);
            ?>
            <li>
                <a href="{{ {{ \App\CMS\Helpers\CMSHelper::url($item->url) }} }}"
                    target="@text($item->target)"
                    title="@text($item->label)" 
                    @if($currentUrl === $url) class="is--active" @endif>
                    @text($item->label)
                </a>
            </li>
        @endforeach
    </ul>
@endhas