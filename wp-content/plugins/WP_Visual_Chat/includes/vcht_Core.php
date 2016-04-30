<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of vcht_Core
 *
 * @author Asibun
 */
if (!defined('ABSPATH'))
    exit;

class vcht_Core {

    /**
     * The single instance
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * For menu instance
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $menu;

    /**
     * For template
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $plugin_slug;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.0.0') {
        $this->_version = $version;
        $this->_token = 'vcht';

        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->templates_url = esc_url(trailingslashit(plugins_url('/templates/', $this->file)));
        $this->templates = array('vcht_template.php' => __('Visual Chat', $this->_token));
        /* if (isset($_GET['page']) && $_GET['page'] == 'visual-chat') {
          add_filter('template_include', array($this, 'load_template'));
          } */
        add_filter('template_include', array($this, 'view_project_template'));
        $templates = wp_get_theme()->get_page_templates();
        $templates = array_merge($templates, $this->templates);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);
        add_action('wp_ajax_nopriv_vcht_newMessage', array($this, 'new_message'));
        add_action('wp_ajax_vcht_newMessage', array($this, 'new_message'));
        add_action('wp_ajax_nopriv_vcht_check_user_chat', array($this, 'check_user_chat'));
        add_action('wp_ajax_vcht_check_user_chat', array($this, 'check_user_chat'));
        add_action('wp_ajax_nopriv_vcht_recoverChat', array($this, 'recoverChat'));
        add_action('wp_ajax_vcht_recoverChat', array($this, 'recoverChat'));
        add_action('wp_ajax_nopriv_vcht_startChat', array($this, 'startChat'));
        add_action('wp_ajax_vcht_startChat', array($this, 'startChat'));
        add_action('wp_ajax_nopriv_vcht_sendEmail', array($this, 'sendEmail'));
        add_action('wp_ajax_vcht_sendEmail', array($this, 'sendEmail'));

    }

    /**
     * Load template.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function load_template($template) {
        $file = plugin_dir_path(__FILE__) . '../templates/vcht_template.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    /**
     * Checks if the template is assigned to the page
     *
     * @version	1.0.0
     * @since	1.0.0
     */
    public function view_project_template($template) {

        global $post;

        if (!isset($this->templates[get_post_meta($post->ID, '_vcht_template', true)])) {
            return $template;
        }

        $file = plugin_dir_path(__FILE__) . '../templates/' . get_post_meta($post->ID, '_vcht_template', true);
        if (file_exists($file)) {
            return $file;
        }
        return $template;
    }

    /**
     * Load chat CSS.
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function chat_enqueue_styles($hook = '') {
        wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'bootstrap/css/bootstrap.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-bootstrap');
        wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-flat-ui');
        wp_register_style($this->_token . '-chat', esc_url($this->assets_url) . 'css/chat.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-chat');
        wp_register_style($this->_token . '-colors', esc_url($this->assets_url) . 'css/colors.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-colors');
    }

    /**
     * Load chat Scripts.
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function chat_enqueue_scripts($hook = '') {

        wp_register_script($this->_token . '-modernizr', esc_url($this->assets_url) . 'js/modernizr.js', array(), $this->_version);
        wp_enqueue_script($this->_token . '-modernizr');
        wp_register_script($this->_token . '-jquery-ui', esc_url($this->assets_url) . 'js/jquery-ui-1.10.3.custom.min.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-jquery-ui');
        wp_register_script($this->_token . '-jquery-nicescroll', esc_url($this->assets_url) . 'js/jquery.nicescroll.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-jquery-nicescroll');
        wp_register_script($this->_token . '-chat', esc_url($this->assets_url) . 'js/chat.min.js', array(), $this->_version);
        wp_enqueue_script($this->_token . '-chat');
        $settings = $this->getSettings();
        wp_localize_script($this->_token . '-chat', 'vcht_settings', array($settings));
        wp_localize_script($this->_token . '-chat', 'vcht_ajaxurl', admin_url('admin-ajax.php'));
        $user = false;
        $userPic = "";
        $userID = 0;
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $userID = $current_user->ID;
            $user = $current_user->user_login;
            $userPic = get_avatar($userID, 48);
        }
        wp_localize_script($this->_token . '-chat', 'vcht_userID', array($userID));
        wp_localize_script($this->_token . '-chat', 'vcht_user', array($user));
        wp_localize_script($this->_token . '-chat', 'vcht_userPic', array($userPic));

        $texts = $this->getTexts();
        $textsJS = array();
        foreach ($texts as $value) {
            $textsJS[] = $value->content;
        }
        wp_localize_script($this->_token . '-chat', 'vcht_texts', $textsJS);
    }

    /**
     * Load frontend CSS.
     * @access public
     * @since 1.0.0
     * @return void
     */
    public function frontend_enqueue_styles($hook = '') {
        global $wp_styles;
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-frontend');
        wp_register_style($this->_token . '-ie10', esc_url($this->assets_url) . 'css/frontend_ie9.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-ie10');
        $wp_styles->add_data($this->_token . '-ie10', 'conditional', 'lt IE 10');
        wp_register_style($this->_token . '-colorsF', esc_url($this->assets_url) . 'css/colors_front.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-colorsF');
    }

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function frontend_enqueue_scripts($hook = '') {
        $settings = $this->getSettings();
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend.min.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-frontend');
        // wp_localize_script($this->_token . '-frontend', 'vcht_url', get_site_url().'/?page=visual-chat');
        wp_localize_script($this->_token . '-frontend', 'vcht_position', $settings->chatPosition);
        wp_localize_script($this->_token . '-frontend', 'vcht_url', get_page_link($settings->pageID));
        if (isset($_GET['vcht_element'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . "vcht_messages";
            $msg = $wpdb->get_results("SELECT * FROM $table_name WHERE id=" . $_GET['vcht_element'] . " LIMIT 1");
                wp_localize_script($this->_token . '-frontend', 'vcht_elementShow', $msg[0]->domElement);
            
        } else {
                wp_localize_script($this->_token . '-frontend', 'vcht_elementShow', "");
            
        }
    }

    /**
     * Return settings.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        if ($settings[0]) {
            return $settings[0];
        } else {
            return false;
        }
    }

    /**
     * Return texts.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getTexts() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_texts";
        $texts = array();
        $rows = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");
        $emptyObj = new StdClass();
        $emptyObj->content = ' ';
        $texts[] = $emptyObj;
        foreach ($rows as $value) {
            $value->content = nl2br($value->content);
            $texts[] = $value;
        }
        return $texts;
    }

    /*
     * ajax : return chat history
     */

    public function recoverChat() {
        global $wpdb;
        $logID = esc_sql($_POST['logID']);
        $rowsC = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE id=$logID");
        $messages = array();
        $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=$logID ORDER BY id ASC");
        foreach ($rows as $value) {
            if ($value->isOperator) {
                $value->avatarOperator = get_avatar($value->userID, 37);
                $user = get_userdata($value->userID);
                $username = $user->user_login;
                $value->username = $username;
            } else {
                $value->username = $rowsC[0]->username;
            }
            $value->content = stripslashes(nl2br($value->content));
            $messages[] = $value;
        }
        echo json_encode($messages);
        die();
    }

    /*
     * Ajax : user start Chat
     */

    public function startChat() {
        global $wpdb;
        $chkOperator = false;
        $rowsO = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_operators");
        foreach ($rowsO as $value) {
            if (abs(strtotime(date('Y-m-d h:i:s')) - strtotime($value->lastActivity)) < 10) {
                $chkOperator = true;
            }
        }
        if ($chkOperator) {
            echo '1';
        } else {
            echo 'nobody';
        }
        die();
    }

    /*
     * Ajax: send email
     */

    public function sendEmail() {
        $settings = $this->getSettings();
        $userID = esc_sql($_POST['userID']);
        $username = esc_sql($_POST['username']);
        $email = esc_sql($_POST['email']);
        $msg = esc_sql($_POST['message']);

        $headers = "Return-Path: " . $email . "\n";
        $headers .= "From:" . $email . "\n";
        $headers .= "X-Mailer: PHP " . phpversion() . "\n";
        $headers .= "Reply-To: " . $email . "\n";
        $headers .= "X-Priority: 3 (Normal)\n";
        $headers .= "Mime-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $content = '<p>' . nl2br(stripslashes($msg)) . '</p>';

        wp_mail($settings->adminEmail, $settings->emailSubject, $content, $headers);
        die();
    }

    /*
     * Ajax : check new messages
     */

    public function check_user_chat() {
        $logID = esc_sql($_POST['logID']);
        global $wpdb;
        $date_past = date('Y-m-d H:i:s', time() - 1 * 60);
        $rep = array();
        $rep['messages'] = array();
        $rowsC = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE id=$logID");
        $rep['finished'] = $rowsC[0]->finished;

        if ($rowsC[0]->operatorID > 0) {
            if (abs(strtotime(date('Y-m-d h:i:s')) - strtotime($rowsC[0]->operatorLastActivity)) > 20) {
                $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $logID));
                $rep['finished'] = 1;
            }
            $rowsO = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_operators WHERE userID=" . $rowsC[0]->operatorID . " LIMIT 1");
            if (count($rowsO) == 0) {
                $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $logID));
                $rep['finished'] = 1;
            }
        } else {
            $rowsO = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_operators");
            if (count($rowsO) == 0) {
                $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $logID));
                $rep['finished'] = 1;
            }
        }

        $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=$logID  AND isOperator=1 AND isRead=0");
        foreach ($rows as $value) {
            if ($value->isOperator) {
                $value->avatarOperator = get_avatar($value->userID, 37);
                $user = get_userdata($value->userID);
                $username = $user->user_login;
                $value->username = $username;
            }
            $value->content = stripslashes(nl2br($value->content));
            $rep['messages'][] = $value;
            $wpdb->update($wpdb->prefix . "vcht_messages", array('isRead' => true), array('id' => $value->id));
        }
        $wpdb->update($wpdb->prefix . "vcht_logs", array('lastActivity' => date('Y-m-d h:i:s')), array('id' => $logID));
        echo json_encode($rep);
        die();
    }

    /*
     * Receive new message from user
     */

    public function new_message() {
        global $wpdb;
        $userID = $_POST['userID'];
        $operatorID = $_POST['operatorID'];
        $username = esc_sql($_POST['username']);
        $message = esc_sql($_POST['message']);
        $message = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $message);
        $logID = $_POST['logID'];
        $operatorID = $_POST['operatorID'];
        $url = ($_POST['url']);


        if ($logID == 0) {
            if ($userID > 0) {
                $user = get_userdata($userID);
                $username = $user->user_login;
                $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE finished=0 AND operatorID=$operatorID AND userID=$userID  LIMIT 1");
            } else {
                $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs  WHERE finished=0 AND  operatorID=$operatorID AND username='$username'  LIMIT 1");
            }
            if (count($rows) > 0) {
                $logID = $rows[0]->id;
                $operatorID = $rows[0]->operatorID;
            } else {
                $rows_affected = $wpdb->insert($wpdb->prefix . "vcht_logs", array('userID' => $userID, 'username' => $username, 'operatorID' => $operatorID, 'date' => date('Y-m-d h:i:s'),'ip'=>$_SERVER['REMOTE_ADDR']));
                $logID = $wpdb->insert_id;
            }
        }
        $wpdb->update($wpdb->prefix . "vcht_logs", array('lastActivity' => date('Y-m-d h:i:s')), array('id' => $logID));

        $rows_affected = $wpdb->insert($wpdb->prefix . "vcht_messages", array('logID' => $logID, 'content' => stripslashes($message), 'date' => date('Y-m-d h:i:s'), 'url' => $url));
        echo '{"logID": "' . $logID . '", "operatorID": "' . $operatorID . '"}';
        die();
    }

    /**
     * Main Instance
     *
     *
     * @since 1.0.0
     * @static
     * @return Main instance
     */
    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

// End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

// End __wakeup()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number() {
        update_option($this->_token . '_version', $this->_version);
    }

}
