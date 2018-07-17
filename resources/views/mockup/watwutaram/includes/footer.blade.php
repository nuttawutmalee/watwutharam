<?php
$template_item = [
    'footer_title' => 'วัดวุฒาราม',
    'address_1' => '<p>923 ซอย วุฒาราม 8<br> ตำบลในเมือง อำเภอเมืองขอนแก่น<br> ขอนแก่น 40000</p>',
    'address_2' => '<p>โทร. <a href="tel:043 343 050">043 343 050</a><br> อีเมล์ <a href="mailto:info@watwutram.co.th">info@watwutram.co.th</a></p>',
    'copyright' => '<p>Copyright watwutaram.co.th All Rights Reserved.</p>',
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
    <footer class="site__footer bg--brand">
        <div class="section__outer">
            <div class="footer__row">
                <div class="footer__column footer__column--full">
                    <div class="footer__title">
                        <h3>{{ $template_item['footer_title'] }}</h3>
                    </div>
                </div>
                <div class="footer__column">
                    <div class="footer__address">
                        <address>
                            {!! $template_item['address_1'] !!}
                        </address>
                    </div>
                </div>
                <div class="footer__column">
                    <div class="footer__address">
                        {!! $template_item['address_2'] !!}
                    </div>
                </div>
                <div class="footer__column footer__column--hide--desktop">
                <div class="footer__menu">
                @include('mockup.watwutaram.partials.menu_top')
            </div>
            <div class="footer__social">
            @include('mockup.watwutaram.partials.social')
            </div>
                </div>
            </div>

            
            <div class="copyright">
                {!! $template_item['copyright'] !!}
            </div>
        </div>
    </footer>