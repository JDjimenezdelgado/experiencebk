<?php

/*
 * Plugin Name: Flat Visual Chat
 * Version: 3.1
 * Plugin URI: http://codecanyon.net/user/loopus/portfolio
 * Description: A unique chat allowing you to visually guide your clients on the site
 * Author: Biscay Charly (loopus)
 * Author URI: http://codecanyon.net/user/loopus/
 * Requires at least: 3.8
 * Tested up to: 3.8.1
 *
 * @package WordPress
 * @author Biscay Charly (loopus)
 * @since 1.0.0
 */


if (!defined('ABSPATH'))
    exit;

register_activation_hook(__FILE__, 'vcht_install');
register_deactivation_hook(__FILE__, 'vcht_uninstall');

global $jal_db_version;
$jal_db_version = "1.0";
require_once ('includes/vcht_Core.php');
require_once ('includes/vcht_admin_menu.php');
require_once ('includes/vcht_LogsTable.php');
require_once ('includes/vcht_MsgTable.php');

function VisualChat() {
    $version = 3.1;
    vcht_checkDBUpdates($version);
    $instance = vcht_Core::instance(__FILE__, $version);
    if (is_null($instance->menu)) {
        $instance->menu = vcht_admin_menu::instance($instance);
    }

    return $instance;
}

/**
 * Installation. Runs on activation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function vcht_install() {
    global $wpdb;
    global $jal_db_version;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    // create settings table
    $db_table_name = $wpdb->prefix . "vcht_settings";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                adminEmail VARCHAR(250) NOT NULL,
                emailSubject VARCHAR(250) NOT NULL,
                chatLogo VARCHAR(250) NOT NULL,
                chatDefaultPic VARCHAR(250) NOT NULL,
                rolesAllowed TEXT NOT NULL,
                colorA VARCHAR(7) NOT NULL,
                colorB VARCHAR(7) NOT NULL,
                colorC VARCHAR(7) NOT NULL,
                colorD VARCHAR(7) NOT NULL,
                colorE VARCHAR(7) NOT NULL,
                colorF VARCHAR(7) NOT NULL,
                chatPosition VARCHAR(7) NOT NULL,
                shineColor VARCHAR(7) NOT NULL,
                bounceFx BOOL NOT NULL,
                purchaseCode VARCHAR(250) NOT NULL,                
                updated BOOL NOT NULL,
                playSound BOOL NOT NULL,
                pageID INT(9) NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
        // insert default settings
        $avatarPath = esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'images/administrator-48.png';
        $defaultPicPath = esc_url(trailingslashit(plugins_url('/assets/', __FILE__))) . 'images/guest-48.png';

        // Create form page 
        $_p = array();
        $_p['post_title'] = 'Visual Chat page';
        $_p['post_content'] = "";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1);
        $page_id = wp_insert_post($_p);
        update_post_meta($page_id, '_vcht_template', 'vcht_template.php');

        $rows_affected = $wpdb->insert($db_table_name, array('id' => 1, 'chatLogo' => $avatarPath, 'chatDefaultPic' => $defaultPicPath,
            'adminEmail' => 'loopus_web@hotmail.fr', 'emailSubject' => 'New message from your website chat',
            'colorA' => '#1ABC9C', 'colorB' => '#34495E', 'colorC' => '#ECF0F1', 'colorD' => '#CACFD2', 'colorE' => '#FFFFFF', 'colorF' => '#bdc3c7', 'shineColor' => '#1ABC9C',
            'bounceFx' => true, 'playSound' => 1, 'chatPosition' => 'right', 'pageID' => $page_id));
    }
    add_option("jal_db_version", $jal_db_version);



    // create logs table
    $db_table_name = $wpdb->prefix . "vcht_logs";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                date DATETIME NOT NULL,
                lastActivity DATETIME NOT NULL,
                operatorLastActivity DATETIME NOT NULL,
                userID SMALLINT(5) NOT NULL,
                username VARCHAR(250) NOT NULL,
                operatorID SMALLINT(5) NOT NULL, 
                finished BOOL NOT NULL,
                sent BOOL NOT NULL,
                transfer BOOL NOT NULL,
                ip VARCHAR(128) NOT NULL,
                country VARCHAR(128) NOT NULL,
                city VARCHAR(128) NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    // create messages table
    $db_table_name = $wpdb->prefix . "vcht_messages";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                date DATETIME NOT NULL,
                logID SMALLINT(5) NOT NULL,
                userID SMALLINT(5) NOT NULL,
                isOperator BOOL NOT NULL,
                content TEXT NOT NULL,
                domElement TEXT NOT NULL,
                url VARCHAR(250) NOT NULL,
                isRead BOOL NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    // create operators table
    $db_table_name = $wpdb->prefix . "vcht_operators";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                lastActivity DATETIME NOT NULL,
                userID SMALLINT(5) NOT NULL,
                username VARCHAR(250) NOT NULL,
                online BOOL NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);
    }

    // create texts table
    $db_table_name = $wpdb->prefix . "vcht_texts";
    if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
        if (!empty($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if (!empty($wpdb->collate))
            $charset_collate .= " COLLATE $wpdb->collate";

        $sql = "CREATE TABLE $db_table_name (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
                original TEXT NOT NULL,
                content TEXT NOT NULL,
                isTextarea BOOL NOT NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";
        dbDelta($sql);

        $text = "Need Help ?";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Enter your name";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Start";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Hello :)\nPlease write your question.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
        $text = "This discussion is finished.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Sorry, there are currently no operators online.\nIf you wish, send us your question using the form below.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
        $text = "Enter your email here";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Write your message here";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Send this message";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text));
        $text = "Thank you.\nYour message has been sent.\nWe will contact you soon.";
        $rows_affected = $wpdb->insert($db_table_name, array('original' => $text, 'content' => $text, 'isTextarea' => true));
    }


    // add chat capability to admins
    global $wp_roles;

    $wp_roles->add_cap('administrator', 'visual_chat');
    add_role('chat_operator', __('Chat Operator'), array(
        'visual_chat' => true,
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false)
    );
    $wp_roles->add_cap('chat_operator', 'visual_chat');
}

/**
 * Update database
 * @access  public
 * @since   2.0
 * @return  void
 */
function vcht_checkDBUpdates($version) {
    global $wpdb;

    $installed_ver = get_option("vcht_version");
    if (!$installed_ver || $installed_ver < 2.1) {
        $table_name = $wpdb->prefix . "vcht_logs";
        $sql = "ALTER TABLE " . $table_name . " ADD ip VARCHAR(128) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD country VARCHAR (128) NOT NULL;";
        $wpdb->query($sql);
        $sql = "ALTER TABLE " . $table_name . " ADD city VARCHAR (250) NOT NULL;";
        $wpdb->query($sql);
    }
    if (!$installed_ver || $installed_ver < 3.0) {
        $table_name = $wpdb->prefix . "vcht_settings";
        $sql = "ALTER TABLE " . $table_name . " MODIFY COLUMN pageID INT(9);";
        $wpdb->query($sql);
		
	}
    update_option("vcht_version", $version);
}

/**
 * Uninstallation.
 * @access  public
 * @since   1.0.0
 * @return  void
 */
function vcht_uninstall() {
    global $wpdb;
    global $jal_db_version;
    setcookie('pll_updateC', 0);
    unset($_COOKIE['pll_updateC']);

    $table_name = $wpdb->prefix . "vcht_logs";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_operators";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_settings";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_messages";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    $table_name = $wpdb->prefix . "vcht_texts";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    global $wp_roles;
    $wp_roles->remove_cap('administrator', 'visual_chat');
    remove_role('chat_operator');
}

// End uninstall()


VisualChat();
?>