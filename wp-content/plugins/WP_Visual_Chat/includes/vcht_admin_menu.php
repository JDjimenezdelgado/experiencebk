<?php
if (!defined('ABSPATH'))
    exit;

class vcht_admin_menu {

    /**
     * The single instance 
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     * @var 	object
     * @access  public
     * @since 	1.0.0
     */
    public $parent = null;

    /**
     * Prefix for plugin settings.
     * @var     string
     * @access  publicexport
     * 
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

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

    public function __construct($parent) {
        $this->_token = 'vcht';
        $this->parent = $parent;
        $this->dir = dirname($parent->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $parent->file)));

        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('admin_init', array($this, 'checkRoles'));
        add_action('wp_ajax_nopriv_vcht_settings_save', array($this, 'settings_save'));
        add_action('wp_ajax_vcht_settings_save', array($this, 'settings_save'));
        add_action('wp_ajax_nopriv_vcht_texts_save', array($this, 'texts_save'));
        add_action('wp_ajax_vcht_texts_save', array($this, 'texts_save'));


        add_action('wp_ajax_nopriv_vcht_check_operator_chat', array($this, 'check_operator_chat'));
        add_action('wp_ajax_vcht_check_operator_chat', array($this, 'check_operator_chat'));
        add_action('wp_ajax_nopriv_vcht_getLogChat', array($this, 'getLogChat'));
        add_action('wp_ajax_vcht_getLogChat', array($this, 'getLogChat'));
        add_action('wp_ajax_nopriv_vcht_acceptChat', array($this, 'acceptChat'));
        add_action('wp_ajax_vcht_acceptChat', array($this, 'acceptChat'));
        add_action('wp_ajax_nopriv_vcht_operatorSay', array($this, 'operatorSay'));
        add_action('wp_ajax_vcht_operatorSay', array($this, 'operatorSay'));
        add_action('wp_ajax_nopriv_vcht_recoverChats', array($this, 'recoverChats'));
        add_action('wp_ajax_vcht_recoverChats', array($this, 'recoverChats'));
        add_action('wp_ajax_nopriv_vcht_closeChat', array($this, 'closeChat'));
        add_action('wp_ajax_vcht_closeChat', array($this, 'closeChat'));
        add_action('wp_ajax_nopriv_vcht_operatorConnect', array($this, 'operatorConnect'));
        add_action('wp_ajax_vcht_operatorConnect', array($this, 'operatorConnect'));
        add_action('wp_ajax_nopriv_vcht_operatorDisconnect', array($this, 'operatorDisconnect'));
        add_action('wp_ajax_vcht_operatorDisconnect', array($this, 'operatorDisconnect'));
        add_action('wp_ajax_nopriv_vcht_transferChat', array($this, 'transferChat'));
        add_action('wp_ajax_vcht_transferChat', array($this, 'transferChat'));

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);
    }

    /*
     * Check roles capabilities
     */

    public function checkRoles() {
        global $wp_roles;
        $settings = $this->getSettings();
        $rolesNew = explode(',', $settings->rolesAllowed);
        foreach ($wp_roles->roles as $role) {
            if (strtolower($role['name']) != 'administrator') {
                if (in_array(($role['name']), $rolesNew)) {
                    $wp_roles->add_cap(strtolower($role['name']), 'visual_chat');
                } else {
                    $wp_roles->remove_cap(strtolower($role['name']), 'visual_chat');
                }
            }
        }
    }

    /**
     * Add menu to admin
     * @return void
     */
    public function add_menu_item() {
        add_menu_page('Visual Chat', 'Visual Chat', 'visual_chat', 'vcht-console', array($this, 'submenu_console'), 'dashicons-format-chat');
        add_submenu_page('vcht-console', 'Chat logs', 'Chat logs', 'visual_chat', 'vcht-logsList', array($this, 'submenu_logsList'));
        add_submenu_page('vcht-console', 'Settings', 'Settings', 'visual_chat', 'vcht-settings', array($this, 'submenu_settings'));
        add_submenu_page('vcht-console', 'Import', 'Import', 'manage_options', 'vcht-import', array($this, 'submenu_import'));
        add_submenu_page('vcht-console', 'Export', 'Export', 'manage_options', 'vcht-export', array($this, 'submenu_export'));
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_styles($hook = '') {
        if (isset($_GET['page']) && $_GET['page'] == 'vcht-console') {
            wp_register_style($this->_token . '-bootstrap', esc_url($this->assets_url) . 'bootstrap/css/bootstrap.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-bootstrap');
            wp_register_style($this->_token . '-flat-ui', esc_url($this->assets_url) . 'css/flat-ui.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-flat-ui');
            wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-admin');
            wp_register_style($this->_token . '-colorsA', esc_url($this->assets_url) . 'css/colors_admin.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-colorsA');
        }
        if (isset($_GET['page']) && strrpos($_GET['page'], 'vcht-settings') !== false) {
            wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.css', array(), $this->_version);
            wp_enqueue_style($this->_token . '-colpick');
        }
        if (isset($_GET['page']) && $_GET['page'] == 'vcht-settings') {
            wp_enqueue_style('thickbox');
        }
    }

// End admin_enqueue_styles()

    /*
     * Check plugin state
     */
    public function checkUpdates() {
        if (strpos(get_admin_url(), '?page=vcht-settings') === false && strpos(get_admin_url(), '?page=vcht') !== false && !$this->isUpdated()) {
            wp_redirect(admin_url('admin.php?page=vcht-settings'));
        }
        if (!isset($_COOKIE['pll_updateC']) || $_COOKIE['pll_updateC'] == '0') {
            $this->form_checkUpdates();
        }
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_scripts($hook = '') {
        if (isset($_GET['page']) && $_GET['page'] == 'vcht-console') {
            wp_register_script($this->_token . '-jquery-ui', esc_url($this->assets_url) . 'js/jquery-ui-1.10.3.custom.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-jquery-ui');
            wp_register_script($this->_token . '-jquery-touch-punch', esc_url($this->assets_url) . 'js/jquery.ui.touch-punch.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-jquery-touch-punch');
            wp_register_script($this->_token . '-bootstrap', esc_url($this->assets_url) . 'js/bootstrap.min.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-bootstrap');
            wp_register_script($this->_token . '-bootstrap-select', esc_url($this->assets_url) . 'js/bootstrap-select.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-bootstrap-select');
            wp_register_script($this->_token . '-bootstrap-switch', esc_url($this->assets_url) . 'js/bootstrap-switch.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-bootstrap-switch');
            wp_register_script($this->_token . '-flatui-checkbox', esc_url($this->assets_url) . 'js/flatui-checkbox.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-flatui-checkbox');
            wp_register_script($this->_token . '-flatui-radio', esc_url($this->assets_url) . 'js/flatui-radio.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-flatui-radio');
            wp_register_script($this->_token . '-jquery-tagsinput', esc_url($this->assets_url) . 'js/jquery.tagsinput.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-jquery-tagsinput');
            wp_register_script($this->_token . '-jquery-placeholder', esc_url($this->assets_url) . 'js/jquery.placeholder.js', array(), $this->_version);
            wp_enqueue_script($this->_token . '-jquery-placeholder');
            wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin.min.js', 'jquery', $this->_version);
            wp_enqueue_script($this->_token . '-admin');
            $user = false;
            $avatarOperator = '';
            $userID = 0;
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                $userID = $current_user->ID;
                $user = $current_user->user_login;
                $avatarOperator = get_avatar($userID, 37);
            }
            wp_localize_script($this->_token . '-admin', 'vcht_operatorID', array($userID));
            wp_localize_script($this->_token . '-admin', 'vcht_operator', array($user));
            wp_localize_script($this->_token . '-admin', 'vcht_operatorAvatar', array($avatarOperator));
        }
        if (isset($_GET['page']) && ($_GET['page'] == 'vcht-settings' || $_GET['page'] == 'vcht-logsList')) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_register_script($this->_token . '-colpick', esc_url($this->assets_url) . 'js/colpick.js', 'jquery', $this->_version);
            wp_enqueue_script($this->_token . '-colpick');
            wp_register_script($this->_token . '-adminSettings', esc_url($this->assets_url) . 'js/admin_settings.js', 'jquery', $this->_version);
            wp_enqueue_script($this->_token . '-adminSettings');
        }
    }

    /*
     * display console page     
     */

    public function submenu_console() {

        $settings = $this->getSettings();
        $current_user = wp_get_current_user();
        $userID = $current_user->ID;
        $user = $current_user->user_login;
        $avatarOperator = get_avatar($userID, 37);

            ?>
            <iframe id="vcht_consoleFrame" src="<?php echo get_site_url(); ?>"></iframe>
            <div id="vcht_consolePanel">
                <div class="container">

                    <nav class="navbar navbar-inverse navbar-embossed" role="navigation">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-01">
                                <span class="sr-only">Toggle navigation</span>
                            </button>
                        </div>
                        <div class="collapse navbar-collapse" id="navbar-collapse-01">
                            <ul id="usersList"  class="nav navbar-nav navbar-left ">   
                                <li class="operatorTab text-center">
                                    <?php echo $avatarOperator; ?>
                                    <strong id="vcht_operatorname"></strong>        
                                    <input type="checkbox" id="vcht_onlineCB" data-toggle="switch" />
                                </li>
                            </ul>
                            <ul class="nav pull-right">
                                <li>
                                    <a href="javascript:" onclick="vcht_toggleChatPanel();" id="vcht_btnMinimize"><span class="glyphicon glyphicon-chevron-down"></span></a>
                                </li>
                                <li>
                                    <a href="admin.php?page=vcht-logsList" onclick="vcht_operatorDisconnect();"><span class="glyphicon glyphicon-remove"></span></a>
                                </li>
                            </ul>
                        </div><!-- /.navbar-collapse -->
                    </nav>

                    <div id="vcht_consolePanelContent">
                        <span id="vcht_loader" class="ouro ouro2">
                            <span class="left"><span class="anim"></span></span>
                            <span class="right"><span class="anim"></span></span>
                        </span>
                        <div data-panel="chat" data-logid="0" class="row-fluid">
                            <div class="col-md-2 palette palette-wet-asphalt" id="vcht_userInfos">                            
                                <div class="vcht_avatarImg"></div>
                                <p>
                                    <strong></strong>
                                </p>
                            </div>
                            <div class="col-md-9">
                                <div id="vcht_chatContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="vcht_infosPanel">
                <div class="container palette palette-wet-asphalt">
                    <div class="col-md-12 text-center">

                    </div>
                </div>            
            </div>
            <audio id="vcht_audioMsg" controls data-enable="<?php
            if ($settings->playSound) {
                echo 'true';
            } else {
                echo 'false';
            }
            ?>">
                <source src="<?php echo $this->assets_url; ?>sound/message.ogg" type="audio/ogg">
                <source src="<?php echo $this->assets_url; ?>sound/message.mp3" type="audio/mpeg">
            </audio>
            <?php
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
     * Recover countries from ip
     * @access  public
     * @since   2.0
     * @return  void
     */
    function checkCountries() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_logs";
        $logs = $wpdb->get_results("SELECT * FROM $table_name WHERE ip!='' AND country=''");
        foreach ($logs as $log) {

            try {
                $country = 'unknow';
                $city = 'unknow';
                $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=$log->ip");
                $country = $xml->geoplugin_countryName;
                $city = $xml->geoplugin_city;

                $wpdb->update($table_name, array('country' => $country, 'city' => $city), array('id' => $log->id));
            } catch (Exception $exc) {
                
            }
        }
    }

    /**
     * Menu import render
     * @return void
     */
    function submenu_import() {
        global $wpdb;
        ?>
        <div class="wrap wpeImport">
            <h2>Import data</h2>
            <?php
            $displayForm = true;
            $settings = $this->getSettings();
//            $pageID = $settings->form_page_id;
            if (isset($_GET['import']) && isset($_FILES['importFile'])) {
                $error = false;
                if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
                    mkdir(plugin_dir_path(__FILE__) . '../tmp');
                    chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
                }
                $target_path = plugin_dir_path(__FILE__) . '../tmp/export_visual_chat.zip';
                if (@move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path)) {


                    $upload_dir = wp_upload_dir();
                    if (!is_dir($upload_dir['path'])) {
                        mkdir($upload_dir['path']);
                    }

                    $zip = new ZipArchive;
                    $res = $zip->open($target_path);
                    if ($res === TRUE) {
                        $zip->extractTo(plugin_dir_path(__FILE__) . '../tmp/');
                        $zip->close();

                        $jsonfilename = 'export_visual_chat.json';
                        if (!file_exists(plugin_dir_path(__FILE__) . '../tmp/export_visual_chat.json')) {
                            $jsonfilename = 'export_visual_chat';
                        }

                        $file = file_get_contents(plugin_dir_path(__FILE__) . '../tmp/' . $jsonfilename);
                        $dataJson = json_decode($file, true);

                        $table_name = $wpdb->prefix . "vcht_settings";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['settings'] as $key => $value) {
                            $wpdb->insert($table_name, $value);
                        }

                        $table_name = $wpdb->prefix . "vcht_logs";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['logs'] as $key => $value) {
                            $wpdb->insert($table_name, $value);
                        }

                        $table_name = $wpdb->prefix . "vcht_messages";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['messages'] as $key => $value) {
                            $wpdb->insert($table_name, $value);
                        }

                        $table_name = $wpdb->prefix . "vcht_texts";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['texts'] as $key => $value) {
                            $wpdb->insert($table_name, $value);
                        }

                        $files = glob(plugin_dir_path(__FILE__) . '../tmp/*');
                        foreach ($files as $file) {
                            if (is_file($file))
                                unlink($file);
                        }

                        $this->updateCSS();
                    } else {
                        $error = true;
                    }
                } else {
                    $error = true;
                }
                if ($error) {
                    echo '<div class="error">An error occurred during the transfer</div>';
                } else {
                    $displayForm = false;
                    echo '<div class="updated">Data has been imported.</div>';
                }
            }
            if ($displayForm) {
                ?>
                <p>
                    Import here the zip file created using the "Export" tool.
                </p>
                <div class="error" style="color: red;">
                    WARNING: import data will overwrite existing ones!
                </div>
                <form action="admin.php?page=vcht-import&import=1" method="post" enctype="multipart/form-data">
                    <p>
                        <input id="importFile" type="file" name="importFile" placeholder="Select the .zip file"/>
                        <label for="importFile"> <span class="description">Select the generated .zip file</span> </label>
                    </p>
                    <p>
                        <button type="submit" class="button-primary">
                            Import
                        </button>
                    </p>
                </form>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Menu export render
     * @return void
     */
    function submenu_export() {
        global $wpdb;

        if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
            mkdir(plugin_dir_path(__FILE__) . '../tmp');
            chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
        }

        $destination = plugin_dir_path(__FILE__) . '../tmp/export_visual_chat.zip';
        $zip = new ZipArchive();
        if (file_exists($destination)) {
            unlink($destination);
        }
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            return false;
        }

        $jsonExport = array();
        $table_name = $wpdb->prefix . "vcht_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        $jsonExport['settings'] = $settings;

        $table_name = $wpdb->prefix . "vcht_logs";
        $steps = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }
        $jsonExport['logs'] = $steps;

        $table_name = $wpdb->prefix . "vcht_messages";
        $items = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $items[] = $row;
        }
        $jsonExport['messages'] = $items;

        $table_name = $wpdb->prefix . "vcht_texts";
        $items = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $items[] = $row;
        }
        $jsonExport['texts'] = $items;

        $fp = fopen(plugin_dir_path(__FILE__) . '../tmp/export_visual_chat.json', 'w');
        fwrite($fp, json_encode($jsonExport));
        fclose($fp);

        $zip->addfile(plugin_dir_path(__FILE__) . '../tmp/export_visual_chat.json', 'export_visual_chat.json');
        $zip->close();
        ?>
        <div class="wrap wpeExport">
            <h2>Export data</h2>
            <p>
                Export all this plugin datas to a zip file will can be imported on another website.
            </p>
            <p>
                <a download class="button-primary" href="<?php echo esc_url(trailingslashit(plugins_url('/', $this->parent->file))) . 'tmp/export_visual_chat.zip'; ?>">Export</a>
            </p>
        </div>
        <?php
    }

    /*
     * Display texts pages
     */

    public function submenu_texts() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_texts";
        $texts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");
        ?>
        <div class="wrap">
            <h2>Chat texts</h2>
            <div id="vcht_response"></div>
            <form id="form_texts" method="post" action="#" onsubmit="qc_process(this);
                    return false;">
                <table class="form-table">
                    <?php
                    foreach ($texts as $text) {
                        echo '<tr>
                                <td colspan="2">';
                        if ($text->isTextarea) {
                            echo '<textarea style="width: 340px;" id="t_' . $text->id . '" name="t' . $text->id . '"  placeholder="' . $text->original . '" >' . $text->content . '</textarea>';
                        } else {
                            echo '<input style="width: 340px;" id="t_' . $text->id . '" type="text" name="t' . $text->id . '"  placeholder="' . $text->original . '" value="' . $text->content . '">';
                        }
                        echo '<label for="t_' . $text->id . '">
                                        <span class="description">Original text : <i>' . $text->original . '</i></span>
                                    </label>
                                </td>
                            </tr>';
                    }
                    ?>   
                    <tr>
                        <td colspan="2">
                            <input type="submit" value="Save" class="button-primary"/>
                        </td>                            
                    </tr>  
                </table>
                <script>
                    function qc_process(e) {
                        var error = false;
                        if (!error) {
                            jQuery("#vcht_response").hide();
                            var data = {action: "vcht_texts_save"};
                            jQuery('#form_texts input, #form_texts textarea').each(function() {
                                if (jQuery(this).attr('name')) {
                                    eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                                }
                            });

                            jQuery.post(ajaxurl, data, function(response) {
                                jQuery("#vcht_response").html('<div id="message" class="updated"><p>Texts <strong>saved</strong>.</p></div>');
                                jQuery("#vcht_response").fadeIn(250);
                            });
                        }
                    }
                </script>
            </form>
        </div>
        <?php
    }

    /*
     * Save texts
     */

    public function texts_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "vcht_texts";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action') {
                $key = substr($key, 1);
                $wpdb->update($table_name, array('content' => stripslashes($value)), array('id' => $key));
            }
        }
        $response = '<div id="message" class="updated"><p>Texts <strong>saved</strong>.</p></div>';

        echo $response;
        die();
    }

    /*
     * display settings page     
     */

    public function submenu_settings() {
        $settings = $this->getSettings();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Chat Settings</h2>            
            
            <div id="vcht_response"></div>
            <h2 class="nav-tab-wrapper">
                <?php
                if (isset($_GET['tab']))
                    $tab = $_GET['tab'];
                else
                    $tab = 'general';

                $active = '';
                if ($tab == 'general') {
                    $active = 'nav-tab-active';
                }
                echo '<a class="nav-tab ' . $active . '" href="?page=vcht-settings&tab=general">General</a>';
                $active = '';
                if ($tab == 'colors') {
                    $active = 'nav-tab-active';
                }
                echo '<a class="nav-tab ' . $active . '" href="?page=vcht-settings&tab=colors">Colors</a>';
                $active = '';
                if ($tab == 'texts') {
                    $active = 'nav-tab-active';
                }
                echo '<a class="nav-tab ' . $active . '" href="?page=vcht-settings&tab=texts">Texts</a>';
                ?>
            </h2>
            <form id="form_settings" method="post" action="#" onsubmit="qc_process(this);
                    return false;">
                <input id="id" type="hidden" name="id" value="1">
                <table class="form-table">
                    <?php
                    switch ($tab) {
                        case 'general' :
                            ?>    
							<tr style="display:none">
                                <th scope="row">PageID</th>
                                <td>
                                    <input id="pageID" type="text" name="pageID"  value="<?php echo $settings->pageID; ?>">
                                    <label for="pageID">
                                        <span class="description">Select an empty page</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Purchase License Code</th>
                                <td>
                                    <input id="purchaseCode" type="text" name="purchaseCode"  placeholder="Enter the purchase license" value="<?php echo $settings->purchaseCode; ?>">
                                    <label for="purchaseCode">
                                        <span class="description"><a href="<?php echo $this->parent->assets_url; ?>img/purchase_code_1200.png" target="_blank">How to find my purchase code ? </a></span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Default operator picture</th>
                                <td>
                                    <input id="chatLogo" type="text" name="chatLogo" value="<?php
                                    echo $settings->chatLogo;
                                    ?>"  />
                                    <input  class="imageBtn button" type="button" value="Upload Image" />
                                    <label for="chatLogo">
                                        <span class="description">Choose a picture</span>
                                    </label>
                                </td>
                            </tr>    

                            <tr>
                                <th scope="row">Default customer picture</th>
                                <td>
                                    <input id="chatDefaultPic" type="text" name="chatDefaultPic" value="<?php
                                    echo $settings->chatDefaultPic;
                                    ?>"  />
                                    <input  class="imageBtn button" type="button" value="Upload Image" />
                                    <label for="chatDefaultPic">
                                        <span class="description">Choose a picture</span>
                                    </label>
                                </td>
                            </tr>   

                            <tr>
                                <th scope="row">Play sound as notification ?</th>
                                <td>
                                    <select id="playSound" name="playSound" >
                                        <option value="0">No</option>
                                        <option value="1" <?php
                                        if ($settings->playSound) {
                                            echo 'selected';
                                        }
                                        ?>>Yes</option>
                                    </select>                                    
                                </td>
                            </tr>    
                            <tr>
                                <th scope="row">Chat position</th>
                                <td>
                                    <select id="chatPosition" name="chatPosition" >
                                        <option value="right">Right</option>
                                        <option value="left" <?php
                                        if ($settings->chatPosition == 'left') {
                                            echo 'selected';
                                        }
                                        ?>>Left</option>
                                    </select>                                    
                                </td>
                            </tr> 


                            <tr>
                                <th scope="row">Admin email</th>
                                <td>
                                    <input id="adminEmail" type="email" name="adminEmail" value="<?php
                                    echo $settings->adminEmail;
                                    ?>"  />
                                    <label for="adminEmail">
                                        <span class="description">Enter the main contact email</span>
                                    </label>
                                </td>
                            </tr>             
                            <tr>
                                <th scope="row">Contact email subject</th>
                                <td>
                                    <input id="emailSubject" type="text" name="emailSubject" value="<?php
                                    echo $settings->emailSubject;
                                    ?>"  />
                                    <label for="emailSubject">
                                        <span class="description">This is the subject of the email when a user contacts you</span>
                                    </label>
                                </td>
                            </tr>  
                            <tr>
                                <th scope="row">Roles allowed to chat</th>
                                <td>
                                    <?php
                                    global $wp_roles;
                                    foreach ($wp_roles->roles as $role) {
                                        $selected = '';
                                        $disabled = '';
                                        if (array_key_exists('visual_chat', $role['capabilities']) && $role['capabilities']['visual_chat'] == 1) {
                                            $selected = 'checked';
                                        }
                                        if (strtolower($role['name']) == 'administrator') {
                                            $disabled = 'disabled';
                                        }
                                        if (strtolower($role['name']) == 'chat operator') {
                                            $selected = 'checked';
                                            $disabled = 'disabled';
                                        }
                                        echo '<p><label><input name="rolesAllowed" value="' . ($role['name']) . '" type="checkbox" ' . $selected . ' ' . $disabled . ' />' . $role['name'] . '</label></p>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Bounce FX</th>
                                <td>
                                    <select id="bounceFx" name="bounceFx">
                                        <option value="0" <?php
                                        if (!$settings->bounceFx) {
                                            echo 'selected';
                                        }
                                        ?>>No</option>
                                        <option value="1" <?php
                                        if ($settings->bounceFx) {
                                            echo 'selected';
                                        }
                                        ?>>Yes</option>
                                    </select>
                                    <label for="emailSubject">
                                        <span class="description">Give a bounce effect to the chat ?</span>
                                    </label>
                                </td>
                            </tr>  


                            <?php
                            break;
                        case 'colors' :
                            ?>

                            <tr>
                                <th scope="row">Default text color</th>
                                <td>
                                    <input id="colorB" type="text" name="colorB" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorB; ?>">
                                    <label for="colorB">
                                        <span class="description">ex : #34495E</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">User bubble background color and header</th>
                                <td>
                                    <input id="colorA" type="text" name="colorA" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorA; ?>">
                                    <label for="colorA">
                                        <span class="description">ex : #1abc9c</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">User bubble text color</th>
                                <td>
                                    <input id="colorE" type="text" name="colorE" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorE; ?>">
                                    <label for="colorE">
                                        <span class="description">ex : #FFFFFF</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Operator bubble background color</th>
                                <td>
                                    <input id="colorC" type="text" name="colorC" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorC; ?>">
                                    <label for="colorC">
                                        <span class="description">ex : #ECF0F1</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Operator bubble text color</th>
                                <td>
                                    <input id="colorF" type="text" name="colorF" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorF; ?>">
                                    <label for="colorF">
                                        <span class="description">ex : #bdc3c7</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Default button color</th>
                                <td>
                                    <input id="colorD" type="text" name="colorD" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->colorD; ?>">
                                    <label for="colorD">
                                        <span class="description">ex : #cacfd2</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Shining element color</th>
                                <td>
                                    <input id="shineColor" type="text" name="shineColor" class="colorpick" placeholder="Enter the color hex" value="<?php echo $settings->shineColor; ?>">
                                    <label for="shineColor">
                                        <span class="description">ex : #1abc9c</span>
                                    </label>
                                </td>
                            </tr>

                            <?php
                            break;
                        case 'texts':
                            global $wpdb;
                            $table_name = $wpdb->prefix . "vcht_texts";
                            $texts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id ASC");

                            foreach ($texts as $text) {
                                echo '<tr>
                                <td colspan="2">';
                                if ($text->isTextarea) {
                                    echo '<textarea style="width: 340px;" id="t_' . $text->id . '" name="t' . $text->id . '"  placeholder="' . $text->original . '" >' . $text->content . '</textarea>';
                                } else {
                                    echo '<input style="width: 340px;" id="t_' . $text->id . '" type="text" name="t' . $text->id . '"  placeholder="' . $text->original . '" value="' . $text->content . '"/>';
                                }
                                echo '<label for="t_' . $text->id . '">
                                        <span class="description">Original text : <i>' . $text->original . '</i></span>
                                    </label>
                                </td>
                            </tr>';
                            }

                            break;
                    }
                    ?>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="submit" value="Save" class="button-primary"/>
                        </td>                            
                    </tr>

                </table>
            </form>
        </div>
        <script>
            var formfield;
            jQuery(document).ready(function() {
                window.old_tb_remove = window.tb_remove;
                window.tb_remove = function() {
                    window.old_tb_remove();
                    formfield = null;
                };
                window.original_send_to_editor = window.send_to_editor;
                window.send_to_editor = function(html) {
                    if (formfield) {
                        fileurl = jQuery('img', html).attr('src');
                        jQuery(formfield).val(fileurl);
                        tb_remove();
                    } else {
                        window.original_send_to_editor(html);
                    }
                };
                jQuery('.imageBtn').click(function() {
                    formfield = jQuery(this).prev('input');
                    tb_show('', 'media-upload.php?TB_iframe=true');

                    return false;

                });
            });
        <?php
        if ($tab == 'texts') {
            ?>
                function qc_process(e) {
                    var error = false;
                    if (!error) {
                        jQuery("#vcht_response").hide();
                        var data = {
                            action: "vcht_texts_save"
                        };
                        jQuery('#form_settings input, #form_settings textarea').each(function() {
                            if (jQuery(this).attr('name')) {
                                eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                            }
                        });

                        jQuery.post(ajaxurl, data, function(response) {
                            jQuery("#vcht_response").html('<div id="message" class="updated"><p>Texts <strong>saved</strong>.</p></div>');
                            jQuery("#vcht_response").fadeIn(250);
                        });
                    }
                }
            <?php
        } else {
            ?>
                function qc_process(e) {

                    var error = false;
                    if (!error) {
                        jQuery("#vcht_response").hide();
                        var data = {action: "vcht_settings_save"};
                        jQuery('#form_settings input, #form_settings select, #form_settings textarea').each(function() {
                            if (jQuery(this).attr('name') && jQuery(this).attr('name') != 'rolesAllowed') {
                                eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                            }
                        });
                        var rolesAllowed = '';
                        jQuery('input[name=rolesAllowed]:checked').each(function() {
                            rolesAllowed += jQuery(this).val() + ',';
                        });
                        if (rolesAllowed.length > 0) {
                            rolesAllowed = rolesAllowed.substr(0, rolesAllowed.length - 1);
                        }
                        data.rolesAllowed = rolesAllowed;
                        jQuery.post(ajaxurl, data, function(response) {
                            jQuery("#vcht_response").html('<div id="message" class="updated"><p>Settings <strong>saved</strong>.</p></div>');
                            jQuery("#vcht_response").fadeIn(250);
                            setTimeout(function() {
                                document.location.href = document.location.href;
                            }, 400);
                        });
                    }
                }
        <?php } ?>
        </script>
        <?php
    }

    private function isUpdated() {
        $settings = $this->getSettings();
        if ($settings->updated) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Return license verification message
     */

    private function licenseMessage() {
        if (!$this->isUpdated()) {
            if (isset($_COOKIE['pll_updateC']) && ($_COOKIE['pll_updateC'] == '2')) {
                echo '<div id="message" class="error"><h3>Purchase Code already used</h3><p>This purchase code is already used on another website. Would you use this license for this site (the plugin will be disabled on the other site)?</p>'
                . '<p><a href="admin.php?page=vcht-settings&activateWebsite=1" class="button-primary">YES</a>&nbsp;<a href="admin.php?page=vcht-settings" class="button-secondary" style="margin-left: 10px;">NO, I have another license</a></p></div>';
            } else {
                echo '<div id="message" class="error"><h3>Purchase Code verification needed</h3><p>Please go to the <a href="admin.php?page=vcht-settings">settings panel</a>, then enter your License <a href="' . $this->parent->assets_url . 'img/purchase_code_1200.png" target="_blank">Purchase Code</a> .</p></div>';
            }
        }
    }

    /**
     * save settings
     * @return void
     */
    public function settings_save() {
        global $wpdb;
        global $wp_roles;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "vcht_settings";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action') {
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        $wpdb->update($table_name, $sqlDatas, array('id' => 1));
        $response = '<div id="message" class="updated"><p>Settings <strong>saved</strong>.</p></div>';
        $this->updateCSS();
        echo $response;
        die();
    }

    /**
     * update colors CSS
     * @return void
     */
    private function updateCSS() {
        $settings = $this->getSettings();

        chmod(plugin_dir_path(__FILE__) . '../assets/css/colors.css', 0747);
        chmod(plugin_dir_path(__FILE__) . '../assets/css/colors_front.css', 0747);
        chmod(plugin_dir_path(__FILE__) . '../assets/css/colors_admin.css', 0747);
        $colorsStyles = '
        body {
            color: ' . $settings->colorB . ';
        }
        .vcht_chat .palette-turquoise,.btn-primary,.btn-primary:hover,.btn-primary:active {
            background-color: ' . $settings->colorA . ';
            color: ' . $settings->colorE . ';
        }
        .vcht_chat .bubble_right .bubble_arrow {
            border-color: transparent transparent transparent ' . $settings->colorA . ';
        }
        .vcht_chat .palette-clouds {
            background-color: ' . $settings->colorC . ';
            color: ' . $settings->colorF . ';
        }
        .vcht_chat .form-control:focus {
            border-color: ' . $settings->colorA . ';    
        }
        .vcht_chat .anim {
            background: none repeat scroll 0 0 ' . $settings->colorA . ';
        }
        .vcht_chat .btn-default,.btn-default:hover,.btn-default:active {
            background-color: ' . $settings->colorD . ';
        }
        .vcht_chat .form-control {
            border-color: ' . $settings->colorD . ';
        }
        .vcht_chat .bubble_left .bubble_arrow {
            border-color: transparent ' . $settings->colorC . ' transparent transparent;            
        }
        .vcht_selectedDom {
            -moz-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;
            -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;
            -o-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;
            box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;
        }
        .nicescroll-rails > div {
            background-color: ' . $settings->colorD . ' !important;
        }
        .avatarImg {
            background-image: url(' . $settings->chatDefaultPic . ');
        }

        @-o-keyframes glow {
            0% {
                -o-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -o-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -o-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @-moz-keyframes glow {
            0% {
                -moz-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -moz-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -moz-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @-webkit-keyframes glow {
            0% {
                -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @keyframes glow {
            0% {
                box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ' ;
            }
            50% {
                box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;        
            }
            100% {
                box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ' ;
            }
        }
        ';

        $fp = fopen(plugin_dir_path(__FILE__) . '../assets/css/colors.css', 'w');
        fwrite($fp, $colorsStyles);
        fclose($fp);

        $colorsStyles2 = '
        .vcht_selectedDom {
            -moz-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' !important;
            -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' !important;
            -o-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' !important ;
            box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' !important ;
        }
        @-o-keyframes glow {
            0% {
                -o-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -o-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -o-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @-moz-keyframes glow {
            0% {
                -moz-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -moz-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -moz-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @-webkit-keyframes glow {
            0% {
                -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
            50% {
                -webkit-box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ';        
            }
            100% {
                -webkit-box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ';
            }
        }
        @keyframes glow {
            0% {
                box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ' ;
            }
            50% {
                box-shadow: 0px 0px 40px 0px ' . $settings->shineColor . ' ;        
            }
            100% {
                box-shadow: 0px 0px 10px 0px ' . $settings->shineColor . ' ;
            }
        }';
        $fp2 = fopen(plugin_dir_path(__FILE__) . '../assets/css/colors_front.css', 'w');
        fwrite($fp2, $colorsStyles2);
        fclose($fp2);

        $colorsStyles3 = '
        .bubble_photo {
            background-image: url(' . $settings->chatDefaultPic . ');
        }';
        $fp3 = fopen(plugin_dir_path(__FILE__) . '../assets/css/colors_admin.css', 'w');
        fwrite($fp3, $colorsStyles3);
        fclose($fp3);
    }

    /*
     * display logs list page     
     */

    public function submenu_logsList() {
        global $wpdb;
        $this->checkCountries();
        if (isset($_GET['log'])) {
            $logID = $_GET['log'];
            $table_name = $wpdb->prefix . "vcht_logs";
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$logID LIMIT 1");
            $logDatas = $rows[0];
            /* $table_name = $wpdb->prefix . "vcht_messages";
              $messages = $wpdb->get_results("SELECT * FROM $table_name WHERE logID=$logID ORDER BY id ASC"); */
            ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2>Chat Log Details</h2>
                <?php
                echo '<p>User : <strong>' . $logDatas->username . '</strong></p>';
                echo '<p>Date : <strong>' . $logDatas->date . '</strong></p>';

                $msgList = new vcht_MsgTable();
                $msgList->logID = $logID;
                $msgList->prepare_items();
                $msgList->display();
            } else {



                if (isset($_GET['remove'])) {
                    $table_name = $wpdb->prefix . "vcht_logs";
                    $wpdb->delete($table_name, array('id' => $_GET['remove']));
                    $table_name = $wpdb->prefix . "vcht_messages";
                    $wpdb->delete($table_name, array('logID' => $_GET['remove']));
                }

                $logsList = new vcht_LogsTable();
                if (isset($_GET['userID'])) {
                    $logsList->userID = $_GET['userID'];
                }
                if (isset($_GET['search'])) {
                    $logsList->search = $_GET['search'];
                }

                $logsList->prepare_items();
                ?>
                <div class="wrap">
                    <div id="icon-users" class="icon32"></div>
                    <h2>Chat Logs</h2>
                    <p>
                        <label>Filter by User : </label>
                        <?php
                        if (isset($_GET['userID']) && $_GET['userID'] != '0') {
                            wp_dropdown_users(array('show_option_all' => "All", 'selected' => $_GET['userID']));
                        } else {
                            wp_dropdown_users(array('show_option_all' => "All"));
                        }
                        ?>       
                        <label>or do a custom search : </label>
                        <input id="filter_custom" type="text" style="width: 260px;" placeholder="Enter a username or country, ip etc ..." value="<?php if (isset($_GET['search'])) {
                echo $_GET['search'];
            } ?>" /> <a href="javascript:" onclick="vcht_customFilter();" class="button-primary">Search</a>

                    </p>
                <?php $logsList->display(); ?>
                </div>                
                <?php
            }
            ?>
            <script>
                function vcht_testIframe() {
                    try {
                        return window.self !== window.top;
                    } catch (e) {
                        return true;
                    }
                }
                jQuery(document).ready(function() {
                    jQuery('#user').change(function() {
                        if (jQuery('#user').val() != '0') {
                            document.location.href = 'admin.php?page=vcht-logsList&userID=' + jQuery('#user').val();
                        } else {
                            document.location.href = 'admin.php?page=vcht-logsList';
                        }
                    });
                    if (vcht_testIframe()) {
                        jQuery('#adminmenuwrap,#adminmenuback').hide();
                        jQuery('#wpcontent, #wpfooter').css('margin-left', '10px');
                    }
                    jQuery('#filter_custom').keyup(function() {
                        jQuery('#user').val('0');
                    });
                });
            </script>
            <?php
        }

        /*
         * Activate license
         */
        private function activateLicense() {
			$wpdb->update($wpdb->prefix . "vcht_settings", array('updated' => 1), array('id' => 1));
			setcookie('pll_updateC', 1, time() + 60 * 60 * 24 * 1);
        }

        /*
         * check updates 
         */
        private function form_checkUpdates() {
			$wpdb->update($wpdb->prefix . "vcht_settings", array('updated' => 1), array('id' => 1));
			setcookie('pll_updateC', 1, time() + 60 * 60 * 24 * 1);
        }

        /*
         *  Ajax: Operator new message
         */

        public function operatorSay() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;

            $msg = esc_sql($_POST['msg']);
            $msg = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $msg);
            $domElement = esc_sql($_POST['domElement']);
            $url = esc_sql($_POST['url']);
            $logID = esc_sql($_POST['logID']);
            $wpdb->insert($wpdb->prefix . "vcht_messages", array('logID' => $logID, 'content' => stripslashes($msg), 'url' => $url, 'domElement' => $domElement, 'isOperator' => true, 'date' => date('Y-m-d h:i:s'), 'userID' => $operatorID));
            echo 1;
            die();
        }

        /*
         * Ajax : get currents chats
         */

        public function recoverChats() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;

            $rep = array();
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE finished=0 AND operatorID=$operatorID");
            foreach ($rows as $value) {

                $userPic = "";
                if ($value->userID > 0) {
                    $userPic = get_avatar($value->userID, 48);
                }
                $value->avatarImg = $userPic;
                $msgs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=$value->id");
                $value->messages = array();
                foreach ($msgs as $msg) {
                    if ($msg->isOperator) {
                        $msg->avatarOperator = get_avatar($msg->userID, 48);
                        $user = get_userdata($msg->userID);
                        $username = $user->user_login;
                        $msg->username = $username;
                        $msg->content = nl2br(stripslashes($msg->content));
                    }
                    $value->messages[] = $msg;
                    $wpdb->update($wpdb->prefix . "vcht_messages", array('isRead' => true), array('id' => $msg->id));
                }
                $rep[] = $value;
            }
            echo json_encode($rep);
            die();
        }

        /*
         * Ajax: transfer chat to operator
         */

        public function transferChat() {
            global $wpdb;
            $operatorID = esc_sql($_POST['operatorID']);
            $logID = esc_sql($_POST['logID']);
            $wpdb->update($wpdb->prefix . "vcht_logs", array('operatorID' => $operatorID, 'transfer' => true), array('id' => $logID));
            echo '1';
            die();
        }

        /*
         * Ajax : operator close chat
         */

        function closeChat() {
            $logID = $_POST['logID'];
            global $wpdb;
            $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $logID));
            echo '1';
            die();
        }

        /*
         * Ajax:  return operator current chats
         */

        public function check_operator_chat() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;
            $operator = $current_user->user_login;

            $rep = array();
            $rep['chatRequests'] = array();
            $rep['chats'] = array();
            $rep['chatsClosed'] = array();
            $rep['operators'] = array();
            $rep['transfers'] = array();

            // check new chat requests
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE finished=0 AND operatorID=0");
            foreach ($rows as $value) {
                $wpdb->update($wpdb->prefix . "vcht_logs", array('operatorLastActivity' => date('Y-m-d h:i:s')), array('id' => $value->id));
                if (abs(strtotime(date('Y-m-d h:i:s')) - strtotime($value->lastActivity)) > 20) { // close chat
                    $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $value->id));
                } else {
                    $rep['chatRequests'][] = $value;
                }
            }

            // check chats
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE finished=0 AND operatorID=" . $operatorID . ' AND transfer=0');
            foreach ($rows as $value) {
                //operatorLastActivity
                $wpdb->update($wpdb->prefix . "vcht_logs", array('operatorLastActivity' => date('Y-m-d h:i:s')), array('id' => $value->id));
                if (abs(strtotime(date('Y-m-d h:i:s')) - strtotime($value->lastActivity)) > 20) { // close chat
                    $wpdb->update($wpdb->prefix . "vcht_logs", array('finished' => true), array('id' => $value->id));
                }
                $msgs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=$value->id AND isOperator=0 AND isRead=0");
                $value->timePast = abs(strtotime(date('Y-m-d h:i:s')) - strtotime($value->date));
                $value->messages = array();
                foreach ($msgs as $msg) {
                    $msg->content = nl2br(stripslashes($msg->content));
                    $value->messages[] = $msg;
                    $wpdb->update($wpdb->prefix . "vcht_messages", array('isRead' => true), array('id' => $msg->id));
                }
                /* if ($value->userID > 0) {
                  $value->avatarImg = get_avatar($value->userID, 37);
                  } */
                $rep['chats'][] = $value;
            }

            // check closed chats
            if (strlen($_POST['currentChats']) > 0) {
                $currentChats = explode(',', $_POST['currentChats']);
                if (count($currentChats) > 0) {
                    foreach ($currentChats as $currentChat) {
                        $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE sent=0 AND finished=1 AND id=" . $currentChat . " LIMIT 1");
                        if (count($rows) > 0) {
                            $rep['chatsClosed'][] = $currentChat;
                            $wpdb->update($wpdb->prefix . "vcht_logs", array('sent' => true), array('id' => $currentChat));
                        }
                    }
                }
            }

            // check transfers
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE transfer=1 AND finished=0 AND operatorID=" . $operatorID);
            foreach ($rows as $value) {
                $wpdb->update($wpdb->prefix . "vcht_logs", array('transfer' => false), array('id' => $value->id));

                $msgs = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=$value->id");
                foreach ($msgs as $msg) {
                    $msg->content = nl2br(stripslashes($msg->content));
                    if ($msg->isOperator) {
                        $user = get_userdata($msg->userID);
                        $username = $user->user_login;
                        $msg->username = $username;
                    }
                    $value->messages[] = $msg;
                }
                $rep['transfers'][] = $value;
            }
            // return online operators
            $wpdb->update($wpdb->prefix . "vcht_operators", array('lastActivity' => date('Y-m-d h:i:s')), array('userID' => $operatorID));
            $rowsO = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_operators");
            foreach ($rowsO as $value) {
                if (abs(strtotime(date('Y-m-d h:i:s')) - strtotime($value->lastActivity)) < 20) { // close chat
                    $rep['operators'][] = $value;
                }
            }


//  echo '{"chatRequests":'. json_encode($rep['chatRequests'] ).',"chats":'.  json_encode($rep['chats'] ).'}';
            echo json_encode($rep);
            die();
        }

        /*
         * Ajax: operator accepts chat 
         */

        public function acceptChat() {
            global $wpdb;
            $logID = esc_sql($_POST['logID']);
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE id=" . $logID . " LIMIT 1");
            if ($rows[0]->operatorID == 0) {
                $current_user = wp_get_current_user();
                $operatorID = $current_user->ID;
                $wpdb->update($wpdb->prefix . "vcht_logs", array('operatorID' => $operatorID), array('id' => $logID));
                echo '1';
            } else {
                echo '0';
            }
            die();
        }

        /*
         * Ajax: operator Conexion
         */

        public function operatorConnect() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;
            $operator = $current_user->user_login;
            $rowsO = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_operators WHERE userID=$operatorID LIMIT 1");
            if (count($rowsO) > 0) {
                $wpdb->update($wpdb->prefix . "vcht_operators", array('lastActivity' => date('Y-m-d h:i:s')), array('userID' => $operatorID));
            } else {
                $wpdb->insert($wpdb->prefix . "vcht_operators", array('lastActivity' => date('Y-m-d h:i:s'), 'userID' => $operatorID, 'username' => $operator));
            }
        }

        /*
         * Ajax: operator disconnects
         */

        public function operatorDisconnect() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;
            $wpdb->delete($wpdb->prefix . "vcht_operators", array('userID' => $operatorID));
        }

        /*
         * Ajax: return a specific log infos
         */

        public function getLogChat() {
            global $wpdb;
            $current_user = wp_get_current_user();
            $operatorID = $current_user->ID;
            $rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_logs WHERE id=" . $_POST['logID'] . " LIMIT 1");
            if (count($rows) > 0) {
                if ($rows[0]->userID > 0) {
                    $rows[0]->avatarImg = get_avatar($rows[0]->userID, 37);
                }
                $rowsM = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "vcht_messages WHERE logID=" . $_POST['logID'] . " ORDER BY id ASC");
                $rows[0]->messages = array();
                foreach ($rowsM as $value) {
                    $value->content = nl2br(stripslashes($value->content));
                    $rows[0]->messages[] = $value;
                }
                echo json_encode($rows[0]);
            }
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
        public static function instance($parent) {
            if (is_null(self::$_instance)) {
                self::$_instance = new self($parent);
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
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
        }

// End __clone()

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.0.0
         */
        public function __wakeup() {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
        }

    }
    