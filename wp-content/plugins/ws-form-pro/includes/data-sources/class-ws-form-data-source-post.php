<?php

	class WS_Form_Data_Source_Post extends WS_Form_Data_Source {

		public $id = 'post';
		public $pro_required = false;
		public $label;
		public $label_retrieving;
		public $records_per_page = 1000;

		public function __construct() {

			// Set label
			$this->label = __('Post', 'ws-form');

			// Set label retrieving
			$this->label_retrieving = __('Retrieving Posts...', 'ws-form');

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

			// Build post types
			$post_types = array();
			foreach($this->data_source_post_filter_post_types as $filter_post_type) {

				if(
					!isset($filter_post_type->{'data_source_' . $this->id . '_post_types'}) ||
					empty($filter_post_type->{'data_source_' . $this->id . '_post_types'})

				) { continue; }

				$post_types[] = $filter_post_type->{'data_source_' . $this->id . '_post_types'};
			}

			// If no post types are specified, set post types to default list
			if(count($post_types) == 0) {

				$post_types_exclude = array('attachment');
				$post_types_array = get_post_types(array('show_in_menu' => true), 'objects', 'or');

				// Sort post types
				usort($post_types_array, function ($post_type_1, $post_type_2) {

					return $post_type_1->labels->singular_name < $post_type_2->labels->singular_name ? -1 : 1;
				});

				foreach($post_types_array as $post_type) {

					$post_type_name = $post_type->name;

					if(in_array($post_type_name, $post_types_exclude)) { continue; }

					$post_types[] = $post_type->name;
				}
			}

			// Post statuses
			$post_status = array();
			if(is_array($this->data_source_post_filter_post_statuses) && (count($this->data_source_post_filter_post_statuses) > 0)) {

				$post_statuses_valid = get_post_stati(array('internal' => false));

				foreach($this->data_source_post_filter_post_statuses as $filter_post_status) {

					if(
						!isset($filter_post_status->{'data_source_' . $this->id . '_post_statuses'}) ||
						empty($filter_post_status->{'data_source_' . $this->id . '_post_statuses'})

					) { continue; }

					$post_status_single = $filter_post_status->{'data_source_' . $this->id . '_post_statuses'};

					if(!in_array($post_status_single, $post_statuses_valid)) { continue; }

					$post_status[] = $post_status_single;
				}
			}

			// Terms
			$tax_query = array();
			if(is_array($this->data_source_post_filter_terms) && (count($this->data_source_post_filter_terms) > 0)) {

				foreach($this->data_source_post_filter_terms as $filter_term) {

					if(
						!isset($filter_term->{'data_source_' . $this->id . '_terms'}) ||
						empty($filter_term->{'data_source_' . $this->id . '_terms'})

					) { continue; }

					$term_id = intval($filter_term->{'data_source_' . $this->id . '_terms'});

					$term = get_term($term_id);

					if($term === false) { continue; }

					$tax_query[] = array('taxonomy' => $term->taxonomy, 'terms' => $term_id);

					// Add relation?
					if(
						(count($tax_query) == 2) &&
						(in_array($this->data_source_post_filter_terms_relation, array('AND', 'OR')))
					) {

						$tax_query['relation'] = $this->data_source_post_filter_terms_relation;
					}
				}
			}

			// Groups
			$data_source_post_groups = ($this->data_source_post_groups == 'on');

			// Check order
			if(!in_array($this->data_source_post_order, array(

				'ASC',
				'DESC'

			))) { return self::error(__('Invalid order method', 'ws-form'), $field_id, $this, $api_request); }

			// Check order by
			if(!in_array($this->data_source_post_order_by, array(

				'none',
				'id',
				'author',
				'title',
				'name',
				'date',
				'modified',
				'rand',
				'comment_count',
				'menu_order',

			))) { return self::error(__('Invalid order by method'), $field_id, $this, $api_request); }

			// Columns
			$meta_value['columns'] = array(

				array('id' => 0, 'label' =>'ID'),
				array('id' => 1, 'label' =>'Title'),
				array('id' => 2, 'label' =>'Status'),
				array('id' => 3, 'label' =>'Slug'),
				array('id' => 4, 'label' =>'Date')
			);

			// Base meta
			$group = $meta_value['groups'][0];
			$max_num_pages = 0;

			// Form parse?
			if($no_paging) { $this->records_per_page = -1; }

			// Run through post types
			$group_index = 0;
			$row_index = 0;
			foreach(($data_source_post_groups ? $post_types : array(false)) as $post_type) {

				// Calculate offset
				if($no_paging === false) {

					// API request
					$offset = (($page - 1) * $this->records_per_page);

				} else {

					// Form parse
					$offset = 0;
				}
				// get_posts args
				$args = array(

					'post_type' => ($this->data_source_post_groups == 'on') ? $post_type : $post_types,
					'posts_per_page' => $this->records_per_page,
					'offset' => $offset,
					'fields' => 'ids',
					'order' => $this->data_source_post_order,
					'orderby' => $this->data_source_post_order_by
				);

				// Post status filtering
				if(count($post_status) > 0) { $args['post_status'] = $post_status; }

				// Term filtering
				if(count($tax_query) > 0) { $args['tax_query'] = $tax_query; }

				// get_posts
				$wp_query = new WP_Query($args);

				// max_num_pages
				if($wp_query->max_num_pages > $max_num_pages) { $max_num_pages = $wp_query->max_num_pages; }

				$post_ids = !empty($wp_query->posts) ? $wp_query->posts : array();

				// Skip if no records
				if(count($post_ids) === 0) { continue; }

				// Rows
				$rows = array();
				foreach($post_ids as $post_index => $post_id) {

					$post = get_post($post_id);

					$rows[] = array(

						'id'		=> $offset + $row_index++,
						'default'	=> '',
						'required'	=> '',
						'disabled'	=> '',
						'hidden'	=> '',
						'data'		=> array(

							$post_id,
							$post->post_title,
							$post->post_status,
							$post->post_name,
							get_the_date('', $post_id)
						)
					);
				}

				// Build new group if one does not exist
				if(!isset($meta_value['groups'][$group_index])) {

					$meta_value['groups'][$group_index] = $group;
				}

				// Post type label
				if($data_source_post_groups) {

					$post_type_object = get_post_type_object($post_type);
					$meta_value['groups'][$group_index]['label'] = $post_type_object->labels->singular_name;

				} else {

					$meta_value['groups'][$group_index]['label'] = $this->label;
				}

				// Rows
				$meta_value['groups'][$group_index]['rows'] = $rows;

				// Enable optgroups
				if($data_source_post_groups && (count($post_types) > 1)) {

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

				'data_source_' . $this->id . '_filter_post_types',
				'data_source_' . $this->id . '_filter_post_statuses',
				'data_source_' . $this->id . '_filter_terms',
				'data_source_' . $this->id . '_filter_terms_relation',
				'data_source_' . $this->id . '_order_by',
				'data_source_' . $this->id . '_order',
				'data_source_' . $this->id . '_groups',
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

				// Filter - Post Types
				'data_source_' . $this->id . '_filter_post_types' => array(

					'label'						=>	__('Filter by Post Type', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which post type(s) to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_post_types'
					)
				),

				// Post types
				'data_source_' . $this->id . '_post_types' => array(

					'label'						=>	__('Post Type', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(),
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				// Filter - Post Status
				'data_source_' . $this->id . '_filter_post_statuses' => array(

					'label'						=>	__('Filter by Post Status', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which post status(es) to include.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_post_statuses'
					)
				),

				// Post statuses
				'data_source_' . $this->id . '_post_statuses' => array(

					'label'						=>	__('Post Status', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'publish',
					'options'					=>	array()
				),

				// Filter - Terms
				'data_source_' . $this->id . '_filter_terms' => array(

					'label'						=>	__('Filter by Term', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select which term(s) to filter by.', 'ws-form'),
					'meta_keys'					=>	array(

						'data_source_' . $this->id . '_terms'
					)
				),

				// Terms
				'data_source_' . $this->id . '_terms' => array(

					'label'						=>	__('Term', 'ws-form'),
					'type'						=>	'select_ajax',
					'select_ajax_method_search' => 'data_source_post_term_search',
					'select_ajax_method_cache'  => 'data_source_post_term_cache',
					'select_ajax_placeholder'   => __('Search terms...', 'ws-form')
				),

				// Filter - Terms - Logic
				'data_source_' . $this->id . '_filter_terms_relation' => array(

					'label'						=>	__('Filter by Term Logic', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'AND',
					'options'					=>	array(

						array('value' => 'AND', 'text' => 'AND'),
						array('value' => 'OR', 'text' => 'OR')
					)
				),

				// Order By
				'data_source_' . $this->id . '_order_by' => array(

					'label'						=>	__('Order By', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'title',
					'options'					=>	array(

						array('value' => 'none', 'text' => 'None'),
						array('value' => 'id', 'text' => 'ID'),
						array('value' => 'author', 'text' => 'Author'),
						array('value' => 'title', 'text' => 'Title'),
						array('value' => 'name', 'text' => 'Name'),
						array('value' => 'date', 'text' => 'Date'),
						array('value' => 'modified', 'text' => 'Date Modified'),
						array('value' => 'comment_count', 'text' => 'Comment Count'),
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

					'label'						=>	__('Group by Post Type', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'show_if_groups_group'		=>	true
				)
			);

			// Add post types
			$post_types_exclude = array('attachment');
			$post_types = get_post_types(array('show_in_menu' => true), 'objects', 'or');

			// Sort post types
			usort($post_types, function ($post_type_1, $post_type_2) {

				return $post_type_1->labels->singular_name < $post_type_2->labels->singular_name ? -1 : 1;
			});

			foreach($post_types as $post_type) {

				$post_type_name = $post_type->name;

				if(in_array($post_type_name, $post_types_exclude)) { continue; }

				$config_meta_keys['data_source_' . $this->id . '_post_types']['options'][] = array('value' => $post_type_name, 'text' => $post_type->labels->singular_name);
			}

			// Add post statuses
			$post_statuses = get_post_stati(array('internal' => false), 'object');
			foreach($post_statuses as $id => $post_status) {

				$config_meta_keys['data_source_' . $this->id . '_post_statuses']['options'][] = array('value' => $id, 'text' => $post_status->label);
			}

			// Merge
			$meta_keys = array_merge($meta_keys, $config_meta_keys);

			return $meta_keys;
		}

		// Term search
		public function term_search($parameters) {

			global $wpdb;

			$term = WS_Form_Common::get_query_var_nonce('term', '', $parameters);
			$type = WS_Form_Common::get_query_var_nonce('_type', '', $parameters);

			$taxonomy_lookups = self::get_taxonomy_lookup();

			$results = array();

			$terms = $wpdb->get_results(sprintf('SELECT DISTINCT t.term_id, t.name, tt.taxonomy FROM wp_terms AS t LEFT JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id WHERE ((t.name LIKE \'%1$s%%\') OR (t.slug LIKE \'%1$s%%\')) ORDER BY t.name ASC', esc_sql($term)));
			foreach ($terms as $term) {

				if(!isset($taxonomy_lookups[$term->taxonomy])) { continue; }
				$taxonomy_label = $taxonomy_lookups[$term->taxonomy];

				$results[] = array('id' => $term->term_id, 'text' => sprintf('%s: %s (ID: %u)', $taxonomy_label, $term->name, $term->term_id));
			}

			return array('results' => $results);
		}

		// Term cache
		public function term_cache($parameters) {

			$return_array = array();

			$taxonomy_lookups = self::get_taxonomy_lookup();

			$term_ids = WS_Form_Common::get_query_var_nonce('ids', '', $parameters);
			foreach ($term_ids as $term_id) {

				$term_id = intval($term_id);

				$term = get_term($term_id);
				if($term === false) { continue; }

				if(!isset($taxonomy_lookups[$term->taxonomy])) { continue; }
				$taxonomy_label = $taxonomy_lookups[$term->taxonomy];

				$return_array[$term_id] = sprintf('%s: %s (ID: %u)', $taxonomy_label, $term->name, $term->term_id);
			}

			return $return_array;
		}

		// Taxonomy lookups
		public function get_taxonomy_lookup() {

			// Get taxonomies
			$taxonomy_lookup = array();
			$taxonomies = get_taxonomies(array(), 'objecy');
			foreach($taxonomies as $id => $taxonomy) {

				$taxonomy_lookup[$id] = $taxonomy->labels->singular_name;
			}

			return $taxonomy_lookup;
		}

		// Build REST API endpoints
		public function rest_api_init() {

			// Get data source
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/data-source/' . $this->id . '/', array('methods' => 'POST', 'callback' => array($this, 'api_post')));

			// Select2 - Term
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/select2/data_source_post_term_search/', array( 'methods' => 'GET', 'callback' => array( $this, 'api_term_search' ) ) );
			register_rest_route(WS_FORM_RESTFUL_NAMESPACE, '/select2/data_source_post_term_cache/', array( 'methods' => 'POST', 'callback' => array( $this, 'api_term_cache' ) ) );
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

		// API endpoint - Search terms
		public function api_term_search( $parameters ) {

			return self::term_search( $parameters );
		}

		// API endpoint - Cache terms
		public function api_term_cache( $parameters ) {

			return self::term_cache( $parameters );
		}
	}

	new WS_Form_Data_Source_Post();
