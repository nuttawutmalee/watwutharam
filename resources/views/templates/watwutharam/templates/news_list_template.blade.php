@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);

    // Section
    $sectionTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_title');
    $sectionButtonTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_title');
    $sectionButtonLink = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link');
    $sectionButtonLinkTarget = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link_target', '_self');

    // News
    $newsLimit = intval(\App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'news_limit', 4));
    $newsCategory = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'news_category');
    $news = \App\CMS\Helpers\CMSHelper::getPagesByCategories(
        $newsCategory,
        $newsLimit,
        CMSConstants::ORDER_BY_UPDATED_AT,
        CMSConstants::ORDER_DESC,
        null,
        ['news_metadata']
    );
    ?>

    @has($news)
        <section class="section__news section__news__lists bg--body--2 js-imageload-section-wrapper">
            <div class="section__outer">
                <div class="section__inner">
                    <div class="headline">
                        @has($sectionTitle)
                            <div class="title">
                                <h2 class="h3 text--inverse">@text($sectionTitle)</h2>
                            </div>
                        @endhas
                        @if(isset_not_empty($sectionButtonLink) && isset_not_empty($sectionButtonTitle))
                            <div class="button">
                                <a href="{{ \App\CMS\Helpers\CMSHelper::url($sectionButtonLink) }}"
                                    target="@text($sectionButtonLinkTarget)"
                                    title="@text($sectionButtonLinkTitle)"
                                    class="btn--readmore">
                                    @text($sectionButtonTitle)
                                    <i></i>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="lists">
                        @foreach($news as $index => $newsItem)
                            <?php
                            $metadata = \App\CMS\Helpers\CMSHelper::getPageItemByVariableName('news_metadata', $newsItem);
                            $link = isset_not_empty($newsItem->friendly_url);
                            $title = isset_not_empty($metadata->title);
                            $image = isset_not_empty($metadata->image);
                            $imageAlt = isset_not_empty($metadata->image_alt);
                            $description = isset_not_empty($metadata->description);
                            $buttonLinkTitle = isset_not_empty($metadata->button_link_title);

                            $day = null;
                            $month = null;

                            if ($eventDate = isset_not_empty($metadata->event_date)) {
                                $locale = \App\CMS\Helpers\CMSHelper::getCurrentLocale()
                                    ? \App\CMS\Helpers\CMSHelper::getCurrentLocale()
                                    : 'th';

                                \Carbon\Carbon::executeWithLocale($locale, function ($newLocale) use ($eventDate, &$day, &$month) {
                                    if ($carbonDate = \App\CMS\Helpers\CMSHelper::createDateTime($eventDate)) {
                                        $day = $carbonDate->formatLocalized('%d');
                                        $month = $carbonDate->formatLocalized('%h');
                                    }
                                });
                            }
                            ?>
                            <div class="list">
                                <div class="news__item">
                                    <div class="news__row">
                                        <div class="news__column news__column--image">
                                            <div class="news__image bg__wrapper">
                                                <div class="bg__container">
                                                    <img data-src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($image) }}"
                                                        alt="@text($imageAlt)"
                                                        class="js-imageload-section">
                                                </div>
                                                <div class="gradient-hover"></div>
                                                <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}" class="btn--link"></a>
                                            </div>
                                        </div>
                                        <div class="news__column news__column--content">
                                            <div class="news__content">
                                                <div class="news__top">
                                                    @if(isset_not_empty($day) && isset_not_empty($month))
                                                        <div class="news__date">
                                                            <span class="day">@text($day)</span>
                                                            <span class="month">@text($month)</span>
                                                        </div>
                                                    @endif
                                                    <div class="news__title">
                                                        <h3 class="h5 text--inverse">@text($title)</h3>
                                                    </div>
                                                </div>
                                                <div class="news__desc">@unescaped($description)</div>
                                                <div class="news__button">
                                                    <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}"
                                                        title="@text($buttonTitle)" class="btn--readmore">
                                                        @text($buttonLinkTitle)
                                                        <i></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endhas
@endhas