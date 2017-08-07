!function(a){a.fn.hoverIntent=function(b,c,d){var e={interval:100,sensitivity:7,timeout:0};e="object"==typeof b?a.extend(e,b):a.isFunction(c)?a.extend(e,{over:b,out:c,selector:d}):a.extend(e,{over:b,out:b,selector:c});var f,g,h,i,j=function(a){f=a.pageX,g=a.pageY},k=function(b,c){return c.hoverIntent_t=clearTimeout(c.hoverIntent_t),Math.abs(h-f)+Math.abs(i-g)<e.sensitivity?(a(c).off("mousemove.hoverIntent",j),c.hoverIntent_s=1,e.over.apply(c,[b])):(h=f,i=g,c.hoverIntent_t=setTimeout(function(){k(b,c)},e.interval),void 0)},l=function(a,b){return b.hoverIntent_t=clearTimeout(b.hoverIntent_t),b.hoverIntent_s=0,e.out.apply(b,[a])},m=function(b){var c=jQuery.extend({},b),d=this;d.hoverIntent_t&&(d.hoverIntent_t=clearTimeout(d.hoverIntent_t)),"mouseenter"==b.type?(h=c.pageX,i=c.pageY,a(d).on("mousemove.hoverIntent",j),1!=d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){k(c,d)},e.interval))):(a(d).off("mousemove.hoverIntent",j),1==d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){l(c,d)},e.timeout)))};return this.on({"mouseenter.hoverIntent":m,"mouseleave.hoverIntent":m},e.selector)}}(jQuery);
var showNotice,adminMenu,columns,validateForm,screenMeta;!function(a,b){adminMenu={init:function(){},fold:function(){},restoreMenuState:function(){},toggle:function(){},favorites:function(){}},columns={init:function(){var b=this;a(".hide-column-tog","#adv-settings").click(function(){var c=a(this),d=c.val();c.prop("checked")?b.checked(d):b.unchecked(d),columns.saveManageColumnsState()})},saveManageColumnsState:function(){var b=this.hidden();a.post(ajaxurl,{action:"hidden-columns",hidden:b,screenoptionnonce:a("#screenoptionnonce").val(),page:pagenow})},checked:function(b){a(".column-"+b).show(),this.colSpanChange(1)},unchecked:function(b){a(".column-"+b).hide(),this.colSpanChange(-1)},hidden:function(){return a(".manage-column").filter(":hidden").map(function(){return this.id}).get().join(",")},useCheckboxesForHidden:function(){this.hidden=function(){return a(".hide-column-tog").not(":checked").map(function(){var a=this.id;return a.substring(a,a.length-5)}).get().join(",")}},colSpanChange:function(b){var c,d=a("table").find(".colspanchange");d.length&&(c=parseInt(d.attr("colspan"),10)+b,d.attr("colspan",c.toString()))}},a(document).ready(function(){columns.init()}),validateForm=function(b){return!a(b).find(".form-required").filter(function(){return""===a("input:visible",this).val()}).addClass("form-invalid").find("input:visible").change(function(){a(this).closest(".form-invalid").removeClass("form-invalid")}).size()},showNotice={warn:function(){var a=commonL10n.warnDelete||"";return confirm(a)?!0:!1},note:function(a){alert(a)}},screenMeta={element:null,toggles:null,page:null,init:function(){this.element=a("#screen-meta"),this.toggles=a(".screen-meta-toggle a"),this.page=a("#wpcontent"),this.toggles.click(this.toggleEvent)},toggleEvent:function(b){var c=a(this.href.replace(/.+#/,"#"));b.preventDefault(),c.length&&(c.is(":visible")?screenMeta.close(c,a(this)):screenMeta.open(c,a(this)))},open:function(b,c){a(".screen-meta-toggle").not(c.parent()).css("visibility","hidden"),b.parent().show(),b.slideDown("fast",function(){b.focus(),c.addClass("screen-meta-active").attr("aria-expanded",!0)})},close:function(b,c){b.slideUp("fast",function(){c.removeClass("screen-meta-active").attr("aria-expanded",!1),a(".screen-meta-toggle").css("visibility",""),b.parent().hide()})}},a(".contextual-help-tabs").delegate("a","click focus",function(b){var c,d=a(this);return b.preventDefault(),d.is(".active a")?!1:(a(".contextual-help-tabs .active").removeClass("active"),d.parent("li").addClass("active"),c=a(d.attr("href")),a(".help-tab-content").not(c).removeClass("active").hide(),void c.addClass("active").show())}),a(document).ready(function(){var c,d,e,f,g,h,i,j,k=!1,l=a("#adminmenu"),m=a("input.current-page"),n=m.val();l.on("click.wp-submenu-head",".wp-submenu-head",function(b){a(b.target).parent().siblings("a").get(0).click()}),a("#collapse-menu").on("click.collapse-menu",function(){var c,d=a(document.body);a("#adminmenu div.wp-submenu").css("margin-top",""),c=b.innerWidth?Math.max(b.innerWidth,document.documentElement.clientWidth):901,c&&900>c?d.hasClass("auto-fold")?(d.removeClass("auto-fold").removeClass("folded"),setUserSetting("unfold",1),setUserSetting("mfold","o")):(d.addClass("auto-fold"),setUserSetting("unfold",0)):d.hasClass("folded")?(d.removeClass("folded"),setUserSetting("mfold","o")):(d.addClass("folded"),setUserSetting("mfold","f"))}),("ontouchstart"in b||/IEMobile\/[1-9]/.test(navigator.userAgent))&&(h=/Mobile\/.+Safari/.test(navigator.userAgent)?"touchstart":"click",a(document.body).on(h+".wp-mobile-hover",function(b){l.data("wp-responsive")||a(b.target).closest("#adminmenu").length||l.find("li.wp-has-submenu.opensub").removeClass("opensub")}),l.find("a.wp-has-submenu").on(h+".wp-mobile-hover",function(c){var d,e,f,g,h,i,j,k=a(this),m=k.parent(),n=m.find(".wp-submenu");l.data("wp-responsive")||m.hasClass("opensub")||m.hasClass("wp-menu-open")&&!(m.width()<40)||(c.preventDefault(),h=m.offset().top,i=a(b).scrollTop(),j=h-i-30,d=h+n.height()+1,e=a("#wpwrap").height(),f=60+d-e,g=a(b).height()+i-50,d-f>g&&(f=d-g),f>j&&(f=j),f>1?n.css("margin-top","-"+f+"px"):n.css("margin-top",""),l.find("li.opensub").removeClass("opensub"),m.addClass("opensub"))})),l.find("li.wp-has-submenu").hoverIntent({over:function(){var c,d,e,f,g,h,i,j=a(this).find(".wp-submenu"),k=parseInt(j.css("top"),10);isNaN(k)||k>-5||l.data("wp-responsive")||(g=a(this).offset().top,h=a(b).scrollTop(),i=g-h-30,c=g+j.height()+1,d=a("#wpwrap").height(),e=60+c-d,f=a(b).height()+h-15,c-e>f&&(e=c-f),e>i&&(e=i),e>1?j.css("margin-top","-"+e+"px"):j.css("margin-top",""),l.find("li.menu-top").removeClass("opensub"),a(this).addClass("opensub"))},out:function(){l.data("wp-responsive")||a(this).removeClass("opensub").find(".wp-submenu").css("margin-top","")},timeout:200,sensitivity:7,interval:90}),l.on("focus.adminmenu",".wp-submenu a",function(b){l.data("wp-responsive")||a(b.target).closest("li.menu-top").addClass("opensub")}).on("blur.adminmenu",".wp-submenu a",function(b){l.data("wp-responsive")||a(b.target).closest("li.menu-top").removeClass("opensub")}),a("div.wrap h2:first").nextAll("div.updated, div.error").addClass("below-h2"),a("div.updated, div.error").not(".below-h2, .inline").insertAfter(a("div.wrap h2:first")),screenMeta.init(),a("tbody").children().children(".check-column").find(":checkbox").click(function(b){if("undefined"==b.shiftKey)return!0;if(b.shiftKey){if(!k)return!0;c=a(k).closest("form").find(":checkbox"),d=c.index(k),e=c.index(this),f=a(this).prop("checked"),d>0&&e>0&&d!=e&&(g=e>d?c.slice(d,e):c.slice(e,d),g.prop("checked",function(){return a(this).closest("tr").is(":visible")?f:!1}))}k=this;var h=a(this).closest("tbody").find(":checkbox").filter(":visible").not(":checked");return a(this).closest("table").children("thead, tfoot").find(":checkbox").prop("checked",function(){return 0===h.length}),!0}),a("thead, tfoot").find(".check-column :checkbox").on("click.wp-toggle-checkboxes",function(b){var c=a(this),d=c.closest("table"),e=c.prop("checked"),f=b.shiftKey||c.data("wp-toggle");d.children("tbody").filter(":visible").children().children(".check-column").find(":checkbox").prop("checked",function(){return a(this).is(":hidden")?!1:f?!a(this).prop("checked"):e?!0:!1}),d.children("thead,  tfoot").filter(":visible").children().children(".check-column").find(":checkbox").prop("checked",function(){return f?!1:e?!0:!1})}),a("td.post-title, td.title, td.comment, .bookmarks td.column-name, td.blogname, td.username, .dashboard-comment-wrap").focusin(function(){clearTimeout(i),j=a(this).find(".row-actions"),j.addClass("visible")}).focusout(function(){i=setTimeout(function(){j.removeClass("visible")},30)}),a("#default-password-nag-no").click(function(){return setUserSetting("default_password_nag","hide"),a("div.default-password-nag").hide(),!1}),a("#newcontent").bind("keydown.wpevent_InsertTab",function(b){var c,d,e,f,g,h=b.target;if(27==b.keyCode)return void a(h).data("tab-out",!0);if(!(9!=b.keyCode||b.ctrlKey||b.altKey||b.shiftKey)){if(a(h).data("tab-out"))return void a(h).data("tab-out",!1);c=h.selectionStart,d=h.selectionEnd,e=h.value;try{this.lastKey=9}catch(i){}document.selection?(h.focus(),g=document.selection.createRange(),g.text="	"):c>=0&&(f=this.scrollTop,h.value=e.substring(0,c).concat("	",e.substring(d)),h.selectionStart=h.selectionEnd=c+1,this.scrollTop=f),b.stopPropagation&&b.stopPropagation(),b.preventDefault&&b.preventDefault()}}),a("#newcontent").bind("blur.wpevent_InsertTab",function(){this.lastKey&&9==this.lastKey&&this.focus()}),m.length&&m.closest("form").submit(function(){-1==a('select[name="action"]').val()&&-1==a('select[name="action2"]').val()&&m.val()==n&&m.val("1")}),a('.search-box input[type="search"], .search-box input[type="submit"]').mousedown(function(){a('select[name^="action"]').val("-1")}),a("#contextual-help-link, #show-settings-link").on("focus.scroll-into-view",function(a){a.target.scrollIntoView&&a.target.scrollIntoView(!1)}),function(){function b(){c.prop("disabled",""===d.map(function(){return a(this).val()}).get().join(""))}var c,d,e=a("form.wp-upload-form");e.length&&(c=e.find('input[type="submit"]'),d=e.find('input[type="file"]'),b(),d.on("change",b))}()}),function(){function c(){a(document).trigger("wp-window-resized")}function d(){b.clearTimeout(e),e=b.setTimeout(c,200)}var e;a(b).on("resize.wp-fire-once",d)}(),a(document).ready(function(){var c=a(document),d=a(b),e=a(document.body),f=a("#adminmenuwrap"),g=a("#collapse-menu"),h=a("#wpwrap"),i=a("#adminmenu"),j=a("#wp-responsive-overlay"),k=a("#wp-toolbar"),l=k.find('a[aria-haspopup="true"]'),m=a(".meta-box-sortables"),n=!1,o=!1;b.stickyMenu={enable:function(){n||(c.on("wp-window-resized.sticky-menu",a.proxy(this.update,this)),g.on("click.sticky-menu",a.proxy(this.update,this)),this.update(),n=!0)},disable:function(){n&&(d.off("resize.sticky-menu"),g.off("click.sticky-menu"),e.removeClass("sticky-menu"),n=!1)},update:function(){d.height()>f.height()+32?e.hasClass("sticky-menu")||e.addClass("sticky-menu"):e.hasClass("sticky-menu")&&e.removeClass("sticky-menu")}},b.wpResponsive={init:function(){var e=this,f=0;c.on("wp-responsive-activate.wp-responsive",function(){e.activate()}).on("wp-responsive-deactivate.wp-responsive",function(){e.deactivate()}),a("#wp-admin-bar-menu-toggle a").attr("aria-expanded","false"),a("#wp-admin-bar-menu-toggle").on("click.wp-responsive",function(b){b.preventDefault(),h.toggleClass("wp-responsive-open"),h.hasClass("wp-responsive-open")?(a(this).find("a").attr("aria-expanded","true"),a("#adminmenu a:first").focus()):a(this).find("a").attr("aria-expanded","false")}),i.on("touchstart.wp-responsive","li.wp-has-submenu > a",function(){f=d.scrollTop()}).on("touchend.wp-responsive click.wp-responsive","li.wp-has-submenu > a",function(b){!i.data("wp-responsive")||"touchend"===b.type&&d.scrollTop()!==f||(a(this).parent("li").toggleClass("selected"),b.preventDefault())}),e.trigger(),c.on("wp-window-resized.wp-responsive",a.proxy(this.trigger,this)),d.on("load.wp-responsive",function(){var a=navigator.userAgent.indexOf("AppleWebKit/")>-1?d.width():b.innerWidth;782>=a&&e.disableSortables()})},activate:function(){b.stickyMenu.disable(),e.hasClass("auto-fold")||e.addClass("auto-fold"),i.data("wp-responsive",1),this.disableSortables()},deactivate:function(){b.stickyMenu.enable(),i.removeData("wp-responsive"),this.enableSortables()},trigger:function(){var a;b.innerWidth&&(a=Math.max(b.innerWidth,document.documentElement.clientWidth),782>=a?o||(c.trigger("wp-responsive-activate"),o=!0):o&&(c.trigger("wp-responsive-deactivate"),o=!1),480>=a?this.enableOverlay():this.disableOverlay())},enableOverlay:function(){0===j.length&&(j=a('<div id="wp-responsive-overlay"></div>').insertAfter("#wpcontent").hide().on("click.wp-responsive",function(){k.find(".menupop.hover").removeClass("hover"),a(this).hide()})),l.on("click.wp-responsive",function(){j.show()})},disableOverlay:function(){l.off("click.wp-responsive"),j.hide()},disableSortables:function(){if(m.length)try{m.sortable("disable")}catch(a){}},enableSortables:function(){if(m.length)try{m.sortable("enable")}catch(a){}}},b.stickyMenu.enable(),b.wpResponsive.init()}),function(){if("-ms-user-select"in document.documentElement.style&&navigator.userAgent.match(/IEMobile\/10\.0/)){var a=document.createElement("style");a.appendChild(document.createTextNode("@-ms-viewport{width:auto!important}")),document.getElementsByTagName("head")[0].appendChild(a)}}()}(jQuery,window);
"undefined"!=typeof jQuery?("undefined"==typeof jQuery.fn.hoverIntent&&!function(a){a.fn.hoverIntent=function(b,c,d){var e={interval:100,sensitivity:7,timeout:0};e="object"==typeof b?a.extend(e,b):a.isFunction(c)?a.extend(e,{over:b,out:c,selector:d}):a.extend(e,{over:b,out:b,selector:c});var f,g,h,i,j=function(a){f=a.pageX,g=a.pageY},k=function(b,c){return c.hoverIntent_t=clearTimeout(c.hoverIntent_t),Math.abs(h-f)+Math.abs(i-g)<e.sensitivity?(a(c).off("mousemove.hoverIntent",j),c.hoverIntent_s=1,e.over.apply(c,[b])):(h=f,i=g,c.hoverIntent_t=setTimeout(function(){k(b,c)},e.interval),void 0)},l=function(a,b){return b.hoverIntent_t=clearTimeout(b.hoverIntent_t),b.hoverIntent_s=0,e.out.apply(b,[a])},m=function(b){var c=jQuery.extend({},b),d=this;d.hoverIntent_t&&(d.hoverIntent_t=clearTimeout(d.hoverIntent_t)),"mouseenter"==b.type?(h=c.pageX,i=c.pageY,a(d).on("mousemove.hoverIntent",j),1!=d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){k(c,d)},e.interval))):(a(d).off("mousemove.hoverIntent",j),1==d.hoverIntent_s&&(d.hoverIntent_t=setTimeout(function(){l(c,d)},e.timeout)))};return this.on({"mouseenter.hoverIntent":m,"mouseleave.hoverIntent":m},e.selector)}}(jQuery),jQuery(document).ready(function(a){var b,c,d,e=a("#wpadminbar"),f=!1;b=function(b,c){var d=a(c),e=d.attr("tabindex");e&&d.attr("tabindex","0").attr("tabindex",e)},c=function(b){e.find("li.menupop").on("click.wp-mobile-hover",function(c){var d=a(this);d.parent().is("#wp-admin-bar-root-default")&&!d.hasClass("hover")?(c.preventDefault(),e.find("li.menupop.hover").removeClass("hover"),d.addClass("hover")):d.hasClass("hover")||(c.stopPropagation(),c.preventDefault(),d.addClass("hover")),b&&(a("li.menupop").off("click.wp-mobile-hover"),f=!1)})},d=function(){var b=/Mobile\/.+Safari/.test(navigator.userAgent)?"touchstart":"click";a(document.body).on(b+".wp-mobile-hover",function(b){a(b.target).closest("#wpadminbar").length||e.find("li.menupop.hover").removeClass("hover")})},e.removeClass("nojq").removeClass("nojs"),"ontouchstart"in window?(e.on("touchstart",function(){c(!0),f=!0}),d()):/IEMobile\/[1-9]/.test(navigator.userAgent)&&(c(),d()),e.find("li.menupop").hoverIntent({over:function(){f||a(this).addClass("hover")},out:function(){f||a(this).removeClass("hover")},timeout:180,sensitivity:7,interval:100}),window.location.hash&&window.scrollBy(0,-32),a("#wp-admin-bar-get-shortlink").click(function(b){b.preventDefault(),a(this).addClass("selected").children(".shortlink-input").blur(function(){a(this).parents("#wp-admin-bar-get-shortlink").removeClass("selected")}).focus().select()}),a("#wpadminbar li.menupop > .ab-item").bind("keydown.adminbar",function(c){if(13==c.which){var d=a(c.target),e=d.closest("ab-sub-wrapper");c.stopPropagation(),c.preventDefault(),e.length||(e=a("#wpadminbar .quicklinks")),e.find(".menupop").removeClass("hover"),d.parent().toggleClass("hover"),d.siblings(".ab-sub-wrapper").find(".ab-item").each(b)}}).each(b),a("#wpadminbar .ab-item").bind("keydown.adminbar",function(c){if(27==c.which){var d=a(c.target);c.stopPropagation(),c.preventDefault(),d.closest(".hover").removeClass("hover").children(".ab-item").focus(),d.siblings(".ab-sub-wrapper").find(".ab-item").each(b)}}),a("#wpadminbar").click(function(b){("wpadminbar"==b.target.id||"wp-admin-bar-top-secondary"==b.target.id)&&(b.preventDefault(),a("html, body").animate({scrollTop:0},"fast"))}),a(".screen-reader-shortcut").keydown(function(b){var c,d;13==b.which&&(c=a(this).attr("href"),d=navigator.userAgent.toLowerCase(),-1!=d.indexOf("applewebkit")&&c&&"#"==c.charAt(0)&&setTimeout(function(){a(c).focus()},100))}),"sessionStorage"in window&&a("#wp-admin-bar-logout a").click(function(){try{for(var a in sessionStorage)-1!=a.indexOf("wp-autosave-")&&sessionStorage.removeItem(a)}catch(b){}}),navigator.userAgent&&-1===document.body.className.indexOf("no-font-face")&&/Android (1.0|1.1|1.5|1.6|2.0|2.1)|Nokia|Opera Mini|w(eb)?OSBrowser|webOS|UCWEB|Windows Phone OS 7|XBLWP7|ZuneWP7|MSIE 7/.test(navigator.userAgent)&&(document.body.className+=" no-font-face")})):!function(a,b){var c,d=function(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent&&a.attachEvent("on"+b,function(){return c.call(a,window.event)})},e=new RegExp("\\bhover\\b","g"),f=[],g=new RegExp("\\bselected\\b","g"),h=function(a){for(var b=f.length;b--;)if(f[b]&&a==f[b][1])return f[b][0];return!1},i=function(b){for(var d,i,j,k,l,m,n=[],o=0;b&&b!=c&&b!=a;)"LI"==b.nodeName.toUpperCase()&&(n[n.length]=b,i=h(b),i&&clearTimeout(i),b.className=b.className?b.className.replace(e,"")+" hover":"hover",k=b),b=b.parentNode;if(k&&k.parentNode&&(l=k.parentNode,l&&"UL"==l.nodeName.toUpperCase()))for(d=l.childNodes.length;d--;)m=l.childNodes[d],m!=k&&(m.className=m.className?m.className.replace(g,""):"");for(d=f.length;d--;){for(j=!1,o=n.length;o--;)n[o]==f[d][1]&&(j=!0);j||(f[d][1].className=f[d][1].className?f[d][1].className.replace(e,""):"")}},j=function(b){for(;b&&b!=c&&b!=a;)"LI"==b.nodeName.toUpperCase()&&!function(a){var b=setTimeout(function(){a.className=a.className?a.className.replace(e,""):""},500);f[f.length]=[b,a]}(b),b=b.parentNode},k=function(b){for(var d,e,f,h=b.target||b.srcElement;;){if(!h||h==a||h==c)return;if(h.id&&"wp-admin-bar-get-shortlink"==h.id)break;h=h.parentNode}for(b.preventDefault&&b.preventDefault(),b.returnValue=!1,-1==h.className.indexOf("selected")&&(h.className+=" selected"),d=0,e=h.childNodes.length;e>d;d++)if(f=h.childNodes[d],f.className&&-1!=f.className.indexOf("shortlink-input")){f.focus(),f.select(),f.onblur=function(){h.className=h.className?h.className.replace(g,""):""};break}return!1},l=function(a){var b,c,d,e,f,g;if(!("wpadminbar"!=a.id&&"wp-admin-bar-top-secondary"!=a.id||(b=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0,1>b)))for(g=b>800?130:100,c=Math.min(12,Math.round(b/g)),d=Math.round(b>800?b/30:b/20),e=[],f=0;b;)b-=d,0>b&&(b=0),e.push(b),setTimeout(function(){window.scrollTo(0,e.shift())},f*c),f++};d(b,"load",function(){c=a.getElementById("wpadminbar"),a.body&&c&&(a.body.appendChild(c),c.className&&(c.className=c.className.replace(/nojs/,"")),d(c,"mouseover",function(a){i(a.target||a.srcElement)}),d(c,"mouseout",function(a){j(a.target||a.srcElement)}),d(c,"click",k),d(c,"click",function(a){l(a.target||a.srcElement)}),d(document.getElementById("wp-admin-bar-logout"),"click",function(){if("sessionStorage"in window)try{for(var a in sessionStorage)-1!=a.indexOf("wp-autosave-")&&sessionStorage.removeItem(a)}catch(b){}})),b.location.hash&&b.scrollBy(0,-32),navigator.userAgent&&-1===document.body.className.indexOf("no-font-face")&&/Android (1.0|1.1|1.5|1.6|2.0|2.1)|Nokia|Opera Mini|w(eb)?OSBrowser|webOS|UCWEB|Windows Phone OS 7|XBLWP7|ZuneWP7|MSIE 7/.test(navigator.userAgent)&&(document.body.className+=" no-font-face")})}(document,window);
!function(a){a.suggest=function(b,c){function d(){var a=o.offset();p.css({top:a.top+b.offsetHeight+"px",left:a.left+"px"})}function e(a){if(/27$|38$|40$/.test(a.keyCode)&&p.is(":visible")||/^13$|^9$/.test(a.keyCode)&&k())switch(a.preventDefault&&a.preventDefault(),a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0,a.returnValue=!1,a.keyCode){case 38:n();break;case 40:m();break;case 9:case 13:l();break;case 27:p.hide()}else o.val().length!=r&&(q&&clearTimeout(q),q=setTimeout(f,c.delay),r=o.val().length)}function f(){var b,d,e=a.trim(o.val());c.multiple&&(b=e.lastIndexOf(c.multipleSep),-1!=b&&(e=a.trim(e.substr(b+c.multipleSep.length)))),e.length>=c.minchars?(cached=g(e),cached?i(cached.items):a.get(c.source,{q:e},function(a){p.hide(),d=j(a,e),i(d),h(e,d,a.length)})):p.hide()}function g(a){var b;for(b=0;b<s.length;b++)if(s[b].q==a)return s.unshift(s.splice(b,1)[0]),s[0];return!1}function h(a,b,d){for(var e;s.length&&t+d>c.maxCacheSize;)e=s.pop(),t-=e.size;s.push({q:a,size:d,items:b}),t+=d}function i(b){var e,f="";if(b){if(!b.length)return void p.hide();for(d(),e=0;e<b.length;e++)f+="<li>"+b[e]+"</li>";p.html(f).show(),p.children("li").mouseover(function(){p.children("li").removeClass(c.selectClass),a(this).addClass(c.selectClass)}).click(function(a){a.preventDefault(),a.stopPropagation(),l()})}}function j(b,d){var e,f,g=[],h=b.split(c.delimiter);for(e=0;e<h.length;e++)f=a.trim(h[e]),f&&(f=f.replace(new RegExp(d,"ig"),function(a){return'<span class="'+c.matchClass+'">'+a+"</span>"}),g[g.length]=f);return g}function k(){var a;return p.is(":visible")?(a=p.children("li."+c.selectClass),a.length||(a=!1),a):!1}function l(){$currentResult=k(),$currentResult&&(c.multiple?($currentVal=-1!=o.val().indexOf(c.multipleSep)?o.val().substr(0,o.val().lastIndexOf(c.multipleSep)+c.multipleSep.length):"",o.val($currentVal+$currentResult.text()+c.multipleSep),o.focus()):o.val($currentResult.text()),p.hide(),o.trigger("change"),c.onSelect&&c.onSelect.apply(o[0]))}function m(){$currentResult=k(),$currentResult?$currentResult.removeClass(c.selectClass).next().addClass(c.selectClass):p.children("li:first-child").addClass(c.selectClass)}function n(){var a=k();a?a.removeClass(c.selectClass).prev().addClass(c.selectClass):p.children("li:last-child").addClass(c.selectClass)}var o,p,q,r,s,t;o=a(b).attr("autocomplete","off"),p=a("<ul/>"),q=!1,r=0,s=[],t=0,p.addClass(c.resultsClass).appendTo("body"),d(),a(window).load(d).resize(d),o.blur(function(){setTimeout(function(){p.hide()},200)}),o.keydown(e)},a.fn.suggest=function(b,c){return b?(c=c||{},c.multiple=c.multiple||!1,c.multipleSep=c.multipleSep||", ",c.source=b,c.delay=c.delay||100,c.resultsClass=c.resultsClass||"ac_results",c.selectClass=c.selectClass||"ac_over",c.matchClass=c.matchClass||"ac_match",c.minchars=c.minchars||2,c.delimiter=c.delimiter||"\n",c.onSelect=c.onSelect||!1,c.maxCacheSize=c.maxCacheSize||65536,this.each(function(){new a.suggest(this,c)}),this):void 0}}(jQuery);

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

function returnList(url, key, id){
    var params = gerParams();    
    if(url == ""){
       url = URL+"/index/";
    }else if(url == URL || url == (URL+"/")){       
       url = URL+"/index/";
    }
    if(url.lastIndexOf("/") != (url.length-1) ){
        url += "/";
    }
    if(key != "" && id != "") {
    	url += (key +"/" + id + "/");
    }
    location.href  = url+params;
}
	
function add(key, id){
    var params = gerParams();
    if(key != "" && id != "") {
        location.href  = URL+"/add/" + key + "/" + id + "/"+params;
    } else {
        location.href  = URL+"/add/"+params;
	}
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

// 查看用户列表
function userlist(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/User/index/"+key+"/"+keyValue + '/' + params;
}

// 查看门禁控制器列表
function doorlist(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/DoorController/index/"+key+"/"+keyValue + '/' + params;
}

function useradd(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/User/add/"+key+"/"+keyValue + '/' + params;
}

function dooradd(key, id) {
    var params = gerParams();
    var keyValue;
    keyValue = id;
    if (!keyValue)
    {
        alert('请选择编辑项！');
        return false;
    }
    location.href =  APP+"/DoorController/add/"+key+"/"+keyValue + '/' + params;
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

function clearData(){
	if (window.confirm('确实要清空全部数据吗？'))
	{
	location.href = URL+"/clear/";
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


function assignnode(key,id){
    document.form1['mode'].value = 'nodelist';
    if(key != "" && id != "") {
        document.form1[key].value = id;
    }
    document.form1.submit();		
}




function fnFormModeSubmit(form, mode, keyname, keyid) {
    document.forms[form]['mode'].value = mode;
    if(keyname != "" && keyid != "") {
        document.forms[form][keyname].value = keyid;
    }
    document.forms[form].submit();
}






