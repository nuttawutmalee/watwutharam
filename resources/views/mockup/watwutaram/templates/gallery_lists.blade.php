<?php
$template_item = [
    'template' => 'gallery',
    'section_title' => 'รูปภาพ',
    'section_button_title' => 'ดูทั้งหมด',
    'section_button_url' => 'gallery',
    'section_button_target' => '',
    'data_item' => [
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img1.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img1_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img2.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img2_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img3.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img3_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img4.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img4_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img5.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img5_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img6.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img6_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img7.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img7_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img8.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img8_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ]
    ]
]
?>


    <section class="section__gallery section__gallery__lists bg--body--2 js-imageload-section-wrapper">
        <div class="section__outer">
            <div class="section__inner">

                {{-- Headline--}}
                <div class="headline">
                    <div class="title">
                        <h2 class="h3 text--inverse">{{ $template_item['section_title'] }}</h2>
                    </div>
                    <div class="button">
                        <a href="{{ $template_item['section_button_url'] }}" target="{{ $template_item['section_button_target'] }}" title="{{ $template_item['section_button_title'] }}"
                            class="btn--readmore">{{ $template_item['section_button_title'] }}
                            <i></i>
                        </a>
                    </div>
                </div>
                <div class="gallery__slides">
                    <div class="gallery__slides__inner">
                        {{-- lists --}}
                        <div class="lists js-lightbox">
                            @foreach($template_item['data_item'] as $i => $k)
                            <div class="list">
                                <div class="articles__item">
                                    <div class="articles__image bg__wrapper">
                                        <div class="bg__container">
                                            <img data-src="{{ $k['image_thumbnail_url'] }}" alt="{{ $k['image_alt'] }}" class="js-imageload-section">
                                        </div>
                                    </div>
                                    <div class="gradient-hover"></div>
                                    <a href="{{ $k['image_large_url'] }}" class="btn--link js-lightbox-link" data-caption=""></a>
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