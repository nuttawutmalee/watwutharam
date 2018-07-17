<?php
$template_item = [
  'template' => 'news',
  'data_item' => [
    [
      'image_url' => CMSHelper::getAssetPath('assets/images/news/news-1.jpg'),
      'image_alt' => '',
      'title' => 'เปิดศูนย์สุขภาพวิถีพุทธและศูนย์ศิลปวัฒนธรรมอีสาน',
      'desc' => '<p>คลับเอ็กซ์เพรสชนะเลิศ โอ้ยไฮเปอร์แอคทีฟโซนรีโมต มะกันซิงเท็กซ์ เคลื่อนย้าย เซ็นทรัล แพนดาแอ็คชั่นดัมพ์ แรงใจซีเรียสดีไซน์เนอร์ศิลป วัฒนธรรมสังโฆ ออร์แกนิกหลวงพี่ ออทิสติก พรีเมียมรุสโซ เสกสรรค์...</p>',
      'button_title' => 'อ่านต่อ',
      'button_url' => 'text',
      'button_target' => '',
      'day' => '12',
      'month' => 'มิ.ย.'
    ],
    [
        'image_url' => CMSHelper::getAssetPath('assets/images/news/news-2.jpg'),
        'image_alt' => '',
        'title' => 'ถวายน้ำปานะ-น้ำดื่ม-ภัตตาหารถวายพระ ๕๐๐ รูป',
        'desc' => '<p>คลับเอ็กซ์เพรสชนะเลิศ โอ้ยไฮเปอร์แอคทีฟโซนรีโมต มะกันซิงเท็กซ์ เคลื่อนย้าย เซ็นทรัล แพนดาแอ็คชั่นดัมพ์ แรงใจซีเรียสดีไซน์เนอร์ศิลป วัฒนธรรมสังโฆ ออร์แกนิกหลวงพี่ ออทิสติก พรีเมียมรุสโซ เสกสรรค์...</p>',
        'button_title' => 'อ่านต่อ',
        'button_url' => 'text',
        'button_target' => '',
        'day' => '02',
        'month' => 'ก.ค.'
      ],
      [
        'image_url' => CMSHelper::getAssetPath('assets/images/news/news-3.jpg'),
        'image_alt' => '',
        'title' => 'ขอเชิญเป็นเจ้าภาพถวายภัตตาหาร และน้ำปานะแก่พระภิกษุสามเณร',
        'desc' => '<p>คลับเอ็กซ์เพรสชนะเลิศ โอ้ยไฮเปอร์แอคทีฟโซนรีโมต มะกันซิงเท็กซ์ เคลื่อนย้าย เซ็นทรัล แพนดาแอ็คชั่นดัมพ์ แรงใจซีเรียสดีไซน์เนอร์ศิลป วัฒนธรรมสังโฆ ออร์แกนิกหลวงพี่ ออทิสติก พรีเมียมรุสโซ เสกสรรค์...</p>',
        'button_title' => 'อ่านต่อ',
        'button_url' => 'text',
        'button_target' => '',
        'day' => '22',
        'month' => 'ก.ค.'
      ],
      [
        'image_url' => CMSHelper::getAssetPath('assets/images/news/news-4.jpg'),
        'image_alt' => '',
        'title' => 'ขอเชิญร่วมงานบุญมหาชาติ',
        'desc' => '<p>คลับเอ็กซ์เพรสชนะเลิศ โอ้ยไฮเปอร์แอคทีฟโซนรีโมต มะกันซิงเท็กซ์ เคลื่อนย้าย เซ็นทรัล แพนดาแอ็คชั่นดัมพ์ แรงใจซีเรียสดีไซน์เนอร์ศิลป วัฒนธรรมสังโฆ ออร์แกนิกหลวงพี่ ออทิสติก พรีเมียมรุสโซ เสกสรรค์...</p>',
        'button_title' => 'อ่านต่อ',
        'button_url' => 'text',
        'button_target' => '',
        'day' => '30',
        'month' => 'ธ.ค.'
      ]
  ]
]
?>
    <section class="section__news">
        <div class="section__outer">
            <div class="section__inner">
                <div class="lists">
                    @foreach($template_item['data_item'] as $i => $k)
                    <div class="list">
                        <div class="news__item">
                            <div class="news__row">
                                <div class="news__column news__column--image">
                                    <div class="news__image bg__wrapper">
                                        <div class="bg__container">
                                            <img data-src="{{ $k['image_url'] }}" alt="{{ $k['image_alt'] }}" class="js-imageload">
                                        </div>
                                        <div class="gradient-hover"></div>
                                        <a href="{{ $k['button_url'] }}" class="btn--link"></a>
                                    </div>
                                </div>
                                <div class="news__column news__column--content">
                                    <div class="news__content">
                                        <div class="news__top">
                                            <div class="news__date">
                                                <span class="day">{{ $k['day'] }}</span>
                                                <span class="month">{{ $k['month'] }}</span>
                                            </div>
                                            <div class="news__title">
                                                <h3 class="h5 text--inverse">{{ $k['title'] }}</h3>
                                            </div>
                                        </div>
                                        <div class="news__desc">
                                            {!! $k['desc'] !!}
                                        </div>
                                        <div class="news__button">
                                            <a href="{{ $k['button_url'] }}" target="{{ $k['button_target'] }}" class="btn--readmore">{{ $k['button_title'] }}
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
                @include('mockup.watwutaram.partials.pagination')
            </div>
        </div>
    </section>