window.smoothscrollbar = {
  scrollbar: '',
  windowHeight: '',
  windowMiddle: '',
  scrollbarLimit: '',
  elements: '',
  selector: '.js-parallax',
  windowScrollTop: 0,
  init: function() {
    if ( window.simplified.smoothscrollVersion() ) {
      window.smoothscrollbar.scrollbar = Scrollbar.init(jQuery('[data-scrollbar]')[0], {
        syncCallbacks: !0
      });
      window.smoothscrollbar.windowHeight = window.tools.globalViewportH;
      window.smoothscrollbar.windowMiddle = window.smoothscrollbar.windowHeight / 2;
      window.smoothscrollbar.scrollbarLimit = window.smoothscrollbar.scrollbar.limit.y + window.smoothscrollbar.windowHeight;
      window.smoothscrollbar.elements = {};

      window.smoothscrollbar.addElements();
      window.smoothscrollbar.checkElements(true);

      window.smoothscrollbar.scrollbar.addListener(function () {
        return window.smoothscrollbar.checkElements();
      });
    } else {
      window.smoothscrollbar.windowHeight = window.tools.globalViewportH;
      window.smoothscrollbar.windowMiddle = window.smoothscrollbar.windowHeight / 2;
      window.smoothscrollbar.scrollbarLimit = window.smoothscrollbar.windowScrollTop + window.smoothscrollbar.windowHeight;
      window.smoothscrollbar.elements = {};

      window.smoothscrollbar.addElements();
      window.smoothscrollbar.checkElements(true);

      // window.smoothscrollbar.scrollbar.addListener(function () {
      //   return window.smoothscrollbar.checkElements();
      // });
    }
  },
  addElements: function() {
    var _this3 = this;

    $(window.smoothscrollbar.selector).each(function (i, el) {
      var $element = $(el);
      var elementSpeed = $element.data('speed') / 10;
      var elementPosition = $element.data('position');
      var elementTarget = $element.data('target');
      var elementHorizontal = $element.data('horizontal');
      var $target = elementTarget ? $(elementTarget) : $element;

      if ( window.simplified.smoothscrollVersion() ) {
        var elementOffset = $target.offset().top + _this3.scrollbar.scrollTop;
      } else {
        var elementOffset = $target.offset().top + _this3.windowScrollTop;
      }


      var elementPersist = $element.data('persist');

      if (!elementTarget && $element.data('transform')) {
        var transform = $element.data('transform');
        elementOffset -= parseFloat(transform.y);
      }

      var elementLimit = elementOffset + $target.outerHeight();
      var elementMiddle = (elementLimit - elementOffset) / 2 + elementOffset;

      _this3.elements[i] = {
        $element: $element,
        offset: elementOffset,
        limit: elementLimit,
        middle: elementMiddle,
        speed: elementSpeed,
        position: elementPosition,
        horizontal: elementHorizontal,
        persist: elementPersist
      };
    });
  },
  checkElements: function(first) {
    if ( window.simplified.smoothscrollVersion() ) {
      var scrollbarTop = window.smoothscrollbar.scrollbar.scrollTop;
    } else {
      var scrollbarTop = window.smoothscrollbar.windowScrollTop;
    }

    var scrollbarLimit = window.smoothscrollbar.scrollbarLimit;
    var scrollbarBottom = scrollbarTop + window.smoothscrollbar.windowHeight;
    var scrollbarMiddle = scrollbarTop + window.smoothscrollbar.windowMiddle;

    for (var i in window.smoothscrollbar.elements) {
      var transformDistance = void 0;
      var scrollBottom = scrollbarBottom;
      var $element = window.smoothscrollbar.elements[i].$element;
      var elementOffset = window.smoothscrollbar.elements[i].offset;
      // elementOffset = $element.offset().top;
      var elementLimit = window.smoothscrollbar.elements[i].limit;
      var elementMiddle = window.smoothscrollbar.elements[i].middle;
      var elementSpeed = window.smoothscrollbar.elements[i].speed;
      var elementPosition = window.smoothscrollbar.elements[i].position;
      var elementHorizontal = window.smoothscrollbar.elements[i].horizontal;
      var elementPersist = window.smoothscrollbar.elements[i].persist;

      if (elementPosition === 'top') {
        scrollBottom = scrollbarTop;
      }

      // Define if the element is inview
      var inview = scrollBottom >= elementOffset && scrollbarTop <= elementLimit;

      // Add class if inview, remove if not
      if (inview) {
        $element.addClass('is-inview');

        if (elementPersist != undefined) {
          $element.addClass('is-show');
        }
      } else {
        $element.removeClass('is-inview');
      }

      if (first && !inview && elementSpeed) {
        // Different calculations if it is the first call and the
        // item is not in the view
        if (elementPosition !== 'top') {
          transformDistance = (elementOffset - window.smoothscrollbar.windowMiddle - elementMiddle) * -elementSpeed;
        }
      }

      // If element is in view
      if (inview && elementSpeed) {
        switch (elementPosition) {
          case 'top':
          transformDistance = (scrollbarTop - elementOffset) * -elementSpeed;
          break;

          case 'bottom':
          transformDistance = (scrollbarLimit - scrollBottom) * elementSpeed;
          break;

          default:
          transformDistance = (scrollbarMiddle - elementMiddle) * -elementSpeed;
          break;
        }
      }

      if (transformDistance) {
        // Transform horizontal OR vertical.
        // Default to vertical.
        elementHorizontal ? window.smoothscrollbar.transform($element, transformDistance + 'px') : window.smoothscrollbar.transform($element, 0, transformDistance + 'px');
      }
    }
  },
  transform: function($element, x, y, z) {
    // Defaults
    x = x || 0;
    y = y || 0;
    z = z || 0;

    // Translate
    $element.css({
      '-webkit-transform': 'translate3d(' + x + ', ' + y + ', ' + z + ')',
      '-ms-transform': 'translate3d(' + x + ', ' + y + ', ' + z + ')',
      'transform': 'translate3d(' + x + ', ' + y + ', ' + z + ')'
    }).data('transform', {
      x: x,
      y: y,
      z: z
    }); // Remember

    $element.find(window.smoothscrollbar.selector).each(function (i, e) {
      var $this = $(e);
      if (!$this.data('transform')) {
        $this.data('transform', {
          x: x,
          y: y,
          z: z
        });
      }
    });
  },
  updateElements: function() {
    if ( window.simplified.smoothscrollVersion() ) {
      window.smoothscrollbar.scrollbar.update(true);
      // Reset container and scrollbar data.
      window.smoothscrollbar.windowHeight = window.tools.globalViewportH;
      window.smoothscrollbar.windowMiddle = window.smoothscrollbar.windowHeight / 2;
      window.smoothscrollbar.scrollbarLimit = window.smoothscrollbar.scrollbar.limit.y + window.smoothscrollbar.windowHeight;
      window.smoothscrollbar.addElements();
      window.smoothscrollbar.checkElements(true);
    } else {
      // window.smoothscrollbar.scrollbar.update(true);
      // Reset container and scrollbar data.
      window.smoothscrollbar.windowHeight = window.tools.globalViewportH;
      window.smoothscrollbar.windowMiddle = window.smoothscrollbar.windowHeight / 2;
      window.smoothscrollbar.scrollbarLimit = window.smoothscrollbar.windowScrollTop + window.smoothscrollbar.windowHeight;
      window.smoothscrollbar.addElements();
      window.smoothscrollbar.checkElements(true);
    }
  },
  onScroll: function() {
    if ( !window.simplified.smoothscrollVersion() ) {
      window.smoothscrollbar.windowScrollTop = jQuery(document).scrollTop();
      window.smoothscrollbar.checkElements();
    }
  }
};

site.ready.push(function() {
  window.smoothscrollbar.init();
});

site.resize.push(function() {
  window.smoothscrollbar.updateElements();
});

site.scroll.push(function() {
  window.smoothscrollbar.onScroll();
});
