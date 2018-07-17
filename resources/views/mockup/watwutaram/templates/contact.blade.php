<?php
$template_item = [
    'contact_title' => 'ที่อยู่',
    'address_1' => '<p>923 ซอย วุฒาราม 8<br> ตำบลในเมือง อำเภอเมืองขอนแก่น<br> ขอนแก่น 40000</p>',
    'address_2' => '<p>โทร. <a href="tel:043 343 050">043 343 050</a><br> อีเมล์ <a href="mailto:info@watwutram.co.th">info@watwutram.co.th</a></p>',
    'map_src' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3827.078150147152!2d102.82388751486313!3d16.420856888664556!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x312289f57c4f3087%3A0x913b5b5bac958cb2!2sWat+Wutharam!5e0!3m2!1sen!2sth!4v1531411532462'
]
?>
    <section class="section__contact">
        <div class="section__outer">
            <div class="contact__wrapper">
                <div class="section__inner">
                    <div class="contact__row">
                        <div class="contact__column contact__column--address">
                            <div class="contact__inner">
                                <div class="contact__title">
                                    <h2 class="h5 text--inverse">{{ $template_item['contact_title'] }}</h2>
                                </div>
                                <div class="contact__address">
                                    <address>
                                        {!! $template_item['address_1'] !!} {!! $template_item['address_2'] !!}
                                    </address>
                                </div>
                            </div>
                        </div>
                        <div class="contact__column contact__column--map">
                            <div class="map__wrapper">
                                <iframe src="{{ $template_item['map_src'] }}" frameborder="0" style="border:0" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>