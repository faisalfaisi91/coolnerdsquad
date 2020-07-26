<?php
/*
*/
require_once dirname( __FILE__ ).'/base.addon.php';

if( !class_exists( 'cptslotsb_DashboardWidget' ) )
{
    class cptslotsb_DashboardWidget extends cptslotsb_BaseAddon
    {

        /************* ADDON SYSTEM - ATTRIBUTES AND METHODS *************/
		protected $addonID = "addon-DashboardWidget-20181221";
		protected $name = "Dashboard Widget: Upcoming appointments";
		protected $description;
        protected $max_image_width = 250;

		public function get_addon_form_settings( $form_id )
		{
			global $wpdb;
			// Insertion in database			
			
		} // end get_addon_form_settings



		/************************ ADDON CODE *****************************/

        /************************ ATTRIBUTES *****************************/


        /************************ CONSTRUCT *****************************/

        function __construct()
        {
			$this->description = __("The add-on adds a widget to display a list of upcoming appointments in the dashboard welcome page.", 'wp-time-slots-booking-form' );
            // Check if the plugin is active
			if( !$this->addon_is_active() ) return;
                      
            add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widgets') );
        } // End __construct


        public function add_dashboard_widgets() {
        	wp_add_dashboard_widget('dashboard_widget', 'WP Time Slots Booking Form: Upcoming appointments', array(&$this, 'pp_DashboardWidget'));
        }        

        /************************ PRIVATE METHODS *****************************/

	

		/************************ PUBLIC METHODS  *****************************/
        
        
		/**
         * print list
         */
		public function pp_DashboardWidget($atts = array())
		{
            global $wpdb, $cp_tslotsb_plugin;           
            extract( shortcode_atts( array(
	        	'calendar' => '',
	        	'showdelete' => '0',
                'columnlabels' => 'Time,Data',
	        	'columns' => 'TIME,data',
                'datefrom' => 'today',
                'dateto' => 'today +1 month',
                'paidonly' => "",
                'maxitems' => "10",
                'datelabel' => __('Date', 'wp-time-slots-booking-form'),
                'status' => "-1"
	        ), $atts ) ); 
            
            $buffer = '<div style="font-weight:bold;margin-bottom:3px;">'.__('Next','wp-time-slots-booking-form')." ".$maxitems." ".__('upcoming appointments:','wp-time-slots-booking-form')."</div>";
            wp_enqueue_script( "jquery" );
            
            $cond = '1=1';            
                
            if ($calendar)
                $cond .= " AND formid=".intval($calendar);     
                
            // calculate dates
            $from = date("Y-m-d",strtotime($datefrom));
            $to = date("Y-m-d",strtotime($dateto));
        
            $events_query = "SELECT * FROM ".$wpdb->prefix.$cp_tslotsb_plugin->table_messages.
                           " WHERE ".$cond." ORDER BY time DESC";
            
            $events = $wpdb->get_results($events_query);

            // pre-select time-slots
            $selection = array();            
            foreach($events as $item)
            {        
                $data = unserialize($item->posted_data);
                if (!$paidonly || $data['paid'])
                {
                    foreach($data["apps"] as $app)                                       
                        if ($app["date"] >= $from && $app["date"] <= $to && ($status == '-1' || $status == $app["cancelled"]) 
						   && $app["cancelled"] != 'Cancelled' && $app["cancelled"] != 'Cancelled by customer')
                        {                      
                            $selection[] = array($app["date"]." ".$app["slot"], $app["date"], $app["slot"], $data, sanitize_email($item->notifyto), $item->data, $app["cancelled"], $app["service"]);
                        }    
                }
            }
        
            // order time-slots
            function listgroupd_addon_wptimeslotbkfastsortfn($a, $b) { return ($a[0] > $b[0]); }
            usort($selection, "listgroupd_addon_wptimeslotbkfastsortfn" );        
            
            // clean fields IDs
            $fields = explode(",",trim($columns));
            for($j=0; $j<count($fields); $j++)
                $fields[$j] = strtolower(trim($fields[$j]));            
     
            $columnlabels = explode(",", $columnlabels);
            $columns = explode(",", $columns);

            if (!count($events))
            {
                $buffer .= __('No upcoming appointments found','wp-time-slots-booking-form');
                echo $buffer;
                return;
            }
  
            $buffer .= '<style>.cptslotsbtable {  border-collapse: collapse;border-spacing: 0;width: 100%;} .cptslotsbtable .cptslotsbth { padding-left: 8px; text-align: left; border: 1px solid #999; background-color:#BDD7EE; } .cptslotsbtable .cptslotsbth th, .cptslotsbtable td {border: 1px solid #999;text-align: left;padding: 8px;word-break: normal; }.cptslotsbheadermth,.cptslotsbheadermtd{ padding-left: 8px; text-align: left; border: 1px solid #999 !important; background-color:#F8CBAD ;}</style>';            
            

            // list data rows
            $colnum = 0;
            $lastdate = '';
            for($i=0; $i<count($selection) && $i<$maxitems; $i++)
            {
                $colnum++;
                if ($lastdate != $selection[$i][1])
                {
                    if ($lastdate != '') $buffer .= '</table></div>';
                    $lastdate = $selection[$i][1];
                    $buffer .= '<div style="overflow-x:auto;"><table class="cptslotsbtable"><tr>';                    
                    // list header rows
                    $buffer .= '<th class="cptslotsbheadermth">'.$datelabel.'</th>';
                    $buffer .= '<td class="cptslotsbheadermtd" nowrap>'.$lastdate.'</td>';
                    $buffer .= '<tr>';
                    $colnum = 0;
                    foreach ($columnlabels as $item)
                    {
                        $colnum++;
                        $buffer .= '<th class="cptslotsbheader'.$colnum.' cptslotsbth">'.esc_html(trim($item)).'</th>';
                    }
                    if ($showdelete)
                        $buffer .= '<th class="cptslotsbheader'.($colnum++).' cptslotsbth"></th>';
                    $buffer .= '</tr>';                       
                }
                $buffer .= '<tr class="'.($selection[$i][6]!=''?' cptslotsb_cancelled':'').'">';            
                for($j=0; $j<count($fields); $j++)
                {    
                   switch ($fields[$j]) {
                        case 'date':
                            $value = esc_html($selection[$i][1]);
                            break;
                        case 'time':
                            $value = esc_html($selection[$i][2]);
                            break;
                        case 'email':
                            $value = sanitize_email($selection[$i][4])."&nbsp;";
                            break;  
                        case 'service':
                            $value = esc_html($selection[$i][7])."&nbsp;";
                            break;                         
                        case 'cancelled':
                            if ($selection[$i][6] == '') 
                                $value = __('Approved','wp-time-slots-booking-form');
                            else
                                $value = esc_html($selection[$i][6]);
                            $value = '&nbsp;';
                            break;                         
                        case 'data':
                            $value = esc_html(substr($selection[$i][5],strpos($selection[$i][5],"\n\n")+2));
                            break;    
                        case 'paid':
                            $value = ($selection[$i][3]['paid']?__('Yes','wp-time-slots-booking-form'):'&nbsp;');
                            break;
                        default:
                            $value = esc_html(($selection[$i][3][$fields[$j]]==''?'&nbsp;':$selection[$i][3][$fields[$j]]))."&nbsp;";
                    }         
                    $buffer .= '<td class="cptslotsbcol'.$colnum.'">'.$this->make_links_clickable(trim($value), is_array(@$params[$item]) ).'</td>';                
                }
                if ($showdelete)
                    $buffer .= '<td class="cptslotsbcol'.($colnum++).'">[<a href="javascript:cptslotsb_deleteitem('.$event->id.');">'.__('delete','wp-time-slots-booking-form').'</a>]</td>';
                    $buffer .= '</tr>';               
            }   

            $buffer .= '</table></div>';
            
            echo $buffer;            
  
		} // end pp_DashboardWidget


        private function make_links_clickable($text, $is_upload)
        {
            $link = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">*LLLK*</a>', $text);
			if ($is_upload)
				$link = str_replace('*LLLK*', basename($text), $link);
			else
				$link = str_replace('*LLLK*', $text, $link);
			return $link;
        }
       
		/**
		 * log
		 */
		private function _log($adarray = array())
		{
			$h = fopen( __DIR__.'/logs.txt', 'a' );
			$log = "";
			foreach( $_REQUEST as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			foreach( $adarray as $KEY => $VAL )
			{
				$log .= $KEY.": ".$VAL."\n";
			}
			$log .= "================================================\n";
			fwrite( $h, $log );
			fclose( $h );
		}


    } // End Class

    // Main add-on code
    $cptslotsb_DashboardWidget_obj = new cptslotsb_DashboardWidget();

	// Add addon object to the objects list
	global $cptslotsb_addons_objs_list;
	$cptslotsb_addons_objs_list[ $cptslotsb_DashboardWidget_obj->get_addon_id() ] = $cptslotsb_DashboardWidget_obj;
}


?>