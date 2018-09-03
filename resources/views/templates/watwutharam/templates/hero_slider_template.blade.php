@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $banners = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'banners', []);
    ?>

    @has($banners)
        <section class="section__hero">
            <div class="section__outer">
                <div class="slides">
                    @foreach($banners as $index => $banner)
                        <?php
                        $title = isset_not_empty($banner->title);
                        $subtitle = isset_not_empty($banner->subtitle);
                        $image = isset_not_empty($banner->image);
                        $imageAlt = isset_not_empty($banner->image_alt);
                        $fontClass = isset_not_empty($banner->font_color) === 'black' ? 'font-black' : '';
                        ?>

                        @has($image)
                            <div class="slide">
                                <div class="hero__image bg__wrapper">
                                    <div class="bg__container">
                                        <img src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($image) }}" alt="@text($imageAlt)">
                                    </div>
                                </div>

                                @if(isset_not_empty($title) || isset_not_empty($subtitle))
                                    <div class="hero__content text--white text--center">
                                        <div class="hero__content__inner {{ $fontClass }}">
                                            @has($title)
                                                <div class="title">
                                                    <h1>@text($title)</h1>
                                                </div>
                                            @endhas
                                            @has($subtitle)
                                                <div class="sub__title">
                                                    <h3 class="h4">@text($subtitle)</h3>
                                                </div>
                                            @endhas
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endhas
                    @endforeach
                </div>
            </div>
        </section>
    @endhas
@endhas