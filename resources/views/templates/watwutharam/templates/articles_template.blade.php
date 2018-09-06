@has($pageItem)
    <?php
    $pageItem = isset_not_empty($pageItem);
    $currentPage = app('request')->input('p', 1);
    $totalPages = 0;

    // Articles
    $articles = \App\CMS\Helpers\CMSHelper::getCurrentChildrenPages(null, null, true, null, [], ['article_metadata']);
	
    if (count($articles) > 0) {
	$articles = collect($articles)
		->filter(function ($article) {
			return \App\CMS\Helpers\CMSHelper::getPageItemByVariableName('article_metadata', $article);
		})
		->all();
        $totalPages = ceil(count($articles) / 6);
    }

    $articles = collect($articles)->slice(($currentPage - 1) * 6, 6)->all();
    ?>

    @has($articles)
        <section class="section__articles">
            <div class="section__outer">
                <div class="section__inner">
                    <div class="lists">
                        @foreach($articles as $index => $article)
                            <?php
                            $metadata = \App\CMS\Helpers\CMSHelper::getPageItemByVariableName('article_metadata', $article);
                            $link = isset_not_empty($article->friendly_url);
                            $title = isset_not_empty($metadata->title);
                            $image = isset_not_empty($metadata->image);
                            $imageAlt = isset_not_empty($metadata->image_alt);
                            $description = isset_not_empty($metadata->description);
                            $buttonLinkTitle = isset_not_empty($metadata->button_link_title);
                            ?>
                            <div class="list">
                                <div class="articles__item">
                                    <div class="articles__top">
                                        <div class="articles__title">
                                            <h3 class="h5 text--inverse">@text($title)</h3>
                                        </div>
                                    </div>
                                    <div class="articles__image bg__wrapper">
                                        <div class="bg__container">
                                            <img data-src="{{ \App\CMS\Helpers\CMSHelper::thumbnail($image) }}"
                                                alt="@text($imageAlt)"
                                                class="js-imageload">
                                        </div>
                                        <div class="gradient-hover"></div>
                                        <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}" class="btn--link"></a>
                                    </div>
                                    <div class="articles__content">
                                        <div class="articles__desc">@unescaped($description)</div>
                                        <div class="articles__button">
                                            <a href="{{ \App\CMS\Helpers\CMSHelper::url($link) }}"
                                                title="@text($buttonTitle)" class="btn--readmore">
                                                @text($buttonLinkTitle)
                                                <i></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @include(\App\CMS\Helpers\CMSHelper::getTemplatePath('partials.pagination'), [
                        'totalPages' => $totalPages,
                        'currentPage' => $currentPage
                    ])
                </div>
            </div>
        </section>
    @endhas
@endhas
