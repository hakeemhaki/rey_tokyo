!function(e){"use strict";var t=function(e,t){var o=t(".reyEl-productGrid",e),a=t("> .splide, .reyajfilter-before-products > .splide",o);if(a.length){var r=function(e){var o={};return t.isEmptyObject(e)||t.each(["top","right","bottom","left"],(function(t,a){e[a]&&(o[a]="px"!==e.unit?e[a]+e.unit:parseInt(e[a]))})),o},d=JSON.parse(o.attr("data-carousel-settings")||"{}"),i=elementorFrontend.config.breakpoints,n=t(".splide__slide",a),s={type:d.infinite?"loop":d.type,rewind:!0,perPage:parseInt(d.slides_to_show)||2,autoplay:d.autoplay,interval:parseInt(d.autoplaySpeed),gap:parseInt(d.gap),speed:parseInt(d.speed),arrows:!1,pagination:!1,breakpoints:{},padding:r(d.carousel_padding),autoWidth:!0};if(e.hasClass("--offset-on")&&t.reyHelpers.is_desktop){var l=function(){var o=t(window).width()-(e.offset().left+e.outerWidth()),a={left:o,right:o};return e.hasClass("--offset-on-left")?a.right=0:e.hasClass("--offset-on-right")&&(a.left=0),t.reyHelpers.setProperty("--stretch",o+"px",t(".splide",e)[0]),a};a.on("rey/splide",(function(e,o){t(window).on("resize",t.reyHelpers.debounce((function(){o.options.padding=l()}),500))})),s.padding=l(),s.type="loop",d.delayInit||(d.delayInit=1e3)}(e.hasClass("--disable-desktop")||n.length<=s.perPage)&&(s.breakpoints[2560]={destroy:!0}),s.breakpoints[i.lg]={perPage:parseInt(d.slides_to_show_tablet)||2,gap:parseInt(d.gap_tablet)||parseInt(d.gap),padding:r(d.carousel_padding_tablet)},(e.hasClass("--disable-tablet")||n.length<=s.breakpoints[i.lg].perPage)&&(s.breakpoints[i.lg]={destroy:!0}),s.breakpoints[i.md]={type:d.infinite_mobile?"loop":s.type,perPage:parseInt(d.slides_to_show_mobile)||2,gap:parseInt(d.gap_mobile)||parseInt(d.gap),padding:r(d.carousel_padding_mobile)},(e.hasClass("--disable-mobile")||n.length<=s.breakpoints[i.md].perPage)&&(s.breakpoints[i.md]={destroy:!0}),t.reySplide({element:a[0],config:s,delay:d.delayInit,customArrows:d.customArrows})}},o=function(e,o){if(e.is("[data-lazy-load]")){o(document).trigger("reycore/elementor/element/lazyload",[e,function(r,d){d.element_id===e.attr("data-id")&&(o(document).trigger("rey/product/loaded",[o("li.product",r)]),"carousel"===d.skin&&t(r,o),"carousel"!==d.skin&&a(r,o))}])}},a=function(e,t){var o=t(".rey-pg-loadmore",e);if(o.length){var a=JSON.parse(o.attr("data-config")||"{}");if(a.element_id&&a.qid){setTimeout((function(){o.addClass("--visible")}),1e3);var r=0,d=a.limit;o.on("click",(function(i){i.preventDefault(),o.addClass("--loading"),t.post({url:t.reyHelpers.params.ajaxurl,data:{action:"reycore_product_grid_load_more",element_id:a.element_id,qid:a.qid,limit:a.limit,offset:d,max:a.max,options:a.options},success:function(i){if(i.success){if(r===a.max-1&&o.addClass("--disabled"),!i.data)return console.log("Empty element data."),void o.addClass("--disabled").removeClass("--loading");var n=t("li.product",i.data);n.length?(n.appendTo(t("ul.products",e)),t(document).trigger("rey/product/loaded",[n]),o.removeClass("--loading"),r++,d=a.limit+d):o.addClass("--disabled").removeClass("--loading")}}})}))}}};e(document).on("reycore/elementor/init",(function(e,r,d){r.hooks.addAction("frontend/element_ready/reycore-product-grid.carousel",t),r.hooks.addAction("frontend/element_ready/reycore-product-grid.carousel",o),r.hooks.addAction("frontend/element_ready/reycore-product-grid.mini",o),r.hooks.addAction("frontend/element_ready/reycore-product-grid.default",o),r.hooks.addAction("frontend/element_ready/reycore-product-grid.default",a),r.hooks.addAction("frontend/element_ready/reycore-product-grid.mini",a)}))}(jQuery);