!function(e){"use strict";var c=function(){e(".rey-classic-reviewOrder-img").each((function(c,o){!function(c){var o=e(c);o.next(".rey-classic-reviewOrder-content").length||o.nextAll().wrapAll("<div class='rey-classic-reviewOrder-content' />")}(o)}))};e(document.body).on("updated_wc_div updated_shipping_method applied_coupon removed_coupon country_to_state_changed applied_coupon_in_checkout update_checkout updated_checkout checkout_error",(function(){c()})),e(document).on("reycore/woocommerce/init",(function(e,o){c()}))}(jQuery);