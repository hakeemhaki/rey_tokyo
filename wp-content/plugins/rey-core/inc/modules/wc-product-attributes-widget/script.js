!function(t){"use strict";var e=function(e){var a,n,i,r,l,c=function(a){var n=a||t(e),i=t(".rey-catWgt-nav[data-height]",n),r=t(".rey-catWgt-navInner",i),l=i.attr("data-height")||0;if(t(".rey-catWgt-navlist",r).height()<parseFloat(l))return r.css("height",""),void t(".rey-catWgt-customHeight-all",i).hide();r.length&&"undefined"!=typeof SimpleScrollbar&&SimpleScrollbar.initEl(r[0])};a=t(".rey-catWgt-nav.--accordion",e),t(".__toggle",a).each((function(e,a){t(a).addClass("--collapsed"),t(a).nextAll("ul.children").addClass("--hidden")})),t(".rey-cw-item-current",a).each((function(e,a){var n=t(a).parents("ul");n.siblings(".__toggle").removeClass("--collapsed"),n.removeClass("--hidden")})),i=t(".rey-catWgt-alphabetic",e),r=t(e).find(".rey-catWgt-navlist"),l=[],t("li[data-letter]",r).each((function(e,a){l.push(t(this).attr("data-letter"))})),n=l.filter((function(t,e,a){return a.indexOf(t)===e})).sort(),t.each(n,(function(e,a){t('<span data-letter="'+a+'">'+a+"</span>").appendTo(i)})),c(),t(".rey-catWgt-nav.--accordion-clk-toggle .rey-catWgt-navlist",e).on("click","li .__toggle ~ a",(function(e){e.preventDefault();var a=t(this).prevAll(".__toggle");t(e.target).is("span.__checkbox")||a.trigger("click")})),t(".rey-catWgt-nav[data-height] .rey-catWgt-customHeight-all",e).on("click",(function(e){e.preventDefault();var a=t(this).closest(".rey-catWgt-nav"),n=a.find(".rey-catWgt-navInner");if(a.hasClass("--reset-height"))return n.css("height",a.attr("data-height")),void a.removeClass("--reset-height");n.css("height",""),a.addClass("--reset-height")})),t(".rey-catWgt-searchbox input",e).on("input",t.reyHelpers.debounce((function(e){e.preventDefault();var a=t(this).closest(".widget").find(".rey-catWgt-navlist"),n=t("li > a",a),i=new RegExp(e.target.value,"gi");n.parent().addClass("--hidden"),n.filter((function(){return t(this).text().match(i)})).parents("li").removeClass("--hidden")}),400)),t(".rey-catWgt-alphabetic span",e).on("click",(function(e){e.preventDefault();var a=t(this),n=a.parent(),i=a.attr("data-letter")||"",r=n.nextAll(".rey-catWgt-nav").find("li[data-letter]");if(a.hasClass("rey-catWgt-alphabetic-all"))return n.children().removeClass("--active"),a.addClass("--active"),r.removeClass("--hidden"),void c(n);n.children().removeClass("--active"),a.addClass("--active"),r.addClass("--hidden");var l=r.filter('[data-letter="'+i+'"]');l.removeClass("--hidden"),l.parents("li[data-letter]").removeClass("--hidden"),c(n)})),t(".rey-catWgt-nav.--accordion .__toggle",e).on("click",(function(e){e.preventDefault(),t(this).toggleClass("--collapsed"),t(this).nextAll("ul.children").toggleClass("--hidden")})),t(".rey-cw-item-current > a",e).on("click",(function(e){var a=t(this).closest(".rey-catWgt-nav").attr("data-shop-url");a&&(e.preventDefault(),window.location.href=a)}))};t(document).one("reycore/init",(function(){t(".widget.rey-catWgt").each((function(t,a){new e(a)}))}))}(jQuery);