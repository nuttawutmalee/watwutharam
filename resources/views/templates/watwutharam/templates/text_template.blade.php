@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $title = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'title');
    $content = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'content');
    $date = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'date');
    $dateText = null;

    if (isset_not_empty($date)) {
        $locale = \App\CMS\Helpers\CMSHelper::getCurrentLocale()
            ? \App\CMS\Helpers\CMSHelper::getCurrentLocale()
            : 'th';

        \Carbon\Carbon::executeWithLocale($locale, function ($newLocale) use ($date, &$dateText) {
            if ($carbonDate = \App\CMS\Helpers\CMSHelper::createDateTime($date)) {
                $dateText = $carbonDate->formatLocalized('%d %b %Y');
            }
        });
    }
    ?>

    <section class="section__text">
        <div class="section__outer">
            <div class="text__wrapper bg--body--3">
                <div class="section__content">
                    <div class="title">
                        <h2>@text($title)</h2>
                    </div>
                    <div class="date">@text($dateText)</div>
                    <div class="entry__content">@unescaped($content)</div>
                </div>
            </div>
        </div>
    </section>
@endhas