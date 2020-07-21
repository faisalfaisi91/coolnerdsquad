<?php

	class WS_Form_Data_Source_Term extends WS_Form_Data_Source {

		public $id = 'term';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 0;

		public function __construct() {

			// Set label
			$this->label = __('Term', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Terms...', 'ws-form');

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

			// Build taxonomies
			$taxonomies = array();
			foreach($this->data_source_term_filter_taxonomies as $filter_taxonomy) {

				if(
					!isset($filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'}) ||
					empty($filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'})

				) { continue; }

				$taxonomies[] = $filter_taxonomy->{'data_source_' . $this->id . '_taxonomies'};
			}

			// If no taxonomies are specified, set taxonomies to default list
			if(count($taxonomies) == 0) {

				$taxonomies_array = get_taxonomies(array(), 'objects');

				// Sort taxonomies
				usort($taxonomies_array, function ($taxonomy_1, $taxonomy_2) {

					return $taxonomy_1->labels->singular_name < $taxonomy_2->labels->singular_name ? -1 : 1;
				});

				foreach($taxonomies_array as $taxonomy) {

					$taxonomies[] = $taxonomy->name;
				}
			}

			// Groups
			$data_source_term_groups = ($this->data_source_term_groups == 'on');

			// Hide empty terms
			$data_source_term_terms_hide_empty = ($this->data_source_term_terms_hide_empty == 'on');

			// Check order
			if(!in_array($this->data_source_term_order, array(

				'ASC',
				'DESC'

			))) { return self::error(__('Invalid order method', 'ws-form'), $field_id, $this, $api_request); }

			// Check order by
			if(!in_array($this->data_source_term_order_by, array(

				'none',
				'term_id',
				'name',
				'slug',
				'menu_order',

			))) { return self::error(__('Invalid order by method'), $field_id, $this, $api_request); }

			// Columns
			$meta_value['columns'] = array(

				array('id' => 0, 'label' =>'ID'),
				array('id' => 1, 'label' =>'Name'),
				array('id' => 2, 'label' =>'Slug')
			);

			// Base meta
			$group = $meta_value['groups'][0];
			$max_num_pages = 0;

			// Form parse?
			if($no_paging) { $this->records_per_page = 0; }

			// Run through taxonomies
			$group_index = 0;
			$row_index = 0;
			foreach(($data_source_term_groups ? $taxonomies : array(false)) as $taxonomy) {

				// Calculate offset
				if($no_paging === false) {

					// API request
					$offset = (($page - 1) * $this->records_per_page);

				} else {

					// Form parse
					$offset = 0;
				}
				// get_terms args
				$args = array(

					'taxonomy' => ($this->data_source_term_groups == 'on') ? $taxonomy : $taxonomies,
					'number' => $this->records_per_page,
					'offset' => $offset,
					'fields' => 'ids',
					'order' => $this->data_source_term_order,
					'orderby' => $this->data_source_term_order_by,
					'hide_empty' => $data_source_term_terms_hide_empty
				);

				// get_terms
				$wp_query = new WP_Term_Query($args);

				// max_num_pages
//				if($wp_query->max_num_pages > $max_num_pages) { $max_num_pages = $wp_query->max_num_pages; }

				$term_ids = !empty($wp_query->terms) ? $wp_query->terms : array();

				// Skip if no records
				if(count($term_ids) === 0) { continue; }

				// Rows
				$rows = array();
				foreach($term_ids as $term_index => $term_id) {

					$term = get_term($term_id);

					$rows[] = array(

						'id'		=> $offset + $row_index++,
						'default'	=> '',
						'required'	=> '',
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> array(

							$term_id,
							$term->name,
							$term->slug
						)
					);
				}

				// Build new group if one does not exist
				if(!isset($meta_value['groups'][$group_index])) {

					$meta_value['groups'][$group_index] = $group;
				}

				// Term label
				if($data_source_term_groups) {

					$taxonomy_object = get_taxonomy($taxonomy);
					$meta_value['groups'][$group_index]['label'] = $taxonomy_object->labels->singular_name;

				} else {

					$meta_value['groups'][$group_index]['label'] = $this->label;
				}

				// Rows
				$meta_value['groups'][$group_index]['rows'] = $rows;

				// Enable optgroups
				if(count($taxonomies) > 1) {

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

				'data_source_' . $this->id . '_filter_taxonomies',
				'data_source_' . $this->id . '_order_by',
				'data_source_' . $this->id . '_order',
				'data_source_' . $this->id . '_groups',
				'data_source_' . $this->id . '_terms_hide_empty',
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

				// Filter - Taxonomy
				'data_source_' . $this->id . '_filter_taxonomies' => array(

					'label'						=>	__('Filter by Taxonomy', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which taxonomies to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_taxonomies'
					)
				),

				// Taxonomies
				'data_source_' . $this->id . '_taxonomies' => array(

					'label'						=>	__('Taxonomy', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// Order By
				'data_source_' . $this->id . '_order_by' => array(

					'label'						=>	__('Order By', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'name',
					'options'					=>	array(

						array('value' => 'none', 'text' => 'None'),
						array('value' => 'term_id', 'text' => 'ID'),
						array('value' => 'name', 'text' => 'Name'),
						array('value' => 'slug', 'text' => 'Slug'),
						array('value' => 'menu_order', 'text' => 'Menu Order')
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

					'label'						=>	__('Group by Taxonomy', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'show_if_groups_group'		=>	true
				),

				// Terms - Hide Empty
				'data_source_' . $this->id . '_terms_hide_empty' => array(

					'label'						=>	__('Hide Empty Terms', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Whether to hide terms not assigned to any posts.', 'ws-form')
				)
			);

			// Add taxonomies
			$taxonomies = get_taxonomies(array(), 'objects');

			// Sort taxonomies
			usort($taxonomies, function ($taxonomy_1, $taxonomy_2) {

				return $taxonomy_1->labels->singular_name < $taxonomy_2->labels->singular_name ? -1 : 1;
			});

			foreach($taxonomies as $taxonomy) {

				if($taxonomy->_builtin && !$taxonomy->public) continue;

				$text = $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')';

				$config_meta_keys['data_source_' . $this->id . '_taxonomies']['options'][] = array('value' => $taxonomy->name, 'text' => $text);
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

	new WS_Form_Data_Source_Term();
