!function(e){"use strict";var t=function(t){this.init=function(){void 0!==e.reyHelpers&&(this._theme=t,this.elements=this._theme.elements,this.misc(),this.dirAware(),this.doTooltips(),this.passVisibility(),this.events(),e(document).trigger("reycore/init",[this]))},this.events=function(){var t=this;e('.js-scroll-to[data-target^="#"], .js-scroll-to[href^="#"], .js-scroll-to > a[href^="#"], .--scrollto > a[href^="#"]').on("click",(function(r){r.preventDefault();var s=e(this),o=s.attr("data-target")||s.attr("href")||"";if(e(o).length){var i=e(o).offset().top;t.elements.$header.hasClass("header-pos--fixed")&&(i-=t.elements.$header.height()),i-=50,e("html, body").animate({scrollTop:i},{duration:250,easing:"swing"})}})),e(document).on("click","a.js-back-button, .js-back-button a",(function(e){e.preventDefault(),window.history.back()})),e(document).on("click",".rey-postSocialShare a[data-share-props]",(function(t){t.preventDefault();var r=JSON.parse(e(this).attr("data-share-props")||"{}");window.open(e(this).attr("href"),r.name||"",r.size||"width=550,height=550")})),e(window).on("scroll",e.reyHelpers.debounce((function(){t.elements.$body.toggleClass("--at-top",!(window.pageYOffset||document.documentElement.scrollTop)>0)}),200)).trigger("scroll")},this.misc=function(){e.each(e.reyHelpers.params.check_for_empty,(function(t,r){e(r).children().length||e(r).addClass("--empty")}))},this.dirAware=function(){var t=this,r=0;reyCoreParams.js_params.dir_aware&&e(window).on("scroll",e.reyHelpers.debounce((function(){var e=window.pageYOffset||document.documentElement.scrollTop;e>r?t.elements.$body.attr("data-direction","down"):t.elements.$body.attr("data-direction","up"),r=e<=0?0:e}),reyCoreParams.js_params.sticky_debounce)).trigger("scroll")},this.doTooltips=function(){if(e.reyHelpers.is_desktop){var t=e("[data-rey-tooltip]");if(t.length){var r=function(t,r){var s=!0,o=e(this),i=o.attr("data-rey-tooltip"),a=o.attr("title"),n=!1;a&&o.removeAttr("title");try{var c=JSON.parse(i)}catch(e){s=!1}s&&(i=o.hasClass(c.switcher_class)?c.active_text:c.text,n=c.fixed);var l=o,p=1.3,h=e(".rey-icon",o);h.length&&(l=h,p=2),e(r).on("mouseenter",(function(t){if(!e(".rey-tooltip").length){var r=e('<div class="rey-tooltip"></div>').text(i).appendTo("body").fadeIn("fast");s&&c.class&&r.addClass(c.class),n&&r.css({top:l.offset().top-l.height()*p-r.height(),left:o.offset().left+o.width()/2-r.width()/2})}})).on("mousemove",(function(t){if(!n){var r=e(".rey-tooltip"),s=r[0].offsetWidth,o=r[0].offsetHeight,i=e(window).width(),a=e(window).height(),c=s/2,l=o+15;t.pageX>.8*i&&(c=s),t.pageY<.2*a&&(l=-1*o),e(".rey-tooltip").css({top:t.pageY-l,left:t.pageX-c})}})).on("mouseleave",(function(t){e(".rey-tooltip").remove()}))};void 0!==e.fn.imagesLoaded&&this.elements.$body.imagesLoaded((function(){t.each(r)})),e(document).on("rey/wishlist/loaded",(function(e,s){t.each(r)}))}}},this.addSvgIcon=function(t,r,s){var o="#";reyCoreParams.svg_icons_version&&(o="?"+reyCoreParams.svg_icons_version+o);var i=e.reyHelpers.params.icons_path+o+t;return r&&("reycore"===r?i=reyCoreParams.icons_path+o+t:"social"===r&&(i=reyCoreParams.social_icons_path+o+t)),'<svg class="rey-icon '+s+'"><use href="'+(i=e.reyHelpers.applyFilter("reycore/reycore/svg_icon",i,t,r,e.reyHelpers.params.icons_path))+'" xlink:href="'+i+'"></use></svg>'},this.getSvgArrows=function(){var t=e("#tmpl-reyArrowSvg"),r={};if(!t.length)return!1;var s=t.html();return r.prev=s.replaceAll("{{{data.direction}}}","left"),r.next=s.replaceAll("{{{data.direction}}}","right"),r},this.passVisibility=function(){var t=this,r=e('input[type="password"].--suports-visibility, #customer_login .woocommerce-Input[type="password"]');r.length&&r.each((function(r,s){var o=e(s);o.wrap('<span class="__passVisibility-wrapper" />'),e('<span class="__passVisibility-toggle">'+t.addSvgIcon("reycore-icon-eye","reycore")+"</span>").insertAfter(o).on("click",(function(e){o.parent().toggleClass("--text"),o.attr("type",(function(e,t){return"password"==t?"text":"password"}))}))}))},this.init()};e(document).on("reytheme/init",(function(r,s){e.reyCore=new t(s)}))}(jQuery);