<?php
/**
 * Template Name: Chat Template
 *
 * @package WordPress
 * @subpackage Chat Template
 */
$vcht = vcht_Core::instance(__FILE__, '1.0.0');
$settings = $vcht->getSettings();
$texts = $vcht->getTexts();

add_action('wp_enqueue_scripts', array($vcht, 'chat_enqueue_scripts'), 10, 1);
add_action('wp_enqueue_scripts', array($vcht, 'chat_enqueue_styles'), 10, 1);

get_header();
$userEmail = "";
$userName = "";
$current_user = wp_get_current_user();
if ( $current_user->ID > 0) {
	$userEmail = $current_user->user_email;
	$userName = $current_user->user_login;
}
?>
<div id="chat" class="vcht_chat">
    <div id="chatPanel" class="container-fluid vcht_chat">
        <div id="chatHeader" class="palette palette-turquoise">
            <span class="fui-chat"></span>
            <?php echo $texts[1]->content; ?>
        </div>
        <div id="chatContent">
            <span id="vcht_loader" class="ouro ouro2">
                <span class="left"><span class="anim"></span></span>
                <span class="right"><span class="anim"></span></span>
            </span>
            <div id="chatIntro">
                <div class="row-fluid">
                    <div class="col-md-12">
                        <div class="text-center">
                            <div class="avatarImg"></div>
                        </div>
                        <div class="form-group" id="chatIntro_formGroup">
                            <input type="text" class="form-control" value="<?php echo $userName; ?>" placeholder="<?php echo $texts[2]->content; ?>" id="chatIntro_username">
                            <label class="chatIntro_username_icon fui-user" for="login-name"></label>
                        </div>
                        <a class="btn btn-primary btn-lg btn-block" href="javascript:" onclick="vcht_validUsername();"><?php echo $texts[3]->content; ?></a>
                    </div>
                </div>
            </div>
            <div id="chatRoom">
                <div id="chatHistory"></div>
                <div id="chatWrite" class="palette palette-clouds">
                    <div class="form-group">
                        <textarea class="form-control" rows="3"></textarea>     
                        <a href="javascript:" onclick="vcht_sendMessage();" class="btn btn-default"><span class="fui-chat"></span></a>                   
                    </div>
                </div>
            </div>
            <div id="emailForm">
			
                <div class="row-fluid">
                    <div class="col-md-12">
                        <p><?php echo $texts[6]->content; ?></p>
                        <div class="form-group" style="<?php  if ($userEmail != "") { echo 'display:none;'; } ?>">
                            <input type="email" id="chatEmail_email" class="form-control" value="<?php echo $userEmail; ?>" placeholder="<?php echo $texts[7]->content; ?>">
                        </div>
                        <div class="form-group">
                            <textarea  id="chatEmail_msg" class="form-control" rows="3" placeholder="<?php echo $texts[8]->content; ?>"></textarea>                      
                        </div>
                        <a href="javascript:" onclick="vcht_sendEmail();" class="btn btn-default"><span class="fui-mail"></span><?php echo $texts[9]->content; ?></a>  
                    </div>
                </div>
            </div>
        </div>
    </div>