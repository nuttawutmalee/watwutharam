<?php
$template_item = [
    'template' => 'gallery',
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
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img9.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img9_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img10.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img10_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img11.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img11_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img12.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img12_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img13.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img13_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img14.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img14_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img15.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img15_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ],
        [
            'image_thumbnail_url' => CMSHelper::getAssetPath('assets/images/gallery/img16.jpg'),
            'image_large_url' => CMSHelper::getAssetPath('assets/images/gallery/img16_large.jpg'),
            'image_alt' => '',
            'caption' => ''
        ]
    ]
]
?>


    <section class="section__gallery">
        <div class="section__outer">
            <div class="section__inner">

                {{-- lists --}}
                <div class="lists js-lightbox">
                    @foreach($template_item['data_item'] as $i => $k)
                    <div class="list">
                        <div class="articles__item">
                            <div class="articles__image bg__wrapper">
                                <div class="bg__container">
                                    <img data-src="{{ $k['image_thumbnail_url'] }}" alt="{{ $k['image_alt'] }}" class="js-imageload">
                                </div>
                            </div>
                            <div class="gradient-hover"></div>
                            <a href="{{ $k['image_large_url'] }}" class="btn--link js-lightbox-link" data-caption=""></a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @include('mockup.watwutaram.partials.pagination')
            </div>
        </div>
    </section>