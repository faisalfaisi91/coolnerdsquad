<?php

class CP_TimeSlotsBookingPlugin extends CP_TSLOTSBOOK_BaseClass {

    private $menu_parameter = 'cp_timeslotsbooking';
    public $prefix = 'cp_tslotsbooking';
    private $plugin_name = 'WP Time Slots Booking Form';
    private $componentid = 160;
    private $plugin_URL = 'http://wptimeslot.dwbooster.com/';
    private $plugin_download_URL = 'https://wptimeslot.dwbooster.com/download';
    public $table_items = "cptslotsbk_forms";
    public $table_messages = "cptslotsbk_messages";
    public $print_counter = 1;
    private $include_user_data_csv = false;
    public $CP_CFPP_global_templates;

    protected $paid_statuses = array('Pending','Cancelled','Rejected');
    public $shorttag = 'CP_TIME_SLOTS_BOOKING';

    function _install() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $results = $wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix.$this->table_messages."'");
        if (!count($results))
        {
            $sql = "CREATE TABLE ".$wpdb->prefix.$this->table_messages." (
                id int(10) NOT NULL AUTO_INCREMENT,
                formid INT NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                ipaddr VARCHAR(250) DEFAULT '' NOT NULL,
                notifyto VARCHAR(250) DEFAULT '' NOT NULL,
                data mediumtext,
                posted_data mediumtext,
                whoadded VARCHAR(250) DEFAULT '' NOT NULL,
                UNIQUE KEY id (id)
            );";
            $wpdb->query($sql);
        }

        $results = $wpdb->get_results("SHOW TABLES LIKE '".$wpdb->prefix.$this->table_items."'");
        if (!count($results))
        {
            $sql = "CREATE TABLE ".$wpdb->prefix.$this->table_items." (
                 id mediumint(9) NOT NULL AUTO_INCREMENT,

                 form_name VARCHAR(250) DEFAULT '' NOT NULL,

                 form_structure mediumtext,

                 calendar_language VARCHAR(250) DEFAULT '' NOT NULL,
                 date_format VARCHAR(250) DEFAULT '' NOT NULL,
                 product_name VARCHAR(250) DEFAULT '' NOT NULL,
                 pay_later_label VARCHAR(250) DEFAULT '' NOT NULL,

                 defaultstatus VARCHAR(250) DEFAULT '' NOT NULL,
                 defaultpaidstatus VARCHAR(250) DEFAULT '' NOT NULL,

                 fp_from_email VARCHAR(250) DEFAULT '' NOT NULL,
                 fp_destination_emails text,
                 fp_subject VARCHAR(250) DEFAULT '' NOT NULL,
                 fp_inc_additional_info VARCHAR(10) DEFAULT '' NOT NULL,
                 fp_return_page VARCHAR(250) DEFAULT '' NOT NULL,
                 fp_message text,
                 fp_emailformat VARCHAR(10) DEFAULT '' NOT NULL,

                 fp_emailtomethod VARCHAR(10) DEFAULT '' NOT NULL,
                 fp_destination_emails_field VARCHAR(200) DEFAULT '' NOT NULL,
                 cu_enable_copy_to_user VARCHAR(10) DEFAULT '' NOT NULL,
                 cu_user_email_field VARCHAR(250) DEFAULT '' NOT NULL,
                 cu_subject VARCHAR(250) DEFAULT '' NOT NULL,
                 cu_message text,
                 cu_emailformat VARCHAR(10) DEFAULT '' NOT NULL,
                 fp_emailfrommethod VARCHAR(10) DEFAULT '' NOT NULL,

                 vs_text_maxapp VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_is_required VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_is_email VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_datemmddyyyy VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_dateddmmyyyy VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_number VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_digits VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_max VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_min VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_pageof VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_submitbtn VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_previousbtn VARCHAR(250) DEFAULT '' NOT NULL,
                 vs_text_nextbtn VARCHAR(250) DEFAULT '' NOT NULL,

                 cp_user_access text,
                 cp_user_access_settings text,
                 rep_enable VARCHAR(10) DEFAULT '' NOT NULL,
                 rep_days VARCHAR(10) DEFAULT '' NOT NULL,
                 rep_hour VARCHAR(10) DEFAULT '' NOT NULL,
                 rep_emails text,
                 rep_subject text,
                 rep_emailformat VARCHAR(10) DEFAULT '' NOT NULL,
                 rep_message text,

                 cv_enable_captcha VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_width VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_height VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_chars VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_font VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_min_font_size VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_max_font_size VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_noise VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_noise_length VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_background VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_border VARCHAR(20) DEFAULT '' NOT NULL,
                 cv_text_enter_valid_captcha VARCHAR(200) DEFAULT '' NOT NULL,

                 UNIQUE KEY id (id)
            );";
            $wpdb->query($sql);
        }

        // insert initial data
        $count = $wpdb->get_var(  "SELECT COUNT(id) FROM ".$wpdb->prefix.$this->table_items  );
        if (!$count)
        {
            define('CP_TSLOTSBOOK_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
            define('CP_TSLOTSBOOK_DEFAULT_fp_destination_emails', CP_TSLOTSBOOK_DEFAULT_fp_from_email);
            $wpdb->insert( $wpdb->prefix.$this->table_items, array( 'id' => 1,
                                      'form_name' => 'Form 1',

                                      'form_structure' => $this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure),

                                      'calendar_language' => $this->get_option('calendar_language', ''),
                                      'date_format' => $this->get_option('date_format', ''),
                                      'product_name' => $this->get_option('fp_from_email', 'Booking'),
                                      'pay_later_label' => $this->get_option('fp_from_email', 'Pay later'),

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
                                      'vs_text_pageof' => $this->get_option('vs_text_pageof', 'Page {0} of {0}'),
                                      'vs_text_submitbtn' => $this->get_option('vs_text_submitbtn', 'Submit'),
                                      'vs_text_previousbtn' => $this->get_option('vs_text_previousbtn', 'Previous'),
                                      'vs_text_nextbtn' => $this->get_option('vs_text_nextbtn', 'Next'),

                                      'rep_enable' => $this->get_option('rep_enable', 'no'),
                                      'rep_days' => $this->get_option('rep_days', '1'),
                                      'rep_hour' => $this->get_option('rep_hour', '0'),
                                      'rep_emails' => $this->get_option('rep_emails', ''),
                                      'rep_subject' => $this->get_option('rep_subject', 'Submissions report...'),
                                      'rep_emailformat' => $this->get_option('rep_emailformat', 'text'),
                                      'rep_message' => $this->get_option('rep_message', 'Attached you will find the data from the form submissions.'),

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
        }
    }


    public function get_status_list()
    {
        $statuses = array('Approved');
        foreach ($this->paid_statuses as $item)
            $statuses[] = $item;
        return $statuses;
    }


    function render_status_box($name, $selected, $displayall = false)
    {
        echo '<select name="'.$name.'" id="'.$name.'">';
        if ($displayall)
            echo '<option value="-1"'.($selected == '-1'?' selected':'').'>'.__('[All]','wp-time-slots-booking-form').'</option>';
        echo '<option value=""'.($selected == ''?' selected':'').'>'.__('Approved','wp-time-slots-booking-form').'</option>';
        foreach ($this->paid_statuses as $item)
            echo '<option value="'.$item.'"'.($selected == $item?' selected':'').'>'.__($item,'wp-time-slots-booking-form').'</option>';
        echo '</select>';
    }


    public function update_status($id, $status)
    {
        global $wpdb;
        $events = $wpdb->get_results( $wpdb->prepare('SELECT * FROM `'.$wpdb->prefix.$this->table_messages.'` WHERE id=%d', $id) );
        $posted_data = unserialize($events[0]->posted_data);
        $countapps = count($posted_data["apps"]);
        for($k=0; $k<$countapps; $k++)
             $posted_data["apps"][$k]["cancelled"] = $status;
        $posted_data = serialize($posted_data);
        $wpdb->update ( $wpdb->prefix.$this->table_messages, array( 'posted_data' => $posted_data ), array( 'id' => $id ));
        do_action( 'cptslotsb_update_status', $id, $status );
    }


    /* Filter for placing the item into the contents */
    public function filter_list($atts) {
        global $wpdb;
        extract( shortcode_atts( array(
	    	'calendar' => '',
	    	'fields' => 'DATE,TIME,email',
	    	'from' => "today",
	    	'to' => "today +30 days",
            'searchfor' => "",
            'paidonly' => "",
            'status' => "-1"
	    ), $atts ) );
     
        if (intval($calendar))
            $this->setId(intval($calendar));
        
        if (!is_admin())
        {
            wp_enqueue_style('cptslots-publicstyle', plugins_url('css/stylepublic.css', __FILE__));
            wp_enqueue_style('cptslots-custompublicstyle', $this->get_site_url( false ).'?cp_cptslotsb_resources=css');
        }

        ob_start();
 
        // calculate dates
        $from = date("Y-m-d",strtotime($from));
        $to = date("Y-m-d",strtotime($to));

        // pre-select time-slots
        $selection = array();
        $rows = $wpdb->get_results( $wpdb->prepare("SELECT notifyto,posted_data,data FROM ".$wpdb->prefix.$this->table_messages." WHERE ".($calendar?'formid='.intval($calendar).' AND ':'')
        .($searchfor?'posted_data like \'%'.esc_sql($searchfor).'%\' AND ':'')
        ."time<=%s ORDER BY time DESC LIMIT 0,1000", date("Y-m-d",strtotime($to." +1 day" ))) );
        foreach($rows as $item)
        {
            $data = unserialize($item->posted_data);
            if (!$paidonly || $data['paid'])
            {
                foreach($data["apps"] as $app)
                    if ($app["date"] >= $from && $app["date"] <= $to && ($status == '-1' || $status == $app["cancelled"]) )
                        $selection[] = array($app["date"]." ".$app["slot"], $app["date"], $app["slot"], $data, $item->notifyto, $item->data, $app["cancelled"], $app["quantity1"], $app["quantity2"], @$app["quantity3"], @$app["quantity4"], @$app["quantity5"]);
            }
        }

        // order time-slots
        if (!function_exists('appbkfastsortfn'))
        {        
            function appbkfastsortfn($a, $b) { return ($a[0] > $b[0]); }
        }
        usort($selection, "appbkfastsortfn" );

        // clean fields IDs
        $fields = explode(",",trim($fields));
        for($j=0; $j<count($fields); $j++)
            $fields[$j] = strtolower(trim($fields[$j]));

        // print table
        for($i=0; $i<count($selection); $i++)
        {
            echo '<div class="cpapp_no_wrap">';
            for($j=0; $j<count($fields); $j++)
            {
                echo '<div class="cptslotsb_field_'.$j.($selection[$i][6]!=''?' cptslotsb_cancelled':'').'">';
                switch ($fields[$j]) {
                    case 'date':
                        echo esc_html($this->format_date($selection[$i][1]));
                        break;
                    case 'time':
                        echo esc_html($selection[$i][2]);
                        break;
                    case 'email':
                        echo esc_html($selection[$i][4])."&nbsp;";
                        break;
                    case 'quantity':
                        echo $selection[$i][7]
                             .($selection[$i][8] || $selection[$i][9] || $selection[$i][10]?'/'.$selection[$i][8]:'')
                             .($selection[$i][9] || $selection[$i][10]?'/'.$selection[$i][9]:'')
                             .($selection[$i][10]?'/'.$selection[$i][10]:'');
                        break;                          
                    case 'quantity1':
                        echo esc_html($selection[$i][7])."&nbsp;";
                        break;
                    case 'quantity2':
                        echo esc_html($selection[$i][8])."&nbsp;";
                        break;
                    case 'quantity3':
                        echo $selection[$i][9]."&nbsp;";
                        break;
                    case 'quantity4':
                        echo $selection[$i][10]."&nbsp;";
                        break;        
                    case 'quantity5':
                        echo $selection[$i][11]."&nbsp;";
                        break;    
                    case 'cancelled':
                        echo esc_html($selection[$i][6]).'&nbsp;';
                        break;
                    case 'data':
                        echo esc_html(substr($selection[$i][5],strpos($selection[$i][5],"\n\n")+2));
                        break;
                    case 'paid':
                        echo esc_html(($selection[$i][3]['paid']?__('Yes','wp-time-slots-booking-form'):'&nbsp;'));
                        break;
                    default:
                        echo esc_html(($selection[$i][3][$fields[$j]]==''?'&nbsp;':$selection[$i][3][$fields[$j]]));
                }
                echo '</div>';
            }
            echo '</div>';
            echo '<div class="cpapp_break"></div>';
        }

        $buffered_contents = ob_get_contents();
        ob_end_clean();
        return $buffered_contents;
    }


    /* Filter for placing the item into the contents */
    public function filter_content($atts) {
        global $wpdb;
        extract( shortcode_atts( array(
    		                           'id' => '',
    	                        ), $atts ) );
        if ($id != '')
            $this->item = $id;

    	/**
    	 * Filters applied before generate the form,
    	 * is passed as parameter an array with the forms attributes, and return the list of attributes
    	 */
        $atts = apply_filters( 'cptslotsb_pre_form',  $atts );

        ob_start();
        $this->insert_public_item();
        $buffered_contents = ob_get_contents();
        ob_end_clean();

	    /**
	     * Filters applied after generate the form,
	     * is passed as parameter the HTML code of the form with the corresponding <LINK> and <SCRIPT> tags,
	     * and returns the HTML code to includes in the webpage
	     */
	    $buffered_contents = apply_filters( 'cptslotsb_the_form', $buffered_contents,  $this->item );

        return $buffered_contents;
    }


    function insert_public_item() {
        global $wpdb;

        $pageof_label = $this->get_option('vs_text_pageof', 'Page {0} of {0}');
        $pageof_label = ($pageof_label==''?'Page {0} of {0}':$pageof_label);
        $previous_label = $this->get_option('vs_text_previousbtn', 'Previous');
        $previous_label = ($previous_label==''?'Previous':$previous_label);
        $next_label = $this->get_option('vs_text_nextbtn', 'Next');
        $next_label = ($next_label==''?'Next':$next_label);

        $calendar_language = $this->get_option('calendar_language','');
        if ($calendar_language == '') $calendar_language = $this->autodetect_language();
        if (CP_TSLOTSBOOK_DEFER_SCRIPTS_LOADING)
        {
            wp_enqueue_style('cptslots-calendarstyle', plugins_url('css/cupertino/calendar.css', __FILE__));
            wp_enqueue_style('cptslots-publicstyle', plugins_url('css/stylepublic.css', __FILE__));
            wp_enqueue_style('cptslots-custompublicstyle', $this->get_site_url( false ).'?cp_cptslotsb_resources=css');

            if ( $calendar_language != '' && file_exists(dirname(  __FILE__  ) .'/js/languages/jquery.ui.datepicker-'.$calendar_language.'.js') )
                wp_enqueue_script($this->prefix.'_language_file', plugins_url('js/languages/jquery.ui.datepicker-'.$calendar_language.'.js', __FILE__), array("jquery","jquery-ui-core","jquery-ui-datepicker","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip"));

            wp_enqueue_script( $this->prefix.'_builder_script',
               $this->get_site_url( false ).'?cp_cptslotsb_resources=public&nc=1',array("jquery","jquery-ui-core","jquery-ui-datepicker","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip"), false, true );

            wp_localize_script($this->prefix.'_builder_script', $this->prefix.'_fbuilder_config'.('_'.$this->print_counter), array('obj' =>
            '{"pub":true,"identifier":"'.('_'.$this->print_counter).'","messages": {
            	                	"required": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_is_required', CP_TSLOTSBOOK_DEFAULT_vs_text_is_required),'wp-time-slots-booking-form')).'",
                                    "maxapp": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_maxapp', CP_TSLOTSBOOK_DEFAULT_vs_text_maxapp),'wp-time-slots-booking-form')).'",
                                    "language": "'.str_replace(array('"'),array('\\"'),$calendar_language).'",
                                    "date_format": "'.str_replace(array('"'),array('\\"'),$this->get_option('date_format', '')).'",
            	                	"email": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_is_email', CP_TSLOTSBOOK_DEFAULT_vs_text_is_email),'wp-time-slots-booking-form')).'",
            	                	"datemmddyyyy": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_datemmddyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_datemmddyyyy),'wp-time-slots-booking-form')).'",
            	                	"dateddmmyyyy": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_dateddmmyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_dateddmmyyyy),'wp-time-slots-booking-form')).'",
            	                	"number": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_number', CP_TSLOTSBOOK_DEFAULT_vs_text_number),'wp-time-slots-booking-form')).'",
            	                	"digits": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_digits', CP_TSLOTSBOOK_DEFAULT_vs_text_digits),'wp-time-slots-booking-form')).'",
            	                	"max": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_max', CP_TSLOTSBOOK_DEFAULT_vs_text_max),'wp-time-slots-booking-form')).'",
            	                	"min": "'.str_replace(array('"'),array('\\"'),__($this->get_option('vs_text_min', CP_TSLOTSBOOK_DEFAULT_vs_text_min),'wp-time-slots-booking-form')).'",
    	                    	    "previous": "'.str_replace(array('"'),array('\\"'),$previous_label).'",
    	                    	    "next": "'.str_replace(array('"'),array('\\"'),$next_label).'",
    	                    	    "pageof": "'.str_replace(array('"'),array('\\"'),$pageof_label).'"
            	                }}'
            ));
        }
        else
        {
            wp_enqueue_script( "jquery" );
            wp_enqueue_script( "jquery-ui-core" );
            wp_enqueue_script( "jquery-ui-datepicker" );
        }
        ?><!--noptimize-->
        <script type="text/javascript">
         var cp_tslotsbk_cancel_label = '<?php echo str_replace("'", "\'", __("cancel",'wp-time-slots-booking-form')); ?>';
         var cp_tslotsbk_cost_label = '<?php echo str_replace("'", "\'", __("Cost",'wp-time-slots-booking-form')); ?>';
         var cp_tslotsbk_nomore_label = '<?php echo str_replace("'", "\'", __("No more slots available.",'wp-time-slots-booking-form')); ?>';
         var cp_tslotsbk_overlapping_label = '<?php echo str_replace("'", "\'", __("Selected time isn't longer available. Please select a different time.",'wp-time-slots-booking-form')); ?>';
         var cp_tslotsbk_avoid_overlapping = 0;
         var cp_tslotsbk_overbooking_handler<?php echo $this->print_counter-1; ?> = false;
         function <?php echo $this->prefix; ?>_pform_doValidate<?php echo '_'.$this->print_counter; ?>(form)
         {
            $dexQuery = jQuery.noConflict();
            try { document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.cp_ref_page.value = document.location; } catch (e) {}
            $dexQuery = jQuery.noConflict();<?php if (!is_admin() && $this->get_option('cv_enable_captcha', CP_TSLOTSBOOK_DEFAULT_cv_enable_captcha) != 'false') { ?>
            if (!cp_tslotsbk_overbooking_handler<?php echo $this->print_counter-1; ?>) {
                if (document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.hdcaptcha_<?php echo $this->prefix; ?>_post.value == '') { setTimeout( "<?php echo $this->prefix; ?>_cerror<?php echo '_'.$this->print_counter; ?>()", 100); return false; }
                var result = $dexQuery.ajax({ type: "GET", url: "<?php echo $this->get_site_url(); ?>/?ps=<?php echo '_'.$this->print_counter; ?>&<?php echo $this->prefix; ?>_pform_process=2&<?php echo $this->prefix; ?>_id=<?php echo $this->item; ?>&inAdmin=1&ps=<?php echo '_'.$this->print_counter; ?>&hdcaptcha_<?php echo $this->prefix; ?>_post="+document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.hdcaptcha_<?php echo $this->prefix; ?>_post.value, async: false }).responseText;
            }
            if (!cp_tslotsbk_overbooking_handler<?php echo $this->print_counter-1; ?> && result.indexOf("captchafailed") != -1) {
                $dexQuery("#captchaimg<?php echo '_'.$this->print_counter; ?>").attr('src', $dexQuery("#captchaimg<?php echo '_'.$this->print_counter; ?>").attr('src')+'&'+Date());
                setTimeout( "<?php echo $this->prefix; ?>_cerror<?php echo '_'.$this->print_counter; ?>()", 100);
                return false;
            } else <?php } ?>
            {
                var cpefb_error = 0;
                $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find(".cpefb_error").each(function(index){
                    if ($dexQuery(this).css("display")!="none")
                        cpefb_error++;
                    });
                if (cpefb_error==0)
                {<?php if (!function_exists('is_product') || !is_product()) { /** disable for woocommerce */ ?>
                    if (!cp_tslotsbk_overbooking_handler<?php echo $this->print_counter-1; ?>)
                    {
                        apptslotsbblink(".pbSubmit:visible");
                        $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find(".avoid_overlapping_before").not(".ignore,.ignorepb").removeClass("avoid_overlapping_before").removeClass("valid").addClass("avoid_overlapping");
                        cp_tslotsbk_avoid_overlapping = 1;
                        try {
                            $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find(".avoid_overlapping").valid();
                        } catch (e) { cp_tslotsbk_avoid_overlapping = 0; }
                        function check_cp_tslotsbk_avoid_overlapping(){
		                    if (cp_tslotsbk_avoid_overlapping>0)
		                        setTimeout(check_cp_tslotsbk_avoid_overlapping,100);
		                    else
		                    {
                                var cpefb_error = 0;
                                $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find(".cpefb_error").each(function(index){
                                    if ($dexQuery(this).css("display")!="none")
                                        cpefb_error++;    
                                    });
                                if (cpefb_error==0)    
                                {
                                    cp_tslotsbk_overbooking_handler<?php echo $this->print_counter-1; ?> = true;
                                    if (<?php echo $this->prefix; ?>_pform_doValidate<?php echo '_'.$this->print_counter; ?>(form))
                                        document.getElementById("<?php echo $this->prefix.'_pform_'.($this->print_counter); ?>").submit();
                                }
		                    }  
		                }
		                check_cp_tslotsbk_avoid_overlapping();
                        return false;
                    }<?php } ?>
                    <?php
                    /**
				     * Action called before insert the data into database.
				     * To the function are passed two parameters: the array with submitted data, and the number of form in the page.
				     */
				    do_action( 'cptslotsb_script_after_validation', $this->print_counter, $this->item );

                    //if (false) {
                    ?>
                    $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find("select").children().each(function(){
	        	    	    $dexQuery(this).val($dexQuery(this).attr("vt"));
	                });
	                $dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find("input:checkbox,input:radio").each(function(){
	        	    	    $dexQuery(this).val($dexQuery(this).attr("vt"));
	                }); <?php
  	                 //  }
	                ?>
	        		$dexQuery("#<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>").find( '.ignore' ).closest( '.fields' ).remove();
	        	}
                if (cpefb_error) return false;
	        	document.getElementById("form_structure<?php echo '_'.$this->print_counter; ?>").value = '';
	        	document.getElementById("refpage<?php echo '_'.$this->print_counter; ?>").value = document.location;
                try { if (document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.<?php echo $this->prefix; ?>_pform_status.value != '0')
                return false; } catch (e) {}
                apptslotsbblink(".pbSubmit");
                try { document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.<?php echo $this->prefix; ?>_pform_status.value = '1';	 } catch (e) {}
                $dexQuery(document).trigger("beforeOnSubmitWPTS",{id:"<?php echo $this->prefix.'_pform_'.($this->print_counter); ?>"});
                return true;
            }
         }
         function apptslotsbblink(selector){
             try {
                 $dexQuery = jQuery.noConflict();
                 $dexQuery(selector).fadeOut(1000, function(){
                     $dexQuery(this).fadeIn(1000, function(){
                             try {
                                 if (document.<?php echo $this->prefix; ?>_pform<?php echo '_'.$this->print_counter; ?>.<?php echo $this->prefix; ?>_pform_status.value != '0')
                                     apptslotsbblink(this);
                             } catch (e) {}
                     });
                 });
             } catch (e) {}
         }
         function <?php echo $this->prefix; ?>_cerror<?php echo '_'.$this->print_counter; ?>(){$dexQuery = jQuery.noConflict();$dexQuery("#hdcaptcha_error<?php echo '_'.$this->print_counter; ?>").css('top',$dexQuery("#hdcaptcha_<?php echo $this->prefix; ?>_post<?php echo '_'.$this->print_counter; ?>").outerHeight());$dexQuery("#hdcaptcha_error<?php echo '_'.$this->print_counter; ?>").css("display","inline");}
        </script><!--/noptimize-->
        <?php

        $button_label = $this->get_option('vs_text_submitbtn', 'Submit');
        $button_label = ($button_label==''?'Submit':$button_label);


        // START:: code to load form settings
        $raw_form_str = str_replace("\r"," ",str_replace("\n"," ",$this->cleanJSON($this->translate_json($this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure)))));

        $form_data = json_decode( $raw_form_str );
        if( is_null( $form_data ) ){
        	$json = new JSON;
        	$form_data = $json->unserialize( $raw_form_str );
        }
                
        if (is_admin())
        {
            $max_capacity = $this->check_max_capacity($form_data);                   
            if ($max_capacity > 1)
                echo '<style>.ts_slot_availability {display: block !important; font-size:80%;text-decoration:none; color: #888888}</style>';
        }
        

        if( !is_null( $form_data ) )
        {
        	if( !empty( $form_data[ 0 ] ) )
        	{
        		foreach( $form_data[ 0 ] as $key => $object )
        		{
        			if( isset( $object->isDataSource ) && $object->isDataSource && function_exists( 'mcrypt_encrypt' ) )
        			{
        				$connection = new stdClass();
        				$connection->connection = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure,$id), serialize( $object->list->database->databaseData ), MCRYPT_MODE_ECB ) );
        				$connection->form = $id;

        				$object->list->database->databaseData = $connection;
        				$form_data[ 0 ][ $key ] = $object;
        				$raw_form_str = json_encode( $form_data );
        			}
        			else if ($object->ftype == 'fcheck' || $object->ftype == 'fradio' || $object->ftype == 'fdropdown')
        			{
        			    for($ki=0; $ki<count($object->choicesVal); $ki++)
        			        $object->choicesVal[$ki] = str_replace('@', CP_TSLOTSBOOK_REP_ARR, $object->choicesVal[$ki]);
        			    $form_data[ 0 ][ $key ] = $object;
        				$raw_form_str = json_encode( $form_data );
        			}
        		}
        	}

        	if( isset( $form_data[ 1 ] ) && isset( $form_data[ 1 ][ 0 ] ) && isset( $form_data[ 1 ][ 0 ]->formtemplate ) )
        	{
        		$templatelist = $this->available_templates();
        		if( isset( $templatelist[ $form_data[ 1 ][ 0 ]->formtemplate ] ) )
        		print '<link href="'.esc_attr( esc_url( $templatelist[ $form_data[ 1 ][ 0 ]->formtemplate ][ 'file' ] ) ).'" type="text/css" rel="stylesheet" />';
        	}
        }

        $raw_form_str = str_replace('"','&quot;',esc_attr($raw_form_str));
        // END:: code to load form settings

        if (!defined('CP_AUTH_INCLUDE')) define('CP_AUTH_INCLUDE',true);
        @include dirname( __FILE__ ) . '/cp-public-int.inc.php';
        if (!CP_TSLOTSBOOK_DEFER_SCRIPTS_LOADING)
        {
            $prefix_ui = '';
            if (@file_exists(dirname( __FILE__ ).'/../../../wp-includes/js/jquery/ui/jquery.ui.core.min.js'))
                $prefix_ui = 'jquery.ui.';
            // This code won't be used in most cases. This code is for preventing problems in wrong WP themes and conflicts with third party plugins.
            ?>
                 <?php $plugin_url = plugins_url('', __FILE__); ?>
                 <!--noptimize-->
                 <link href="<?php echo plugins_url('css/stylepublic.css', __FILE__); ?>" type="text/css" rel="stylesheet" />
                 <link href="<?php echo $this->get_site_url( false ).'?cp_cptslotsb_resources=css'; ?>" type="text/css" rel="stylesheet" />
                 <link href="<?php echo plugins_url('css/cupertino/calendar.css', __FILE__); ?>" type="text/css" rel="stylesheet" />
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/jquery.js'; ?>'></script>
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'core.min.js'; ?>'></script>
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'datepicker.min.js'; ?>'></script>
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'widget.min.js'; ?>'></script>
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'position.min.js'; ?>'></script>
                 <script type='text/javascript' src='<?php echo $plugin_url.'/../../../wp-includes/js/jquery/ui/'.$prefix_ui.'tooltip.min.js'; ?>'></script>
                 <?php if ($calendar_language != '') { ?><script type='text/javascript' src='<?php echo plugins_url('js/languages/jquery.ui.datepicker-'.$calendar_language.'.js', __FILE__); ?>'></script><?php } ?>
                 <script type='text/javascript'>
                 /* <![CDATA[ */
                 var <?php echo $this->prefix; ?>_fbuilder_config<?php echo '_'.$this->print_counter; ?> = {"obj":"{\"pub\":true,\"identifier\":\"<?php echo '_'.$this->print_counter; ?>\",\"messages\": {\n    \t                \t\"required\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_is_required', CP_TSLOTSBOOK_DEFAULT_vs_text_is_required));?>\",\"maxapp\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_maxapp', CP_TSLOTSBOOK_DEFAULT_vs_text_maxapp));?>\",\"language\": \"<?php echo str_replace(array('"'),array('\\"'),$calendar_language);?>\",\"date_format\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('date_format', ''));?>\",\n    \t                \t\"email\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_is_email', CP_TSLOTSBOOK_DEFAULT_vs_text_is_email));?>\",\n    \t                \t\"datemmddyyyy\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_datemmddyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_datemmddyyyy));?>\",\n    \t                \t\"dateddmmyyyy\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_dateddmmyyyy', CP_TSLOTSBOOK_DEFAULT_vs_text_dateddmmyyyy));?>\",\n    \t                \t\"number\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_number', CP_TSLOTSBOOK_DEFAULT_vs_text_number));?>\",\n    \t                \t\"digits\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_digits', CP_TSLOTSBOOK_DEFAULT_vs_text_digits));?>\",\n    \t                \t\"max\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_max', CP_TSLOTSBOOK_DEFAULT_vs_text_max));?>\",\n    \t                \t\"min\": \"<?php echo str_replace(array('"'),array('\\"'),$this->get_option('vs_text_min', CP_TSLOTSBOOK_DEFAULT_vs_text_min));?>\",\"previous\": \"<?php echo str_replace(array('"'),array('\\"'),$previous_label); ?>\",\"next\": \"<?php echo str_replace(array('"'),array('\\"'),$next_label); ?>\"\n    \t                }}"};
                 /* ]]> */
                 </script>
                 <script type='text/javascript' src='<?php echo $this->get_site_url( false ).'?cp_cptslotsb_resources=public&nc=1'; ?>'></script><!--/noptimize-->
            <?php
        }
        $this->print_counter++;
    }


    /* Code for the admin area */

    public function plugin_page_links($links) {
        $customAdjustments_link = '<a href="'.$this->plugin_URL.'contact-us">'.__('Request custom changes').'</a>';
    	array_unshift($links, $customAdjustments_link);
        $settings_link = '<a href="admin.php?page='.$this->menu_parameter.'">'.__('Settings').'</a>';
    	array_unshift($links, $settings_link);
    	$help_link = '<a href="'.$this->plugin_URL.'">'.__('Help').'</a>';
    	array_unshift($links, $help_link);
    	return $links;
    }


    public function admin_menu() {
        add_options_page($this->plugin_name.' Options', $this->plugin_name, 'manage_options', $this->menu_parameter, array($this, 'settings_page') );
        add_menu_page( $this->plugin_name.' Options', $this->plugin_name, 'read', $this->menu_parameter, array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'General Settings', 'General Settings', 'edit_pages', $this->menu_parameter."_settings", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Add Ons', 'Add Ons', 'edit_pages', $this->menu_parameter."_addons", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Online Demo', 'Online Demo', 'edit_pages', $this->menu_parameter."_odemo", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'I Need Help', 'I Need Help', 'edit_pages', $this->menu_parameter."_support", array($this, 'settings_page') );
        add_submenu_page( $this->menu_parameter, 'Upgrade Plugin', 'Upgrade Plugin', 'edit_pages', $this->menu_parameter."_upgrade", array($this, 'settings_page') );
    }


    function insert_button() {
        global $wpdb;
        $options = '';
        $calendars = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.$this->table_items);
        foreach($calendars as $item)
            $options .= '<option value="'.$item->id.'">'.$item->form_name.'</option>';

        if (!defined('ELEMENTOR_MENUS_VERSION') || @$_GET["action"] != 'elementor')
            wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script( 'cptimeslotsbk_classic_editor', plugins_url('/js/insertpanel.js', __FILE__));

        $forms = array();
        $rows = $wpdb->get_results("SELECT id,form_name FROM ".$wpdb->prefix.$this->table_items." ORDER BY form_name");
        foreach ($rows as $item)
           $forms[] = array (
                            'value' => $item->id,
                            'label' => $item->form_name,
                            );

        wp_localize_script( 'cptimeslotsbk_classic_editor', 'cptimeslotsbk_formsclassic', array(
                            'forms' => $forms,
                            'siteUrl' => get_site_url()
                          ) );

        print '<a href="javascript:cptslotsbk_appointments_fpanel.open()" title="'.__('Insert WP Time Slots Booking Form','wp-time-slots-booking-form').'"><img hspace="5" src="'.plugins_url('/images/cp_form.gif', __FILE__).'" alt="'.__('Insert WP Time Slots Booking Form','wp-time-slots-booking-form').'" /></a>';

    }


    public function settings_page() {
        global $wpdb;
        if ($this->get_param("cal") || $this->get_param("cal") == '0' || $this->get_param("pwizard") == '1')
        {
            $this->item = $this->get_param("cal");
            if (isset($_GET["edit"]) && $_GET["edit"] == '1')
                @include_once dirname( __FILE__ ) . '/cp_admin_int_edition.inc.php';
            else if ($this->get_param("schedule") == '1')
                @include_once dirname( __FILE__ ) . '/cp-admin-int-schedule.inc.php';
            else if ($this->get_param("list") == '1')
                @include_once dirname( __FILE__ ) . '/cp-admin-int-message-list.inc.php';
            else if ($this->get_param("report") == '1')
                @include_once dirname( __FILE__ ) . '/cp-admin-int-report.inc.php';
            else if ($this->get_param("addbk") == '1')
                @include_once dirname( __FILE__ ) . '/cp-admin-int-add-booking.inc.php';
            else if ($this->get_param("pwizard") == '1')
            {
                if ($this->get_param("cal"))
                    $this->item = $this->get_param("cal");
                @include_once dirname( __FILE__ ) . '/cp-publish-wizzard.inc.php';
            }
            else
                @include_once dirname( __FILE__ ) . '/cp-admin-int.inc.php';
        }
        else if ($this->get_param("page") == $this->menu_parameter.'_upgrade')
        {
            echo("Redirecting to upgrade page...<script type='text/javascript'>document.location='".$this->plugin_download_URL."';</script>");
            exit;
        }
        else if ($this->get_param("page") == $this->menu_parameter.'_odemo')
        {
            echo("Redirecting to demo page...<script type='text/javascript'>document.location='https://wptimeslot.dwbooster.com/home#demos';</script>");
            exit;
        }
        else if ($this->get_param("page") == $this->menu_parameter.'_support')
        {
            echo("Redirecting to upgrade page...<script type='text/javascript'>document.location='https://wptimeslot.dwbooster.com/contact-us';</script>");
            exit;
        }
        else if ($this->get_param("page") == $this->menu_parameter.'_settings')
        {
            @include_once dirname( __FILE__ ) . '/cp-settings.inc.php';
        }
        else if ($this->get_param("page") == $this->menu_parameter.'_addons')
        {
            @include_once dirname( __FILE__ ) . '/cp-addons.inc.php';
        }
        else
            @include_once dirname( __FILE__ ) . '/cp-admin-int-list.inc.php';
    }


    function gutenberg_block() {
        global $wpdb;

        wp_enqueue_script( 'cptimeslotsbk_gutenberg_editor', plugins_url('/js/block.js', __FILE__));

        wp_enqueue_style('cptslots-calendarstyle', plugins_url('css/cupertino/calendar.css', __FILE__));
        wp_enqueue_style('cptslots-publicstyle', plugins_url('css/stylepublic.css', __FILE__));
        wp_enqueue_style('cptslots-custompublicstyle', $this->get_site_url( false ).'?cp_cptslotsb_resources=css');

        wp_enqueue_script( $this->prefix.'_builder_script',
               $this->get_site_url( false ).'?cp_cptslotsb_resources=public',array("jquery","jquery-ui-core","jquery-ui-datepicker","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip"), false, true );

        $forms = array();
        $rows = $wpdb->get_results("SELECT id,form_name FROM ".$wpdb->prefix.$this->table_items." ORDER BY form_name");
        foreach ($rows as $item)
           $forms[] = array (
                            'value' => $item->id,
                            'label' => $item->form_name,
                            );

        wp_localize_script( 'cptimeslotsbk_gutenberg_editor', 'cptimeslots_forms', array(
                            'forms' => $forms,
                            'siteUrl' => get_site_url()
                          ) );
    }


    public function render_form_admin ($atts) {
        $is_gutemberg_editor = defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'];
        if (!$is_gutemberg_editor)
            return $this->filter_content (array('id' => $atts["formId"]));
        else if ($atts["formId"])
        {
            $this->setId($atts["formId"]);
            return '<input type="hidden" name="form_structure'.$atts["instanceId"].'" id="form_structure'.$atts["instanceId"].'" value="'.esc_attr($this->get_option('form_structure')).'" /><fieldset class="ahbgutenberg_editor" disabled><div id="fbuilder"><div id="fbuilder_'.$atts["instanceId"].'"><div id="formheader_'.$atts["instanceId"].'"></div><div id="fieldlist_'.$atts["instanceId"].'"></div></div></div></fieldset>';
        }
        else
            return '';
    }


    function insert_adminScripts($hook) {
        if ($this->get_param("page") == $this->menu_parameter && $this->get_param("addbk") != '1')
        {
            wp_deregister_script( 'bootstrap-datepicker-js' );
            wp_register_script('bootstrap-datepicker-js', plugins_url('/js/nope.js', __FILE__));
            wp_deregister_script( 'wpsp_wp_admin_jquery7' );
            wp_register_script('wpsp_wp_admin_jquery7', plugins_url('/js/nope.js', __FILE__));
            wp_enqueue_script( $this->prefix.'_builder_script', $this->get_site_url( true ).'/?cp_cptslotsb_resources=admin&nc=1',array("jquery","jquery-ui-core","jquery-ui-sortable","jquery-ui-tabs","jquery-ui-droppable","jquery-ui-button","jquery-ui-datepicker") );

            if (isset($_GET["calendarview"]) && $_GET["calendarview"] == '1')
                wp_enqueue_script( 'jquery-ui-dialog' );

            wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            wp_enqueue_style('cptslots-style', plugins_url('/css/style.css', __FILE__));
            wp_enqueue_style('cptslots-newadminstyle', plugins_url('/css/newadminlayout.css', __FILE__));
            $calendar_language = $this->get_option('calendar_language','');
            if ($calendar_language == '') $calendar_language = $this->autodetect_language();
            if ($calendar_language != '')
                wp_enqueue_script($this->prefix.'_language_file', plugins_url('js/languages/jquery.ui.datepicker-'.$calendar_language.'.js', __FILE__), array("jquery","jquery-ui-core","jquery-ui-datepicker","jquery-ui-widget","jquery-ui-position","jquery-ui-tooltip"));
        }
        if( 'post.php' != $hook  && 'post-new.php' != $hook )
            return;
        // space to include some script in the post or page areas if needed
    }


    function autodetect_language() {
        $basename = '/js/languages/jquery.ui.datepicker-';

        $options = array (get_bloginfo('language'),
                          strtolower(get_bloginfo('language')),
                          substr(strtolower(get_bloginfo('language')),0,2)."-".substr(strtoupper(get_bloginfo('language')),strlen(strtoupper(get_bloginfo('language')))-2,2),
                          substr(strtolower(get_bloginfo('language')),0,2),
                          substr(strtolower(get_bloginfo('language')),strlen(strtolower(get_bloginfo('language')))-2,2)
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

    /* hook for checking posted data for the admin area */

   function data_management_loaded() {
        global $wpdb;

        $action = $this->get_param('cp_timeslotsbooking_do_action_loaded');
    	if (!$action) return; // go out if the call isn't for this one

        if ($this->get_param('cptimeslotsbk_id')) $this->item = $this->get_param('cptimeslotsbk_id');

        if ($action == "wizard" && current_user_can('manage_options') )
        {
            $this->verify_nonce ($_POST["anonce"], 'cptslotsb_actions_wizard');
            $shortcode = '['.$this->shorttag.'  id="'.$this->item .'"]';
            $this->postURL = $this->publish_on($_POST["whereto"], $_POST["publishpage"], $_POST["publishpost"], $shortcode, $_POST["posttitle"]);
            return;
        }

        // ...
        echo 'Some unexpected error happened. If you see this error contact the support service at https://wptimeslot.dwbooster.com/contact-us';

        exit();
    }


    private function publish_on($whereto, $publishpage = '', $publishpost = '', $content = '', $posttitle = 'Booking Form')
    {
        global $wpdb;
        $id = '';
        if ($whereto == '0' || $whereto =='1') // new page
        {
            $my_post = array(
              'post_title'    => $posttitle,
              'post_type' => ($whereto == '0'?'page':'post'),
              'post_content'  => 'This is a <b>preview</b> page, remember to publish it if needed. You can edit the full calendar and form settings into the admin settings page.<br /><br /> '.$content,
              'post_status'   => 'draft'
            );

            // Insert the post into the database
            $id = wp_insert_post( $my_post );
        }
        else
        {
            $id = ($whereto == '2'?$publishpage:$publishpost);
            $post = get_post( $id );
            if (strpos($post->post_content,$content) === false)
            {
                $my_post = array(
                      'ID'           => $id,
                      'post_content' => $content.$post->post_content,
                  );
                // Update the post into the database
                wp_update_post( $my_post );
            }
        }
        return get_permalink($id);
    }


    function print_multiview_format($data)
    {
        // $data[$k]["d"] - date
        // $data[$k]["h1"] - hour
        // $data[$k]["m1"] - minute
        // $data[$k]["info"] - description
        // echo '<pre>';print_r($data);

        function _js2PhpTime($jsdate){
          if(preg_match('@(\d+)/(\d+)/(\d+)\s+(\d+):(\d+)((am|pm)*)@', $jsdate, $matches)==1){
            if ($matches[6]=="pm")
                if ($matches[4]<12)
                    $matches[4] += 12;
            $ret = mktime($matches[4], $matches[5], 0, $matches[1], $matches[2], $matches[3]);
          }else if(preg_match('@(\d+)/(\d+)/(\d+)@', $jsdate, $matches)==1){
            $ret = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
          }
          return $ret;
        }


        function _php2MySqlTime($phpDate){
            return date("Y-m-d H:i:s", $phpDate);
        }


        function _php2JsTime($phpDate){
            return @date("m/d/Y H:i", $phpDate);
        }


        function _mySql2PhpTime($sqlDate){
            $a1 = explode (" ",$sqlDate);
            $a2 = explode ("-",$a1[0]);
            $a3 = explode (":",$a1[1]);
            $t = mktime($a3[0],$a3[1],$a3[2],$a2[1],$a2[2],$a2[0]);
            return $t;
        }

        usort($data, array($this, 'wptsbk_custom_sort') );

        $ret = array();
        $ret['events'] = array();
        $ret["issort"] = true;
        $ret['error'] = null;
        $d1 = _js2PhpTime($_POST["startdate"]);
        $d2 = _js2PhpTime($_POST["enddate"]);
        $d1 = mktime(0, 0, 0,  date("m", $d1), date("d", $d1), date("Y", $d1));
        $d2 = mktime(0, 0, 0, date("m", $d2), date("d", $d2), date("Y", $d2))+24*60*60-1;
        $ret["start"] = _php2JsTime($d1);
        $ret["end"] = _php2JsTime($d2);

        $TIME_SLOT_SIZE = " +30 minutes";

        foreach ($data as $item)
        {
            $datetime = $item["d"]." ".$item["h1"].":".($item["m1"]<10?"0":"").$item["m1"];
            $ev = array(
                $row["id"],
                $item["e"],
                _php2JsTime(_mySql2PhpTime($datetime)),
                _php2JsTime(_mySql2PhpTime( date("Y-m-d H:i",strtotime($datetime.$TIME_SLOT_SIZE)))),
                0, // is  all day event?
                0, // more than one day event
                '',//Recurring event rule,
                '#3CF',
                0,//editable
                '',
                '',//$attends
                $item["info"],
                '',
                1
            );
            $ret['events'][] = $ev;
        }
        echo json_encode($ret);
        exit;
    }


    public function wptsbk_custom_sort($a,$b) {
          return ($a['d']>$b['d']) ||
                 ($a['d']==$b['d'] && $a['h1']>$b['h1']) ||
                 ($a['d']==$b['d'] && $a['h1']==$b['h1'] && $a['m1']>$b['m1']);
    }


    function check_current_user_access($calid = '')
    {        
        $current_user = wp_get_current_user();
        $current_user_access = current_user_can('manage_options');
        $saved_id = $this->item;
        $this->setId($calid);        
        $result = ($current_user_access || (intval($current_user->ID) && @in_array($current_user->ID, unserialize($this->get_option("cp_user_access","")))));
        $this->setId($saved_id);
        return $result;
    }
    

    function data_management() {
        global $wpdb;

        load_plugin_textdomain( 'wp-time-slots-booking-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if(!empty($_REQUEST['cp_slots_action']))
        {
            $formid = $_REQUEST['formid'];
            $field = @$_REQUEST['formfield'];

            $myrows = $wpdb->get_results( $wpdb->prepare("SELECT posted_data,notifyto,data FROM ".$wpdb->prefix.$this->table_messages." where formid=%d", $formid) );
            $tmp2 = array();
            for ($i=0; $i < count($myrows); $i++)
            {
                $data = unserialize($myrows[$i]->posted_data);
                for($k=0; $k<count($data["apps"]); $k++)
                    if ( (!isset($data["apps"][$k]["cancelled"]) || $data["apps"][$k]["cancelled"] == '') &&
                        ( !isset($data["apps"][$k]["field"]) || @$data["apps"][$k]["field"] == $field || $field == '')
                        )
                    {
                        $slot = $data["apps"][$k]["slot"];
                        $tmp2[] = array("d"=>$data["apps"][$k]["date"] ,"h1"=>intval(substr($slot,0,2)),"m1"=>intval(substr($slot,3,2)),"quantity1"=>$data["apps"][$k]["quantity1"],"quantity2"=>$data["apps"][$k]["quantity2"],"quantity3"=>@$data["apps"][$k]["quantity3"],"quantity4"=>@$data["apps"][$k]["quantity4"],"quantity5"=>@$data["apps"][$k]["quantity5"]);
                        if ($_REQUEST['cp_slots_action'] == 'mv')
                        {
                            $tmp2[count($tmp2)-1]["info"] = $myrows[$i]->data;
                            $tmp2[count($tmp2)-1]["e"] = $myrows[$i]->notifyto;
                        }
                    }
            }
            if ($_REQUEST['cp_slots_action'] == 'mv' && is_admin())
            {
                $this->print_multiview_format($tmp2);
            }
            else
                echo json_encode($tmp2); //{type:"all",d:"",h1:8,m1:0,h2:17,m2:0}
		    exit;
        }

    	if( isset( $_REQUEST[ 'cp_cptslotsb_resources' ] ) )
    	{
    		if( $_REQUEST[ 'cp_cptslotsb_resources' ] == 'admin' )
    		{
    			require_once dirname( __FILE__ ).'/js/fbuilder-loader-admin.php';
    		}
            else if( $_REQUEST[ 'cp_cptslotsb_resources' ] == 'css' )
    		{
                header("Content-type: text/css");
    			$custom_styles = base64_decode(get_option('CP_WPTSLOTSBK_CSS', ''));
                echo $custom_styles;
    		}
    		else
    		{
    			require_once dirname( __FILE__ ).'/js/fbuilder-loader-public.php';
    		}
    		exit;
    	}

        $this->check_reports();

        if ($this->get_param($this->prefix.'_encodingfix') == '1')
        {
            $wpdb->query('alter table '.$wpdb->prefix.$this->table_items.' convert to character set utf8 collate utf8_unicode_ci;');
            $wpdb->query('alter table '.$wpdb->prefix.$this->table_messages.' convert to character set utf8 collate utf8_unicode_ci;');
            echo 'Ok, encoding fixed.';
            exit;
        }

        if ($this->get_param($this->prefix.'_captcha') == 'captcha' )
        {
            @include_once dirname( __FILE__ ) . '/captcha/captcha.php';
            exit;
        }


        if ($this->get_param($this->prefix.'_csv') && is_admin() && $this->check_current_user_access(intval($this->get_param("cal"))))
        {
            $this->export_csv();
            return;
        }

        
        if ($this->get_param($this->prefix.'_csv2') && is_admin() && $this->check_current_user_access(intval($this->get_param("cal"))))
        {
            $this->export_csv_schedule(array());
            return;
        }
        

        if ( $this->get_param($this->prefix.'_post_options') && is_admin() && $this->check_current_user_access(intval($this->get_param("cal"))))
        {
            $this->save_options();
            return;
        }


        if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['CP_WPTSLOTSBK_post_edition'] ) && current_user_can('edit_pages') && is_admin() )
        {
            $this->save_edition();
            return;
        }


    	if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST[$this->prefix.'_pform_process'] ) )
    	    if ( 'GET' != $_SERVER['REQUEST_METHOD'] || !isset( $_GET['hdcaptcha_'.$this->prefix.'_post'] ) )
    		    return;

        if ($this->get_param($this->prefix.'_id')) $this->item = $this->get_param($this->prefix.'_id');

        @session_start();
        if (isset($_GET["ps"])) $sequence = $_GET["ps"]; else if (isset($_POST["cp_pform_psequence"])) $sequence = $_POST["cp_pform_psequence"];
        if (
               !apply_filters( 'cptslotsb_valid_submission', true) ||
               (
                   (!is_admin() && $this->get_option('cv_enable_captcha', CP_TSLOTSBOOK_DEFAULT_cv_enable_captcha) != 'false') &&
                   ( (strtolower($this->get_param('hdcaptcha_'.$this->prefix.'_post')) != strtolower($_SESSION['rand_code'.$sequence])) ||
                     ($_SESSION['rand_code'.$sequence] == '')
                   )
                   &&
                   ( (md5(strtolower($this->get_param('hdcaptcha_'.$this->prefix.'_post'))) != ($_COOKIE['rand_code'.$sequence])) ||
                     ($_COOKIE['rand_code'.$sequence] == '')
                   )
               )
           )
        {
            echo 'captchafailed';
            exit;
        }

    	// if this isn't the real post (it was the captcha verification) then echo ok and exit
        if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST[$this->prefix.'_pform_process'] ) )
    	{
    	    echo 'ok';
            exit;
    	}

        foreach ($_POST as $item => $value)
            $_POST[$item] = (is_array($value)?$value:stripcslashes($value));

        // get form info
        //---------------------------
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        $form_data = json_decode($this->cleanJSON($this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure)));
        $fields = array();

        $apps = $this->extract_appointments($form_data[0], $_POST, $sequence);
        $price = $this->extract_total_price ($apps);

        $apptext = $this->get_appointments_text ($apps);
        $excluded_items = array();

        foreach ($form_data[0] as $item)
            if ($item->ftype != 'fslots')
            {
                $fields[$item->name] = $item->title;
                if ($item->ftype == 'fPhone') // join fields for phone fields
                {
                    $_POST[$item->name.$sequence] = '';
                    for($i=0; $i<=substr_count($item->dformat," "); $i++)
                    {
                        $_POST[$item->name.$sequence] .= ($_POST[$item->name.$sequence."_".$i]!=''?($i==0?'':'-').$_POST[$item->name.$sequence."_".$i]:'');
                        unset($_POST[$item->name.$sequence."_".$i]);
                    }
                }
            }
            else
            {
                $fields[$item->name] = $item->title;
                $_POST[$item->name.$sequence] = $apptext;
                $excluded_items[] = $item->name;
            }

        // grab posted data
        //---------------------------
        $buffer = __('Appointments','wp-time-slots-booking-form').":\n".$apptext."\n";
        $params = array();
        $params["final_price"] = $price;
        $params["apps"] = $apps;
        foreach ($apps as $appitem) 
        {   
           $params["app_status_".$appitem["id"]] = $appitem["cancelled"];
           $params["app_price_".$appitem["id"]] = $appitem["services_totalprice"];
           $params["app_date_".$appitem["id"]] = $this->format_date($appitem["date"]);
           $params["app_slot_".$appitem["id"]] = $appitem["slot"];
           $params["app_quantity1_".$appitem["id"]] = $appitem["quantity1"];
           $params["app_quantity2_".$appitem["id"]] = $appitem["quantity2"];
           $params["app_quantity3_".$appitem["id"]] = $appitem["quantity3"];
           $params["app_quantity4_".$appitem["id"]] = $appitem["quantity4"];
           $params["app_quantity5_".$appitem["id"]] = $appitem["quantity5"];
        }        
        $params["formid"] = $this->item;
        $params["formname"] = $this->get_option('form_name');
        $params["referrer"] = $_POST["refpage".$sequence];
        foreach ($_POST as $item => $value)
            if (isset($fields[str_replace($sequence,'',$item)]))
            {
                if (is_array($value))
                {
                    for ($iv=0; $iv<count($value); $iv++)
                        $value[$iv] = str_replace(CP_TSLOTSBOOK_REP_ARR, "@", $value[$iv]);
                }
                else
                    $value = str_replace(CP_TSLOTSBOOK_REP_ARR, "@", $value);
                if (!in_array(str_replace($sequence,'',$item), $excluded_items))
                    $buffer .= $fields[str_replace($sequence,'',$item)] . ": ". (is_array($value)?(implode(", ",$value)):($value)) . "\n\n";
                $params[str_replace($sequence,'',$item)] = $value;
            }

        foreach ($_FILES as $item => $value)
        {
            $item = str_replace( $sequence,'',$item );
	    	if ( isset( $fields[ $item ] ) )
            {
	    		$files_names_arr = array();
	    		$files_links_arr = array();
	    		$files_urls_arr  = array();
	    		for( $f = 0; $f < count( $value[ 'name' ] ); $f++ )
	    		{
	    			if( !empty( $value[ 'name' ][ $f ] ) )
	    			{
	    				$uploaded_file = array(
	    					'name' 		=> $value[ 'name' ][ $f ],
	    					'type' 		=> $value[ 'type' ][ $f ],
	    					'tmp_name' 	=> $value[ 'tmp_name' ][ $f ],
	    					'error' 	=> $value[ 'error' ][ $f ],
	    					'size' 		=> $value[ 'size' ][ $f ],
	    				);
	    				$movefile = wp_handle_upload( $uploaded_file, array( 'test_form' => false ) );
	    				if ( empty( $movefile[ 'error' ] ) )
	    				{
	    					$files_links_arr[] = $params[ $item."_link" ][ $f ] = $movefile["file"];
	    					$files_urls_arr[]  = $params[ $item."_url" ][ $f ] = $movefile["url"];
	    					$files_names_arr[] = $uploaded_file[ 'name' ];

						    /**
						     * Action called when the file is uploaded, the file's data is passed as parameter
						     */
						    do_action( 'cptslotsb_file_uploaded', $movefile );
	    				} //else echo $movefile[ 'error' ];
	    			}
	    		}
	    		$joinned_files_names = implode( ", ", $files_names_arr );
	    		$buffer .= $fields[ $item ] . ": ". $joinned_files_names . "\n\n";
	    		$params[ $item ] = $joinned_files_names;
	    		//$params[ $item."_links"] = implode( ",",  $files_links_arr );
	    		//$params[ $item."_urls"] = implode( ",",  $files_urls_arr );
	    	}
	    }

        $buffer_A = $buffer;

	    /**
	     * Action called before insert the data into database.
	     * To the function is passed an array with submitted data.
	     */
	    do_action_ref_array( 'cptslotsb_process_data_before_insert', array(&$params) );

        // insert into database
        //---------------------------
        $current_user = wp_get_current_user();
        $this->add_field_verify($wpdb->prefix.$this->table_messages, "whoadded");

        $wpdb->query("ALTER TABLE ".$wpdb->prefix.$this->table_messages." CHANGE `ipaddr` `ipaddr` VARCHAR(250)");
        $to = $this->get_option('cu_user_email_field', CP_TSLOTSBOOK_DEFAULT_cu_user_email_field);
        $rows_affected = $wpdb->insert( $wpdb->prefix.$this->table_messages, array( 'formid' => $this->item,
                                                                                    'time' => current_time('mysql'),
                                                                                    'ipaddr' => (CP_TSLOTSBOOK_DEFAULT_track_IP?$_SERVER['REMOTE_ADDR']:''),
                                                                                    'notifyto' => sanitize_email((@$_POST[$to.$sequence]?$_POST[$to.$sequence]:'')),
                                                                                    'posted_data' => serialize($params),
                                                                                    'data' =>$buffer_A,
                                                                                    'whoadded' => "".$current_user->ID
                                                                                   ) );
        if (!$rows_affected)
        {
            echo 'Error saving data! Please try again.';
            exit;
        }

        $myrows = $wpdb->get_results( "SELECT MAX(id) as max_id FROM ".$wpdb->prefix.$this->table_messages );
        $item_number = $myrows[0]->max_id;

	    // Call action for data processing
	    //---------------------------------
	    $params[ 'itemnumber' ] = $item_number;

	    /**
	     * Action called after inserted the data into database.
	     * To the function is passed an array with submitted data.
	     */
	    do_action_ref_array( 'cptslotsb_process_data',  array(&$params) );

        $wpdb->update( $wpdb->prefix.$this->table_messages,
                       array( 'posted_data' => serialize($params) ),
                       array ( 'id' => $item_number),
                       array( '%s' ),
	                   array( '%d' )
                       );

        $this->ready_to_go_reservation($item_number, "", $params);
        $_SESSION[ 'cp_cff_form_data' ] = $item_number;

        if (is_admin())
            return;

        /**
		 * Filters applied to decide if the website should be redirected to the thank you page after submit the form,
		 * pass a boolean as parameter and returns a boolean
		 */
		$redirect = true;
        $redirect = apply_filters( 'cptslotsb_redirect', $redirect );

        if( $redirect )
        {
            header("Location: ". $this->replace_tags($this->get_option('fp_return_page', CP_TSLOTSBOOK_DEFAULT_fp_return_page), $params));
            exit();
        }
    }


    public function replace_tags ($message, $params)
    {
        foreach ($params as $item => $value)
        {
            $message = str_replace('<'.'%'.$item.'%'.'>',(is_array($value)?(implode(", ",$value)):($value)),$message);
            $message = str_replace('%'.$item.'%',(is_array($value)?(implode(", ",$value)):($value)),$message);
        }
        for ($i=0;$i<500;$i++)
        {
            $message = str_replace('<'.'%fieldname'.$i.'%'.'>',"",$message);
            $message = str_replace('%fieldname'.$i.'%',"",$message);
        }
        return $message;
    }


    public function extract_appointments($form,$data,$sequence)
    {
        $apps = array();
        $subid = 0;
        foreach($form as $field)
            if ($field->ftype == 'fslots' && @$data[$field->name.$sequence] != '')
            {
                $apps_text = explode(';',$data[$field->name.$sequence]);
                foreach($apps_text as $app_item_text)
                {
                    $item_split = explode(' ',$app_item_text);
                    $quantity1 = intval($item_split[2]);
                    $quantity2 = intval($item_split[3]);
                    $quantity3 = intval($item_split[4]);
                    $quantity4 = intval($item_split[5]);
                    $quantity5 = intval($item_split[6]);
                    $subid++;
                    $apps[] = array (
                                     'id' => $subid,
                                     'cancelled' => $this->get_option('defaultstatus', ''),
                                     'price' => floatval($item_split[7]),
                                     'priceindex' => intval($item_split[8]),
                                     'date' => $item_split[0],
                                     'slot' => $item_split[1],
                                     'quantity1' => $quantity1,
                                     'quantity2' => $quantity2,
                                     'quantity3' => $quantity3,
                                     'quantity4' => $quantity4,
                                     'quantity5' => $quantity5,
                                     'military' => $field->militaryTime,
                                     'field' => $field->name,
                                     'services_totalprice' => @$data[$field->name.$sequence."_services"]
                                     );
                }
            }
        return $apps;
    }


    function extract_total_price($apps)
    {
        $price = 0;
        foreach($apps as $app)
            $price += $app["price"];
        if (isset($apps[0]) && floatval($apps[0]["services_totalprice"]))
            $price += floatval($apps[0]["services_totalprice"]);
        return number_format($price,2,'.','');
    }


    public function get_appointments_text($apps)
    {
        $text = '';
        foreach($apps as $app)
        {
            $slot = $app["slot"];
            if (@$app["military"] == 0)            
            { 
                $times[0] = explode(":",$slot);              
                $slot = ($times[0][0]>12?$times[0][0]-12:$times[0][0]).":".$times[0][1].' '.($times[0][0]>=12?'PM':'AM');                       
            } 
            $text .= " - ".$this->format_date($app["date"])." ".$slot." (".(
                  $app["quantity1"]
                  .($app["quantity2"] || $app["quantity3"] || $app["quantity4"] || $app["quantity5"]?'/'.$app["quantity2"]:'')
                  .($app["quantity3"] || $app["quantity4"] || $app["quantity5"]?'/'.$app["quantity3"]:'')
                  .($app["quantity4"] || $app["quantity5"]?'/'.$app["quantity4"]:'')
                  .($app["quantity5"]?'/'.$app["quantity5"]:'')
                  ).")\n";
        }
        return $text;
    }


    function format12hours($time, $is_non_military)
    {
        if ($is_non_military)
        {
            $times = explode(":",$time);
            $time = ($times[0]>12?$times[0]-12:$times[0]).":".$times[1].' '.($times[0]>=12?'PM':'AM');
        }
        return $time;
    }    


    function format_date($date)
    {
        $format = $this->get_option('date_format', 'mm/dd/yy');
        if (!$format) $format = 'mm/dd/yy';
        $format = str_replace('mm', 'm', $format);
        $format = str_replace('dd', 'd', $format);
        $format = str_replace('yy', 'Y', $format);
        $format = str_replace('DD', 'K', $format);
        $format = str_replace('MM', 'Q', $format);
        
        $dconv = date($format, strtotime($date));
        
        $dconv = str_replace('K', ucfirst (__(date('l', strtotime($date)))), $dconv);
        $dconv = str_replace('Q', ucfirst (__(date('F', strtotime($date)))), $dconv);
        
        return $dconv;
    }


    function ready_to_go_reservation($itemnumber, $payer_email = "", $params = array())
    {

        global $wpdb;

        $myrows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table_messages." WHERE id=%d", $itemnumber) );

        $mycalendarrows = $wpdb->get_results( $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.$this->table_items.' WHERE `id`=%d', $myrows[0]->formid) );

        $this->item = $myrows[0]->formid;

        $buffer_A = $myrows[0]->data;
        $buffer = $buffer_A;

        if ('true' == $this->get_option('fp_inc_additional_info', CP_TSLOTSBOOK_DEFAULT_fp_inc_additional_info))
        {
            $buffer .="ADDITIONAL INFORMATION\n"
                  ."*********************************\n";

            $basic_data = "IP: ".$myrows[0]->ipaddr."\n"
              ."Server Time:  ".date("Y-m-d H:i:s")."\n";

		    /**
		     *	Includes additional information to the email's message,
		     *  are passed two parameters: the basic information, and the IP address
		     */
		    $basic_data = apply_filters( 'cptslotsb_additional_information',  $basic_data, $myrows[0]->ipaddr );
		    $params["additional"] = $basic_data;
		    $buffer .= $basic_data;
        }

        // 1- Send email
        //---------------------------
        $attachments = array();

        $message = str_replace('<'.'%', '%', $this->get_option('fp_message', CP_TSLOTSBOOK_DEFAULT_fp_message));
        $message = str_replace('%'.'>', '%', $message);
        $subject = str_replace('<'.'%', '%', $this->get_option('fp_subject', CP_TSLOTSBOOK_DEFAULT_fp_subject));
        $subject = str_replace('%'.'>', '%', $subject);

        if ('html' == $this->get_option('fp_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format))
            $message = str_replace('%INFO%',str_replace("\n","<br />",str_replace('<','&lt;',$buffer)),$message);
        else
            $message = str_replace('%INFO%',$buffer,$message);
        $subject = $this->get_option('fp_subject', CP_TSLOTSBOOK_DEFAULT_fp_subject);

        /**
		 *	Attach or modify attached files,
		 *  Example for adding ical or PDF attachments
		 */
		$attachments = apply_filters( 'cptslotsb_email_attachments',  $attachments, $params, $this->item);

        $params["apps"] = $this->get_appointments_text($params["apps"]);
        foreach ($params as $item => $value)
        {
            $message = str_replace('%'.$item.'%',(is_array($value)?(implode(", ",$value)):($value)),$message);
            $subject = str_replace('%'.$item.'%',(is_array($value)?(implode(", ",$value)):($value)),$subject);
            if (strpos($item,"_link"))
            {
                foreach ($value as $filevalue)
                    $attachments[] = $filevalue;
            }
        }

        $message = str_replace('%itemnumber%',$itemnumber,$message);
        $subject = str_replace('%itemnumber%',$itemnumber,$subject);

        $from = $this->get_option('fp_from_email', @CP_TSLOTSBOOK_DEFAULT_fp_from_email);
        $to = explode(",",$this->get_option('fp_destination_emails', @CP_TSLOTSBOOK_DEFAULT_fp_destination_emails));
        if ('html' == $this->get_option('fp_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format)) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";

        $replyto = $myrows[0]->notifyto;
        if ($this->get_option('fp_emailfrommethod', "fixed") == "customer")
            $from_1 = $replyto;
        else
            $from_1 = $from;

        if ($this->get_option('fp_emailtomethod', "fixed") == 'customer')
        {
            $text_addr = $params[$this->get_option('fp_destination_emails_field', "fixed")];
            if (is_array($text_addr))
                $text_addr = implode(", ",$text_addr);
            $pattern = '/[a-zA-Z0-9_\.\+-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; //regex for pattern of e-mail address
            preg_match_all($pattern, $text_addr, $matches);
            if (count($matches[0]) > 0)
                $to = $matches[0];
        }
        $to = array_unique ($to);

        for ($i=0;$i<500;$i++)
        {
            $subject = str_replace('%fieldname'.$i.'%',"",$subject);
            $message = str_replace('%fieldname'.$i.'%',"",$message);
        }

        // if is_admin and not required emails end function here
        if (is_admin() && !isset($_POST["sendemails_admin"]))
            return;

        foreach ($to as $item)
            if (trim($item) != '')
            {
                if (!strpos($from_1,">"))
                    $from_1 = '"'.$from_1.'" <'.$from_1.'>';
                wp_mail(trim($item), $subject, $message,
                    "From: ".$from_1."\r\n".
                    ($replyto!=''?"Reply-To: ".$replyto."\r\n":'').
                    $content_type.
                    "X-Mailer: PHP/" . phpversion(), $attachments);
            }

        if ($mycalendarrows[0]->rep_days == 0 && $mycalendarrows[0]->rep_enable == 'yes')
        {
            $this->check_reports(true);
        }

        // 2- Send copy to user
        //---------------------------
        $to = $this->get_option('cu_user_email_field', CP_TSLOTSBOOK_DEFAULT_cu_user_email_field);
        $_POST[$to] = $myrows[0]->notifyto;
        if ((trim($_POST[$to]) != '' || $payer_email != '') && 'true' == $this->get_option('cu_enable_copy_to_user', CP_TSLOTSBOOK_DEFAULT_cu_enable_copy_to_user))
        {
            $message = str_replace('<'.'%', '%', $this->get_option('cu_message', CP_TSLOTSBOOK_DEFAULT_cu_message));
            $message = str_replace('%'.'>', '%', $message);

            $subject = str_replace('<'.'%', '%', $this->get_option('cu_subject', CP_TSLOTSBOOK_DEFAULT_cu_subject));
            $subject = str_replace('%'.'>', '%', $subject);
            if ('html' == $this->get_option('cu_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format))
                $message = str_replace('%INFO%',str_replace("\n","<br />",str_replace('<','&lt;',$buffer_A)).'</pre>',$message);
            else
                $message = str_replace('%INFO%',$buffer_A,$message);

            foreach ($params as $item => $value)
            {
                $message = str_replace('%'.$item.'%',(is_array($value)?(implode(", ",$value)):($value)),$message);
                $subject = str_replace('%'.$item.'%',(is_array($value)?(implode(", ",$value)):($value)),$subject);
            }

            $message = str_replace('%itemnumber%',$itemnumber,$message);
            $subject = str_replace('%itemnumber%',$itemnumber,$subject);

            for ($i=0;$i<500;$i++)
            {
                $subject = str_replace('%fieldname'.$i.'%',"",$subject);
                $message = str_replace('%fieldname'.$i.'%',"",$message);
            }

            if (!strpos($from,">"))
                $from = '"'.$from.'" <'.$from.'>';
            if ('html' == $this->get_option('cu_emailformat', CP_TSLOTSBOOK_DEFAULT_email_format)) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
            if ($_POST[$to] != '')
                wp_mail(trim($_POST[$to]), $subject, $message,
                        "From: ".$from."\r\n".
                        $content_type.
                        "X-Mailer: PHP/" . phpversion(), $attachments);
            if ($_POST[$to] != $payer_email && $payer_email != '')
                wp_mail(trim($payer_email), $subject, $message,
                        "From: ".$from."\r\n".
                        $content_type.
                        "X-Mailer: PHP/" . phpversion(), $attachments);
        }

    }


    function available_templates(){

    	if( empty( $this->CP_CFPP_global_templates ) )
    	{
    		// Get available designs
    		$tpls_dir = dir( plugin_dir_path( __FILE__ ).'templates' );
    		$this->CP_CFPP_global_templates = array();
    		while( false !== ( $entry = $tpls_dir->read() ) )
    		{
    			if ( $entry != '.' && $entry != '..' && is_dir( $tpls_dir->path.'/'.$entry ) && file_exists( $tpls_dir->path.'/'.$entry.'/config.ini' ) )
    			{
    				if( ( $ini_array = parse_ini_file( $tpls_dir->path.'/'.$entry.'/config.ini' ) ) !== false )
    				{
    					if( !empty( $ini_array[ 'file' ] ) ) $ini_array[ 'file' ] = plugins_url( 'templates/'.$entry.'/'.$ini_array[ 'file' ], __FILE__ );
    					if( !empty( $ini_array[ 'thumbnail' ] ) ) $ini_array[ 'thumbnail' ] = plugins_url( 'templates/'.$entry.'/'.$ini_array[ 'thumbnail' ], __FILE__ );
    					$this->CP_CFPP_global_templates[ $ini_array[ 'prefix' ] ] = $ini_array;
    				}
    			}
    		}
    	}

    	return $this->CP_CFPP_global_templates;
    }



    function save_edition()
    {
        $this->verify_nonce ($_POST["nonce"], 'cptslotsb_actions_adminsettings');

        global $wpdb;

        foreach ($_POST as $item => $value)
            if (!is_array($value))
                $_POST[$item] = stripcslashes($value);
        if (isset($_POST["gotab"]) && @$_POST["gotab"] == '')
        {
            update_option( 'cp_cptslotsb_rep_enable', $_POST["cp_cptslotsb_rep_enable"]);
            update_option( 'cp_cptslotsb_rep_days', $_POST["cp_cptslotsb_rep_days"]);
            update_option( 'cp_cptslotsb_rep_hour', $_POST["cp_cptslotsb_rep_hour"]);
            update_option( 'cp_cptslotsb_rep_emails', $_POST["cp_cptslotsb_rep_emails"]);
            update_option( 'cp_cptslotsb_fp_from_email', $_POST["cp_cptslotsb_fp_from_email"]);
            update_option( 'cp_cptslotsb_rep_subject', $_POST["cp_cptslotsb_rep_subject"]);
            update_option( 'cp_cptslotsb_rep_emailformat', $_POST["cp_cptslotsb_rep_emailformat"]);
            update_option( 'cp_cptslotsb_rep_message', $_POST["cp_cptslotsb_rep_message"]);
        }
        else if (@$_POST["gotab"] == 'fixarea')
        {
            update_option( 'cp_tslotsb_LOAD_SCRIPTS', ($_POST["ccscriptload"]=="1"?"0":"1") );
            if ($_POST["cccharsets"] != '')
            {
                $target_charset = str_replace('`','``',sanitize_text_field($_GET["cccharsets"]));
                $tables = array( $wpdb->prefix.$this->table_messages, $wpdb->prefix.$this->table_items );
                foreach ($tables as $tab)
                {
                    $myrows = $wpdb->get_results( "DESCRIBE {$tab}" );
                    foreach ($myrows as $item)
	                {
	                    $name = $item->Field;
	        	        $type = $item->Type;
	        	        if (preg_match("/^varchar\((\d+)\)$/i", $type, $mat) || !strcasecmp($type, "CHAR") || !strcasecmp($type, "TEXT") || !strcasecmp($type, "MEDIUMTEXT"))
	        	        {
	                        $wpdb->query("ALTER TABLE {$tab} CHANGE {$name} {$name} {$type} COLLATE `{$target_charset}`");
	                    }
	                }
                }
            }
        }
        else
        {
            if (substr_count(@$_POST['editionarea'],"\\\""))
                $_POST["editionarea"] = stripcslashes(@$_POST["editionarea"]);
            if (@$_POST["cfwpp_edit"] == 'js')
                update_option('CP_WPTSLOTSBK_JS', base64_encode(@$_POST["editionarea"]));
            else if (@$_POST["cfwpp_edit"] == 'css')
                update_option('CP_WPTSLOTSBK_CSS', base64_encode(@$_POST["editionarea"]));
        }
    }



    function save_options()
    {
        global $wpdb;
        $this->item = $_POST[$this->prefix."_id"];

        $this->verify_nonce ($_POST["anonce"], 'cptslotsb_actions_admin');

        $this->add_field_verify($wpdb->prefix.$this->table_items, 'calendar_language');
        $this->add_field_verify($wpdb->prefix.$this->table_items, 'date_format');
        $this->add_field_verify($wpdb->prefix.$this->table_items, 'vs_text_maxapp');
        $this->add_field_verify($wpdb->prefix.$this->table_items, 'defaultstatus', "VARCHAR(250) DEFAULT '' NOT NULL");;
        $this->add_field_verify($wpdb->prefix.$this->table_items, 'defaultpaidstatus', "VARCHAR(250) DEFAULT '' NOT NULL");
        $this->add_field_verify($wpdb->prefix.$this->table_items, 'cp_user_access_settings');

        if ((substr_count($_POST['form_structure_control'],"\\") > 1) || substr_count($_POST['form_structure_control'],"\\\"title\\\":"))
            foreach ($_POST as $item => $value)
                if (!is_array($value))
                    $_POST[$item] = stripcslashes($value);

        $data = array(
                      'form_structure' => $_POST['form_structure'],

                      'vs_text_maxapp' => $_POST['vs_text_maxapp'],
                      'calendar_language' => $_POST['calendar_language'],
                      'date_format' => $_POST['date_format'],
                      'product_name' => $_POST['product_name'],
                      'pay_later_label' => $_POST['pay_later_label'],
                      'fp_from_email' => $_POST['fp_from_email'],
                      'fp_destination_emails' => $_POST['fp_destination_emails'],
                      'fp_subject' => $_POST['fp_subject'],
                      'fp_inc_additional_info' => $_POST['fp_inc_additional_info'],
                      'fp_return_page' => $_POST['fp_return_page'],
                      'fp_message' => $_POST['fp_message'],
                      'fp_emailformat' => $_POST['fp_emailformat'],

                      'defaultstatus' => $_POST['defaultstatus'],
                      'defaultpaidstatus' => $_POST['defaultpaidstatus'],

                      'fp_emailtomethod' => $_POST['fp_emailtomethod'],
                      'fp_destination_emails_field' => $_POST['fp_destination_emails_field'],

                      'cu_enable_copy_to_user' => $_POST['cu_enable_copy_to_user'],
                      'cu_user_email_field' => $_POST['cu_user_email_field'],
                      'cu_subject' => $_POST['cu_subject'],
                      'cu_message' => $_POST['cu_message'],
                      'cu_emailformat' => $_POST['cu_emailformat'],
                      'fp_emailfrommethod' => $_POST['fp_emailfrommethod'],

                      'vs_text_is_required' => $_POST['vs_text_is_required'],
                      'vs_text_is_email' => $_POST['vs_text_is_email'],
                      'vs_text_datemmddyyyy' => $_POST['vs_text_datemmddyyyy'],
                      'vs_text_dateddmmyyyy' => $_POST['vs_text_dateddmmyyyy'],
                      'vs_text_number' => $_POST['vs_text_number'],
                      'vs_text_digits' => $_POST['vs_text_digits'],
                      'vs_text_max' => $_POST['vs_text_max'],
                      'vs_text_min' => $_POST['vs_text_min'],
                      'vs_text_pageof' => $_POST['vs_text_pageof'],
                      'vs_text_submitbtn' => $_POST['vs_text_submitbtn'],
                      'vs_text_previousbtn' => $_POST['vs_text_previousbtn'],
                      'vs_text_nextbtn' => $_POST['vs_text_nextbtn'],

                      'cp_user_access' => serialize(@$_POST["cp_user_access"]),
                      'cp_user_access_settings' => @$_POST["cp_user_access_settings"],
                      'rep_enable' => $_POST['rep_enable'],
                      'rep_days' => $_POST['rep_days'],
                      'rep_hour' => $_POST['rep_hour'],
                      'rep_emails' => $_POST['rep_emails'],
                      'rep_subject' => $_POST['rep_subject'],
                      'rep_emailformat' => $_POST['rep_emailformat'],
                      'rep_message' => $_POST['rep_message'],

                      'cv_enable_captcha' => $_POST['cv_enable_captcha'],
                      'cv_width' => $_POST['cv_width'],
                      'cv_height' => $_POST['cv_height'],
                      'cv_chars' => $_POST['cv_chars'],
                      'cv_font' => $_POST['cv_font'],
                      'cv_min_font_size' => $_POST['cv_min_font_size'],
                      'cv_max_font_size' => $_POST['cv_max_font_size'],
                      'cv_noise' => $_POST['cv_noise'],
                      'cv_noise_length' => $_POST['cv_noise_length'],
                      'cv_background' => str_replace('#','',$_POST['cv_background']),
                      'cv_border' => str_replace('#','',$_POST['cv_border']),
                      'cv_text_enter_valid_captcha' => $_POST['cv_text_enter_valid_captcha']
    	);
        $wpdb->update ( $wpdb->prefix.$this->table_items, $data, array( 'id' => $this->item ));

        if (isset($_POST["savepublish"]))
        {
            echo '<script type="text/javascript">document.location="?page=cp_timeslotsbooking&pwizard=1&cal='.$this->item.'";</script>';
        } else if (isset($_POST["savereturn"]))
        {
            echo '<script type="text/javascript">document.location="?page=cp_timeslotsbooking&confirm=1";</script>';
        }
    }


    function get_form_field_label ($fieldid, $form)
    {
            foreach($form as $item)
                if ($item->name == $fieldid)
                {
                    if (isset($item->shortlabel) && $item->shortlabel != '')
                        return $item->shortlabel;
                    else
                        return $item->title;
                }
        return $fieldid;
    }


    function generateSafeFileName($filename) {
        $filename = strtolower(strip_tags($filename));
        $filename = str_replace(";","_",$filename);
        $filename = str_replace("#","_",$filename);
        $filename = str_replace(" ","_",$filename);
        $filename = str_replace("'","",$filename);
        $filename = str_replace('"',"",$filename);
        $filename = str_replace("__","_",$filename);
        $filename = str_replace("&","and",$filename);
        $filename = str_replace("/","_",$filename);
        $filename = str_replace("\\","_",$filename);
        $filename = str_replace("?","",$filename);
        return sanitize_file_name($filename);
    }

    function export_csv ()
    {
        if (!is_admin())
            return;
        global $wpdb;

        $this->item = intval($this->get_param("cal"));

        if ($this->item)
        {
            $form = json_decode($this->cleanJSON($this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure)));
            $form = $form[0];
        }
        else
            $form = array();

        $cond = '';
        if ($this->get_param("search")) $cond .= " AND (data like '%".esc_sql($this->get_param("search"))."%' OR posted_data LIKE '%".esc_sql($this->get_param("search"))."%')";
        if ($this->get_param("dfrom")) $cond .= " AND (`time` >= '".esc_sql($this->get_param("dfrom"))."')";
        if ($this->get_param("dto")) $cond .= " AND (`time` <= '".esc_sql($this->get_param("dto"))." 23:59:59')";
        if ($this->item != 0) $cond .= " AND formid=".$this->item;


	    $events_query = "SELECT * FROM ".$wpdb->prefix.$this->table_messages." WHERE 1=1 ".$cond." ORDER BY `time` DESC";
	    /**
	     * Allows modify the query of messages, passing the query as parameter
	     * returns the new query
	     */
	    $events_query = apply_filters( 'cptslotsb_csv_query', $events_query );
	    $events = $wpdb->get_results( $events_query );

        if ($this->include_user_data_csv)
            $fields = array("ID", "Form ID", "Time", "IP Address", "email");
        else
            $fields = array("ID", "Form", "Time", "email");
        $values = array();
        foreach ($events as $item)
        {
            if ($this->include_user_data_csv)
                $value = array($item->id,$this->get_option('form_name',''), $item->time, $item->ipaddr, $item->notifyto);
            else
                $value = array($item->id,$this->get_option('form_name',''),  $item->time, $item->notifyto);
            if ($item->posted_data)
                $data = unserialize($item->posted_data);
            else
                $data = array();

            $end = count($fields);
            for ($i=0; $i<$end; $i++)
                if (isset($data[$fields[$i]]) ){
                    $d = $data[$fields[$i]];
                    if (substr($fields[$i],0,strlen('app_status_')) == 'app_status_')
                    {
                       $d = $data["apps"][ intval( substr($fields[$i],strlen('app_status_'))-1) ]["cancelled"];
                       if ($d == '')
                           $d = __('Approved','wp-time-slots-booking-form');
                    }
                    $value[$i] = $d;
                    unset($data[$fields[$i]]);
                }

            if (is_array($data)) foreach ($data as $k => $d)
                if ($k != 'apps' && $k != 'itemnumber')
                {
                   $fields[] = $k;
                   if (substr($k,0,strlen('app_status_')) == 'app_status_')
                   {
                       $d = $data["apps"][ intval( substr($k,strlen('app_status_'))-1) ]["cancelled"];
                       if ($d == '')
                           $d = __('Approved','appointment-hour-booking');
                   }
                   $value[] = $d;
                }
            $values[] = $value;
        }

        $filename = $this->generateSafeFileName(strtolower($this->get_option('form_name','export'))).'_'.date("m_d_y");

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$filename.".csv");

        $end = count($fields);
        for ($i=0; $i<$end; $i++)
        {
            $hlabel = $this->iconv("utf-8", "ISO-8859-1//TRANSLIT//IGNORE", $this->get_form_field_label($fields[$i],$form));
            echo '"'.str_replace('"','""', $hlabel).'",';
        }
        echo "\n";
        foreach ($values as $item)
        {
            for ($i=0; $i<$end; $i++)
            {
                if (!isset($item[$i]))
                    $item[$i] = '';
                if (is_array($item[$i]))
                    $item[$i] = implode($item[$i],',');
                $item[$i] = $this->iconv("utf-8", "ISO-8859-1//TRANSLIT//IGNORE", $item[$i]);
                echo '"'.str_replace('"','""', $item[$i]).'",';
            }
            echo "\n";
        }

        exit;
    }
    
    
    function export_csv_schedule ($atts)
    {
        if (!is_admin())
            return;
        global $wpdb;
        extract( shortcode_atts( array(
	    	'calendar' => '',
	    	'fields' => 'DATE,TIME,SERVICE,quantity,final_price,paid,email,data,cancelled',
	    	'from' => "today -10 days",
	    	'to' => "today +30 days",
            'paidonly' => "",
            'status' => "-1"
	    ), $atts ) );
        
        if (isset($_REQUEST["dfrom"]) && $_REQUEST["dfrom"] != '') $from = sanitize_text_field($_REQUEST["dfrom"]);        
        if (isset($_REQUEST["dto"]) && $_REQUEST["dto"] != '') $to = sanitize_text_field($_REQUEST["dto"]);     
        if (isset($_REQUEST["status"])) $status = sanitize_text_field($_REQUEST["status"]);  
        if (isset($_REQUEST["paid"])) $paidonly = sanitize_text_field($_REQUEST["paid"]);  
        if ($this->get_param("cal")) $calendar = intval($this->get_param("cal"));        
     
        $this->item = intval($this->get_param("cal"));
     
        ob_start();
        
        // calculate dates
        if ($this->get_option('date_format', 'mm/dd/yy') == 'dd/mm/yy')
        {
            $from = str_replace('/','.',$from);
            $to = str_replace('/','.',$to);
        }

        $from = date("Y-m-d",strtotime($from));
        $to = date("Y-m-d",strtotime($to)). " 23:59:59" ;
        
        $calquery = '';        
        $calendar = explode (",",$calendar);
        foreach ($calendar as $cal)
            if (trim($cal))
                $calquery .= ($calquery!=''?' OR ':'').'formid='.intval(trim($cal));
        if ($calquery != '')
            $calquery = '('.$calquery.') AND ';
        
        // pre-select time-slots
        $selection = array();
        $rows = $wpdb->get_results( $wpdb->prepare("SELECT notifyto,posted_data,data,time,formid FROM ".$wpdb->prefix.$this->table_messages." WHERE ".$calquery."time<=%s ORDER BY time DESC LIMIT 0,90000", $to) );
        
        // clean fields IDs
        $fields = explode(",",trim($fields));                
        for($j=0; $j<count($fields); $j++)
           $fields[$j] = strtolower(trim($fields[$j]));
       
        foreach($rows as $item)
        {        
            $data = unserialize($item->posted_data);
                
            if (!$paidonly || $data['paid'])
            {
                foreach($data["apps"] as $app)
                    if ($app["date"] >= $from && $app["date"] <= $to && ($status == '-1' || $status == $app["cancelled"]) )
                    {          
                        foreach ($data as $fielditemname => $fieldvaluedata)
                             if ($fielditemname != "apps" && !in_array($fielditemname,$fields) && $fieldvaluedata != '')
                                 $fields[] = $fielditemname;
                
                        //$selection[] = array($app["date"]." ".$app["slot"], $app["date"], $app["slot"], $data, $item->notifyto, $item->data, $app["cancelled"], $app["service"], $app["quant"]);
                        $selection[] = array($app["date"]." ".$app["slot"], $app["date"], $app["slot"], $data, $item->notifyto, $item->data, $app["cancelled"], 
                        $app["quantity1"], 
                        $app["quantity2"], 
                        @$app["quantity3"], 
                        @$app["quantity4"], 
                        @$app["quantity5"], // 11
                        $item->formid, // 12
                        $item->time  // 13
                        );
                    }    
            }
        }
        
        

        // order time-slots
        if (!function_exists('appbkfastsortfn'))
        {
            function appbkfastsortfn($a, $b) { return ($a[0] > $b[0]); }
        }
        usort($selection, "appbkfastsortfn" );        
        
        $separator =  get_option('CP_WPTS_CSV_SEPARATOR',",");
        if ($separator == '') $separator = ',';       
       
        $fields_exclude = explode(",",trim(get_option('cp_wpts_schcsvexclude',"")));
        for($j=0; $j<count($fields_exclude); $j++)
           $fields_exclude[$j] = strtolower(trim($fields_exclude[$j]));
        
        $fields = array_values(array_diff ($fields, $fields_exclude));

        // print table
        for($i=0; $i<count($selection); $i++)
        {
            for($j=0; $j<count($fields); $j++)
            {        
                if ($j>0) echo $separator;
                echo '"';             
                switch ($fields[$j]) {
                    case 'date':
                        $value = $selection[$i][1];
                        break;
                    case 'time':
                        $value = $selection[$i][2];
                        break;
                    case 'email':
                        $value = $selection[$i][4];
                        break;  
                    case 'quantity':
                        $value =  $selection[$i][7]
                             .($selection[$i][8] || $selection[$i][9] || $selection[$i][10]?'/'.$selection[$i][8]:'')
                             .($selection[$i][9] || $selection[$i][10]?'/'.$selection[$i][9]:'')
                             .($selection[$i][10]?'/'.$selection[$i][10]:'');
                        break;    
                    case 'quantity1':
                        $value =  $selection[$i][7]."&nbsp;";
                        break;
                    case 'quantity2':
                        $value =  $selection[$i][8]."&nbsp;";
                        break;
                    case 'quantity3':
                        $value =  $selection[$i][9]."&nbsp;";
                        break;
                    case 'quantity4':
                        $value =  $selection[$i][10]."&nbsp;";
                        break;   
                    case 'quantity5':
                        $value =  $selection[$i][11]."&nbsp;";
                        break;   
                    case 'cancelled':
                        if ($selection[$i][6] == '') 
                            $value = __('Approved','appointment-hour-booking');
                        else
                            $value = $selection[$i][6];
                        break;                         
                    case 'data':
                        $value = substr($selection[$i][5],strpos($selection[$i][5],"\n\n")+2);
                        break;    
                    case 'paid':
                        $value = (@$selection[$i][3]['paid']?__('Yes','appointment-hour-booking'):'');
                        break;
                    default:
                    {
                        $value = ($selection[$i][3][$fields[$j]]==''?'':$selection[$i][3][$fields[$j]])."";
                    }
                } 
                $value = str_replace('"','""', $value);
                echo trim($value);
                echo '"';                
            }             
            echo "\n"; 
        }    
        
        $buffered_contents = ob_get_contents();
        ob_end_clean();            
      
        if ($this->item)
        {
            $form = json_decode($this->cleanJSON($this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure)));
            $form = $form[0];
        }
        else
            $form = array();
            
        $filename = $this->generateSafeFileName(strtolower($this->get_option('form_name','export'))).'_'.date("m_d_y");

        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=".$filename.".csv");

        $end = count($fields);
        for ($i=0; $i<$end; $i++)
        {
            $hlabel = $this->iconv("utf-8", "ISO-8859-1//TRANSLIT//IGNORE", $this->get_form_field_label($fields[$i],$form));
            echo '"'.str_replace('"','""', $hlabel).'"'.$separator;
        }    
        echo "\n";
        echo $buffered_contents;

        exit;
    }
    

    public function setId($id)
    {
        $this->item = $id;
    }
    
   
    public function check_max_capacity($form_data)
    {
        $max = 0;
        if( !is_null( $form_data ) )
        	if( !empty( $form_data[ 0 ] ) )
        		foreach( $form_data[ 0 ] as $key => $object )
                    if ($object->ftype == 'fslots')
                    {                        
                        foreach ($object->slotsavail as $item => $value)
                            $max = max($max,intval(@$value[0]->c)); 
                        foreach ($object->slots_special as $item => $value)
                            $max = max($max,intval(@$value->slots[0]->c));    
                    }
        return $max;                
    }
    

    public function translate_json($str)
    {
        $form_data = json_decode($this->cleanJSON($str));

        $form_data[1][0]->title = __($form_data[1][0]->title,'wp-time-slots-booking-form');
        $form_data[1][0]->description = __($form_data[1][0]->description,'wp-time-slots-booking-form');

        for ($i=0; $i < count($form_data[0]); $i++)
        {
            $form_data[0][$i]->title = __($form_data[0][$i]->title,'wp-time-slots-booking-form');
            $form_data[0][$i]->userhelpTooltip = __($form_data[0][$i]->userhelpTooltip,'wp-time-slots-booking-form');
            $form_data[0][$i]->userhelp = __($form_data[0][$i]->userhelp,'wp-time-slots-booking-form');
            if ($form_data[0][$i]->ftype == 'fCommentArea')
                $form_data[0][$i]->userhelp = __($form_data[0][$i]->userhelp,'wp-time-slots-booking-form');
            else
                if ($form_data[0][$i]->ftype == 'fradio' || $form_data[0][$i]->ftype == 'fcheck' || $form_data[0][$i]->ftype == 'fradio')
                {
                    for ($j=0; $j < count($form_data[0][$i]->choices); $j++)
                        $form_data[0][$i]->choices[$j] = __($form_data[0][$i]->choices[$j],'wp-time-slots-booking-form');
                }
        }
        $str = json_encode($form_data);
        return $str;
    }


    private function get_records_csv($formid, $form_name = "")
    {
        global $wpdb;

        $saved_item = $this->item;
        $this->item = $formid;

        $last_sent_id = get_option('cp_cptslotsb_last_sent_id_'.$formid, '0');
        $events = $wpdb->get_results(
                             $wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$this->table_messages." WHERE formid=%d AND id>%d ORDER BY id ASC",$formid,$last_sent_id)
                                     );

        if ($wpdb->num_rows <= 0) // if no rows, return empty
        {
            $this->item = $saved_item;
            return '';
        }

        if ($this->item)
        {
            $form = json_decode($this->cleanJSON($this->get_option('form_structure', CP_TSLOTSBOOK_DEFAULT_form_structure)));
            $form = $form[0];
        }
        else
            $form = array();

        $buffer = '';
        if ($this->include_user_data_csv)
            $fields = array("Submission ID", "Form", "Time", "IP Address", "email");
        else
            $fields = array("Submission ID", "Form", "Time", "email");
        $values = array();
        foreach ($events as $item)
        {
            if ($this->include_user_data_csv)
                $value = array($item->id, $form_name, $item->time, $item->ipaddr, $item->notifyto);
            else
                $value = array($item->id, $form_name, $item->time, $item->notifyto);
            $last_sent_id = $item->id;
            if ($item->posted_data)
                $data = unserialize($item->posted_data);
            else
                $data = array();

            $end = count($fields);
            for ($i=0; $i<$end; $i++)
                if (isset($data[$fields[$i]]) ){
                    $value[$i] = $data[$fields[$i]];
                    unset($data[$fields[$i]]);
                }

            if (is_array($data)) foreach ($data as $k => $d)
                if ($k != 'apps' && $k != 'itemnumber')
                {
                   $fields[] = $k;
                   $value[] = $d;
                }
            $values[] = $value;
        }
        update_option('cp_cptslotsb_last_sent_id_'.$formid, $last_sent_id);

        $end = count($fields);
        for ($i=0; $i<$end; $i++)
        {
            $hlabel = $this->iconv("utf-8", "ISO-8859-1//TRANSLIT//IGNORE", $this->get_form_field_label($fields[$i],$form));
            $buffer .= '"'.str_replace('"','""', $hlabel).'",';
        }
        $buffer .= "\n";
        foreach ($values as $item)
        {
            for ($i=0; $i<$end; $i++)
            {
                if (!isset($item[$i]))
                    $item[$i] = '';
                if (is_array($item[$i]))
                    $item[$i] = implode($item[$i],',');
                $item[$i] = $this->iconv("utf-8", "ISO-8859-1//TRANSLIT//IGNORE", $item[$i]);
                $buffer .= '"'.str_replace('"','""', $item[$i]).'",';
            }
            $buffer .= "\n";
        }

        $this->item = $saved_item;
        return $buffer;

    }

    private function check_reports($skip_verification = false) {
        global $wpdb;

        $last_verified = get_option('cp_cptslotsb_last_verified','');
        if ( $skip_verification || $last_verified == '' || $last_verified < date("Y-m-d H:i:s", strtotime("-1 minutes")) )  // verification to don't check too fast to avoid overloading the site
        {
            update_option('cp_cptslotsb_last_verified',date("Y-m-d H:i:s"));

            // global reports for all forms
            if (get_option('cp_cptslotsb_rep_enable', 'no') == 'yes' && get_option('cp_cptslotsb_rep_days', '') != '' && get_option('cp_cptslotsb_rep_emails', '') != '' )
            {
                $formid = 0;
                $verify_after = date("Y-m-d H:i:s", strtotime("-".get_option('cp_cptslotsb_rep_days', '')." days"));
                $last_sent = get_option('cp_cptslotsb_last_sent'.$formid, '');
                if ($last_sent == '' || $last_sent < $verify_after)  // check if this form needs to check for a new report
                {
                    update_option('cp_cptslotsb_last_sent'.$formid, date("Y-m-d ".(get_option('cp_cptslotsb_rep_hour', '')<'10'?'0':'').get_option('cp_cptslotsb_rep_hour', '').":00:00"));
                    $text = '';
                    $forms = $wpdb->get_results("SELECT id,fp_from_email,form_name,rep_days,rep_hour,rep_emails,rep_subject,rep_emailformat,rep_message,rep_enable FROM ".$wpdb->prefix.$this->table_items); // " WHERE rep_emails<>'' AND rep_enable='yes'"
                    $attachments = array();
                    foreach ($forms as $form)  // for each form with the reports enabled
                    {
                        $csv = $this->get_records_csv($form->id, $form->form_name);
                        if ($csv != '')
                        {
                            $text = "- ".substr_count($csv,",\n\"").' submissions from '.$form->form_name."\n";
                            $filename = $this->generateSafeFileName(strtolower($form->form_name)).'_'.date("m_d_y");
                            $filename = WP_CONTENT_DIR . '/uploads/'.$filename .'.csv';
                            $handle = fopen($filename, 'w');
                            fwrite($handle,$csv);
                            fclose($handle);
                            $attachments[] = $filename;
                        }
                    }
                    if ('html' == get_option('cp_cptslotsb_rep_emailformat','')) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
                    if (count($attachments))
                        wp_mail( str_replace(" ","",str_replace(";",",",get_option('cp_cptslotsb_rep_emails',''))), get_option('cp_cptslotsb_rep_subject',''), get_option('cp_cptslotsb_rep_message','')."\n".$text,
                                    "From: \"".get_option('cp_cptslotsb_fp_from_email','')."\" <".get_option('cp_cptslotsb_fp_from_email','').">\r\n".
                                    $content_type.
                                    "X-Mailer: PHP/" . phpversion(),
                                    @$attachments);
                }
            }

            // reports for specific forms
            $forms = $wpdb->get_results("SELECT id,form_name,fp_from_email,rep_days,rep_hour,rep_emails,rep_subject,rep_emailformat,rep_message,rep_enable FROM ".$wpdb->prefix.$this->table_items." WHERE rep_emails<>'' AND rep_enable='yes'");
            foreach ($forms as $form)  // for each form with the reports enabled
            {
                $formid = $form->id;
                $verify_after = date("Y-m-d H:i:s", strtotime("-".$form->rep_days." days"));
                $last_sent = get_option('cp_cptslotsb_last_sent'.$formid, '');
                if ($skip_verification || $last_sent == '' || $last_sent < $verify_after)  // check if this form needs to check for a new report
                {
                    update_option('cp_cptslotsb_last_sent'.$formid, date("Y-m-d ".($form->rep_hour<'10'?'0':'').$form->rep_hour.":00:00"));
                    $csv = $this->get_records_csv($formid, $form->form_name);
                    if ($csv != '')
                    {
                        $filename = $this->generateSafeFileName(strtolower($form->form_name)).'_'.date("m_d_y");
                        $filename = WP_CONTENT_DIR . '/uploads/'.$filename .'.csv';
                        $handle = fopen($filename, 'w');
                        fwrite($handle,$csv);
                        fclose($handle);
                        $attachments = array( $filename );
                        if ('html' == $form->rep_emailformat) $content_type = "Content-Type: text/html; charset=utf-8\n"; else $content_type = "Content-Type: text/plain; charset=utf-8\n";
                        wp_mail( str_replace(" ","",str_replace(";",",",$form->rep_emails)), $form->rep_subject, $form->rep_message,
                                "From: \"".$form->fp_from_email."\" <".$form->fp_from_email.">\r\n".
                                $content_type.
                                "X-Mailer: PHP/" . phpversion(),
                                $attachments);
                    }
                }
            } // end foreach
        } // end if
    }  // end check_reports function


    protected function iconv($from, $to, $text)
    {
        $text = trim($text);
        if ( strlen($text) > 1 && (in_array(substr($text,0,1), array('=','@','+'))) )
        {
                $text = chr(9).$text;
        }        
        if (function_exists('iconv'))
            return iconv($from, $to, $text);
        else
            return $text;
    }

} // end class

// WIDGET CODE BELOW
require_once dirname( __FILE__ ).'/cp-widget.inc.php';


// Auxiliar functions
// ******************************************************************

// Auxiliar functions
// ***********************************************************************

function cptslotsb_cleanJSON ($str)
{
    $str = str_replace('&qquot;','"',$str);
    $str = str_replace('	',' ',$str);
    $str = str_replace("\n",'\n',$str);
    $str = str_replace("\r",'',$str);
    return $str;
}

function cptslotsb_form_result( $atts, $content = "", $id = 0 )
	{

		global $wpdb;
		if( $id == 0 && !empty( $_SESSION[ 'cp_cff_form_data' ] ) ) $id = $_SESSION[ 'cp_cff_form_data' ];
		if( !empty( $id ) )
		{
			$content = html_entity_decode( $content );
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT form_settings.form_structure AS form_structure, form_data.data AS data, form_data.data AS data, form_data.ipaddr as ipaddr FROM ".$wpdb->prefix."cptslotsbk_forms"." AS form_settings,".$wpdb->prefix."cptslotsbk_messages"." AS form_data WHERE form_data.id=%d AND form_data.formid=form_settings.id", $id ) );

			if( !is_null( $result ) )
			{
				$atts = shortcode_atts( array( 'fields' => '' ), $atts );
				if( !empty( $atts[ 'fields' ] ) || !empty( $content ) )
				{
					$raw_form_str = cptslotsb_cleanJSON( $result->form_structure );
					$form_data = json_decode( $raw_form_str );

					if( is_null( $form_data ) )
					{
						$json = new JSON;
						$form_data = $json->unserialize( $raw_form_str );
					}
				}

				if( empty( $form_data ) )
				{
					return "<p>" . preg_replace( "/\n+/", "<br />", $result->data ) . "</p>";
				}
				else
				{
					$fields = array();
					foreach($form_data[0] as $item)
					{
						$fields[$item->name] = $item;
					}
					$fields[ 'ipaddr' ] = $result->ipaddr;
					$result->data = unserialize( $result->data );
					$str = '';
					$atts[ 'fields' ] = explode( ",", str_replace( " ", "", $atts[ 'fields' ] ) );
					foreach( $atts[ 'fields' ] as $field )
					{
                        if( isset( $fields[ $field ] ) )
                        {
                            if( isset( $result->data[ $field ] ) )
                            {
                                if( is_array( $result->data[ $field ] ) ) $result->data[ $field ] = implode( ',', $result->data[ $field ] );
                                $str .= "<p>{$fields[ $field ]->title} {$result->data[ $field ]}</p>";
                            }
                            elseif( in_array( $fields[ $field ]->ftype, array( 'fSectionBreak' ) ) )
                            {
                                $str .= "<p><strong>".$fields[ $field ]->title."</strong>".(( !empty($fields[ $field ]->userhelp) ) ? "<br /><pan class='uh'>".$fields[ $field ]->userhelp."</span>" : '' )."</p>";
                            }
						}

					}

                    if( $content != '' )
                    {
	                    $replaced_values = _cptslotsb_replace_vars( $fields, $result->data, $content, $result->data, 'html', $id );
	                    $str .= $replaced_values[ 'message' ];
                    }

					return $str;
				}
			}
		}

		return '';
	}


function _cptslotsb_extract_tags( $message )
{
	$tags_arr = array();
	if(
		preg_match_all(	"/<%(info|fieldname\d+|fieldname\d+_label|fieldname\d+_shortlabel|fieldname\d+_value|fieldname\d+_url|fieldname\d+_urls|coupon|itemnumber|final_price|payment_option|ipaddress|currentdate_mmddyyyy|currentdate_ddmmyyyy)\b(?:(?!%>).)*%>/i",
			$message,
			$matches
		)
	)
	{
		$tag = array();
		foreach( $matches[ 0 ] as $index => $value )
		{
			$tag[ 'node' ] = $value;
			$tag[ 'tag' ]  = strtolower( $matches[ 1 ][ $index ] );
			$tag[ 'if_not_empty' ] 	= preg_match( "/if_not_empty/i", $value );
			$tag[ 'before' ]    	= ( preg_match( "/before\s*=\s*\{\{((?:(?!\}\}).)*)\}\}/i",  $value, $match ) ) ? $match[ 1 ] : '';
			$tag[ 'after' ]   		= ( preg_match( "/after\s*=\s*\{\{((?:(?!\}\}).)*)\}\}/i", $value, $match ) ) ? $match[ 1 ] : '';
			$tag[ 'separator' ]    	= ( preg_match( "/separator\s*=\s*\{\{((?:(?!\}\}).)*)\}\}/i",  $value, $match ) ) ? $match[ 1 ] : '';

			$baseTag = ( preg_match( "/(fieldname\d+)_(label|value|shortlabel)/i", $tag[ 'tag' ], $match ) ) ? $match[ 1 ] : $tag[ 'tag' ];

			if( empty( $tags_arr[ $baseTag ] ) ) $tags_arr[ $baseTag ] = array();
			$tags_arr[ $baseTag ][] = $tag;
		}
	}
	return $tags_arr;
}

function _cptslotsb_replace_vars( $fields, $params, $message, $buffer = '', $contentType = 'html', $itemnumber = '' )
{
	// Lambda functions
    $arrayReplacementFunction = function ( &$tags, $tagName, $replacement, &$message ) { if(isset($tags[ $tagName ])){foreach( $tags[ $tagName ] as $tagData ){ $message = str_replace( $tagData[ "node" ], $tagData[ "before" ].$replacement.$tagData[ "after" ], $message );}unset( $tags[ $tagName ] );} };
	
	$singleReplacementFunction = function ( $tagData, $value, &$message ) { $message = str_replace( $tagData[ "node" ], $tagData[ "before" ].$value.$tagData[ "after" ],$message ); };

	$message = str_replace( '< %', '<%', $message );
    $attachments = array();

	// Remove empty blocks
	while( preg_match( "/<%\s*fieldname(\d+)_block\s*%>/", $message, $matches ) )
	{
		if( empty( $params[ 'fieldname'.$matches[ 1 ] ] ) )
		{
			$from = strpos( $message, $matches[ 0 ] );
			if( preg_match( "/<%\s*fieldname(".$matches[ 1 ].")_endblock\s*%>/", $message, $matches_end ) )
			{
				$lenght = strpos( $message, $matches_end[ 0 ] ) + strlen( $matches_end[ 0 ] ) - $from;
			}
			else
			{
				$lenght = strlen( $matches[ 0 ] );
			}
			$message = substr_replace( $message, '', $from, $lenght );
		}
		else
		{
			$message = preg_replace( array( "/<%\s*fieldname".$matches[ 1 ]."_block\s*%>/", "/<%\s*fieldname".$matches[ 1 ]."_endblock\s*%>/"), "", $message );
		}
	}

	$tags = _cptslotsb_extract_tags( $message );

	if ( 'html' == $contentType )
    {
        $message = str_replace( "\n", "", $message );
        $buffer = str_replace( array('&lt;', '&gt;', '\"', "\'"), array('<', '>', '"', "'" ), $buffer );
    }

	// Replace the INFO tags
    if( !empty( $tags[ 'info' ] ) )
	{
		$buffer1 = $buffer;
		do{
			$tmp = $buffer1;
			$buffer1 = preg_replace(
				array(
					"/^[^\n:]*:{1,2}\s*\n/",
					"/\n[^\n:]*:{1,2}\s*\n/",
					"/\n[^\n:]*:{1,2}\s*$/"
				),
				array(
					"",
					"\n",
					""
				),
				$buffer1
			);
		}while( $buffer1 <> $tmp );

		foreach( $tags[ 'info' ] as $tagData )
		{
			$singleReplacementFunction( $tagData, ( ( $tagData[ 'if_not_empty' ] ) ? $buffer1 : $buffer ), $message );
		}
		unset( $tags[ 'info' ] );
	}

	foreach ($params as $item => $value)
    {
		$value_bk = $value;
		if( isset( $tags[ $item ] ) )
		{
			$label 		= ( isset( $fields[ $item ] ) && property_exists( $fields[ $item ], 'title' ) ) ? $fields[ $item ]->title : '';
			$shortlabel = ( isset( $fields[ $item ] ) && property_exists( $fields[ $item ], 'shortlabel' ) ) ? $fields[ $item ]->shortlabel : '';
			$value = ( !empty( $value ) || is_numeric( $value ) && $value == 0 ) ? ( ( is_array( $value ) ) ? implode( ", ", $value ) : $value ) : '';

			if ( 'html' == $contentType )
			{
				$label = str_replace( array('&lt;', '&gt;', '\"', "\'"), array('<', '>', '"', "'" ), $label );
				$shortlabel = str_replace( array('&lt;', '&gt;', '\"', "\'"), array('<', '>', '"', "'" ), $shortlabel );
				$value = str_replace( array('&lt;', '&gt;', '\"', "\'"), array('<', '>', '"', "'" ), $value );
			}

			foreach( $tags[ $item ] as $tagData )
			{
				if( $tagData[ 'if_not_empty' ] == 0 || $value !== '' )
				{
					switch( $tagData[ 'tag' ] )
					{
						case $item:
							$singleReplacementFunction( $tagData, $label.$tagData[ 'separator' ].$value, $message );
						break;
						case $item.'_label':
							$singleReplacementFunction( $tagData, $label, $message );
						break;
						case $item.'_value':
							$singleReplacementFunction( $tagData, $value, $message );
						break;
						case $item.'_shortlabel':
							$singleReplacementFunction( $tagData, $shortlabel, $message );
						break;
					}
				}
				else
				{
					$message = str_replace( $tagData[ 'node' ], '', $message );
				}
			}
			unset( $tags[ $item ] );
		}

        if( preg_match( "/_link\b/i", $item ) )
        {
            $attachments = array_merge( $attachments, $value_bk );
        }
    }

	$arrayReplacementFunction( $tags, 'itemnumber', $itemnumber, $message );
	$arrayReplacementFunction( $tags, 'currentdate_mmddyyyy', date("m/d/Y H:i:s"), $message );
	$arrayReplacementFunction( $tags, 'currentdate_ddmmyyyy', date("d/m/Y H:i:s"), $message );
	$arrayReplacementFunction( $tags, 'ipaddress', $fields[ 'ipaddr' ], $message );

    // Replace coupons code
	if( isset( $_REQUEST[ 'couponcode' ] ) && isset( $tags[ 'couponcode' ] ) )
    {
		$arrayReplacementFunction( $tags, 'couponcode', $_REQUEST[ 'couponcode' ], $message );
    }

	foreach( $tags as $tagArr )
    {
        foreach( $tagArr as $tagData )
		{
			$message = str_replace( $tagData[ 'node' ], '', $message );
		}
	}

    if ( 'html' == $contentType )
    {
        $message = str_replace( "\n", "<br>", $message );
    }
    $message = str_replace( '\\', '', stripslashes( stripcslashes( $message ) ) );

	return array( 'message' => $message, 'attachments' => $attachments );
}



?>