!function(e,n,i){"use strict";var t,o=function(e,n){var i=n(".rey-coverSideSlide",e),t=n(".cSslide-sliderWrapper",e),o=n(".splide",e),s=n(".cSslide-slider",e),r=n(".cSslide-slide",s),a=n(".cSslide-caption",i),l=n(".cSlide-logoInner",i);if(!o.hasClass("--init")){var d=JSON.parse(i.attr("data-slider-settings")||"{}"),c={type:d.type,rewind:!0,perPage:1,autoplay:d.autoplay,interval:parseInt(d.interval),gap:0,speed:700,arrows:!1,pagination:!1},m=d.intro,f=i.children(".cSslide-effectBg--1"),u=i.children(".cSslide-effectBg--2"),p=(o.children(".cSslide-effectBg-slide--1"),o.children(".cSslide-effectBg-slide--2"));o.on("rey/splide",(function(e,t){var s=n.reyVideosHelper({containers:r});s.init(),f.remove(),u.remove(),i.removeClass("--animate-intro"),t.on("mounted",(function(){i.addClass("--init"),s.changeState(0,"play")}));var l=function(e){a.length>1&&a.eq(e).addClass("--active")};l(0),t.on("move",(function(e){a.removeClass("--active"),"curtains"==d.effect&&o.addClass("--animate-curtain"),s.changeState(e,"pause")})).on("moved",(function(e){"curtains"==d.effect&&p.on("animationend webkitAnimationEnd",(function(){o.removeClass("--animate-curtain")})),setTimeout((function(){l(e)}),n.reyHelpers.is_desktop?400:0),s.changeState(e,"play")}))})),a.each((function(){n(".cSslide-captionEl",n(this)).each((function(e,i){var t=n(i),o=t.css("margin-bottom");t.is(":visible")&&t.css({"transition-delay":.1*e+"s","margin-bottom":0}).wrap('<div class="cSslide-captionWrapper" style="margin-bottom:'+o+'"/>')}))}));var v=function(){n("body").addClass("--cSslide-active")};i.imagesLoaded((function(){if(i.removeClass("--loading"),m&&i.addClass("--animate-intro"),l.addClass("--shown"),n.reyHelpers.is_desktop&&i.closest(".elementor-hidden-desktop").length)v();else if(n.reyHelpers.is_tablet&&i.closest(".elementor-hidden-tablet").length)v();else if(n.reyHelpers.is_mobile&&i.closest(".elementor-hidden-mobile").length)v();else{var e=function(){v(),d.vertical&&r.height(s.outerHeight());var e=!1,i=function(i){e||(e=!0,n.reySplide({element:o[0],config:c,customArrows:d.customArrows}),setTimeout((function(){l.removeClass("--shown")}),1e3))};n.reyHelpers.is_mobile?i():m?t.one("animationend",i):i()};n.reyHelpers.is_mobile?e():m?u.on("transitionend",e):e()}})),n(window).on("resize",n.reyHelpers.debounce((function(){d.vertical&&r.height(s.outerHeight())}),50))}};e(document).on("rey/site_loaded",(function(){!t&&e.reyHelpers.$sitePreloader.length&&e(".elementor-widget-reycore-cover-sideslide").each((function(n,i){new o(e(i),e)}))})),e(window).on("elementor/frontend/init elementor/frontend/ajax",(function(){n.hooks.addAction("frontend/element_ready/reycore-cover-sideslide.default",(function(e,n){new o(e,n),t=!0}))}))}(jQuery,window.elementorFrontend,window.elementorModules);