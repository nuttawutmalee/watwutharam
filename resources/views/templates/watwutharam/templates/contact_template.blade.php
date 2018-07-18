@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $sectionTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'section_title');
    $address = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'address');
    $map = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem, 'map_src');
    ?>

    @if(isset_not_empty($address) || isset_not_empty($map))
        <section class="section__contact">
            <div class="section__outer">
                <div class="contact__wrapper">
                    <div class="section__inner">
                        <div class="contact__row">
                            <div class="contact__column contact__column--address">
                                <div class="contact__inner">
                                    @has($sectionTitle)
                                        <div class="contact__title">
                                            <h2 class="h5 text--inverse">@text($sectionTitle)</h2>
                                        </div>
                                    @endhas
                                    @has($address)
                                        <div class="contact__address">
                                            <address>@unescaped($address)</address>
                                        </div>
                                    @endhas
                                </div>
                            </div>
                            @has($map)
                                <div class="contact__column contact__column--map">
                                    <div class="map__wrapper">
                                        <iframe src="@text($map)" frameborder="0" style="border:0" allowfullscreen></iframe>
                                    </div>
                                </div>
                            @endhas
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endhas