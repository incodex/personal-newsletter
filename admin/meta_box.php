<?php

function personal_campagins_schedule_meta_box(){
global $personal;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'personal_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
     $send_mode = $data->send_mode;
   }
?>
   <input type="radio" name="mode" id="mode-manual"  value="manual"  <?php if(isset($send_mode) && $send_mode == 'manual')  echo 'checked'; ?> />
   <label for="mode-manual" ><?php _e( 'Manual'  ); ?></label><br>
   <input type="radio" name="mode" id="mode-daily"   value="daily"   <?php if(isset($send_mode) && $send_mode == 'daily')   echo 'checked'; ?> />
   <label for="mode-daily"  ><?php _e( 'Diario'  ); ?></label><br>
   <input type="radio" name="mode" id="mode-weekly"  value="weekly"  <?php if(isset($send_mode) && $send_mode == 'weekly')  echo 'checked'; ?> />
   <label for="mode-weekly" ><?php _e( 'Semanal' ); ?></label><br>
   <input type="radio" name="mode" id="mode-monthly" value="monthly" <?php if(isset($send_mode) && $send_mode == 'monthly') echo 'checked'; ?> />
   <label for="mode-monthly"><?php _e( 'Mensual' ); ?></label><br>
<?php	
}

function personal_campagins_content_meta_box(){
   global $personal;
   if(!empty($_GET['id'])){
     global $wpdb;
     $newsletter_table = $wpdb->prefix.'personal_newsletter';
     $query = "SELECT * FROM $newsletter_table WHERE id = ".$_GET['id'];
     $data = $wpdb->get_row($query);                    
   }else{
	  $blogname = get_option('blogname'); 
	  $subject = 'Tema';
	  $template = '';
   }
?>
   <table class="form-table">
		<tr>
			<th class='campaign'>
            	<label for="subject"><?php _e( 'Asunto:', 'newsletter' ); ?></label> 
            </th>
            <td>
            	<input id="subject" name="subject" type="text" value="<?php if(isset($data)){echo $data->email_subject; }else{echo $subject;}?>" />
            </td>
		</tr>
		<tr>
		    <th class='campaign'>
            	<label for="template"><?php _e( 'Template:', 'newsletter' ); ?></label> 
             </th>
             <td>
		      <textarea rows="4" cols="65" name="template"><?php if(isset($data)){echo $data->email_template; }else{echo $template; }?></textarea>
		   </td> 
           <br />
           <span>Se puede usar PHP en el template. Existe la variable <small>$user</small> que es un objeto usuario, al cual se le enviará el newsletter</span>
		</tr>
	</table><!-- .form-table -->
<?php	
}
?>
