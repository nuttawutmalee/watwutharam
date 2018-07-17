<style type="text/css">
html,
body{
  margin:0;
  padding:0;
}

*,
::before,
::after{
  -webkit-box-sizing:border-box;
          box-sizing:border-box;
  -webkit-tap-highlight-color:transparent;
  outline:none;
}

html{
  -webkit-tap-highlight-color:rgba(0, 0, 0, 0);
  -ms-text-size-adjust:100%;
  -webkit-text-size-adjust:100%;
}

html:not(.ie) *,
html:not(.ie) ::before,
html:not(.ie) ::after{
  -webkit-backface-visibility:hidden;
          backface-visibility:hidden;
}

html:not(.ie) body.is--first--loading{
  position:fixed;
  overflow:hidden;
  top:0;
  left:0;
  width:100%;
}

html.no-touchevents:not(.ie){
  overflow:hidden;
  height:100%;
}

html.no-touchevents:not(.ie) body{
  overflow:hidden;
  height:100%;
}

html.no-touchevents:not(.ie) .global__container,
html.no-touchevents:not(.ie) .page__container{
  height:100%;
}

html.touchevents.is--ios [data-scrollbar]{
  display:initial;
}

body{
  background-color:#f9f7f4;
}

body{
  color:#483931;
  font-family:Tahoma, Geneva, sans-serif;
  font-weight:normal;
  font-smoothing:antialiased;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

body.overflow--hidden{
  height:100%;
  width:100%;
  overflow:hidden;
}

::-moz-selection{
  color:#ffffff;
  background-color:#a46838;
}

::selection{
  color:#ffffff;
  background-color:#a46838;
}

body.is--first--loading .site__preload{
  opacity:1;
  visibility:visible;
}

body.is--first--loading .global__container{
  opacity:0;
  visibility:hidden;
}

.site__preload{
  display:block;
  position:fixed;
  overflow:hidden;
  top:0;
  bottom:0;
  right:0;
  left:0;
  z-index:16777271;
  font-size:0;
  line-height:0;
  background-color:#f9f7f4;
  opacity:0;
  visibility:hidden;
  -webkit-transform:translateZ(0);
          transform:translateZ(0);
  -webkit-transition:0.55s cubic-bezier(0.165, 0.84, 0.44, 1) 0.5s, visibility 0s 1s;
  transition:0.55s cubic-bezier(0.165, 0.84, 0.44, 1) 0.5s, visibility 0s 1s;
}

.site__preload .loader-intro__logo{
  position:absolute;
  top:50%;
  left:50%;
  -webkit-transform:translate(-50%, -50%);
          transform:translate(-50%, -50%);
  width:200px;
}

.site__preload .loader-intro__logo span{
  position:relative;
  display:block;
}

.site__preload .loader-intro__logo canvas{
  width:100%;
  height:auto;
}

.site__preload .loader-intro__logo svg{
  position:absolute;
  left:0;
  top:0;
  width:100%;
  height:auto;
}

.site__preload .loader-intro__logo #loader__logo__1{
  -webkit-transform:translateY(35px) translateZ(0);
          transform:translateY(35px) translateZ(0);
}

.site__preload .loader-intro__logo #loader__logo__2{
  opacity:0;
  -webkit-transform:translateY(35px) translateZ(0);
          transform:translateY(35px) translateZ(0);
}

.site__preload .loader-intro__logo #loader__logo__3{
  opacity:0;
  -webkit-transform:translateY(35px) translateZ(0);
          transform:translateY(35px) translateZ(0);
}

.global__container{
  opacity:1;
  visibility:visible;
}

body.is--assets--loaded .site__preload .loader-intro__logo #loader__logo__1{
  -webkit-transform:translateY(0) translateZ(0);
          transform:translateY(0) translateZ(0);
  -webkit-transition:-webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1);
  transition:-webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1);
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1);
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1), -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1);
}

body.is--assets--loaded .site__preload .loader-intro__logo #loader__logo__2{
  opacity:1;
  -webkit-transform:translateY(0) translateZ(0);
          transform:translateY(0) translateZ(0);
  -webkit-transition:opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.15s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.15s;
  transition:opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.15s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.15s;
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.15s, opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.15s;
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.15s, opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.15s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.15s;
}

body.is--assets--loaded .site__preload .loader-intro__logo #loader__logo__3{
  opacity:1;
  -webkit-transform:translateY(0) translateZ(0);
          transform:translateY(0) translateZ(0);
  -webkit-transition:opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.25s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.25s;
  transition:opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.25s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.25s;
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.25s, opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.25s;
  transition:transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.25s, opacity 1s cubic-bezier(0.165, 0.84, 0.44, 1) 0.25s, -webkit-transform 1s cubic-bezier(0.23, 1, 0.32, 1) 0.25s;
}
@font-face{
  font-family:'promptlight';
  src:url("templates/watwutaram/assets/fonts/prompt-light-webfont.woff2") format("woff2"), url("templates/watwutaram/assets/fonts/prompt-light-webfont.woff") format("woff"), url("templates/watwutaram/assets/fonts/prompt-light-webfont.ttf") format("truetype");
  font-weight:normal;
  font-style:normal;
}

@font-face{
  font-family:'promptmedium';
  src:url("templates/watwutaram/assets/fonts/prompt-medium-webfont.woff2") format("woff2"), url("templates/watwutaram/assets/fonts/prompt-medium-webfont.woff") format("woff"), url("templates/watwutaram/assets/fonts/prompt-medium-webfont.ttf") format("truetype");
  font-weight:normal;
  font-style:normal;
}

@font-face{
  font-family:'promptregular';
  src:url("templates/watwutaram/assets/fonts/prompt-regular-webfont.woff2") format("woff2"), url("templates/watwutaram/assets/fonts/prompt-regular-webfont.woff") format("woff"), url("templates/watwutaram/assets/fonts/prompt-regular-webfont.ttf") format("truetype");
  font-weight:normal;
  font-style:normal;
}

@font-face{
  font-family:'watwutharam';
  src:url("templates/watwutaram/assets/fonts/watwutharam.eot?djbho9");
  src:url("templates/watwutaram/assets/fonts/watwutharam.eot?djbho9#iefix") format("embedded-opentype"), url("templates/watwutaram/assets/fonts/watwutharam.ttf?djbho9") format("truetype"), url("templates/watwutaram/assets/fonts/watwutharam.woff?djbho9") format("woff"), url("templates/watwutaram/assets/fonts/watwutharam.svg?djbho9#watwutharam") format("svg");
  font-weight:normal;
  font-style:normal;
}

[class^="icon-"],
[class*=" icon-"]{
  font-family:'watwutharam' !important;
  speak:none;
  font-style:normal;
  font-weight:normal;
  font-variant:normal;
  text-transform:none;
  line-height:1;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
}

.icon-behance:before{
  content:"\E900";
}

.icon-facebook:before{
  content:"\E901";
}

.icon-flickr:before{
  content:"\E902";
}

.icon-google-plus:before{
  content:"\E903";
}

.icon-instagram:before{
  content:"\E904";
}

.icon-linkedin:before{
  content:"\E905";
}

.icon-pinterest:before{
  content:"\E906";
}

.icon-skype:before{
  content:"\E907";
}

.icon-twitter:before{
  content:"\E908";
}

.icon-vimeo:before{
  content:"\E909";
}

.icon-youtube:before{
  content:"\E90A";
}

.icon-behance2:before{
  content:"\E90B";
}

.icon-facebook2:before{
  content:"\E90C";
}

.icon-flickr2:before{
  content:"\E90D";
}

.icon-google-plus2:before{
  content:"\E90E";
}

.icon-instagram2:before{
  content:"\E90F";
}

.icon-linkedin2:before{
  content:"\E910";
}

.icon-pinterest2:before{
  content:"\E911";
}

.icon-skype2:before{
  content:"\E912";
}

.icon-twitter2:before{
  content:"\E913";
}

.icon-vimeo2:before{
  content:"\E914";
}

.icon-youtube2:before{
  content:"\E915";
}


</style>