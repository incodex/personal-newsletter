<?php
function personal_newsletter_add_new_settings(){
  global $personal;
  add_meta_box( 
             'personal-newsletter-add-new-meta-box'
            ,__( 'Programación de envío', 'newsletter' )
            ,'personal_campagins_schedule_meta_box'
            ,$personal->addnew
            ,'schedule'
            ,'high'
        );      
  add_meta_box( 
             'personal-newsletter-content-meta-box'
            ,__( 'Seleccionar Template', 'newsletter' )
            ,'personal_campagins_content_meta_box'
            ,$personal->addnew
            ,'content'
            ,'high'
        );       
              	
}

function personal_newsletter_campaigns_page(){
    //Create an instance of our package class...
    $campaignsListTable = new Campaigns_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $campaignsListTable->prepare_items();
?>
 <div class="wrap">
        
        <?php if ( function_exists( 'screen_icon' ) ) screen_icon(); ?>
        <h2><?php _e( 'Campaigns List', 'newsletter' ); ?></h2>
        <?php if ( isset( $_GET['success'] ) && 'true' == esc_attr( $_GET['success'] ) ) personal_newsletter_success_message(); ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $campaignsListTable->display() ?>
        </form>
        
    </div>
    <?php    
     	
}

function personal_newsletter_add_new_page(){
   global $personal;
   $plugin_data = get_plugin_data( PERSONAL_DIR . 'personal_newsletter.php' ); 
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'personal_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);
     $content = $data->content;                    
   }else{
	 $content = '';   
   } 
?>
   
	<div class="wrap">
		
        <?php if ( function_exists( 'screen_icon' ) ) screen_icon(); ?>
        
		<h2><?php _e( 'Agregar nueva campaña', 'newsletter' ); ?></h2>
        <?php if ( isset( $_GET['updated'] ) && 'true' == esc_attr( $_GET['updated'] ) && !empty($_GET['id']) ) personal_newsletter_update_message(); ?>
        <?php //if ( is_null( $_GET['update'] ) && !empty($_GET['id']) ) personal_newsletter_create_message(); ?>
		     
        <form id="addnew" method="post">
		<input name="action" value="<?php if(!empty($_GET['id'])){echo "update";}else{echo "create";}?>" type="hidden">
		<div id="poststuff" class="metabox-holder has-right-sidebar">			               
				<div id="side-info-column" class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortables">
				   	 <?php do_meta_boxes( $personal->addnew, 'schedule', $plugin_data ); ?>
				   	 <?php do_meta_boxes( $personal->addnew, 'category', $plugin_data ); ?>
				   	 <?php do_meta_boxes( $personal->addnew, 'number', $plugin_data ); ?>  
				    </div>
				</div>	
				<div id="post-body">
				   <div id="post-body-content">
				       <div id="titlediv">
				         <div id="titlewrap">
				             <input id="title" tabindex="1" size="30" name="name" type="text" value="<?php if(!empty($_GET['id'])){echo $data->name;}?>"/>
				         </div><!-- #titlewrap -->
				       </div><!-- #titlediv -->
				      <?php do_meta_boxes( $personal->addnew, 'content', $plugin_data ); ?>	   
				       
				   </div><!-- #post-body-content -->
				</div><!-- post-body -->
									
		</div><!-- #poststuff -->
		<br class="clear">
        <input class="button button-primary" type="submit" value="<?php _e('Save'); ?>" name="campaign" />
        </form>
	</div><!-- .wrap -->  
<?php		
}
