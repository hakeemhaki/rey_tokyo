!function(t,i,s){"use strict";var e=function(t,i){this.init=function(){if(i.reyHelpers.is_edit_mode||!t.hasClass("--init")){t.addClass("--init"),this.activeClass="--active",this.animateOutClass="--animate-out",this.currentSlide=0,this.slidesCount=0,this.settings={},this.cssSpeed=2100,this.interval=!1,this.isAnimating=!1,this.$slider=i(".rey-coverSkew",t),this.$slidesContainer=i(".coverSkew-slides",this.$slider),this.$slides=i(".coverSkew-slide",this.$slidesContainer),this.$captionsContainer=i(".coverSkew-captions",this.$slider),this.$captionItems=i(".coverSkew-captionItem",this.$captionsContainer),this.$nextContainer=i(".coverSkew-next",this.$slider),this.$nextItems=i(".coverSkew-nextItem",this.$nextContainer),this.$dotsNavContainer=i(".coverSkew-nav",this.$slider),this.$dots=i("span",this.$dotsNavContainer),this.slidesCount=this.$slides.length;var s=this;this.slidesCount&&(this.settings=i.extend({autoplay:!1,autoplayDuration:5e3,dots:!0,waitForAnimation:!0},JSON.parse(this.$slider.attr("data-slider-settings")||"{}")),this.$slides.imagesLoaded((function(){s.loadFirst()})),this.events())}},this.events=function(){var t,s=this;(this.$nextContainer.on("click",(function(t){t.preventDefault(),s.goToNext(),s.resetAutoplay()})),i(document).keydown((function(t){"39"==t.keyCode&&(s.goToNext(),s.resetAutoplay())})),this.$slider.on("onEndSlide",(function(t,i){s.toggleActiveCaption()})),this.$slider.on("onInitSlider",(function(){s.$slider.addClass("--initialised"),s.autoplay(),s.dotsNav()})),this.$dots.on("click",(function(t){var e=i(this);if(!e.hasClass(s.activeClass)){if(s.settings.waitForAnimation&&s.isAnimating)return;s.goToSlide(parseInt(e.attr("data-index")||0))}})),void 0!==i.reyHelpers&&i.reyHelpers.is_touch_device())&&(this.$slidesContainer.bind("touchstart",(function(i){t=i.originalEvent.touches[0].clientX})),this.$slidesContainer.bind("touchend",(function(i){var e=i.originalEvent.changedTouches[0].clientX;t>e?s.goToPrev():s.goToNext()})))},this.toggleActiveNav=function(){var t=this.currentSlide+1;this.slidesCount==t&&(t=0),this.$nextItems.removeClass(this.activeClass),this.$nextItems.eq(t).addClass(this.activeClass)},this.toggleActiveCaption=function(){this.$captionItems.eq(this.currentSlide).addClass(this.activeClass)},this.goToNext=function(){var t=this.currentSlide+1;this.slidesCount==t&&(t=0),this.settings.waitForAnimation&&this.isAnimating||this.goToSlide(t)},this.goToPrev=function(){var t;t=0==this.currentSlide?this.slidesCount-1:this.currentSlide-1,this.settings.waitForAnimation&&this.isAnimating||this.goToSlide(t)},this.goToSlide=function(t){var i=this,s=this.currentSlide;this.currentSlide=t,this.$slides.removeClass(this.animateOutClass),this.isAnimating=!0,this.$slider.trigger("onStartSlide",[this.currentSlide]),this.$captionItems.removeClass(this.activeClass),this.$slides.eq(s).addClass(this.animateOutClass),this.$slides.removeClass(this.activeClass),this.$slides.eq(this.currentSlide).addClass(this.activeClass),this.toggleActiveNav(),this.toggleActiveDot(),this.$slides.eq(s).one("transitionend oTransitionEnd MSTransitionEnd",(function(t){i.$slides.removeClass(i.animateOutClass),i.$slider.trigger("onEndSlide",[this.currentSlide]),i.isAnimating=!1}))},this.loadFirst=function(){var t=this;this.$slider.removeClass("--loading"),this.isAnimating=!0,this.$slides.removeClass(this.activeClass),this.$slides.eq(this.currentSlide).addClass(this.activeClass),this.$slider.trigger("onInitSlider"),this.toggleActiveNav(),this.toggleActiveDot(),this.$slides.eq(this.currentSlide).one("transitionend oTransitionEnd MSTransitionEnd",(function(i){t.$slider.trigger("onEndSlide",[this.currentSlide]),t.$nextContainer.addClass("--visible"),t.isAnimating=!1}))},this.autoplay=function(){var t=this;this.settings.autoplay&&this.slidesCount>1&&(this.interval=setInterval((function(){t.goToNext()}),parseInt(this.settings.autoplayDuration)+this.cssSpeed))},this.resetAutoplay=function(){this.settings.autoplay&&this.interval&&(clearInterval(this.interval),this.autoplay())},this.dotsNav=function(){this.settings.dots&&this.$dots.eq(this.currentSlide).addClass(this.activeClass)},this.toggleActiveDot=function(){this.settings.dots&&(this.$dots.removeClass(this.activeClass),this.$dots.eq(this.currentSlide).addClass(this.activeClass))},this.init()};t(document).on("rey/site_loaded",(function(){t.reyHelpers.$sitePreloader.length>0&&t(".elementor-widget-reycore-cover-skew").each((function(i,s){new e(t(s),t)}))})),t(window).on("elementor/frontend/init elementor/frontend/ajax",(function(){i.hooks.addAction("frontend/element_ready/reycore-cover-skew.default",(function(t,i){new e(t,i)}))}))}(jQuery,window.elementorFrontend,window.elementorModules);