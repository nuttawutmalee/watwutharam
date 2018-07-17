window.heroSlider = {
    SELECTOR: '.section__hero .slides',
    init: function () {
        window.heroSlider.setupScaleEffect();
        window.heroSlider.setupSlick();
    },
    setupSlick: function () {
        jQuery(window.heroSlider.SELECTOR).slick({
            autoplay: true,
            autoplaySpeed: 4000,
            infinite: true,
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
            speed: 1000,
            arrows: false,
            // appendArrows: $('.paging'),
            // prevArrow: '<button type="button" class="slider__arrow slider__arrow--prev slick-arrow"></button>',
            // nextArrow: '<button type="button" class="slider__arrow slider__arrow--next slick-arrow"></button>',
            dots: true
        }).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
            // window.imageload.setSlideShow();
            // window.smoothscrollbar.updateElements();
        });
    },
    setupScaleEffect: function () {
        jQuery(window.heroSlider.SELECTOR).length <= 0 || (jQuery(window.heroSlider.SELECTOR).on("init", function (t, n) {
                jQuery(n.$slides[0]).find(".slider__slide-background").css({
                    transform: "scale(1)"
                }).end().find(".slider__video").css({
                    transform: "translateX(-50%) translateY(-50%) scale(1)"
                })
            }),
            jQuery(window.heroSlider.SELECTOR).on("beforeChange", function (t, n, i, s) {
                jQuery(n.$slides[i]).find(".slider__slide-background").css({
                        transform: "scale(1.03)"
                    }).end().find(".slider__video").css({
                        transform: "translateX(-50%) translateY(-50%) scale(1.03)"
                    }),
                    jQuery(n.$slides[s]).find(".slider__slide-background, .slider__video").css({
                        transform: "scale(1)"
                    }).end().find(".slider__video").css({
                        transform: "translateX(-50%) translateY(-50%) scale(1)"
                    })
            }))
    }
};

site.ready.push(function () {
    window.heroSlider.init();
});


window.articlesSlider = {
    SELECTOR: '.section__articles__lists .lists',
    init: function () {
        window.articlesSlider.setupSlick();
    },
    setupSlick: function () {
        // var parent = jQuery(window.articlesSlider.SELECTOR).closest('.articles__slides');
        jQuery(window.articlesSlider.SELECTOR).slick({
            autoplay: true,
            autoplaySpeed: 4000,
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            // fade: true,
            speed: 1000,
            arrows: true,
            appendArrows: $('.articles__arrows'),
            prevArrow: '<button type="button" class="slider__arrow slider__arrow--prev slick-arrow"></button>',
            nextArrow: '<button type="button" class="slider__arrow slider__arrow--next slick-arrow"></button>',
            dots: false,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false
                    }
                }, {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false
                    }
                }
            ]
        });
    }
};

site.ready.push(function () {
    window.articlesSlider.init();
});

window.homeGallerySlider = {
    SELECTOR: '.section__gallery__lists .lists',
    init: function () {
        window.homeGallerySlider.setupSlick();
    },
    setupSlick: function () {
        var parent = jQuery(window.homeGallerySlider.SELECTOR).closest('.gallery__slides');
        jQuery(window.homeGallerySlider.SELECTOR).slick({
            autoplay: true,
            autoplaySpeed: 4000,
            infinite: true,
            slidesToShow: 4,
            slidesToScroll: 1,
            speed: 1000,
            arrows: true,
            appendArrows: $('.gallery__arrows'),
            prevArrow: '<button type="button" class="slider__arrow slider__arrow--prev slick-arrow"></button>',
            nextArrow: '<button type="button" class="slider__arrow slider__arrow--next slick-arrow"></button>',
            dots: false,
            responsive: [{
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: false
                    }
                }
            ]
        });
    }
};

site.ready.push(function () {
    window.homeGallerySlider.init();
});

window.newsSlider = {
    SELECTOR: '.section__news__lists .lists',
    SETUP: '',
    init: function () {
        jQuery(window.newsSlider.SELECTOR).each(function () {
            var _this = jQuery(this);
            if (window.tools.globalWW >= 1200) {
                if (_this.hasClass('slick-initialized')) {
                    window.newsSlider.destroySlick();
                    console.log('destroy slide');
                    return
                }
            } else {
                if (!_this.hasClass('slick-initialized')) {
                    window.newsSlider.setupSlick();
                    console.log('init slide');
                    return
                }
            }
        });


    },
    setupSlick: function () {
        window.newsSlider.SETUP = {
            autoplay: true,
            autoplaySpeed: 4000,
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            speed: 1000,
            arrows: false,
            dots: true,
            responsive: [{
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        dots: true
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        dots: true
                    }
                }
            ]
        };
        jQuery(window.newsSlider.SELECTOR).slick(window.newsSlider.SETUP);
    },
    destroySlick: function () {
        jQuery(window.newsSlider.SELECTOR).slick('unslick');
    }
};

site.ready.push(function () {
    window.newsSlider.init();
});
site.resize.push(function () {
    window.newsSlider.init();
});