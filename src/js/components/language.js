window.language = {
  isOpened: false,
  init: function () {
    jQuery('body').on('click', '.site__language > span', function (e) {
      var container = $(this).closest('.site__language');

      e.preventDefault();
      e.stopImmediatePropagation();

      if (container.hasClass('is--language--opened')) {
        window.language.showSubmenu(container, false);
      } else {
        window.language.showSubmenu(container, true);
      }

    });

    var touch = (window.Modernizr.touchevents) ? "touchstart" : "click";

    $(document).on(touch, function (event) {
      (!$(event.target).is('.site__language') && !$(event.target).is('.site__language > span') && !$(event.target).is('.site__language ul') && !$(event.target).is('.site__language ul a')) && window.language.showSubmenu($('.site__language.is--language--opened'), false)
    });

    window.language.normalScroll();
    window.language.smoothScroll();
  },
  showSubmenu: function (container, show) {

    if (show) {

      if (window.language.isOpened == true) return;

      container.addClass('is--language--opened');
      jQuery('body').addClass('language-is-opened');
      window.language.isOpened = true;

    } else {

      if (window.language.isOpened == false) return;

      container.removeClass('is--language--opened');
      jQuery('body').removeClass('language-is-opened');

      window.language.isOpened = false;
    }

  },
  normalScroll: function () {
    if (!window.simplified.smoothscrollVersion()) {
      window.language.showSubmenu($('.site__language.is--language--opened'), false);
    }
  },
  smoothScroll: function () {
    if (window.simplified.smoothscrollVersion()) {
      window.smoothscrollbar.scrollbar.addListener(function (status) {
        window.language.showSubmenu($('.site__language.is--language--opened'), false);
      });
    }
  },
  moveLanguage: function () {
    if (jQuery('.site__language').length) {

      if (window.tools.globalWW >= 1024) {
        if (jQuery('.site__language').prev().is('.site__nav .site__social')) return;
        jQuery('.site__language').insertAfter('.site__nav .site__social');
      } else {
        if (jQuery('.site__language').prev().is('.site__burger')) return;
        jQuery('.site__language').insertAfter('.site__burger');
      }

    }
  }
};
site.ready.push(function () {
  window.language.init();
})
site.load.push(function () {
  window.language.moveLanguage();
})
site.resize.push(function () {
  window.language.moveLanguage();
})
site.scroll.push(function () {
  window.language.normalScroll();
})