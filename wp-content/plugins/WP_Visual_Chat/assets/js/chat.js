// Sent from php : vcht_settings, vcht_user,vcht_userPic; vcht_ajaxurl,vcht_texts

var vcht_username = false;
var vcht_operatorID = 0;
var vcht_logID = 0;
var vcht_timer;
var vcht_timerBounce = false;
var vcht_isChatting = false;
var vcht_started = false;

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
    jQuery('#chatWrite textarea').css({
        width: jQuery(window).width() - 90
    });
    vcht_timer = false;
    vcht_settings = vcht_settings[0];
    vcht_user = vcht_user[0];
    vcht_username = vcht_user;
    vcht_userPic = vcht_userPic[0];
    vcht_userID = vcht_userID[0];

    if (sessionStorage.vcht_username) {
        vcht_username = sessionStorage.vcht_username;
    }
    if (sessionStorage.vcht_operatorID) {
        vcht_operatorID = parseInt(sessionStorage.vcht_operatorID);
    }
    if (sessionStorage.vcht_userID) {
        vcht_userID = parseInt(sessionStorage.vcht_userID);
    }
    if (sessionStorage.vcht_logID) {
        vcht_logID = parseInt(sessionStorage.vcht_logID);
    }
    vcht_disablesTheme();

    jQuery('body').hide();
    jQuery('body').html(jQuery('#chat').html());
    jQuery('body').attr('class', '');
    jQuery('body').attr('style', '');
    jQuery('body').delay(500).fadeIn(500);

    if (vcht_logID > 0) {
        vcht_initChat();
        vcht_started = true;
    }

    if (jQuery(window.parent.window).width() <= 480 &&vcht_isMobile.any()) {
        jQuery('#chatContent').addClass('mob');
        jQuery('#chatPanel').addClass('mob');
        jQuery('#chatHistory').css({
            minHeight: jQuery(window.parent.window).height() - 150
        });
    }


    jQuery(window).resize(function() {
        if (jQuery(window.parent.window).width() < 410) {
            jQuery('#chatContent').addClass('mob');
            jQuery('#chatHistory').css({
                minHeight: jQuery('#chatContent').outerHeight() - 140
            });
            jQuery('#chatWrite textarea').css({
                width: jQuery(window).width() - 100
            });

        } else {
            jQuery('#chatContent').removeClass('mob');

            jQuery('#chatWrite textarea').css({
                width: 184
            });
        }
    });

    jQuery('#chatHeader').click(function() {
        if (jQuery(this).is('.open')) {
            jQuery(this).removeClass('open');
            jQuery(this).parent().removeClass('open');
            jQuery('.nicescroll-rails').addClass('hidden');
            jQuery(window.parent.document).find('body').removeClass('vcht_open');
            jQuery(window.parent.document).find('#vcht_chatframe').removeClass('vcht_open');
            vcht_initBounce();
            jQuery(window.parent).find('.vcht_avatarSel').fadeOut(200);
            jQuery(window.parent.document).find('.vcht_selectedDom').removeClass('vcht_selectedDom');
            setTimeout(function() {
                jQuery(window.parent.document).find('.vcht_avatarSel').remove();
            }, 500);

        } else {
            if (!vcht_started) {
                vcht_initChat();
                vcht_started = true;
            }
            jQuery(window.parent.document).find('#vcht_chatframe').prop('style', '');
            jQuery(window.parent.document).find('#vcht_chatframe').attr('style', '');
            clearInterval(vcht_timerBounce);
            jQuery(this).addClass('open');
            jQuery(this).parent().addClass('open');
            setTimeout(function() {
                jQuery('.nicescroll-rails').removeClass('hidden');
            }, 400);
            jQuery(window.parent.document).find('body').addClass('vcht_open');
            jQuery(window.parent.document).find('#vcht_chatframe').addClass('vcht_open');
        }
    });
    if (!vcht_isMobile.any()) {
        jQuery('#chatContent').niceScroll({
            cursorwidth: 10,
            autohidemode: false
        });
        jQuery('.nicescroll-rails').addClass('hidden');
        vcht_initBounce();

    }
});

function vcht_initBounce() {
    if (vcht_settings.bounceFx == '1') {
        if (vcht_timerBounce) {
            clearInterval(vcht_timerBounce);
        }
        vcht_timerBounce = setInterval(function() {
            if (!jQuery(window.parent.document).find('#vcht_chatframe').is('.vcht_open')) {
                jQuery(window.parent.document).find('#vcht_chatframe').css('height', 15);
                setTimeout(function() {
                    if (!jQuery(window.parent.document).find('#vcht_chatframe').is('.vcht_open')) {
                        jQuery(window.parent.document).find('#vcht_chatframe').css('height', 45);
                    }
                }, 800);
            }
        }, 5800);
    }
}

function vcht_initTimer() {
    if (vcht_isChatting) {
        if (isNaN(vcht_logID)) {
            vcht_logID = 0;
        }
        vcht_timer = setTimeout(function() {
            jQuery.ajax({
                url: vcht_ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_check_user_chat',
                    logID: vcht_logID
                },
                success: function(repS) {
                    if (repS.indexOf('}') > 0) {
                        var rep = jQuery.parseJSON(repS);
                        vcht_lastRep = rep;

                        jQuery.each(rep.messages, function() {
                            req = this;
                            vcht_operatorSay(req);
                        });

                        if (rep.finished == 1) {
                            vcht_isChatting = false;
                            clearTimeout(vcht_timer);
                            vcht_logID = 0;
                            sessionStorage.vcht_logID = (vcht_logID);
                            var bubble = jQuery('<div class="bubble_left palette palette-clouds"></div>');
                            if (vcht_operatorID == 0 && vcht_settings.chatLogo != "") {
                                bubble.append('<img src="' + vcht_settings.chatLogo + '" alt="" class="bubble_photo" />');
                            }
                            bubble.append(vcht_texts[5]);
                            jQuery('#chatHistory').append(bubble);
                            bubble.fadeIn(250);
                            jQuery('#chatWrite').fadeOut(250);
                            jQuery('#chatContent').scrollTop(jQuery('#chatContent')[0].scrollHeight);
                            jQuery(window.parent.document).find('.vcht_selectedDom').removeClass('vcht_selectedDom');
                            jQuery(window.parent).find('.vcht_avatarSel').fadeOut(200);
                            setTimeout(function() {
                                window.parent.document.location.href = window.parent.document.location.href;
                            }, 2600);
                        }
                    }
                    setTimeout(function() {
                        vcht_initTimer();
                    },1500);

                }
            });
        }, 1000);
    }
}

function vcht_disablesTheme() {
    jQuery('style').each(function() {
        jQuery(this).remove();
    });
    jQuery('link').each(function() {
        if (jQuery(this).attr('href')) {
            if (jQuery(this).attr('href') && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/css/frontend.css') < 0
                && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/css/reset.css') < 0
                && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/bootstrap/css/bootstrap.css') < 0
                && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/css/flat-ui.css') < 0
                && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/css/chat.css') < 0
                && jQuery(this).attr('href').indexOf('WP_Visual_Chat/assets/css/colors.css') < 0) {
                jQuery(this).attr("disabled", "disabled");
            }
        }
    });
    var scriptsCheck = false;
    jQuery('script').each(function() {
        if ((scriptsCheck)) {
            jQuery(this).attr("disabled", "disabled");
        }
        if (jQuery(this).attr("src") && jQuery(this).attr("src").indexOf("chat.min.js") > 0) {
            scriptsCheck = true;
        }
    });
}
function vcht_initChat() {
    if (isNaN(vcht_logID)) {
        vcht_logID = 0;
    }
    jQuery('#vcht_loader').fadeIn(100);
    if (vcht_user || vcht_username) {
        jQuery('#chatIntro').hide();
        vcht_startChat();
        if (vcht_logID > 0) {
            jQuery(window.parent.document).find('#vcht_chatframe').prop('style', '');
            jQuery(window.parent.document).find('#vcht_chatframe').attr('style', '');
            clearInterval(vcht_timerBounce);
            jQuery(this).addClass('open');
            jQuery(this).parent().addClass('open');
            setTimeout(function() {
                jQuery('.nicescroll-rails').removeClass('hidden');
            }, 400);
            jQuery(window.parent.document).find('body').addClass('vcht_open');
            jQuery(window.parent.document).find('#vcht_chatframe').addClass('vcht_open');
            vcht_recoverChat();
        }
    } else {
        jQuery('#vcht_loader').fadeOut(100);
    }
    jQuery('#chatWrite textarea').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            vcht_sendMessage();
        }
    });
}

function vcht_checkEmail(email) {
    if (email.indexOf("@") != "-1" && email.indexOf(".") != "-1" && email != "")
        return true;
    return false;
}


function vcht_sendEmail() {
    jQuery('#chatEmail_email,#chatEmail_msg').parent().removeClass('has-error');
    var email = jQuery('#chatEmail_email').val();
    var msg = jQuery('#chatEmail_msg').val();
    var error = false;

    if (!vcht_checkEmail(email)) {
        error = true;
        jQuery('#chatEmail_email').parent().addClass('has-error');
    }
    if (msg.length < 5) {
        error = true;
        jQuery('#chatEmail_msg').parent().addClass('has-error');
    }

    if (!error) {

        jQuery.ajax({
            url: vcht_ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_sendEmail',
                userID: vcht_userID,
                username: vcht_username,
                email: email,
                message: msg
            },
            success: function(repS) {
                var rep = jQuery.parseJSON(repS);
                jQuery('#emailForm').fadeOut(500);
                setTimeout(function() {
                    jQuery('#emailForm').html('<h4>' + vcht_texts[10] + '</h4>');
                    jQuery('#emailForm').fadeIn(500);
                }, 1000);

                setTimeout(function() {
                    window.parent.document.location.href = window.parent.document.location.href;
                }, 3500);

            }
        });
    }
}

function vcht_recoverChat() {
    jQuery.ajax({
        url: vcht_ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_recoverChat',
            logID: vcht_logID
        },
        success: function(repS) {
            var rep = jQuery.parseJSON(repS);
            jQuery('#chatHistory').html('');
            jQuery.each(rep, function(i) {
                var msg = this;
                if (msg.isOperator == '1') {
                    if (i == rep.length - 1) {
                        vcht_operatorSay(msg);
                    } else {
                        vcht_operatorSay(msg, true);
                    }
                } else {
                    vcht_userSay(msg.content);
                }
            });
            vcht_isChatting = true;
            vcht_initTimer();
        }
    });
}

function vcht_validUsername() {
    jQuery('#chatIntro_username').parent().removeClass('has-error');
    var username = jQuery('#chatIntro_username').val();
    if (username == "") {
        jQuery('#chatIntro_username').parent().addClass('has-error');
    } else {
        jQuery('#chatIntro').slideUp(250);
        vcht_username = jQuery('#chatIntro_username').val();
        sessionStorage.vcht_username = vcht_username;

        vcht_startChat();
    }
}

function vcht_startChat() {
    jQuery.ajax({
        url: vcht_ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_startChat',
            userID: vcht_userID,
            username: vcht_username
        },
        success: function(rep) {
            if (rep == 'nobody') {
                jQuery('#vcht_loader').fadeOut(100);
                jQuery('#emailForm').fadeIn(250);
            } else {
                jQuery('#vcht_loader').fadeOut(200);
                jQuery('#chatRoom').delay(200).fadeIn(250);
                if (vcht_logID == 0) {
                    setTimeout(function() {
                        jQuery("#chatContent").getNiceScroll().resize();
                        setTimeout(function() {
                            jQuery("#chatContent").getNiceScroll().resize();
                        }, 250);
                        vcht_operatorSay({userID: 0, content: vcht_texts[4], domelement: ''});
                    }, 250);
                }
            }
        }
    });

}

function vcht_operatorSay(msg, recover) {
    var bubble = jQuery('<div class="bubble_left palette palette-clouds" data-domelement="' + msg.domelement + '"></div>');
    bubble.append('<div class="bubble_arrow"></div>');
    if (msg.userID == 0 && vcht_settings.chatLogo != "") {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image:none;"><img src="' + vcht_settings.chatLogo + '" alt=""  /></div>');
    } else {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image:none;">' + msg.avatarOperator + '</div>');
    }

    bubble.append(msg.content);
    var username = '';
    if (msg.username && msg.username != "") {
        username = msg.username;
    }
    //bubble.append('<div class="bubble_meta">' + username + '</div>');

    jQuery('#chatHistory').append(bubble);
    bubble.fadeIn(250);
    jQuery('#chatContent').scrollTop(jQuery('#chatContent')[0].scrollHeight);
    jQuery("#chatContent").getNiceScroll().resize();
    setTimeout(function() {
        jQuery("#chatContent").getNiceScroll().resize();
    }, 400);
    jQuery(window.parent.document).find('.vcht_selectedDom').removeClass('vcht_selectedDom');
    var chkRedir = false;
    if (!recover) {
        if (msg.url && msg.url != "" && msg.url != window.parent.window.document.location.href && sessionStorage.lastRedir != msg.id) {
            sessionStorage.lastRedir = msg.id;
            chkRedir = true;
            window.parent.window.document.location.href = msg.url;
        }
        if (!chkRedir) {
            if (msg.domElement && msg.domElement != "") {
                window.parent.vcht_showElement(msg.domElement);
            } else {
                window.parent.vcht_selectionMode = false;
                jQuery(window.parent).find('.vcht_avatarSel').fadeOut(200);
                setTimeout(function() {
                    jQuery(window.parent.document).find('.vcht_avatarSel').remove();
                }, 500);
            }
        }
    }
}

function vcht_userSay(msg) {
    var bubble = jQuery('<div class="bubble_right palette palette-turquoise"></div>');
    bubble.append(msg);
    bubble.append('<div class="bubble_arrow"></div>');
    if (vcht_userPic) {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image:none;">' + vcht_userPic + '</div>');
    } else {
        bubble.append('<div class="avatarImg bubble_photo"></div>');
    }
    jQuery('#chatHistory').append(bubble);
    var username = '';
    if (msg.username && msg.username != "") {
        username = msg.username;
    } else {
        username = vcht_username;
    }
    //bubble.append('<div class="bubble_meta">' + username + '</div>');
    bubble.fadeIn(250);
    jQuery('#chatContent').scrollTop(jQuery('#chatContent')[0].scrollHeight);
    jQuery("#chatContent").getNiceScroll().resize();
    setTimeout(function() {
        jQuery("#chatContent").getNiceScroll().resize();
    }, 400);
}
function vcht_sendMessage() {
    if (isNaN(vcht_operatorID)) {
        vcht_operatorID = 0;
    }
    var message = jQuery('#chatWrite textarea').val();
    jQuery('#chatWrite textarea').parent().removeClass('has-error');
    if (message == "") {
        jQuery('#chatWrite textarea').parent().addClass('has-error');
    } else {
        message = message.replace(/\n/g, "<br />");
        message = message.replace(/(<([^>]+)>)/ig, "");
        vcht_userSay(message);
        jQuery.ajax({
            url: vcht_ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_newMessage',
                userID: vcht_userID,
                logID: vcht_logID,
                operatorID: vcht_operatorID,
                username: vcht_username,
                message: message,
                url: window.parent.window.document.location.href
            },
            success: function(repS) {
                jQuery('#chatWrite textarea').val('');
                var rep = jQuery.parseJSON(repS);
                if (vcht_logID == 0) {
                    vcht_logID = parseInt(rep.logID);
                    sessionStorage.vcht_logID = parseInt(vcht_logID);
                }
                if (vcht_operatorID == 0) {
                    vcht_operatorID = parseInt(rep.operatorID);
                    sessionStorage.vcht_operatorID = vcht_operatorID;
                }
                if (!vcht_isChatting) {
                    vcht_isChatting = true;
                    vcht_initTimer();
                }
            }
        });
    }


}