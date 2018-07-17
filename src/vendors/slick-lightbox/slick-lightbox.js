'use strict';
(function ($) {
    var SlickLightbox, defaults;
    SlickLightbox = function () {
        /*
  The core class.
   */
        function SlickLightbox(element, options1) {
            var slickLightbox;
            this.options = options1;
            /* Binds the plugin. */
            this.$element = $(element);
            this.didInit = false;
            slickLightbox = this;
            this.$element.on('click.slickLightbox', this.options.itemSelector, function (e) {
                var $clickedItem, $items;
                e.preventDefault();
                $clickedItem = $(this);
                $clickedItem.blur();
                if (typeof slickLightbox.options.shouldOpen === 'function') {
                    if (!slickLightbox.options.shouldOpen(slickLightbox, $clickedItem, e)) {
                        return;
                    }
                }
                $items = slickLightbox.filterOutSlickClones(slickLightbox.$element.find(slickLightbox.options.itemSelector));
                return slickLightbox.init($items.index($clickedItem));
            });
        }
        SlickLightbox.prototype.init = function (index) {
            /* Creates the lightbox, opens it, binds events and calls `slick`. Accepts `index` of the element, that triggered it (so that we know, on which slide to start slick). */
            this.didInit = true;
            this.detectIE();
            this.createModal();
            this.bindEvents();
            this.initSlick(index);
            return this.open();
        };
        SlickLightbox.prototype.createModalItems = function () {
            /* Creates individual slides to be used with slick. If `options.images` array is specified, it uses it's contents, otherwise loops through elements' `options.itemSelector`. */
            var $items, createItem, itemTemplate, lazyPlaceholder, length, links;
            lazyPlaceholder = this.options.lazyPlaceholder || 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            itemTemplate = function (source, caption, lazy) {
                var imgSourceParams;
                if (lazy === true) {
                    imgSourceParams = ' data-lazy="' + source + '" src="' + lazyPlaceholder + '" ';
                } else {
                    imgSourceParams = ' src="' + source + '" ';
                }
                return '<div class="slick-lightbox-slick-item">\n <div class="slick-lightbox-slick-item-outer">\n <div class="slick-lightbox-slick-item-inner"><img class="slick-lightbox-slick-img" ' + imgSourceParams + ' />\n </div>' + caption + '\n  \n</div>\n</div>';
            };
            if (this.options.images) {
                links = $.map(this.options.images, function (img) {
                    return itemTemplate(img, this.options.lazy);
                });
            } else {
                $items = this.filterOutSlickClones(this.$element.find(this.options.itemSelector));
                length = $items.length;
                createItem = function (_this) {
                    return function (el, index) {
                        var caption, info, src, poster;
                        info = {
                            index: index,
                            length: length
                        };
                        caption = _this.getElementCaption(el, info);
                        src = _this.getElementSrc(el);
                        poster = _this.getElementPoster(el);


                        if (_this.detectImage(src)) {
                            // console.log('image');
                        } else if (_this.detectMP4(src)) {
                            return "<div class=\"slick-lightbox-slick-item\"><div class=\"slick-lightbox-slick-item-inner mp4\"><div class=\"slick-lightbox-slick-mp4-wrap\" style=\"background-image: url(" + poster + ")\"><video playsinline loop class=\"slick-lightbox-slick-mp4\" data-src=\"" + src + "\"  type=\"video/mp4\"></video></div>" + caption + "</div></div>";
                        } else {
                            return "<div class=\"slick-lightbox-slick-item\"><div class=\"slick-lightbox-slick-item-inner iframe\"><div class=\"slick-lightbox-slick-iframe-wrap\" style=\"background-image: url(" + poster + ")\"><iframe class=\"slick-lightbox-slick-iframe\" data-src=\"" + src + "\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>" + caption + "</div></div>";
                        }

                        return itemTemplate(src, caption, _this.options.lazy);
                    };
                }(this);
                links = $.map($items, createItem);
            }
            return links;
        };
        SlickLightbox.prototype.createModal = function () {
            /* Creates a `slick`-friendly modal. */
            var html, links, number;
            links = this.createModalItems();
            number = this.createNumbers();
            html = '<div class="slick-lightbox slick-lightbox-hide-init' + (this.isIE ? ' slick-lightbox-ie' : '') + '" style="background: ' + this.options.background + ';">\n  <div class="slick-lightbox-inner">\n    <div class="slick-lightbox-slick slick-caption-' + this.options.captionPosition + '">' + links.join('') + '</div>\n  <div>\n<div>';
            this.$modalElement = $(html);
            this.$parts = {};
            this.$parts['closeButton'] = $(this.options.layouts.closeButton);
            this.$modalElement.find('.slick-lightbox-inner').append(this.$parts['closeButton']);
            // this.$modalElement.find('.slick-lightbox-inner').append('<div class="slick-lightbox-number"><div class="number"><span></span> <span>of</span> <span>' + number + '</span></div><div class="paging"> <button type="button" class="slider__arrow slider__arrow--prev slick-arrow"> <canvas width="32px" height="32px"></canvas> <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g fill="#ffffff"> <path d="M19.3461,20.7605 L17.4901,22.6055 L10.8301,15.9825 L17.4901,9.3605 L19.3461,11.2055 L14.5411,15.9825 L19.3461,20.7605 Z M16.0001,0.0005 C7.16386307,0.0005 0.0001,7.16411134 0.0001,16.0005 C0.0001,24.8368887 7.16386307,32.0005 16.0001,32.0005 C24.8373325,32.0005 32.0001,24.8368887 32.0001,16.0005 C32.0001,7.16411134 24.8373325,0.0005 16.0001,0.0005 Z"></path> </g> </g> </svg> </button> <button type="button" class="slider__arrow slider__arrow--next slick-arrow"> <canvas width="32px" height="32px"></canvas> <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g fill="#ffffff"> <path d="M14.6542,22.606 L12.7982,20.76 L17.6032,15.983 L12.7982,11.206 L14.6542,9.36 L21.3142,15.983 L14.6542,22.606 Z M16.0002,0 C7.16296755,0 0.0002,7.16338724 0.0002,15.9994995 C0.0002,24.8356117 7.16296755,32 16.0002,32 C24.8364369,32 32.0002,24.8356117 32.0002,15.9994995 C32.0002,7.16338724 24.8364369,0 16.0002,0 Z"></path> </g> </g> </svg> </button></div></div>');
            this.$modalElement.find('.slick-lightbox-inner').append('<div class="slick-lightbox-number"><div class="slick-lightbox-number-inner"><div class="number"></div><div class="paging"></div></div></div>');
            return $('body').append(this.$modalElement);
        };
        SlickLightbox.prototype.createNumbers = function () {
            var $items, createItem, itemTemplate, length;
            $items = this.filterOutSlickClones(this.$element.find(this.options.itemSelector));
            length = $items.length;
            return length;
        };
        SlickLightbox.prototype.initSlick = function (index) {
            /* Runs slick by default, using `options.slick` if provided. If `options.slick` is a function, it gets fired instead of us initializing slick. Merges in initialSlide option. */
            var additional;
            additional = {
                initialSlide: index
            };
            if (this.options.lazy) {
                additional.lazyLoad = 'ondemand';
            }
            if (this.options.slick != null) {
                if (typeof this.options.slick === 'function') { 
                    this.slick = this.options.slick(this.$modalElement);
                } else {
                    const extraDom = {
                        appendArrows: this.$modalElement.find('.slick-lightbox-number .paging'),
                        appendPaging: this.$modalElement.find('.slick-lightbox-number .number')
                    }
                    this.slick = this.$modalElement.find('.slick-lightbox-slick').slick($.extend({}, this.options.slick, additional, extraDom));
                }
            } else {
                this.slick = this.$modalElement.find('.slick-lightbox-slick').slick(additional);
            }
            return this.$modalElement.trigger('init.slickLightbox');
        };
        SlickLightbox.prototype.open = function () {
            /* Opens the lightbox. */
            if (this.options.useHistoryApi) {
                this.writeHistory();
            }
            this.$element.trigger('show.slickLightbox');
            setTimeout(function (_this) {
                return function () {
                    return _this.$element.trigger('shown.slickLightbox');
                };
            }(this), this.getTransitionDuration());
            return this.$modalElement.removeClass('slick-lightbox-hide-init');
        };
        SlickLightbox.prototype.close = function () {
            /* Closes the lightbox and destroys it, maintaining the original element bindings. */
            this.$element.trigger('hide.slickLightbox');
            setTimeout(function (_this) {
                return function () {
                    return _this.$element.trigger('hidden.slickLightbox');
                };
            }(this), this.getTransitionDuration());
            this.$modalElement.addClass('slick-lightbox-hide');
            return this.destroy();
        };
        SlickLightbox.prototype.bindEvents = function () {
            /* Binds global events. */
            var resizeSlides;
            resizeSlides = function (_this) {
                return function () {

                    var h;
                    var hh = Math.round(0.8 * verge.viewportH());
                    var ww = hh * (16 / 9);
                    h = _this.$modalElement.find('.slick-lightbox-inner').height();
                    _this.$modalElement.find('.slick-lightbox-slick-item').height(h);
                    return //_this.$modalElement.find('.slick-lightbox-slick-img').css('max-height', Math.round(0.8 * verge.viewportH()) ),
                      _this.$modalElement.find('.slick-lightbox-slick-item-inner.iframe, .slick-lightbox-slick-item-inner.mp4').css('width', ww);


                    //   var h;
                    //   h = _this.$modalElement.find('.slick-lightbox-inner').height();
                    //   _this.$modalElement.find('.slick-lightbox-slick-item').height(h);
                    //   return _this.$modalElement.find('.slick-lightbox-slick-img, .slick-lightbox-slick-item-inner').css('max-height', Math.round(_this.options.imageMaxHeight * h));
                };
            }(this);
            $(window).on('orientationchange.slickLightbox resize.slickLightbox', resizeSlides);
            if (this.options.useHistoryApi) {
                $(window).on('popstate.slickLightbox', function (_this) {
                    return function () {
                        return _this.close();
                    };
                }(this));
            }
            this.$modalElement.on('init.slickLightbox', resizeSlides);
            this.$modalElement.on('destroy.slickLightbox', function (_this) {
                return function () {
                    return _this.destroy();
                };
            }(this));
            this.$element.on('destroy.slickLightbox', function (_this) {
                return function () {
                    return _this.destroy(true);
                };
            }(this));
            this.$parts['closeButton'].on('click.slickLightbox touchstart.slickLightbox', function (_this) {
                return function (e) {
                    e.preventDefault();
                    return _this.close();
                };
            }(this));
            if (this.options.closeOnEscape || this.options.navigateByKeyboard) {
                $(document).on('keydown.slickLightbox', function (_this) {
                    return function (e) {
                        var code;
                        code = e.keyCode ? e.keyCode : e.which;
                        if (_this.options.navigateByKeyboard) {
                            if (code === 37) {
                                _this.slideSlick('left');
                            } else if (code === 39) {
                                _this.slideSlick('right');
                            }
                        }
                        if (_this.options.closeOnEscape) {
                            if (code === 27) {
                                return _this.close();
                            }
                        }
                    };
                }(this));
            }
            if (this.options.closeOnBackdropClick) {
                this.$modalElement.on('click.slickLightbox touchstart.slickLightbox', '.slick-lightbox-slick-img', function (e) {
                    return e.stopPropagation();
                });
                return this.$modalElement.on('click.slickLightbox', '.slick-lightbox-slick-item', function (_this) {
                    return function (e) {
                        e.preventDefault();
                        return _this.close();
                    };
                }(this));
            }
        };
        SlickLightbox.prototype.slideSlick = function (direction) {
            /* Moves the slick prev or next. */
            if (direction === 'left') {
                return this.slick.slick('slickPrev');
            } else {
                return this.slick.slick('slickNext');
            }
        };
        SlickLightbox.prototype.detectIE = function () {
            /* Detects usage of IE8 and lower. */
            var ieversion;
            this.isIE = false;
            if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)) {
                ieversion = new Number(RegExp.$1);
                if (ieversion < 9) {
                    return this.isIE = true;
                }
            }
        };
        SlickLightbox.prototype.getElementCaption = function (el, info) {
            /* Returns caption for each slide based on the type of `options.caption`. */
            var c;
            if (!this.options.caption) {
                return '';
            }
            c = function () {
                switch (typeof this.options.caption) {
                    case 'function':
                        return this.options.caption(el, info);
                    case 'string':
                        return $(el).data(this.options.caption);
                }
            }.call(this);
            return '<span class="slick-lightbox-slick-caption">' + c + '</span>';
        };
        SlickLightbox.prototype.getElementSrc = function (el) {
            /* Returns src for each slide image based on the type of `options.src`. */
            switch (typeof this.options.src) {
                case 'function':
                    return this.options.src(el);
                case 'string':
                    return $(el).attr(this.options.src);
                default:
                    return el.href;
            }
        };
        SlickLightbox.prototype.getElementPoster = function (el) {
            if (!this.options.poster) {
              return '';
            }
            switch (typeof this.options.poster) {
              case 'function':
                return this.options.poster(el);
              case 'string':
                return $(el).data(this.options.poster);
            }
          };
        SlickLightbox.prototype.detectMP4 = function (url) {

            /* Returns true if finds mp4 */
            return url.match(/\.(mp4)$/) !== null;
        };
        SlickLightbox.prototype.detectImage = function (url) {
            /* Returns true if finds image file extension */
            return url.match(/\.(jpeg|jpg|gif|png)$/) !== null;
        };
        SlickLightbox.prototype.unbindEvents = function () {
            /* Unbinds global events. */
            $(window).off('.slickLightbox');
            $(document).off('.slickLightbox');
            return this.$modalElement.off('.slickLightbox');
        };
        SlickLightbox.prototype.destroy = function (unbindAnchors) {
            if (unbindAnchors == null) {
                unbindAnchors = false;
            }
            /* Destroys the lightbox and unbinds global events. If `true` is passed as an argument, unbinds the original element as well. */
            if (this.didInit) {
                this.unbindEvents();
                setTimeout(function (_this) {
                    return function () {
                        return _this.$modalElement.remove();
                    };
                }(this), this.options.destroyTimeout);
            }
            if (unbindAnchors) {
                this.$element.off('.slickLightbox');
                return this.$element.off('.slickLightbox', this.options.itemSelector);
            }
        };
        SlickLightbox.prototype.destroyPrevious = function () {
            /* Destroys lightboxes currently in DOM. */
            return $('body').children('.slick-lightbox').trigger('destroy.slickLightbox');
        };
        SlickLightbox.prototype.getTransitionDuration = function () {
            /* Detects the transition duration to know when to remove stuff from DOM etc. */
            var duration;
            if (this.transitionDuration) {
                return this.transitionDuration;
            }
            duration = this.$modalElement.css('transition-duration');
            if (typeof duration === 'undefined') {
                return this.transitionDuration = 500;
            } else {
                return this.transitionDuration = duration.indexOf('ms') > -1 ? parseFloat(duration) : parseFloat(duration) * 1000;
            }
        };
        SlickLightbox.prototype.writeHistory = function () {
            /* Writes an empty state to the history API if supported. */
            return typeof history !== 'undefined' && history !== null ? typeof history.pushState === 'function' ? history.pushState(null, null, '') : void 0 : void 0;
        };
        SlickLightbox.prototype.filterOutSlickClones = function ($items) {
            /* Removes all slick clones from the set of elements. Only does so, if the target element is a slick slider. */
            if (!this.$element.hasClass('slick-slider')) {
                return $items;
            }
            return $items = $items.filter(function () {
                var $item;
                $item = $(this);
                return !$item.hasClass('slick-cloned') && $item.parents('.slick-cloned').length === 0;
            });
        };
        return SlickLightbox;
    }();
    defaults = {
        background: 'rgba(0,0,0,.8)',
        closeOnEscape: true,
        closeOnBackdropClick: true,
        destroyTimeout: 500,
        itemSelector: 'a',
        navigateByKeyboard: true,
        src: false,
        caption: false,
        captionPosition: 'dynamic',
        images: false,
        poster: false,
        slick: {},
        useHistoryApi: false,
        layouts: {
            closeButton: '<button type="button" class="slick-lightbox-close"></button>'
        },
        shouldOpen: null,
        imageMaxHeight: 0.9,
        lazy: false
    };
    $.fn.slickLightbox = function (options) {
        /* Fires the plugin. */
        options = $.extend({}, defaults, options);
        $(this).each(function () {
            return this.slickLightbox = new SlickLightbox(this, options);
        });
        return this;
    };
    $.fn.unslickLightbox = function () {
        /* Removes everything. */
        return $(this).trigger('destroy.slickLightbox').each(function () {
            return this.slickLightbox = null;
        });
    };
}(jQuery));