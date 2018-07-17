window.imageload = {
  onReady: function () {
    // window.imageload.updateResponsiveImg();
    window.imageload.setImagesAsBackground();
    window.imageload.smoothScroll();
  },
  smoothScroll: function () {
    if (window.simplified.smoothscrollVersion()) {
      window.smoothscrollbar.scrollbar.addListener(function (status) {
        window.imageload.setImagesAsBackground();
        // window.imageload.setSlideShow();
      });
    }
  },
  normalScroll: function () {
    if (!window.simplified.smoothscrollVersion()) {
      window.imageload.setImagesAsBackground();
      // window.imageload.setSlideShow();
    }
  },
  setImagesAsBackground: function () {

    jQuery('.js-imageload:not(.is--loaded)').each(function () {

      var image = jQuery(this);
      var container = image.parent();
      var src = image.data('src');

      container.addClass('as--parent');
      if (verge.inViewport(container) && src != undefined) {

        container.css({
          backgroundImage: 'url("' + image.attr('data-src') + '")'
        });

        image.attr('src', src);
        image.hide();
        image.addClass('is--loaded');
        image.one('load', function () {
          container.addClass('is--visible');
          container.addClass('is--loaded');

          setTimeout(function () {
            container.addClass('is--loaded--done');
          }, 1000);
        });
      }
    });

    // SPECIAL
    jQuery('.js-imageload-special:not(.is--loaded)').each(function () {

      var image = jQuery(this);
      var container = image.parent();
      var src = image.data('src');

      container.addClass('as--parent');
      if (verge.inViewport(container, -2) && src != undefined) {

        container.css({
          backgroundImage: 'url("' + image.attr('data-src') + '")'
        });

        image.attr('src', src);
        image.hide();
        image.addClass('is--loaded');
        image.one('load', function () {
          container.addClass('is--visible');
          container.addClass('is--loaded');

          setTimeout(function () {
            container.addClass('is--loaded--done');
          }, 2000);
        });
      }
    });

    // SECTION 
    jQuery('.js-imageload-section-wrapper:not(.is--loaded)').each(function () {
      var section = jQuery(this);
      var image = section.find('.js-imageload-section');
      var container = image.parent();

      container.addClass('as--parent');
      if (verge.inViewport(section)) {

        image.each(function () {
          var self = jQuery(this);
          var src = self.data('src');
          var container = self.parent();

          container.css({
            backgroundImage: 'url("' + self.attr('data-src') + '")'
          });

          self.attr('src', src);
          self.hide();
          section.addClass('is--loaded');
          self.one('load', function () {
            container.addClass('is--visible');
            container.addClass('is--loaded');
            setTimeout(function () {
              container.addClass('is--loaded--done');
            }, 1000);
          });

        });
      }
    });


    jQuery('.section__cases__slider:not(.is--loaded)').each(function () {
      var section = jQuery(this);
      var img = section.find('.as__background');
      var parent = img.parent();
      parent.addClass('as--parent');

      if (verge.inViewport(section)) {
        img.each(function () {
          var self = jQuery(this);
          var src = self.data('src');
          var container = self.parent();

          container.css({
            backgroundImage: 'url("' + self.attr('data-src') + '")'
          });

          self.attr('src', src);
          self.hide();
          section.addClass('is--loaded');
          self.one('load', function () {
            container.addClass('is--visible');
            container.addClass('image--loaded');
            setTimeout(function () {
              container.addClass('image--loaded--done');
            }, 1000);
          });

        });
      }


    });
  },
  updateResponsiveImg: function () {
    jQuery('.section__hero .slide:first-child .js-imageload-slideshows:not(.is--loaded)').each(function () {

      // console.log('slider');

      var image = $(this);
      var container = image.parent();

      if (window.tools.isIpadProPortrait) {
        // ipad pro portrait
        if (container.is('.is--mobile')) {
          // console.log('load ipad pro portrait');
          window.imageload.loadImg(container, image);
        }

      } else if (window.tools.globalWW >= 1024) {
        // desktop
        if (container.is('.is--desktop')) {
          // console.log('load desktop');
          window.imageload.loadImg(container, image);
        }

      } else {
        // mobile
        if (container.is('.is--mobile')) {
          // console.log('load mobile');
          window.imageload.loadImg(container, image);
        }
      }
    });

    $('.js-responsive:not(.is--loaded)').each(function () {
      var image = $(this);
      var container = image.parent();


      if (window.tools.isIpadProPortrait) {
        // ipad pro portrait
        if (container.is('.is--mobile')) {
          // console.log('load ipad pro portrait');
          window.imageload.loadImg(container, image);
        }

      } else if (window.tools.globalWW >= 1024) {
        // desktop
        if (container.is('.is--desktop')) {
          // console.log('load desktop');
          window.imageload.loadImg(container, image);
        }

      } else {
        // mobile
        if (container.is('.is--mobile')) {
          // console.log('load mobile');
          window.imageload.loadImg(container, image);
        }
      }
    });
  },
  loadImg: function ($container, $image) {
    var src = $image.data('src');
    if (src != undefined) {
      // console.log('loadImg');
      $container.css({
        backgroundImage: 'url("' + src + '")'
      });
      $image.attr('src', src);
      $image.hide();
      $image.addClass('is--loaded');
      $image.one('load', function () {
        // $container.addClass('is-visible');
      });
    }
  },
  setSlideShow: function () {


    jQuery('.js-imageload-slideshows:not(.is--loaded)').each(function () {
      var image = $(this);
      var container = image.parent();


      if (window.tools.isIpadProPortrait) {
        // ipad pro portrait
        if (container.is('.is--mobile')) {
          // console.log('load ipad pro portrait');
          window.imageload.loadImg(container, image);
        }

      } else if (window.tools.globalWW >= 1024) {
        // desktop
        if (container.is('.is--desktop')) {
          // console.log('load desktop');
          window.imageload.loadImg(container, image);
        }

      } else {
        // mobile
        if (container.is('.is--mobile')) {
          // console.log('load mobile');
          window.imageload.loadImg(container, image);
        }
      }
    });
  }
};
site.ready.push(function () {
  window.imageload.onReady();
  window.imageload.updateResponsiveImg();
});
site.resize.push(function () {
  window.imageload.updateResponsiveImg();
});
site.scroll.push(function () {
  window.imageload.normalScroll();
});