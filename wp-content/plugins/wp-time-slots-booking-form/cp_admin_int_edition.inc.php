<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

global $wpdb;

$cpid = 'CP_AHB';
$plugslug = $this->menu_parameter;

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST[$cpid.'_post_edition'] ) )
    echo "<div id='setting-error-settings_updated' class='updated settings-error'> <p><strong>Settings saved.</strong></p></div>";

if ($_GET["item"] == 'js')
    $saved_contents = base64_decode(get_option($cpid.'_JS', ''));
else if ($_GET["item"] == 'css')
    $saved_contents = base64_decode(get_option($cpid.'_CSS', ''));

?>
<div class="wrap">
<h1><?php _e('Customization / Edit Page','wp-time-slots-booking-form'); ?></h1>  



<input type="button" name="backbtn" value="<?php _e('Back to items list','wp-time-slots-booking-form'); ?>..." onclick="document.location='admin.php?page=<?php echo $plugslug; ?>';">
<br /><br />

<form method="post" action="" name="cpformconf"> 
<input name="<?php echo $cpid; ?>_post_edition" type="hidden" value="1" />
<input name="cfwpp_edit" type="hidden" value="<?php echo esc_attr($_GET["item"]); ?>" />
   
<div id="normal-sortables" class="meta-box-sortables">

<textarea name="editionarea" id="editionarea" style="width:100%" rows="20"><?php echo $saved_contents; ?></textarea> 
  
</div> 


<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Changes','wp-time-slots-booking-form'); ?>"  /></p>


</form>
</div>













