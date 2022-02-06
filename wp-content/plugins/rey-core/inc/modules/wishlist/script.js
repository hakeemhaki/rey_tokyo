!function(e){"use strict";var t=function(){this.cookie_key="rey_wishlist_ids_"+e.reyHelpers.params.site_id,this.logged_in=e("body").hasClass("logged-in"),this.isWishlistPage=e("body.rey-wishlist-page").length,this.init=function(){this.$wishlistWrapper=e(".rey-wishlistWrapper"),this.$siteMain=e("body.rey-wishlist-page .rey-siteContent"),this.$emptyPage=e(".rey-wishlist-emptyPage",this.$siteMain),this.hideTitle=this.$wishlistWrapper.hasClass("--hide-title"),this.mobileCloneTop(),this.checkPageIds(),this.cleanupPage(),this.updateCounters(),this.events()},this.events=function(){var t=this;e(document).on("click",".rey-wishlistBtn",(function(s){s.preventDefault();var i=e(this),r=parseInt(i.attr("data-id")||""),o=t.getProducts(),a=e(".rey-wishlistBtn-text",i);if(!isNaN(r)){if(i.hasClass("--supports-ajax")&&i.addClass("--doing"),i.hasClass("--in-wishlist")){var n=o.indexOf(r);if(-1!==n)return t.removeProduct(r,n),i.removeClass("--in-wishlist"),i.attr({title:e.reyHelpers.params.wishlist_text_add,"aria-label":e.reyHelpers.params.wishlist_text_add}),void(a.length&&a.text(e.reyHelpers.params.wishlist_text_add))}t.setProduct(r),i.addClass("--in-wishlist"),i.attr({title:e.reyHelpers.params.wishlist_text_rm,"aria-label":e.reyHelpers.params.wishlist_text_rm}),a.length&&a.text(e.reyHelpers.params.wishlist_text_rm)}})),e(document).on("click",".rey-wishlistItem-remove",(function(s){s.preventDefault();var i=e(this),r=parseInt(i.attr("data-id")||""),o=t.getProducts();if(!isNaN(r)){var a=o.indexOf(r);-1!==a&&(t.removeProduct(r,a),i.closest(".rey-wishlistItem").fadeOut(500,(function(){e(this).remove(),e(document).trigger("reycore/woocommerce/wishlist_account/remove")})),e(".rey-wishlistBtn.--in-wishlist[data-id="+r+"]").removeClass("--in-wishlist").attr({title:e.reyHelpers.params.wishlist_text_add,"aria-label":e.reyHelpers.params.wishlist_text_add}))}})),e(document).on("reycore/woocommerce/wishlist/added_product reycore/woocommerce/wishlist/removed_product",(function(e,s,i){t.updateCounters(s),t.save(s),t.logged_in||t.animateBtn(i),t.cleanupPage()})),e(document).on("reycore/woocommerce/after_login",(function(e,s){t.getSavedProducts()})),e(document).on("reycore/ajaxfilters/finished",(function(e,s){t.mobileCloneTop()})),e(document).on("rey/product/loaded",(function(e,s){t.mobileCloneTop(s)}))},this.checkPageIds=function(){if(this.isWishlistPage){var t=this.getProducts();t.length?this.$wishlistWrapper.length&&e("li.product",this.$wishlistWrapper).length===t.length||this.getWishlistPageContent():this.$siteMain.removeClass("--loading")}},this.getWishlistPageContent=function(){var t=this;(e.reyHelpers.params.ajaxurl||e.reyHelpers.params.ajax_nonce)&&(this.$siteMain.addClass("--loading"),e.ajax({url:e.reyHelpers.params.ajaxurl,dataType:"json",cache:!1,data:{action:"rey_wishlist_get_page_content",security:e.reyHelpers.params.ajax_nonce,pid:t.$emptyPage.attr("data-id"),"hide-title":this.hideTitle?1:0},success:function(s){if(s&&(!s||s.success)&&s.data&&t.$siteMain.length){var i=e(s.data),r=e("li.product",i);i.appendTo(t.$wishlistWrapper),t.cleanupPage(),t.$siteMain.removeClass("--loading"),r.length&&t.$wishlistWrapper.removeClass("--empty"),e(document).trigger("rey/wishlist/loaded",[i]),e(document).trigger("rey/product/loaded",[r,i]),e(document).on("click",".rey-wishlist-removeBtn",(function(s){s.preventDefault();var i=e(this),r=parseInt(i.attr("data-id")||""),o=t.getProducts(),a=i.closest("li.product");if(!isNaN(r)){var n=o.indexOf(r);-1!==n&&(t.removeProduct(r,n),a.attr("style","").fadeOut(300,(function(){e(this).remove(),e("li.product",t.$wishlistWrapper).length||t.$wishlistWrapper.addClass("--empty")})))}}))}},error:function(e,t,s){console.error(e),console.error(t),console.error(s)}}))},this.cleanupPage=function(){this.isWishlistPage&&this.$siteMain.length&&e(".woocommerce-notices-wrapper, .rey-loopHeader",this.$siteMain).remove()},this.animateBtn=function(t){var s=this,i=e(t?".rey-wishlistBtn[data-id="+t+"]":".rey-wishlistBtn.--doing");i.addClass("--animate"),setTimeout((function(){i.removeClass("--animate"),"notice"===e.reyHelpers.params.wishlist_after_add&&s.showNotice(i)}),500)},this.mobileCloneTop=function(t){e.reyHelpers.is_mobile&&e(".rey-wishlistBtn.--show-mobile--top",t||e(document)).each((function(){var t=e(this),s=t.closest("li.product"),i=e(".rey-productThumbnail .rey-thPos--top-right",s);i.length||(i=e('<div class="rey-thPos rey-thPos--top-right"></div>').appendTo(e(".rey-productThumbnail",s))),t.clone().removeClass("--show-mobile--top").addClass("--show-mobile--top-show").appendTo(i)}))},this.showNotice=function(t){if(t.hasClass("--in-wishlist")){var s=e(".rey-wishlist-notice-wrapper");s.removeClass("--hidden").addClass("--visible");var i=new function(e,t){var s,i,r=t;this.pause=function(){window.clearTimeout(s),r-=Date.now()-i},this.resume=function(){i=Date.now(),window.clearTimeout(s),s=window.setTimeout(e,r)},this.resume()}((function(){s.removeClass("--visible")}),3200);i.resume(),e(".rey-wishlist-notice",s).on("mouseenter",(function(){i.pause()})).on("mouseleave",(function(){i.resume()}))}},this.updateCounters=function(t){t||(t=this.getProducts());var s=e(".rey-wishlistCounter-number"),i=t.length;i?s.text(i).removeClass("--empty"):s.text("").addClass("--empty")},this.save=function(t){if(t||(t=this.getProducts()),this.logged_in&&(e.reyHelpers.params.ajaxurl||e.reyHelpers.params.ajax_nonce)){var s=this;e(".rey-wishlistBtn").addClass("--disabled"),e.ajax({url:e.reyHelpers.params.ajaxurl,dataType:"json",cache:!1,data:{action:"rey_wishlist_add_to_user_meta",security:e.reyHelpers.params.ajax_nonce},success:function(t){s.animateBtn(),e(".rey-wishlistBtn").removeClass("--disabled --doing")},error:function(e,t,s){console.error(e),console.error(t),console.error(s)}})}},this.getProducts=function(){if("undefined"!=typeof Cookies){var e=Cookies.get(this.cookie_key);return e?e.split("|").map((function(e){return parseInt(e)})):[]}},this.getSavedProducts=function(){if(e.reyHelpers.params.ajaxurl||e.reyHelpers.params.ajax_nonce){this.logged_in=!0;var t=this;e.ajax({url:e.reyHelpers.params.ajaxurl,dataType:"json",cache:!1,data:{action:"get_wishlist_data",security:e.reyHelpers.params.ajax_nonce},success:function(s){if(s&&s.success){var i=s.data;if(i.length){var r=i.map((function(e){return e.id}));"undefined"!=typeof Cookies&&(Cookies.set(t.cookie_key,r.join("|")),t.updateCounters(r),e(".rey-wishlistBtn").removeClass("--supports-ajax --in-wishlist"),e.each(r,(function(s,i){var r=e(".rey-wishlistBtn[data-id="+i+"]");r.addClass("--in-wishlist"),t.logged_in&&r.addClass("--supports-ajax")})),e(document).trigger("reycore/woocommerce/wishlist/get_saved_products",[i]))}}},error:function(e,t,s){console.error(e),console.error(t),console.error(s)}})}},this.setProduct=function(t){if("undefined"!=typeof Cookies){t=parseInt(t);var s=this.getProducts();-1===s.indexOf(t)&&(s.push(t),Cookies.set(this.cookie_key,s.join("|")),e(document).trigger("reycore/woocommerce/wishlist/added_product",[s,t]))}},this.removeProduct=function(t,s,i){if("undefined"!=typeof Cookies){t=parseInt(t);var r=this.getProducts();r.splice(s,1),Cookies.set(this.cookie_key,r.join("|")),i||e(document).trigger("reycore/woocommerce/wishlist/removed_product",[r,t])}},this.init()};e(document).one("reycore/init",(function(){e.reyWishlist=new t}))}(jQuery);