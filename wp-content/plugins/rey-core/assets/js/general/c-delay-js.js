document.addEventListener("DOMContentLoaded",(function(){for(var e=!1,t=[".woocommerce-product-gallery__mobileWrapper"],n=0;n<t.length;n++){if(e)return;document.querySelectorAll(t[n]).length&&setTimeout((function(){window.dispatchEvent(new Event("mousemove")),e=!0}),10)}})),window.addEventListener("rocket-allScriptsLoaded",(function(){document.querySelectorAll("body.--not-ready").length&&window.dispatchEvent(new Event("rey/delayed_ready"))}));