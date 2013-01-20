<?php
/**
 * Plugin Name: Personal Newsletter
 * Plugin URI: http://incodex.com/wp/newsletter
 * Description: Customizable autosended newsletters for each user, using PHP directly on the newsletter (thought to use user_meta).
 * Version: 0.0.1
 * Author: Julian Perelli, based on work from Darell Sun
 * Author URI:  http://jperelli.com.ar/
 * License: GPL2
 */

/*  Copyright 2013  jperelli  (email : jperelli@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('PERSONAL_OPTIONS', 'personal_opts');
define('PERSONAL_VERSION', '0.0.1');

function personal_newsletter_run_campaigns($data, $manual=false){
   global $wpdb;
   $subject = $data->email_subject;
   $template = $data->email_template;
   
   // Reset Post Data
   wp_reset_postdata();
   
   $users = get_users(array('fields' => 'all_with_meta'));

   //$fp = fopen('/tmp/wp_mail.html', 'w');
   foreach($users as $user) {
      if ( 
            ( get_the_author_meta( 'personal_newsletter_suscr', $user->ID ) == 'S' )
        and ( $manual
         or   get_the_author_meta( 'personal_newsletter_frec', $user->ID ) == $data->send_mode )
        ) {
        $email = $user->user_email;
        ob_start();
        eval( "?>$template<?" );
        $message = ob_get_contents();
        ob_end_clean();

        ob_start();
        eval( "?>$subject<?" );
        $subject = ob_get_contents();
        ob_end_clean();
      
        //$message = stripslashes($message);
        $result = wp_mail($email, $subject, $message);
        //fwrite($fp, $message);
      }
   }
   //fclose($fp);
   
}

/* Set up the plugin. */
add_action('plugins_loaded', 'personal_newsletter_setup');  
//add cron intervals
add_filter('cron_schedules', 'personal_newsletter_intervals');
//Actions for Cron job
add_action('personal_newsletter_cron', 'personal_newsletter_cron_hook');
/* Create table when admin active this plugin*/
register_activation_hook(__FILE__,'personal_newsletter_activation');
register_deactivation_hook(__FILE__, 'personal_newsletter_deactivation');

function personal_newsletter_activation()
{
	$personal_opts = get_option(PERSONAL_OPTIONS);
	if(!empty($personal_opts)){
	   $personal_opts['version'] = PERSONAL_VERSION;
	   update_option(PERSONAL_OPTIONS, $personal_opts); 	
	}else{
	   $opts = array(
		'version' => PERSONAL_VERSION,
		'import' => 'off'				
	  );
	  // add the configuration options
	  add_option(PERSONAL_OPTIONS, $opts);   	
	}	
	
	
	//test if cron active
	//if (!(wp_next_scheduled('personal_newsletter_cron')))
	   wp_schedule_event(time(), 'personal_intervals', 'personal_newsletter_cron');
	
	personal_create_table();
}

function personal_newsletter_deactivation(){
    wp_clear_scheduled_hook('personal_newsletter_cron');	
}

function personal_newsletter_cron_hook(){
    global $wpdb;
    $newsletter_table = $wpdb->prefix.'personal_newsletter';
	$query = "SELECT * FROM $newsletter_table";
    $entrys = $wpdb->get_results($query);
    
    foreach($entrys as $entry){
	   $next_run = $entry->next_run;
	   $current_time = time();
	   if (!empty($next_run)&&($next_run<=$current_time)) {
		personal_newsletter_run_campaigns($entry);
		switch ($entry->send_mode){
		   	case 'daily':
		   	   $next_day = time() + (1 * 24 * 60 * 60);
		   	   $settings['next_run'] = $next_day;
		   	   $settings['last_run'] = time();
		   	   $where = array('id' => $entry->id); 
               $wpdb->update($wpdb->prefix.'personal_newsletter', $settings, $where);
		   	case 'weekly':
		   	   $next_week = time() + (7 * 24 * 60 * 60);
		   	   $settings['next_run'] = $next_week;
		   	   $settings['last_run'] = time();
		   	   $where = array('id' => $entry->id); 
               $wpdb->update($wpdb->prefix.'personal_newsletter', $settings, $where);
		   	case 'monthly':
		   	   $next_month = time() + (30 * 24 * 60 * 60);
		   	   $settings['next_run'] = $next_month;
		   	   $settings['last_run'] = time();
		   	   $where = array('id' => $entry->id); 
               $wpdb->update($wpdb->prefix.'personal_newsletter', $settings, $where);
		 }
	   }	
	}	
}

function personal_newsletter_intervals($schedules){
   $intervals['personal_intervals']=array('interval' => '300', 'display' => 'personal_newsletter');
   $schedules=array_merge($intervals,$schedules);
   return $schedules;	
}

/*
 *Create database table for this plugin
*/
function personal_create_table(){
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  global $wpdb;
  $newsletter_table = $wpdb->prefix . 'personal_newsletter';    
  
  if( $wpdb->get_var( "SHOW TABLES LIKE '$newsletter_table'" ) != $newsletter_table ){
         $sql = "CREATE TABLE " . $newsletter_table . " (
         id       bigint(20) auto_increment primary key,
         name varchar(100),
         email_subject varchar(100),
         email_template text,
         send_mode varchar(10),
         number int(11),
         last_run varchar(20),
         next_run varchar(20)       
         );";
         dbDelta($sql);  	  
         $h = fopen(dirname(__FILE__).'/log.txt', 'w'); fwrite($h, $sql); fclose($h);
  }
  
}

/* 
 * Set up the social server plugin and load files at appropriate time. 
*/
function personal_newsletter_setup(){
   /* Set constant path for the plugin directory */
   define('PERSONAL_DIR', plugin_dir_path(__FILE__));
   define('PERSONAL_ADMIN', PERSONAL_DIR.'/admin/');
   define('PERSONAL_INC', PERSONAL_DIR.'/include/');

   /* Set constant path for the plugin url */
   define('PERSONAL_URL', plugin_dir_url(__FILE__));
   define('PERSONAL_CSS', PERSONAL_URL.'css/');
   define('PERSONAL_JS', PERSONAL_URL.'js/');

   if(is_admin())
      require_once(PERSONAL_ADMIN.'admin.php');

   /*Print style */
   add_action('wp_print_styles', 'personal_newsletter_style');
 
   /* print script */
   add_action('wp_print_scripts', 'personal_newsletter_script');
   
   /* display as text/html format */
   add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

   /* Profile fields */
   add_action('personal_options_update',  'personal_newsletter_edit_user_profile_update');
   add_action('edit_user_profile_update', 'personal_newsletter_edit_user_profile_update');
   
   add_action( 'show_user_profile', 'personal_newsletter_edit_user_profile' );
   add_action( 'edit_user_profile', 'personal_newsletter_edit_user_profile' );

   //$cron = wp_get_schedules();
   //error_log( "CRON jobs: " . print_r( $cron, true ) );
}

function personal_newsletter_edit_user_profile($user) {
  ?>
    <h3><?php _e('Newsletter') ?></h3>

    <table>
        <tr>
            <td>
                <label><?php _e('Recibir newsletter') ?></label>
            </td>
            <td>
                <?php $personal_newsletter_suscr = esc_attr(get_user_meta( $user->ID, 'personal_newsletter_suscr', true ) ); ?>
                <?php _e('Si') ?><input type="radio" name="personal_newsletter_suscr" value="S"<?php echo $personal_newsletter_suscr == 'S'? ' checked': '' ?>>
                <?php _e('No') ?><input type="radio" name="personal_newsletter_suscr" value="N"<?php echo $personal_newsletter_suscr == 'N'? ' checked': '' ?>>
            </td>
        </tr>
        <tr>
            <td>
                <label><?php _e('Frecuencia') ?></label>
            </td>
            <td>
                <?php $personal_newsletter_frec = esc_attr(get_user_meta( $user->ID, 'personal_newsletter_frec', true ) ); ?>
                <span class="radio"><input type="radio" name="personal_newsletter_frec" value="daily"<?php   echo $personal_newsletter_frec == 'daily'  ? ' checked': '' ?>><?php _e('Diaria') ?></span>
                <span class="radio"><input type="radio" name="personal_newsletter_frec" value="weekly"<?php  echo $personal_newsletter_frec == 'weekly' ? ' checked': '' ?>><?php _e('Semanal') ?></span>
                <span class="radio"><input type="radio" name="personal_newsletter_frec" value="monthly"<?php echo $personal_newsletter_frec == 'monthly'? ' checked': '' ?>><?php _e('Mensual') ?></span>
            </td>
        </tr>

        <?php do_action('personal_newsletter_edit_user_profile', $user); ?>

    </table>
    <?php
}

function personal_newsletter_edit_user_profile_update($user_ID) {
    update_usermeta($user_ID, 'personal_newsletter_suscr',  ( isset($_POST['personal_newsletter_suscr'] ) ? $_POST['personal_newsletter_suscr']  : 'N' ) );
    update_usermeta($user_ID, 'personal_newsletter_frec',   ( isset($_POST['personal_newsletter_frec']  ) ? $_POST['personal_newsletter_frec']   : 'weekly' ) );
}

function personal_newsletter_style(){
  
}
function personal_newsletter_script(){
 
}
?>
