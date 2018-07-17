/* === Application's main css and js vendors file === */
import '../sass/main.scss';
import './bootstrap.js';


/* === Import modules to init when the page initialized === */
import { init as MainInit, pluginCheck as pCheck } from './modules/main';

/* Constant that needed in page initialization */
const WebFont = require('webfontloader');

/* === Initilize function === */
const initApp = () => {
	// Remove web loader
	window.scrollTo(0, 0);
	$('.web-loader').fadeOut(300, () => {
		$('.web-loader').remove();
	});

	/* First, init the modules that requires for functional when page loaded
	* e.g. carousel, masonry, parallax etc. 
	* Since these modules may not be required for every page,
	* you can check if it's the page you want before calling the method */
	MainInit();
	pCheck();

	/* Then, check if there is any window.initPage was declared in the specified page.
		* If there is, run the specific page scripts via window.initPage() */
	if (typeof window.initPage === 'function') {
		window.initPage(window);
	}
};

/* === Load font === */
window.onload = () => {
	WebFont.load({
		google: {
			families: ['Open+Sans:300,400,700']
		},
		active: () => {
			initApp();
		}
	});
};
