!function(e){"use strict";var t=function(e,t){if(!t.reyHelpers.elementor_edit_mode&&e.hasClass("--sticky-col")&&t.reyHelpers.is_desktop&&void 0!==t.reySticky){var r,n,a=e.attr("data-id");a&&(r='.elementor-element[data-id="'+a+'"] > .elementor-column-wrap--'+a);var o=e.closest(".elementor-section").attr("data-id");if(o&&(n='.elementor-section[data-id="'+o+'"] > .elementor-container'),!r||!n)return;t.reySticky({element:r,marginTop:parseInt(e.attr("data-top-offset"))||0,stickyContainer:n,fixedHeaderAware:"yes"===e.attr("data-sticky-hf"),stickyClass:"--is-sticked"})}};e(document).on("reycore/elementor/init",(function(e,r,n){r.hooks.addAction("frontend/element_ready/column",t)}))}(jQuery);