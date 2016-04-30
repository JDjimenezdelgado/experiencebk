// sent from pPHP : vcht_operatorID,vcht_operator,vcht_operatorAvatar
var vcht_checkTimer;
var vcht_lastRep = false;
var vcht_currentLogID = 0;
var vcht_currentDomElement = '';
var vcht_isChatting = false;

vcht_operatorID = vcht_operatorID[0];
vcht_operatorAvatar = vcht_operatorAvatar[0];
jQuery(document).ready(function() {

    // Tooltips
    jQuery("[data-toggle=tooltip]").tooltip("show");
    // Switch
    jQuery("[data-toggle='switch']").wrap('<div class="switch" />').parent().bootstrapSwitch();

    jQuery('#vcht_operatorname').html(vcht_operator);
    jQuery('#vcht_consolePanelContent').hide();
    jQuery('#vcht_onlineCB').change(function() {
        if (jQuery(this).is(':checked')) {
            vcht_operatorConnect();
        } else {
            vcht_operatorDisconnect();
        }
    });
});

function vcht_operatorConnect() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_operatorConnect',
            success: function() {
                vcht_recoverChats();
            }
        }
    });
}
function vcht_operatorDisconnect() {
    clearTimeout(vcht_checkTimer);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_operatorDisconnect',
            success: function() {
            }
        }
    });
}
function vcht_checkUsers() {
    if (jQuery('#usersList li[data-logid]').length == 0) {
        jQuery('#vcht_btnMinimize').fadeOut(200);
        if (jQuery('#usersList li').length == 1) {
            jQuery('#usersList').append('<li class="vcht_nobody">There are no chat currently</li>');
        }
        vcht_minifyChatPanel();
    } else {
        jQuery('#vcht_btnMinimize').fadeIn(200);
        jQuery('#vcht_btnMinimize').delay(250).css('display', 'block');
        jQuery('#usersList .vcht_nobody').remove();
    }
    if (jQuery('#usersList li[data-logid="' + vcht_currentLogID + '"]').length == 0) {
        vcht_currentLogID = 0;
        vcht_minifyChatPanel();
    }
}

function vcht_recoverChats() {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_recoverChats'
        },
        success: function(repS) {
            var rep = jQuery.parseJSON(repS);
            jQuery.each(rep, function() {
                var chat = this;
                if (chat.id) {

                    var $li = jQuery('<li class="chat" data-logid="' + chat.id + '" data-chatrequest="0" data-userid="' + chat.userID + '" data-username="' + chat.username + '"></li>');
                    $li.append('<a href="javascript:">' + chat.username + '<span class="navbar-unread">1</span></a>');
                    jQuery('#usersList').append($li);
                    $li.children('a').click(vcht_clickUserTab);
                    vcht_createChatPanel(chat);


                    jQuery.each(chat.messages, function() {
                        var msg = this;
                        if (msg.isOperator == 1) {
                            vcht_say(msg, chat.id, msg.avatarOperator);
                        } else {
                            if (chat.avatarImg) {
                                vcht_userSay(msg, chat.id, msg.url, chat.avatarImg);
                            } else {
                                vcht_userSay(msg, chat.id, msg.url);
                            }
                        }
                    });
                }
            });
            vcht_isChatting = true;
            vcht_checkOperatorChat();
            vcht_checkUsers();
        }
    });
}
function vcht_toggleChatPanel() {
    if (jQuery('#vcht_consolePanelContent').is('.opened')) {
        vcht_minifyChatPanel();
    } else {
        vcht_openChatPanel();
    }
}
function vcht_minifyChatPanel() {
    if (jQuery('#vcht_consolePanelContent').is('.opened')) {
        jQuery('#vcht_btnMinimize span').removeClass('glyphicon-chevron-down');
        jQuery('#vcht_btnMinimize span').addClass('glyphicon-chevron-up');
        jQuery('#vcht_consolePanelContent').removeClass('opened');
        jQuery('#vcht_consolePanelContent').hide('bind');
    }
}
function vcht_openChatPanel() {
    if (!jQuery('#vcht_consolePanelContent').is('.opened')) {

        jQuery('#vcht_consolePanelContent > div[data-logid]').each(function() {
            if (jQuery(this).css('display') != 'none') {
                vcht_currentLogID = parseInt(jQuery(this).data('logid'));
            }
        });

        jQuery('#vcht_btnMinimize span').removeClass('glyphicon-chevron-up');
        jQuery('#vcht_btnMinimize span').addClass('glyphicon-chevron-down');
        jQuery('#vcht_consolePanelContent').addClass('opened');
        jQuery('#vcht_consolePanelContent').show('bind');
    }
}

function vcht_getLogs(userID, ip) {
    vcht_minifyChatPanel();

    vcht_currentLogID = 0;
    if (userID > 0) {
        jQuery('#vcht_consoleFrame').prop('src', 'admin.php?page=vcht-logsList&userID=' + userID);
    } else {
        jQuery('#vcht_consoleFrame').prop('src', 'admin.php?page=vcht-logsList&search=' + ip);
    }
}

function vcht_exitChat(logID) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_closeChat',
            logID: logID
        }, success: function() {
            jQuery('#usersList li[data-logid=' + logID + ']').fadeOut(250);
            jQuery('#usersList li[data-logid=' + logID + ']').delay(500).remove();
            jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').fadeOut(250);
            jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').delay(500).remove();
            vcht_currentLogID = 0;
            vcht_checkUsers();
        }
    });
}

function vcht_createChatPanel(chat) {
    var bgImg = '';
    if (chat.avatarImg && chat.avatarImg != "") {
        bgImg = 'style="background-image: none;"';
    } else {
        chat.avatarImg = '';
    }
    var logsBtn = '';
    //if (chat.userID > 0) {

    logsBtn = '<p><a href="javascript:" onclick="vcht_getLogs(' + chat.userID + ',\'' + chat.ip + '\');"  class="btn btn-primary"><span class="fui-search"></span>See user logs</a></p>';
    // }

    var panel = jQuery('<div data-logid="' + chat.id + '" data-userid="' + chat.userid + '"></div>');
    panel.append('<div class="col-md-2 palette palette-wet-asphalt" id="vcht_userInfos">' +
            '<div class="vcht_avatarImg" ' + bgImg + '>' + chat.avatarImg + '</div>' +
            '<p><strong class="vcht_username">' + chat.username + '</strong></p>' +
            logsBtn +
            '<p class="vcht_onlineOperatorsNb">Online operators: <strong>1</strong></p>' +
            '</div>');
    jQuery('#vcht_consolePanelContent').append(panel);

    var historyCt = jQuery('<div class="vcht_chatContent"></div>');
    panel.append(historyCt);

    var history = jQuery('<div class="vcht_chatHistory"></div>');

    historyCt.append(history);
    historyCt.append('<div class="vcht_chatInfos palette palette-primary"><p><span class="glyphicon glyphicon-time"></span><br/><strong class="vcht_timePast">00:00:00</strong></p>' +
            '<p><div class="dropdown">' +
            '<div class="btn-group select select-block open"><button class="btn dropdown-toggle clearfix btn-primary" data-toggle="dropdown"><span class="filter-option pull-left">Transfer chat</span>&nbsp;<span class="caret"></span></button><span class="dropdown-arrow dropdown-arrow-inverse"></span><ul data-logid="' + chat.id + '" class="vcht_transferSelect dropdown-menu dropdown-inverse" role="menu" style="max-height: 616px; overflow-y: auto; min-height: 108px;"></ul></div>' +
            '</div></p>' +
            '<p><a href="javascript:" onclick="vcht_exitChat(' + chat.id + ');" class="btn btn-danger"><span class="fui-cross"></span>Stop chat</a></p>' +
            '</div>');
    var panelRep = jQuery('<div class="vcht_chatWrite palette palette-clouds container-fluid"></div>');
    historyCt.append(panelRep);



    if (chat.operatorID == 0) {
        panel.find('.vcht_chatInfos').hide();
        panelRep.append('<p class="vcht_chatWrite_newChat"><a href="javascript:" onclick="vcht_acceptChat(' + chat.id + ');" class="btn btn-primary">Accept this chat</a></p>');
    } else {
        panel.find('.vcht_chatInfos').fadeIn(200);
        vcht_initChatWrite(chat.id);
    }
}

function vcht_checkOperatorChat() {

    if (vcht_isChatting) {
        vcht_checkTimer = setTimeout(function() {
            var currentChats = '';
            jQuery('#usersList li[data-logid]').each(function() {
                var li = this;
                currentChats += jQuery(this).data('logid') + ',';
            });
            if (currentChats.length > 0) {
                currentChats = currentChats.substr(0, currentChats.length - 1);
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'vcht_check_operator_chat',
                    operatorID: vcht_operatorID,
                    currentChats: currentChats
                },
                success: function(repS) {
                    var rep = jQuery.parseJSON(repS);
                    vcht_lastRep = rep;
                    var requestsArray = new Array();
                    if (rep.chatRequests.length > 0) {
                        jQuery.each(rep.chatRequests, function() {
                            req = this;
                            requestsArray.push((req.id));
                            if (jQuery('#usersList li[data-logid=' + req.id + ']').length == 0) {
                                var $li = jQuery('<li class="palette palette-turquoise reqChat" data-logid="' + req.id + '" data-chatrequest="1" data-userid="' + req.userID + '" data-username="' + req.username + '"></li>');
                                $li.append('<a href="javascript:">' + req.username + '</a>');
                                jQuery('#usersList').append($li);
                                $li.children('a').click(vcht_clickUserTab);
                                if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                                    jQuery('#vcht_audioMsg').get(0).play();
                                }

                                if (req.id != vcht_currentLogID && jQuery('#usersList li[data-logid=' + req.id + '] a span').length == 0) {
                                    jQuery('#usersList li[data-logid=' + req.id + '] a').append('<span class="navbar-unread">1</span>');
                                }
                            }
                        });
                    }

                    // remove chats requests past
                    jQuery('#usersList li[data-logid][data-chatrequest="1"]').not('.vcht_accepted').each(function() {
                        var li = this;
                        var chk = false;
                        jQuery.each(rep.chatRequests, function() {
                            req = this;
                            if (parseInt(req.id) == parseInt(jQuery(li).data('logid'))) {
                                chk = true;
                            }
                        });
                        if (!chk) {
                            jQuery(this).remove();
                        }
                    });

                    jQuery.each(rep.chats, function() {
                        var chat = this;
                        var hours = Math.floor(chat.timePast / (60 * 60));
                        var divisor_for_minutes = chat.timePast % (60 * 60);
                        var minutes = Math.floor(divisor_for_minutes / 60);
                        var divisor_for_seconds = divisor_for_minutes % 60;
                        var seconds = Math.ceil(divisor_for_seconds);

                        jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + '] .vcht_timePast').html(hours.toString().replace(/^(\d)$/, '0$1') + ':' + minutes.toString().replace(/^(\d)$/, '0$1') + ':' + seconds.toString().replace(/^(\d)$/, '0$1'));
                        jQuery.each(chat.messages, function() {
                            var msg = this;
                            vcht_userSay(msg, chat.id, msg.url);
                            if (vcht_currentLogID == chat.id && msg.url != jQuery('#vcht_consoleFrame').prop('src')) {
                                jQuery('#vcht_consoleFrame').prop('src', msg.url);
                            }
                            if (chat.id != vcht_currentLogID) {
                                jQuery('#usersList li[data-logid=' + chat.id + '] a > span.navbar-unread').fadeIn(100);

                                if (jQuery('#vcht_audioMsg').data('enable') != 'false') {
                                    jQuery('#vcht_audioMsg').get(0).play();
                                }
                            }
                        });
                    });

                    jQuery.each(rep.chatsClosed, function() {
                        var chatClosed = this;
                        if (jQuery('#usersList li[data-logid=' + chatClosed + ']').length > 0) {
                            if (jQuery('#usersList li[data-logid=' + chatClosed + ']').is('.palette-turquoise')) {
                                vcht_exitChat(chatClosed);
                            } else {
                                jQuery('#usersList li[data-logid=' + chatClosed + ']').addClass('palette palette-concrete');
                                jQuery('#vcht_consolePanelContent > div[data-logid=' + chatClosed + '] .vcht_chatHistory').append('<div class="userAction palette palette-clouds"><div class="avatarImg bubble_photo"></div> User disconnects.</div>');
                                jQuery('#vcht_consolePanelContent > div[data-logid=' + chatClosed + '] .vcht_chatHistory .userAction:last-child').fadeIn(250);
                                jQuery('#vcht_consolePanelContent > div[data-logid=' + chatClosed + '] .vcht_chatWrite').fadeOut(200);
                            }
                        }
                    });

                    // transfers 
                    jQuery.each(rep.transfers, function() {
                        var chat = this;
                        vcht_createChatPanel(chat);
                        jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + '] .vcht_chatInfos').fadeIn(250);
                        jQuery.each(chat.messages, function() {
                            var msg = this;
                            if (msg.isOperator == '1') {
                                vcht_say(msg, chat.id);
                            } else {
                                vcht_userSay(msg, chat.id, msg.url);
                            }

                        });
                        jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + '] .vcht_chatHistory').append('<div class="userAction palette palette-clouds"><div class="avatarImg bubble_photo"></div> This discussion was transferred to you</div>');
                        jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + '] .vcht_chatHistory .userAction:last-child').fadeIn(250);
                        var $li = jQuery('<li class="palette palette-turquoise reqChat" data-logid="' + chat.id + '" data-chatrequest="0" data-userid="' + chat.userID + '" data-username="' + chat.username + '"></li>');
                        $li.append('<a href="javascript:">' + chat.username + '</a>');
                        jQuery('#usersList').append($li);
                        $li.children('a').click(vcht_clickUserTab);
                        jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + ']').find('.vcht_chatContent').scrollTop(jQuery('#vcht_consolePanelContent > div[data-logid=' + chat.id + ']').find('.vcht_chatContent')[0].scrollHeight);

                    });

                    // operators
                    if (rep.operators.length > 1) {
                        jQuery('ul.vcht_transferSelect').html('<li rel="0" class=""><a tabindex="-1" href="#" class="active"><span class="pull-left">Choose operator</span></a></li>');
                    } else {
                        jQuery('ul.vcht_transferSelect').html('<li rel="0" class=""><a tabindex="-1" href="#" class="active"><span class="pull-left">No operators </<span></a></li>');

                    }
                    jQuery.each(rep.operators, function(i) {
                        var operator = this;
                        if (operator.userID != vcht_operatorID) {
                            var $li = jQuery('<li data-userid="' + operator.userID + '" rel="' + (i + 1) + '" class=""><a tabindex="-1" href="#" class="active"><span class="pull-left">' + operator.username + '</span></a></li>');
                            jQuery('ul.vcht_transferSelect').append($li);
                            $li.click(function() {
                                var logID = jQuery(this).parent('ul').data('logid');
                                var operatorID = jQuery(this).data('userid');

                                jQuery.ajax({
                                    url: ajaxurl,
                                    type: 'post',
                                    data: {
                                        action: 'vcht_transferChat',
                                        logID: logID,
                                        operatorID: operatorID
                                    },
                                    success: function() {
                                        jQuery('#usersList li[data-logid=' + logID + ']').fadeOut(250);
                                        jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').fadeOut(250);
                                        setTimeout(function() {
                                            jQuery('#usersList li[data-logid=' + logID + ']').remove();
                                            jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').remove();
                                        }, 500);
                                    }
                                });

                            });
                        }
                    });
                    jQuery('.vcht_onlineOperatorsNb strong').html(rep.operators.length);


                    vcht_checkUsers();

                    setTimeout(function() {
                    vcht_checkOperatorChat();
                    },1500);
                }
            });
        }, 1000);
    }
}

function vcht_getChat(userID, username, isRequest) {
    var rep = false;
    if (vcht_lastRep) {
        if (isRequest) {
            jQuery.each(vcht_lastRep.chatRequests, function() {
                var req = this;
                if (userID > 0 && req.userID == userID) {
                    rep = req;
                } else if (userID == 0 && req.username == username) {
                    rep = req;
                }

            });
        } else {
            jQuery.each(vcht_lastRep.chats, function() {
                var req = this;
                if (userID > 0 && req.userID == userID) {
                    rep = req;
                } else if (userID == 0 && req.username == username) {
                    rep = req;
                }
            });
        }
    }
    return rep;
}

function vcht_clickUserTab() {
    jQuery(this).find('span.navbar-unread').fadeOut(100);
    if (jQuery(this).is('palette-turquoise') && jQuery(this).data('chatrequest') == '0') {
        jQuery(this).removeClass('palette-turquoise');
    }
    vcht_loadChatPanel(jQuery(this).parent().data('logid'));
    vcht_currentLogID = jQuery(this).parent().data('logid');
    vcht_openChatPanel();
}

function vcht_isIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

function vcht_declineChat(logID) {
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').hide();
    jQuery('#usersList li[data-logid=' + logID + ']').hide();
}

function vcht_acceptChat(logID) {
    jQuery('#usersList li[data-logid=' + logID + ']').addClass('vcht_accepted');
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').fadeOut(250);
    jQuery('#vcht_loader').delay(300).fadeIn(100);
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'vcht_acceptChat',
            logID: logID
        },
        success: function(rep) {
            if (rep == '1') {
                jQuery('#usersList li[data-logid=' + logID + ']').removeClass('palette');
                jQuery('#usersList li[data-logid=' + logID + ']').removeClass('palette-turquoise').data('chatrequest', '0');
                vcht_initChatWrite(logID);
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatHistory').html('');

                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').delay(1500).fadeIn(250);
                jQuery('#vcht_loader').delay(1400).fadeOut(100);
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').delay(300).find('.vcht_chatInfos').fadeIn(200);
            }
        }
    });
}

function vcht_initChatWrite(logID) {
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatWrite').html('<div class="col-md-7"><div class="form-group"><textarea data-logid="' + logID + '" data-url="" data-domelement="" class="form-control" rows="3" placeholder="Write your message here"></textarea></div></div>');
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatWrite').append('<div class="col-md-3"><p>Element selected : <strong class="vcht_hasElementSelected">No</strong></p><p><a href="javascript:" class="btn btn-primary" onclick="vcht_startSelectDomElement();"><span class="fui-eye"></span> Select an element</a></p></div>');
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatWrite').append('<div class="col-md-2"><a href="javascript:" onclick="vcht_sendMessage(' + logID + ')" class="btn btn-lg btn-primary btn-block vcht_sendBtn"><span class="fui-chat"></span></a></div>');
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatWrite textarea').keypress(function(e) {
        if (e.which == 13 && !e.shiftKey) {
            e.preventDefault();
            vcht_sendMessage(jQuery(this).data('logid'));
        }
    });
}

function vcht_startSelectDomElement() {
    vcht_minifyChatPanel();
    jQuery('#vcht_consolePanel').delay(500).fadeOut(250);
    jQuery('#vcht_infosPanel > .container > .col-md-12').html('<p>Navigate to the desired page and click on "Select an element" button.</p>');
    jQuery('#vcht_infosPanel > .container > .col-md-12').append('<p><a href="javascript:" class="btn btn-primary" onclick="vcht_targetDomElement();">Select an element</a><a href="javascript:" class="btn btn-warning" onclick="vcht_stopSelectDomElement();">Cancel</a></p>');
    jQuery('#vcht_infosPanel').delay(850).fadeIn(250);
}
function vcht_targetDomElement() {
    jQuery('#vcht_infosPanel > .container > .col-md-12').html('<p>Click on the desired item</p>');
    jQuery('#vcht_consoleFrame').get(0).contentWindow.vcht_startSelection();
}
function vcht_stopSelectDomElement() {
    jQuery('#vcht_infosPanel').fadeOut(250);
    jQuery('#vcht_consolePanel').delay(300).fadeIn(250);
    setTimeout(function() {
        vcht_openChatPanel();
    }, 600);

}
function vcht_selectDomElement(el) {
    vcht_currentDomElement = el;
    jQuery('#vcht_infosPanel > .container > .col-md-12').html('<p>Do you want to show this item to the user?</p>');
    jQuery('#vcht_infosPanel > .container > .col-md-12').append('<p><a href="javascript:" class="btn btn-primary" onclick="vcht_confirmDomElement();">Yes</a><a href="javascript:" class="btn btn-warning" onclick="vcht_targetDomElement();">No</a></p>');
}

function vcht_confirmDomElement(el) {
    var path = vcht_getPath(vcht_currentDomElement);
    jQuery('#vcht_consolePanelContent > div[data-logid=' + vcht_currentLogID + '] .vcht_chatWrite .vcht_hasElementSelected').html('Yes');
    jQuery('#vcht_consolePanelContent > div[data-logid=' + vcht_currentLogID + '] .vcht_chatWrite textarea').data('domelement', path);
    jQuery('#vcht_consolePanelContent > div[data-logid=' + vcht_currentLogID + '] .vcht_chatWrite textarea').data('url', jQuery('#vcht_consoleFrame').get(0).contentWindow.document.location.href);

    vcht_stopSelectDomElement();
}
function vcht_getPath(el) {
    var path = '';
    if (jQuery(el).length > 0 && typeof (jQuery(el).prop('tagName')) != "undefined") {
        if (!jQuery(el).attr('id')) {
            path = '>' + jQuery(el).prop('tagName') + ':nth-child(' + (jQuery(el).index() + 1) + ')' + path;
            path = vcht_getPath(jQuery(el).parent()) + path;
        } else {
            path += '#' + jQuery(el).attr('id');
        }
    }
    return path;
}
function vcht_sendMessage(logID) {
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').parent().removeClass('has-error');
    var msg = jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').val();
    if (msg == "") {
        jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').parent().addClass('has-error');
    } else {
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1;
        var yyyy = today.getFullYear();
        var h = today.getHours();
        var m = today.getMinutes();
        var s = today.getSeconds();
        if (dd < 10) {
            dd = '0' + dd;
        }
        if (mm < 10) {
            mm = '0' + mm;
        }
        if (h < 10) {
            h = '0' + h;
        }
        if (m < 10) {
            m = '0' + m;
        }
        if (s < 10) {
            s = '0' + s;
        }
        var date = yyyy + '-' + mm + '-' + dd + ' ' + h + ':' + m + ':' + s;
        msg = msg.replace(/\n/g, '<br/>');
        vcht_say({content: msg, date: date}, logID);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_operatorSay',
                logID: logID,
                msg: msg,
                domElement: jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').data('domelement'),
                url: jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').data('url')
            },
            success: function(repS) {
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatWrite .vcht_hasElementSelected').html('No');
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').val('');
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').data('domelement', '');
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatWrite textarea').data('url', '');
            }
        });
    }
}

function vcht_userSay(msg, logID, url, avatarImg) {
    var bubble = jQuery('<div class="bubble_left palette palette-clouds" data-url="' + url + '"></div>');
    bubble.append('<div class="bubble_arrow"></div>');
    bubble.append(msg.content);
    var username = jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_username').html();

    var hour = msg.date.substr(msg.date.indexOf(' ') + 1);
    bubble.append('<div class="bubble_meta">' + hour + ' - ' + username + '</div>');
    if (avatarImg && avatarImg != "") {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image: none;">' + avatarImg + '</div>');
    } else {
        bubble.append('<div class="avatarImg bubble_photo"></div>');
    }
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatHistory').append(bubble);
    bubble.fadeIn(250);
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent').scrollTop(jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent')[0].scrollHeight);
}
function vcht_say(msg, logID, avatarOperator) {

    var bubble = jQuery('<div class="bubble_right palette palette-turquoise"></div>');
    bubble.append(msg.content);
    var username = jQuery('#vcht_operatorname').html();
    if (msg.username && msg.username != "") {
        username = msg.username;
    }
    var hour = msg.date.substr(msg.date.indexOf(' ') + 1);
    bubble.append('<div class="bubble_meta">' + hour + ' - ' + username + '</div>');
    bubble.append('<div class="bubble_arrow"></div>');
    if (avatarOperator) {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image: none;">' + avatarOperator + '</div>');
    } else {
        bubble.append('<div class="avatarImg bubble_photo" style="background-image: none;">' + vcht_operatorAvatar + '</div>');
    }
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatHistory').append(bubble);
    bubble.fadeIn(250);
    jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent').scrollTop(jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent')[0].scrollHeight);



}

function vcht_loadChatPanel(logID) {

    if (jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').length > 0) {
        jQuery('#vcht_consolePanelContent > div').fadeOut(250);
        jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').delay(300).fadeIn(250);
        setTimeout(function() {
            jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent').scrollTop(jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent')[0].scrollHeight);
        }, 800);

        var userUrl = '';
        userUrl = jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + '] .vcht_chatHistory .bubble_left:last-child').data('url');
        jQuery('#vcht_consoleFrame').prop('src', userUrl);
    } else {
        jQuery('#vcht_loader').fadeIn(100);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'vcht_getLogChat',
                logID: logID
            }, success: function(repS) {
                var rep = jQuery.parseJSON(repS);
                jQuery('#vcht_consolePanelContent > div').fadeOut(500);
                vcht_createChatPanel(rep);


                var userURL = '';
                jQuery.each(rep.messages, function() {
                    var msg = this;
                    if (msg.isOperator == 1) {
                        var bubble = jQuery('<div class="bubble_right palette palette-turquoise"></div>');
                        bubble.append(msg.content);
                        bubble.append('<div class="bubble_arrow"></div>');
                    } else {
                        var bubble = jQuery('<div class="bubble_left palette palette-clouds" data-url="' + msg.url + '"></div>');
                        bubble.append('<div class="bubble_arrow"></div>');
                        bubble.append(msg.content);
                        userURL = msg.url;
                    }
                    bubble.append('<div class="avatarImg bubble_photo"></div>');
                    var panel = jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']');
                    panel.find('.vcht_chatHistory').append(bubble);
                    bubble.fadeIn(250);
                    panel.find('.vcht_chatContent').delay(500).scrollTop(panel.find('.vcht_chatContent')[0].scrollHeight);
                });

                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').delay(1000).fadeIn(500);
                jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent').delay(1500).scrollTop(jQuery('#vcht_consolePanelContent > div[data-logid=' + logID + ']').find('.vcht_chatContent')[0].scrollHeight);

                jQuery('#vcht_consoleFrame').prop('src', userURL);
                jQuery('#vcht_loader').fadeOut(100);

            }
        });
    }
}