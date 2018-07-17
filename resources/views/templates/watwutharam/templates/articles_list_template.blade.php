@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);

    // Section
    $sectionTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_title');
    $sectionButtonTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_title');
    $sectionButtonLink = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link');
    $sectionButtonLinkTarget = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link_target', '_self');

    // Articles
    $articleLimit = intval(\App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'article_limit', 6));
    $articleCategory = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'article_category');
    $articles = \App\CMS\Helpers\CMSHelper::getPagesByCategories(
        $articleCategory,
        $articleLimit,
        CMSConstants::ORDER_BY_UPDATED_AT,
        CMSConstants::ORDER_DESC,
        null,
        ['article_metadata']
    );
    ?>

    @has($articles)
        <section class="section__articles section__articles__lists js-imageload-section-wrapper">
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
                    <div class="articles__slides">
                        <div class="articles__slide__inner">
                            <div class="lists">
                                @foreach($articles as $index => $article)
                                    <?php
                                    $metadata = \App\CMS\Helpers\CMSHelper::getPageItemByVariableName('article_metadata', $article);
                                    $link = isset_not_empty($article->friendly_url);
                                    $title = isset_not_empty($metadata->title);
                                    $image = isset_not_empty($metadata->image);
                                    $imageAlt = isset_not_empty($metadata->image_alt);
                                    $description = isset_not_empty($metadata->description);
                                    $buttonLinkTitle = isset_not_empty($metadata->button_link_title);
                                    ?>
                                    <div class="list">
                                        <div class="articles__item">
                                            <div class="articles__top">
                                                <div class="articles__title">
                                                    <h3 class="h5 text--inverse">@text($title)</h3>
                                                </div>
                                            </div>
                                            <div class="articles__image bg__wrapper">
                                                <div class="bg__container">
                                                    <img data-src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($image) }}"
                                                        alt="@text($imageAlt)"
                                                        class="js-imageload-section">
                                                </div>
                                                <div class="gradient-hover"></div>
                                                <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}" class="btn--link"></a>
                                            </div>
                                            <div class="articles__content">
                                                <div class="articles__desc">@unescaped($description)</div>
                                                <div class="articles__button">
                                                    <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}"
                                                        title="@text($buttonTitle)" class="btn--readmore">
                                                        @text($buttonLinkTitle)
                                                        <i></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="articles__arrows"></div>
                    </div>
                </div>
            </div>
        </section>
    @endhas
@endhas