<?php Header ("Content-type: text/css; charset=utf-8");
/*
Plugin Name: UMW Comment Tracking Widget
Plugin URI: http://wordpress.org/plugins/UMW-Comment-Tracker/
Description: This widget keeps track of all of your comments
Author: Pat Galyen
Version: .9
Author URI: http://pa.t/
*/


function install () {
	global $wpdb;

  	

   global $current_user;
      get_currentuserinfo();

   
   $suffix = $current_user->ID;	

   $table_name = "my_comments_" . $suffix; 

   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
	   my_comment_ID bigint(20) NOT NULL AUTO_INCREMENT,
	   UNIQUE KEY id (my_comment_ID),
	   author_comment_ID bigint(20),	
	   post_author_ID bigint(20) NOT NULL,
	   comment_post_ID bigint(20) NOT NULL,
	   comment_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	   comment_content text NOT NULL,
	   user_id bigint(20) NOT NULL

    );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );

}


/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */


function add_dashboard_widgets() {



	install(); // makes sure table exists

 	wp_add_dashboard_widget(
                 'dashboard_widget',         // Widget slug.
                 'Comment Tracker',         // Title.
                 'dashboard_widget_function' // Display function.
        );	
}
add_action( 'wp_dashboard_setup', 'add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */




function dashboard_widget_function() {

/

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 


//add_action( 'admin_menu', 'add_my_menu_items' );

//function add_my_menu_items(){

  $hook = add_menu_page( 'My Plugin List Table', 'Comment Tracker', 'activate_plugins', 'my_list_test', 'my_render_list_page' );
  add_action( "load-$hook", 'add_my_options' );
//}
 
//function add_my_options() {
  //global $myListTable;
  $option = 'per_page';
  $args = array(
         'label' => 'comments',
         'default' => 5,
         'option' => 'comments_per_page'
         );
  add_screen_option( $option, $args );
  $myListTable = new My_Example_List_Table();
//}
 
 

//function my_render_list_page(){
   $myListTable = new My_Example_List_Table();
  
$myListTable->prepare_items();

echo'  
  <form method="post">
    <input type="hidden" name="page" value="ttest_list_table">';


  $myListTable->display(); 
  echo '</form></div>'; 
//}








/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //}



//mysqli_close($mysqli);

}


/*
Plugin Name: Test List Table Example
*/

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class My_Example_List_Table extends WP_List_Table {


var  $example_data = array();
	

function __construct(){

global $current_user;
      get_currentuserinfo();




$mysqli = new mysqli("localhost","root", "", "wordpress");

if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }



   $suffix = $current_user->ID;	

   $table_name = "my_comments_" . $suffix;

$result = mysqli_query($mysqli,"SELECT * FROM $table_name WHERE user_id = $current_user->ID");

if(!$result){

$this->example_date = NULL;

}

if($result){

while($row = mysqli_fetch_array($result)){

	  
	  $author = $row['post_author_ID'];

	  $temp = mysqli_query($mysqli,"SELECT * FROM wp_users WHERE ID = $author");
  	$blog = mysqli_fetch_array($temp);	



  	$temp2 = mysqli_query($mysqli,"SELECT * FROM wp_". ($author+1) ."_posts WHERE ID = $row[comment_post_ID]");
  	$post = mysqli_fetch_array($temp2);

	
	$hyperlink = '<a href= '."$post[guid]".'/>'."$post[post_title]".'</a>';


  	$row_data = array( 'Blog Author' =>  "$blog[display_name]", 'Post Title' => "$hyperlink", 
                   'Comment' => "$row[comment_content]",'Date'=> "$row[comment_date]" );

    	$this->example_data[] = $row_data;

}


}


mysqli_close($mysqli);

    
    global $status, $page;

        parent::__construct( array(
            'singular'  => __( 'comment', 'mylisttable' ),     //singular name of the listed records
            'plural'    => __( 'comments', 'mylisttable' ),   //plural name of the listed records
            'ajax'      => false        //does this table support ajax?

    ) );

    add_action( 'admin_head', array( &$this, 'admin_header' ) );            

}

  function admin_header() {
    $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
    if( 'my_list_test' != $page )
    return;
    echo '<style type="text/css">';
    echo '.wp-list-table .column-id { width: 5%; }';
    echo '.wp-list-table .column-Blog Author { width: 40%; }';
    echo '.wp-list-table .column-Post Title { width: 35%; }';
    echo '.wp-list-table .column-Comment { width: 20%;}';
    echo '.wp-list-table .column-Date { width: 20%;}';
    echo '</style>';
  }

  function no_items() {
    _e( 'No Comments to display' );
  }

  function column_default( $item, $column_name ) {
    switch( $column_name ) { 
        case 'Blog Author':
        case 'Post Title':
	case 'Comment':
	case 'Date':	
            return $item[ $column_name ];
        default:
            return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }

function get_sortable_columns() {
  $sortable_columns = array(
    'Blog Author'  => array('Blog Author',false),
    'Post Title' => array('Post Title',false),
    'Comment'   => array('Comment',false),
    'Date' => array('Date',false)
  );
  return $sortable_columns;
}

function get_columns(){
        $columns = array(
            'Blog Author' => __( 'Blog Author', 'mylisttable' ),
            'Post Title'    => __( 'Post Title', 'mylisttable' ),
	    'Comment'      => __( 'Comment', 'mylisttable' ),
	    'Date'        =>  __( 'Date','mylisttable')
        );
         return $columns;
    }

function usort_reorder( $a, $b ) {
  // If no sort, default to title
  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'Date';
  // If no order, default to asc
  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
  // Determine sort order
  $result = strcmp( $a[$orderby], $b[$orderby] );
  // Send final sort direction to usort
  return ( $order === 'asc' ) ? $result : -$result;
}

 
function prepare_items() {
  $columns  = $this->get_columns();
  $hidden   = array();
  $sortable = $this->get_sortable_columns();
  $this->_column_headers = array( $columns, $hidden, $sortable );
  usort( $this->example_data, array( &$this, 'usort_reorder' ) );
  
  $per_page = 5;
  $current_page = $this->get_pagenum();
  $total_items = count( $this->example_data );

  // only ncessary because we have sample data
  $this->found_data = array_slice( $this->example_data,( ( $current_page-1 )* $per_page ), $per_page );

  $this->set_pagination_args( array(
    'total_items' => $total_items,                  //WE have to calculate the total number of items
    'per_page'    => $per_page                     //WE have to determine how many items to show on a page
  ) );
  $this->items = $this->found_data;
}

} //class




?>
