<?php

	class WS_Form_Data_Source_ACF extends WS_Form_Data_Source {

		public $id = 'acf';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('ACF', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving ACF field options...', 'ws-form');

			// Register action
			parent::register($this);

			// Register config filters
			add_filter('wsf_config_meta_keys', array($this, 'config_meta_keys'), 10, 2);

			// Register API endpoint
			add_action('rest_api_init', array($this, 'rest_api_init'), 10, 0);

			// Records per page
			$this->records_per_page = apply_filters('wsf_data_source_' . $this->id . '_records_per_age', $this->records_per_page);
		}

		// Get
		public function get($field_id, $page, $meta_key, $meta_value, $no_paging = false, $api_request = false) {

			// Check meta key
			if(empty($meta_key)) { return self::error(__('No meta key specified', 'ws-form'), $field_id, $this, $api_request); }

			// Get meta key config
			$meta_keys = WS_Form_Config::get_meta_keys();
			if(!isset($meta_keys[$meta_key])) { return self::error(__('Unknown meta key', 'ws-form'), $field_id, $this, $api_request); }
			$meta_key_config = $meta_keys[$meta_key];

			// Check meta value
			if(
				!is_array($meta_value) ||
				!isset($meta_value['columns']) ||
				!isset($meta_value['groups']) ||
				!isset($meta_value['groups'][0])
			) {

				if(!isset($meta_key_config['default'])) { return self::error(__('No default value', 'ws-form'), $field_id, $this, $api_request); }

				// If meta_value is invalid, create one from default
				$meta_value = $meta_key_config['default'];
			}

			// Columns
			$meta_value['columns'] = array(

				array('id' => 0, 'label' =>'Value'),
				array('id' => 1, 'label' =>'Label')
			);

			// Get ACF field object
			$acf_field_object = get_field_object($this->{'data_source_' . $this->id . '_field_key'});

			// Get ACF field label
			$label = isset($acf_field_object['label']) ? $acf_field_object['label'] : $this->label;

			// Get ACF field choices
			$choices = isset($acf_field_object['choices']) ? $acf_field_object['choices'] : array();

			// Run through roles
			$rows = array();
			$row_index = 0;
			foreach($choices as $value => $text) {

				$rows[] = array(

					'id'		=> $row_index++,
					'default'	=> '',
					'required'	=> '',
					'disabled'	=> '',
					'hidden'	=> '',
					'data'		=> array(

						$value,
						$text
					)
				);
			}

			// Build new group if one does not exist
			if(!isset($meta_value['groups'][0])) {

				$meta_value['groups'][0] = $group;
			}

			$meta_value['groups'][0]['label'] = $label;

			// Rows
			$meta_value['groups'][0]['rows'] = $rows;

			// Delete any old groups
			$group_index = 1;
			while(isset($meta_value['groups'][$group_index])) {

				unset($meta_value['groups'][$group_index++]);
			}

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => 0, 'meta_keys' => array());
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_field_key'
			);
		}

		// Get settings
		public function get_data_source_settings() {

			// Build settings
			$settings = array(

				'meta_keys' => self::get_data_source_meta_keys()
			);

			// Add retrieve button
			$settings['meta_keys'][] = 'data_source_' . $this->id . '_get';

			// Wrap settings so they will work with sidebar_html function in admin.js
			$settings = parent::get_settings_wrapper($settings);

			// Add label
			$settings->label = $this->label;

			// Add label retrieving
			$settings->label_retrieving = $this->label_retrieving;

			// Add API GET endpoint
			$settings->endpoint_get = 'data-source/' . $this->id . '/';

			// Apply filter
			$settings = apply_filters('wsf_data_source_' . $this->id . '_settings', $settings);

			return $settings;
		}

		// ACF - Get fields
		public function acf_get_fields(&$options_acf, $acf_field_group_name, $acf_fields, $prefix = '') {

			foreach($acf_fields as $acf_field) {

				// Check for sub fields
				if(isset($acf_field['sub_fields'])) {

					$acf_fields = $acf_field['sub_fields'];

					self::acf_get_fields($options_acf, $acf_field_group_name, $acf_fields, $prefix . ' - ' . $acf_field['label']);

				} else {

					// Only return fields that have choices
					if(
						isset($acf_field['choices']) &&
						(count($acf_field['choices']) > 0)
					) {

						$options_acf[] = array('value' => $acf_field['key'], 'text' => sprintf('%s%s - %s', $acf_field_group_name, $prefix, $acf_field['label']));
					}
				}
			}
		}

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// ACF
			$options_acf = array();

			$acf_field_groups = acf_get_field_groups();

			foreach($acf_field_groups as $acf_field_group) {

				$acf_fields = acf_get_fields($acf_field_group);
				
				$acf_field_group_name = $acf_field_group['title'];

				self::acf_get_fields($options_acf, $acf_field_group_name, $acf_fields);
			}

			// Build config_meta_keys
			$config_meta_keys = array(

				// ACF Field
				'data_source_' . $this->id . '_field_key' => array(

					'label'						=>	__('ACF Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	$options_acf,
					'options_blank'				=>	__('Select...', 'ws-form')
				),


				// Get Data
				'data_source_' . $this->id . '_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'data_source_' . $this->id . '_field_key',
							'meta_value'		=>	''
						)
					),
					'key'						=>	'data_source_get'
				)
			);

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			// Get data source
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/data-source/' . $this->id . '/', array('methods' => 'POST', 'callback' => array($this, 'api_post')));
		}

		// api_post
		public function api_post() {

			// Get meta keys
			$meta_keys = self::get_data_source_meta_keys();

			// Read settings
			foreach($meta_keys as $meta_key) {

				$this->{$meta_key} = WS_Form_Common::get_query_var($meta_key, false);
				if(
					is_object($this->{$meta_key}) ||
					is_array($this->{$meta_key})
				) {

					$this->{$meta_key} = json_decode(json_encode($this->{$meta_key}));
				}
			}

			// Get field ID
			$field_id = WS_Form_Common::get_query_var('field_id', 0);

			// Get page
			$page = intval(WS_Form_Common::get_query_var('page', 1));

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var('meta_key', 0);

			// Get meta value
			$meta_value = WS_Form_Common::get_query_var('meta_value', 0);

			// Get return data
			$get_return = self::get($field_id, $page, $meta_key, $meta_value, false, true);

			// Error checking
			if($get_return['error']) {

				// Error
				return self::api_error($get_return);

			} else {

				// Success
				return $get_return;
			}
		}
	}

	new WS_Form_Data_Source_ACF();
