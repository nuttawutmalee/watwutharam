window.globalresize = {
  NAV_SELECTOR: '.site__nav',
  HERO_SELECTOR: '.section__hero:not(.section__404)',
  ERROR_SELECTOR: '.section__404',
  LIGHTOX_SELECTOR: '.slick-lightbox, .slick-lightbox .slick-slide',
  init: function () {

    // NAV_SELECTOR
    if (jQuery(window.globalresize.NAV_SELECTOR).length) {
      if (window.tools.globalWW >= 1024) {
        jQuery(window.globalresize.NAV_SELECTOR).css({
          height: 'auto'
        });
      } else {
        jQuery(window.globalresize.NAV_SELECTOR).css({
          height: window.tools.globalViewportH
        });
      }
    }

    // ERROR 404
    // if (jQuery(window.globalresize.ERROR_SELECTOR).length) {
    //   if (window.tools.globalWW >= 1024) {
    //     jQuery(window.globalresize.ERROR_SELECTOR).css({
    //       height: window.tools.globalViewportH - 100
    //     });
    //   } else {
    //     jQuery(window.globalresize.ERROR_SELECTOR).css({
    //       height: window.tools.globalViewportH
    //     });
    //   }
    // }

  },
  resizeLightbox: function () {
    if (jQuery(window.globalresize.LIGHTOX_SELECTOR).length) {
      jQuery(window.globalresize.LIGHTOX_SELECTOR).css({
        height: window.tools.globalViewportH
      });
    }
  }
};

site.ready.push(function () {
  window.globalresize.init();
  window.globalresize.resizeLightbox();
});
site.resize.push(function () {
  window.globalresize.init();
  window.globalresize.resizeLightbox();
});