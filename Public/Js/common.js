!function(a){a.fn.hoverIntent=function(b,c,d){var e={interval:100,sensitivity:7,timeout:0};e="object"==typeof b?a.extend(e,b):a.isFunction(c)?a.extend(e,{over:b,out:c,selector:d}):a.extend(e,{over:b,out:b,selector:c});var f,g,h,i,j=function(a){f=a.pageX,g=a.pageY},k=function(b,c){return c.hoverIntent_t=clearTimeout(c.hoverIntent_t),Math.abs(h-f)+Math.abs(i-g)<e.sensitivity?(a(c).off("mousemove.hoverIntent",j),c.hoverIntent_s=1,e.over.apply(c,[b])):(h=f,i=g,c.hoverIntent_t=setTimeout(function(){k(b,c)},e.interval),void 0)},l=function(a,b){return b.hoverIntent_t=clearTimeout(b.hoverIntent_t),b.hoverIntent_s=0,e.out.apply(b,[a])},m=function(b){var c=jQuery.extend({},b),d=this;d.hoverIntent_t&&(d.hoverIntent_t=clearTimeout(d.hoverIntent_t)),"mouseenter"==b.type?(h=c.pageX,i=c.pageY,a(d).on("mousemove.hoverIntent",j),1!=d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){k(c,d)},e.interval))):(a(d).off("mousemove.hoverIntent",j),1==d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){l(c,d)},e.timeout)))};return this.on({"mouseenter.hoverIntent":m,"mouseleave.hoverIntent":m},e.selector)}}(jQuery);
var showNotice,adminMenu,columns,validateForm,screenMeta;!function(a,b){adminMenu={init:function(){},fold:function(){},restoreMenuState:function(){},toggle:function(){},favorites:function(){}},columns={init:function(){var b=this;a(".hide-column-tog","#adv-settings").click(function(){var c=a(this),d=c.val();c.prop("checked")?b.checked(d):b.unchecked(d),columns.saveManageColumnsState()})},saveManageColumnsState:function(){var b=this.hidden();a.post(ajaxurl,{action:"hidden-columns",hidden:b,screenoptionnonce:a("#screenoptionnonce").val(),page:pagenow})},checked:function(b){a(".column-"+b).show(),this.colSpanChange(1)},unchecked:function(b){a(".column-"+b).hide(),this.colSpanChange(-1)},hidden:function(){return a(".manage-column").filter(":hidden").map(function(){return this.id}).get().join(",")},useCheckboxesForHidden:function(){this.hidden=function(){return a(".hide-column-tog").not(":checked").map(function(){var a=this.id;return a.substring(a,a.length-5)}).get().join(",")}},colSpanChange:function(b){var c,d=a("table").find(".colspanchange");d.length&&(c=parseInt(d.attr("colspan"),10)+b,d.attr("colspan",c.toString()))}},a(document).ready(function(){columns.init()}),validateForm=function(b){return!a(b).find(".form-required").filter(function(){return""===a("input:visible",this).val()}).addClass("form-invalid").find("input:visible").change(function(){a(this).closest(".form-invalid").removeClass("form-invalid")}).size()},showNotice={warn:function(){var a=commonL10n.warnDelete||"";return confirm(a)?!0:!1},note:function(a){alert(a)}},screenMeta={element:null,toggles:null,page:null,init:function(){this.element=a("#screen-meta"),this.toggles=a(".screen-meta-toggle a"),this.page=a("#wpcontent"),this.toggles.click(this.toggleEvent)},toggleEvent:function(b){var c=a(this.href.replace(/.+#/,"#"));b.preventDefault(),c.length&&(c.is(":visible")?screenMeta.close(c,a(this)):screenMeta.open(c,a(this)))},open:function(b,c){a(".screen-meta-toggle").not(c.parent()).css("visibility","hidden"),b.parent().show(),b.slideDown("fast",function(){b.focus(),c.addClass("screen-meta-active").attr("aria-expanded",!0)})},close:function(b,c){b.slideUp("fast",function(){c.removeClass("screen-meta-active").attr("aria-expanded",!1),a(".screen-meta-toggle").css("visibility",""),b.parent().hide()})}},a(".contextual-help-tabs").delegate("a","click focus",function(b){var c,d=a(this);return b.preventDefault(),d.is(".active a")?!1:(a(".contextual-help-tabs .active").removeClass("active"),d.parent("li").addClass("active"),c=a(d.attr("href")),a(".help-tab-content").not(c).removeClass("active").hide(),void c.addClass("active").show())}),a(document).ready(function(){var c,d,e,f,g,h,i,j,k=!1,l=a("#adminmenu"),m=a("input.current-page"),n=m.val();l.on("click.wp-submenu-head",".wp-submenu-head",function(b){a(b.target).parent().siblings("a").get(0).click()}),a("#collapse-menu").on("click.collapse-menu",function(){var c,d=a(document.body);a("#adminmenu div.wp-submenu").css("margin-top",""),c=b.innerWidth?Math.max(b.innerWidth,document.documentElement.clientWidth):901,c&&900>c?d.hasClass("auto-fold")?(d.removeClass("auto-fold").removeClass("folded"),setUserSetting("unfold",1),setUserSetting("mfold","o")):(d.addClass("auto-fold"),setUserSetting("unfold",0)):d.hasClass("folded")?(d.removeClass("folded"),setUserSetting("mfold","o")):(d.addClass("folded"),setUserSetting("mfold","f"))}),("ontouchstart"in b||/IEMobile\/[1-9]/.test(navigator.userAgent))&&(h=/Mobile\/.+Safari/.test(navigator.userAgent)?"touchstart":"click",a(document.body).on(h+".wp-mobile-hover",function(b){l.data("wp-responsive")||a(b.target).closest("#adminmenu").length||l.find("li.wp-has-submenu.opensub").removeClass("opensub")}),l.find("a.wp-has-submenu").on(h+".wp-mobile-hover",function(c){var d,e,f,g,h,i,j,k=a(this),m=k.parent(),n=m.find(".wp-submenu");l.data("wp-responsive")||m.hasClass("opensub")||m.hasClass("wp-menu-open")&&!(m.width()<40)||(c.preventDefault(),h=m.offset().top,i=a(b).scrollTop(),j=h-i-30,d=h+n.height()+1,e=a("#wpwrap").height(),f=60+d-e,g=a(b).height()+i-50,d-f>g&&(f=d-g),f>j&&(f=j),f>1?n.css("margin-top","-"+f+"px"):n.css("margin-top",""),l.find("li.opensub").removeClass("opensub"),m.addClass("opensub"))})),l.find("li.wp-has-submenu").hoverIntent({over:function(){var c,d,e,f,g,h,i,j=a(this).find(".wp-submenu"),k=parseInt(j.css("top"),10);isNaN(k)||k>-5||l.data("wp-responsive")||(g=a(this).offset().top,h=a(b).scrollTop(),i=g-h-30,c=g+j.height()+1,d=a("#wpwrap").height(),e=60+c-d,f=a(b).height()+h-15,c-e>f&&(e=c-f),e>i&&(e=i),e>1?j.css("margin-top","-"+e+"px"):j.css("margin-top",""),l.find("li.menu-top").removeClass("opensub"),a(this).addClass("opensub"))},out:function(){l.data("wp-responsive")||a(this).removeClass("opensub").find(".wp-submenu").css("margin-top","")},timeout:200,sensitivity:7,interval:90}),l.on("focus.adminmenu",".wp-submenu a",function(b){l.data("wp-responsive")||a(b.target).closest("li.menu-top").addClass("opensub")}).on("blur.adminmenu",".wp-submenu a",function(b){l.data("wp-responsive")||a(b.target).closest("li.menu-top").removeClass("opensub")}),a("div.wrap h2:first").nextAll("div.updated, div.error").addClass("below-h2"),a("div.updated, div.error").not(".below-h2, .inline").insertAfter(a("div.wrap h2:first")),screenMeta.init(),a("tbody").children().children(".check-column").find(":checkbox").click(function(b){if("undefined"==b.shiftKey)return!0;if(b.shiftKey){if(!k)return!0;c=a(k).closest("form").find(":checkbox"),d=c.index(k),e=c.index(this),f=a(this).prop("checked"),d>0&&e>0&&d!=e&&(g=e>d?c.slice(d,e):c.slice(e,d),g.prop("checked",function(){return a(this).closest("tr").is(":visible")?f:!1}))}k=this;var h=a(this).closest("tbody").find(":checkbox").filter(":visible").not(":checked");return a(this).closest("table").children("thead, tfoot").find(":checkbox").prop("checked",function(){return 0===h.length}),!0}),a("thead, tfoot").find(".check-column :checkbox").on("click.wp-toggle-checkboxes",function(b){var c=a(this),d=c.closest("table"),e=c.prop("checked"),f=b.shiftKey||c.data("wp-toggle");d.children("tbody").filter(":visible").children().children(".check-column").find(":checkbox").prop("checked",function(){return a(this).is(":hidden")?!1:f?!a(this).prop("checked"):e?!0:!1}),d.children("thead,  tfoot").filter(":visible").children().children(".check-column").find(":checkbox").prop("checked",function(){return f?!1:e?!0:!1})}),a("td.post-title, td.title, td.comment, .bookmarks td.column-name, td.blogname, td.username, .dashboard-comment-wrap").focusin(function(){clearTimeout(i),j=a(this).find(".row-actions"),j.addClass("visible")}).focusout(function(){i=setTimeout(function(){j.removeClass("visible")},30)}),a("#default-password-nag-no").click(function(){return setUserSetting("default_password_nag","hide"),a("div.default-password-nag").hide(),!1}),a("#newcontent").bind("keydown.wpevent_InsertTab",function(b){var c,d,e,f,g,h=b.target;if(27==b.keyCode)return void a(h).data("tab-out",!0);if(!(9!=b.keyCode||b.ctrlKey||b.altKey||b.shiftKey)){if(a(h).data("tab-out"))return void a(h).data("tab-out",!1);c=h.selectionStart,d=h.selectionEnd,e=h.value;try{this.lastKey=9}catch(i){}document.selection?(h.focus(),g=document.selection.createRange(),g.text="	"):c>=0&&(f=this.scrollTop,h.value=e.substring(0,c).concat("	",e.substring(d)),h.selectionStart=h.selectionEnd=c+1,this.scrollTop=f),b.stopPropagation&&b.stopPropagation(),b.preventDefault&&b.preventDefault()}}),a("#newcontent").bind("blur.wpevent_InsertTab",function(){this.lastKey&&9==this.lastKey&&this.focus()}),m.length&&m.closest("form").submit(function(){-1==a('select[name="action"]').val()&&-1==a('select[name="action2"]').val()&&m.val()==n&&m.val("1")}),a('.search-box input[type="search"], .search-box input[type="submit"]').mousedown(function(){a('select[name^="action"]').val("-1")}),a("#contextual-help-link, #show-settings-link").on("focus.scroll-into-view",function(a){a.target.scrollIntoView&&a.target.scrollIntoView(!1)}),function(){function b(){c.prop("disabled",""===d.map(function(){return a(this).val()}).get().join(""))}var c,d,e=a("form.wp-upload-form");e.length&&(c=e.find('input[type="submit"]'),d=e.find('input[type="file"]'),b(),d.on("change",b))}()}),function(){function c(){a(document).trigger("wp-window-resized")}function d(){b.clearTimeout(e),e=b.setTimeout(c,200)}var e;a(b).on("resize.wp-fire-once",d)}(),a(document).ready(function(){var c=a(document),d=a(b),e=a(document.body),f=a("#adminmenuwrap"),g=a("#collapse-menu"),h=a("#wpwrap"),i=a("#adminmenu"),j=a("#wp-responsive-overlay"),k=a("#wp-toolbar"),l=k.find('a[aria-haspopup="true"]'),m=a(".meta-box-sortables"),n=!1,o=!1;b.stickyMenu={enable:function(){n||(c.on("wp-window-resized.sticky-menu",a.proxy(this.update,this)),g.on("click.sticky-menu",a.proxy(this.update,this)),this.update(),n=!0)},disable:function(){n&&(d.off("resize.sticky-menu"),g.off("click.sticky-menu"),e.removeClass("sticky-menu"),n=!1)},update:function(){d.height()>f.height()+32?e.hasClass("sticky-menu")||e.addClass("sticky-menu"):e.hasClass("sticky-menu")&&e.removeClass("sticky-menu")}},b.wpResponsive={init:function(){var e=this,f=0;c.on("wp-responsive-activate.wp-responsive",function(){e.activate()}).on("wp-responsive-deactivate.wp-responsive",function(){e.deactivate()}),a("#wp-admin-bar-menu-toggle a").attr("aria-expanded","false"),a("#wp-admin-bar-menu-toggle").on("click.wp-responsive",function(b){b.preventDefault(),h.toggleClass("wp-responsive-open"),h.hasClass("wp-responsive-open")?(a(this).find("a").attr("aria-expanded","true"),a("#adminmenu a:first").focus()):a(this).find("a").attr("aria-expanded","false")}),i.on("touchstart.wp-responsive","li.wp-has-submenu > a",function(){f=d.scrollTop()}).on("touchend.wp-responsive click.wp-responsive","li.wp-has-submenu > a",function(b){!i.data("wp-responsive")||"touchend"===b.type&&d.scrollTop()!==f||(a(this).parent("li").toggleClass("selected"),b.preventDefault())}),e.trigger(),c.on("wp-window-resized.wp-responsive",a.proxy(this.trigger,this)),d.on("load.wp-responsive",function(){var a=navigator.userAgent.indexOf("AppleWebKit/")>-1?d.width():b.innerWidth;782>=a&&e.disableSortables()})},activate:function(){b.stickyMenu.disable(),e.hasClass("auto-fold")||e.addClass("auto-fold"),i.data("wp-responsive",1),this.disableSortables()},deactivate:function(){b.stickyMenu.enable(),i.removeData("wp-responsive"),this.enableSortables()},trigger:function(){var a;b.innerWidth&&(a=Math.max(b.innerWidth,document.documentElement.clientWidth),782>=a?o||(c.trigger("wp-responsive-activate"),o=!0):o&&(c.trigger("wp-responsive-deactivate"),o=!1),480>=a?this.enableOverlay():this.disableOverlay())},enableOverlay:function(){0===j.length&&(j=a('<div id="wp-responsive-overlay"></div>').insertAfter("#wpcontent").hide().on("click.wp-responsive",function(){k.find(".menupop.hover").removeClass("hover"),a(this).hide()})),l.on("click.wp-responsive",function(){j.show()})},disableOverlay:function(){l.off("click.wp-responsive"),j.hide()},disableSortables:function(){if(m.length)try{m.sortable("disable")}catch(a){}},enableSortables:function(){if(m.length)try{m.sortable("enable")}catch(a){}}},b.stickyMenu.enable(),b.wpResponsive.init()}),function(){if("-ms-user-select"in document.documentElement.style&&navigator.userAgent.match(/IEMobile\/10\.0/)){var a=document.createElement("style");a.appendChild(document.createTextNode("@-ms-viewport{width:auto!important}")),document.getElementsByTagName("head")[0].appendChild(a)}}()}(jQuery,window);
"undefined"!=typeof jQuery?("undefined"==typeof jQuery.fn.hoverIntent&&!function(a){a.fn.hoverIntent=function(b,c,d){var e={interval:100,sensitivity:7,timeout:0};e="object"==typeof b?a.extend(e,b):a.isFunction(c)?a.extend(e,{over:b,out:c,selector:d}):a.extend(e,{over:b,out:b,selector:c});var f,g,h,i,j=function(a){f=a.pageX,g=a.pageY},k=function(b,c){return c.hoverIntent_t=clearTimeout(c.hoverIntent_t),Math.abs(h-f)+Math.abs(i-g)<e.sensitivity?(a(c).off("mousemove.hoverIntent",j),c.hoverIntent_s=1,e.over.apply(c,[b])):(h=f,i=g,c.hoverIntent_t=setTimeout(function(){k(b,c)},e.interval),void 0)},l=function(a,b){return b.hoverIntent_t=clearTimeout(b.hoverIntent_t),b.hoverIntent_s=0,e.out.apply(b,[a])},m=function(b){var c=jQuery.extend({},b),d=this;d.hoverIntent_t&&(d.hoverIntent_t=clearTimeout(d.hoverIntent_t)),"mouseenter"==b.type?(h=c.pageX,i=c.pageY,a(d).on("mousemove.hoverIntent",j),1!=d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){k(c,d)},e.interval))):(a(d).off("mousemove.hoverIntent",j),1==d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){l(c,d)},e.timeout)))};return this.on({"mouseenter.hoverIntent":m,"mouseleave.hoverIntent":m},e.selector)}}(jQuery),jQuery(document).ready(function(a){var b,c,d,e=a("#wpadminbar"),f=!1;b=function(b,c){var d=a(c),e=d.attr("tabindex");e&&d.attr("tabindex","0").attr("tabindex",e)},c=function(b){e.find("li.menupop").on("click.wp-mobile-hover",function(c){var d=a(this);d.parent().is("#wp-admin-bar-root-default")&&!d.hasClass("hover")?(c.preventDefault(),e.find("li.menupop.hover").removeClass("hover"),d.addClass("hover")):d.hasClass("hover")||(c.stopPropagation(),c.preventDefault(),d.addClass("hover")),b&&(a("li.menupop").off("click.wp-mobile-hover"),f=!1)})},d=function(){var b=/Mobile\/.+Safari/.test(navigator.userAgent)?"touchstart":"click";a(document.body).on(b+".wp-mobile-hover",function(b){a(b.target).closest("#wpadminbar").length||e.find("li.menupop.hover").removeClass("hover")})},e.removeClass("nojq").removeClass("nojs"),"ontouchstart"in window?(e.on("touchstart",function(){c(!0),f=!0}),d()):/IEMobile\/[1-9]/.test(navigator.userAgent)&&(c(),d()),e.find("li.menupop").hoverIntent({over:function(){f||a(this).addClass("hover")},out:function(){f||a(this).removeClass("hover")},timeout:180,sensitivity:7,interval:100}),window.location.hash&&window.scrollBy(0,-32),a("#wp-admin-bar-get-shortlink").click(function(b){b.preventDefault(),a(this).addClass("selected").children(".shortlink-input").blur(function(){a(this).parents("#wp-admin-bar-get-shortlink").removeClass("selected")}).focus().select()}),a("#wpadminbar li.menupop > .ab-item").bind("keydown.adminbar",function(c){if(13==c.which){var d=a(c.target),e=d.closest("ab-sub-wrapper");c.stopPropagation(),c.preventDefault(),e.length||(e=a("#wpadminbar .quicklinks")),e.find(".menupop").removeClass("hover"),d.parent().toggleClass("hover"),d.siblings(".ab-sub-wrapper").find(".ab-item").each(b)}}).each(b),a("#wpadminbar .ab-item").bind("keydown.adminbar",function(c){if(27==c.which){var d=a(c.target);c.stopPropagation(),c.preventDefault(),d.closest(".hover").removeClass("hover").children(".ab-item").focus(),d.siblings(".ab-sub-wrapper").find(".ab-item").each(b)}}),a("#wpadminbar").click(function(b){("wpadminbar"==b.target.id||"wp-admin-bar-top-secondary"==b.target.id)&&(b.preventDefault(),a("html, body").animate({scrollTop:0},"fast"))}),a(".screen-reader-shortcut").keydown(function(b){var c,d;13==b.which&&(c=a(this).attr("href"),d=navigator.userAgent.toLowerCase(),-1!=d.indexOf("applewebkit")&&c&&"#"==c.charAt(0)&&setTimeout(function(){a(c).focus()},100))}),"sessionStorage"in window&&a("#wp-admin-bar-logout a").click(function(){try{for(var a in sessionStorage)-1!=a.indexOf("wp-autosave-")&&sessionStorage.removeItem(a)}catch(b){}}),navigator.userAgent&&-1===document.body.className.indexOf("no-font-face")&&/Android (1.0|1.1|1.5|1.6|2.0|2.1)|Nokia|Opera Mini|w(eb)?OSBrowser|webOS|UCWEB|Windows Phone OS 7|XBLWP7|ZuneWP7|MSIE 7/.test(navigator.userAgent)&&(document.body.className+=" no-font-face")})):!function(a,b){var c,d=function(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent&&a.attachEvent("on"+b,function(){return c.call(a,window.event)})},e=new RegExp("\\bhover\\b","g"),f=[],g=new RegExp("\\bselected\\b","g"),h=function(a){for(var b=f.length;b--;)if(f[b]&&a==f[b][1])return f[b][0];return!1},i=function(b){for(var d,i,j,k,l,m,n=[],o=0;b&&b!=c&&b!=a;)"LI"==b.nodeName.toUpperCase()&&(n[n.length]=b,i=h(b),i&&clearTimeout(i),b.className=b.className?b.className.replace(e,"")+" hover":"hover",k=b),b=b.parentNode;if(k&&k.parentNode&&(l=k.parentNode,l&&"UL"==l.nodeName.toUpperCase()))for(d=l.childNodes.length;d--;)m=l.childNodes[d],m!=k&&(m.className=m.className?m.className.replace(g,""):"");for(d=f.length;d--;){for(j=!1,o=n.length;o--;)n[o]==f[d][1]&&(j=!0);j||(f[d][1].className=f[d][1].className?f[d][1].className.replace(e,""):"")}},j=function(b){for(;b&&b!=c&&b!=a;)"LI"==b.nodeName.toUpperCase()&&!function(a){var b=setTimeout(function(){a.className=a.className?a.className.replace(e,""):""},500);f[f.length]=[b,a]}(b),b=b.parentNode},k=function(b){for(var d,e,f,h=b.target||b.srcElement;;){if(!h||h==a||h==c)return;if(h.id&&"wp-admin-bar-get-shortlink"==h.id)break;h=h.parentNode}for(b.preventDefault&&b.preventDefault(),b.returnValue=!1,-1==h.className.indexOf("selected")&&(h.className+=" selected"),d=0,e=h.childNodes.length;e>d;d++)if(f=h.childNodes[d],f.className&&-1!=f.className.indexOf("shortlink-input")){f.focus(),f.select(),f.onblur=function(){h.className=h.className?h.className.replace(g,""):""};break}return!1},l=function(a){var b,c,d,e,f,g;if(!("wpadminbar"!=a.id&&"wp-admin-bar-top-secondary"!=a.id||(b=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0,1>b)))for(g=b>800?130:100,c=Math.min(12,Math.round(b/g)),d=Math.round(b>800?b/30:b/20),e=[],f=0;b;)b-=d,0>b&&(b=0),e.push(b),setTimeout(function(){window.scrollTo(0,e.shift())},f*c),f++};d(b,"load",function(){c=a.getElementById("wpadminbar"),a.body&&c&&(a.body.appendChild(c),c.className&&(c.className=c.className.replace(/nojs/,"")),d(c,"mouseover",function(a){i(a.target||a.srcElement)}),d(c,"mouseout",function(a){j(a.target||a.srcElement)}),d(c,"click",k),d(c,"click",function(a){l(a.target||a.srcElement)}),d(document.getElementById("wp-admin-bar-logout"),"click",function(){if("sessionStorage"in window)try{for(var a in sessionStorage)-1!=a.indexOf("wp-autosave-")&&sessionStorage.removeItem(a)}catch(b){}})),b.location.hash&&b.scrollBy(0,-32),navigator.userAgent&&-1===document.body.className.indexOf("no-font-face")&&/Android (1.0|1.1|1.5|1.6|2.0|2.1)|Nokia|Opera Mini|w(eb)?OSBrowser|webOS|UCWEB|Windows Phone OS 7|XBLWP7|ZuneWP7|MSIE 7/.test(navigator.userAgent)&&(document.body.className+=" no-font-face")})}(document,window);
!function(a){a.suggest=function(b,c){function d(){var a=o.offset();p.css({top:a.top+b.offsetHeight+"px",left:a.left+"px"})}function e(a){if(/27$|38$|40$/.test(a.keyCode)&&p.is(":visible")||/^13$|^9$/.test(a.keyCode)&&k())switch(a.preventDefault&&a.preventDefault(),a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0,a.returnValue=!1,a.keyCode){case 38:n();break;case 40:m();break;case 9:case 13:l();break;case 27:p.hide()}else o.val().length!=r&&(q&&clearTimeout(q),q=setTimeout(f,c.delay),r=o.val().length)}function f(){var b,d,e=a.trim(o.val());c.multiple&&(b=e.lastIndexOf(c.multipleSep),-1!=b&&(e=a.trim(e.substr(b+c.multipleSep.length)))),e.length>=c.minchars?(cached=g(e),cached?i(cached.items):a.get(c.source,{q:e},function(a){p.hide(),d=j(a,e),i(d),h(e,d,a.length)})):p.hide()}function g(a){var b;for(b=0;b<s.length;b++)if(s[b].q==a)return s.unshift(s.splice(b,1)[0]),s[0];return!1}function h(a,b,d){for(var e;s.length&&t+d>c.maxCacheSize;)e=s.pop(),t-=e.size;s.push({q:a,size:d,items:b}),t+=d}function i(b){var e,f="";if(b){if(!b.length)return void p.hide();for(d(),e=0;e<b.length;e++)f+="<li>"+b[e]+"</li>";p.html(f).show(),p.children("li").mouseover(function(){p.children("li").removeClass(c.selectClass),a(this).addClass(c.selectClass)}).click(function(a){a.preventDefault(),a.stopPropagation(),l()})}}function j(b,d){var e,f,g=[],h=b.split(c.delimiter);for(e=0;e<h.length;e++)f=a.trim(h[e]),f&&(f=f.replace(new RegExp(d,"ig"),function(a){return'<span class="'+c.matchClass+'">'+a+"</span>"}),g[g.length]=f);return g}function k(){var a;return p.is(":visible")?(a=p.children("li."+c.selectClass),a.length||(a=!1),a):!1}function l(){$currentResult=k(),$currentResult&&(c.multiple?($currentVal=-1!=o.val().indexOf(c.multipleSep)?o.val().substr(0,o.val().lastIndexOf(c.multipleSep)+c.multipleSep.length):"",o.val($currentVal+$currentResult.text()+c.multipleSep),o.focus()):o.val($currentResult.text()),p.hide(),o.trigger("change"),c.onSelect&&c.onSelect.apply(o[0]))}function m(){$currentResult=k(),$currentResult?$currentResult.removeClass(c.selectClass).next().addClass(c.selectClass):p.children("li:first-child").addClass(c.selectClass)}function n(){var a=k();a?a.removeClass(c.selectClass).prev().addClass(c.selectClass):p.children("li:last-child").addClass(c.selectClass)}var o,p,q,r,s,t;o=a(b).attr("autocomplete","off"),p=a("<ul/>"),q=!1,r=0,s=[],t=0,p.addClass(c.resultsClass).appendTo("body"),d(),a(window).load(d).resize(d),o.blur(function(){setTimeout(function(){p.hide()},200)}),o.keydown(e)},a.fn.suggest=function(b,c){return b?(c=c||{},c.multiple=c.multiple||!1,c.multipleSep=c.multipleSep||", ",c.source=b,c.delay=c.delay||100,c.resultsClass=c.resultsClass||"ac_results",c.selectClass=c.selectClass||"ac_over",c.matchClass=c.matchClass||"ac_match",c.minchars=c.minchars||2,c.delimiter=c.delimiter||"\n",c.onSelect=c.onSelect||!1,c.maxCacheSize=c.maxCacheSize||65536,this.each(function(){new a.suggest(this,c)}),this):void 0}}(jQuery);
!function(a,b,c){var d=function(){function d(){if("string"==typeof b.pagenow&&(B.screenId=b.pagenow),"string"==typeof b.ajaxurl&&(B.url=b.ajaxurl),"object"==typeof b.heartbeatSettings){var c=b.heartbeatSettings;!B.url&&c.ajaxurl&&(B.url=c.ajaxurl),c.interval&&(B.mainInterval=c.interval,B.mainInterval<15?B.mainInterval=15:B.mainInterval>60&&(B.mainInterval=60)),B.screenId||(B.screenId=c.screenId||"front"),"disable"===c.suspension&&(B.suspendEnabled=!1)}B.mainInterval=1e3*B.mainInterval,B.originalInterval=B.mainInterval,a(b).on("blur.wp-heartbeat-focus",function(){m(),B.winBlurTimer=b.setTimeout(function(){k()},500)}).on("focus.wp-heartbeat-focus",function(){n(),l()}).on("unload.wp-heartbeat",function(){B.suspend=!0,B.xhr&&4!==B.xhr.readyState&&B.xhr.abort()}),b.setInterval(function(){q()},3e4),A.ready(function(){B.lastTick=e(),j()})}function e(){return(new Date).getTime()}function f(a){var c,d=a.src;if(d&&/^https?:\/\//.test(d)&&(c=b.location.origin?b.location.origin:b.location.protocol+"//"+b.location.host,0!==d.indexOf(c)))return!1;try{if(a.contentWindow.document)return!0}catch(e){}return!1}function g(a,b){var c;if(a){switch(a){case"abort":break;case"timeout":c=!0;break;case"error":if(503===b&&B.hasConnected){c=!0;break}case"parsererror":case"empty":case"unknown":B.errorcount++,B.errorcount>2&&B.hasConnected&&(c=!0)}c&&!s()&&(B.connectionError=!0,A.trigger("heartbeat-connection-lost",[a,b]))}}function h(){B.hasConnected=!0,s()&&(B.errorcount=0,B.connectionError=!1,A.trigger("heartbeat-connection-restored"))}function i(){var c,d;B.connecting||B.suspend||(B.lastTick=e(),d=a.extend({},B.queue),B.queue={},A.trigger("heartbeat-send",[d]),c={data:d,interval:B.tempInterval?B.tempInterval/1e3:B.mainInterval/1e3,_nonce:"object"==typeof b.heartbeatSettings?b.heartbeatSettings.nonce:"",action:"heartbeat",screen_id:B.screenId,has_focus:B.hasFocus},B.connecting=!0,B.xhr=a.ajax({url:B.url,type:"post",timeout:3e4,data:c,dataType:"json"}).always(function(){B.connecting=!1,j()}).done(function(a,b,c){var d;return a?(h(),a.nonces_expired?void A.trigger("heartbeat-nonces-expired"):(a.heartbeat_interval&&(d=a.heartbeat_interval,delete a.heartbeat_interval),A.trigger("heartbeat-tick",[a,b,c]),void(d&&v(d)))):void g("empty")}).fail(function(a,b,c){g(b||"unknown",a.status),A.trigger("heartbeat-error",[a,b,c])}))}function j(){var a=e()-B.lastTick,c=B.mainInterval;B.suspend||(B.hasFocus?B.countdown>0&&B.tempInterval&&(c=B.tempInterval,B.countdown--,B.countdown<1&&(B.tempInterval=0)):c=12e4,b.clearTimeout(B.beatTimer),c>a?B.beatTimer=b.setTimeout(function(){i()},c-a):i())}function k(){o(),B.hasFocus=!1}function l(){o(),B.userActivity=e(),B.suspend=!1,B.hasFocus||(B.hasFocus=!0,j())}function m(){a("iframe").each(function(c,d){f(d)&&(a.data(d,"wp-heartbeat-focus")||(a.data(d,"wp-heartbeat-focus",1),a(d.contentWindow).on("focus.wp-heartbeat-focus",function(){l()}).on("blur.wp-heartbeat-focus",function(){m(),B.frameBlurTimer=b.setTimeout(function(){k()},500)})))})}function n(){a("iframe").each(function(b,c){f(c)&&(a.removeData(c,"wp-heartbeat-focus"),a(c.contentWindow).off(".wp-heartbeat-focus"))})}function o(){b.clearTimeout(B.winBlurTimer),b.clearTimeout(B.frameBlurTimer)}function p(){B.userActivityEvents=!1,A.off(".wp-heartbeat-active"),a("iframe").each(function(b,c){f(c)&&a(c.contentWindow).off(".wp-heartbeat-active")}),l()}function q(){var b=B.userActivity?e()-B.userActivity:0;b>3e5&&B.hasFocus&&k(),B.suspendEnabled&&b>12e5&&(B.suspend=!0),B.userActivityEvents||(A.on("mouseover.wp-heartbeat-active keyup.wp-heartbeat-active",function(){p()}),a("iframe").each(function(b,c){f(c)&&a(c.contentWindow).on("mouseover.wp-heartbeat-active keyup.wp-heartbeat-active",function(){p()})}),B.userActivityEvents=!0)}function r(){return B.hasFocus}function s(){return B.connectionError}function t(){B.lastTick=0,j()}function u(){B.suspendEnabled=!1}function v(a,b){var c,d=B.tempInterval?B.tempInterval:B.mainInterval;if(a){switch(a){case"fast":case 5:c=5e3;break;case 15:c=15e3;break;case 30:c=3e4;break;case 60:c=6e4;break;case"long-polling":return B.mainInterval=0,0;default:c=B.originalInterval}5e3===c?(b=parseInt(b,10)||30,b=1>b||b>30?30:b,B.countdown=b,B.tempInterval=c):(B.countdown=0,B.tempInterval=0,B.mainInterval=c),c!==d&&j()}return B.tempInterval?B.tempInterval/1e3:B.mainInterval/1e3}function w(a,b,c){return a?c&&this.isQueued(a)?!1:(B.queue[a]=b,!0):!1}function x(a){return a?B.queue.hasOwnProperty(a):void 0}function y(a){a&&delete B.queue[a]}function z(a){return a?this.isQueued(a)?B.queue[a]:c:void 0}var A=a(document),B={suspend:!1,suspendEnabled:!0,screenId:"",url:"",lastTick:0,queue:{},mainInterval:60,tempInterval:0,originalInterval:0,countdown:0,connecting:!1,connectionError:!1,errorcount:0,hasConnected:!1,hasFocus:!0,userActivity:0,userActivityEvents:!1,beatTimer:0,winBlurTimer:0,frameBlurTimer:0};return d(),{hasFocus:r,connectNow:t,disableSuspend:u,interval:v,hasConnectionError:s,enqueue:w,dequeue:y,isQueued:x,getQueuedItem:z}};b.wp=b.wp||{},b.wp.heartbeat=new d}(jQuery,window);
var inlineEditPost;!function(a){inlineEditPost={init:function(){var b=this,c=a("#inline-edit"),d=a("#bulk-edit");b.type=a("table.widefat").hasClass("pages")?"page":"post",b.what="#post-",c.keyup(function(a){return 27===a.which?inlineEditPost.revert():void 0}),d.keyup(function(a){return 27===a.which?inlineEditPost.revert():void 0}),a("a.cancel",c).click(function(){return inlineEditPost.revert()}),a("a.save",c).click(function(){return inlineEditPost.save(this)}),a("td",c).keydown(function(a){return 13===a.which?inlineEditPost.save(this):void 0}),a("a.cancel",d).click(function(){return inlineEditPost.revert()}),a('#inline-edit .inline-edit-private input[value="private"]').click(function(){var b=a("input.inline-edit-password-input");a(this).prop("checked")?b.val("").prop("disabled",!0):b.prop("disabled",!1)}),a("#the-list").on("click","a.editinline",function(){return inlineEditPost.edit(this),!1}),a("#bulk-title-div").parents("fieldset").after(a("#inline-edit fieldset.inline-edit-categories").clone()).siblings("fieldset:last").prepend(a("#inline-edit label.inline-edit-tags").clone()),a('select[name="_status"] option[value="future"]',d).remove(),a("#doaction, #doaction2").click(function(c){var d=a(this).attr("id").substr(2);"edit"===a('select[name="'+d+'"]').val()?(c.preventDefault(),b.setBulk()):a("form#posts-filter tr.inline-editor").length>0&&b.revert()})},toggle:function(b){var c=this;"none"===a(c.what+c.getId(b)).css("display")?c.revert():c.edit(b)},setBulk:function(){var b,c="",d=this.type,e=!0;return this.revert(),a("#bulk-edit td").attr("colspan",a(".widefat:first thead th:visible").length),a("table.widefat tbody").prepend(a("#bulk-edit")),a("#bulk-edit").addClass("inline-editor").show(),a('tbody th.check-column input[type="checkbox"]').each(function(){if(a(this).prop("checked")){e=!1;var b,d=a(this).val();b=a("#inline_"+d+" .post_title").html()||inlineEditL10n.notitle,c+='<div id="ttle'+d+'"><a id="_'+d+'" class="ntdelbutton" title="'+inlineEditL10n.ntdeltitle+'">X</a>'+b+"</div>"}}),e?this.revert():(a("#bulk-titles").html(c),a("#bulk-titles a").click(function(){var b=a(this).attr("id").substr(1);a('table.widefat input[value="'+b+'"]').prop("checked",!1),a("#ttle"+b).remove()}),"post"===d&&(b="post_tag",a('tr.inline-editor textarea[name="tax_input['+b+']"]').suggest(ajaxurl+"?action=ajax-tag-search&tax="+b,{delay:500,minchars:2,multiple:!0,multipleSep:inlineEditL10n.comma+" "})),void a("html, body").animate({scrollTop:0},"fast"))},edit:function(b){var c,d,e,f,g,h,i,j,k,l,m=this,n=!0;for(m.revert(),"object"==typeof b&&(b=m.getId(b)),c=["post_title","post_name","post_author","_status","jj","mm","aa","hh","mn","ss","post_password","post_format","menu_order"],"page"===m.type&&c.push("post_parent","page_template"),d=a("#inline-edit").clone(!0),a("td",d).attr("colspan",a(".widefat:first thead th:visible").length),a(m.what+b).hasClass("alternate")&&a(d).addClass("alternate"),a(m.what+b).hide().after(d),e=a("#inline_"+b),a(':input[name="post_author"] option[value="'+a(".post_author",e).text()+'"]',d).val()||a(':input[name="post_author"]',d).prepend('<option value="'+a(".post_author",e).text()+'">'+a("#"+m.type+"-"+b+" .author").text()+"</option>"),1===a(':input[name="post_author"] option',d).length&&a("label.inline-edit-author",d).hide(),k=a(".post_format",e).text(),a("option.unsupported",d).each(function(){var b=a(this);b.val()!==k&&b.remove()}),l=0;l<c.length;l++)a(':input[name="'+c[l]+'"]',d).val(a("."+c[l],e).text());if("open"===a(".comment_status",e).text()&&a('input[name="comment_status"]',d).prop("checked",!0),"open"===a(".ping_status",e).text()&&a('input[name="ping_status"]',d).prop("checked",!0),"sticky"===a(".sticky",e).text()&&a('input[name="sticky"]',d).prop("checked",!0),a(".post_category",e).each(function(){var c,e=a(this).text();e&&(c=a(this).attr("id").replace("_"+b,""),a("ul."+c+"-checklist :checkbox",d).val(e.split(",")))}),a(".tags_input",e).each(function(){var c=a(this).text(),e=a(this).attr("id").replace("_"+b,""),f=a("textarea.tax_input_"+e,d),g=inlineEditL10n.comma;c&&(","!==g&&(c=c.replace(/,/g,g)),f.val(c)),f.suggest(ajaxurl+"?action=ajax-tag-search&tax="+e,{delay:500,minchars:2,multiple:!0,multipleSep:inlineEditL10n.comma+" "})}),f=a("._status",e).text(),"future"!==f&&a('select[name="_status"] option[value="future"]',d).remove(),"private"===f&&(a('input[name="keep_private"]',d).prop("checked",!0),a("input.inline-edit-password-input").val("").prop("disabled",!0)),g=a('select[name="post_parent"] option[value="'+b+'"]',d),g.length>0){for(h=g[0].className.split("-")[1],i=g;n&&(i=i.next("option"),0!==i.length);)j=i[0].className.split("-")[1],h>=j?n=!1:(i.remove(),i=g);g.remove()}return a(d).attr("id","edit-"+b).addClass("inline-editor").show(),a(".ptitle",d).focus(),!1},save:function(b){var c,d,e=a(".post_status_page").val()||"";return"object"==typeof b&&(b=this.getId(b)),a("table.widefat .spinner").show(),c={action:"inline-save",post_type:typenow,post_ID:b,edit_date:"true",post_status:e},d=a("#edit-"+b).find(":input").serialize(),c=d+"&"+a.param(c),a.post(ajaxurl,c,function(c){a("table.widefat .spinner").hide(),c?-1!==c.indexOf("<tr")?(a(inlineEditPost.what+b).remove(),a("#edit-"+b).before(c).remove(),a(inlineEditPost.what+b).hide().fadeIn()):(c=c.replace(/<.[^<>]*?>/g,""),a("#edit-"+b+" .inline-edit-save .error").html(c).show()):a("#edit-"+b+" .inline-edit-save .error").html(inlineEditL10n.error).show(),a("#post-"+b).prev().hasClass("alternate")&&a("#post-"+b).removeClass("alternate")},"html"),!1},revert:function(){var b=a("table.widefat tr.inline-editor").attr("id");return b&&(a("table.widefat .spinner").hide(),"bulk-edit"===b?(a("table.widefat #bulk-edit").removeClass("inline-editor").hide(),a("#bulk-titles").html(""),a("#inlineedit").append(a("#bulk-edit"))):(a("#"+b).remove(),b=b.substr(b.lastIndexOf("-")+1),a(this.what+b).show())),!1},getId:function(b){var c=a(b).closest("tr").attr("id"),d=c.split("-");return d[d.length-1]}},a(document).ready(function(){inlineEditPost.init()}),a(document).on("heartbeat-tick.wp-check-locked-posts",function(b,c){var d=c["wp-check-locked-posts"]||{};a("#the-list tr").each(function(b,c){var e,f,g=c.id,h=a(c);d.hasOwnProperty(g)?h.hasClass("wp-locked")||(e=d[g],h.find(".column-title .locked-text").text(e.text),h.find(".check-column checkbox").prop("checked",!1),e.avatar_src&&(f=a('<img class="avatar avatar-18 photo" width="18" height="18" />').attr("src",e.avatar_src.replace(/&amp;/g,"&")),h.find(".column-title .locked-avatar").empty().append(f)),h.addClass("wp-locked")):h.hasClass("wp-locked")&&h.removeClass("wp-locked").delay(1e3).find(".locked-info span").empty()})}).on("heartbeat-send.wp-check-locked-posts",function(b,c){var d=[];a("#the-list tr").each(function(a,b){b.id&&d.push(b.id)}),d.length&&(c["wp-check-locked-posts"]=d)}).ready(function(){"undefined"!=typeof wp&&wp.heartbeat&&wp.heartbeat.interval(15)})}(jQuery);
/**
 * Attempt to re-color SVG icons used in the admin menu or the toolbar
 *
 */

window.wp = window.wp || {};

wp.svgPainter = ( function( $, window, document, undefined ) {
	'use strict';
	var selector, base64, painter,
		colorscheme = {},
		elements = [];

	$(document).ready( function() {
		// detection for browser SVG capability
		if ( document.implementation.hasFeature( 'http://www.w3.org/TR/SVG11/feature#Image', '1.1' ) ) {
			$( document.body ).removeClass( 'no-svg' ).addClass( 'svg' );
			wp.svgPainter.init();
		}
	});

	/**
	 * Needed only for IE9
	 *
	 * Based on jquery.base64.js 0.0.3 - https://github.com/yckart/jquery.base64.js
	 *
	 * Based on: https://gist.github.com/Yaffle/1284012
	 *
	 * Copyright (c) 2012 Yannick Albert (http://yckart.com)
	 * Licensed under the MIT license
	 * http://www.opensource.org/licenses/mit-license.php
	 */
	base64 = ( function() {
		var c,
			b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
			a256 = '',
			r64 = [256],
			r256 = [256],
			i = 0;

		function init() {
			while( i < 256 ) {
				c = String.fromCharCode(i);
				a256 += c;
				r256[i] = i;
				r64[i] = b64.indexOf(c);
				++i;
			}
		}

		function code( s, discard, alpha, beta, w1, w2 ) {
			var tmp, length,
				buffer = 0,
				i = 0,
				result = '',
				bitsInBuffer = 0;

			s = String(s);
			length = s.length;

			while( i < length ) {
				c = s.charCodeAt(i);
				c = c < 256 ? alpha[c] : -1;

				buffer = ( buffer << w1 ) + c;
				bitsInBuffer += w1;

				while( bitsInBuffer >= w2 ) {
					bitsInBuffer -= w2;
					tmp = buffer >> bitsInBuffer;
					result += beta.charAt(tmp);
					buffer ^= tmp << bitsInBuffer;
				}
				++i;
			}

			if ( ! discard && bitsInBuffer > 0 ) {
				result += beta.charAt( buffer << ( w2 - bitsInBuffer ) );
			}

			return result;
		}

		function btoa( plain ) {
			if ( ! c ) {
				init();
			}

			plain = code( plain, false, r256, b64, 8, 6 );
			return plain + '===='.slice( ( plain.length % 4 ) || 4 );
		}

		function atob( coded ) {
			var i;

			if ( ! c ) {
				init();
			}

			coded = coded.replace( /[^A-Za-z0-9\+\/\=]/g, '' );
			coded = String(coded).split('=');
			i = coded.length;

			do {
				--i;
				coded[i] = code( coded[i], true, r64, a256, 6, 8 );
			} while ( i > 0 );

			coded = coded.join('');
			return coded;
		}

		return {
			atob: atob,
			btoa: btoa
		};
	})();

	return {
		init: function() {
			painter = this;
			selector = $( '#adminmenu .wp-menu-image, #wpadminbar .ab-item' );

			this.setColors();
			this.findElements();
			this.paint();
		},

		setColors: function( colors ) {
			if ( typeof colors === 'undefined' && typeof window._wpColorScheme !== 'undefined' ) {
				colors = window._wpColorScheme;
			}

			if ( colors && colors.icons && colors.icons.base && colors.icons.current && colors.icons.focus ) {
				colorscheme = colors.icons;
			}
		},

		findElements: function() {
			selector.each( function() {
				var $this = $(this), bgImage = $this.css( 'background-image' );

				if ( bgImage && bgImage.indexOf( 'data:image/svg+xml;base64' ) != -1 ) {
					elements.push( $this );
				}
			});
		},

		paint: function() {
			// loop through all elements
			$.each( elements, function( index, $element ) {
				var $menuitem = $element.parent().parent();

				if ( $menuitem.hasClass( 'current' ) || $menuitem.hasClass( 'wp-has-current-submenu' ) ) {
					// paint icon in 'current' color
					painter.paintElement( $element, 'current' );
				} else {
					// paint icon in base color
					painter.paintElement( $element, 'base' );

					// set hover callbacks
					$menuitem.hover(
						function() {
							painter.paintElement( $element, 'focus' );
						},
						function() {
							// Match the delay from hoverIntent
							window.setTimeout( function() {
								painter.paintElement( $element, 'base' );
							}, 100 );
						}
					);
				}
			});
		},

		paintElement: function( $element, colorType ) {
			var xml, encoded, color;

			if ( ! colorType || ! colorscheme.hasOwnProperty( colorType ) ) {
				return;
			}

			color = colorscheme[ colorType ];

			// only accept hex colors: #101 or #101010
			if ( ! color.match( /^(#[0-9a-f]{3}|#[0-9a-f]{6})$/i ) ) {
				return;
			}

			xml = $element.data( 'wp-ui-svg-' + color );

			if ( xml === 'none' ) {
				return;
			}

			if ( ! xml ) {
				encoded = $element.css( 'background-image' ).match( /.+data:image\/svg\+xml;base64,([A-Za-z0-9\+\/\=]+)/ );

				if ( ! encoded || ! encoded[1] ) {
					$element.data( 'wp-ui-svg-' + color, 'none' );
					return;
				}

				try {
					if ( 'atob' in window ) {
						xml = window.atob( encoded[1] );
					} else {
						xml = base64.atob( encoded[1] );
					}
				} catch ( error ) {}

				if ( xml ) {
					// replace `fill` attributes
					xml = xml.replace( /fill="(.+?)"/g, 'fill="' + color + '"');

					// replace `style` attributes
					xml = xml.replace( /style="(.+?)"/g, 'style="fill:' + color + '"');

					// replace `fill` properties in `<style>` tags
					xml = xml.replace( /fill:.*?;/g, 'fill: ' + color + ';');

					if ( 'btoa' in window ) {
						xml = window.btoa( xml );
					} else {
						xml = base64.btoa( xml );
					}

					$element.data( 'wp-ui-svg-' + color, xml );
				} else {
					$element.data( 'wp-ui-svg-' + color, 'none' );
					return;
				}
			}

			$element.attr( 'style', 'background-image: url("data:image/svg+xml;base64,' + xml + '") !important;' );
		}
	};

})( jQuery, window, document );

!function(a){function b(){var b,d=a("#wp-auth-check"),f=a("#wp-auth-check-form"),g=e.find(".wp-auth-fallback-expired"),h=!1;f.length&&(a(window).on("beforeunload.wp-auth-check",function(a){a.originalEvent.returnValue=window.authcheckL10n.beforeunload}),b=a('<iframe id="wp-auth-check-frame" frameborder="0">').attr("title",g.text()),b.load(function(){var b,i;h=!0;try{i=a(this).contents().find("body"),b=i.height()}catch(j){return e.addClass("fallback"),d.css("max-height",""),f.remove(),void g.focus()}b?i&&i.hasClass("interim-login-success")?c():d.css("max-height",b+40+"px"):i&&i.length||(e.addClass("fallback"),d.css("max-height",""),f.remove(),g.focus())}).attr("src",f.data("src")),a("#wp-auth-check-form").append(b)),e.removeClass("hidden"),b?(b.focus(),setTimeout(function(){h||(e.addClass("fallback"),f.remove(),g.focus())},1e4)):g.focus()}function c(){a(window).off("beforeunload.wp-auth-check"),"undefined"==typeof adminpage||"post-php"!==adminpage&&"post-new-php"!==adminpage||"undefined"==typeof wp||!wp.heartbeat||wp.heartbeat.connectNow(),e.fadeOut(200,function(){e.addClass("hidden").css("display",""),a("#wp-auth-check-frame").remove()})}function d(){var a=parseInt(window.authcheckL10n.interval,10)||180;f=(new Date).getTime()+1e3*a}var e,f;a(document).on("heartbeat-tick.wp-auth-check",function(a,f){"wp-auth-check"in f&&(d(),!f["wp-auth-check"]&&e.hasClass("hidden")?b():f["wp-auth-check"]&&!e.hasClass("hidden")&&c())}).on("heartbeat-send.wp-auth-check",function(a,b){(new Date).getTime()>f&&(b["wp-auth-check"]=!0)}).ready(function(){d(),e=a("#wp-auth-check-wrap"),e.find(".wp-auth-check-close").on("click",function(){c()})})}(jQuery);

rowIndex = 0;

function hideSelects(visibility){
   selects = document.getElementsByTagName('select');
   for(i = 0; i < selects.length; i++) {
		   selects[i].style.visibility = visibility;
	}
}

function allSelect(id){
	var ms	=	id?document.getElementById(id):document;
	var	colInputs = ms.getElementsByTagName("input");
	for	(var i=0; i < colInputs.length; i++)
	{
		colInputs[i].checked= true;
	}
}
function allUnSelect(id){
	var ms	=	id?document.getElementById(id):document;
	var	colInputs = ms.getElementsByTagName("input");
	for	(var i=0; i < colInputs.length; i++)
	{
		colInputs[i].checked= false;
	}
}


function checkAll(allname,onename){
    if($(":input[name='"+allname+"']").prop("checked") == true){
        $(":input[name='"+onename+"']").prop("checked",true);
    }else{
        $(":input[name='"+onename+"']").prop("checked",false);
    }
}


function checkOne(allname,onename){
    var onenamelength = $(":input[name='"+onename+"']").length;     
    if($(":input[name='"+onename+"']").prop("checked") == true){
              
        if($(":input[name='"+onename+"']:checked").length == onenamelength){
            $(":input[name='"+allname+"']").prop("checked",true);
        }        
    }else{
        if($(":input[name='"+allname+"']").prop("checked") == true){
            $(":input[name='"+allname+"']").prop("checked",false);
        }
    }
}

function InverSelect(id){
	var ms	=	id?document.getElementById(id):document;
	var	colInputs = ms.getElementsByTagName("input");
	for	(var i=0; i < colInputs.length; i++)
	{
		colInputs[i].checked= !colInputs[i].checked;
	}
}

function show(){
	if (document.getElementById('menu').style.display!='none')
	{
	document.getElementById('menu').style.display='none';
	document.getElementById('main').className = 'full';
	}else {
	document.getElementById('menu').style.display='inline';
	document.getElementById('main').className = 'main';
	}
}

function CheckAll(strSection){
	var i;
	var	colInputs = document.getElementById(strSection).getElementsByTagName("input");
	for	(i=1; i < colInputs.length; i++)
	{
		colInputs[i].checked=colInputs[0].checked;
	}
}
	
function returnIndex(){	
    var params = gerParams();    
	location.href  = URL+"/index/"+params;	
}

function returnList(url){	
    var params = gerParams();    
    if(url == ""){
       url = URL+"/index/";
    }else if(url == URL || url == (URL+"/")){       
       url = URL+"/index/";
    }
    if(url.lastIndexOf("/") != (url.length-1) ){
        url += "/";
    }
    location.href  = url+params;
}
	
function add(){
    var params = gerParams();
	location.href  = URL+"/add/"+params;
}

function imports(){
    var params = gerParams();
	location.href  = URL+"/import/"+params;
}

function exports(){
    var params = gerParams();
    var relation = $('input[name="relation"]:checked').val();
	location.href  = URL+"/export/relation/"+relation+"/"+params;
}

function pre_edit(key,id){
    document.form1['mode'].value = 'edit';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();	
}

function data_access_edit(key1,value1,key2,value2){
    document.form1['mode'].value = 'dataaccessedit';
    if(key1 != "" && value1 != "") {
        document.form1[key1].value = value1;
    }
    if(key2 != "" && value2 != "") {
        document.form1[key2].value = value2;
    }
    document.form1.submit();
}

function pre_edit_form(key,id){
    document.form1['mode'].value = 'edit_form';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();	
}

function up(key,id){
	var length = arguments.length;
	if(length > 2){
		document.form1['mode'].value = arguments[2];
	}else{
    	document.form1['mode'].value = 'up';
	}
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();	
}

function down(key,id){
	var length = arguments.length;
	if(length > 2){
		document.form1['mode'].value = arguments[2];
	}else{
    	document.form1['mode'].value = 'down';
	}
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();	
}

function treedelete(key,id){
    document.form1['mode'].value = 'delete';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();
}

function data_access_delete(key1,value1,key2,value2){
    document.form1['mode'].value = 'dataaccessdelete';    
    if(key1 != "" && value1 != "") {
        document.form1[key1].value = value1;
    }
    if(key2 != "" && value2 != "") {
        document.form1[key2].value = value2;
    }    
    document.form1.submit();
}

function edit(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/edit/"+key+"/"+keyValue + '/' + params;
}

function modifyProfile(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/modifyProfile/"+key+"/"+keyValue + '/' + params;
}

function view(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/view/"+key+"/"+keyValue + '/' + params;
}

function viewUser(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/User/view/"+key+"/"+keyValue + '/' + params;
}

function viewDoorController(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/DoorController/view/"+key+"/"+keyValue + '/' + params;
}

//查看门禁出入记录
function openRecord(key,id){
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    //alert(URL);
    location.href =  APP+"/OpenRecord/index/"+key+"/"+keyValue + '/' + params;
}

function PopModalWindow(url,width,height){
	var result=window.showModalDialog(url,"win","dialogWidth:"+width+"px;dialogHeight:"+height+"px;center:yes;status:no;scroll:no;dialogHide:no;resizable:no;help:no;edge:sunken;");
	return result;
}

function showHideSearch(){
	if (document.getElementById('searchM').style.display=='inline')
	{
		document.getElementById('searchM').style.display='none';
		document.getElementById('showText').value ='高级';
		//document.getElementById('key').style.display='inline';
	}else {
		document.getElementById('searchM').style.display='inline';
		document.getElementById('showText').value ='隐藏';
		//document.getElementById('key').style.display='none';

	}
}

function sortBy (field,sort){
	var params = gerParams();
	location.href = URL+"/index/_order/"+field+"/_sort/"+sort + '/' + params ;
}

function forbid(key,id){
    var keyValue;
    var params = gerParams();
    
    if (id)
	{
		keyValue = id;
	} else {
	    alert('请选择要禁用的项目！');
		return false;
	}    
	
	if (window.confirm('确实要禁用选择项吗？'))
	{
	   location.href = URL+"/forbid/"+key+"/" + id + '/' + params;
	}
}

function recycle(key,id){
	var params = gerParams();
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValue();
	}
	if (!keyValue)
	{
		alert('请选择要还原的项目！');
		return false;
	}
	location.href = URL+"/recycle/"+key+"/"+keyValue + '/' + params;
}

function resume(key,id){
	var params = gerParams();
	location.href = URL+"/resume/"+key+"/" + id + '/' + params;
}

function output(){
	location.href = URL+"/output/";
}

function read(key,id){
	var params = gerParams();
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValue();
	}
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/read/"+key+"/" + keyValue + '/' + params;
}

var selectRowIndex = Array();
function del(key,id){
	var params = gerParams();
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{
		location.href =  URL+"/del/"+key+"/"+keyValue+'/'+params;
		//ThinkAjax.send(URL+"/delete/",key+"="+keyValue+'&ajax=1',doDelete);
	}
}

function foreverdel(key,id){
	var params = gerParams();
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要永久删除选择项吗？删除后将无法还原！'))
	{
		location.href =  URL+"/foreverdel/"+key+"/"+keyValue+'/'+params;
	}
}

function getTableRowIndex(obj){ 
	selectRowIndex[0] =obj.parentElement.parentElement.rowIndex;/*当前行对象*/
}

function doDelete(data,status){
		if (status==1)
		{
		var Table = $('checkList');
		var len	=	selectRowIndex.length;
		for (var i=len-1;i>=0;i-- )
		{
			//删除表格行
			Table.deleteRow(selectRowIndex[i]);
		}
		selectRowIndex = Array();
		}

}

function delAttach(id,showId){
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{
		$('result').style.display = 'block';
		ThinkAjax.send(URL+"/delAttach/","id="+keyValue+'&_AJAX_SUBMIT_=1');
		if (showId != undefined)
		{
			$(showId).innerHTML = '';
		}
	}
}

function clearData(){
	if (window.confirm('确实要清空全部数据吗？'))
	{
	location.href = URL+"/clear/";
	}
}
function takeback(key,id){
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValues();
	}
	if (!keyValue)
	{
		alert('请选择回收项！');
		return false;
	}

	if (window.confirm('确实要回收选择项吗？'))
	{
	location.href = URL+"/takeback/"+key+"/"+keyValue;
	}
}


function getSelectCheckboxValue(){
	var obj = document.getElementsByName('key');
	var result ='';
	for (var i=0;i<obj.length;i++)
	{
		if (obj[i].checked==true)
				return obj[i].value;

	}
	return false;
}

function getSelectCheckboxValues(){
	var obj = document.getElementsByName('key');
	var result ='';
	var j= 0;
	for (var i=0;i<obj.length;i++)
	{
		if (obj[i].checked==true){
				selectRowIndex[j] = i+1;
				result += obj[i].value+",";
				j++;
		}
	}
	return result.substring(0, result.length-1);
}

function searchItem(item){
	for(i=0;i<selectSource.length;i++)
		if (selectSource[i].text.indexOf(item)!=-1)
		{selectSource[i].selected = true;break;}
}

function addItem(){
	for(i=0;i<selectSource.length;i++)
		if(selectSource[i].selected){
			selectTarget.add( new Option(selectSource[i].text,selectSource[i].value));
			}
		for(i=0;i<selectTarget.length;i++)
			for(j=0;j<selectSource.length;j++)
				if(selectSource[j].text==selectTarget[i].text)
					selectSource[j]=null;
}

function delItem(){
	for(i=0;i<selectTarget.length;i++)
		if(selectTarget[i].selected){
		selectSource.add(new Option(selectTarget[i].text,selectTarget[i].value));
		
		}
		for(i=0;i<selectSource.length;i++)
			for(j=0;j<selectTarget.length;j++)
			if(selectTarget[j].text==selectSource[i].text) selectTarget[j]=null;
}

function delAllItem(){
	for(i=0;i<selectTarget.length;i++){
		selectSource.add(new Option(selectTarget[i].text,selectTarget[i].value));
		
	}
	selectTarget.length=0;
}
function addAllItem(){
	for(i=0;i<selectSource.length;i++){
		selectTarget.add(new Option(selectSource[i].text,selectSource[i].value));
		
	}
	selectSource.length=0;
}

function getReturnValue(){
	for(i=0;i<selectTarget.length;i++){
		selectTarget[i].selected = true;
	}
}

//传递search page参数。暂时不支持checkbox和select
function gerParams(){
	var params = '';	
	$(':input[usefor~="search"]').each(function( index ) {
		if($(this).val()){
			params += $(this).attr('name') + '/' + encodeURIComponent($(this).val()) + '/';
		}
	});
	return params;
}

//form=form id   action=php action名 button 表示点击了哪个按钮 submit表示点击了提交按钮 save表示点击了保存按钮
function save(form,action){
	params = gerParams();
	url = URL+"/"+action+"/"+params;
	//修改form action url
	$("#"+form).attr('action',url);
	//提交
	$('#'+form).submit();
}

function fnModeSubmit(mode, keyname, keyid) {
    document.form1['mode'].value = mode;    
    if(keyname != "" && keyid != "") {
        document.form1[keyname].value = keyid;
    }    
    document.form1.submit();
}

function fnSubmit(mode) {
    document.form1['mode'].value = mode;     
    document.form1.submit();
}

function assignrole(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择分配角色的用户！');
		return false;
	}
	location.href =  URL+"/assignrole/"+key+"/"+keyValue + '/' + params;	
}

function assigndepartment(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择分配部门的用户！');
		return false;
	}
	location.href =  URL+"/assigndepartment/"+key+"/"+keyValue + '/' + params;	
}


function assigncategoryrole(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择分配角色分类的客户！');
		return false;
	}
	location.href =  URL+"/assigncategoryrole/"+key+"/"+keyValue + '/' + params;	
}

//联系人态度
function customeruserbid(key,id){	
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择用户的客户！');
		return false;
	}	
	location.href =  APP+"/CustomerUser/bidlist/"+key+"/"+keyValue + '/' + params;
}

//客户联系人列表
function customeruserlist(key,id){	
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择用户的客户！');
		return false;
	}	
	location.href =  APP+"/CustomerUser/index/"+key+"/"+keyValue + '/' + params;
}
//查看该客户经理对该客户所立的项目

function projectApprovalList(customerKey,customerId,ownerKey,ownerId){
    //var params = gerParams();
    location.href =  APP+"/Project/index/"+customerKey+"/"+customerId + '/report_status/4/';
    //alert();
}

//客户用户分配角色
function assigncususerrole(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;	
	if (!keyValue)
	{
		alert('请选择分配角色的用户！');
		return false;
	}
	location.href =  URL+"/assignrole/"+key+"/"+keyValue + '/' + params;	
}

//客户用户变动历史
function cususerchangehistory(key,id){
	var params = gerParams();
	var keyValue;
	keyValue = id;	
	if (!keyValue)
	{
		alert('请选择分配角色的用户！');
		return false;
	}
	location.href =  URL+"/changehistory/"+key+"/"+keyValue + '/' + params;	
}


function assignnode(key,id){
    document.form1['mode'].value = 'nodelist';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();		
}

function fnSetFormVal(form, key, val) {
	document.forms[form][key].value = val;
}

function assignuser(key,id){
    document.form1['mode'].value = 'userlist';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();		
}

function admin_form(key,id){
    document.form1['mode'].value = 'admin_form';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();		
}
//关联表单
function saveRelation(){
    document.form1['mode'].value = 'saveRelation';
    var deputy_table = $('#relationForm').val();//关联表单的值
    var relation_type = $('#relationType').val(); //关联方式
    $('#deputy_table').val(deputy_table);
    $('#relation_type').val(relation_type);
    //alert(deputy_table);
    //alert(relation_type);
    //return false;
    document.form1.submit();
}
function deleteusers(key1,value1,key2,value2){
    document.form1['mode'].value = 'deleteusers';
    if(key1 != "" && value1 != "") {
        document.form1[key1].value = value1;
    }
    if(key2 != "" && value2 != "") {
        document.form1[key2].value = value2;
    }    
    document.form1.submit();		
}

function cloneblock(id){ 
	var cloneblock = $("#"+id).html();
	$("#selectblock").append(cloneblock);
}

function selectForms(forms){
    $('#forms'+forms).val(forms);
    
}

function gerParamsByForm(form){
	var params = '';
	//select标签
	var selectLength = $('#'+form+' select').length;
	var selectArr = new Array;
	for(var i = 0;i<selectLength;i++){
	   var selectId = $('#'+form+' select').eq(i).attr('id');
	   //$.inArray判断值在不在数组中
	   if($.inArray(selectId,selectArr)<0){
	       selectArr.push(selectId);
	       var selected = $('#'+form+' #'+selectId).val();
	       if(selected != ''){
            params += $('#'+form+' #'+selectId).attr('name')+'/'+encodeURIComponent($('#'+form+' #'+selectId).val()) + '/';
        }
    }
 }
 //checkbox标签
 var checkboxLength = $('#'+form+' input:checkbox').length;
 var checkboxNameArr = new Array;
 for(var i = 0;i<checkboxLength;i++){
	   var checkboxName = $('#'+form+' input:checkbox').eq(i).attr('name');
	   //$.inArray判断值在不在数组中
	   if($.inArray(checkboxName,checkboxNameArr)<0){
	       checkboxNameArr.push(checkboxName);
    }
 }
 $.each(checkboxNameArr, function( key, value ) {
    var length = $("#"+form+" input[name='"+value+"']:checked").length;
    if(length != 0){
        var tempStr = '';
        $("#"+form+" input[name='"+value+"']:checked").each(function() {
            tempStr += $(this).val()+'-';
        });
        
        tempStr = tempStr.substr(0,tempStr.length-1);
        params += value.substr(0,value.length-2)+'/'+encodeURIComponent(tempStr) + '/';
    }
    
 });
 //radio
 var radioLength = $('#'+form+' input:radio').length;
 var radioNameArr = new Array;
 for(var i = 0;i<radioLength;i++){
	   var radioName = $('#'+form+' input:radio').eq(i).attr('name');
	   //$.inArray判断值在不在数组中
	   if($.inArray(radioName,radioNameArr)<0){
	       radioNameArr.push(radioName);
    }
 }
 $.each(radioNameArr, function( key, value ) {
    //var flag = $("#"+form+" input[name='"+value+"'"]).is(":checked");
    //alert(flag);
    var tempStr = $("#"+form+" input[name='"+value+"']:checked").val();
    if(typeof(tempStr) != "undefined" && tempStr!='null'){
        params += value+'/'+encodeURIComponent(tempStr) + '/';
    }
 });
 
	$('input[usefor~="searchForms'+form+'"]').each(function( index ) {
		if($(this).val()){
    params += $(this).attr('name')+'/' + encodeURIComponent($(this).val()) + '/';
		}
	});
	return params;
}
function selectData(forms,action,form){
    //id$danxuan$xialakuang|select|1=>男:2=>女:$fuxuankuang|checkbox|1=>男:2=>女:$radio|radio|1=>男:2=>女:$create_time|time$
    $('#'+form).css('display','');
    var params = gerParamsByForm(form);
    var tempUrl = URL;
    postUrl= tempUrl+'/'+action+'/'+params;
    
    var html = $('#showField'+form).val();
    $.post(postUrl,params,function(data){
        //var tempHtml = html.replace(/\<!--/,'');
        //var tempHtml = tempHtml.replace(/--\>/,'');
        //id$shuliang$danwei$jiage$addTime|time$jine$create_time|time$
        var tempArr = html.split('$');
        var resultHtml = '';
        if(data.list != null){
            for(var i=0;i<data.list.length;i++){
                resultHtml +='<tr>';
                for(var j = 0;j< tempArr.length;j++){
                    if(tempArr[j]!=''){
                        var tempArr2 = tempArr[j].split('|');
                        if(tempArr2[1] == 'time'){
                            resultHtml += '<td>'+unix_to_datetime(data.list[i][tempArr2[0]])+'</td>';
                        }else if(tempArr2[1] == 'select' || tempArr2[1] == 'radio' || tempArr2[1] == 'checkbox'){
                            var arr = tempArr2[2].split(':');
                            var selectArr = new Array;
                            for(var k=0;k<arr.length;k++){
                                if(arr[k]!=''){
                                    var arr1 = arr[k].split('=>');
                                    selectArr[arr1[0]] = arr1[1];
                                }
                            }
                            if(tempArr2[1] == 'checkbox'){
                                var checkStr = '';
                                //data.list[i][tempArr2[0]]的格式为1-2
                                var dataArr = data.list[i][tempArr2[0]].split('-');
                                for(var m=0;m< dataArr.length;m++){
                                    checkStr +=selectArr[dataArr[m]]+' ';
                                }
                                resultHtml += '<td>'+checkStr+'</td>';
                            }else{
                                resultHtml += '<td>'+selectArr[data.list[i][tempArr2[0]]]+'</td>';
                            }
                            
                        }else if(tempArr2[1] == 'baifen'){
                            //百分比
                            resultHtml += '<td>'+data.list[i][tempArr2[0]]*100+'%'+'</td>';
                        }else{
                            resultHtml += '<td>'+data.list[i][tempArr[j]]+'</td>';
                        }
                    }
                }
                resultHtml += '</tr>';
            }
        }else{
            resultHtml = '<tr><td>暂无数据！</td></tr>';
        }
        
        
        $('#'+form +' table>tbody').html(resultHtml);
        $('#'+form).css('display','');
    },'JSON');
    //修改form action url
    
}
//把时间戳转化为固定格式的时间
function unix_to_datetime(unix) {
    var now = new Date(parseInt(unix) * 1000);
    return now.toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
}
//把固定时间转化为时间戳
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}
//把时间戳转化为只有年月日的时间
function unix_to_datetime_type(unix) {
    var now = new Date(parseInt(unix) * 1000);
    //alert(now.toLocaleString().substr(0,10));
    var year = now.getFullYear();
    var month = parseInt( now.getMonth() + 1).toString();
    month = month.length > 1 ? month : "0"+month;
    var date = now.getDate().toString();
    date = date.length > 1 ? date : "0"+ date;
    return year+"-"+month+"-"+date;
    //return now.toLocaleString().substr(0,10).replace(/\//g, "-");
}
//计算两个时间相差的天数
function GetDateDiff(startTime, endTime, diffType) {
//将xxxx-xx-xx的时间格式，转换为 xxxx/xx/xx的格式
startTime = startTime.replace(/-/g, "/");
endTime = endTime.replace(/-/g, "/");
//将计算间隔类性字符转换为小写
diffType = diffType.toLowerCase();
var sTime = new Date(startTime); //开始时间
var eTime = new Date(endTime); //结束时间
//作为除数的数字
var divNum = 1;
switch (diffType) {
    case "day":
    divNum = 1000 * 3600 * 24;
    break;
    default:
    break;
}
if($.trim(startTime) == '00:00:00' || $.trim(endTime) == '00:00:00'){
    return 0;
}else{
    return parseInt((eTime.getTime() - sTime.getTime()) / parseInt(divNum));
}

} 
//隐藏弹出层
function hide(vod){
    $('#'+vod).css('display','none');
}

function deal(taskid, action, search_id,flow_id){
	
	if(search_id==-1){
	    //查看详情
		location.href  = APP+"/"+action +"/historyDetail/taskid/"+taskid+"/flowid/"+flow_id;
		return;
	}else if(search_id !=0) {
	     //编辑状态
		location.href  = APP+"/"+action +"/edit/id/"+flow_id+"/search_pid/"+ search_id;	
	   
	}
	else if(search_id ==0){
	    //处理任务
		location.href  = APP+"/"+action +"/approve/taskid/"+taskid+"/flowid/"+flow_id;	
	}
}

function showDetail(taskid, action){
	
	location.href  = APP+"/"+action +"/view/taskid/"+taskid;	
	
}
//把时间戳转为天数
function unix_to_day(unix) {
    return parseInt(unix)/parseInt(24*3600*1000);
}

	
	
	
//当指针移动到审批意见上面则显示意见DIV
function mouseOver(t,e,data){ 
     //参数含义    
     //t:指当前对象，即超链接<a>  
     //e:event事件  
     //data:要显示的内容  
     var tooltipHtml = "<div id='tooltip' class='tooltip'>"+data+"</div>";  
     $(t).append(tooltipHtml); //添加到页面中    
     $("#tooltip").css({    
          "top": (e.pageY) + "px",    
          "left": (e.pageX) + "px"   
     }).show("fast"); //设置提示框的坐标，并显示   
}
//当指针移开审批意见时则移除意见DIV
function mouseOut(){  
     $("#tooltip").remove();    
}
 
/** 
* 时间对象的格式化 
*/  
Date.prototype.format = function(format)  
{  
/* 
* format="yyyy-MM-dd hh:mm:ss"; 
*/  
var o = {  
"M+" : this.getMonth() + 1,  
"d+" : this.getDate(),  
"h+" : this.getHours(),  
"m+" : this.getMinutes(),  
"s+" : this.getSeconds(),  
"q+" : Math.floor((this.getMonth() + 3) / 3),  
"S" : this.getMilliseconds()  
}  
  
if (/(y+)/.test(format))  
{  
format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4  
- RegExp.$1.length));  
}  
  
for (var k in o)  
{  
if (new RegExp("(" + k + ")").test(format))  
{  
format = format.replace(RegExp.$1, RegExp.$1.length == 1  
? o[k]  
: ("00" + o[k]).substr(("" + o[k]).length));  
}  
}  
return format;  
}  
function fnFormModeSubmit(form, mode, keyname, keyid) {
    document.forms[form]['mode'].value = mode;
    if(keyname != "" && keyid != "") {
        document.forms[form][keyname].value = keyid;
    }
    document.forms[form].submit();
}


//取得每个select框的选取值，选择岗位时需要使用
function selectRole(obj){
	var val = $(obj).val();
	var index = $("#selectblock select").index(obj);

	$('#selectblock input[name="role_id[]"]:eq('+index+')').val(val);
}

function viewPropertyMaterialRequirement(key,id){
    var params = '';
	var tempParames = gerParams();	
	if(tempParames != ""){
	    var lastIndex = tempParames.lastIndexOf("/");	  
	    if(lastIndex == tempParames.length-1){
	        tempParames = tempParames.slice(0,-1);
	    }	   
	    var arrTempParames = tempParames.split("/");
	    for(var k=0;k<arrTempParames.length;){
	        params += '/project_'+arrTempParames[k]+"/"+arrTempParames[k+1];
	        k = k+2;
	    }
	}
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择需求分析物业！');
		return false;
	}
	location.href =  APP+"/MaterialRequirement/index/"+key+"/"+keyValue + '/' + params;
}

function delproperty(key1, val1, key2,id){
	var params = gerParams();
	var keyValue;
	if (id)
	{
		keyValue = id;
	}else {
		keyValue = getSelectCheckboxValues();
	}
	if (!keyValue)
	{
		alert('请选择删除项！');
		return false;
	}

	if (window.confirm('确实要删除选择项吗？'))
	{
		location.href =  URL+"/delproperty/"+key1+"/"+val1+"/"+key2+"/"+keyValue+'/'+params;
		//ThinkAjax.send(URL+"/delete/",key+"="+keyValue+'&ajax=1',doDelete);
	}
}

function handoveredit(key,id){    
	var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/handoverEdit/"+key+"/"+keyValue + '/' + params;
}

function ajaxsubmitpage(url,targetid){    
    if(arguments.length == 1){
        var targetid = "#ajaxForm";
    }
    $.post(url,{},function(data){
        $(targetid).replaceWith(data);
    })
    return false;
}

function ajaxreplacesubmitpage(url,targetid){
    if(arguments.length == 1){
        var targetid = "#ajaxForm";
    }
    $.post(url,{},function(data){
        $(targetid).replaceWith(data);
        var is_maintain = $("#ajaxForm :hidden[name='is_maintain']").val(); 
        fnFormDataReplaceWithTable(is_maintain);
    })
    return false;
}

//查看品牌报备详情
function viewbrandreportstatis(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择查看的项！');
		return false;
	}
	location.href =  URL+"/viewByStatic/"+key+"/"+keyValue + '/' + params;
}

//物料用户权限价格设置
function materialuserprice(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/materialUserPrice/"+key+"/"+keyValue + '/' + params;
}

//物料客户价格设置
function materialcustomerprice(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/materialCustomerPrice/"+key+"/"+keyValue + '/' + params;
}

//新增方案配置
function addquotation(key,id){
    var numargs = arguments.length;
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择方案！');
		return false;
	}
	if(numargs > 2){
        location.href =  URL+"/addQuotation/"+key+"/"+keyValue + '/related_scheme_id/'+arguments[2]+"/" + params;
    }else{
        location.href =  URL+"/addQuotation/"+key+"/"+keyValue + '/' + params;
    }
	
}

//编辑方案配置
function editquotation(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择配置项！');
		return false;
	}
	location.href =  URL+"/editQuotation/"+key+"/"+keyValue + '/' + params;
}

//查看方案配置
function viewquotation(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择配置项！');
		return false;
	}
	location.href =  URL+"/viewQuotation/"+key+"/"+keyValue + '/' + params;
}

//查看方案历史配置列表
function viewquotationhistory(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择方案！');
		return false;
	}
	location.href =  URL+"/viewQuotationHistory/"+key+"/"+keyValue + '/' + params;
}

//核价
function verifyprice(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择编辑项！');
		return false;
	}
	location.href =  URL+"/verifyPrice/"+key+"/"+keyValue + '/' + params;
}

//方案核价
function verifyprice(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择核价项！');
		return false;
	}
	location.href =  URL+"/verifyPrice/"+key+"/"+keyValue + '/' + params;
}

//方案核价申请
function applyverifyprice(schemeKey,schemeId,quotationKey,quotationId){
    var params = gerParams();
	var keyValue;
	keyValue = quotationId;
	if (!keyValue)
	{
		alert('请选择申请项！');
		return false;
	}
	location.href =  URL+"/applyVerifyPrice/"+schemeKey+"/"+schemeId + '/' +quotationKey+"/"+quotationId+'/' +  params;
}

//方案终审
function finaljudgment(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择终审项！');
		return false;
	}
	location.href =  URL+"/finalJudgment/"+key+"/"+keyValue + '/' + params;
}

//方案跟踪反馈
function addtrack(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择跟踪项！');
		return false;
	}
	location.href =  URL+"/addTrack/"+key+"/"+keyValue + '/' + params;
}

//方案反馈结果
function addfeedback(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择反馈项！');
		return false;
	}
	location.href =  URL+"/addFeedback/"+key+"/"+keyValue + '/' + params;
}

//签约比例编辑
function signedit(){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择导出项！');
		return false;
	}
	location.href =  URL+"/signEdit/"+key+"/"+keyValue + '/' + params;
}

//方案反馈结果
function exportpdf(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择导出项！');
		return false;
	}
	location.href =  URL+"/exportPdf/"+key+"/"+keyValue + '/' + params;
}

//js保留2位小数，将浮点数四舍五入，取小数点后2位
function changeTwoDecimal(floatvar)
{
    var f_x = parseFloat(floatvar);
    if (isNaN(f_x))
    {
        alert('function:changeTwoDecimal->parameter error');
        return false;
    }
    var f_x = Math.round(floatvar*100)/100;
    return f_x;
}

//品牌报备审核查看
function brandapproveview(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择查看项！');
		return false;
	}
	location.href =  URL+"/approveView/"+key+"/"+keyValue + '/' + params;
}

//品牌报备审核查看
function returnapproveindex(key,id){
    var params = gerParams();    
	location.href  = URL+"/approveIndex/"+params;
}

//外部报备页
function externalreport(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择外部报备项！');
		return false;
	}
	location.href =  URL+"/externalReport/"+key+"/"+keyValue + '/' + params;
}

//结果反馈页
function externalreportfeedback(key,id){
    var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择反馈项！');
		return false;
	}
	location.href =  URL+"/feedback/"+key+"/"+keyValue + '/' + params;
}


function markSignStatus(key,id){
 var params = gerParams();
	var keyValue;
	keyValue = id;
	if (!keyValue)
	{
		alert('请选择！');
		return false;
	}
	location.href =  URL+"/markSignStatus/"+key+"/"+keyValue + '/' + params;
}
//查看已报项目
function showSchemeDetailByCustomerId(key,id){
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
    alert('请选择编辑项！');
    return false;
    }
    window.open(URL+"/showSchemeDetailByCustomerId/"+key+"/"+keyValue + '/' + params);
}

//除法函数，用来得到精确的除法结果
//说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
//调用：accDiv(arg1,arg2)
//返回值：arg1除以arg2的精确结果
function accDiv(arg1,arg2){
    var t1=0,t2=0,r1,r2;
    try{t1=arg1.toString().split(".")[1].length}catch(e){}
    try{t2=arg2.toString().split(".")[1].length}catch(e){}
    with(Math){
        r1=Number(arg1.toString().replace(".",""));
        r2=Number(arg2.toString().replace(".",""));
        return (r1/r2)*pow(10,t2-t1);
    }
}
 
//给Number类型增加一个div方法，调用起来更加 方便。
Number.prototype.div = function (arg){
    return accDiv(this, arg);
}
 
//乘法函数，用来得到精确的乘法结果
//说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
//调用：accMul(arg1,arg2)
//返回值：arg1乘以 arg2的精确结果
function accMul(arg1,arg2)
{
    var m=0,s1=arg1.toString(),s2=arg2.toString();
    try{m+=s1.split(".")[1].length}catch(e){}
    try{m+=s2.split(".")[1].length}catch(e){}
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);
}
 
// 给Number类型增加一个mul方法，调用起来更加方便。
Number.prototype.mul = function (arg){
    return accMul(arg, this);
}
 
//加法函数，用来得到精确的加法结果
//说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
//调用：accAdd(arg1,arg2)
// 返回值：arg1加上arg2的精确结果
function accAdd(arg1,arg2){
    var r1,r2,m;
    try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
    try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
    m=Math.pow(10,Math.max(r1,r2));
    return (arg1*m+arg2*m)/m;
}
 
//给Number类型增加一个add方法，调用起来更加方便。
Number.prototype.add = function (arg){
    return accAdd(arg,this);
}


function isURL(str_url){
    var strRegex="^((https|http|ftp|rtsp|mms)?://)"  
    + "?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?" // ftp的user@  
    + "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184  
    + "|" // 允许IP和DOMAIN（域名）  
    + "([0-9a-z_!~*'()-]+\.)*" // 域名- www.  
    + "([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\." // 二级域名  
    + "[a-z]{2,6})" // first level domain- .com or .museum  
    + "(:[0-9]{1,4})?" // 端口- :80  
    + "((/?)|" // a slash isn't required if there is no file name  
    + "(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$"; 
    // /^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/
    var re=new RegExp(strRegex);

    return re.test(str_url);
}

function fmoney(s, n) {
    n = n > 0 && n <= 20 ? n : 2;  
    s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(2) + "";     
    var l = s.split(".")[0].split("").reverse(), r = s.split(".")[1];  
    t = "";  
    for (i = 0; i < l.length; i++) {  
        t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");  
    }
    return t.split("").reverse().join("") + "." + r;  
} 

//编辑一个流程流转函数
function flowPassingEdit(key1,flow_id,key2,group_id){
var params = gerParams();
location.href =  URL+"/edit/"+key1+"/"+flow_id+"/"+key2+"/"+group_id +'/' + params;	
}



//删除流程中单独的节点
function flowPassingDelete(key,id){
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择删除项！');
        return false;
    }
    location.href =  URL+"/flowPassingDelete/"+key+"/"+keyValue + '/' + params;
}


//按照组删除节点
function delflowpassing(key,keyId,groupKey,groupId){
    var params = gerParams();
    location.href =  URL+"/delflowpassingGroup/"+key+"/"+keyId + '/' +groupKey+'/'+groupId+'/'+ params;
}