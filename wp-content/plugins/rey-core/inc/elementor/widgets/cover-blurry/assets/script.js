!function(e,n,o){"use strict";var a=function(e,n){var o=n(".rey-coverBlurry",e),a=n(".cBlurry-wrapper",e),t=n(".cBlurry-slide",a);if(!a.hasClass("is-active")){var r=JSON.parse(o.attr("data-slider-settings")||"{}"),i={type:"fade",rewind:!0,perPage:1,autoplay:r.autoplay,interval:parseInt(r.autoplaySpeed),gap:0,arrows:!1,pagination:!1,speed:600,pauseOnHover:r.pauseOnHover,pauseOnFocus:r.pauseOnHover};a.on("rey/splide",(function(e,a){var r=n.reyVideosHelper({containers:t});r.init();var i=function(e,o){var a=n("video",t.eq(e));a.length&&a[0][o]()};a.on("mounted",(function(){o.removeClass("--loading").addClass("--init"),i(0,"play"),r.changeState(0,"play")})),a.on("move",(function(e){i(e,"pause"),n('.rey-youtubeVideo[data-player-state="1"]',t.eq(e)).length&&r.changeState(e,"pause")})),a.on("moved",(function(e){i(e,"play"),r.changeState(e,"play")}))}));var d=function(){n.reySplide({element:a[0],config:i,delay:r.delayInit,customArrows:r.customArrows,customPagination:r.customPagination})};void 0!==n.fn.imagesLoaded?o.imagesLoaded(d):d()}};e(document).on("rey/site_loaded",(function(){e.reyHelpers.$sitePreloader.length>0&&e(".elementor-widget-reycore-cover-blurry").each((function(n,o){new a(e(o),e)}))})),e(window).on("elementor/frontend/init elementor/frontend/ajax",(function(){n.hooks.addAction("frontend/element_ready/reycore-cover-blurry.default",(function(e,n){new a(e,n)}))}))}(jQuery,window.elementorFrontend,window.elementorModules);