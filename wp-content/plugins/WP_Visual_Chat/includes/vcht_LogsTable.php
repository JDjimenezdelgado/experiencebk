<?php

if (!class_exists('vcht_LogsTable')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class vcht_LogsTable extends WP_List_Table {

    public $userID = 0;
    public $search = "";
    public $username = "";

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
        $columns = array('id' => 'ID', 'date' => 'Date', 'hour' => 'Hour', 'user' => 'User', 'operator' => 'Operator', 'ip' => 'IP', 'country' => 'Country', 'city' => 'City', 'view' => 'Read log', 'remove' => '');

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
        if ($this->userID > 0) {
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE userID=$this->userID ORDER BY id DESC");
        } else if ($this->search != "") {

            // date
            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE date LIKE '%$this->search%' ORDER BY date DESC");
            if (count($rows) == 0) {
                // username                
                $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE username LIKE '%$this->search%' ORDER BY date DESC");
                if (count($rows) == 0) {
                    // ip                
                    $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE ip LIKE '%$this->search%' ORDER BY date DESC");
                    if (count($rows) == 0) {
                        // country                
                        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE country LIKE '%$this->search%' ORDER BY date DESC");
                        if (count($rows) == 0) {
                            // city                
                            $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE city LIKE '%$this->search%' ORDER BY date DESC");
                            
                        }
                    }
                }
            }
        } else {
            $rows = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
        }

        $data = array();
        foreach ($rows as $row) {
            $user = $row->username;
            $role = '';
            if ($row->userID > 0) {
                $user = get_userdata($row->userID);
                $user = $user->user_login;
            }
            if ($row->operatorID > 0) {
                $operator = get_userdata($row->operatorID);
                $operator = $operator->user_login;
            } else {
                $operator = 'none';
            }
            $data[] = array('id' => $row->id, 'date' => substr($row->date, 0, strpos($row->date, ' ')), 'hour' => substr($row->date, strpos($row->date, ' ') + 1), 'userID' => $row->userID, 'user' => $user, 'operator' => $operator, 'operatorID' => $row->operatorID, 'remove' => '', 'view' => 'Read this log', 'ip' => $row->ip, 'country' => $row->country, 'city' => $row->city);
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
            case 'remove' :
                return '<a href="admin.php?page=vcht-logsList&remove=' . $item['id'] . '">Delete</a>';
                break;
            case 'user':
                if ($item['userID'] > 0) {
                    return '<a href="user-edit.php?user_id=' . $item['userID'] . '">' . $item[$column_name] . '</a>';
                } else {
                    return $item[$column_name];
                }
                break;
            case 'operator':
                if ($item['operatorID'] > 0) {
                    return '<a href="user-edit.php?user_id=' . $item['operatorID'] . '">' . $item[$column_name] . '</a>';
                } else {
                    return $item[$column_name];
                }
                break;
            case 'view':
                return '<a href="admin.php?page=vcht-logsList&log=' . $item['id'] . '">' . $item[$column_name] . '</a>';
                break;
            case 'id' :
            case 'date':
            case 'hour':
            case 'operator':
            case 'ip':
            case 'country':
            case 'city':
                return $item[$column_name];
                break;
            default :
                return print_r($item, true);
        }
    }

}
