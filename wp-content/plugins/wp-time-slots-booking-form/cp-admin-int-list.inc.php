<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

   
$current_user_access = current_user_can('manage_options');
$current_user_can_edit = current_user_can('edit_posts');

global $wpdb, $cptslotsb_addons_active_list, $cptslotsb_addons_objs_list;

$message = "";


if( isset( $_GET[ 'b' ] ) && $_GET[ 'b' ] == 1 )
{
    $this->verify_nonce ($_GET["anonce"], 'cptslotsb_actions_list');
	// Save the option for active addons
	delete_option( 'cptslotsb_addons_active_list' );
	if( !empty( $_GET[ 'cptslotsb_addons_active_list' ] ) && is_array( $_GET[ 'cptslotsb_addons_active_list' ] ) ) 
	{
		update_option( 'cptslotsb_addons_active_list', $_GET[ 'cptslotsb_addons_active_list' ] );
	}	
	
	// Get the list of active addons
	$cptslotsb_addons_active_list = get_option( 'cptslotsb_addons_active_list', array() );
}


if (isset($_GET['a']) && $_GET['a'] == '1' && $current_user_access)
{
    $this->verify_nonce ($_GET["anonce"], 'cptslotsb_actions_list');
    define('CP_TSLOTSBOOK_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
    define('CP_TSLOTSBOOK_DEFAULT_fp_destination_emails', CP_TSLOTSBOOK_DEFAULT_fp_from_email);
    
    $wpdb->insert( $wpdb->prefix.$this->table_items, array( 
                                      'form_name' => stripcslashes($_GET["name"]),

                                      'form_structure' => $this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure),

                                      'fp_from_email' => $this->get_option('fp_from_email', CP_TSLOTSBOOK_DEFAULT_fp_from_email),
                                      'fp_destination_emails' => $this->get_option('fp_destination_emails', CP_TSLOTSBOOK_DEFAULT_fp_destination_emails),
                                      'fp_subject' => $this->get_option('fp_subject', CP_TSLOTSBOOK_DEFAULT_fp_subject),
                                      'fp_inc_additional_info' => $this->get_option('fp_inc_additional_info', CP_TSLOTSBOOK_DEFAULT_fp_inc_additional_info),
                                      'fp_return_page' => $this->get_option('fp_return_page', CP_TSLOTSBOOK_DEFAULT_fp_return_page),
                                      'fp_message' => $this->get_option('fp_message', CP_TSLOTSBOOK_DEFAULT_fp_message),
                                      'fp_emailformat' => $this->get_option('fp_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format),

                                      'cu_enable_copy_to_user' => $this->get_option('cu_enable_copy_to_user', CP_TSLOTSBOOK_DEFAULT_cu_enable_copy_to_user),
                                      'cu_user_email_field' => $this->get_option('cu_user_email_field', CP_TSLOTSBOOK_DEFAULT_cu_user_email_field),
                                      'cu_subject' => $this->get_option('cu_subject', CP_TSLOTSBOOK_DEFAULT_cu_subject),
                                      'cu_message' => $this->get_option('cu_message', CP_TSLOTSBOOK_DEFAULT_cu_message),
                                      'cu_emailformat' => $this->get_option('cu_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format),
                                      
                                      'vs_text_is_required' => $this->get_option('vs_text_is_required', CP_TSLOTSBOOK_DEFAULT_vs_text_is_required),
                                      'vs_text_maxapp' => $this->get_option('vs_text_maxapp', CP_TSLOTSBOOK_DEFAULT_vs_text_maxapp),
                                      'vs_text_is_email' => $this->get_option('vs_text_is_email', CP_TSLOTSBOOK_DEFAULT_vs_text_is_email),
                                      'vs_text_datemmddyyyy' => $this->get_option('vs_text_datemmddyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_datemmddyyyy),
                                      'vs_text_dateddmmyyyy' => $this->get_option('vs_text_dateddmmyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_dateddmmyyyy),
                                      'vs_text_number' => $this->get_option('vs_text_number', CP_TSLOTSBOOK_DEFAULT_vs_text_number),
                                      'vs_text_digits' => $this->get_option('vs_text_digits', CP_TSLOTSBOOK_DEFAULT_vs_text_digits),
                                      'vs_text_max' => $this->get_option('vs_text_max', CP_TSLOTSBOOK_DEFAULT_vs_text_max),
                                      'vs_text_min' => $this->get_option('vs_text_min', CP_TSLOTSBOOK_DEFAULT_vs_text_min),                                       

                                      'cv_enable_captcha' => $this->get_option('cv_enable_captcha', CP_TSLOTSBOOK_DEFAULT_cv_enable_captcha),
                                      'cv_width' => $this->get_option('cv_width', CP_TSLOTSBOOK_DEFAULT_cv_width),
                                      'cv_height' => $this->get_option('cv_height', CP_TSLOTSBOOK_DEFAULT_cv_height),
                                      'cv_chars' => $this->get_option('cv_chars', CP_TSLOTSBOOK_DEFAULT_cv_chars),
                                      'cv_font' => $this->get_option('cv_font', CP_TSLOTSBOOK_DEFAULT_cv_font),
                                      'cv_min_font_size' => $this->get_option('cv_min_font_size', CP_TSLOTSBOOK_DEFAULT_cv_min_font_size),
                                      'cv_max_font_size' => $this->get_option('cv_max_font_size', CP_TSLOTSBOOK_DEFAULT_cv_max_font_size),
                                      'cv_noise' => $this->get_option('cv_noise', CP_TSLOTSBOOK_DEFAULT_cv_noise),
                                      'cv_noise_length' => $this->get_option('cv_noise_length', CP_TSLOTSBOOK_DEFAULT_cv_noise_length),
                                      'cv_background' => $this->get_option('cv_background', CP_TSLOTSBOOK_DEFAULT_cv_background),
                                      'cv_border' => $this->get_option('cv_border', CP_TSLOTSBOOK_DEFAULT_cv_border),
                                      'cv_text_enter_valid_captcha' => $this->get_option('cv_text_enter_valid_captcha', CP_TSLOTSBOOK_DEFAULT_cv_text_enter_valid_captcha)
                                     )
                      );   
    
    $message = "<script>document.location='?page=cp_timeslotsbooking&cal=".$wpdb->insert_id."';</script>";
} 
else if (isset($_GET['u']) && $_GET['u'] != '' && $current_user_access)
{
    $this->verify_nonce ($_GET["anonce"], 'cptslotsb_actions_list');
    $wpdb->query( $wpdb->prepare('UPDATE `'.$wpdb->prefix.$this->table_items.'` SET form_name=%s WHERE id=%d', sanitize_text_field($_GET["name"]), $_GET['u']) );
    $message = __('Item updated','wp-time-slots-booking-form');        
} else if (isset($_GET['d']) && $_GET['d'] != '' && $current_user_access)
{
    $this->verify_nonce ($_GET["anonce"], 'cptslotsb_actions_list');
    $wpdb->query( $wpdb->prepare('DELETE FROM `'.$wpdb->prefix.$this->table_items.'` WHERE id=%d', $_GET['d']) );
    $message = __('Item deleted','wp-time-slots-booking-form');
} else if (isset($_GET['c']) && $_GET['c'] != '' && $current_user_access)
{
    $this->verify_nonce ($_GET["anonce"], 'cptslotsb_actions_list');
    $myrows = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table_items." WHERE id=%d", $_GET['c']) , ARRAY_A);    
    unset($myrows["id"]);
    $myrows["form_name"] = 'Cloned: '.$myrows["form_name"];
    $wpdb->insert( $wpdb->prefix.$this->table_items, $myrows);
    $message = __('Item duplicated/cloned','wp-time-slots-booking-form');
}


if ($message) echo "<div id='setting-error-settings_updated' class='updated'><h2>".$message."</h2></div>";

$nonce = wp_create_nonce( 'cptslotsb_actions_list' );


?>
<h1><?php echo $this->plugin_name; ?></h1>

<script type="text/javascript">
        
 function cp_addItem()
 {
    var calname = document.getElementById("cp_itemname").value;
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&anonce=<?php echo $nonce; ?>&a=1&r='+Math.random()+'&name='+encodeURIComponent(calname);       
 }
 
 function cp_updateItem(id)
 {
    var calname = document.getElementById("calname_"+id).value;    
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&anonce=<?php echo $nonce; ?>&u='+id+'&r='+Math.random()+'&name='+encodeURIComponent(calname);    
 }
 
 function cp_cloneItem(id)
 {
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&anonce=<?php echo $nonce; ?>&c='+id+'&r='+Math.random();  
 } 
 
 function cp_manageSettings(id)
 {
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&cal='+id+'&r='+Math.random();
 }
 
 function cp_publish(id)
 {
     document.location = 'admin.php?page=cp_timeslotsbooking&pwizard=1&cal='+id+'&r='+Math.random();
 }

 function cp_addbk(id)
 {
     document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&addbk=1&cal='+id+'&r='+Math.random();
 } 
 
 function cp_viewMessages(id)
 {
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&cal='+id+'&list=1&r='+Math.random();
 } 
 
 function cp_viewSchedule(id)
 {
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&cal='+id+'&schedule=1&calendarview=1&r='+Math.random();
 }
 
 function cp_viewReport(id)
 {
    document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&cal='+id+'&report=1&r='+Math.random();
 } 
 
 function cp_deleteItem(id)
 {
    if (confirm('Are you sure that you want to delete this item?'))
    {        
        document.location = 'admin.php?page=<?php echo $this->menu_parameter; ?>&anonce=<?php echo $nonce; ?>&d='+id+'&r='+Math.random();
    }
 }
 
</script>

<?php if ($current_user_access) { ?> 
 <div class="ahb-section-container">
	<div class="ahb-section">
		<label><?php _e('New Calendar','wp-time-slots-booking-form'); ?></label>&nbsp;&nbsp;&nbsp;
		<input type="text" name="cp_itemname" id="cp_itemname" placeholder=" - Calendar Name - " class="ahb-new-calendar" />
		<input type="button" class="button-primary" value="Add New" onclick="cp_addItem();" />
	</div>
</div>
<?php } ?>

<h2><?php _e('Calendars List','wp-time-slots-booking-form'); ?></h2>

<div class="ahb-section-container">
	<div class="ahb-section">
    
  <table cellspacing="10" cellpadding="6" class="ahb-calendars-list"> 
   <tr>
    <th align="left"><?php _e('ID','wp-time-slots-booking-form'); ?></th><th align="left"><?php _e('Form Name','wp-time-slots-booking-form'); ?></th><th align="left">&nbsp; &nbsp; <?php _e('Options','wp-time-slots-booking-form'); ?></th><?php if ($current_user_can_edit) { ?><th align="left"><?php _e('Shortcode for Pages and Posts','wp-time-slots-booking-form'); ?></th><?php } ?>
   </tr> 
<?php  

  $current_user = wp_get_current_user();
  $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items );                                                                     
  foreach ($myrows as $item)         
  if ($current_user_access || @in_array($current_user->ID, unserialize($item->cp_user_access)))     
  {
?>
   <tr> 
    <td nowrap><?php echo $item->id; ?></td>
    <td nowrap><?php if ($current_user_access) { ?><input type="text" name="calname_<?php echo $item->id; ?>" id="calname_<?php echo $item->id; ?>" value="<?php echo esc_attr($item->form_name); ?>" /><?php } else { ?><?php echo htmlentities($item->form_name); ?><?php } ?></td>          
    
    <td>
<?php if ($current_user_access) { ?>        
                             <input style="margin-bottom:3px;" class="button" type="button" name="calupdate_<?php echo $item->id; ?>" value="<?php _e('Rename','wp-time-slots-booking-form'); ?>" onclick="cp_updateItem(<?php echo $item->id; ?>);" />
<?php } ?> 
<?php if ($current_user_access || @$item->cp_user_access_settings == 'true') { ?>                              
                             <input style="margin-bottom:3px;" class="button-primary button" type="button" name="calmanage_<?php echo $item->id; ?>" value="<?php _e('Edit','wp-time-slots-booking-form'); ?>" onclick="cp_manageSettings(<?php echo $item->id; ?>);" />   
<?php } ?>
<?php if ($current_user_access) { ?>                               
                             <input style="margin-bottom:3px;" class="button-primary button" type="button" name="calpublish_<?php echo $item->id; ?>" value="<?php _e('Publish','wp-time-slots-booking-form'); ?>" onclick="cp_publish(<?php echo $item->id; ?>);" />                            
<?php } ?>                             
                             <input style="margin-bottom:3px;" class="button" type="button" name="calmessages_<?php echo $item->id; ?>" value="<?php _e('Booking Orders','wp-time-slots-booking-form'); ?>" onclick="cp_viewMessages(<?php echo $item->id; ?>);" />                              
                             <input style="margin-bottom:3px;" class="button" type="button" name="calschedule_<?php echo $item->id; ?>" value="<?php _e('Schedule','wp-time-slots-booking-form'); ?>" onclick="cp_viewSchedule(<?php echo $item->id; ?>);" /> 
                             <input style="margin-bottom:5px;" class="button" type="button" name="caladdbk_<?php echo $item->id; ?>" value="<?php _e('Add Booking','wp-time-slots-booking-form'); ?>" onclick="cp_addbk(<?php echo $item->id; ?>);" />                             
                             <input style="margin-bottom:3px;" class="button" type="button" name="calreport_<?php echo $item->id; ?>" value="<?php _e('Stats','wp-time-slots-booking-form'); ?>" onclick="cp_viewReport(<?php echo $item->id; ?>);" />                              
<?php if ($current_user_access) { ?>                             
                             <input style="margin-bottom:3px;" class="button" type="button" name="calclone_<?php echo $item->id; ?>" value="<?php _e('Clone','wp-time-slots-booking-form'); ?>" onclick="cp_cloneItem(<?php echo $item->id; ?>);" />                              
                             <input style="margin-bottom:3px;" class="button" type="button" name="caldelete_<?php echo $item->id; ?>" value="<?php _e('Delete','wp-time-slots-booking-form'); ?>" onclick="cp_deleteItem(<?php echo $item->id; ?>);" />                             
<?php } ?>
    </td>
    <?php if ($current_user_can_edit) {?><td><nobr>[<?php echo $this->shorttag; ?> id="<?php echo $item->id; ?>"]</nobr></td><?php } ?>
   </tr>
<?php  
   } 
?>   
     
  </table> 
     
     <div class="clearer"></div>
     
	</div>
</div>
  


 
<div id="normal-sortables" class="meta-box-sortables"> 

<?php if ($current_user_access) { ?> 


<?php
	if( count( $cptslotsb_addons_active_list ) )
	{	
		foreach( $cptslotsb_addons_active_list as $addon_id ) if( isset( $cptslotsb_addons_objs_list[ $addon_id ] ) ) print $cptslotsb_addons_objs_list[ $addon_id ]->get_addon_settings();
	}
?>  


 
<?php } ?>
  
</div> 



[<a href="https://wordpress.dwbooster.com/contact-us" target="_blank"><?php _e('Request Custom Modifications','wp-time-slots-booking-form'); ?></a>] | [<a href="<?php echo $this->plugin_URL; ?>" target="_blank"><?php _e('Help','wp-time-slots-booking-form'); ?></a>]
</form>
</div>