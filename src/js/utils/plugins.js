/* Avoid `console` errors in browsers that lack a console
   -------------------------------------------------------------------------- */
    (function() {
        var method;
        var noop = function () {};
        var methods = [
            'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
            'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
            'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
            'timeStamp', 'trace', 'warn'
        ];
        var length = methods.length;
        var console = (window.console = window.console || {});

        while (length--) {
            method = methods[length];

            // Only stub undefined methods.
            if (!console[method]) {
                console[method] = noop;
            }
        }
    }());


/* Reverse a selection
   -------------------------------------------------------------------------- */
    jQuery.fn.reverse = [].reverse;


/* Convert string to Camel Case
   -------------------------------------------------------------------------- */
    String.prototype.toCamel = function(){
        var string = this.replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
        string = string.replace(/(\/.*)/g, '');
        string = string.replace(/(^.){1}/g, function($1){return $1.toUpperCase();});
        return string;
    };


/* Get hash from URL
   -------------------------------------------------------------------------- */
	String.prototype.getHash = function(){
		var string = this.replace(AWBP.host, '').replace(/^\//g, '').replace(/\/$/g, '');

		if (string == '')
			string = 'home';
		return string;
	}


/* Get slug from URL
   -------------------------------------------------------------------------- */
	String.prototype.getSlug = function(){
		var string = this.replace(AWBP.host, '').replace(/^\//g, '').replace(/\/$/g, '');

		if (string == '')
			string = 'home';
		return string;
	}


/* Trim slash from string
   -------------------------------------------------------------------------- */
	String.prototype.trimSlash = function() {
		return this.replace(/^\/+|\/+$/gm,'');
	}


/* Add ending slash from string
   -------------------------------------------------------------------------- */
	String.prototype.addEndSlash = function() {
		if (this.indexOf('?s=') != -1) {
			return this;
		} else {
			var string = this+'/';
			return string.replace(/\/\/+$/gm,'/');
		}
	}


/* Check if image is loaded
   -------------------------------------------------------------------------- */
	function isImageOk(img) {
		_img = img.data('img');
		if (typeof _img == 'undefined') {
			var _img = new Image();
			_img.src = img.attr('src');
			img.data('img', _img);
		}

		if (!_img.complete) {
			return false;
		}

		if (typeof _img.naturalWidth != "undefined" && _img.naturalWidth == 0) {
			return false;
		}

		return true;
	}


/* Images queue loading
   -------------------------------------------------------------------------- */
	var imagesToLoad = null;

	(function( $ ) {
		$.fn.queueLoading = function() {
			var maxLoading = 2;

			var images = $(this);
			if (imagesToLoad == null || imagesToLoad.length == 0)
				imagesToLoad = images;
			else
				imagesToLoad = imagesToLoad.add(images);
			var imagesLoading = null;

			function checkImages() {
				// Get loading images
				imagesLoading = imagesToLoad.filter('.is-loading');

				// Check if loading images are ready or not
				imagesLoading.each(function() {
					var image = $(this);

					if (isImageOk(image)) {
						image.addClass('is-loaded').removeClass('is-loading');
						image.trigger('loaded');
					}
				});

				// Remove loaded images from images to load list
				imagesToLoad = images.not('.is-loaded');

				// Load next images
				loadNextImages();
			}

			function loadNextImages() {
				// Get images not already loading
				imagesLoading = imagesToLoad.filter('.is-loading');
				var nextImages = imagesToLoad.slice(0, maxLoading-imagesLoading.length);

				nextImages.each(function() {
					var image = $(this);
					if (image.hasClass('is-loading'))
						return;

					// Start loading
					image.attr('src', image.attr('data-src'));
					image.addClass('is-loading');
				});

				if (imagesToLoad.length != 0)
					setTimeout(checkImages, 25);
			}

			checkImages();
		};
	}( jQuery ));


/* Open a popup centered in viewport
   -------------------------------------------------------------------------- */
	function popupCenter(url, title, w, h) {
		// Fixes dual-screen position Most browsers Firefox
		var dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : screen.left;
		var dualScreenTop = window.screenTop !== undefined ? window.screenTop : screen.top;

		var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
		var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

		var left = ((width / 2) - (w / 2)) + dualScreenLeft;
		var top = ((height / 3) - (h / 3)) + dualScreenTop;

		var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

		// Puts focus on the newWindow
		if (window.focus)
			newWindow.focus();
	}


/* Get element translateY
   -------------------------------------------------------------------------- */

function getTranslateY(element) {
	// Get style
	var style = window.getComputedStyle(element.get(0));

	// Get matrix
	var matrix = style.getPropertyValue("-webkit-transform") ||
	     style.getPropertyValue("-moz-transform") ||
	     style.getPropertyValue("-ms-transform") ||
	     style.getPropertyValue("-o-transform") ||
	     style.getPropertyValue("transform");

	if(matrix === 'none') {
		matrix = 'matrix(0,0,0,0,0)';
	}
	var values = matrix.match(/([-+]?[\d\.]+)/g);

	return values[14] || values[5] || 0;
}
