!function(t){"use strict";var e=function(e){this.$body=t("body"),this.isOpen=null,this.$panel=!1,this.entryTimeline=!1,this.settings={},this.lazyLoaded=!1,this.$btn=!1,this.init=function(){if(this.args=t.extend({panel_id:!1,btn:!1,legacySettings:{}},e),this.args.btn)if(this.$btn=t(this.args.btn),this.trigger=this.$btn.attr("data-trigger")||"click",this.pre_events(),Object.keys(this.args.legacySettings).length){if(this.settings=this.args.legacySettings,!this.settings.gs)return void console.info("Missing GSID.");this.id=this.settings.gs;var s=t('.rey-offcanvas-wrapper[data-gs-id="'+this.id+'"]');if(s.length||(s=t('.rey-offcanvas-wrapper[data-legacy-id="'+this.id+'"]')),t('.rey-triggerBtn[data-offcanvas-id="'+this.id+'"]').length&&(s=s.clone().appendTo(this.$body)),!s.length)return void console.info("No public offcanvas sections.");this.makeLegacySettings(s)}else{if(this.id=this.$btn.attr("data-offcanvas-id"),!this.id)return void console.info("Missing GSID.");if(this.$panel=t('.rey-offcanvas-wrapper[data-gs-id="'+this.id+'"]'),!this.$panel.length)return;this.makeSettings()}else if(this.args.panel_id){if(this.id=this.args.panel_id,!this.id)return void console.info("Missing GSID.");if(this.$panel=t('.rey-offcanvas-wrapper[data-gs-id="'+this.id+'"]'),!this.$panel.length)return;this.makeSettings()}this.make_panel(),this.bind_events()},this.makeSettings=function(){Object.keys(this.args.legacySettings).length||(this.settings.position=this.$panel.attr("data-position")||"left",this.settings.transition=this.$panel.attr("data-transition")||"",this.settings.transition_duration=this.$panel.attr("data-transition-duration")||"700",this.settings.shift="yes"===this.$panel.attr("data-shift"),this.settings.animate_inside="yes"===this.$panel.attr("data-animate-cols"),this.settings.close_position=this.$panel.attr("data-close-position")||"inside",this.settings.close_rotate="yes"===this.$panel.attr("data-close-rotate"))},this.makeLegacySettings=function(e){Object.keys(this.args.legacySettings).length&&(this.$panel=e.attr({"data-id":this.settings.id,"data-transition":this.settings.transition,"data-transition-duration":this.settings.transition_duration,"data-position":this.settings.position,"data-close-position":this.settings.close_position,"data-close-rotate":this.settings.close_rotate,"data-animate-cols":this.settings.animate_inside,"data-shift":this.settings.shift,"data-legacy-id":this.id}).removeAttr("data-gs-id"),t(".rey-offcanvas-closeText",this.$panel).text(this.settings.close_text))},this.bind_events=function(){var e=this;t(document).on("rey/close_panels",(function(t,s){"offcanvas-panels"!==s&&e.close()})),this.$panel&&t('a[href*="#"]:not([href$="#"])',this.$panel).on("click",(function(){e.close()})),t(".rey-offcanvas-close",this.$panel).on("click",(function(t){t.preventDefault(),e.close()})),t(document).on("reycore/offcanvas_panel/open",(function(e,s){t("a",s.$panel).first().trigger("focus")})),t(document).on("reycore/offcanvas_panel/close",(function(t,e){e.$btn.trigger("focus")}))},this.pre_events=function(){var e=this,s=this.trigger;t.reyHelpers.is_desktop||(s="click"),"click"===s?this.$btn.on("click",(function(t){t.preventDefault(),e.isOpen?e.close():e.open()})):"hover"===s&&this.$btn.on("mouseenter",(function(t){e.open()})).on("mouseleave",(function(t){e.close()}))},this.make_panel=function(){this.$contentWrapper=t(".rey-offcanvas-contentWrapper",this.$panel),this.$content=t(".rey-offcanvas-content",this.$panel),this.$closeBtn=t(".rey-offcanvas-close",this.$panel),this.settings.animate_inside&&(t(".elementor-column-wrap.elementor-element-populated, .elementor-widget-wrap.elementor-element-populated",this.$panel).each((function(e,s){t(s).css("transition-delay",75*e+"ms")})),this.$panel.addClass("--animate-inside")),this.settings.close_rotate&&this.$closeBtn.addClass("--close-rotate")},this.create_anime=function(){if(!this.timelineExists&&"undefined"!=typeof anime&&"basic"!==this.settings.transition){var e=this;switch(this.entryTimeline=anime.timeline({easing:"easeInOutQuart",duration:parseInt(this.settings.transition_duration),autoplay:!1,begin:function(){e.isOpen=!0,e.$body.addClass("--offcanvas-active")},complete:function(){e.$panel.addClass("--active")}}),this.settings.transition){default:var s={left:{translateX:["-100%","0%"]},right:{translateX:["100%","0%"]},top:{translateY:["-100%","0%"]},bottom:{translateY:["100%","-100%"]}};this.entryTimeline.add(t.extend({targets:this.$contentWrapper[0]},s[this.settings.position]));break;case"slideskew":s={left:{opacity:[0,1],translateX:["-120%","0%"],skewX:["-7deg","0deg"]},right:{opacity:[0,1],translateX:["120%","0%"],skewX:["7deg","0deg"]},top:{opacity:[0,1],translateY:["-120%","0%"],skewY:["-7deg","0deg"]},bottom:{opacity:[0,1],translateY:["120%","-120%"],skewY:["7deg","0deg"]}};this.entryTimeline.add(t.extend({targets:this.$contentWrapper[0]},s[this.settings.position]));break;case"curtain":var i,n;i=t(".rey-offcanvas-mask.--m1",this.$panel).length?t(".rey-offcanvas-mask.--m1",this.$panel):t('<div class="rey-offcanvas-mask --m1" />').prependTo(this.$contentWrapper),n=t(".rey-offcanvas-mask.--m2",this.$panel).length?t(".rey-offcanvas-mask.--m2",this.$panel):t('<div class="rey-offcanvas-mask --m2" />').prependTo(this.$contentWrapper);s={left:{c:{scaleX:[0,1]},m:{scaleX:[1,0]}},right:{c:{scaleX:[0,1]},m:{scaleX:[1,0]}},top:{c:{scaleY:[0,1]},m:{scaleY:[1,0]}},bottom:{c:{scaleY:[0,1]},m:{scaleY:[1,0]}}};var a={m1:"-="+.75*parseInt(this.settings.transition_duration),m2:"-="+.4*parseInt(this.settings.transition_duration)};this.entryTimeline.add(t.extend({targets:this.$contentWrapper[0]},s[this.settings.position].c)).add(t.extend({targets:i[0]},s[this.settings.position].m),a.m1).add(t.extend({targets:n[0]},s[this.settings.position].m),a.m2)}this.timelineExists=!0}},this.refreshScroll=function(){var t=this.$content;t.length&&"undefined"!=typeof SimpleScrollbar&&SimpleScrollbar.initEl(t[0])},this.animate_panel=function(){"undefined"!=typeof anime&&this.entryTimeline&&this.entryTimeline.play()},this.open=function(){return this.$panel.length?(this.$panel.removeClass("--hidden"),this.refreshScroll(),this.create_anime(),t(document).trigger("rey/close_panels",["offcanvas-panels"]),t.reyHelpers.overlay("site","open"),t.reyHelpers.doScroll.disable(),this.settings.shift&&(this.$body.addClass("--offcanvas-shift"),this.$body.addClass("--offcanvas-shift--"+this.settings.position)),this.$btn&&this.$btn.addClass("--active"),t(document).trigger("reycore/offcanvas_panel/open",[this]),"basic"===this.settings.transition?(this.isOpen=!0,this.$body.addClass("--offcanvas-active"),void this.$panel.addClass("--active")):void this.animate_panel()):(this.lazyLoaded=!0,void this.lazyLoadPanel())},this.close=function(){if(this.$panel&&this.isOpen){var e=this,s=function(){e.$body.removeClass("--offcanvas-active"),e.$body.removeClass("--offcanvas-shift"),e.$body.removeClass("--offcanvas-shift--"+e.settings.position),e.$panel.removeClass("--active"),e.$btn&&e.$btn.removeClass("--active"),t.reyHelpers.overlay("site","close"),t.reyHelpers.doScroll.enable(),t(document).trigger("reycore/offcanvas_panel/close",[e])};if("basic"===this.settings.transition)s();else{anime(t.extend({easing:"easeInOutQuart",duration:parseInt(this.settings.transition_duration)/2,targets:this.$contentWrapper[0],complete:s},{left:{translateX:["0%","-100%"]},right:{translateX:["0%","100%"]},top:{translateY:["0%","-100%"]},bottom:{translateY:["-100%","100%"]}}[this.settings.position]))}this.isOpen=!1}},this.lazyLoadPanel=function(){var e=this;if(""!==this.id&&void 0!==t.reyHelpers.params.ajaxurl&&void 0!==t.reyHelpers.params.ajax_nonce){var s=!1;this.$btn&&(this.$btn.addClass("--loading"),s=t('<i class="__loader"></i>').appendTo(this.$btn)),t.ajax({type:"POST",url:t.reyHelpers.params.ajaxurl,data:{action:"reycore_offcanvas_panel",gs:e.id,security:t.reyHelpers.params.ajax_nonce},dataType:"json",success:function(i){!i||i&&!i.success||(e.$panel=t(i.data).appendTo(e.$body),e.makeSettings(),e.makeLegacySettings(e.$panel),e.make_panel(),e.bind_events(),e.lazyLoaded&&t(document).trigger("reycore/global_sections/ajax",[e.$panel,i.data]),e.open(),e.$btn&&(e.$btn.removeClass("--loading"),s&&s.remove()))}})}},this.init()};t(document).one("reycore/init",(function(){t(".js-triggerBtn[data-offcanvas-settings]").each((function(s,i){new e({btn:i,legacySettings:t.extend({id:"",gs:"",shift:!0,trigger:"click",position:"left",transition:"",transition_duration:700,animate_inside:!0,close_position:"inside",close_text:"",close_rotate:!1},JSON.parse(t(i).attr("data-offcanvas-settings")||"{}"))})})),t(".js-triggerBtn[data-offcanvas-id]").each((function(t,s){new e({btn:s})})),t("a[data-offcanvas-id]").each((function(s,i){t(i).on("click",(function(s){if(s.preventDefault(),a)a.open();else{var n=t(i).attr("data-offcanvas-id");if(n&&!isNaN(n)){var a=new e({panel_id:parseInt(n)});a.open()}}}))})),t("a[href^='#offcanvas-']").each((function(s,i){t(i).on("click",(function(s){if(s.preventDefault(),a)a.open();else{var n=t(i).attr("href").split("-");if(n[1]&&!isNaN(n[1])){var a=new e({panel_id:parseInt(n[1])});a.open()}}}))})),t(document).on("reycore/offcanvas/open",(function(t,s){new e({panel_id:s}).open()}))}))}(jQuery);