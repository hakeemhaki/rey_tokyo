!function(t){"use strict";var o=function(o){var e=t(".woocommerce-result-count");if(t(".rey-ajaxLoadMore").length&&e.length){var n=e.text(),c=e.html(n.replace(/(\d+–\d+)/gi,"<span class='total-count'>$1</span>")),r=t(".total-count",c),a=r.text().split("–");void 0!==o&&2===a.length&&(a[1]=o.length+parseInt(a[1]),r.text(a.join("–")))}};t(document).on("reycore/woocommerce/init",(function(t,e){o()})),t(document).on("rey/product/loaded",(function(t,e,n){o(e)}))}(jQuery);