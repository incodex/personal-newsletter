<?php
/* Admin functions to set and save settings of the 
 * @package PERSONAL
*/
require_once('pages.php');
require_once('meta_box.php');
//require_once(PERSONAL_INC.'tools.php');
require_once(PERSONAL_INC.'list_table.php');
/* Initialize the theme admin functions */
add_action('init', 'personal_newsletter_admin_init');

function personal_newsletter_admin_init(){
			
    add_action('admin_menu', 'personal_newsletter_settings_init');
    add_action('admin_init', 'personal_newsletter_actions_handler');
    add_action('admin_init', 'personal_newsletter_admin_style');
    add_action('admin_init', 'personal_newsletter_admin_script');

}

function personal_newsletter_add_meta_box(){
    add_meta_box( 
			'newsletter',
			__( 'Send as newsletter', 'newsletter' ),
			'personal_post_inner_meta_box',
			'post',
			'side',
			'core'
		);	
}

function personal_newsletter_settings_init(){
   global $personal;
   add_menu_page('Newsletter', 'Newsletter', 'manage_options', 'personal-campaigns', 'personal_newsletter_campaigns_page' ); 
   $personal->campaings = add_submenu_page('personal-campaigns', 'Campaigns', 'Campañas', 'manage_options', 'personal-campaigns', 'personal_newsletter_campaigns_page' );
   $personal->addnew = add_submenu_page('personal-campaigns', 'Add Campaign', 'Añadir nueva', 'manage_options', 'personal-add-new', 'personal_newsletter_add_new_page');
   //$personal->import = add_submenu_page('personal-campaigns', 'Import/Export', 'Import/Export', 'manage_options', 'personal-import', 'personal_newsletter_import_page' );

   add_action( "load-{$personal->addnew}", 'personal_newsletter_add_new_settings');
   add_action( "load-{$personal->settings}", 'personal_newsletter_configuration_settings');
}

function personal_newsletter_admin_style(){
  $plugin_data = get_plugin_data( PERSONAL_DIR . 'personal_newsletter.php' );
	
	wp_enqueue_style( 'personal-newsletter-admin', PERSONAL_CSS . 'style.css', false, $plugin_data['Version'], 'screen' );	
    wp_enqueue_style( 'personal-newsletter-new', PERSONAL_CSS . 'newsletter.css', false, $plugin_data['Version'], 'screen' );       
}
function personal_newsletter_admin_script(){}
function personal_newsletter_actions_handler(){
   global $wpdb;
   
   if(isset($_GET['action']) && $_GET['action']=='campaign-delete'){
	  $newsletter_table = $wpdb->prefix.'personal_newsletter';
	  $query = "DELETE FROM $newsletter_table WHERE id=".$_GET['id'];
	  $wpdb->query($query);
	  $redirect = admin_url( 'admin.php?page=personal-campaigns' ); 
      wp_redirect($redirect);
   }
   
   if(isset($_GET['action']) && $_GET['action']=='campaign-run'){
	  $newsletter_table = $wpdb->prefix.'personal_newsletter';
	  $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
      $data = $wpdb->get_row($query);                    
      personal_newsletter_run_campaigns($data, true);
	  //update the last_run field of this campaign
	  $settings['last_run'] = time();
	  $where = array('id' => $_GET['id']); 
      $wpdb->update($wpdb->prefix.'personal_newsletter', $settings, $where);
	  $redirect = admin_url( 'admin.php?page=personal-campaigns&success=true' ); 
      wp_redirect($redirect);
   }
      
   if(isset($_POST['personal-settings'])){
	   $personal_opts = get_option(PERSONAL_OPTIONS);
	   $personal_opts['import'] = $_POST['import'];
	   update_option(PERSONAL_OPTIONS, $personal_opts);
	   $redirect = admin_url( 'admin.php?page=personal-settings&updated=true' ); 
       wp_redirect($redirect);    
   }   
      
   if(isset($_POST['campaign'])){
	  //put the campaign info to settings array
	  $settings = array();
	  $settings['name'] = $_POST['name'];
	  $settings['email_subject'] = $_POST['subject'];
	  $settings['email_template'] = stripslashes($_POST['template']);
	  $settings['send_mode'] = $_POST['mode'];
      
      
      //get the next run time according to send_mode 	  
	  switch ($_POST['mode']){
		 case 'manual':
		    $settings['next_run'] = ''; 
		 break;
		 case 'daily':		    
		    $next_day = time() + (1 * 24 * 60 * 60);
		    $settings['next_run'] = $next_day;
		 break;
		 case 'weekly':		    
		    $next_week = time() + (7 * 24 * 60 * 60);
		    $settings['next_run'] = $next_week;		    
		 break;
		 case 'monthly':		    
		    $next_month = time() + (30 * 24 * 60 * 60);
		    $settings['next_run'] = $next_month;
		 break;
	  }
	  
	  //process the settings info according to action value
	  switch ($_POST['action']) { 
		 case 'create':		       
	        $settings['last_run'] = '';
	        $wpdb->insert( $wpdb->prefix.'personal_newsletter', $settings);
            if($wpdb->insert_id != false){ 
               $redirect = admin_url( 'admin.php?page=personal-add-new&updated=true&id=' ).$wpdb->insert_id; 
               wp_redirect($redirect);
            }      
		 break;
		 case 'update':
		    $where = array('id' => $_GET['id']); 
            $wpdb->update($wpdb->prefix.'personal_newsletter', $settings, $where);
            $redirect = admin_url( 'admin.php?page=personal-add-new&updated=true&id=' ).$_GET['id'];
            wp_redirect($redirect);		 
		 break;  
	  }  
   }

}
function personal_newsletter_error_message(){
   echo '<div class="error">
		<p>API is wrong</p>
  </div>';  
}
function personal_newsletter_success_message(){
  echo '<div class="updated fade">
		<p>This campaign has done successfully.</p>
  </div>';  
}
function personal_newsletter_update_message(){
   echo '<div class="updated fade">
		<p>Settings Updated</p>
  </div>';  	
}
function personal_newsletter_create_message(){
   echo '<div class="updated fade">
		<p>Campaign Created</p>
  </div>';	
}
?>
