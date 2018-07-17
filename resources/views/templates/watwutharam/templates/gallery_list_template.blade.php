@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);

    // Section
    $sectionTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_title');
    $sectionButtonTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_title');
    $sectionButtonLink = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link');
    $sectionButtonLinkTarget = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_button_link_target', '_self');

    // Gallery
    $galleryLimit = intval(\App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'gallery_limit', 8));
    $collections = \App\CMS\Helpers\CMSHelper::getGlobalItemByCategories('GALLERY');
    $galleryItems = [];

    if (isset_not_empty($collections)) {
        $galleryItems = collect($collections)
            ->map(function ($collection) {
                return isset_not_empty($collection->items, []);
            })
            ->filter()
            ->flatten()
            ->take($galleryLimit)
            ->all();
    }
    ?>

    <section class="section__gallery section__gallery__lists bg--body--2 js-imageload-section-wrapper">
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
                <div class="gallery__slides">
                    <div class="gallery__slides__inner">
                        <div class="lists js-lightbox">
                            @foreach($galleryItems as $index => $galleryItem)
                                <?php
                                $imageThumbnail = isset_not_empty($galleryItem->image_thumbnail);
                                $imageLarge = isset_not_empty($galleryItem->image_large);
                                $imageAlt = isset_not_empty($galleryItem->image_alt);
                                $imageCaption = isset_not_empty($galleryItem->image_caption);
                                ?>
                                <div class="list">
                                    <div class="articles__item">
                                        <div class="articles__image bg__wrapper">
                                            <div class="bg__container">
                                                <img data-src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($imageThumbnail) }}" alt="@text($imageAlt)" class="js-imageload-section">
                                            </div>
                                        </div>
                                        <div class="gradient-hover"></div>
                                        <a href="{{ \App\CMS\Helpers\CMSHelper::thumbnail($imageLarge) }}" class="btn--link js-lightbox-link" data-caption="@text($imageCaption)"></a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="gallery__arrows"></div>
                </div>
            </div>
        </div>
    </section>
@endhas