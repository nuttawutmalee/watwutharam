"use strict";

window.requestAnimFrame = (function () {
  return  window.requestAnimationFrame ||
  window.webkitRequestAnimationFrame ||
  window.mozRequestAnimationFrame ||
  window.oRequestAnimationFrame ||
  window.msRequestAnimationFrame ||
  function (callback) {
    window.setTimeout(callback, 1000 / 60);
  };
})();

var B_EVENT = {
  ANIMATION_END: 'animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd',
  TRANSITION_END: 'transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd'
}

var B_ = {
  GET_NUMBER:function(data,_default){
    if(isNaN(data) || (!data && typeof data == "string")){
      return _default;
    }
    return data;
  }
}
