window.slickLightbox = {
  init: function () {

    jQuery('.js-gallery-slider').each(function (idx, item) {
      var parent = jQuery(this).closest('.gallery__promotion__wrapper');
      jQuery(this).slick({
        slidesToShow: 1,
        // infinite: false,
        fade: true,
        cssEase: 'linear',
        speed: 500,
        paging: true,
        appendPaging: parent.find('.number'),
        arrows: true,
        appendArrows: parent.find('.paging'),
        prevArrow: '<div class="prev"> <span> <canvas width="32px" height="32px"></canvas> <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Path"> <path d="M0,15.9994995 C0,24.8366128 7.16376307,32 16,32 C24.8372325,32 32,24.8366128 32,15.9994995 C32,7.16438828 24.8372325,0 16,0 C7.16376307,0 0,7.16438828 0,15.9994995 Z" fill="#676C72"></path> <polygon fill="#FFFFFF" points="11 15.999547 17.0339751 10 18.7155153 11.6715742 14.3621744 15.999547 18.7155153 20.3284258 17.0339751 22"></polygon> </g> </g> </svg> </span> </div>',
        nextArrow: '<div class="next"> <span> <canvas width="32px" height="32px"></canvas> <svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g transform="translate(16.000000, 16.000000) scale(-1, 1) translate(-16.000000, -16.000000) " id="Path"> <path d="M0,15.9994995 C0,24.8366128 7.16376307,32 16,32 C24.8372325,32 32,24.8366128 32,15.9994995 C32,7.16438828 24.8372325,0 16,0 C7.16376307,0 0,7.16438828 0,15.9994995 Z" fill="#676C72"></path> <polygon fill="#FFFFFF" points="11 15.999547 17.0339751 10 18.7155153 11.6715742 14.3621744 15.999547 18.7155153 20.3284258 17.0339751 22"></polygon> </g> </g> </svg> </span> </div>'
      }).on('beforeChange', function (event, slick, currentSlide, nextSlide) {
        window.smoothscrollbar.updateElements();
      });
    });

    jQuery('.js-lightbox').each(function (idx, item) {
      var parent = jQuery('.slick-lightbox');
      jQuery(this).slickLightbox({
        itemSelector: '.js-lightbox-link',
        caption: 'caption',
        captionPosition: 'bottom',
        lazy: true,
        imageMaxHeight: 0.8,
        poster: 'poster',
        layouts: {
          closeButton: '<button type="button" class="slick-lightbox-close btn--burger"><div class="btn--burger--inner"><span class="btn--burger--bar"></span><span class="btn--burger--bar"></span></div></button>'
        }
        // slick: {
        //   appendPaging: $('.slick-lightbox-number .number'),
        //   paging: true,
        //   appendArrows: $('.slick-lightbox-number .paging'),
        //   arrows: true,
        //   prevArrow: '<button type="button" class="slider__arrow slider__arrow--prev slick-arrow"><svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g fill="#ffffff"><path d="M19.3461,20.7605 L17.4901,22.6055 L10.8301,15.9825 L17.4901,9.3605 L19.3461,11.2055 L14.5411,15.9825 L19.3461,20.7605 Z M16.0001,0.0005 C7.16386307,0.0005 0.0001,7.16411134 0.0001,16.0005 C0.0001,24.8368887 7.16386307,32.0005 16.0001,32.0005 C24.8373325,32.0005 32.0001,24.8368887 32.0001,16.0005 C32.0001,7.16411134 24.8373325,0.0005 16.0001,0.0005 Z"></path></g></g></svg></button>',
        //   nextArrow: '<button type="button" class="slider__arrow slider__arrow--next slick-arrow"><svg width="32px" height="32px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g fill="#ffffff"><path d="M14.6542,22.606 L12.7982,20.76 L17.6032,15.983 L12.7982,11.206 L14.6542,9.36 L21.3142,15.983 L14.6542,22.606 Z M16.0002,0 C7.16296755,0 0.0002,7.16338724 0.0002,15.9994995 C0.0002,24.8356117 7.16296755,32 16.0002,32 C24.8364369,32 32.0002,24.8356117 32.0002,15.9994995 C32.0002,7.16338724 24.8364369,0 16.0002,0 Z"></path></g></g></svg></button>',
        // }
      }).on({
        'shown.slickLightbox': function () {
          window.globalresize.resizeLightbox();
        }
      });

    })

  }

};

site.ready.push(function () {
  window.slickLightbox.init();
});