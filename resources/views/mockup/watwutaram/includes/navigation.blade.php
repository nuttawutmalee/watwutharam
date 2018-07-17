<?php
$template_item = [
  'template' => 'hero',
  'data_top_menu' => [
      [
          'nav_title' => 'เกี่ยวกับเรา',
          'nav_url' => 'about',
          'nav_target' => ''
      ],
      [
          'nav_title' => 'text',
          'nav_url' => 'text',
          'nav_target' => ''
      ]
  ],
  'data_main_menu' => [
      [
          'nav_title' => 'หน้าหลัก',
          'nav_url' => '/',
          'nav_target' => ''
      ],
      [
          'nav_title' => 'ข่าวสาร',
          'nav_url' => 'news',
          'nav_target' => ''
      ],
      [
          'nav_title' => 'บทความ',
          'nav_url' => 'articles',
          'nav_target' => ''
      ],
      [
          'nav_title' => 'รูปภาพ',
          'nav_url' => 'gallery',
          'nav_target' => ''
      ],
      [
          'nav_title' => 'ติดต่อเรา',
          'nav_url' => 'contact',
          'nav_target' => ''
      ]
  ],
  'data_social' => [
      [
          'social_title' => 'facebook',
          'social_url' => '#',
          'social_target' => '',
          'social_class' => 'facebook', // behance facebook flickr google-plus instagram linkedin pinterest skype twitter vimeo youtube
      ],
      [
          'social_title' => 'twitter',
          'social_url' => '#',
          'social_target' => '',
          'social_class' => 'twitter' // behance facebook flickr google-plus instagram linkedin pinterest skype twitter vimeo youtube
      ],
      [
          'social_title' => 'instagram',
          'social_url' => '#',
          'social_target' => '',
          'social_class' => 'instagram' // behance facebook flickr google-plus instagram linkedin pinterest skype twitter vimeo youtube
      ],
      [
          'social_title' => 'youtube',
          'social_url' => '#',
          'social_target' => '',
          'social_class' => 'youtube' // behance facebook flickr google-plus instagram linkedin pinterest skype twitter vimeo youtube
      ]
  ]
]

?>
    <nav class="site__nav">
        <div class="site__nav__top">
            @include('mockup.watwutaram.partials.menu_top') @include('mockup.watwutaram.partials.social')
            <div class="site__language">
                <span>ภาษาไทย
                    <i></i>
                </span>
                <ul class="">
                    <li>
                        <a href="">English</a>
                    </li>
                    <li>
                        <a href="">Deutsch</a>
                    </li>
                    <li>
                        <a href="">español</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="site__nav__main">
            <ul class="site__menu">
                @foreach($template_item['data_main_menu'] as $i => $k)
                <li>
                    <a href="{{ $k['nav_url'] }}" target="{{ $k['nav_target'] }}" title="{{ $k['nav_title'] }}" @isset($page) @if($page===$k[ 'nav_url'])
                        class="is--active" @endif @endisset>{{ $k['nav_title'] }}</a>
                </li>
                @endforeach
            </ul>
        </div>
    </nav>