<?php
$template_item = [
  'template' => 'hero',
  'data_item' => [
    [
      'image_url' => CMSHelper::getAssetPath('assets/images/hero/hero-1.jpg'),
      'image_alt' => '',
      'title' => 'วัดส่งเสริมสุขภาพ',
      'sub_title' => 'ส่งเสริมชุมชนทั้ง 8 ชุมชนรอบวัดวุฒาราม เทศบาลขอนแก่น',
    ],
    [
        'image_url' => CMSHelper::getAssetPath('assets/images/hero/hero-2.jpg'),
        'image_alt' => '',
        'title' => 'วัดส่งเสริมสุขภาพ',
        'sub_title' => 'ส่งเสริมชุมชนทั้ง 8 ชุมชนรอบวัดวุฒาราม เทศบาลขอนแก่น',
    ]
  ]
]
?>
    <section class="section__hero">
        <div class="section__outer">
            <div class="slides">
                @foreach($template_item['data_item'] as $i => $k)
                <div class="slide">
                    <div class="hero__image bg__wrapper">
                        <div class="bg__container">
                            <img src="{{ $k['image_url'] }}" alt="{{ $k['image_alt'] }}">
                        </div>
                    </div>
                    <div class="hero__content text--white text--center">
                        <div class="hero__content__inner">
                            <div class="title">
                                <h1>{{ $k['title'] }}</h1>
                            </div>
                            <div class="sub__title">
                                <h3 class="h4">{{ $k['sub_title'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>