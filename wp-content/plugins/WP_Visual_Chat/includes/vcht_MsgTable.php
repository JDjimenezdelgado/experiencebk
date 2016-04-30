<?php

if (!class_exists('vcht_MsgTable')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class vcht_MsgTable extends WP_List_Table {

    public $logID = 0;

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        $columns = array('id' => 'ID', 'date' => 'Hour', 'user' => 'user', 'msg' => 'Message', 'operator' => 'Is operator ?', 'url' => 'Page', 'domElement' => 'Element displayed');

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array('id');
        return null;
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        return null;
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . "vcht_logs";
        $rowsL = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$this->logID LIMIT 1");
        $log = $rowsL[0];
        $table_name = $wpdb->prefix . "vcht_messages";
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE logID=$this->logID ORDER BY id ASC");

        /*  $user = get_userdata($row->userID);
          $user = $user->user_login; */

        $data = array();
        foreach ($rows as $row) {
            if ($row->userID > 0) {
                $user = get_userdata($row->userID);
                $user = $user->user_login;
            } else {
                // $user = $log->username;
                $user = 'test';
            }

            $row->content = nl2br(stripslashes($row->content));
            $data[] = array('id' => $row->id, 'date' => substr($row->date, strrpos($row->date, ' ')), 'user' => $user, 'operator' => $row->isOperator, 'domElement' => $row->domElement, 'url' => $row->url, 'msg' => nl2br(stripslashes($row->content)));
        }
        return $data;
    }

    // Used to display the value of the id column
    public function column_id($item) {
        return $item['id'];
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id' :
            case 'date':
            case 'user':
                return $item[$column_name];
                break;
            case 'operator':
                if ($item['operator'] > 0) {
                    return 'Yes';
                } else {
                    return 'No';
                }
                break;
            case 'msg':
                return ($item[$column_name]);
                break;
            case 'url':
                $url = $item[$column_name];
                if (strrpos($url, site_url('/')) === false) {
                    
                } else {
                   $url = substr($url, strlen(site_url('/'))-1);
                }
                return '<a href="' . $item[$column_name] . '">' . $url . '</a>';
                break;
            case 'domElement':
                if ($item[$column_name] != "") {
                    $url = $item['url'];
                    if (strpos('?', $url) !== false) {
                        $url.='&vcht_element=' . $item['id'];
                    } else {
                        $url .= '?vcht_element=' . $item['id'];
                    }
                    return '<a href="' . $url . '" target="_blank">View the selected element</a>';
                } else {
                    return '';
                }
                break;
            default :
                return print_r($item, true);
        }
    }

}
