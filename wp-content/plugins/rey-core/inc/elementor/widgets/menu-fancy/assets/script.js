!function(e,n,t){"use strict";var a=function(e,n,t){this.$backBtn=!1,this.init=function(){this.$scope=e,this.$nav=n(".reyEl-fancyMenu",this.$scope),this.$parentNav=n(".reyEl-fancyMenu-nav",this.$scope),this.depth=this.$nav.attr("data-depth")||20,this.makeHeight(),this.addBacks(),this.a11y(),this.createSubmenuIndicators(),this.events()},this.events=function(){var e=this;n(".menu-item.menu-item-has-children > a",this.$nav).on("click",(function(t){var a=n(this);a.siblings().length&&(t.preventDefault(),n("ul.--start",e.$nav).removeClass("--start"),a.closest("ul").addClass("--back"),a.next("ul").addClass("--start"),e.$nav.css("height",a.nextAll("ul").outerHeight()+"px"))})),n(".reyEl-fancyMenu-back",this.$parentNav).on("click",(function(t){t.preventDefault();var a=n(this);a.closest(".--start").removeClass("--start"),a.closest(".--back").removeClass("--back").addClass("--start"),e.$nav.css("height",n("ul.--start",e.$nav).outerHeight())})),n(document).on("reycore/offcanvas_panel/open",(function(){e.makeHeight()}))},this.makeHeight=function(){this.$nav.css("height",n("ul.--start",this.$nav).outerHeight())},this.a11y=function(){var e=this;this.$popupItems=n(".menu-item-has-children",this.$nav),this.$popupItems.attr({"aria-haspopup":"true","aria-expanded":"false"}),n(".sub-menu a, .reyEl-fancyMenu-back",this.$popupItems).attr("tabindex","-1"),n(document).on("keydown",(function(a){if(9!==a.keyCode){if(-1!==[13,32].indexOf(a.keyCode)){var i=n('.menu-item[aria-haspopup="true"] > a:focus',e.$nav).parent("li");i.length&&(a.preventDefault(),i.each((function(e,t){n(t).attr("aria-expanded","true").children("a").trigger("click"),n("> .sub-menu > li > a, > .sub-menu > .reyEl-fancyMenu-back",t).removeAttr("tabindex")})));var r=n(".reyEl-fancyMenu-back:focus",e.$nav);r.length&&t(r)}27==a.keyCode&&t()}}));var t=function(t){if(t)var a=t.closest('.menu-item[aria-haspopup="true"][aria-expanded="true"]');else a=n('.menu-item[aria-haspopup="true"][aria-expanded="true"]',e.$nav);a.length&&(n("> .sub-menu > .reyEl-fancyMenu-back",a).trigger("click"),a.attr("aria-expanded","false"),n(".sub-menu a, .sub-menu .reyEl-fancyMenu-back",a).attr("tabindex","-1"),a.children("a").trigger("focus"))}},this.addBacks=function(){var e=this.$nav.children(".reyEl-fancyMenu-back");e.length&&n(".sub-menu",this.$parentNav).each((function(t,a){n(a).children(".reyEl-fancyMenu-back").length||e.clone().appendTo(a)}))},this.createSubmenuIndicators=function(){var e,t=this,a=this.$nav.attr("data-indicator");if(a){var i=function(){void 0!==n.reyCore&&("yes"===a&&(e=n.reyCore.addSvgIcon("reycore-icon-play","reycore")),"chevron"===a&&(e=n.reyCore.addSvgIcon("reycore-icon-arrow","reycore")),n.each(n(".menu-item-has-children > a",t.$nav),(function(t,a){var i=n(a);i.siblings().length&&n('<i class="--submenu-indicator">'+e+"</i>").appendTo(i)})))};void 0!==n.reyCore?i():n(document).one("reycore/init",(function(){i()}))}},this.init()};e(window).on("elementor/frontend/init elementor/frontend/ajax",(function(){e.each({"reycore-menu-fancy.default":function(e,n){new a(e,n,"default")}},(function(e,t){void 0!==n.hooks&&n.hooks.addAction("frontend/element_ready/"+e,t)}))})),e(document).on("reymodule/fullscreen_nav/loaded",(function(n,t){"menu"===t&&new a(e(document),e,"default")}))}(jQuery,window.elementorFrontend,window.elementorModules);