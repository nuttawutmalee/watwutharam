@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $content = \App\CMS\Helpers\CMSHelper::getItemOption($content);
    ?>

    @has($content)
        <section class="section__about">
            <div class="section__outer">
                <div class="about__wrapper bg--body--3">
                    <div class="section__content">
                        <div class="entry__content">
                            @unescaped($content)
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endhas
@endhas
