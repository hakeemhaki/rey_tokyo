!function(a){"use strict";var t=function(){this.variation_ids=[],this.init=function(){this.$variations=a("#variable_product_options .woocommerce_variation"),this.getVariationIDs(),this.getVariationImages(),this.events()},this.getVariationImages=function(){if(this.variation_ids.length){var t=this,i=wp.template("rey-extra-images-admin");a.post({url:reyEviParams.ajax_url,data:{action:"rey_get_extra_variation_images_admin",security:reyEviParams.ajax_nonce,variation_ids:t.variation_ids},success:function(e){Object.keys(e.data).length?(a.each(e.data,(function(e,r){var n=a('a.upload_image_button[rel="'+e+'"]',t.$variations);n.next(".rey-extraVariationsImages").length||n.after(i({variation_id:e,variation_images:r}))})),t.runSortable()):console.warn("empty")},error:function(a,t,i){console.error(a),console.error(t),console.error(i)}})}},this.getVariationIDs=function(){var t=this;a.each(this.$variations,(function(){var i=a(this).find("a.upload_image_button").prop("rel");t.variation_ids.push(i)}))},this.runSortable=function(){var t=this;a(".rey-extraVariationsImages").each((function(i,e){a(".rey-extraVariationsImages-list",e).sortable({update:function(){t.prepThumbs(a(e).attr("data-variation-id"))},placeholder:"sortable-placeholder",cursor:"move"})}))},this.prepThumbs=function(t){if(t){var i=[],e=a('.rey-extraVariationsImages[data-variation-id="'+t+'"]'),r=a("input.js-rey-extraVariationsImages-save",e),n=a(".rey-extraVariationsImages-list li",e);n.length?(n.each((function(t,e){var r=a("a",e).attr("data-id");r&&i.push(r)})),i.join(",").replace(/,,/gi,",")):i="",r.val(i),a("#variable_product_options input").first().change(),e.parents(".woocommerce_variation").first().addClass("variation-needs-update")}},this.events=function(){var t=this;this.$variations.on("click",".rey-extraVariationsImages-btn",(function(i){i.preventDefault();var e=a(this).parent(".rey-extraVariationsImages"),r=e.closest(".upload_image"),n=e.attr("data-variation-id"),o=a(".rey-extraVariationsImages-list",e),s=!1,c=r.find("input.upload_image_id").val(),l=a(".upload_image_button",r);c&&0!=c?(l.removeClass("--force-upload"),!1===s?((s=wp.media.frames.mediaFrame=wp.media({title:reyEviParams.media_title,button:{text:reyEviParams.media_button_text},library:{type:"image"},multiple:!0})).on("select",(function(){s.state().get("selection").map((function(a){if((a=a.toJSON()).id){var t=a.sizes.thumbnail?a.sizes.thumbnail.url:a.url;o.append('<li><a href="#" data-id="'+a.id+'"><img src="'+t+'" /></a></li>')}})),wp.media.model.settings.post.id=n,t.runSortable(),t.prepThumbs(n)})),s.open()):s.open()):l.addClass("--force-upload")})),this.$variations.on("click",".rey-extraVariationsImages-list > li > a",(function(i){i.preventDefault();var e=a(this),r=e.closest(".rey-extraVariationsImages").attr("data-variation-id");e.parent("li").remove(),t.prepThumbs(r)})),a(document).on("click",".js-enable-extraimages",(function(t){t.preventDefault();var i=a(this).closest(".rey-extraVariationsImages-notice");i.css({opacity:.5,"pointer-events":"none"}),a.post({url:reyEviParams.ajax_url,data:{action:"rey_extra_variation_enable",security:reyEviParams.ajax_nonce},success:function(a){a&&a.success&&(i.text("Reloading page..."),setTimeout((function(){window.location.reload()}),1e3))},error:function(a,t,i){console.error(a),console.error(t),console.error(i)}})}))},this.init()};a(document).ready((function(){a.reyWcExtraVariationImages=new t,a("body").on("woocommerce_variations_added woocommerce_variations_loaded",(function(){a.reyWcExtraVariationImages=new t}))}))}(jQuery);