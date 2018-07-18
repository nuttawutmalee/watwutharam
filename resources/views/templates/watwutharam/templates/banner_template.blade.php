@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $title = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'title');
    $image = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'image');
    $imageAlt = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'image_alt');
    ?>

    @has($image)
        <section class="section__banner">
            <div class="section__outer">
                <div class="banner__wrapper">
                    <div class="banner__image bg__wrapper">
                        <div class="bg__container">
                            <img src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($image) }}" alt="@text($imageAlt)">
                        </div>
                    </div>
                    @has($title)
                        <div class="banner__content text--white text--center">
                            <div class="banner__content__inner">
                                <div class="title">
                                    <h1>@text($title)</h1>
                                </div>
                            </div>
                        </div>
                    @endhas
                </div>
            </div>
        </section>
    @endhas
@endhas
