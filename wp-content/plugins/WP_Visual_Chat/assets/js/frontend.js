// sent from php : vcht_url,vcht_position,vcht_pageUrl
var vcht_selectionMode = false;
var vcht_documentBody;
var vcht_avatarSel = false;
var nua = navigator.userAgent;
var is_androidNative = ((nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1) && !(nua.indexOf('Chrome') > -1));

var vcht_isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (vcht_isMobile.Android() || vcht_isMobile.BlackBerry() || vcht_isMobile.iOS() || vcht_isMobile.Opera() || vcht_isMobile.Windows());
    }
};


jQuery(window).load(function() {
    jQuery('#vcht_chatframe').css('opacity',0);
    if (vcht_url.substr(0,8) == 'https://' && window.parent.document.location.href.substr(0,7) == 'http://'){
        vcht_url = 'http://'+vcht_url.substr(8,vcht_url.length);
    } else if (vcht_url.substr(0,7) == 'http://' && window.parent.document.location.href.substr(0,8) == 'https://'){
        vcht_url = 'https://'+vcht_url.substr(7,vcht_url.length);
    }
    vcht_documentBody = ((jQuery.browser.chrome) || (jQuery.browser.safari)) ? document.body : document.documentElement;
    if (!vcht_isIframe()) {
        jQuery('body').append('<iframe id="vcht_chatframe" src="'+vcht_url+'" onload="jQuery(\'#vcht_chatframe\').animate({opacity:1},500);" class="vcht_'+vcht_position+'"></iframe>');
        if(vcht_isMobile.any() && navigator.platform.indexOf("iPad") == -1 && jQuery(window).width() <= 480) {
            jQuery('#vcht_chatframe').addClass('vcht_mob');
        }
        if(vcht_isMobile.iOS() && navigator.platform.indexOf("iPhone") > -1) {
            jQuery('#vcht_chatframe').addClass('vcht_iPhone');
        }
        if (vcht_elementShow != "" && vcht_elementShow.length > 0){
            vcht_showElement(vcht_elementShow);
        }
    }
    if (is_androidNative){
        jQuery('#vcht_chatframe').addClass('vcht_android');
    }
    jQuery('*').click(function(e) {
        if (vcht_selectionMode) {
            if (jQuery(this).children().length == 0 || jQuery(this).is('a') || jQuery(this).is('button') || jQuery(this).is('img') || jQuery(this).is('select')) {
                e.preventDefault();
                jQuery('.vcht_selectedDom').removeClass('vcht_selectedDom');
                jQuery(this).addClass('vcht_selectedDom');
                window.parent.vcht_selectDomElement(this);
                vcht_selectionMode = false;
            }

        }
    });

});



function vcht_isIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function vcht_startSelection() {
    vcht_selectionMode = true;
}

function vcht_showElement(el, avatarImg) {
    if (jQuery(el).length > 0) {
        if (jQuery('.vcht_avatarSel').length > 0) {
            vcht_avatarSel = jQuery('.vcht_avatarSel');
        } else {
            if (avatarImg) {
                vcht_avatarSel = jQuery('<div class="vcht_avatarSel" style="background-image: none;"><div class="vcht_avatarArrow"></div>' + avatarImg + '</div>');
            } else {
                vcht_avatarSel = jQuery('<div class="vcht_avatarSel"><div class="vcht_avatarArrow"></div></div>');
            }
        }
        jQuery('body').append(vcht_avatarSel);
        jQuery(el).addClass('vcht_selectedDom');
        if (vcht_isAnyParentFixed(jQuery(el))) {
            if (jQuery(el).position().top - 140 < 0) {
                vcht_avatarSel.addClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).position().top + jQuery(el).outerHeight() + 80,
                    left: jQuery(el).position().left + jQuery(el).outerWidth() / 2
                });
            } else {
                vcht_avatarSel.removeClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).position().top - 80,
                    left: jQuery(el).position().left + jQuery(el).outerWidth() / 2
                });
            }
            jQuery(vcht_documentBody).animate({scrollTop: jQuery(el).position().top - 200}, 500);
        } else {
            if (jQuery(el).offset().top - 140 < 0) {
                vcht_avatarSel.addClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).offset().top + jQuery(el).outerHeight() + 80,
                    left: jQuery(el).offset().left + jQuery(el).outerWidth() / 2
                });
            } else {
                vcht_avatarSel.removeClass('bottom');
                vcht_avatarSel.css({
                    top: jQuery(el).offset().top - 80,
                    left: jQuery(el).offset().left + jQuery(el).outerWidth() / 2
                });
            }
            jQuery(vcht_documentBody).animate({scrollTop: jQuery(el).offset().top - 200}, 500);
        }
    }
}

function vcht_isAnyParentFixed($el, rep) {
    if (!rep) {
        var rep = false;
    }
    try {
        if ($el.parent().length > 0 && $el.parent().css('position') == "fixed") {
            rep = true;
        }
    } catch (e) {
    }
    if (!rep && $el.parent().length > 0) {
        rep = vcht_isAnyParentFixed($el.parent(), rep);
    }
    return rep;
}