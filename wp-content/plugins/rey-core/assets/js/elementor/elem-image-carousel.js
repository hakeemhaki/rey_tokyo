!function(e){"use strict";var i=function(e,i){var n=i(".swiper-container.--variable-width",e);if(n.length&&"undefined"!=typeof Swiper){var t=function(){var e=n[0].swiper;if(void 0!==e){var i=e.params;i.slidesPerView="auto",i.breakpoints={},i.on={init:function(){n.css("opacity",1)}},e.destroy();new Swiper(n[0],i)}else setTimeout(t,500)};t()}};e(document).on("reycore/elementor/init",(function(e,n,t){n.hooks.addAction("frontend/element_ready/image-carousel.default",i)}))}(jQuery);