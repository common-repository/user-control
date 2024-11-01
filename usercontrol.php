<?php

/*

Plugin Name: User Control

Plugin URI: http://www.iwebux.com

Description: Disable/Enable Users

Version: 2.1.0

Author: Surendhar Rajahram 

Author URI: http://www.iwebux.com

*/


/*  Copyright 2013  Iwebux  (url- http://iwebux.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

require_once('pagination.class.php');

global $wpdb;

global $default;

global $limit;

global $pageLimit;

global $p;

global $searchTerm;

 if(isset($_POST['disabledefault']))
 {
			$wpdb->query("UPDATE ".$wpdb->prefix."default_status SET status = 'disabled'");
			
 }
 else if(isset($_POST['enabledefault']))
 {
 			$wpdb->query("UPDATE ".$wpdb->prefix."default_status SET status = 'enabled'");
 }


 $default = $wpdb->get_row("SELECT status from ".$wpdb->prefix."default_status");


if(isset($_POST['disable']))
{
	foreach ( $_POST['users'] as $userid ) {
		$wpdb->query("UPDATE ".$wpdb->prefix."usercontrol SET disable_status = 'disabled' WHERE ID = ".$wpdb->escape($userid));
	}
}

if(isset($_POST['enable']))
{
	foreach ( $_POST['users'] as $userid ) {
		$wpdb->query("UPDATE ".$wpdb->prefix."usercontrol SET disable_status = 'enabled' WHERE ID = ".$wpdb->escape($userid));
	}
}

/**Check for the user status set by usercontrol plugin***/
function checkstatus() {
   global $current_user;
   global $wpdb;
   get_currentuserinfo();
   
  $row = $wpdb->get_row("SELECT disable_status FROM ".$wpdb->prefix."usercontrol WHERE ID = ".$current_user->ID);
 
  if ($row->disable_status == 'disabled') {
	wp_redirect(get_option('siteurl') . '/wp-login.php?disabled=true');
	wp_logout();
	$setMessage = 1;
  }
}

add_action('init', 'checkstatus');

/*** Display message if user is blocked ***/
function display_message() {
 
  if ($_GET['disabled']) {

		$message = '<div id="login_error">	<strong>ERROR</strong>: Admin disabled your account.<br>
</div>';
		return $message;
  }
}
add_filter('login_message', 'display_message');


/*** Set pagination class***/
function pager($items)
{
	global $limit;
	global $p;
	global $searchTerm;
	global $pageLimit;
	
	if($items > 0) {
		$p = new pagination;
		$p->items($items);
		$p->limit($pageLimit); // Limit entries per page
		$p->target("admin.php?page=User Control&usersearch=".$_REQUEST['usersearch']."&page-limit=".$_REQUEST['page-limit']);
		$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
		$p->calculate(); // Calculates what to show
		$p->parameterName('paging');
		$p->adjacents(1); //No. of page away from the current page

		if(!isset($_GET['paging'])) {
			$p->page = 1;
		} else {
			$p->page = $_GET['paging'];
		}

		//Query for limit paging
		$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;

	} else {
		echo "No Record Found";
	}

}



/*** Retrieve users and their status ***/
function set_users()
{
   global $wpdb;
   
   global $default;
   
   global $limit;
   
   global $pageLimit;
   
   global $p;
   
   global $searchTerm;
   
   if(isset($_REQUEST['usersearch']) && $_REQUEST['usersearch'])
   $searchTerm =  $_REQUEST['usersearch'];
   
   $usersearch = isset($_REQUEST['usersearch']) ? $_REQUEST['usersearch'] : null;
   
   $pageLimit = isset($_REQUEST['page-limit']) ? $_REQUEST['page-limit'] : 10;
   
   
   if ( isset($_REQUEST['usersearch']) && $_REQUEST['usersearch'] )
	{
		$sql = "SELECT * FROM ".$wpdb->prefix."usercontrol WHERE user_login LIKE '%".$usersearch."%' OR user_nicename LIKE '%".$usersearch."%' OR user_email LIKE '%".$usersearch."%' OR role LIKE '%".$usersearch."%' OR disable_status LIKE '%".$usersearch."%'";
		$result = $wpdb->get_results($sql);
		$items = $wpdb->num_rows;
		pager($items);
	}
	else
	{
		$sql = "SELECT * FROM ".$wpdb->prefix."usercontrol";
		$result = $wpdb->get_results($sql);
		$items = $wpdb->num_rows;
		pager($items);
	}
   
   

   echo '<div class="wrap">';
   echo "<div id=\"message\" class=\"updated fade\"><p><strong>New users ". $default->status ." by default.</strong></p></div>";

	?>
    
    <div style="float:left">
        <h2>
        <?php
          if ( isset($_REQUEST['usersearch']) && $_REQUEST['usersearch'] )
        printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html( $_REQUEST['usersearch'] ) ); 
        ?>
        </h2>
        <?php
         if ( isset($_REQUEST['usersearch']) && $_REQUEST['usersearch'] ) {?>
         <p><a href="admin.php?page=User Control"><?php _e("&larr; Back to All Users")?></a></p>
        <?php } ?>
    </div>    
    <div style="float:right;">
        <br />
        <form class="search-form" action="" method="get">
        <p class="search-box">
            <input type="hidden" value="User Control" name="page"  />
            <label for="page-limit">No of users to be displayed</label>
            <input type="text" id="page-limit" name="page-limit" size="2" value="<?php echo isset($_REQUEST['page-limit']) ? $_REQUEST['page-limit'] : 10?>" />
            <label class="screen-reader-text" for="user-search-input"><?php _e( 'Search Users' ); ?>:</label>
            <input type="text" id="user-search-input" name="usersearch" value="<?php echo esc_attr($wp_user_search->search_term); ?>" />
            <input type="submit" value="<?php esc_attr_e( 'Search Users' ); ?>" class="button" />
        </p>
        </form>
        <br /><br class="clear" />
   </div>
   <form action="" method="post" id="usercontrol">
   	<div class="tablenav">
        <div class='tablenav-pages'>
            <?php echo $p->show();  // Echo out the list of paging. ?>
        </div>
	</div>

   
    <table class="widefat fixed" cellspacing="0">
    <thead>
    <tr class="thead">
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-username" id="username" scope="col">Username</th>
	<th style="" class="manage-column column-status" id="status" scope="col">Status</th>
	<th style="" class="manage-column column-email" id="email" scope="col">E-mail</th>
	<th style="" class="manage-column column-role" id="role" scope="col">Role</th>
	
    </tr>
    </thead>
    
    <tfoot>
    <tr class="thead">
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
	<th style="" class="manage-column column-username" id="username" scope="col">Username</th>
	<th style="" class="manage-column column-status" id="status" scope="col">Status</th>
	<th style="" class="manage-column column-email" id="email" scope="col">E-mail</th>
	<th style="" class="manage-column column-role" id="role" scope="col">Role</th>
	
    </tr>
    </tfoot>

    
    <tbody id="users" class="list:user user-list">
	<?php
    $style = '';
	if ( isset($_REQUEST['usersearch']) && $_REQUEST['usersearch'] )
	{
		$sql = "SELECT * FROM ".$wpdb->prefix."usercontrol WHERE user_login LIKE '%".$usersearch."%' OR user_nicename LIKE '%".$usersearch."%' OR user_email LIKE '%".$usersearch."%' OR role LIKE '%".$usersearch."%' OR disable_status LIKE '%".$usersearch."%' ".$limit;
		$result = $wpdb->get_results($sql);
	}
	else
	{
		$sql = "SELECT * FROM ".$wpdb->prefix."usercontrol ".$limit;
		$result = $wpdb->get_results($sql);
	}
    foreach ( $result as $row) {
        $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
        echo "\n\t" . '<tr class="'.$style.'" id="user-'.$row->ID.'">
		<th class="check-column" scope="row"><input type="checkbox" value="'.$row->ID.'" class="editor" id="user_'.$row->ID.'" name="users[]"></th><td class="username column-username">'.$row->user_nicename.'</td><td class="name column-status">'.$row->disable_status.'</td><td class="email column-email"><a title="e-mail:'.$row->user_email.'" href="mailto:'.$row->user_email.'">'.$row->user_email.'</a></td><td class="role column-role">'.$row->role.'</td></tr>';
    }
    ?>
    </tbody>
    </table>
    
    <div class="tablenav">
        <div class='tablenav-pages'>
            <?php echo $p->show();  // Echo out the list of paging. ?>
        </div>
	</div>
    
    
    <div class="alignleft actions">
    	<br /><input type="submit" value="<?php esc_attr_e('Disable'); ?>" name="disable" id="disable" class="button-secondary action" />
        <input type="submit" value="<?php esc_attr_e('Enable'); ?>" name="enable" id="enable" class="button-secondary action" /><br /><br />
        
        <b>Note: The following controls the default status of the new users. You can later enable/disable your new users.</b> <br /><br /> 
        
        <input type="submit" value="<?php esc_attr_e('Disable new users by default '); ?>" name="disabledefault" id="disabledefault" class="button-secondary action" />
        
        <input type="submit" value="<?php esc_attr_e('Enable new users by default '); ?>" name="enabledefault" id="enabledefault" class="button-secondary action" />
    
    	 <br /><br class="clear" /> <br /><br class="clear" />    
    
    </div>
    
    </form>
   </div> <!--class wrap ends --> 
 <?php 

}



function user_control() {
	
	global $wpdb;
	global $default;
	
	$wpdb->query("Delete from  ".$wpdb->prefix."usercontrol where id NOT IN (select ID from $wpdb->users)");
	
	$result = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users WHERE ID != %d and ID NOT IN (select id from ".$wpdb->prefix."usercontrol)",1));
	
    foreach ( $result as $userid ) {
		$user = get_userdata($userid);
		$user_object = new WP_User($userid);
        $roles = $user_object->roles;
        $role = array_shift($roles);
		$wpdb->query("INSERT INTO ".$wpdb->prefix."usercontrol (ID, user_login, user_nicename, user_email, role, disable_status) VALUES (".$user->ID.",'".$user->user_login."','".$user->user_nicename."','".$user->user_email."','".$role."','".$default->status."');");
	}

	add_menu_page( 'User Enable/Disable','User Control',9, 'User Control', 'set_users' );
	

}

add_action('admin_menu', 'user_control');



function anonymous_user(){
	
	
	global $wpdb;
	global $default;
	
	$wpdb->query("Delete from  ".$wpdb->prefix."usercontrol where id NOT IN (select ID from $wpdb->users)");
	
	$result = $wpdb->get_col( $wpdb->prepare("SELECT $wpdb->users.ID FROM $wpdb->users WHERE ID != %d and ID NOT IN (select id from ".$wpdb->prefix."usercontrol)",1));
	
    foreach ( $result as $userid ) {
		$user = get_userdata($userid);
		$user_object = new WP_User($userid);
        $roles = $user_object->roles;
        $role = array_shift($roles);
		$wpdb->query("INSERT INTO ".$wpdb->prefix."usercontrol (ID, user_login, user_nicename, user_email, role, disable_status) VALUES (".$user->ID.",'".$user->user_login."','".$user->user_nicename."','".$user->user_email."','".$role."','".$default->status."');");
	}
	
	
}


add_action('user_register', 'anonymous_user');

add_action('wpmu_activate_signup', 'anonymous_user');


function addStatus()
{
	global $wpdb;
	$wpdb->query( "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."usercontrol (
		ID bigint(20) NOT NULL,
		user_login varchar(60) NOT NULL,
		user_nicename varchar(50) NOT NULL,
		user_email varchar(100) NOT NULL,
		role varchar(50) NOT NULL,
		disable_status varchar(10) NOT NULL DEFAULT 'enabled',
		PRIMARY KEY (ID));" );
	$wpdb->query( "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."default_status (
		ID bigint(20) NOT NULL,
		status varchar(10) NOT NULL,
		PRIMARY KEY (ID));" );
	$wpdb->query( "INSERT INTO ".$wpdb->prefix."default_status values(1,'enabled')");
}


add_action('activate_'.plugin_basename( __FILE__ ),'addStatus' );

?>