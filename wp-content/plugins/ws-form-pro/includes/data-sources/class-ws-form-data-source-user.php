<?php

	class WS_Form_Data_Source_User extends WS_Form_Data_Source {

		public $id = 'user';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('User', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Users...', 'ws-form');

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

			global $wp_roles;

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

			// Build roles
			$roles = array();
			foreach($this->data_source_user_filter_roles as $filter_taxonomy) {

				if(
					!isset($filter_taxonomy->{'data_source_' . $this->id . '_roles'}) ||
					empty($filter_taxonomy->{'data_source_' . $this->id . '_roles'})

				) { continue; }

				$roles[] = $filter_taxonomy->{'data_source_' . $this->id . '_roles'};
			}
			if(count($roles) == 0) {

				$roles = array_keys($wp_roles->roles);
			}

			// Groups
			$data_source_user_groups = ($this->data_source_user_groups == 'on');

			// Check order
			if(!in_array($this->data_source_user_order, array(

				'ASC',
				'DESC'

			))) { return self::error(__('Invalid order method', 'ws-form'), $field_id, $this, $api_request); }

			// Check order by
			if(!in_array($this->data_source_user_order_by, array(

				'ID',
				'display_name',
				'user_name',
				'login',
				'nicename',
				'email',
				'url',
				'post_count'

			))) { return self::error(__('Invalid order by method'), $field_id, $this, $api_request); }

			// Columns
			$meta_value['columns'] = array(

				array('id' => 0, 'label' =>'ID'),
				array('id' => 1, 'label' =>'Display Name'),
				array('id' => 2, 'label' =>'Nicename'),
				array('id' => 3, 'label' =>'Login'),
				array('id' => 4, 'label' =>'Email'),
			);

			// Base meta
			$group = $meta_value['groups'][0];
			$max_num_pages = 0;

			// Form parse?
			if($no_paging) { $this->records_per_page = 0; }

			// Run through roles
			$group_index = 0;
			$row_index = 0;
			foreach(($data_source_user_groups ? $roles : array(false)) as $role) {

				// Calculate offset
				if($no_paging === false) {

					// API request
					$offset = (($page - 1) * $this->records_per_page);

				} else {

					// Form parse
					$offset = 0;
				}
				// get_users args
				$args = array(

					'role__in' => ($data_source_user_groups == 'on') ? $role : $roles,
					'number' => $this->records_per_page,
					'offset' => $offset,
					'fields' => 'ids',
					'order' => $this->data_source_user_order,
					'orderby' => $this->data_source_user_order_by
				);

				// get_users
				$wp_query = new WP_User_Query($args);

					// max_num_pages
//				if($wp_query->max_num_pages > $max_num_pages) { $max_num_pages = $wp_query->max_num_pages; }

				$user_ids = $wp_query->get_results();

				// Skip if no records
				if(count($user_ids) === 0) { continue; }

				// Rows
				$rows = array();
				foreach($user_ids as $user_index => $user_id) {

					$user = get_user_by('ID', $user_id);

					$rows[] = array(

						'id'		=> $offset + $row_index++,
						'default'	=> '',
						'required'	=> '',
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> array(

							$user_id,
							$user->display_name,
							$user->user_nicename,
							$user->user_login,
							$user->user_email,
						)
					);
				}

				// Build new group if one does not exist
				if(!isset($meta_value['groups'][$group_index])) {

					$meta_value['groups'][$group_index] = $group;
				}

				// User label
				if($data_source_user_groups) {

					$roles_for_group_label = $wp_roles->roles;
					$meta_value['groups'][$group_index]['label'] = (isset($roles_for_group_label[$role]) ? $roles_for_group_label[$role]['name'] : __('Unknown', 'ws-form'));

				} else {

					$meta_value['groups'][$group_index]['label'] = $this->label;
				}

				// Rows
				$meta_value['groups'][$group_index]['rows'] = $rows;

				// Enable optgroups
				if(count($roles) > 1) {

					$meta_value['groups'][$group_index]['mask_group'] = 'on';
					$meta_value['groups'][$group_index]['label_render'] = 'on';
				}

				$group_index++;
			}

			// Delete any old groups
			while(isset($meta_value['groups'][$group_index])) {

				unset($meta_value['groups'][$group_index++]);
			}

			// Return data
			return array('error' => false, 'error_message' => '', 'meta_value' => $meta_value, 'max_num_pages' => $max_num_pages, 'meta_keys' => array());
		}

		// Get meta keys
		public function get_data_source_meta_keys() {

			return array(

				'data_source_' . $this->id . '_filter_roles',
				'data_source_' . $this->id . '_order_by',
				'data_source_' . $this->id . '_order',
				'data_source_' . $this->id . '_groups'
//				'data_source_recurrence'
			);
		}

		// Get settings
		public function get_data_source_settings() {

			// Build settings
			$settings = array(

				'meta_keys' => self::get_data_source_meta_keys()
			);

			// Add retrieve button
			$settings['meta_keys'][] = 'data_source_get';

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

		// Meta keys for this action
		public function config_meta_keys($meta_keys = array(), $form_id = 0) {

			// Build config_meta_keys
			$config_meta_keys = array(

				// Filter - Role
				'data_source_' . $this->id . '_filter_roles' => array(

					'label'						=>	__('Filter by Role', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which roles to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_roles'
					)
				),

				// Taxonomies
				'data_source_' . $this->id . '_roles' => array(

					'label'						=>	__('Role', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// Order By
				'data_source_' . $this->id . '_order_by' => array(

					'label'						=>	__('Order By', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'display_name',
					'options'					=>	array(

						array('value' => 'ID', 'text' => 'ID'),
						array('value' => 'display_name', 'text' => 'Display Name'),
						array('value' => 'user_name', 'text' => 'User Name'),
						array('value' => 'login', 'text' => 'Login'),
						array('value' => 'nicename', 'text' => 'Nicename'),
						array('value' => 'email', 'text' => 'Email'),
						array('value' => 'post_count', 'text' => 'Post Count'),
					)
				),

				// Order
				'data_source_' . $this->id . '_order' => array(

					'label'						=>	__('Order', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'ASC',
					'options'					=>	array(

						array('value' => 'ASC', 'text' => 'Ascending'),
						array('value' => 'DESC', 'text' => 'Descending')
					)
				),

				// Groups
				'data_source_' . $this->id . '_groups' => array(

					'label'						=>	__('Group by Role', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'show_if_groups_group'		=>	true
				)
			);

			// Add roles
			global $wp_roles;
			$roles = $wp_roles->roles;

			// Sort roles
			uasort($roles, function ($role_1, $role_2) {

				return $role_1['name'] < $role_2['name'] ? -1 : 1;
			});

			foreach($roles as $role_id => $role) {

				$config_meta_keys['data_source_' . $this->id . '_roles']['options'][] = array('value' => $role_id, 'text' => $role['name']);
			}

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

	new WS_Form_Data_Source_User();
