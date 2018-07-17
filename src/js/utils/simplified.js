window.simplified = {
  isIE: '',
  IEVersion: '',
  init: function() {
    window.simplified.simplifiedVersion();
    window.simplified.safariVersion();
    window.simplified.smoothscrollVersion();
    window.simplified.detectIE();
    window.simplified.detectIEVersion();
  },
  detectIE: function() {
    if(window.Modernizr.ie) {
      window.simplified.isIE = true;
    }
  },
  detectIEVersion: function() {
    if(window.Modernizr.ie9 || window.Modernizr.ie8) {
      window.simplified.IEVersion = true;
    }
  },
  simplifiedVersion: function() {
    if (window.Modernizr.touchevents || (window.simplified.isIE && window.simplified.IEVersion)) {
      return true;
    } else {
      return false;
    }
  },
  safariVersion: function() {
    if (window.Modernizr.safari || window.Modernizr.touchevents || (window.simplified.isIE && window.simplified.IEVersion)) {
      return true;
    } else {
      return false;
    }
  },
  smoothscrollVersion: function() {
    if (!window.Modernizr.touchevents) {
      if (window.Modernizr.ie) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }
  }
};

site.ready.push(function() {
  window.simplified.smoothscrollVersion();
});