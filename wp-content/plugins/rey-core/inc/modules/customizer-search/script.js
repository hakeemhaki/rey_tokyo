!function(e,t){"use strict";var i={containerSelector:"#rey-customizer-search",init:function(){this.customizerWrapper=e("#customize-controls"),this.customizerPanels=e("#customize-theme-controls",this.customizerWrapper),this.customizerSectionThemes=e("#accordion-section-themes",this.customizerWrapper),this.customizerInfo=e("#customize-info",this.customizerWrapper),this.addSearchForm(),this.searchInput=e('input[type="search"]',this.containerSelector),this.searchResults=e('<div class="rey-customizerSearch-results"><ul class="rey-searchResults"></ul></div>').insertAfter(this.customizerPanels),this.resultsTemplate=wp.template("rey-customizer-search-results"),this.indexControls(),this.events()},events:function(){var t=this;e(this.searchInput,document).on("input",(function(i){i.preventDefault();var s=e(i.target).val();s.length>0?t.displayMatches(s):t.clearSearch()})).on("focus",(function(e){t.activateSearchView()})),e(".rey-customizerSearch-cancel",this.containerSelector).on("click",(function(){t.deactivateSearchView()})),this.customizerPanels.on("click",(function(){t.customizerWrapper.hasClass("--focused")&&t.deactivateSearchView()}))},addSearchForm:function(){this.customizerWrapper.addClass("--rey-search");var t=wp.template("rey-customizer-search-form");e(this.containerSelector).length||this.customizerInfo.after(t())},activateSearchView:function(){this.customizerWrapper.addClass("--focused"),this.customizerPanels.animate({opacity:.5},500),this.customizerSectionThemes.slideUp("fast"),this.customizerInfo.slideUp("fast")},deactivateSearchView:function(){this.customizerWrapper.removeClass("--focused"),this.customizerPanels.animate({opacity:1},500),this.customizerSectionThemes.slideDown("fast"),this.customizerInfo.slideDown("fast"),this.clearSearch(!0)},indexControls:function(){this.controls=e.map(_wpCustomizeSettings.controls,(function(t,i){return e.map(_wpCustomizeSettings.sections,(function(i,s){t.section==i.id&&e.map(_wpCustomizeSettings.panels,(function(e,s){""==i.panel&&(t.panelName=i.title),i.panel==e.id&&(t.sectionName=i.title,t.panel=i.panel,t.panelName=e.title)}))})),[t]})),this.controls=this.controls.filter((function(e){return-1===["widgets","themes","nav_menus"].indexOf(e.panel)}))},expandSection:function(e){},displayMatches:function(i){var s=this,n=this.findMatches(i);if(n.length){var c=n.map((function(t,n){if(""!==t.label){var c=t.label;c=c.replace(/&quot;/g,'"');var a=new RegExp(i,"gi"),r='<span class="hl">'+i+"</span>",o=t.description,l=e("<div></div>").html(c);return o=e("<div></div>").html(o).text(),l.find(".rey-csTitleHelp-title").length&&(c=l.find(".rey-csTitleHelp-title").html(),o=l.find(".rey-csTitleHelp-content").text()),c=c.replace(a,r),o=o.replace(a,r),s.resultsTemplate({section:t.section,label:c,description:o,panelName:t.panelName,sectionName:t.sectionName,controlId:t.id})}}));this.customizerWrapper.addClass("--hide-panels"),this.searchResults.children("ul").html(c),e(".rey-searchResult",this.searchResults).on("click",(function(i){var n=e(this),c=n.attr("data-section"),a=n.attr("data-control"),r=t.section(c);s.deactivateSearchView(),r.expand({completeCallback:function(){t.control(a,(function(e){e.deferred.embedded.done((function(){t.previewer.deferred.active.done((function(){e.focus(),e.container.addClass("--focus-control"),setTimeout((function(){e.container.removeClass("--focus-control")}),1500)}))}))}))}})}))}},findMatches:function(e){return this.controls.filter((function(t){null==t.panelName&&(t.panelName=""),null==t.sectionName&&(t.sectionName="");var i=new RegExp(e,"gi");return t.label.match(i)||t.panelName.match(i)||t.sectionName.match(i)}))},clearSearch:function(e){this.customizerWrapper.removeClass("--hide-panels"),this.searchResults.children("ul").html(""),this.searchInput.val(""),!0!==e&&this.searchInput.focus()}};t.bind("ready",(function(){i.init()}))}(jQuery,wp.customize);