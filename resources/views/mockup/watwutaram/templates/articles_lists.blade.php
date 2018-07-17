<?php
$template_item = [
  'template' => 'articles',
  'section_title' => 'บทความ',
  'section_button_title' => 'ดูทั้งหมด',
  'section_button_url' => 'articles',
  'section_button_target' => '',
  'data_item' => [
    [
      'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-1.jpg'),
      'image_alt' => '',
      'title' => 'อานิสงส์ของการสวดมนต์',
      'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัยและไม่ใช่เรื่องของคนแก่...</p>',
      'button_title' => 'อ่านต่อ',
        'button_url' => 'text',
        'button_target' => '',
    ],
    [
        'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-2.jpg'),
        'image_alt' => '',
        'title' => 'หมดหวังท้อแท้ในชีวิต...คิดอย่างไรให้ใจสู้',
        'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัยและไม่ใช่เรื่องของคนแก่...</p>',
        'button_title' => 'อ่านต่อ',
          'button_url' => 'text',
          'button_target' => '',
      ],
      [
        'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-3.jpg'),
        'image_alt' => '',
        'title' => 'ความเป็นมาของวันพระ',
        'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัย...</p>',
        'button_title' => 'อ่านต่อ',
          'button_url' => 'text',
          'button_target' => '',
      ],
      [
        'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-1.jpg'),
        'image_alt' => '',
        'title' => 'อานิสงส์ของการสวดมนต์',
        'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัยและไม่ใช่เรื่องของคนแก่...</p>',
        'button_title' => 'อ่านต่อ',
          'button_url' => 'text',
          'button_target' => '',
      ],
      [
          'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-2.jpg'),
          'image_alt' => '',
          'title' => 'หมดหวังท้อแท้ในชีวิต...คิดอย่างไรให้ใจสู้',
          'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัยและไม่ใช่เรื่องของคนแก่...</p>',
          'button_title' => 'อ่านต่อ',
            'button_url' => 'text',
            'button_target' => '',
        ],
        [
          'image_url' => CMSHelper::getAssetPath('assets/images/articles/articles-3.jpg'),
          'image_alt' => '',
          'title' => 'ความเป็นมาของวันพระ',
          'desc' => '<p>การสวดมนต์นั้น ต้องถือว่าเป็นเรื่องที่ง่าย สำหรับทุกคนในยุคนี้ สะดวกมากใน ทุกเพศทุกวัย...</p>',
          'button_title' => 'อ่านต่อ',
            'button_url' => 'text',
            'button_target' => '',
        ]
  ]
]
?>

    <section class="section__articles section__articles__lists js-imageload-section-wrapper">
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
                <div class="articles__slides">
                    <div class="articles__slide__inner">
                        {{-- lists --}}
                        <div class="lists">
                            @foreach($template_item['data_item'] as $i => $k)
                            <div class="list">
                                <div class="articles__item">
                                    <div class="articles__top">
                                        <div class="articles__title">
                                            <h3 class="h5 text--inverse">{{ $k['title'] }}</h3>
                                        </div>
                                    </div>
                                    <div class="articles__image bg__wrapper">
                                        <div class="bg__container">
                                            <img data-src="{{ $k['image_url'] }}" alt="{{ $k['image_alt'] }}" class="js-imageload-section">
                                        </div>
                                        <div class="gradient-hover"></div>
                                        <a href="{{ $k['button_url'] }}" class="btn--link"></a>
                                    </div>
                                    <div class="articles__content">
                                        <div class="articles__desc">
                                            {!! $k['desc'] !!}
                                        </div>
                                        <div class="articles__button">
                                            <a href="{{ $k['button_url'] }}" target="{{ $k['button_target'] }}" title="{{ $k['button_title'] }}" class="btn--readmore">{{ $k['button_title'] }}
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