<?php 

if(!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * List table class
 */

 class cln_collection_list_table extends \WP_List_Table
 {

    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'collection',
            'plural' => 'collections',
            'ajax' => false,
        ));
    }

    public function get_table_classes()
    {
        return array('widefat', 'fixed', 'striped', $this->_args['plural']);
    }

    /**
     * Message to show if no designation found
     *
     * @return void
     */
    public function no_items()
    {
        _e('Not found!', 'cln');
    }

      /**
     * Default column values if no callback found
     *
     * @param  object  $item
     * @param  string  $column_name
     *
     * @return string
     */
    //Function buat nambahin value kolom
    public function column_default($item, $column_name)
    {

        switch ($column_name) {
            case 'id':
                return $item->id;
            case 'title':
                $item->name = $item->title;
                return $item->title;
            case 'url_file':
                 return $item->url_file;
            case 'status':
                if($item->status == 1){
                    $item->status = 'Enable';
                }else{
                    $item->status = 'Disable';
                }
                return $item->status;
            case 'type': 
                if($item->type == 1){
                    $item->type = 'collection';
                }else{
                    $item->type = 'collection Slide';
                }        
                return $item->type;
            default:
                return isset($item->$column_name) ? $item->$column_name : '';
        }
    }

    /**
     * Get the column names
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            //  'cb'    => '<input type="checkbox" />',
            'name' => __('Title', 'cln'),
            'url_file' => __('File' , 'cln'),
            'status' => __('Status', 'cln')
        );

        return $columns;
    }

    /**
     * Render the designation name column
     *
     * @param  object  $item
     *
     * @return string
     */
    public function column_name($item)
    {
        $actions = array();       
        $actions['edit'] = sprintf('<a href="%s" data-id="%d" title="%s">%s</a>', admin_url('admin.php?page=collection&action=edit&id=' . $item->id), $item->id, __('Edit this item', 'cln'), __('Edit', 'cln'));
        $actions['delete'] = sprintf( '<a href="%s" onclick="return validate()" class="submitdelete" data-id="%d" title="%s">%s</a>', admin_url( 'admin.php?page=' . $_GET['page'] . '&action=delete&id=' . $item->id ), $item->id, __( 'Delete this item', 'cln' ), __( 'Delete', 'cln' ) );

        return sprintf('<a href="%1$s"><strong>%2$s</strong></a> %3$s', admin_url('admin.php?page=collection&action=view&id=' . $item->id), $item->title, $this->row_actions($actions));

    }

    public function current_action(){

        // check if our action(s) are set and handle them
        if (isset($_REQUEST['action']) && 'edit' === $_REQUEST['action']) {
            return 'edit';
        }
        if (isset($_REQUEST['action']) && 'delete' === $_REQUEST['action']) {
            return 'delete';
        }

        // let the parent class handle all other actions
        parent::current_action();

    }

        /**
     * Get sortable columns
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'name' => array('name', true),
        );

        return $sortable_columns;
    }

    public function get_bulk_actions()
    {
        $actions = array(
            'delete' => __('Delete', 'cln'),
        );
        return $actions;
    }

    public function process_bulk_action(){
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            if (!empty($ids)) {
                audition_event_delete_audition_event($ids);
            }
        }

    }

    // public function get_views_(){
    //     $status_links = array();
    //     $base_link = admin_url('admin.php?page=sample-page');

    //     foreach ($this->counts as $key => $value) {
    //         $class = ($key == $this->page_status) ? 'current' : 'status-' . $key;
    //         $status_links[$key] = sprintf('<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg(array('status' => $key), $base_link), $class, $value['label'], $value['count']);
    //     }

    //     return $status_links;

    // }

    public function get_per_page()
    {
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $per_page = get_user_meta($user, $screen_option, true);

        if (empty($per_page) || $per_page < 1) {
            $per_page = $screen->get_option('per_page', 'default');
        }

        return (int) $per_page;
    }
   

    /**
     * Prepare the class items
     *
     * @return void
     */
    public function prepare_items()
    {

        $this->process_bulk_action();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        if (isset($_GET['per_page']) && intval($_GET['per_page']) != '') {
            $per_page = $_GET['per_page'];
        } else {
            $per_page = $this->get_per_page();
        }

        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $this->page_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '2';

        // only ncessary because we have sample data
        $args = array(
            'offset' => $offset,
            'number' => $per_page,
        );

        if (isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'];
        }

        // if( isset($_REQUEST['s']) ) $args = array();

        $this->items = get_all_collection($args);

        $this->set_pagination_args(array(
            'total_items' => get_count_collection(),
            'per_page' => $per_page,
        ));
    }





 }