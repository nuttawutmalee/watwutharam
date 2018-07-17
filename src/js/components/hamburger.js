window.triggerMenu = {
  TRIGGER_SELECTOR: '.site__burger',
  BOOKIG_SELECTOR: '.site__nav',
  ACTIVE_CLASS: 'is--nav--open',
  onReady: function () {
    jQuery(window.triggerMenu.TRIGGER_SELECTOR).on('click', function (e) {
      e.preventDefault();

      var nav = jQuery(window.triggerMenu.BOOKIG_SELECTOR);


      var wait = nav.data('wait');
      if (wait)
        return;
      nav.data('wait', true);


      if (jQuery('body').hasClass(window.triggerMenu.ACTIVE_CLASS)) {

        // close
        jQuery('body').removeClass(window.triggerMenu.ACTIVE_CLASS);

        setTimeout(function () {
          nav.data('wait', false);
        }, 1500);
      } else {

        // open
        jQuery('body').addClass(window.triggerMenu.ACTIVE_CLASS);
        nav.data('wait', false);
      }
    });
  }
}

site.ready.push(function () {
  window.triggerMenu.onReady();
});