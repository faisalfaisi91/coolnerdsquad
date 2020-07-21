<?php

	class WS_Form_API_Field extends WS_Form_API {

		public function __construct() {

			// Call parent on WS_Form_API
			parent::__construct();
		}

		// API - GET
		public function api_get($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('read_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);

			try {

				$field = $ws_form_field->db_read();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($field);
		}

		// API - POST
		public function api_post($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);
			$ws_form_field->section_id = self::api_get_section_id($parameters);

			// Get field type ID
			$ws_form_field->type = WS_Form_Common::get_query_var_nonce('type', '', $parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			try {

				// Create field
				$ws_form_field->db_create($next_sibling_id);

				// Width factor
				$width_factor = WS_Form_Common::get_query_var_nonce('width_factor', false, $parameters);
				if($width_factor !== false) {

					// Get framework info and calculate breakpoint meta key and value for 50% width
					$framework_id = WS_Form_Common::option_get('framework');
					$framework_column_count = WS_Form_Common::option_get('framework_column_count');
					$frameworks = WS_Form_Config::get_frameworks();
					$framework_breakpoints = $frameworks['types'][$framework_id]['breakpoints'];
					reset($framework_breakpoints);
					$breakpoint_first = key($framework_breakpoints);
					$breakpoint_meta_key = 'breakpoint_size_' . $breakpoint_first;
					$breakpoint_meta_value = round($framework_column_count * $width_factor);

					// Build meta data
					$field_meta = New WS_Form_Meta();
					$field_meta->object = 'field';
					$field_meta->parent_id = $ws_form_field->id;
					$field_meta->db_update_from_array(array($breakpoint_meta_key => $breakpoint_meta_value));
				}

				// Build api_json_response
				$api_json_response = $ws_form_field->db_read();

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	'post',
					'label'			=>	$ws_form_field->label,
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($api_json_response, $ws_form_field->form_id, $history);
		}

		// API - POST - Download - CSV
		public function api_post_download_csv($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('export_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var_nonce('meta_key', false, $parameters);
			if($meta_key === false) { parent::api_throw_error(__('Meta key not specified', 'ws-form')); }

			$meta_value_url = WS_Form_Common::get_query_var_nonce('meta_value', false, $parameters);
			if($meta_value_url !== false) {

				// Get meta value (Scratch)
				$meta_value_json = urldecode($meta_value_url);
				$meta_value = json_decode($meta_value_json);

			} else {

				// Get meta value (Database)
				$ws_form_meta = New WS_Form_Meta();
				$ws_form_meta->object = 'field';
				$ws_form_meta->parent_id = $ws_form_field->id;

				try {

					$meta_value = $ws_form_meta->db_get_object_meta($meta_key);

				} catch (Exception $e) {

					parent::api_throw_error($e->getMessage());
				}

				if($meta_value == '') { parent::api_throw_error(__('Meta value empty', 'ws-form')); }
			}

			// Get file index
			$group_index = WS_Form_Common::get_query_var_nonce('group_index', false, $parameters);
			if($group_index === false) { parent::api_throw_error(__('Group index not specified', 'ws-form')); }
			$group_index = intval($group_index);
			if($group_index < 0) { parent::api_throw_error(__('Group index invalid', 'ws-form')); }

			// Get columns
			if(!isset($meta_value->columns)) { parent::api_throw_error(__('Columns not found', 'ws-form')); }
			$columns = $meta_value->columns;

			// Get group
			if(!isset($meta_value->groups[$group_index])) { parent::api_throw_error(__('Group index invalid', 'ws-form')); }
			$group = $meta_value->groups[$group_index];

			// Get group label
			if(!isset($group->label)) { parent::api_throw_error(__('Group label not found', 'ws-form')); }
			$group_label = $group->label;

			// Get group rows
			if(!isset($group->rows)) { parent::api_throw_error(__('Group rows not found', 'ws-form')); }
			$rows = $group->rows;

			// Build filename
			$filename = strtolower($group_label) . '.csv';

			// HTTP headers
			WS_Form_Common::file_download_headers($filename, 'application/octet-stream');

			// Open stream
			$out = fopen('php://output', 'w');

			// Build header
			$row_array = array('wsf_id', 'wsf_default', 'wsf_required', 'wsf_disabled', 'wsf_hidden');
			foreach($columns as $column) {

				if(!isset($column->label)) { parent::api_throw_error(__('Column label not found', 'ws-form')); }
				$row_array[] = $column->label;
			}
			fputcsv($out, $row_array);

			// Build rows
			foreach($rows as $row) {

				if(!isset($row->data)) { parent::api_throw_error(__('Row data not found', 'ws-form')); }

				$default = isset($row->default) ? $row->default : '';
				$required = isset($row->required) ? $row->required : '';
				$disabled = isset($row->disabled) ? $row->disabled : '';
				$hidden = isset($row->hidden) ? $row->hidden : '';

				$data = array($row->id, $default, $required, $disabled, $hidden);
				$data = array_merge($data, (array)$row->data);

				fputcsv($out, $data);
			}

			// Close stream
			fclose($out);

			// Exit (Ensures WordPress intentional 'null' is not sent)
			exit;
		}

		// API - POST - Upload - CSV
		public function api_post_upload_csv($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);

			// Get meta key
			$meta_key = WS_Form_Common::get_query_var_nonce('meta_key', false, $parameters);
			if($meta_key === false) { parent::api_throw_error(__('Meta key not specified', 'ws-form')); }

			// Read current meta value
			$ws_form_meta = New WS_Form_Meta();
			$ws_form_meta->object = 'field';
			$ws_form_meta->parent_id = $ws_form_field->id;

			try {

				$meta_value = $ws_form_meta->db_get_object_meta($meta_key, false, false);

				if(!$meta_value) { parent::api_throw_error(__('Unable to read meta data', 'ws-form') + ': ' + $meta_key); }

				// Get files
				if(!isset($_FILES)) { parent::api_throw_error(__('No files found', 'ws-form')); }
				if(!isset($_FILES['file'])) { parent::api_throw_error(__('No files found', 'ws-form')); }

				// Run through files
				$file = $_FILES['file'];

				// Get CSV meta_value
				$meta_value = WS_Form_Common::csv_file_to_data_grid_meta_value($file, $meta_key, $meta_value);

				// Get section ID
				$ws_form_field->section_id = $ws_form_field->db_get_section_id();

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'post'),
					'label'			=>	$ws_form_field->db_get_label($ws_form_field->table_name, $ws_form_field->id),
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($meta_value, $ws_form_field->form_id, $history);
		}

		// API - PUT
		public function api_put($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);

			// Get field data
			$field_object = WS_Form_Common::get_query_var_nonce('field', false, $parameters);
			if(!$field_object) { return false; }

			try {

				// Put field
				$ws_form_field->db_update_from_object($field_object);

				// Get section ID
				$ws_form_field->section_id = $ws_form_field->db_get_section_id();

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	WS_Form_Common::get_query_var_nonce('history_method', 'put'),
					'label'			=>	$ws_form_field->db_get_label($ws_form_field->table_name, $ws_form_field->id),
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_field->form_id, isset($field_object->history_suppress) ? false : $history);
		}

		// API - PUT - SORT INDEX
		public function api_put_sort_index($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$api_json_response = [];

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);
			$ws_form_field->section_id = self::api_get_section_id($parameters);

			// Get next sibling ID
			$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

			try {

				// Process sort index
				$ws_form_field->db_object_sort_index($ws_form_field->table_name, 'section_id', $ws_form_field->section_id, $next_sibling_id, $ws_form_field->id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	'put_sort_index',
					'label'			=>	$ws_form_field->db_get_label($ws_form_field->table_name, $ws_form_field->id),
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($api_json_response, $ws_form_field->form_id, $history);
		}

		// API - PUT - CLONE
		public function api_put_clone($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$api_json_response = [];

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);

			try {

				// Read
				$ws_form_field->db_read();

				// Get section ID
				$ws_form_field->section_id = $ws_form_field->db_get_section_id();

				// Get next sibling ID
				$next_sibling_id = intval(WS_Form_Common::get_query_var_nonce('next_sibling_id', 0, $parameters));

				// Get sort_index
				$ws_form_field->sort_index = $ws_form_field->db_object_sort_index_get($ws_form_field->table_name, 'section_id', $ws_form_field->section_id, $next_sibling_id);

				// Rename
				$ws_form_field->label = sprintf(__('%s (Copy)', 'ws-form'), $ws_form_field->label);

				// Clone
				$ws_form_field->id = $ws_form_field->db_clone();

				// Remember label before change
				$label = $ws_form_field->label;

				// Build api_json_response
				$api_json_response = $ws_form_field->db_read();

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	'put_clone',
					'label'			=>	$label,
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response($api_json_response, $ws_form_field->form_id, $history);
		}

		// API - DELETE
		public function api_delete($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);
			$ws_form_field->form_id = self::api_get_form_id($parameters);

			try {

				// Get section ID
				$ws_form_field->section_id = $ws_form_field->db_get_section_id();

				// Get label (We do this because once its deleted, we can't reference it)
				$label = $ws_form_field->db_get_label($ws_form_field->table_name, $ws_form_field->id);

				// Delete field
				$ws_form_field->db_delete();

				// Clean up sort index for section
				$ws_form_field->db_object_sort_index_clean($ws_form_field->table_name, 'section_id', $ws_form_field->section_id);

				// Describe transaction for history
				$history = array(

					'object'		=>	'field',
					'method'		=>	'delete',
					'label'			=>	$label,
					'section_id'	=>	$ws_form_field->section_id,
					'id'			=>	$ws_form_field->id
				);

				// Update checksum
				$ws_form_field->db_checksum();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response([], $ws_form_field->form_id, $history);
		}

		// Clear last api error
		public function api_put_last_api_error_clear($parameters) {

			// User capability check
			if(!WS_Form_Common::can_user('edit_form')) { parent::api_access_denied(); }

			$ws_form_field = new WS_Form_Field();
			$ws_form_field->id = self::api_get_id($parameters);

			try {

				$ws_form_field->db_last_api_error_clear();

			} catch (Exception $e) {

				parent::api_throw_error($e->getMessage());
			}

			// Send JSON response
			parent::api_json_response();
		}

		// Get form ID
		public function api_get_form_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('id', 0, $parameters));
		}

		// Get section ID
		public function api_get_section_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('section_id', 0, $parameters));
		}

		// Get section ID from (used to determine where a field was dragged from)
		public function api_get_section_id_from($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('section_id_from', 0, $parameters));
		}

		// Get field ID
		public function api_get_id($parameters) {

			return intval(WS_Form_Common::get_query_var_nonce('field_id', 0, $parameters));
		}
	}
