!function(e){"use strict";var t=function(e,t){({$parent:!1,init:function(){var n=this;"undefined"!=typeof elementor&&(this.$scope=e,this.$link=t(".js-click-activate-element",this.$scope),this.$parent=this.$link.parent(),this.$link.on("click",(function(e){var r=t(this).attr("data-element");n.$parent.addClass("--disabled"),t.ajax({method:"post",url:reyElementorFrontendParams.ajax_url,data:{action:"rey_activate_element",security:reyElementorFrontendParams.ajax_nonce,element:r},success:function(e){e&&e.success&&n.reloadElementor(r)},error:function(e,t,n){console.error(e),console.error(t),console.error(n)}})})))},reloadElementor:function(e){if(this.$parent){this.$parent.removeClass("--disabled"),elementor.reloadPreview();var t='<style>.elementor-element[data-widget-type="'+e+'"]:before {display: none}</style>';elementor.getPanelView().$el.closest("#elementor-editor-wrapper").after(t)}}}).init()};e(document).on("reycore/elementor/init",(function(e,n,r){n.hooks.addAction("frontend/element_ready/widget",t)}))}(jQuery);