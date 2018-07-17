window.site = {
  ready: [],
  resize: [],
  scroll: [],
  load: [],
  onReady: function(){
    window.site.ready.forEach(function(item,index){
      item();
    });
  },
  onLoad: function(){
    window.site.load.forEach(function(item,index){
      item();
    });
  },
  onResize:function(){
    window.site.resize.forEach(function(item,index){
      item();
    });
  },
  onScroll:function(){
    window.site.scroll.forEach(function(item,index){
      item();
    });
  }
};

jQuery(document).ready(window.site.onReady);

jQuery(window).on('resize orientationchange',function(){
  window.requestAnimFrame(window.site.onResize);
});

jQuery(window).on('scroll',function(){
	window.requestAnimFrame(window.site.onScroll);
});
