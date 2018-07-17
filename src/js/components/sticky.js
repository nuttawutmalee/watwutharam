window.sticky = {
    BOOKING_SELECTOR: '.site__booking',
    BOOKING_HEIGHT: 100,
    BOOKING_SMALL_HEIGHT: 70,
    HERO_SELECTOR: '.section__hero .slides',
    HEADER_SELECTOR: '.site__header',
    SCROLL_TOP: 0,
    WINDOW_BOTTOM: 0,
    fixed_class: 'is--header--fixed',
    init: function () {
        // console.log('sticky: 1.03');
        window.sticky.normalSCroll();
        window.sticky.smoothScroll();
    },
    normalSCroll: function () {
        if (!jQuery(window.sticky.BOOKING_SELECTOR).length) return;
        if (!window.simplified.smoothscrollVersion()) {
            window.sticky.updateNormalScroll();
        }
    },
    smoothScroll: function () {
        if (!jQuery(window.sticky.BOOKING_SELECTOR).length) return;
        if (window.simplified.smoothscrollVersion()) {

            jQuery(window.sticky.BOOKING_SELECTOR).css({
                top: window.tools.globalViewportH - window.sticky.BOOKING_HEIGHT
            });

            window.smoothscrollbar.scrollbar.addListener(function (status) {

                window.sticky.SCROLL_TOP = status.offset.y;
                window.sticky.WINDOW_BOTTOM = window.tools.globalViewportH - window.sticky.BOOKING_HEIGHT;
                window.sticky.WINDOW_BOTTOM_SMALL = window.tools.globalViewportH - window.sticky.BOOKING_SMALL_HEIGHT;

                // console.log(status);

                window.sticky.updateSmoothScroll();


                // if ("down" == status.direction.y) {
                //     if (window.sticky.SCROLL_TOP < window.sticky.WINDOW_BOTTOM) {
                //         jQuery(window.sticky.BOOKING_SELECTOR).css({
                //             top: window.sticky.WINDOW_BOTTOM - window.sticky.SCROLL_TOP
                //         });
                //         jQuery('body').removeClass(window.sticky.fixed_class)
                //     } else {
                //         jQuery(window.sticky.BOOKING_SELECTOR).css({
                //             top: 0
                //         });
                //         if (window.sticky.SCROLL_TOP >= window.tools.globalViewportH) {
                //             jQuery('body').addClass(window.sticky.fixed_class)
                //         }
                //     }
                // } else {
                //     if (window.sticky.SCROLL_TOP < window.sticky.WINDOW_BOTTOM_SMALL) {
                //         jQuery(window.sticky.BOOKING_SELECTOR).css({
                //             top: window.sticky.WINDOW_BOTTOM - window.sticky.SCROLL_TOP
                //         });
                //         jQuery('body').removeClass(window.sticky.fixed_class)
                //     } else {
                //         jQuery(window.sticky.BOOKING_SELECTOR).css({
                //             top: 0
                //         });
                //         if (window.sticky.SCROLL_TOP >= window.tools.globalViewportH) {
                //             jQuery('body').addClass(window.sticky.fixed_class)
                //         }
                //     }
                // }
            });

        }
    },
    updateNormalScroll: function () {
        if (!window.skipScroll) {
            window.sticky.SCROLL_TOP = $(window).scrollTop();
            window.sticky.WINDOW_BOTTOM = window.tools.globalViewportH - window.sticky.BOOKING_HEIGHT;
            window.sticky.WINDOW_BOTTOM_SMALL = window.tools.globalViewportH - window.sticky.BOOKING_SMALL_HEIGHT;

            if (window.tools.globalWW >= 1024) {
                if (window.sticky.SCROLL_TOP < window.sticky.WINDOW_BOTTOM) {
                    $(window.sticky.BOOKING_SELECTOR).css({
                        position: 'absolute',
                        top: 'auto',
                        bottom: '0'

                    });
                    $('body').removeClass(window.sticky.fixed_class)
                } else {
                    $(window.sticky.BOOKING_SELECTOR).css({
                        position: 'fixed',
                        top: '0',
                        bottom: 'auto'
                    });
                    $('body').addClass(window.sticky.fixed_class)
                }
            } else {
                $(window.sticky.BOOKING_SELECTOR).css({
                    position: 'fixed',
                    top: '0',
                    bottom: 'auto'
                });
            }


        }
    },
    updateSmoothScroll: function () {
        if (!window.skipScroll) {

            if (window.sticky.SCROLL_TOP < window.sticky.WINDOW_BOTTOM) {
                jQuery(window.sticky.BOOKING_SELECTOR).css({
                    top: window.sticky.WINDOW_BOTTOM - window.sticky.SCROLL_TOP
                });
                jQuery('body').removeClass(window.sticky.fixed_class)
            } else {
                jQuery(window.sticky.BOOKING_SELECTOR).css({
                    top: 0
                });
                jQuery('body').addClass(window.sticky.fixed_class)
            }
        }
    },
    moveBooking: function () {
        if (!window.simplified.smoothscrollVersion()) {
            if (window.tools.globalWW >= 1024) {
                if (jQuery(window.sticky.BOOKING_SELECTOR).prev().is(window.sticky.HERO_SELECTOR)) return;
                jQuery(window.sticky.BOOKING_SELECTOR).insertAfter(window.sticky.HERO_SELECTOR);
            } else {
                if (jQuery(window.sticky.BOOKING_SELECTOR).prev().is(window.sticky.HEADER_SELECTOR)) return;
                jQuery(window.sticky.BOOKING_SELECTOR).insertAfter(window.sticky.HEADER_SELECTOR);
            }
        }

    }
};
site.ready.push(function () {
    window.sticky.init();
    window.sticky.moveBooking();
});
site.resize.push(function () {
    window.sticky.init();
    window.sticky.moveBooking();
    window.sticky.updateSmoothScroll();
});

// site.scroll.push(function () {
//     window.sticky.normalSCroll();
// });

$(window).on('scroll', window.sticky.normalSCroll);