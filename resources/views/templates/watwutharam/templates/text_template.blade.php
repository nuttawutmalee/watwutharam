@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $title = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'title');
    $subtitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'subtitle');
    $content = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'content');
    ?>

    <section class="section__text">
        <div class="section__outer">
            <div class="text__wrapper bg--body--3">
                <div class="section__content">
                    <div class="title">
                        <h2>@text($title)</h2>
                    </div>
                    <div class="date">@text($subtitle)</div>
                    <div class="entry__content">@unescaped($content)</div>
                </div>
            </div>
        </div>
    </section>
@endhas