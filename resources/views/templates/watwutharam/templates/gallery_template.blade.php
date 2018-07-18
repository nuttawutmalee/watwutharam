@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $currentPage = app('request')->input('p', 1);
    $totalPages = 0;

    // Gallery
    $collections = \App\CMS\Helpers\CMSHelper::getGlobalItemByCategories('GALLERY');
    $galleryItems = [];

    if (isset_not_empty($collections)) {
        $galleryItems = collect($collections)
            ->map(function ($collection) {
                return isset_not_empty($collection->items, []);
            })
            ->filter()
            ->flatten()
            ->all();
    }


    if (count($galleryItems) > 0) {
        $totalPages = round(count($galleryItems) / 8);
    }

    $galleryItems = collect($galleryItems)->slice(($currentPage - 1) * 8, 8)->all();
    ?>

    @has($galleryItems)
        <section class="section__gallery">
            <div class="section__outer">
                <div class="section__inner">
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
                                            <img data-src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($imageThumbnail) }}" alt="@text($imageAlt)" class="js-imageload">
                                        </div>
                                    </div>
                                    <div class="gradient-hover"></div>
                                    <a href="{{ \App\CMS\Helpers\CMSHelper::thumbnail($imageLarge) }}" class="btn--link js-lightbox-link" data-caption="@text($imageCaption)"></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.pagination'), [
                        'totalPages' => $totalPages,
                        'currentPage' => $currentPage
                    ])
                </div>
            </div>
        </section>
    @endhas
@endhas