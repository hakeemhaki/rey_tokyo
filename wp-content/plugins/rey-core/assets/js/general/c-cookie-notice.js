!function(e){"use strict";e(document).on("reytheme/init",(function(o,t){var i;(i=e(".rey-cookieNotice")).length&&(e.reyHelpers.ls.get("rey-cookie-notice")?e(document).trigger("reycore/cookie_notice/accepted"):(e.reyHelpers.$sitePreloader.length?e(document).on("rey/site_loaded",(function(){i.addClass("--visible")})):setTimeout((function(){i.addClass("--visible")}),2e3),e(".btn",i).on("click",(function(o){o.preventDefault(),e.reyHelpers.ls.set("rey-cookie-notice",!0,e.reyHelpers.expiration.month),i.removeClass("--visible"),e(document).trigger("reycore/cookie_notice/accepted",[!0])}))))}))}(jQuery);