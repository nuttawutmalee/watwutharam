window.preloader = {
  containerSelector: '[data-loader]',
  percentSelector: '[data-loader-percent]',
  $container: '',
  $percent: '',
  paths: '',
  imgs: '',
  imgsLoaded: '',
  isLoaded: false,
  loadSuccess: false,
  init: function () {

    window.preloader.startPreload();

    // assign variables
    window.preloader.$container = jQuery(window.preloader.containerSelector);
    window.preloader.$percent = window.preloader.$container.find(window.preloader.percentSelector);
    window.preloader.imgs = jQuery();
    window.preloader.imgsLoaded = 0;

    // collect img
    window.preloader.paths = [];

    jQuery('*').each(function () {
      // bg img
      var bgPath = jQuery(this).css('background-image');
      if (bgPath != 'none' && bgPath.indexOf('linear-gradient') == -1) {
        var path = jQuery(this).css('background-image');
        path = path.replace('url(', '').replace(')', '').replace(/\"/gi, "");
        window.preloader.addImg(path);
      }

      // img tag
      if (jQuery(this).is('img[src]')) {
        window.preloader.addImg(jQuery(this).attr('src'));
      }
    });

    // add loaded event
    window.preloader.paths.forEach(function (item, i) {
      window.preloader.imgs = window.preloader.imgs.add(jQuery('<img src="' + item + '"/>'));
    });

    // start load
    if (window.preloader.imgs.length) {
      window.preloader.onProgress();
      window.preloader.imgs.on('load', window.preloader.onImgLoaded);
      window.preloader.imgs.on('error', window.preloader.onError);

      // Animation Loading

    } else {
      window.preloader.onLoadComplete();
    }
  },
  addImg: function (path) {
    if (path.indexOf('.svg') == -1 && path != '') {
      window.preloader.paths.push(path);
    }
  },
  onError: function (e) {
    window.preloader.imgsLoaded++;
    window.preloader.onProgress();
  },
  onImgLoaded: function (e) {
    window.preloader.imgsLoaded++;
    window.preloader.onProgress();
  },
  onProgress: function (e) {
    var currentPercent = parseInt((window.preloader.imgsLoaded / window.preloader.imgs.length) * 100);

    if (window.preloader.$percent.length) window.preloader.$percent.html(currentPercent);

    if (currentPercent >= 100) {
      window.preloader.onLoadComplete();
    }
  },
  onLoadComplete: function () {
    window.preloader.isLoaded = true;
    window.preloader.imgs.off('load error');
    window.preloader.$percent.html(100);
    window.preloader.endPreload();
  },
  startPreload: function () {
    // console.log('//--- Start Preload ---//');

    if (typeof (Storage) !== "undefined") {
      if (sessionStorage.load) {
        // if(false){
        sessionStorage.load = Number(sessionStorage.load) + 1;
        window.preloader.preloadIn();
        return
      } else {
        sessionStorage.load = 1;
        window.preloader.preloadInFirstTime();
        window.preloader.loadSuccess = true;
      }
    }
  },
  preloadIn: function () {
    // console.log('preloadIn');
  },
  preloadOut: function () {
    // console.log('preloadOut');
  },
  preloadInFirstTime: function () {
    // console.log('preloadInFirstTime');
  },
  preloadOutFirstTime: function () {
    // console.log('preloadOutFirstTime');
  },
  endPreload: function () {
    // console.log('endPreload');

    jQuery('body').addClass('is--assets--loaded');

    setTimeout(function () {
      jQuery('body').removeClass('is--first--loading');

      // console.log('//--- Start Onload ---//');
      window.site.onLoad();
    }, 500);

    // jQuery('body').removeClass('is--loading');
    // setTimeout(function () {
    //   // jQuery('body').addClass('is--loaded');

    // }, 2000);
  }
};

site.ready.push(function () {
  window.preloader.init();
});