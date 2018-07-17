@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $sectionTitle = \App\CMS\Helpers\CMSHelper::getItemOption($pageItem->section_title);
    ?>

    @has($sectionTitle)
        <section class="section__intro">
            <div class="section__outer">
                <div class="intro text--center">
                    <h3 class="h2 text--light text--inverse">@text($sectionTitle)</h3>
                </div>
            </div>
        </section>
    @endhas
@endhas
