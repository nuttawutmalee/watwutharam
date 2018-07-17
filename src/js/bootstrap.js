import * as axios from 'axios';
import * as _ from 'lodash';
import * as $ from 'jquery';

// !** Don't need to change this but we may be don't need this in the future
window._ = _;
window.$ = window.jQuery = $;

// !** Modernizr and Detectizr
require('imports-loader?this=>window!../vendors/modernizr/modernizr-custom.js');
require('imports-loader?this=>window!../../node_modules/detectizr/dist/detectizr.js');

// !** Your jQuery plugins goes here
window.verge = require('verge');
// window.TweenMax = require('../../node_modules/gsap/src/uncompressed/TweenMax.js');
// window.TimelineMax = require('../../node_modules/gsap/src/uncompressed/TimelineMax.js');
window.Scrollbar = require('../../node_modules/smooth-scrollbar/dist/smooth-scrollbar.js');
require('../vendors/slick-carousel/slick.js');
require('../vendors/slick-lightbox/slick-lightbox.js');

// require('../../node_modules/jquery.easing/jquery.easing.js');
// require('../../node_modules/bezier-easing/dist/bezier-easing.js');
// require('../../node_modules/jquery-mousewheel/jquery.mousewheel.js');


// require('./utils/plugins.js');
require('./base/global.js');
require('./base/base.js');
require('./utils/tools.js');
require('./utils/simplified.js');
require('./components/preloader.js');
require('./components/globalresize.js');
require('./components/smoothscrollbar.js');
require('./components/imageLoad.js');
// require('./components/ggmap.js');
require('./components/hamburger.js');
require('./templates/homeSlider.js');
require('./components/slickLightbox.js');
require('./components/language.js');

// ========= Ignore below code, it's for the Laravel system ==========
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}