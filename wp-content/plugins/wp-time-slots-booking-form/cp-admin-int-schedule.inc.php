<?php


$this->item = intval($_GET["cal"]);

$current_user = wp_get_current_user();
$current_user_access = current_user_can('edit_pages');

if ( !is_admin() || (!$current_user_access && !@in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))))
{
    echo 'Direct access not allowed.';
    exit;
}

if ($this->item != 0)
    $myform = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.$this->table_items .' WHERE id=%d' ,$this->item) );

$default_from = date("Y-m-d",strtotime("today -10 days"));
$default_to = date("Y-m-d",strtotime("today +30 days"));

$rawfrom = (isset($_GET["dfrom"]) ? sanitize_text_field($_GET["dfrom"]) : '');
$rawto = (isset($_GET["dto"]) ? sanitize_text_field(@$_GET["dto"]) : '');

if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy')
{
    $rawfrom = str_replace('/','.',$rawfrom);
    $rawto = str_replace('/','.',$rawto);
}

$dfrom = ($rawfrom ? date("Y-m-d", strtotime($rawfrom)) : $default_from);
$dto = ($rawto ? date("Y-m-d", strtotime($rawto)) : $default_to);

?>


<h1><?php _e('Schedule','wp-time-slots-booking-form'); ?> - <?php if ($this->item != 0) echo $myform[0]->form_name; else echo 'All forms'; ?></h1>

<div class="ahb-buttons-container">

    <?php if (!isset($_GET["calendarview"])) { ?>
     <input type="button" value="<?php _e('Change to Schedule Calendar View','wp-time-slots-booking-form'); ?>" class="button button-primary" onclick="document.location='?page=<?php echo $this->menu_parameter; ?>&cal=<?php echo $this->item; ?>&schedule=1&calendarview=1';" />  
    <?php } else { ?>
     <input type="button" value="<?php _e('Change to Schedule List View','wp-time-slots-booking-form'); ?>" class="button button-primary" onclick="document.location='?page=<?php echo $this->menu_parameter; ?>&cal=<?php echo $this->item; ?>&schedule=1';" />
    <?php } ?>
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php _e('Return to the calendars list','wp-time-slots-booking-form'); ?></a>
	<div class="clear"></div>
</div>

<?php if (!isset($_GET["calendarview"])) { ?>

<div class="ahb-section-container">
	<div class="ahb-section">
      <form action="admin.php" method="get">
        <input type="hidden" name="page" value="<?php echo $this->menu_parameter; ?>" />
        <input type="hidden" name="cal" value="<?php echo $this->item; ?>" />
        <input type="hidden" name="schedule" value="1" />
		<nobr><label><?php _e('From','wp-time-slots-booking-form'); ?>:</label> <input autocomplete="off" type="text" id="dfrom" name="dfrom" value="<?php echo esc_attr(@$_GET["dfrom"]); ?>" >&nbsp;&nbsp;</nobr>
		<nobr><label><?php _e('To','wp-time-slots-booking-form'); ?>:</label> <input autocomplete="off" type="text" id="dto" name="dto" value="<?php echo esc_attr(@$_GET["dto"]); ?>" >&nbsp;&nbsp;</nobr>
        <nobr><label><?php _e('Search for','wp-time-slots-booking-form'); ?>:</label> <input type="text" id="searchfor" name="searchfor" value="<?php echo esc_attr(@$_GET["searchfor"]); ?>" >
        <nobr><?php _e('Paid Status','wp-time-slots-booking-form'); ?>: <select id="paid" name="paid">
         <option value=""><?php _e('All','wp-time-slots-booking-form'); ?></option>
         <option value="1" <?php if (@$_GET["paid"]) echo ' selected'; ?>><?php _e('Paid only','wp-time-slots-booking-form'); ?></option>
      </select></nobr>
      <nobr><?php _e('Paid Status','wp-time-slots-booking-form'); ?>: <?php $this->render_status_box('status', (!isset($_GET["status"])?'-1':$_GET["status"]), true); ?></nobr>
		<nobr><label><?php _e('Item','wp-time-slots-booking-form'); ?>:</label> <select id="cal" name="cal">
          <option value="0"><?php _e('[All Items]','wp-time-slots-booking-form'); ?></option>
   <?php
    $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.$this->table_items );                                                                     
    foreach ($myrows as $item)  
         echo '<option value="'.$item->id.'"'.(intval($item->id)==intval($this->item)?" selected":"").'>'.$item->form_name.'</option>'; 
   ?>
    </select></nobr>
		<nobr>
			<input type="submit" name="<?php echo $this->prefix; ?>_csv2" value="<?php _e('Export to CSV','wp-time-slots-booking-form'); ?>" class="button" style="float:right;margin-left:10px;">
			<input type="submit" name="ds" value="<?php _e('Filter','wp-time-slots-booking-form'); ?>" class="button-primary button" style="float:right;">
		</nobr>
        <div style="clear:both"></div>
      </form>
	</div>
</div>


<br />                            

<div id="dex_printable_contents">

 <div class="cpapp_no_wrap">
  <div class="cptslotsb_field_0 cptslotsb_field_header"><?php _e('Date','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_1 cptslotsb_field_header"><?php _e('Slot','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_2 cptslotsb_field_header"><?php _e('Qty','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_3 cptslotsb_field_header"><?php _e('Paid','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_4 cptslotsb_field_header"><?php _e('Email','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_5 cptslotsb_field_header"><?php _e('Data','wp-time-slots-booking-form'); ?></div>
  <div class="cptslotsb_field_6 cptslotsb_field_header"><?php _e('Status','wp-time-slots-booking-form'); ?></div>
  <div class="cpapp_break"></div>
 </div> 
 <div class="cpapp_break"></div>
<?php

echo $this->filter_list( array(
                               'calendar' => ($this->item != 0 ? $this->item : ''),
                               'fields' => 'DATE,TIME,quantity,paid,email,data,cancelled',
	    	                   'from' => $dfrom,
	    	                   'to' => $dto,
                               'searchfor' => (isset($_GET["searchfor"])?$_GET["searchfor"]:''),
                               'paidonly' => @$_GET["paid"],
                               'status' => (!isset($_GET["status"])?'-1':$_GET["status"])
                               ) );

?>
</div>

<div class="ahb-buttons-container">
    <input type="button" value="<?php _e('Print','wp-time-slots-booking-form'); ?>" class="button button-primary" onclick="do_dexapp_print();" />
	<a href="<?php print esc_attr(admin_url('admin.php?page='.$this->menu_parameter));?>" class="ahb-return-link">&larr;<?php _e('Return to the calendars list','wp-time-slots-booking-form'); ?></a>
	<div class="clear"></div>
</div>

<?php } else { ?>

<div id="cpabc_printable_contents">

<p>The purpose of this page is to <strong>display the bookings/schedule in a calendar view</strong>. You can add bookings from the public booking form or <a href="?page=<?php echo $this->menu_parameter; ?>&cal=<?php echo $this->item; ?>&addbk=1">add bookings from the dashboard</a> and the bookings will appear in this calendar.<br />For CSV export, print and filter options switch to the "<strong>List View</strong>" with the button above this text. </p>
<div class="clearer"></div>

            <link rel="stylesheet" href="<?php echo plugins_url('mv/css/cupertino/calendar.css', __FILE__); ?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo plugins_url('mv/css/main.css', __FILE__); ?>" type="text/css" /> 
            
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/underscore.js', __FILE__); ?>"></script>
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/rrule.js', __FILE__); ?>"></script>
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/Common.js', __FILE__); ?>"></script>
            
<?php
function cpwptimeslotsbk_mv_autodetect_language()
{
        $basename = '/mv/language/multiview_lang_';
        
        $binfo = str_replace('-','_',get_bloginfo('language'));
        
        $options = array ($binfo,
                          strtolower($binfo),
                          substr(strtolower($binfo),0,2)."_".substr(strtoupper($binfo),strlen(strtoupper($binfo))-2,2),
                          substr(strtolower($binfo),0,2),
                          substr(strtolower($binfo),strlen(strtolower($binfo))-2,2)                      
                          );
        foreach ($options as $option)
        {
            if (file_exists(dirname( __FILE__ ).$basename.$option.'.js'))
                return $option;
            $option = str_replace ("-","_", $option);    
            if (file_exists(dirname( __FILE__ ).$basename.$option.'.js'))
                return $option;
        }  
        return '';    
}

        if (file_exists(dirname( __FILE__ ).'/mv/language/multiview_lang_'.cpwptimeslotsbk_mv_autodetect_language().'.js'))
            $langscript = plugins_url('/mv/language/multiview_lang_'.cpwptimeslotsbk_mv_autodetect_language().'.js', __FILE__);
        else
            $langscript = plugins_url('/mv/language/multiview_lang_en_GB.js', __FILE__);
?>        
            <script type="text/javascript" src="<?php echo $langscript; ?>"></script>
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/jquery.calendar.js', __FILE__); ?>"></script>
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/jquery.alert.js', __FILE__); ?>"></script>
            <script type="text/javascript" src="<?php echo plugins_url('mv/js/multiview.js', __FILE__); ?>"></script>
          
            <script type="text/javascript">
             var pathCalendar = "<?php echo $this->get_site_url( true ); ?>/?cp_slots_action=mv&formid=<?php echo $this->item; ?>";
             var dc_subjects = "";var dc_locations = "";
             initMultiViewCal("cal<?php echo $this->item; ?>", <?php echo $this->item; ?>,
          {viewDay:true,
          viewWeek:true,
          viewMonth:true,
          viewNMonth:true,
          viewList:false,
          viewdefault:"week",
          numberOfMonths:12,
          showtooltip:false,
          tooltipon:0,
          shownavigate:false,
          url:"",
          target:0,
          start_weekday:0,
          language:"en-GB",
          cssStyle:"cupertino",
          edition:true,
          btoday:true,
          dialogWidth: 330,
          bnavigation:true,
          brefresh:true,
          bnew:false,
          path:pathCalendar,
          userAdd:false,
          userEdit:false,
          userDel:false,
          userEditOwner:true,
          userDelOwner:false,
          showtooltipdwm_mouseover:true,
          userOwner:0 ,cellheight:62 , palette:0, paletteDefault:"F00", paletteFull:["FFF","FCC","FC9","FF9","FFC","9F9","9FF","CFF","CCF","FCF","CCC","F66","F96","FF6","FF3","6F9","3FF","6FF","99F","F9F","BBB","F00","F90","FC6","FF0","3F3","6CC","3CF","66C","C6C","999","C00","F60","FC3","FC0","3C0","0CC","36F","63F","C3C","666","900","C60","C93","990","090","399","33F","60C","939","333","600","930","963","660","060","366","009","339","636","000","300","630","633","330","030","033","006","309","303"]});
            </script>
          
            <div id="multicalendar"><div id="cal<?php echo $this->item; ?>" class="multicalendar"></div></div>
            
             <div style="clear:both;height:20px" ></div>    

</div>

<?php } ?>




<script type="text/javascript">
 function do_dexapp_print()
 {
      w=window.open();
      w.document.write("<style>.cptslotsb_field_header {font-weight: bold;background-color: #dcdcdc;}.cpapp_break { clear: both; }.cptslotsb_field_0, .cptslotsb_field_1,.cptslotsb_field_2, .cptslotsb_field_3,.cptslotsb_field_4, .cptslotsb_field_5,.cptslotsb_field_6, .cptslotsb_field_7,.cptslotsb_field_7, .cptslotsb_field_9,.cptslotsb_field_10, .cptslotsb_field_11{float: left; min-width: 85px;padding-right:11px;border-bottom: 1px dotted #777777;margin-left: 1px;     padding: 5px;margin: 2px;}.cptslotsb_field_0 {color: #44aa44;font-weight: bold; }.cptslotsb_field_1, .cptslotsb_field_2 {color: #aaaa44;font-weight: bold;min-width: 35px !important;width:35px !important; }.cptslotsb_field_4{width:200px;}.cptslotsb_field_5{width:200px;display:none}.cptslotsb_field_6{}.cpnopr{display:none;};table{border:2px solid black;width:100%;}th{border-bottom:2px solid black;text-align:left}td{padding-left:10px;border-bottom:1px solid black;}</style>"+document.getElementById('dex_printable_contents').innerHTML);
      w.print();
      w.close();     
 }
 
 var $j = jQuery.noConflict();
 $j(function() {
 	$j("#dfrom").datepicker({     	                
                    dateFormat: 'yy-mm-dd'
                 });
 	$j("#dto").datepicker({     	                
                    dateFormat: 'yy-mm-dd'
                 });
 });
 
</script>














