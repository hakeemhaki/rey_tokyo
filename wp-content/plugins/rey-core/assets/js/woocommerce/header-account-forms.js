!function(e){"use strict";e(document).on("reycore/woocommerce/init",(function(o,t){var r;r=function(o){this.forms=[{type:"login",formScope:"form.js-rey-woocommerce-form-login",replace:".woocommerce-MyAccount-navigation-wrapper"},{type:"forgot",formScope:"form.js-rey-woocommerce-form-forgot",replace:".rey-pageContent > .woocommerce"},{type:"register",formScope:"form.js-rey-woocommerce-form-register",replace:".woocommerce-MyAccount-navigation-wrapper"}],this.init=function(){var t=this;this.$scope=e(o),this.$notice=e(".rey-accountForms-notice",this.$scope),e.each(this.forms,(function(e,o){t.makeForm(o)})),this.events()},this.events=function(){var o=this;e(".rey-accountForms-links .btn",this.$scope).on("click",(function(t){var r=e(this).attr("data-location")||"";r&&(t.preventDefault(),o.switchForm(r))}))},this.makeForm=function(o){var t=this,r=e(o.formScope,this.$scope);this.$scope.is("[data-no-ajax]")||r.on("submit",(function(i){i.preventDefault(),t.$scope.addClass("--loading"),t.noticeHandlerRemove();var c=r.serialize()||"";c+="&action=reycore_account_forms",c+="&action_type="+o.type,"login"===o.type?c+="&login="+e('button[name="login"]',r).val():"register"===o.type&&(c+="&register="+e('button[name="register"]',r).val()),e.post({url:e.reyHelpers.params.ajaxurl,data:c,success:function(r){if(r&&(!r||r.success)&&void 0!==r.data)if(t.$scope.removeClass("--loading"),r.data.notices)t.noticeHandlerAdd(r.data.notices);else{if("login"===o.type)e(document).trigger("reycore/woocommerce/after_login",[r]),e("body").addClass("logged-in");else if("register"===o.type)e(document).trigger("reycore/woocommerce/after_register",[r]);else if("forgot"===o.type&&!r.data.notices&&!r.data.html)return void t.switchForm("rey-loginForm");var i=t.$scope.attr("data-redirect-type")||"load_menu",c=t.$scope.attr("data-redirect-url");"refresh"===i?c||window.location.reload():"load_menu"===i&&""!=r.data.html?e(".rey-accountForms").wrap('<div class="rey-accountForms-response --'+o.type+'"></div>').replaceWith(e(r.data.html)):window.location.href=c}},error:function(e,o,t){console.error(e),console.error(o),console.error(t)}})}))},this.switchForm=function(o){var t=this;this.$scope.addClass("--loading"),setTimeout((function(){t.$scope.removeClass("--loading"),t.$scope.find(".--active").removeClass("--active"),e("."+o,t.$scope).addClass("--active")}),1e3)},this.noticeHandlerAdd=function(e){this.$notice.length&&this.$notice.html(e).addClass("--filled")},this.noticeHandlerRemove=function(e){this.$notice.length&&this.$notice.html("").removeClass("--filled")},this.init()},e(".rey-accountForms").each((function(e,o){new r(o)}))}))}(jQuery);