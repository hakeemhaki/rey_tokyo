!function(t){"use strict";var e=function(e){this.init=function(){t.reyHelpers.params.loop_ajax_variable_products&&(this.options=t.extend({removeInstances:!1,closePanels:!0},e),this.options.removeInstances&&t(".rey-productLoop-variationsForm").remove(),void 0!==t.reyThemeWooCommerce&&(this.$wcApp=t.reyThemeWooCommerce,this.handleButtons(),this.events()))},this.events=function(){},this.handleButtons=function(){var e=this,o=function(o){e.$btn=t(this),e.$parentProduct=e.$btn.closest("li.product"),e.options.closePanels=!0;var r=e.$btn.closest(".rey-crossSells-item");if(r.length&&(e.$parentProduct=r,e.options.closePanels=!1),e.$parentProduct.length){e.$parentProduct.addClass("--hover"),o.preventDefault(),e.productId=e.$btn.attr("data-product_id")||"",e.btnPos=e.$btn.offset(),e.parentPos=e.$parentProduct.offset(),e.leftPos=e.parentPos.left+parseInt(e.$parentProduct.css("padding-left")),e.productWidth=e.$parentProduct.width();var a=t('.rey-productLoop-variationsForm[data-id="'+e.productId+'"]');if(e.productWidth<300&&(e.productWidth=300),t.reyHelpers.is_mobile&&(e.productWidth="calc(100vw - 30px)"),a.length){e.options.closePanels&&t(document).trigger("rey/close_panels",["productLoop-variationsForm"]);var s={left:"var(--half-gutter-size)",top:e.btnPos.top};return t.reyHelpers.is_mobile||(s.left=e.leftPos,s.width=e.productWidth),void a.css(s).addClass("--visible")}e.$parentProduct.addClass("--loading --hover"),t.ajax({url:t.reyHelpers.params.ajaxurl,data:{action:"loop_variable_product_add_to_cart",product_id:e.productId},success:function(o){e.assets_loaded?e.getData(o,e):(t.reyHelpers.getMultiStyles(reyAjaxVariablesParams.styles),t.reyHelpers.getMultiScripts(reyAjaxVariablesParams.scripts).done((function(){e.assets_loaded=!0,e.getData(o,e)})))},fail:function(){e.$parentProduct.removeClass("--loading --hover")}})}};t(document).on("click",".product_type_variable.add_to_cart_button",o),t(document).on("click",".product_type_bundle_input_required.add_to_cart_button",o)},this.getData=function(e,o){if(e)if(e.success){o.options.closePanels&&t(document).trigger("rey/close_panels",["productLoop-variationsForm"]),o.$parentProduct.removeClass("--loading --hover");var r=t(e.data).appendTo(document.body);void 0!==t.fn.wc_variation_form&&t(".variations_form",r).each((function(){t(this).wc_variation_form()})),t(document).trigger("reycore/ajax_variation_popup/after_open",[r,o]),o.$wcApp.modifyQuantityNumberField();var a={left:"var(--half-gutter-size)",top:o.btnPos.top};t.reyHelpers.is_mobile||(a.left=o.leftPos,a.width=o.productWidth),r.css(a).addClass("--visible"),t(".rey-productLoop-variationsForm-pointer").css("left",o.btnPos.left-o.leftPos+o.$btn.width()/2);var s=function(t){o.$parentProduct.removeClass("--hover"),r.fadeOut("200",(function(){t&&r.remove(),r.removeClass("--visible").css({left:"",top:"",display:""})}))};t(".rey-productLoop-variationsForm-close",r).on("click",(function(t){t.preventDefault(),s()})),t(document).on("keyup",(function(t){27==t.keyCode&&r.hasClass("--visible")&&s()})),t(document.body).on("added_to_cart",(function(t,e,o,r){s()})),t(document).on("click",".rey-productLoop-variationsForm .rey-overlay",(function(){s()})),t(".variations_form",r).addClass("--prevent-scroll-to-gallery")}else console.warn(e.data)},this.init()};t(document).one("reycore/init",(function(){t.reyVariablesPopup=new e}))}(jQuery);