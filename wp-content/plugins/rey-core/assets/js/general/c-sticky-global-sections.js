!function(e){"use strict";e(document).one("reycore/init",(function(t,s){!function(t){var s=e(".rey-stickyContent"),n=s.children(".elementor").attr("data-elementor-id"),i=s.is("[data-close]"),o=s.attr("data-close")||"",r=s.is('[data-align="top"]'),a={};if(!(r&&t.elements.$body.hasClass("--prevent-top-sticky")||!r&&t.elements.$body.hasClass("--prevent-bottom-sticky"))&&s.length){if(""!==o){var l=e.reyHelpers.ls.get("top-sticky-"+n);if(i&&!0===l)return}if(s.hasClass("--always-visible"))s.addClass("--visible");else{var c=function(){s.each((function(t,s){var n=e(s),i=n.attr("data-offset")||e(".rey-siteHeader").height()||0,o=n.attr("data-align")||"top";a[o]={},a[o].ob=n,a[o].dirAware=n.is(".--dir-aware"),isNaN(parseInt(i))?e(i).length&&(a[o].offset=e(i).first().offset().top):a[o].offset=parseInt(i)}))};c(),e(window).on("scroll",e.reyHelpers.debounce((function(){if(Object.keys(a).length>0){var t=window.pageYOffset||document.documentElement.scrollTop;e.each(a,(function(e,s){t>s.offset?s.ob.addClass("--visible"):s.ob.removeClass("--visible")}))}}),reyCoreParams.js_params.sticky_debounce)).on("resize",e.reyHelpers.debounce(c,200)).trigger("scroll"),i&&e('<button class="btn rey-stickyContent-close">'+t.addSvgIcon("rey-icon-close")+"</button>").appendTo(s.children(".elementor")).on("click",(function(t){t.preventDefault(),s.removeClass("--visible"),""!==o&&e.reyHelpers.ls.set("top-sticky-"+n,!0,e.reyHelpers.expiration[o])}));var d=function(){e.reyHelpers.is_desktop||e.each(e(".rey-mainNavigation.rey-mainNavigation--mobile",s),(function(t,s){e(s).css("height",window.innerHeight+"px")}))};d(),e(window).on("resize",e.reyHelpers.debounce(d,200))}}}(s)}))}(jQuery);