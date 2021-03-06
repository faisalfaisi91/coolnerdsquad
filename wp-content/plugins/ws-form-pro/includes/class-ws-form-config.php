<?php

	/**
	 * Configuration settings
	 * Pro Version
	 */

	class WS_Form_Config {

		// Caches
		public static $meta_keys = array();
		public static $field_types = array();
		public static $file_types = false;
		public static $settings_plugin = array();
		public static $settings_form_admin = false;
		public static $frameworks = array();
		public static $parse_variables = array();
		public static $parse_variable_help = array();
		public static $calc = false;
		public static $tracking = array();
		public static $ecommerce = false;
		public static $data_sources = false;

		// Get full public or admin config
		public static function get_config($parameters = false, $field_types = array()) {

			// Determine if this is an admin or public API request
			$is_admin = (WS_Form_Common::get_query_var('form_is_admin', 'false') == 'true');
			$form_id = WS_Form_Common::get_query_var('form_id', 0);

			// Standard response
			$config = array();

			// Different for admin or public
			if($is_admin) {

				$config['meta_keys'] = self::get_meta_keys($form_id, false);
				$config['field_types'] = self::get_field_types(false);
				$config['file_types'] = self::get_file_types(false);
				$config['settings_plugin'] = self::get_settings_plugin(false);
				$config['settings_form'] = self::get_settings_form_admin();
				$config['frameworks'] = self::get_frameworks(false);
				$config['parse_variable_help'] = self::get_parse_variable_help($form_id, false);
				$config['calc'] = self::get_calc();
				$config['tracking'] = self::get_tracking(false);
				$config['ecommerce'] = self::get_ecommerce();
				$config['actions'] = WS_Form_Action::get_settings();
				$config['data_sources'] = WS_Form_Data_Source::get_settings();

			} else {

				$config['meta_keys'] = self::get_meta_keys($form_id, true);
				$config['field_types'] = self::get_field_types_public($field_types);
				$config['settings_plugin'] = self::get_settings_plugin();
				$config['settings_form'] = self::get_settings_form_public();
				$config['frameworks'] = self::get_frameworks();
				$config['parse_variables'] = self::get_parse_variables();
				$config['external'] = self::get_external();
				$config['analytics'] = self::get_analytics();
				$config['tracking'] = self::get_tracking();
				$config['ecommerce'] = self::get_ecommerce();

				// Debug
				if(WS_Form_Common::debug_enabled()) {

					$config['debug'] = self::get_debug();
				}
			}

			// Add generic settings (Shared between both admin and public, e.g. language)
			$config['settings_form'] = array_merge_recursive($config['settings_form'], self::get_settings_form(!$is_admin));

			return $config;
		}

		// Attributes

		//	label 					Field type label
		//	label_default 			Default label injected into field when it is created
		//	label_position_force	Force position of the label on this field. This useful if you don't want label positioning to affect the masks for this fied (e.g. input type file)
		//	license 				true = Licensed to use, false = Not licensed to use
		//	required 				Whether or not required functionality applies to this field
		//	fieldsets 				Configuration fieldsets (meta_keys) shown in the sidebar

		//	data_source 			Type and ID of data source linked to this field (e.g. for rendering repeater elements such as options)

		//	mask 						Overall field mask wrapper (Defaults to #field if not specified)

		//	mask_group 					Mask for groups (e.g. <optgroup label="#group_label"#disabled>#group</optgroup>)
		//	mask_group_attributes		Which fields should be rendered as part of the #attributes tag
		//	mask_group_label			Mask for the group label (e.g. #group_label)
		//	mask_group_always 			Should the group mask always be rendered?

		//	mask_row 					Mask for each data grid row (e.g. <option value="#select_field_value"#attributes>#select_field_label</option> or <div#attributes>#row_label</div>)
		//	mask_row 					Mask for placeholder row (e.g. <option value="">#value</option for Select... row)
		//	mask_row_attributes			Which fields should be rendered as part of the #attributes tag
		//	mask_row_label 				Mask for each row label (Typically include #row_field)
		//	mask_row_label_attributes	Attributes to include in row labels
		//	mask_row_field 				Mask for each row field
		//	mask_row_field_attributes	Attributes to include in row labels
		//	mask_row_lookups			Which fields are made available in mask_row (These are lookups in the data)
		//	datagrid_column_value		Name of field that is saved as the submit value
		//	mask_row_default			String to use if a row is marked as default (e.g. ' selected' for a select field)

		//	mask_field 					Mask for the field itself (e.g. <input type="text" id="#id" name="#name" class="#class" value="#value"#attributes />)
		//	mask_field_attributes		Which fields should be rendered as part of the #attributes tag

		//	mask_field_label 			Mask for the field label when rendered (e.g. <label id="#label_id" for="#id" class="#class">#label</label>)
		//	mask_field_label_attributes	Which fields should be rendered as part of the #attributes tag for field labels
		//	mask_field_label_hide_group	Hide labels on groups

		//	mask_help 					Mask for field help (if omitted, falls back to framework mask_help)


		// Submit variables

		//	submit_save 				Should this field be saved to meta data (e.g. html_editor = false)
		//	submit_edit					Can this field be edited once submitted (e.g. signature = false)
		//	submit_edit_type			Override type for editing (e.g. hidden = text)
		//	submit_array				Should this field be treated as array (true for datagrid fields such as select, radio and checkbox)


		// Mask variables

		//	#id 					Field ID
		//	#label 					Field label
		//	#attributes 			Field attributes (attributes that may or may not have a value, e.g. maxlength)
		//	#value 					Field value

		//	#group_id 				Group ID (i.e. Unique to tab index in data grid)
		//	#group_label 			Group label (i.e. Tab name in data grid)

		//	#row_id 				Row ID (e.g. of option in optgroup or datalist)
		//	#row_label 				Render the mask_row_label mask
		//	#row_field 				Render the mask_row_field mask

		//	#data 					Outputs content of data grid (e.g. select optgroup/options or datalist)

		//	logics_enabled			Logics enabled on 'if' condition
		//	actions_enabled			Actions enavbled on 'then'/'else'
		//	condition_event 		Event that will fire a conditional check (space separate multiple events)

		// Configuration - Field Types
		public static function get_field_types($public = true) {

			// Check cache
			if(isset(self::$field_types[$public])) { return self::$field_types[$public]; }

			$field_types = array(

				'basic' => array(

					'label'	=> __('Basic', 'ws-form'),
					'types' => array(

						'text' => array (

							'label'				=>	__('Text', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/text/',
							'label_default'		=>	__('Text', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'		=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'cw==', 'cw!=', 'cw>', 'cw<', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'		=>	array('visibility', 'required', 'focus', 'value', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'		=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'	=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'					=>	'<input type="text" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'readonly', 'required', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'placeholder', 'pattern', 'list', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value', 'placeholder', 'help_count_char_word'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled', 'readonly', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'pattern')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Autocomplete
								'datalist'	=> array(

									'label'		=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'textarea' => array (

							'label'				=>	__('Text Area', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/textarea/',
							'label_default'		=>	__('Text Area', 'ws-form'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'		=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'cw==', 'cw!=', 'cw>', 'cw<', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'		=>	array('visibility', 'required', 'focus', 'value_textarea', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'		=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Fields
							'mask_field'					=>	'<textarea id="#id" name="#name"#attributes>#value</textarea>#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'readonly', 'required', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'placeholder', 'spellcheck', 'cols', 'rows', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'input_type_textarea', 'input_type_textarea_toolbar'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'input_type_textarea', 'input_type_textarea_toolbar', 'required', 'hidden', 'default_value_textarea', 'placeholder', 'help_count_char_word_with_default'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'cols', 'rows')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'number' => array (

							'label'				=>	__('Number', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/number/',
							'label_default'		=>	__('Number', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'blank', 'blank_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value_number', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-number',
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'				=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'		=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'									=>	'<input type="number" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'				=>	array('class', 'list', 'min', 'max', 'step', 'disabled', 'readonly', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_number', 'placeholder', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly', 'min', 'max', 'step')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'		=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'tel' => array (

							'label'				=>	__('Phone', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/tel/',
							'label_default'		=>	__('Phone', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	false,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value_tel', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-email-tel-url',
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'				=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'		=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'					=>	'<input type="tel" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'readonly', 'min_length', 'max_length', 'pattern_tel', 'list', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'input_mask', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_tel', 'placeholder', 'help_count_char'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'		=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled','readonly', 'min_length', 'max_length', 'input_mask', 'pattern_tel')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'		=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'email' => array (

							'label'					=>	__('Email', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'				=>	'/knowledgebase/email/',
							'label_default'			=>	__('Email', 'ws-form'),
							'data_source'			=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'			=>	true,
							'submit_edit'			=>	true,
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'value_out'				=>	true,
							'progress'				=>	true,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'regex_email', 'regex_email_not', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value_email', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-email-tel-url',
							'events'				=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'			=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'		=> true,

							// Rows
							'mask_row'				=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'		=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'						=>	'<input type="email" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'				=>	array('class', 'multiple_email', 'min_length', 'max_length', 'pattern', 'list', 'disabled', 'readonly', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'					=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'		=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_email', 'multiple_email', 'placeholder', 'help_count_char'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled','readonly', 'min_length', 'max_length', 'pattern')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'		=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'url' => array (

							'label'				=>	__('URL', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/url/',
							'label_default'		=>	__('URL', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	false,
							'calc_out'			=>	false,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'regex_url', 'regex_url_not', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value_url', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-email-tel-url',
							'events'						=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'				=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'							=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'			=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'									=>	'<input type="url" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'				=>	array('class', 'min_length', 'max_length', 'list', 'disabled', 'readonly', 'required', 'placeholder', 'pattern', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_url', 'placeholder', 'help_count_char'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'			=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled','readonly', 'min_length', 'max_length', 'pattern')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'			=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						)
					)
				),

				'choice' => array(

					'label'	=> __('Choice', 'ws-form'),
					'types' => array(

						'select' => array (

							'label'				=>	__('Select', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/select/',
							'label_default'		=>	__('Select', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_select'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'submit_array'		=>	true,
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'			=>	'data_grid_select',
								'option_text'				=>	'select_field_label',
								'logics_enabled'			=>	array('selected', 'selected_not', 'selected_any', 'selected_any_not', 'selected_value_equals', 'selected_value_equals', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'			=>	array('visibility', 'required', 'focus', 'value_row_select', 'value_row_deselect', 'value_row_select_value', 'value_row_deselect_value', 'value_row_disabled', 'value_row_not_disabled', 'value_row_class_add', 'value_row_class_remove', 'value', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field', 'value_row_reset'),
								'condition_event'			=>	'change'
							),
							'events'	=>	array(

								'event'						=>	'change',
								'event_category'			=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'					=>	'<optgroup label="#group_label"#disabled>#group</optgroup>',
							'mask_group_label'				=>	'#group_label',

							// Rows
							'mask_row'						=>	'<option id="#row_id" data-id="#data_id" value="#select_field_value"#attributes>#select_field_label</option>',
							'mask_row_placeholder'			=>	'<option data-id="0" value="" data-placeholder>#value</option>',
							'mask_row_attributes'			=>	array('default', 'disabled'),
							'mask_row_lookups'				=>	array('select_field_value', 'select_field_label', 'select_field_parse_variable', 'select_cascade_field_filter'),
							'datagrid_column_value'			=>	'select_field_value',
							'mask_row_default' 				=>	' selected',

							// Fields
							'mask_field'					=>	'<select id="#id" name="#name"#attributes>#data</select>#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'size', 'multiple', 'required', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=> array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'multiple', 'size', 'placeholder_row', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'select_min', 'select_max')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Options
								'options'	=> array(

									'label'			=>	__('Options', 'ws-form'),
									'meta_keys'		=> array('data_grid_select', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('select_field_label', 'select_field_value', 'select_field_parse_variable')
										),
										array(
											'label'		=>	__('Cascading', 'ws-form'),
											'meta_keys'	=> array('select_cascade', 'select_cascade_field_filter', 'select_cascade_field_id')
										)
									)
								)
							)
						),

						'checkbox' => array (

							'label'				=>	__('Checkbox', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/checkbox/',
							'label_default'		=>	__('Checkbox', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_checkbox'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'submit_array'		=>	true,
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'		=>	'data_grid_checkbox',
								'option_text'			=>	'checkbox_field_label',
								'logics_enabled'		=>	array('checked', 'checked_not', 'checked_any', 'checked_any_not', 'checked_value_equals', 'checked_value_equals', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'		=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper', 'value_row_check', 'value_row_uncheck', 'value_row_check_value','value_row_uncheck_value', 'value_row_focus', 'value_row_required', 'value_row_not_required', 'value_row_disabled', 'value_row_not_disabled', 'value_row_visible', 'value_row_not_visible', 'value_row_class_add', 'value_row_class_remove', 'value_row_set_custom_validity'),
								'condition_event'		=>	'change',
								'condition_event_row'	=>	true
							),
							'events'	=>	array(

								'event'				=>	'change',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group_wrapper'		=>	'<div#attributes>#group</div>',
							'mask_group_label'			=>	'<legend>#group_label</legend>',

							// Rows
							'mask_row'					=>	'<div#attributes>#row_label</div>',
							'mask_row_attributes'		=>	array('class'),
							'mask_row_label'			=>	'<label id="#label_row_id" for="#row_id"#attributes>#row_field#checkbox_field_label#required</label>#invalid_feedback',
							'mask_row_label_attributes'	=>	array('class'),
							'mask_row_field'			=>	'<input type="checkbox" id="#row_id" name="#name" value="#checkbox_field_value"#attributes />',
							'mask_row_field_attributes'	=>	array('class', 'default', 'disabled', 'required', 'aria_labelledby'),
							'mask_row_lookups'			=>	array('checkbox_field_value', 'checkbox_field_label', 'checkbox_field_parse_variable'),
							'datagrid_column_value'		=>	'checkbox_field_value',
							'mask_row_default' 			=>	' checked',

							// Fields
							'mask_field'					=>	'#data#invalid_feedback#help',
							'mask_field_label'				=>	'<label id="#label_id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),
//							'mask_field_label_hide_group'	=>	true,

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off', 'label_position', 'label_column_width', 'hidden', 'select_all', 'select_all_label', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Layout', 'ws-form'),
											'meta_keys'	=>	array('orientation',
												'orientation_breakpoint_sizes'
											)
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('checkbox_min', 'checkbox_max')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Checkboxes
								'checkboxes' 	=> array(

									'label'		=>	__('Checkboxes', 'ws-form'),
									'meta_keys'	=> array('data_grid_checkbox', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('checkbox_field_label', 'checkbox_field_value', 'checkbox_field_parse_variable')
										)
									)
								)
							)
						),

						'radio' => array (

							'label'				=>	__('Radio', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/radio/',
							'label_default'		=>	__('Radio', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_radio'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'submit_array'		=>	true,
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'		=>	'data_grid_radio',
								'option_text'			=>	'radio_field_label',
								'logics_enabled'		=>	array('checked', 'checked_not', 'checked_any', 'checked_any_not', 'checked_value_equals', 'checked_value_equals', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'		=>	array('visibility', 'required', 'class_add_wrapper', 'class_remove_wrapper', 'value_row_check', 'value_row_uncheck', 'value_row_check_value','value_row_uncheck_value', 'value_row_focus', 'value_row_disabled', 'value_row_not_disabled', 'value_row_visible', 'value_row_not_visible', 'value_row_class_add', 'value_row_class_remove', 'set_custom_validity'),
								'condition_event'		=>	'change',
								'condition_event_row'	=>	true
							),
							'events'	=>	array(

								'event'				=>	'change',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group_wrapper'		=>	'<div#attributes>#group</div>',
							'mask_group_label'			=>	'<legend>#group_label</legend>',

							// Rows
							'mask_row'					=>	'<div#attributes>#row_label</div>',
							'mask_row_attributes'		=>	array('class'),
							'mask_row_label'			=>	'<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#row_field#radio_field_label</label>#invalid_feedback',
							'mask_row_label_attributes'	=>	array('class'),
							'mask_row_field'			=>	'<input type="radio" id="#row_id" name="#name" value="#radio_field_value"#attributes />',
							'mask_row_field_attributes'	=>	array('class', 'default', 'disabled', 'required_row', 'aria_labelledby', 'hidden'),
							'mask_row_lookups'			=>	array('radio_field_value', 'radio_field_label', 'radio_field_parse_variable', 'radio_cascade_field_filter'),
							'datagrid_column_value'		=>	'radio_field_value',
							'mask_row_default' 			=>	' checked',

							// Fields
							'mask_field'					=>	'#data#help',
							'mask_field_attributes'			=>	array('class', 'required_attribute_no'),
							'mask_field_label'				=>	'<label id="#label_id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),
//							'mask_field_label_hide_group'	=>	true,

							'invalid_feedback_last_row'		=> true,

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'required_attribute_no', 'hidden', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Layout', 'ws-form'),
											'meta_keys'	=>	array('orientation',
												'orientation_breakpoint_sizes'
											)
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Radios
								'radios'	=> array(

									'label'		=>	__('Radios', 'ws-form'),
									'meta_keys'	=> array('data_grid_radio', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('radio_field_label', 'radio_field_value', 'radio_field_parse_variable')
										),
										array(
											'label'		=>	__('Cascading', 'ws-form'),
											'meta_keys'	=> array('radio_cascade', 'radio_cascade_field_filter', 'radio_cascade_field_id')
										)
									)
								)
							)
						),

						'datetime' => array (

							'label'				=>	__('Date/Time', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/datetime/',
							'label_default'		=>	__('Date/Time', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('d==', 'd!=', 'd<', 'd>', 'blank', 'blank_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value_datetime', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change'
							),
							'compatibility_id'	=>	'input-datetime',
							'events'			=>	array(

								'event'				=>	'change',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'	=>	array('datalist_field_value', 'datalist_field_text'),

							// Fields
							'mask_field'					=>	'<input id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('input_type_datetime', 'format_date', 'format_time', 'class', 'disabled', 'required', 'readonly', 'min_date', 'max_date', 'year_start', 'year_end', 'step', 'input_mask', 'pattern_date', 'list', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'input_type_datetime', 'format_date', 'format_time', 'required', 'hidden', 'default_value_datetime', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly', 'min_date', 'max_date', 'year_start', 'year_end', 'step', 'input_mask', 'pattern_date')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Autocomplete
								'datalist'	=> array(

									'label'		=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'range' => array (

							'label'				=>	__('Range Slider', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/range/',
							'label_default'		=>	__('Range Slider', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'	=>	array('visibility', 'focus', 'value_range', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-range',
							'events'						=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),
							'trigger'			=> 'input',

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'	=>	array('datalist_field_value', 'datalist_field_text'),

							// Fields
							'mask_field'					=>	'<input type="range" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'list', 'min', 'max', 'step', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'class_fill_lower_track'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'hidden', 'default_value_range', 'help_range'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align', 'class_fill_lower_track')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'min', 'max', 'step')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Tick Marks
								'tickmarks'	=> array(

									'label'		=>	__('Tick Marks', 'ws-form'),
									'meta_keys'	=>	array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'color' => array (

							'label'				=>	__('Color Picker', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/color/',
							'label_default'		=>	__('Color Picker', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	false,
							'calc_out'			=>	false,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('c==', 'c!=', 'ch<', 'ch>', 'cs<', 'cs>', 'cl<', 'cl>', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'focus', 'value_color', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change'
							),
							'compatibility_id'	=>	'input-color',
							'events'			=>	array(

								'event'				=>	'change',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option>#datalist_field_value</option>',
							'mask_row_lookups'	=>	array('datalist_field_value'),

							// Fields
							'mask_field'					=>	'<input type="#color_type" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'list', 'required', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_color', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'		=>	array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_value')
										)
									)
								)
							)
						),

						'rating' => array (

							'label'				=>	__('Rating', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/rating/',
							'label_default'		=>	__('Rating', 'ws-form'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'	=>	array('visibility', 'value_rating', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),
							'trigger'			=> 'input',

							'mask_field'					=>	'<input data-rating type="number" id="#id" name="#name" value="#value"#attributes style="display:none;" />#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'required', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'rating_color_off', 'rating_color_on'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value_number', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align', 'horizontal_align', 'rating_icon', 'rating_icon_html', 'rating_size', 'rating_color_off', 'rating_color_on')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'			=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('rating_max')
										),										

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'			=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					)
				),

				'advanced' => array(

					'label'	=> __('Advanced', 'ws-form'),
					'types' => array(

						'file' => array (

							'label'							=>	__('File Upload', 'ws-form'),
							'pro_required'					=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'						=>	'/knowledgebase/file/',
							'label_default'					=>	__('File Upload', 'ws-form'),
							'label_position_force'			=>	'top',	// Prevent formatting issues with different label positioning. The label is the button.
							'submit_save'					=>	true,
							'submit_edit'					=>	false,
							'submit_array'					=>	true,
							'calc_in'						=>	false,
							'calc_out'						=>	false,
							'value_out'						=>	false,
							'progress'						=>	true,
							'conditional'					=>	array(

								'logics_enabled'	=>	array('f==', 'f!=', 'f<', 'f>', 'file', 'file_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'click', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field', 'reset_file'),
								'condition_event'	=>	'change input'
							),
							'events'						=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Fields
							'mask_field'					=>	'<input type="file" id="#id" name="#name"#attributes />#label#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'multiple_file', 'directory', 'disabled', 'accept', 'required', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('required', 'hidden', 'multiple_file', 'directory', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Image Optimization', 'ws-form'),
											'meta_keys'	=> array('file_image_max_width', 'file_image_max_height', 'file_image_crop', 'file_image_compression', 'file_image_mime')
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled')
										),

										array(
											'label'		=>	__('File Restrictions', 'ws-form'),
											'meta_keys'	=> array('accept')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'hidden' => array (

							'label'						=>	__('Hidden', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'					=>	'/knowledgebase/hidden/',
							'label_default'				=>	__('Hidden', 'ws-form'),
							'mask_field'				=>	'<input type="hidden" id="#id" name="#name" value="#value" />',
							'submit_save'				=>	true,
							'submit_edit'				=>	true,
							'submit_edit_type'			=>	'text',
							'calc_in'					=>	true,
							'calc_out'					=>	true,
							'value_out'					=>	true,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'		=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', '<', '>', '<=', '>=', 'blank', 'blank_not', 'regex', 'regex_not', 'field_match', 'field_match_not'),
								'actions_enabled'		=>	array('value'),
								'condition_event'		=>	'change'
							),
							'mask_wrappers_drop'		=>	true,

							'fieldsets'					=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('default_value'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email_on')
										)
									)
								)
							)
						),

						'recaptcha' => array (

							'label'							=>	__('reCAPTCHA', 'ws-form'),
							'pro_required'					=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'						=>	'/knowledgebase/recaptcha/',
							'label_default'					=>	__('reCAPTCHA', 'ws-form'),
							'mask_field'					=>	'<div id="#id" data-name="#name" style="border: none; padding: 0" required#attributes></div>#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'recaptcha_site_key', 'recaptcha_recaptcha_type', 'recaptcha_badge', 'recaptcha_type', 'recaptcha_theme', 'recaptcha_size', 'recaptcha_language', 'recaptcha_action'),
							'submit_save'					=>	false,
							'submit_edit'					=>	false,
							'calc_in'						=>	false,
							'calc_out'						=>	false,
							'value_out'						=>	false,
							'progress'						=>	false,
							'multiple'						=>	false,
							'conditional'					=>	array(

								'logics_enabled'	=>	array('recaptcha', 'recaptcha_not'),
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper'),
								'condition_event'	=> 'recaptcha'
							),
							'events'						=>	array(

								'event'				=>	'mousedown touchstart',
								'event_category'	=>	__('Field', 'ws-form')
							),

							'fieldsets'						=> array(

								// Tab: Basic
								'basic'		=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'recaptcha_recaptcha_type', 'recaptcha_site_key', 'recaptcha_secret_key', 'recaptcha_badge', 'recaptcha_type', 'recaptcha_theme', 'recaptcha_size', 'recaptcha_action', 'help'),
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),

										array(
											'label'		=>	__('Localization', 'ws-form'),
											'meta_keys'	=>	array('recaptcha_language')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'signature' => array (

							'label'								=>	__('Signature', 'ws-form'),
							'pro_required'						=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'							=>	'/knowledgebase/signature/',
							'label_default'						=>	__('Signature', 'ws-form'),
							'mask_field'						=>	'<canvas id="#id" data-name="#name"#attributes tabIndex="0"></canvas>#invalid_feedback#help',
							'mask_field_attributes'				=>	array('class', 'signature_mime', 'signature_dot_size', 'signature_pen_color', 'signature_background_color', 'signature_height', 'signature_crop', 'required', 'disabled', 'custom_attributes'),
							'mask_field_label'					=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'		=>	array('class'),
							'mask_help_append'					=>	'#help_append_separator<a href="#" data-action="wsf-signature-clear">' . __('Clear', 'ws-form') . '</a>',
							'mask_help_append_separator'		=>	'<br />',
							'submit_save'						=>	true,
							'submit_edit'						=>	false,
							'calc_in'							=>	false,
							'calc_out'							=>	false,
							'value_out'							=>	false,
							'progress'							=>	true,
							'conditional'						=>	array(

								'logics_enabled'		=>	array('signature', 'signature_not', 'validate', 'validate_not'),
								'actions_enabled'		=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field', 'required_signature', 'disabled', 'reset_signature'),
								'condition_event'		=>	'mouseup touchend'
							),
							'events'							=>	array(

								'event'				=>	'mouseup touchend',
								'event_category'	=>	__('Field', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required_on', 'hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align', 'signature_mime', 'signature_pen_color', 'signature_background_color', 'signature_dot_size', 'signature_height', 'signature_crop',)
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'			=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'progress' => array (

							'label'				=>	__('Progress', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/progress/',
							'label_default'		=>	__('Progress', 'ws-form'),
							'submit_save'		=>	false,
							'submit_edit'		=>	false,
							'progress'			=>	false,
							'calc_in'			=>	true,
							'calc_out'			=>	false,
							'value_out'			=>	false,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'field_match', 'field_match_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change'),
								'actions_enabled'	=>	array('visibility', 'value', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change'
							),
							'mask_field'					=>	'<progress data-progress-bar data-progress-bar-value id="#id" name="#name" value="#value" min="0" max="100"#attributes /></progress>#help',
							'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'hidden', 'default_value_number', 'progress_source', 'help_progress'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'			=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('min', 'max')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'password' => array (

							'label'				=>	__('Password', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/password/',
							'label_default'		=>	__('Password', 'ws-form'),
							'submit_save'		=>	false,
							'submit_edit'		=>	false,
							'calc_in'			=>	false,
							'calc_out'			=>	false,
							'value_out'			=>	false,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'value', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'events'				=>	array(

								'event'				=>	'change',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Fields
							'mask_field'					=>	'<input type="password" id="#id" name="#name" value="#value"#attributes />#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'autocomplete_new_password', 'required', 'readonly', 'min_length', 'max_length', 'placeholder', 'input_mask', 'pattern', 'aria_describedby', 'aria_labelledby', 'aria_label', 'password_strength_meter', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'required_on', 'hidden', 'default_value', 'placeholder', 'help_count_char'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Strength', 'ws-form'),
											'meta_keys'	=>	array('password_strength_meter')
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled', 'readonly', 'autocomplete_new_password', 'min_length', 'max_length', 'input_mask', 'pattern')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'search' => array (

							'label'				=>	__('Search', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/search/',
							'label_default'		=>	__('Search', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	true,
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'value_out'			=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'		=>	array('equals', 'equals_not', 'contains', 'contains_not', 'starts', 'starts_not', 'ends', 'ends_not', 'blank', 'blank_not', 'cc==', 'cc!=', 'cc>', 'cc<', 'cw==', 'cw!=', 'cw>', 'cw<', 'regex', 'regex_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'		=>	array('visibility', 'required', 'focus', 'value', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'		=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'keyup',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'	=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'					=>	'<input type="search" id="#id" name="#name" value="#value"#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'readonly', 'required', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'placeholder', 'pattern', 'list', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'default_value', 'placeholder', 'help_count_char_word'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled', 'readonly', 'min_length', 'max_length', 'min_length_words', 'max_length_words', 'input_mask', 'pattern')
										),

										array(
											'label'		=>	__('Duplication', 'ws-form'),
											'meta_keys'	=>	array('dedupe','dedupe_message')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Autocomplete
								'datalist'	=> array(

									'label'		=>	__('Datalist', 'ws-form'),
									'meta_keys'	=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'legal' => array (

							'label'					=>	__('Legal', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/legal/',
							'label_default'			=>	__('Legal', 'ws-form'),

							// Fields
							'mask_field'			=>	'<div data-wsf-legal#attributes>#value</div>',
							'mask_field_attributes'			=>	array('class', 'legal_source', 'legal_termageddon_key', 'legal_termageddon_hide_title', 'legal_style_height'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'calc_in'				=>	false,
							'calc_out'				=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off', 'label_position', 'label_column_width', 'hidden', 'legal_source', 'legal_termageddon_intro', 'legal_termageddon_key', 'legal_termageddon_hide_title', 'legal_text_editor')
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('legal_style_height', 'class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)

									)
								)
							)
						)
					)
				),

				'content' => array(

					'label'	=> __('Content', 'ws-form'),
					'types' => array(

						'texteditor' => array (

							'label'					=>	__('Text Editor', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'				=>	'/knowledgebase/texteditor/',
							'label_default'			=>	__('Text Editor', 'ws-form'),
							'mask_field'			=>	'<div data-text-editor#attributes>#value</div>',
							'mask_preview'			=>	'#text_editor',
							'meta_wpautop'			=>	'text_editor',
							'meta_do_shortcode'		=>	'text_editor',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'static'				=>	'text_editor',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'text_editor', 'html', 'class_add_wrapper', 'class_remove_wrapper')
							),

							'fieldsets'				=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'text_editor'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email_on')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'html' => array (

							'label'					=>	__('HTML', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/html/',
							'label_default'			=>	__('HTML', 'ws-form'),
							'mask_field'			=>	'<div data-html#attributes>#value</div>',
							'meta_do_shortcode'		=>	'html_editor',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'static'				=>	'html_editor',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'		=>	array('visibility', 'html', 'class_add_wrapper', 'class_remove_wrapper')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'html_editor'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email_on')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)

									)
								)
							)
						),

						'divider' => array (

							'label'					=>	__('Divider', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'				=>	'/knowledgebase/divider/',
							'label_default'			=>	__('Divider', 'ws-form'),
							'mask_field'			=>	'<hr#attributes />',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'calc_in'				=>	false,
							'calc_out'				=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper')
							),
							'label_disabled'			=>	true,

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden')
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'spacer' => array (

							'label'				=>	__('Spacer', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'			=>	'/knowledgebase/spacer/',
							'label_default'		=>	__('Spacer', 'ws-form'),
							'mask_field'		=>	'',
							'submit_save'		=>	false,
							'submit_edit'		=>	false,
							'calc_in'			=>	false,
							'calc_out'			=>	false,
							'value_out'			=>	false,
							'progress'			=>	false,
							'conditional'		=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper')
							),
							'label_disabled'	=>	true,

							'fieldsets'			=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden')
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'message' => array (

							'label'					=>	__('Message', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/message/',
							'icon'					=>	'info-circle',
							'label_default'			=>	__('Message', 'ws-form'),
							'mask_field'			=>	'<div data-text-editor#attributes>#value</div>',
							'mask_field_attributes'	=>	array('class'),
							'mask_preview'			=>	'#text_editor',
							'meta_wpautop'			=>	'text_editor',
							'meta_do_shortcode'		=>	'text_editor',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'static'				=>	'text_editor',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'text_editor', 'html', 'class_add_wrapper', 'class_remove_wrapper')
							),
							'fieldsets'				=>	array(

								// Tab: Basic
								'basic'	=>	array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'class_field_message_type', 'text_editor'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email_on')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					)
				),

				'buttons' => array(

					'label'	=> __('Buttons', 'ws-form'),
					'types' => array(

						'submit' => array (

							'label'							=>	__('Submit', 'ws-form'),
							'pro_required'					=>	!WS_Form_Common::is_edition('basic'),
							'kb_url'						=>	'/knowledgebase/submit/',
							'label_default'					=>	__('Submit', 'ws-form'),
							'label_position_force'			=>	'top',
							'mask_field'					=>	'<button type="submit" id="#id" name="#name"#attributes>#label</button>#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'#label',
							'submit_save'					=>	false,
							'submit_edit'					=>	false,
							'calc_in'						=>	true,
							'calc_out'						=>	false,
							'value_out'						=>	false,
							'progress'						=>	false,
							'conditional'					=>	array(

								'logics_enabled'		=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'		=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'		=>	'click',
							),
							'events'	=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type_primary', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'save' => array (

							'label'					=>	__('Save', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/save/',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'label_default'			=>	__('Save', 'ws-form'),
							'label_position_force'	=>	'top',
							'mask_field'			=>	'<button type="button" id="#id" name="#name" data-action="wsf-save"#attributes>#label</button>#help',
							'mask_field_attributes'	=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'		=>	'#label',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('click', 'hidden', 'mouseover', 'mouseout', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'	=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type_success', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'clear' => array (

							'label'					=>	__('Clear', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/clear/',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'label_default'			=>	__('Clear', 'ws-form'),
							'label_position_force'	=>	'top',
							'mask_field'			=>	'<button type="button" id="#id" name="#name" data-action="wsf-clear"#attributes>#label</button>#help',
							'mask_field_attributes'	=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'		=>	'#label',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'	=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'reset' => array (

							'label'							=>	__('Reset', 'ws-form'),
							'pro_required'					=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'						=>	'/knowledgebase/reset/',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'label_default'					=>	__('Reset', 'ws-form'),
							'label_position_force'			=>	'top',
							'mask_field'					=>	'<button type="reset" id="#id" name="#name" data-action="wsf-reset"#attributes>#label</button>#help',
							'mask_field_attributes'			=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'#label',
							'submit_save'					=>	false,
							'submit_edit'					=>	false,
							'value_out'						=>	false,
							'progress'						=>	false,
							'conditional'					=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'	=>	array(

								'event'						=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'				=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'tab_previous' => array (

							'label'						=>	__('Previous Tab', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'					=>	'/knowledgebase/tab_previous/',
							'icon'						=>	'previous',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Previous', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name" data-action="wsf-tab_previous"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'			=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'			=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'			=>	'click',
							),
							'events'	=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Scroll', 'ws-form'),
											'meta_keys'	=>	array('scroll_to_top', 'scroll_to_top_offset', 'scroll_to_top_duration')
										),

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'tab_next' => array (

							'label'					=>	__('Next Tab', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'				=>	'/knowledgebase/tab_next/',
							'icon'					=>	'next',
							'calc_in'				=>	true,
							'calc_out'				=>	false,
							'label_default'			=>	__('Next', 'ws-form'),
							'label_position_force'	=>	'top',
							'mask_field'			=>	'<button type="button" id="#id" name="#name" data-action="wsf-tab_next"#attributes>#label</button>#help',
							'mask_field_attributes'	=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'		=>	'#label',
							'submit_save'			=>	false,
							'submit_edit'			=>	false,
							'value_out'				=>	false,
							'progress'				=>	false,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'	=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'			=>	__('Scroll', 'ws-form'),
											'meta_keys'	=>	array('scroll_to_top', 'scroll_to_top_offset', 'scroll_to_top_duration')
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'				=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'button' => array (

							'label'						=>	__('Custom', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'					=>	'/knowledgebase/button/',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Button', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'					=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'				=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					)
				),

				'section' => array(

					'label'	=> __('Repeatable Sections', 'ws-form'),
					'types' => array(

						'section_add' => array (

							'label'						=>	__('Add', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'icon'						=>	'plus',
							'kb_url'					=>	'/knowledgebase/section_add/',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Add', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name" data-action="wsf-section-add-button"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'section_repeatable_section_id'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'					=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'				=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'section_repeatable_section_id', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'section_delete' => array (

							'label'						=>	__('Remove', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'icon'						=>	'minus',
							'kb_url'					=>	'/knowledgebase/section_delete/',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Remove', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name" data-action="wsf-section-delete-button"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'section_repeatable_section_id'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'					=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'				=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'section_repeatable_section_id', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type_danger', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'section_up' => array (

							'label'						=>	__('Move Up', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'icon'						=>	'up',
							'kb_url'					=>	'/knowledgebase/section_move_up/',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Move Up', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name" data-action="wsf-section-move-up-button"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'					=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'				=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),


						'section_down' => array (

							'label'						=>	__('Move Down', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'icon'						=>	'down',
							'kb_url'					=>	'/knowledgebase/section_move_down/',
							'calc_in'					=>	true,
							'calc_out'					=>	false,
							'label_default'				=>	__('Move Down', 'ws-form'),
							'label_position_force'		=>	'top',
							'mask_field'				=>	'<button type="button" id="#id" name="#name" data-action="wsf-section-move-down-button"#attributes>#label</button>#help',
							'mask_field_attributes'		=>	array('class', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'			=>	'#label',
							'submit_save'				=>	false,
							'submit_edit'				=>	false,
							'value_out'					=>	false,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur'),
								'actions_enabled'	=>	array('visibility', 'focus', 'blur', 'button_html', 'click', 'disabled', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'click',
							),
							'events'					=>	array(

								'event'				=>	'click',
								'event_category'	=>	__('Button', 'ws-form')
							),

							'fieldsets'				=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'class_field_button_type', 'class_field_full_button_remove')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),

						'section_icons' => array (

							'label'				=>	__('Icons', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'kb_url'			=>	'/knowledgebase/section_icons/',
							'icon'				=>	'section-icons',
							'calc_in'			=>	false,
							'calc_out'			=>	false,
							'label_default'		=>	__('Icons', 'ws-form'),
							'submit_save'		=>	false,
							'submit_edit'		=>	false,
							'value_out'			=>	false,
							'progress'			=>	false,
							'conditional'		=>	array(

								'exclude_condition'	=>	true,
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper')
							),

							'mask_field'					=>	'<div data-section-icons#attributes></div>',
							'mask_field_attributes'			=>	array('class', 'section_repeatable_section_id'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('hidden', 'section_repeatable_section_id'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Icons', 'ws-form'),
											'meta_keys'	=>	array('section_icons')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align_bottom', 'horizontal_align', 'section_icons_style', 'section_icons_size', 'section_icons_color_on', 'section_icons_color_off', 'section_icons_html_add', 'section_icons_html_delete', 'section_icons_html_move_up', 'section_icons_html_move_down', 'section_icons_html_drag')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=>	array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=>	array('disabled')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						),
					)
				),

				'ecommerce' => array(

					'label'	=> __('E-Commerce', 'ws-form'),
					'types' => array(

						'price' => array (

							'label'				=>	__('Price', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'text',
							'kb_url'			=>	'/knowledgebase/price/',
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'label_default'		=>	__('Price', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'value_out'			=>	true,
							'ecommerce_price'	=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'blank', 'blank_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'blur', 'value_number', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'				=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'		=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'					=>	'<input type="text" id="#id" name="#name" value="#value" data-ecommerce-price#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'list', 'disabled', 'readonly', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'ecommerce_price_negative', 'ecommerce_price_min', 'ecommerce_price_max', 'text_align_right', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'text_align_right', 'default_value', 'placeholder', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly', 'ecommerce_price_min', 'ecommerce_price_max', 'ecommerce_price_negative')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'		=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'price_select' => array (

							'label'				=>	__('Price Select', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'select',
							'kb_url'			=>	'/knowledgebase/price_select/',
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'label_default'		=>	__('Price Select', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_select_price'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'submit_array'		=>	true,
							'value_out'			=>	true,
							'ecommerce_price'	=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'			=>	'data_grid_select_price',
								'option_text'				=>	'select_price_field_label',
								'logics_enabled'			=>	array('selected', 'selected_not', 'selected_any', 'selected_any_not', 'selected_value_equals', 'selected_value_equals', 'focus', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'			=>	array('visibility', 'required', 'focus', 'blur', 'value_row_select', 'value_row_deselect', 'value_row_disabled', 'value_row_not_disabled', 'value_row_class_add', 'value_row_class_remove', 'value', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field', 'value_row_reset'),
								'condition_event'			=>	'change'
							),

							'events'	=>	array(

								'event'						=>	'change',
								'event_category'			=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'					=>	'<optgroup label="#group_label"#disabled>#group</optgroup>',
							'mask_group_label'				=>	'#group_label',

							// Rows
							'mask_row'						=>	'<option id="#row_id" data-id="#data_id" data-price="#select_price_field_value" value="#row_value"#attributes>#select_price_field_label</option>',
							'mask_row_value'				=>	'#select_price_field_label_html',
							'mask_row_placeholder'			=>	'<option data-id="0" value="" data-placeholder>#value</option>',
							'mask_row_attributes'			=>	array('default', 'disabled'),
							'mask_row_lookups'				=>	array('select_price_field_value', 'select_price_field_label', 'price_select_cascade_field_filter'),
							'datagrid_column_value'			=>	'select_price_field_value',
							'mask_row_default' 				=>	' selected',

							// Fields
							'mask_field'					=>	'<select id="#id" name="#name" data-ecommerce-price#attributes>#data</select>#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'size', 'multiple', 'required', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=> array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'size', 'multiple', 'placeholder_row', 'help'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Options
								'options'	=> array(

									'label'			=>	__('Options', 'ws-form'),
									'meta_keys'		=> array('data_grid_select_price', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('select_price_field_label', 'select_price_field_value')
										),

										array(
											'label'		=>	__('Cascading', 'ws-form'),
											'meta_keys'	=> array('price_select_cascade', 'price_select_cascade_field_filter', 'price_select_cascade_field_id')
										)
									)
								)
							)
						),

						'price_checkbox' => array (

							'label'				=>	__('Price Checkbox', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'checkbox',
							'kb_url'			=>	'/knowledgebase/price_checkbox/',
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'label_default'		=>	__('Price Checkbox', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_checkbox_price'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'submit_array'		=>	true,
							'value_out'			=>	true,
							'ecommerce_price'	=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'		=>	'data_grid_checkbox_price',
								'option_text'			=>	'checkbox_price_field_label',
								'logics_enabled'		=>	array('checked', 'checked_not', 'checked_any', 'checked_any_not', 'checked_value_equals', 'checked_value_equals', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'		=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper', 'value_row_check', 'value_row_uncheck', 'value_row_check_value','value_row_uncheck_value', 'value_row_focus', 'value_row_required', 'value_row_not_required', 'value_row_disabled', 'value_row_not_disabled', 'value_row_visible', 'value_row_not_visible', 'value_row_class_add', 'value_row_class_remove', 'value_row_set_custom_validity'),
								'condition_event'		=>	'change',
								'condition_event_row'	=>	true
							),
							'events'		=>	array(

								'event'					=>	'change',
								'event_category'		=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group_wrapper'		=>	'<div#attributes>#group</div>',
							'mask_group_label'			=>	'<legend>#group_label</legend>',

							// Rows
							'mask_row'					=>	'<div#attributes>#row_label</div>',
							'mask_row_attributes'		=>	array('class'),
							'mask_row_label'			=>	'<label id="#label_row_id" for="#row_id"#attributes>#row_field#checkbox_price_field_label#required</label>#invalid_feedback',
							'mask_row_label_attributes'	=>	array('class'),
							'mask_row_field'			=>	'<input type="checkbox" id="#row_id" name="#name" data-price="#checkbox_price_field_value" value="#row_value" data-ecommerce-price#attributes />',
							'mask_row_value'				=>	'#checkbox_price_field_label_html',
							'mask_row_field_attributes'	=>	array('class', 'default', 'disabled', 'required', 'aria_labelledby'),
							'mask_row_lookups'			=>	array('checkbox_price_field_value', 'checkbox_price_field_label'),
							'datagrid_column_value'		=>	'checkbox_price_field_value',
							'mask_row_default' 			=>	' checked',

							// Fields
							'mask_field'				=>	'#data#help',
							'mask_field_label'				=>	'<label id="#label_id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),
//							'mask_field_label_hide_group'	=>	true,

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render_off', 'label_position', 'label_column_width', 'hidden', 'select_all', 'select_all_label', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Layout', 'ws-form'),
											'meta_keys'	=>	array('orientation',
												'orientation_breakpoint_sizes'
											)
										),

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Checkboxes
								'checkboxes' 	=> array(

									'label'			=>	__('Checkboxes', 'ws-form'),
									'meta_keys'		=> array('data_grid_checkbox_price', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('checkbox_price_field_label', 'checkbox_price_field_value')
										)
									)
								)
							)
						),

						'price_radio' => array (

							'label'				=>	__('Price Radio', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'radio',
							'kb_url'			=>	'/knowledgebase/price_radio/',
							'calc_in'			=>	false,
							'calc_out'			=>	true,
							'label_default'		=>	__('Price Radio', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_radio_price'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'submit_array'		=>	true,
							'value_out'			=>	true,
							'ecommerce_price'	=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'data_grid_fields'			=>	'data_grid_radio_price',
								'option_text'				=>	'radio_price_field_label',
								'logics_enabled'			=>	array('checked', 'checked_not', 'checked_any', 'checked_any_not', 'checked_value_equals', 'checked_value_equals', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'			=>	array('visibility', 'required', 'class_add_wrapper', 'class_remove_wrapper', 'value_row_check', 'value_row_uncheck', 'value_row_check_value','value_row_uncheck_value', 'value_row_focus', 'value_row_disabled', 'value_row_not_disabled', 'value_row_visible', 'value_row_not_visible', 'value_row_class_add', 'value_row_class_remove', 'set_custom_validity'),
								'condition_event'			=>	'change',
								'condition_event_row'		=>	true
							),

							'events'	=>	array(

								'event'						=>	'change',
								'event_category'			=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'					=>	'<fieldset#disabled>#group_label#group</fieldset>',
							'mask_group_wrapper'			=>	'<div#attributes>#group</div>',
							'mask_group_label'				=>	'<legend>#group_label</legend>',

							// Rows
							'mask_row'						=>	'<div#attributes>#row_label</div>',
							'mask_row_attributes'			=>	array('class'),
							'mask_row_label'				=>	'<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#row_field#radio_price_field_label</label>#invalid_feedback',
							'mask_row_label_attributes'		=>	array('class'),
							'mask_row_field'				=>	'<input type="radio" id="#row_id" name="#name" data-price="#radio_price_field_value" value="#row_value" data-ecommerce-price#attributes />',
							'mask_row_value'				=>	'#radio_price_field_label_html',
							'mask_row_field_attributes'		=>	array('class', 'default', 'disabled', 'required_row', 'aria_labelledby'),
							'mask_row_lookups'				=>	array('radio_price_field_value', 'radio_price_field_label', 'price_radio_cascade_field_filter'),
							'datagrid_column_value'			=>	'radio_price_field_value',
							'mask_row_default' 				=>	' checked',

							// Fields
							'mask_field'					=>	'#data#help',
							'mask_field_attributes'			=>	array('required_attribute_no'),
							'mask_field_label'				=>	'<label id="#label_id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),
//							'mask_field_label_hide_group'	=>	true,

							'invalid_feedback_last_row'		=> true,

							'fieldsets'						=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required_attribute_no', 'hidden', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Layout', 'ws-form'),
											'meta_keys'	=>	array('orientation',
												'orientation_breakpoint_sizes'
											)
										),

										array(
											'label'			=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'	=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'		=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'			=>	__('Validation', 'ws-form'),
											'meta_keys'		=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'			=>	__('Breakpoints', 'ws-form'),
											'meta_keys'		=> array('breakpoint_sizes'),
											'class'			=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Radios
								'radios'	=> array(

									'label'			=>	__('Radios', 'ws-form'),
									'meta_keys'		=> array('data_grid_radio_price', 'data_grid_rows_randomize'),
									'fieldsets' => array(

										array(
											'label'		=>	__('Column Mapping', 'ws-form'),
											'meta_keys'	=> array('radio_price_field_label', 'radio_price_field_value')
										),

										array(
											'label'		=>	__('Cascading', 'ws-form'),
											'meta_keys'	=> array('price_radio_cascade', 'price_radio_cascade_field_filter', 'price_radio_cascade_field_id')
										)
									)
								)
							)
						),

						'price_range' => array (

							'label'				=>	__('Price Range', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'range',
							'kb_url'			=>	'/knowledgebase/price_range/',
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'label_default'		=>	__('Price Range', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'value_out'			=>	true,
							'ecommerce_price'	=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input'),
								'actions_enabled'	=>	array('visibility', 'focus', 'blur', 'value_range', 'disabled', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-range',
							'events'						=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),
							'trigger'			=> 'input',

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'			=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'	=>	array('datalist_field_value', 'datalist_field_text'),

							// Fields
							'mask_field'					=>	'<input type="range" id="#id" name="#name" value="#value" data-ecommerce-price#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'list', 'min', 'max', 'step', 'disabled', 'aria_describedby', 'aria_labelledby', 'aria_label', 'custom_attributes', 'class_fill_lower_track'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'hidden', 'default_value_price_range', 'help_price_range'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'		=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align', 'class_fill_lower_track')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'min', 'max', 'step')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Tab: Tick Marks
								'tickmarks'	=> array(

									'label'		=>	__('Tick Marks', 'ws-form'),
									'meta_keys'	=>	array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'quantity' => array (

							'label'				=>	__('Quantity', 'ws-form'),
							'pro_required'		=>	!WS_Form_Common::is_edition('pro'),
							'icon'				=>	'quantity',
							'kb_url'			=>	'/knowledgebase/quantity/',
							'calc_in'			=>	true,
							'calc_out'			=>	true,
							'label_default'		=>	__('Quantity', 'ws-form'),
							'data_source'		=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'		=>	true,
							'submit_edit'		=>	false,
							'value_out'			=>	true,
							'ecommerce_quantity'=>	true,
							'progress'			=>	true,
							'conditional'		=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'blank', 'blank_not', 'field_match', 'field_match_not', 'validate', 'validate_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change', 'input', 'change_input', 'keyup', 'keydown'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'blur', 'value_number', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'compatibility_id'	=>	'input-number',
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'					=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'				=> true,

							// Rows
							'mask_row'						=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'				=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'			=>	'datalist_field_value',

							// Fields
							'mask_field'					=>	'<input type="number" id="#id" name="#name" value="#value" data-ecommerce-quantity#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'			=>	array('class', 'list', 'disabled', 'readonly', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'ecommerce_quantity_min', 'max', 'ecommerce_field_id', 'text_align_center', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'text_align_center', 'ecommerce_field_id', 'ecommerce_quantity_default_value', 'placeholder', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly', 'ecommerce_quantity_min', 'max')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'		=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'price_subtotal' => array (

							'label'						=>	__('Price Subtotal', 'ws-form'),
							'pro_required'				=>	!WS_Form_Common::is_edition('pro'),
							'icon'						=>	'calculator',
							'kb_url'					=>	'/knowledgebase/price_subtotal/',
							'calc_in'					=>	false,
							'calc_out'					=>	true,
							'label_default'				=>	__('Price Subtotal', 'ws-form'),
							'submit_save'				=>	true,
							'submit_edit'				=>	false,
							'value_out'					=>	true,
							'ecommerce_price_subtotal'	=>	true,
							'progress'					=>	false,
							'conditional'				=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'field_match', 'field_match_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change'),
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),

							// Fields
							'mask_field'					=>	'<input type="text" id="#id" name="#name" data-ecommerce-price-subtotal readonly#attributes />',
							'mask_field_attributes'			=>	array('class', 'aria_describedby', 'aria_labelledby', 'aria_label', 'ecommerce_field_id', 'text_align_right', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'hidden', 'text_align_right', 'ecommerce_field_id'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)

									)
								)
							)
						),

						'cart_price' => array (

							'label'					=>	__('Cart Detail', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'icon'					=>	'price',
							'kb_url'				=>	'/knowledgebase/cart_price/',
							'calc_in'				=>	true,
							'calc_out'				=>	true,
							'label_default'			=>	__('Cart Detail', 'ws-form'),
							'data_source'			=>	array('type' => 'data_grid', 'id' => 'data_grid_datalist'),
							'submit_save'			=>	true,
							'submit_edit'			=>	false,
							'value_out'				=>	true,
							'ecommerce_cart_price'	=>	true,
							'progress'				=>	true,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'blank', 'blank_not', 'field_match', 'field_match_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change'),
								'actions_enabled'	=>	array('visibility', 'required', 'focus', 'blur', 'value_number', 'disabled', 'readonly', 'set_custom_validity', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),
							'events'			=>	array(

								'event'				=>	'change input',
								'event_category'	=>	__('Field', 'ws-form')
							),

							// Groups
							'mask_group'		=>	"\n\n<datalist id=\"#group_id\">#group</datalist>",
							'mask_group_always'	=> true,

							// Rows
							'mask_row'				=>	'<option value="#datalist_field_value">#datalist_field_text</option>',
							'mask_row_lookups'		=>	array('datalist_field_value', 'datalist_field_text'),
							'datagrid_column_value'	=>	'datalist_field_value',

							// Fields
							'mask_field'									=>	'<input type="text" id="#id" name="#name" value="#value" data-ecommerce-cart-price#attributes />#data#invalid_feedback#help',
							'mask_field_attributes'				=>	array('class', 'list', 'disabled', 'readonly_on', 'required', 'placeholder', 'aria_describedby', 'aria_labelledby', 'aria_label', 'ecommerce_price_negative', 'ecommerce_price_min', 'ecommerce_price_max', 'ecommerce_cart_price_type', 'text_align_right', 'custom_attributes'),
							'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render', 'label_position', 'label_column_width', 'required', 'hidden', 'text_align_right', 'ecommerce_cart_price_type', 'default_value', 'placeholder', 'help'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array('disabled', 'readonly_on', 'ecommerce_price_min', 'ecommerce_price_max', 'ecommerce_price_negative')
										),

										array(
											'label'		=>	__('Validation', 'ws-form'),
											'meta_keys'	=>	array('invalid_feedback_render', 'invalid_feedback')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								),

								// Datalist
								'datalist'	=> array(

									'label'			=>	__('Datalist', 'ws-form'),
									'meta_keys'		=> array('data_grid_datalist'),
									'fieldsets' => array(

										array(
											'label' => __('Column Mapping', 'ws-form'),
											'meta_keys' => array('datalist_field_text', 'datalist_field_value')
										)
									)
								)
							)
						),

						'cart_total' => array (

							'label'					=>	__('Cart Total', 'ws-form'),
							'pro_required'			=>	!WS_Form_Common::is_edition('pro'),
							'icon'					=>	'calculator',
							'kb_url'				=>	'/knowledgebase/cart_total/',
							'calc_in'				=>	false,
							'calc_out'				=>	true,
							'label_default'			=>	__('Cart Total', 'ws-form'),
							'submit_save'			=>	true,
							'submit_edit'			=>	false,
							'value_out'				=>	true,
							'ecommerce_cart_total'	=>	true,
							'progress'				=>	false,
							'conditional'			=>	array(

								'logics_enabled'	=>	array('==', '!=', '<', '>', '<=', '>=', 'field_match', 'field_match_not', 'click', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'touchstart', 'touchend', 'touchmove', 'touchcancel', 'focus', 'blur', 'change'),
								'actions_enabled'	=>	array('visibility', 'class_add_wrapper', 'class_remove_wrapper', 'class_add_field', 'class_remove_field'),
								'condition_event'	=>	'change input'
							),

							// Fields
							'mask_field'					=>	'<input type="text" id="#id" name="#name" data-ecommerce-cart-total readonly#attributes />',
							'mask_field_attributes'			=>	array('class', 'aria_describedby', 'aria_labelledby', 'aria_label', 'text_align_right', 'custom_attributes'),
							'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
							'mask_field_label_attributes'	=>	array('class'),

							'fieldsets'	=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'		=>	array('label_render', 'label_position', 'label_column_width', 'hidden', 'text_align_right'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Accessibility', 'ws-form'),
											'meta_keys'	=>	array('aria_label')
										),

										array(
											'label'		=>	__('Exclusions', 'ws-form'),
											'meta_keys'	=>	array('exclude_email')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=>	array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'		=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'		=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_field_wrapper', 'class_field')
										),

										array(
											'label'		=>	__('Custom Attributes', 'ws-form'),
											'meta_keys'	=>	array('custom_attributes')
										),

										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					)
				)
			);

			// Apply filter
			$field_types = apply_filters('wsf_config_field_types', $field_types);

			// Add icons and compatibility links
			if(!$public) {

				foreach($field_types as $group_key => $group) {

					$types = $group['types'];

					foreach($types as $field_key => $field_type) {

						// Set icons (If not already an SVG)
						$field_icon = isset($field_type['icon']) ? $field_type['icon'] : $field_key;
						if(strpos($field_icon, '<svg') === false) {

							$field_types[$group_key]['types'][$field_key]['icon'] = self::get_icon_16_svg($field_icon);
						}

						// Set compatibility
						if(isset($field_type['compatibility_id'])) {

							$field_types[$group_key]['types'][$field_key]['compatibility_url'] = str_replace('#compatibility_id', $field_type['compatibility_id'], WS_FORM_COMPATIBILITY_MASK);
							unset($field_types[$group_key]['types'][$field_key]['compatibility_id']);
						}
					}
				}
			}

			// Cache
			self::$field_types[$public] = $field_types;

			return $field_types;
		}

		// Configuration - Get field types public
		public static function get_field_types_public($field_types_filter) {

			$field_types = self::get_field_types_flat(true);

			// Filter by fields found in forms
			if(count($field_types_filter) > 0) {

				$field_types_old = $field_types;
				$field_types = array();

				foreach($field_types_filter as $field_type) {

					if(isset($field_types_old[$field_type])) { $field_types[$field_type] = $field_types_old[$field_type]; }
				}
	
			} else {

				return $field_types;	
			}

			// Strip attributes
			$public_attributes_strip = array('label' => false, 'label_default' => false, 'submit_edit' => false, 'conditional' => array('logics_enabled', 'actions_enabled'), 'compatibility_id' => false, 'kb_url' => false, 'fieldsets' => false, 'pro_required' => false);

			foreach($field_types as $key => $field) {

				foreach($public_attributes_strip as $attribute_strip => $attributes_strip_sub) {

					if(isset($field_types[$key][$attribute_strip])) {

						if(is_array($attributes_strip_sub)) {

							foreach($attributes_strip_sub as $attribute_strip_sub) {

								if(isset($field_types[$key][$attribute_strip][$attribute_strip_sub])) {

									unset($field_types[$key][$attribute_strip][$attribute_strip_sub]);
								}
							}

						} else {

							unset($field_types[$key][$attribute_strip]);
						}
					}
				}
			}

			return $field_types;
		}

		// Configuration - Field types (Single dimension array)
		public static function get_field_types_flat($public = true) {

			$field_types = array();
			$field_types_config = self::get_field_types($public);

			foreach($field_types_config as $group) {

				$types = $group['types'];

				foreach($types as $key => $field_type) {

					$field_types[$key] = $field_type;
				}
			}

			return $field_types;
		}

		// Configuration - Customize
		public static function get_customize() {

			$customize	=	array(

				'colors'	=>	array(

					'heading'	=>	__('Colors', 'ws-form'),
					'fields'	=>	array(

						'skin_color_default'	=> array(

							'label'			=>	__('Default', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#000000',
							'description'	=>	__('Labels and field values.', 'ws-form')
						),

						'skin_color_default_inverted'	=> array(

							'label'			=>	__('Inverted', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#FFFFFF',
							'description'	=>	__('Field backgrounds and button text.', 'ws-form')
						),

						'skin_color_default_light'	=> array(

							'label'			=>	__('Light', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#8E8E93',
							'description'	=>	__('Placeholders, help text, and disabled field values.', 'ws-form')
						),

						'skin_color_default_lighter'	=> array(

							'label'			=>	__('Lighter', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#CECED2',
							'description'	=>	__('Field borders and buttons.', 'ws-form')
						),

						'skin_color_default_lightest'	=> array(

							'label'			=>	__('Lightest', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#EFEFF4',
							'description'	=>	__('Range slider backgrounds, progress bar backgrounds, and disabled field backgrounds.', 'ws-form')
						),

						'skin_color_primary'	=> array(

							'label'			=>	__('Primary', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#205493',
							'description'	=>	__('Checkboxes, radios, range sliders, progress bars, and submit buttons.')
						),

						'skin_color_secondary'	=> array(

							'label'			=>	__('Secondary', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#5b616b',
							'description'	=>	__('Secondary elements such as a reset button.', 'ws-form')
						),

						'skin_color_success'	=> array(

							'label'			=>	__('Success', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#2e8540',
							'description'	=>	__('Completed progress bars, save buttons, and success messages.')
						),

						'skin_color_information'	=> array(

							'label'			=>	__('Information', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#02bfe7',
							'description'	=>	__('Information messages.', 'ws-form')
						),

						'skin_color_warning'	=> array(

							'label'			=>	__('Warning', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#fdb81e',
							'description'	=>	__('Warning messages.', 'ws-form')
						),

						'skin_color_danger'	=> array(

							'label'			=>	__('Danger', 'ws-form'),
							'type'			=>	'color',
							'default'		=>	'#981b1e',
							'description'	=>	__('Required field labels, invalid field borders, invalid feedback text, remove repeatable section buttons, and danger messages.')
						)
					)
				),

				'typography'	=>	array(

					'heading'		=>	__('Typography', 'ws-form'),
					'fields'		=>	array(

						'skin_font_family'	=> array(

							'label'			=>	__('Font Family', 'ws-form'),
							'type'			=>	'text',
							'default'		=>	'inherit',
							'description'	=>	__('Font family used throughout the form.', 'ws-form')
						),

						'skin_font_size'	=> array(

							'label'			=>	__('Font Size', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	14,
							'description'	=>	__('Regular font size used on the form.', 'ws-form')
						),

						'skin_font_size_large'	=> array(

							'label'			=>	__('Font Size Large', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	25,
							'description'	=>	__('Font size used for section labels and fieldset legends.', 'ws-form')
						),

						'skin_font_size_small'	=> array(

							'label'			=>	__('Font Size Small', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	12,
							'description'	=>	__('Font size used for help text and invalid feedback text.', 'ws-form')
						),

						'skin_font_weight'	=>	array(

							'label'			=>	__('Font Weight', 'ws-form'),
							'type'			=>	'select',
							'default'		=>	'inherit',
							'choices'		=>	array(

								'inherit'	=>	__('Inherit', 'ws-form'),
								'normal'	=>	__('Normal', 'ws-form'),
								'bold'		=>	__('Bold', 'ws-form'),
								'100'		=>	__('100', 'ws-form'),
								'200'		=>	__('200', 'ws-form'),
								'300'		=>	__('300', 'ws-form'),
								'400'		=>	__('400 (Normal)', 'ws-form'),
								'500'		=>	__('500', 'ws-form'),
								'600'		=>	__('600', 'ws-form'),
								'700'		=>	__('700 (Bold)', 'ws-form'),
								'800'		=>	__('800', 'ws-form'),
								'900'		=>	__('900', 'ws-form')
							),
							'description'	=>	__('Font weight used throughout the form.', 'ws-form')
						),


						'skin_line_height'	=> array(

							'label'			=>	__('Line Height', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	1.4,
							'description'	=>	__('Line height used throughout form.', 'ws-form')
						)
					)
				),

				'borders'	=>	array(

					'heading'		=>	__('Borders', 'ws-form'),
					'fields'		=>	array(

						'skin_border'	=>	array(

							'label'			=>	__('Enabled', 'ws-form'),
							'type'			=>	'checkbox',
							'default'		=>	true,
							'description'	=>	__('When checked, borders will be shown.', 'ws-form')
							),

						'skin_border_width'	=> array(

							'label'			=>	__('Width', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	1,
							'description'	=>	__('Specify the width of borders used through the form. For example, borders around form fields.', 'ws-form')
						),

						'skin_border_style'	=>	array(

							'label'			=>	__('Style', 'ws-form'),
							'type'			=>	'select',
							'default'		=>	'solid',
							'choices'		=>	array(

								'dashed'	=>	__('Dashed', 'ws-form'),
								'dotted'	=>	__('Dotted', 'ws-form'),
								'double'	=>	__('Double', 'ws-form'),
								'groove'	=>	__('Groove', 'ws-form'),
								'inset'		=>	__('Inset', 'ws-form'),
								'outset'	=>	__('Outset', 'ws-form'),
								'ridge'		=>	__('Ridge', 'ws-form'),
								'solid'		=>	__('Solid', 'ws-form')
							),
							'description'	=>	__('Border style used throughout the form.', 'ws-form')
						),

						'skin_border_radius'	=> array(

							'label'			=>	__('Radius', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	4,
							'description'	=>	__('Border radius used throughout the form.', 'ws-form')
						)
					)
				),

				'transitions'	=>	array(

					'heading'	=>	__('Transitions', 'ws-form'),
					'fields'	=>	array(

						'skin_transition'	=>	array(

							'label'			=>	__('Enabled', 'ws-form'),
							'type'			=>	'checkbox',
							'default'		=>	true,
							'description'	=>	__('When checked, transitions will be used on the form.', 'ws-form')
						),

						'skin_transition_speed'	=> array(

							'label'			=>	__('Speed', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	200,
							'help'			=>	__('Value in milliseconds.', 'ws-form'),
							'description'	=>	__('Transition speed in milliseconds.', 'ws-form')
						),

						'skin_transition_timing_function'	=>	array(

							'label'			=>	__('Timing Function', 'ws-form'),
							'type'			=>	'select',
							'default'		=>	'ease-in-out',
							'choices'		=>	array(

								'ease'			=>	__('Ease', 'ws-form'),
								'ease-in'		=>	__('Ease In', 'ws-form'),
								'ease-in-out'	=>	__('Ease In Out', 'ws-form'),
								'ease-out'		=>	__('Ease Out', 'ws-form'),
								'linear'		=>	__('Linear', 'ws-form'),
								'step-end'		=>	__('Step End', 'ws-form'),
								'step-start'	=>	__('Step Start', 'ws-form')
							),
							'description'	=>	__('Speed curve of the transition effect.', 'ws-form')
						)
					)
				),

				'advanced'	=>	array(

					'heading'	=>	__('Advanced', 'ws-form'),
					'fields'	=>	array(

						'skin_grid_gutter'	=> array(

							'label'			=>	__('Grid Gutter', 'ws-form'),
							'type'			=>	'number',
							'default'		=>	20,
							'description'	=>	__('Sets the distance between form elements.', 'ws-form')
						)
					)
				)
			);

			// Apply filter
			$customize = apply_filters('wsf_config_customize', $customize);

			return $customize;
		}

		// Configuration - Options
		public static function get_options() {

			$options_v_1_0_0 = array(

				// Appearance
				'appearance'		=> array(

					'label'		=>	__('Appearance', 'ws-form'),
					'groups'	=>	array(

						'framework'	=>	array(

							'heading'		=>	__('Framework', 'ws-form'),
							'fields'	=>	array(

								'framework'	=> array(

									'label'				=>	__('Framework', 'ws-form'),
									'type'				=>	'select',
									'help'				=>	__('Framework used for rendering the front-end HTML.', 'ws-form'),
									'options'			=>	array(),	// Populated below
									'default'			=>	WS_FORM_DEFAULT_FRAMEWORK,
									'button'			=>	'wsf-framework-detect',
									'public'			=>	true,
									'data_change'		=>	'reload' 				// Reload settings on change
								)
							)
						),

						'preview'	=>	array(

							'heading'		=>	__('Preview', 'ws-form'),
							'fields'	=>	array(

								'helper_live_preview'	=>	array(

									'label'		=>	__('Live Preview', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Update the form preview window automatically.', 'ws-form'),
									'default'	=>	true
								),

								'preview_template'	=> array(

									'label'				=>	__('Template', 'ws-form'),
									'type'				=>	'select',
									'help'				=>	__('Page template used for previewing forms.', 'ws-form'),
									'options'			=>	array(),	// Populated below
									'default'			=>	''
								)
							)
						),

						'public'	=>	array(

							'heading'		=>	__('Public', 'ws-form'),
							'fields'	=>	array(

								'css_layout'	=>	array(

									'label'		=>	__('Framework CSS', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Should the WS Form framework CSS be rendered?', 'ws-form'),
									'default'	=>	true,
									'public'	=>	true,
									'condition'	=>	array('framework' => 'ws-form')
								),

								'css_skin'	=>	array(

									'label'		=>	__('Skin CSS', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	sprintf(__('Should the WS Form skin CSS be rendered? <a href="%s">Click here</a> to customize the WS Form skin.', 'ws-form'), admin_url('customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dws-form-settings%26tab%3Dappearance')),
									'default'	=>	true,
									'public'	=>	true,
									'condition'	=>	array('framework' => 'ws-form')
								),

								'css_minify'	=>	array(

									'label'		=>	__('Minify CSS', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Should the WS Form CSS be minified to improve page speed?', 'ws-form'),
									'default'	=>	'',
									'condition'	=>	array('framework' => 'ws-form')
								),

								'css_inline'	=>	array(

									'label'		=>	__('Inline CSS', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Should the WS Form CSS be rendered inline to improve page speed?', 'ws-form'),
									'default'	=>	'',
									'condition'	=>	array('framework' => 'ws-form')
								),

								'css_cache_duration'	=>	array(

									'label'		=>	__('CSS Cache Duration', 'ws-form'),
									'type'		=>	'number',
									'help'		=>	__('Expires header duration in seconds for WS Form CSS.', 'ws-form'),
									'default'	=>	31536000,
									'public'	=>	true,
									'condition'	=>	array('framework' => 'ws-form')
								),

								'comments_css'	=>	array(

									'label'		=>	__('CSS Comments', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Should WS Form CSS include comments?', 'ws-form'),
									'default'	=>	false,
									'public'	=>	true,
									'condition'	=>	array('framework' => 'ws-form')
								),

								'comments_html'	=>	array(

									'label'		=>	__('HTML Comments', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Should WS Form HTML include comments?', 'ws-form'),
									'default'	=>	false,
									'public'	=>	true
								)
							)
						)
					)
				),

				// Advanced
				'advanced'	=> array(

					'label'		=>	__('Advanced', 'ws-form'),
					'groups'	=>	array(

						'helpers'	=>	array(

							'heading'	=>	__('Helpers', 'ws-form'),
							'fields'	=>	array(

								'helper_debug'	=> array(

									'label'		=>	__('Debug Console', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('Choose when to show the debug console.', 'ws-form'),
									'default'	=>	'',
									'options'	=>	array(

										'off'				=>	array('text' => __('Off', 'ws-form')),
										'administrator'		=>	array('text' => __('Administrators only', 'ws-form')),
										'on'				=>	array('text' => __('Show always'), 'ws-form')
									),
									'mode'	=>	array(

										'basic'		=>	'off',
										'advanced'	=>	'administrator'
									)
								),
								'helper_columns'	=>	array(

									'label'		=>	__('Column Guidelines', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('Show column guidelines when editing forms?', 'ws-form'),
									'options'	=>	array(

										'off'		=>	array('text' => __('Off', 'ws-form')),
										'resize'	=>	array('text' => __('On resize', 'ws-form')),
										'on'		=>	array('text' => __('Always on', 'ws-form')),
									),
									'default'	=>	'resize'
								),
								'helper_breakpoint_width'	=>	array(

									'label'		=>	__('Breakpoint Widths', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Resize the width of the form to the selected breakpoint.', 'ws-form'),
									'default'	=>	true
								),
								'helper_compatibility' => array(

									'label'		=>	__('HTML Compatibility Helpers', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Render HTML compatibility helper links (Data from', 'ws-form') . ' <a href="' . WS_FORM_COMPATIBILITY_URL . '" target="_blank">' . WS_FORM_COMPATIBILITY_NAME . '</a>).',
									'default'	=>	false,
									'mode'		=>	array(

										'basic'		=>	false,
										'advanced'	=>	true
									)
								),

								'helper_field_help' => array(

									'label'		=>	__('Help Text', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Render help text in sidebar.'),
									'default'	=>	true
								),

								'helper_section_id'	=> array(

									'label'		=>	__('Section IDs', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Show IDs on sections?', 'ws-form'),
									'default'	=>	true,
									'mode'		=>	array(

										'basic'		=>	false,
										'advanced'	=>	true
									)
								),

								'helper_field_id'	=> array(

									'label'		=>	__('Field IDs', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Show IDs on fields? Useful for #field(nnn) variables.', 'ws-form'),
									'default'	=>	true
								),

								'mode'	=> array(

									'label'		=>	__('Mode', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('Selecting advanced mode will enable more features for developers.', 'ws-form'),
									'default'	=>	'basic',
									'options'	=>	array(

										'basic'		=>	array('text' => __('Basic', 'ws-form')),
										'advanced'	=>	array('text' => __('Advanced', 'ws-form'))
									)
								)
							)
						),

						'api'	=>	array(

							'heading'	=>	__('API', 'ws-form'),
							'fields'	=>	array(

								'ajax_http_method_override' => array(

									'label'		=>	__('Use X-HTTP-Method-Override for API Requests', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('Useful if your hosting provider does not support DELETE or PUT methods.', 'ws-form'),
									'default'	=>	true,
									'public'	=>	true
								)
							)
						),

						'admin'	=>	array(

							'heading'	=>	__('Administration', 'ws-form'),
							'fields'	=>	array(

								'disable_form_stats'			=>	array(

									'label'		=>	__('Disable Statistics', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false,
									'help'		=>	__('If checked, WS Form will stop gathering statistical data about forms.', 'ws-form'),
								),

								'disable_count_submit_unread'	=>	array(

									'label'		=>	__('Disable Unread Submission Bubbles', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false
								),

								'disable_toolbar_menu'			=>	array(

									'label'		=>	__('Disable Toolbar Menu', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false,
									'help'		=>	__('If checked, the WS Form toolbar menu will not be shown.', 'ws-form'),
								)
							)
						),

						'cookie'	=>	array(

							'heading'	=>	__('Cookies', 'ws-form'),
							'fields'	=>	array(

								'cookie_timeout'	=>	array(

									'label'		=>	__('Cookie Timeout (Seconds)', 'ws-form'),
									'type'		=>	'number',
									'help'		=>	__('Duration in seconds cookies are valid for.', 'ws-form'),
									'default'	=>	60 * 60 * 24 * 28,	// 28 day
									'public'	=>	true
								),

								'cookie_prefix'	=>	array(

									'label'		=>	__('Cookie Prefix', 'ws-form'),
									'type'		=>	'text',
									'help'		=>	__('We recommend leaving this value as it is.', 'ws-form'),
									'default'	=>	WS_FORM_IDENTIFIER,
									'public'	=>	true
								)
							)
						),
						'upload'	=>	array(

							'heading'	=>	__('File Uploads', 'ws-form'),
							'fields'	=>	array(

								'max_upload_size'	=>	array(

									'label'		=>	__('Maximum Filesize (Bytes)', 'ws-form'),
									'type'		=>	'number',
									'default'	=>	'#max_upload_size',
									'minimum'	=>	0,
									'maximum'	=>	'#max_upload_size',
									'button'	=>	'wsf-max-upload-size'
								),

								'max_uploads'	=>	array(

									'label'		=>	__('Maximum Files', 'ws-form'),
									'type'		=>	'number',
									'default'	=>	'#max_uploads',
									'minimum'	=>	0,
									'maximum'	=>	'#max_uploads',
									'button'	=>	'wsf-max-uploads'
								)
							)
						),

						'lookup'	=>	array(

							'heading'	=>	__('Tracking Lookups', 'ws-form'),
							'fields'	=>	array(

								'ip_lookup_url_mask' => array(

									'label'		=>	__('IP Lookup URL Mask', 'ws-form'),
									'type'		=>	'text',
									'default'	=>	'https://whatismyipaddress.com/ip/#value',
									'help'		=>	__('#value will be replaced with the tracking IP address.', 'ws-form')
								),

								'latlon_lookup_url_mask' => array(

									'label'		=>	__('Geolocation Lookup URL Mask', 'ws-form'),
									'type'		=>	'text',
									'default'	=>	'https://www.google.com/search?q=#value',
									'help'		=>	__('#value will be replaced with latitude,longitude.', 'ws-form')
								)
							)
						),

						'javascript'	=>	array(

							'heading'	=>	__('Javascript', 'ws-form'),
							'fields'	=>	array(

								'jquery_footer'	=>	array(

									'label'		=>	__('Enqueue in Footer', 'ws-form'),
									'type'		=>	'checkbox',
									'help'		=>	__('If checked, scripts will be enqueued in the footer.', 'ws-form'),
									'default'	=>	''
								),

								'jquery_source'	=>	array(

									'label'		=>	__('jQuery Source', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('Where should external libraries load from? Use \'Local\' if you are using optimization plugins.', 'ws-form'),
									'default'	=>	'local',
									'public'	=>	true,
									'options'	=>	array(

										'local'		=>	array('text' => __('Local', 'ws-form')),
										'cdn'		=>	array('text' => __('CDN', 'ws-form'))
									)
								),

								'ui_datepicker'	=>	array(

									'label'		=>	__('jQuery Date/Time Picker', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('When should date fields use a jQuery Date/Time Picker component?', 'ws-form'),
									'default'	=>	'on',
									'public'	=>	true,
									'options'	=>	array(

										'on'		=>	array('text' => __('Always', 'ws-form')),
										'native'	=>	array('text' => __('If native not available', 'ws-form')),
										'off'		=>	array('text' => __('Never', 'ws-form'))
									)
								),

								'ui_color'	=>	array(

									'label'		=>	__('jQuery Color Picker', 'ws-form'),
									'type'		=>	'select',
									'help'		=>	__('When should color fields use a jQuery Color picker component?', 'ws-form'),
									'default'	=>	'on',
									'public'	=>	true,
									'options'	=>	array(

										'native'	=>	array('text' => __('If native not available', 'ws-form')),
										'on'		=>	array('text' => __('Always', 'ws-form')),
										'off'		=>	array('text' => __('Never', 'ws-form'))
									)
								),
							)
						),

						'framework'	=>	array(

							'heading'		=>	__('Framework', 'ws-form'),
							'fields'	=>	array(

								'framework_column_count'	=> array(

									'label'		=>	__('Column Count', 'ws-form'),
									'type'		=>	'select_number',
									'default'	=>	12,
									'minimum'	=>	1,
									'maximum'	=>	24,
									'public'	=>	true,
									'absint'	=>	true,
									'help'		=>	__('We recommend leaving this setting at 12.', 'ws-form')
								)
							)
						),
					)
				),

				// E-Commerce
				'ecommerce'	=> array(

					'label'		=>	__('E-Commerce', 'ws-form'),
					'groups'	=>	array(

						'price'	=>	array(

							'heading'	=>	__('Prices', 'ws-form'),
							'fields'	=>	array(

								'currency'	=> array(

									'label'		=>	__('Currency', 'ws-form'),
									'type'		=>	'select',
									'default'	=>	WS_Form_Common::get_currency_default(),
									'options'	=>	array(),
									'public'	=>	true
								),

								'currency_position'	=> array(

									'label'		=>	__('Currency Position', 'ws-form'),
									'type'		=>	'select',
									'default'	=>	'left',
									'options'	=>	array(
										'left'			=>	array('text' => __('Left', 'ws-form')),
										'right'			=>	array('text' => __('Right', 'ws-form')),
										'left_space'	=>	array('text' => __('Left with space', 'ws-form')),
										'right_space'	=>	array('text' => __('Right with space', 'ws-form'))
									),
									'public'	=>	true
								),

								'price_thousand_separator'	=> array(

									'label'		=>	__('Thousand Separator', 'ws-form'),
									'type'		=>	'text',
									'default'	=>	',',
									'public'	=>	true
								),

								'price_decimal_separator'	=> array(

									'label'		=>	__('Decimal Separator', 'ws-form'),
									'type'		=>	'text',
									'default'	=>	'.',
									'public'	=>	true
								),

								'price_decimals'	=> array(

									'label'		=>	__('Number Of Decimals', 'ws-form'),
									'type'		=>	'number',
									'default'	=>	'2',
									'public'	=>	true
								)
							)
						)
					)
				),
				// System
				'system'	=> array(

					'label'		=>	__('System', 'ws-form'),
					'fields'	=>	array(

						'system' => array(

							'label'		=>	__('System Report', 'ws-form'),
							'type'		=>	'static'
						),

						'setup'	=> array(

							'type'		=>	'hidden',
							'default'	=>	false
						)
					)
				),
				// License
				'license'	=> array(

					'label'		=>	__('License', 'ws-form'),
					'fields'	=>	array(

						'version'	=>	array(

							'label'		=>	__('Version', 'ws-form'),
							'type'		=>	'static'						),

						'license_key'	=>	array(

							'label'		=>	__('License Key', 'ws-form'),
							'type'		=>	'text',
							'help'		=>	sprintf(__('Enter your %1$s license key here. If you have an All Access key, please enter the %1$s key instead.', 'ws-form'), WS_FORM_NAME_PRESENTABLE),
							'button'	=>	'wsf-license'
						),

						'license_status'	=>	array(

							'label'		=>	__('License Status', 'ws-form'),
							'type'		=>	'static'
						)
					)
				),
				// Data
				'data'	=> array(

					'label'		=>	__('Data', 'ws-form'),
					'groups'	=>	array(

						'encryption'	=>	array(

							'heading'	=>	__('Encryption', 'ws-form'),
							'fields'	=>	array(

								'encryption_enabled' => array(

									'label'		=>	__('Enable Data Encryption', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false
								),

								'encryption_status' => array(

									'label'		=>	__('Encryption Status', 'ws-form'),
									'type'		=>	'static'
								)
							)
						),
						'uninstall'	=>	array(

							'heading'	=>	__('Uninstall', 'ws-form'),
							'fields'	=>	array(

								'uninstall_options' => array(

									'label'		=>	__('Delete Plugin Settings on Uninstall', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false
								),

								'uninstall_database' => array(

									'label'		=>	__('Delete Database Tables on Uninstall', 'ws-form'),
									'type'		=>	'checkbox',
									'default'	=>	false
								)
							)
						)
					)
				)
			);
			$options = $options_v_1_0_0;

			// Frameworks
			$frameworks = self::get_frameworks(false);
			foreach($frameworks['types'] as $key => $framework) {

				$name = $framework['name'];
				$options['appearance']['groups']['framework']['fields']['framework']['options'][$key] = array('text' => $name);
			}

			// Templates
			$options['appearance']['groups']['preview']['fields']['preview_template']['options'][''] = array('text' => __('Automatic', 'ws-form'));

			// Custom page templates
			$page_templates = array();
			$templates_path = get_template_directory();
			$templates = wp_get_theme()->get_page_templates();
			$templates['page.php'] = 'Page';
			$templates['singular.php'] = 'Singular';
			$templates['index.php'] = 'Index';
			$templates['front-page.php'] = 'Front Page';
			$templates['single-post.php'] = 'Single Post';
			$templates['single.php'] = 'Single';
			$templates['home.php'] = 'Home';

			foreach($templates as $template_file => $template_title) {

				// Build template path
				$template_file_full = $templates_path . '/' . $template_file;

				// Skip files that don't exist
				if(!file_exists($template_file_full)) { continue; }

				$page_templates[$template_file] = $template_title . ' (' . $template_file . ')';
			}

			asort($page_templates);

			foreach($page_templates as $template_file => $template_title) {

				$options['appearance']['groups']['preview']['fields']['preview_template']['options'][$template_file] = array('text' => $template_title);
			}

			// Currencies
			$currencies = self::get_currencies();
			foreach($currencies as $currency) {

				$options['ecommerce']['groups']['price']['fields']['currency']['options'][$currency['code']] = array('text' => $currency['name'] . ' (' . $currency['symbol'] . ')');
			}
			// Apply filter
			$options = apply_filters('wsf_config_options', $options);

			return $options;
		}

		// Configuration - Settings - Admin
		public static function get_settings_form_admin() {

			// Check cache
			if(self::$settings_form_admin !== false) { return self::$settings_form_admin; }

			$settings_form_admin = array(

				'sidebars'	=> array(

					// Toolbox
					'toolbox'	=> array(

						'label'		=>	__('Toolbox', 'ws-form'),
						'icon'		=>	'tools',
						'buttons'	=>	array(

							array(

								'label' 	=> __('Close', 'ws-form'),
								'action' 	=> 'wsf-sidebar-cancel'
							)
						),
						'static'	=>	true,
						'nav'		=>	true,
						'expand'	=>	false,

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'field-selector'	=>	array(

									'label'		=> __('Fields', 'ws-form'),
									'meta_keys'	=>	array('field_select')
								),

								'form-history'	=>	array(

									'label'		=>	__('Undo', 'ws-form'),
									'meta_keys'	=>	array('form_history')
								)
							)
						)
					),

					// Conditional
					'conditional'	=> array(

						'label'		=>	__('Conditional Logic', 'ws-form'),
						'icon'		=>	'conditional',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	true,
						'expand'	=>	true,
						'kb_url'	=>	'/knowledgebase/conditional-logic/',

						'meta'	=>	array(

							'fieldsets'	=>	array(

								'conditional'	=>	array(

									'meta_keys'	=>	array('conditional')
								)
							)
						)
					),

					// Actions
					'action'	=> array(

						'label'		=>	__('Actions', 'ws-form'),
						'icon'		=>	'actions',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	true,
						'expand'	=>	true,
						'kb_url'	=>	'/knowledgebase_category/actions/',

						// When an action is fired...
						'events'	=>	array(

							'save'		=>	array('label' => __('Form Saved', 'ws-form')),
							'submit'	=>	array('label' => __('Form Submitted', 'ws-form'))
						),

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'action'	=>	array(

									'meta_keys'	=>	array('action')
								)
							)
						),

					),

					// Support
					'support'	=> array(

						'label'		=>	__('Support', 'ws-form'),
						'icon'		=>	'support',
						'buttons'	=>	array(

							array(

								'label' => __('Close', 'ws-form'),
								'action' => 'wsf-sidebar-cancel'
							)
						),
						'static'	=>	true,
						'nav'		=>	true,
						'expand'	=>	true,

						'meta'		=>	array(

							'fieldsets'	=>	array(

								'knowledgebase'	=>	array(

									'label'		=> __('Knowledge Base', 'ws-form'),
									'meta_keys'	=>	array('knowledgebase')
								),

								'contact'		=>	array(

									'label'		=>	__('Contact', 'ws-form'),
									'meta_keys'	=>	array('contact_first_name', 'contact_last_name', 'contact_email', 'contact_inquiry', 'contact_push_form', 'contact_push_system', 'contact_gdpr', 'contact_submit')
								)
							)
						)
					),

					// Form
					'form' => array (

						'label'		=>	__('Settings', 'ws-form'),
						'icon'		=>	'settings',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	true,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic'	=> array(

									'label'		=>	__('Basic', 'ws-form'),

									'meta_keys'	=>	array('label_render_off'),

									'fieldsets'	=>	array(
										array(
											'label'			=>	__('Google Analytics', 'ws-form'),
											'meta_keys'	=> array('analytics_google', 'analytics_google_event_tab', 'analytics_google_event_field')
										),

										array(
											'label'			=>	__('Tracking', 'ws-form'),
											'meta_keys'	=> array('tracking_remote_ip', 'tracking_agent', 'tracking_referrer', 'tracking_os', 'tracking_host', 'tracking_pathname', 'tracking_query_string', 'tracking_geo_location', 'tracking_ip_lookup_latlon', 'tracking_ip_lookup_city', 'tracking_ip_lookup_region', 'tracking_ip_lookup_country', 'tracking_utm_source', 'tracking_utm_medium', 'tracking_utm_campaign', 'tracking_utm_term', 'tracking_utm_content')
										),
										array(
											'label'			=>	__('Spam Protection', 'ws-form'),
											'meta_keys'	=> array('honeypot', 'spam_threshold')
										)
									)
								),

								// Tab: Advanced
								'advanced'	=> array(

									'label'			=>	__('Advanced', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Form', 'ws-form'),
											'meta_keys'	=> array('label_mask_form', 'class_form_wrapper')
										),

										array(
											'label'		=>	__('Form Processing', 'ws-form'),
											'meta_keys'	=> array('submit_on_enter', 'submit_lock', 'submit_unlock', 'submit_reload', 'submit_show_errors', 'form_action')
										),

										array(
											'label'		=>	__('Tabs', 'ws-form'),
											'meta_keys'	=> array('cookie_tab_index', 'tab_validation', 'label_mask_group', 'class_group_wrapper')
										),

										array(
											'label'		=>	__('Sections', 'ws-form'),
											'meta_keys'	=> array('label_mask_section', 'class_section_wrapper')
										),

										array(
											'label'		=>	__('Fields', 'ws-form'),
											'meta_keys'	=> array('invalid_field_focus', 'class_field_wrapper', 'class_field', 'label_position_form', 'label_column_width_form', 'label_required', 'label_mask_required')
										)
									)
								),
								// Tab: Limit
								'limit'	=> array(

									'label'		=>	__('Limit', 'ws-form'),

									'fieldsets'	=>	array(

										array(
											'label'			=>	__('By Submission Count', 'ws-form'),
											'meta_keys'	=> array('submit_limit', 'submit_limit_count', 'submit_limit_period', 'submit_limit_message', 'submit_limit_message_type')
										),

										array(
											'label'			=>	__('By Schedule', 'ws-form'),
											'meta_keys'	=> array(

												'schedule_start', 'schedule_start_datetime', 'schedule_start_message', 'schedule_start_message_type', 
												'schedule_end', 'schedule_end_datetime', 'schedule_end_message', 'schedule_end_message_type'
											)
										),

										array(
											'label'			=>	__('By User', 'ws-form'),
											'meta_keys'	=> array('user_limit_logged_in', 'user_limit_logged_in_message', 'user_limit_logged_in_message_type')
										)
									)
								),
							),

							// Hidden meta data used to render admin interface
							'hidden'	=> array(

								'meta_keys'	=>	array('breakpoint', 'tab_index', 'action')
							)
						)
					),

					// Groups
					'group' => array(

						'label'		=>	__('Group', 'ws-form'),
						'icon'		=>	'group',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'			=>	false,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic' 		=> array(

									'label'		=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off')
								),

								// Tab: Advanced
								'advanced'		=> array(

									'label'		=>	__('Advanced', 'ws-form'),
									'meta_keys'	=>	array('class_group_wrapper')
								)
							)
						)
					),

					// Sections
					'section' => array(

						'label'		=>	__('Section', 'ws-form'),
						'icon'		=>	'section',
						'buttons'	=>	true,
						'static'	=>	false,
						'nav'		=>	false,
						'expand'	=>	true,

						'meta' => array (

							'fieldsets'			=> array(

								// Tab: Basic
								'basic' 		=> array(

									'label'			=>	__('Basic', 'ws-form'),
									'meta_keys'	=>	array('label_render_off', 'hidden_section'),
									'fieldsets'	=>	array(

										array(
											'label'			=>	__('Repeatable', 'ws-form'),
											'meta_keys'	=> array('section_repeatable', 'section_repeat_label', 'section_repeat_default')
										)
									)
								),

								// Tab: Advanced
								'advanced'		=> array(

									'label'			=>	__('Advanced', 'ws-form'),
									'fieldsets'	=>	array(

										array(
											'label'		=>	__('Style', 'ws-form'),
											'meta_keys'	=>	array('class_single_vertical_align')
										),

										array(
											'label'			=>	__('Classes', 'ws-form'),
											'meta_keys'	=> array('class_section_wrapper')
										),

										array(
											'label'			=>	__('Restrictions', 'ws-form'),
											'meta_keys'	=> array(

												'disabled_section'
											)
										),
										array(
											'label'			=>	__('Repeatable', 'ws-form'),
											'meta_keys'	=> array('section_repeat_min',
												'section_repeat_max', 'section_repeatable_delimiter_section', 'section_repeatable_delimiter_row')
										),
										array(
											'label'		=>	__('Breakpoints', 'ws-form'),
											'meta_keys'	=> array('breakpoint_sizes'),
											'class'		=>	array('wsf-fieldset-panel')
										)
									)
								)
							)
						)
					),

					// Fields
					'field' => array(

						'buttons'	=>	true,
						'static'	=>	false,
						'nav'			=>	false,
						'expand'	=>	true,
					)
				),

				'group' => array(

					'buttons' =>	array(

						array('name' => __('Clone', 'ws-form'), 'method' => 'clone'),
						array('name' => __('Delete', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Edit', 'ws-form'), 'method' => 'edit')
					),
				),

				'section' => array(

					'buttons' =>	array(

						array('name' => __('Clone', 'ws-form'), 'method' => 'clone'),
						array('name' => __('Delete', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Edit', 'ws-form'), 'method' => 'edit')
					),
				),

				'field' => array(

					'buttons' =>	array(

						array('name' => __('Clone', 'ws-form'), 'method' => 'clone'),
						array('name' => __('Delete', 'ws-form'), 'method' => 'delete'),
						array('name' => __('Edit', 'ws-form'), 'method' => 'edit')
					),
				),

				// Data grid
				'data_grid' => array(

					'rows_per_page_options' => array(

						5	=>	'5',
						10	=>	'10',
						25	=>	'25',
						50	=>	'50',
						100	=>	'100',
						150	=>	'150',
						200	=>	'200',
						250	=>	'250',
						500	=>	'500'
					)
				),
				// Conditional
				'conditional' => array(

					// Objects
					'objects' => array(

						// Form
						'form' => array(

							'text' 		=> __('Form', 'ws-form'),
							'logic' => array(

								// Events
								'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate', 'case_sensitive' => false),
								'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate', 'case_sensitive' => false),

								'wsf-submit'	=> array('text' => __('Submitted', 'ws-form'), 'values' => false, 'event' => 'wsf-submit', 'case_sensitive' => false),
								'wsf-save'	=> array('text' => __('Saved', 'ws-form'), 'values' => false, 'event' => 'wsf-save', 'case_sensitive' => false),
								'wsf-submit-save'	=> array('text' => __('Submitted or Saved', 'ws-form'), 'values' => false, 'event' => 'wsf-submit wsf-save', 'case_sensitive' => false),
								'wsf-complete'	=> array('text' => __('Submit or Save Complete', 'ws-form'), 'values' => false, 'event' => 'wsf-complete', 'case_sensitive' => false),
								'wsf-error'	=> array('text' => __('Submit or Save Error', 'ws-form'), 'values' => false, 'event' => 'wsf-error', 'case_sensitive' => false),

								'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click', 'case_sensitive' => false),

								'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'case_sensitive' => false),
								'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'case_sensitive' => false),
								'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'case_sensitive' => false),
								'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'case_sensitive' => false),

								'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'case_sensitive' => false),
								'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'case_sensitive' => false),
								'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'case_sensitive' => false),
								'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'case_sensitive' => false),
							),
							'action' => array(

								'form_submit'			=> array('text' => __('Submit', 'ws-form'), 'values' => false),
								'form_save'				=> array('text' => __('Save', 'ws-form'), 'values' => false),
								'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_wrapper'),
								'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_wrapper'),
								'javascript'			=> array('text' => __('Run JavaScript', 'ws-form'), 'type' => 'html_editor'),
							)
						),

						// Groups
						'group' => array(

							'text' 		=> __('Tab', 'ws-form'),
							'logic' => array(

								// Events
								'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),
								'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),
								'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click', 'case_sensitive' => false),
								'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'case_sensitive' => false),
								'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'case_sensitive' => false),
								'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'case_sensitive' => false),
								'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'case_sensitive' => false),
								'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'case_sensitive' => false),
								'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'case_sensitive' => false),
								'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'case_sensitive' => false),
								'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'case_sensitive' => false),
							),
							'action' => array(

/*								'visibility'	=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(
									array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
									array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => '')
								), 'auto_else' => 'visibility'),
								'disabled'		=> array('text' => __('Set disabled', 'ws-form'), 		'values' => array(
									array('text' => __('Not disabled', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Disabled', 'ws-form'), 'value' => 'on', 'auto_else' => '')
								), 'auto_else' => 'disabled'),
								'class_add_wrapper'			=> array('text' => __('Add wrapper class', 'ws-form'), 		'values' => true, 'auto_else' => 'class_remove_wrapper'),
								'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 	'values' => true, 'auto_else' => 'class_add_wrapper'),
*/
								'click'			=> array('text' => __('Click', 'ws-form'), 'values' => false),
							)
						),

						// Section
						'section' => array(

							'text' 		=> __('Section', 'ws-form'),
							'logic' => array(

								// Events
								'validate'		=> array('text' => __('Validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),
								'validate_not'	=> array('text' => __('Not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),

								// Section repeatable count
								'r=='					=> array('text' => __('Row count equals', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'r!='					=> array('text' => __('Row count does not equal', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'r>'					=> array('text' => __('Row count greater than', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'r<'					=> array('text' => __('Row count less than', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'r>='					=> array('text' => __('Row count greater than or equal to', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'r<='					=> array('text' => __('Row count less than or equal to', 'ws-form'), 'type' => 'number', 'event' => 'wsf-section-repeatable', 'case_sensitive' => false),
								'section_repeatable'	=> array('text' => __('Row count changes', 'ws-form'), 'values' => false, 'event' => 'wsf-section-repeatable'),
							),
							'action' => array(

								'visibility'			=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(

									array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
									array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => ''),

								), 'auto_else' => 'visibility'),

								'disabled'				=> array('text' => __('Set disabled', 'ws-form'), 'values' => array(

									array('text' => __('Not disabled', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Disabled', 'ws-form'), 'value' => 'on', 'auto_else' => '')

								), 'auto_else' => 'disabled'),

								'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 		'values' => true, 'auto_else' => 'class_remove_wrapper'),
								'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 	'values' => true, 'auto_else' => 'class_add_wrapper'),
								'set_row_count'	=> array('text' => __('Set row count', 'ws-form'), 	'values' => true),
							)
						),

						// Field
						'field' => array(

							'text'		=> __('Field', 'ws-form'),
							'logic' => array(

								// Numeric
								'=='					=> array('text' => __('Equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'!='					=> array('text' => __('Does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'>'						=> array('text' => __('Greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'<'						=> array('text' => __('Less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'>='					=> array('text' => __('Greater than or equal to', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'<='					=> array('text' => __('Less than or equal to', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),

								// Strings
								'equals'				=> array('text' => __('Equals', 'ws-form')),
								'equals_not' 			=> array('text' => __('Does not equal', 'ws-form')),
								'contains'				=> array('text' => __('Contains', 'ws-form')),
								'contains_not'			=> array('text' => __('Does not contain', 'ws-form')),
								'starts'				=> array('text' => __('Starts with', 'ws-form')),
								'starts_not'			=> array('text' => __('Does not start with', 'ws-form')),
								'ends'					=> array('text' => __('Ends with', 'ws-form')),
								'ends_not'				=> array('text' => __('Does not end with', 'ws-form')),
								'blank'					=> array('text' => __('Is blank', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'blank_not'				=> array('text' => __('Is not blank', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'checked'				=> array('text' => __('Row checked', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true),
								'checked_not'			=> array('text' => __('Row not checked', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true),
								'checked_any'			=> array('text' => __('Any row checked', 'ws-form'), 'values' => false),
								'checked_any_not'			=> array('text' => __('No row checked', 'ws-form'), 'values' => false),
								'checked_value_equals'	=> array('text' => __('Checked value equals', 'ws-form')),
								'checked_value_equals_not'	=> array('text' => __('Checked value does not equal', 'ws-form')),
								'selected'				=> array('text' => __('Row selected', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true),
								'selected_not'			=> array('text' => __('Row not selected', 'ws-form'), 'values' => false, 'rows' => true, 'case_sensitive' => false, 'data_source_exclude' => true),
								'selected_any'			=> array('text' => __('Any row selected', 'ws-form'), 'values' => false),
								'selected_any_not'			=> array('text' => __('No row selected', 'ws-form'), 'values' => false),
								'selected_value_equals'	=> array('text' => __('Select value equals', 'ws-form')),
								'selected_value_equals_not'	=> array('text' => __('Select value does not equal', 'ws-form')),
								'regex_email'			=> array('text' => __('Is valid email address', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'regex_email_not'		=> array('text' => __('Is not a valid email address', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'regex_url'				=> array('text' => __('Is valid URL', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'regex_url_not'			=> array('text' => __('Is not a valid URL', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'regex'					=> array('text' => __('Matches JS regex', 'ws-form'), 'case_sensitive' => false),
								'regex_not'				=> array('text' => __('Does not match JS regex', 'ws-form'), 'case_sensitive' => false),

								// Events
								'validate'				=> array('text' => __('Is validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),
								'validate_not'			=> array('text' => __('Is not validated', 'ws-form'), 'values' => false, 'event' => 'wsf-validate-silent', 'case_sensitive' => false),

								'click'			=> array('text' => __('Clicked', 'ws-form'), 'values' => false, 'event' => 'click', 'case_sensitive' => false),
								'mousedown'		=> array('text' => __('Mouse down', 'ws-form'), 'values' => false, 'event' => 'mousedown', 'case_sensitive' => false),
								'mouseup'		=> array('text' => __('Mouse up', 'ws-form'), 'values' => false, 'event' => 'mouseup', 'case_sensitive' => false),
								'mouseover'		=> array('text' => __('Mouse over', 'ws-form'), 'values' => false, 'event' => 'mouseover', 'case_sensitive' => false),
								'mouseout'		=> array('text' => __('Mouse out', 'ws-form'), 'values' => false, 'event' => 'mouseout', 'case_sensitive' => false),
								'touchstart'		=> array('text' => __('Touch start', 'ws-form'), 'values' => false, 'event' => 'touchstart', 'case_sensitive' => false),
								'touchend'		=> array('text' => __('Touch end', 'ws-form'), 'values' => false, 'event' => 'touchend', 'case_sensitive' => false),
								'touchmove'		=> array('text' => __('Touch move', 'ws-form'), 'values' => false, 'event' => 'touchmove', 'case_sensitive' => false),
								'touchcancel'		=> array('text' => __('Touch cancel', 'ws-form'), 'values' => false, 'event' => 'touchcancel', 'case_sensitive' => false),

								'focus'					=> array('text' => __('On focus', 'ws-form'), 'values' => false, 'event' => 'focus', 'case_sensitive' => false),
								'blur'					=> array('text' => __('On blur', 'ws-form'), 'values' => false, 'event' => 'blur', 'case_sensitive' => false),
								'change'				=> array('text' => __('On change', 'ws-form'), 'values' => false, 'event' => 'change', 'case_sensitive' => false),
								'input'					=> array('text' => __('On input', 'ws-form'), 'values' => false, 'event' => 'input', 'case_sensitive' => false),
								'change_input'			=> array('text' => __('On change or input', 'ws-form'), 'values' => false, 'event' => 'change input', 'case_sensitive' => false),
								'keyup'					=> array('text' => __('On key up', 'ws-form'), 'values' => false, 'event' => 'keyup', 'case_sensitive' => false),
								'keydown'				=> array('text' => __('On key down', 'ws-form'), 'values' => false, 'event' => 'keydown', 'case_sensitive' => false),

								// Date/Time
								'd=='					=> array('text' => __('Equals', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false),
								'd!='					=> array('text' => __('Does not equal', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false),
								'd>'					=> array('text' => __('Greater than', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false),
								'd<'					=> array('text' => __('Less than', 'ws-form'), 'type' => 'datetime', 'case_sensitive' => false),

								// Color
								'c==' 					=> array('text' => __('Equals (#RRGGBB)', 'ws-form'), 'type' => 'text', 'case_sensitive' => false),
								'c!=' 					=> array('text' => __('Does not equal (#RRGGBB)', 'ws-form'), 'type' => 'text', 'case_sensitive' => false),
								'ch>'					=> array('text' => __('Hue greater than', 'ws-form'), 'type' =>	'number', 'min' => 0, 'max' => 360, 'unit' => '&#176;', 'case_sensitive' => false),
								'ch<' 					=> array('text' => __('Hue less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 360, 'unit' => '&#176;', 'case_sensitive' => false),
								'cs>'					=> array('text' => __('Saturation greater than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false),
								'cs<'					=> array('text' => __('Saturation less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false),
								'cl>'					=> array('text' => __('Lightness greater than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false),
								'cl<'					=> array('text' => __('Lightness less than', 'ws-form'), 'type' => 'number', 'min' => 0, 'max' => 100, 'unit' => '%', 'case_sensitive' => false),

								// reCAPTCHA
								'recaptcha' 			=> array('text' => __('reCAPTCHA valid', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'recaptcha_not' 	=> array('text' => __('reCAPTCHA invalid', 'ws-form'), 'values' => false, 'case_sensitive' => false),

								// Signature
								'signature' 			=> array('text' => __('Signed', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'signature_not' 		=> array('text' => __('Unsigned', 'ws-form'), 'values' => false, 'case_sensitive' => false),

								// File
								'file' 					=> array('text' => __('File selected', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'file_not'				=> array('text' => __('No file selected', 'ws-form'), 'values' => false, 'case_sensitive' => false),
								'f==' 					=> array('text' => __('File count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'f!=' 					=> array('text' => __('File count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'f>' 					=> array('text' => __('File count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'f<' 					=> array('text' => __('File count less than', 'ws-form'), 'type' =>	'number', 'case_sensitive' => false),

								// Match field
								'field_match' 			=> array('text' => __('Matches field', 'ws-form'), 'values' => 'fields'),
								'field_match_not'		=> array('text' => __('Does not match field', 'ws-form'), 'values' => 'fields'),

								// Character and word count
								'cc==' 					=> array('text' => __('Character count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cc!=' 					=> array('text' => __('Character count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cc>' 					=> array('text' => __('Character count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cc<' 					=> array('text' => __('Character count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cw==' 					=> array('text' => __('Word count equals', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cw!=' 					=> array('text' => __('Word count does not equal', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cw>' 					=> array('text' => __('Word count greater than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
								'cw<' 					=> array('text' => __('Word count less than', 'ws-form'), 'type' => 'number', 'case_sensitive' => false),
							),
							'action' => array(

								// General
								'visibility'		=> array('text' => __('Set visibility', 'ws-form'), 'values' => array(

									array('text' => __('Visible', 'ws-form'), 'value' => '', 'auto_else' => 'off'),
									array('text' => __('Hidden', 'ws-form'), 'value' => 'off', 'auto_else' => '')

								), 'auto_else' => 'visibility'),

								'required'			=> array('text' => __('Set required', 'ws-form'), 'values' => array(

									array('text' => __('Not required', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Required', 'ws-form'), 'value' => 'on', 'auto_else' => '')

								), 'auto_else' => 'required'),

								'focus'				=> array('text' => __('Focus', 'ws-form'), 'values' => false),
								'blur'				=> array('text' => __('Blur', 'ws-form'), 'values' => false),
								'value'				=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value'),

								'disabled'			=> array('text' => __('Set disabled', 'ws-form'), 'values' => array(

									array('text' => __('Not disabled', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Disabled', 'ws-form'), 'value' => 'on', 'auto_else' => '')

								), 'auto_else' => 'disabled'),

								'readonly'			=> array('text' => __('Set read only', 'ws-form'), 'values' => array(

									array('text' => __('Not read only', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Read only', 'ws-form'), 'value' => 'on', 'auto_else' => '')

								), 'auto_else' => 'readonly'),

								// Values by field type
								'value_datetime'	=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_datetime', 'auto_else_copy' => true, 'type' => 'datetime'),
								'value_number'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_number', 'auto_else_copy' => true, 'type' => 'number'),
								'value_range'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_range', 'auto_else_copy' => true, 'type' => 'range'),
								'value_rating'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_rating', 'auto_else_copy' => true, 'type' => 'rating'),
								'value_color'		=> array('text' => __('Set color', 'ws-form'), 'values' => true, 'auto_else' => 'value_color', 'auto_else_copy' => true, 'type' => 'color'),
								'value_email'		=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_email', 'auto_else_copy' => true, 'type' => 'email'),
								'value_tel'			=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_tel', 'auto_else_copy' => true, 'type' => 'tel'),
								'value_url'			=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_url', 'auto_else_copy' => true, 'type' => 'url'),
								'value_textarea'	=> array('text' => __('Set value', 'ws-form'), 'values' => true, 'auto_else' => 'value_textarea', 'auto_else_copy' => true, 'type' => 'textarea'),				

								// Validation
								'set_custom_validity'	=> array('text' => __('Set custom validity', 'ws-form'), 'values' => true, 'auto_else' => 'set_custom_validity'),

								// Data grid rows
								'value_row_select'			=> array('text' => __('Select row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_deselect', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_deselect'		=> array('text' => __('Deselect row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_select', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_select_value'	=> array('text' => __('Select row with value', 'ws-form'), 'auto_else' => 'value_row_deselect_value', 'auto_else_copy' => true),
								'value_row_deselect_value'	=> array('text' => __('Deselect row with value', 'ws-form'), 'auto_else' => 'value_row_select_value', 'auto_else_copy' => true),

								'value_row_reset'			=> array('text' => __('Reset', 'ws-form'), 'values' => false),

								'value_row_check'			=> array('text' => __('Check row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_uncheck', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_uncheck'			=> array('text' => __('Uncheck row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_check', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_check_value'		=> array('text' => __('Check row with value', 'ws-form'), 'auto_else' => 'value_row_uncheck_value', 'auto_else_copy' => true),
								'value_row_uncheck_value'	=> array('text' => __('Uncheck row with value', 'ws-form'), 'auto_else' => 'value_row_check_value', 'auto_else_copy' => true),

								'value_row_required'		=> array('text' => __('Set row required', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_required', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_not_required'	=> array('text' => __('Set row not required', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_required', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_disabled'		=> array('text' => __('Set row disabled', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_disabled', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_not_disabled'	=> array('text' => __('Set row not disabled', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_disabled', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_class_add'		=> array('text' => __('Add row class', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_class_remove', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_class_remove'	=> array('text' => __('Remove row class', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_class_add', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_visible'			=> array('text' => __('Set row visible', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_not_visible', 'auto_else_copy' => true, 'data_source_exclude' => true),
								'value_row_not_visible'		=> array('text' => __('Set row not visible', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'auto_else' => 'value_row_visible', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'value_row_focus'			=> array('text' => __('Focus row', 'ws-form'), 'values' => false, 'value_row_ids' => true, 'data_source_exclude' => true),

								'value_row_set_custom_validity'		=> array('text' => __('Set row custom validity', 'ws-form'), 'values' => true, 'value_row_ids' => true, 'auto_else' => 'value_row_set_custom_validity', 'auto_else_copy' => true, 'data_source_exclude' => true),

								'html'					=> array('text' => __('Set HTML', 'ws-form'), 'type' => 'html_editor'),
								'text_editor'			=> array('text' => __('Set content', 'ws-form'), 'type' => 'text_editor'),

								// Buttons
								'button_html'	=> array('text' => __('Set label', 'ws-form')),
								'click'			=> array('text' => __('Click', 'ws-form'), 'values' => false),

								// Classes
								'class_add_wrapper'		=> array('text' => __('Add wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_wrapper'),
								'class_remove_wrapper'	=> array('text' => __('Remove wrapper class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_wrapper'),
								'class_add_field'		=> array('text' => __('Add field class', 'ws-form'), 'values' => true, 'auto_else' => 'class_remove_field'),
								'class_remove_field'	=> array('text' => __('Remove field class', 'ws-form'), 'values' => true, 'auto_else' => 'class_add_field'),

								// File
								'reset_file'	=> array('text' => __('Reset', 'ws-form'), 	'values' => false),

								// Signature
								'reset_signature'		=> array('text' => __('Reset', 'ws-form'), 	'values' => false),
								'required_signature'	=> array('text' => __('Set required', 'ws-form'), 'values' => array(
									array('text' => __('Not required', 'ws-form'), 'value' => '', 'auto_else' => 'on'),
									array('text' => __('Required', 'ws-form'), 'value' => 'on', 'auto_else' => '')
								), 'auto_else' => 'required_signature')
							)
						),

						// Action
						'action' => array(

							'text'		=> __('Action', 'ws-form'),
							'logic' 	=> array(),
							'action'	=> array(

								'action_run' 					=> array('text' => __('Run immediately', 'ws-form'), 'values' => false),
								'action_run_on_submit' 			=> array('text' => __('Run when form submitted', 'ws-form'), 'values' => false, 'auto_else' => 'action_do_not_run_on_submit'),
								'action_do_not_run_on_submit' 	=> array('text' => __('Do not run when form submitted', 'ws-form'), 'values' => false, 'auto_else' => 'action_run_submit'),
								'action_run_on_save' 			=> array('text' => __('Run when form saved', 'ws-form'), 'values' => false, 'auto_else' => 'action_do_not_run_on_save'),
								'action_do_not_run_on_save' 	=> array('text' => __('Do not run when form saved', 'ws-form'), 'values' => false, 'auto_else' => 'action_run_save')
							)
						)
					),

					// Logic previous
					'logic_previous' => array(

						'||' => array('text' => __('OR', 'ws-form')),
						'&&' => array('text' => __('AND', 'ws-form')),
					)
				),

				// History
				'history'	=> array(

					'initial'	=> __('Initial form', 'ws-form'),

					'method' 	=> array(

						// All past tense
						'get'				=> __('Read', 'ws-form'),
						'put'				=> __('Updated', 'ws-form'),
						'put_clone'			=> __('Cloned', 'ws-form'),
						'put_resize'		=> __('Resized', 'ws-form'),
						'put_offset'		=> __('Offset', 'ws-form'),
						'put_sort_index'	=> __('Moved', 'ws-form'),
						'put_reset'			=> __('Reset', 'ws-form'),
						'post'				=> __('Added', 'ws-form'),
						'post_upload_json'	=> __('Uploaded', 'ws-form'),
						'delete'			=> __('Deleted', 'ws-form'),
					),

					'object'	=> array(

						'form'		=> __('form', 'ws-form'),
						'group'		=> __('group', 'ws-form'),
						'section'	=> __('section', 'ws-form'),
						'field'		=> __('field', 'ws-form')
					)
				),

				// Icons
				'icons'		=> array(

					'actions'			=> self::get_icon_16_svg('actions'),
					'asterisk'			=> self::get_icon_16_svg('asterisk'),
					'calc'				=> self::get_icon_16_svg('calc'),
					'check'				=> self::get_icon_16_svg('check'),
					'close-circle'		=> self::get_icon_16_svg('close-circle'),
					'clone'				=> self::get_icon_16_svg('clone'),
					'conditional'		=> self::get_icon_16_svg('conditional'),
					'contract'			=> self::get_icon_16_svg('contract'),
					'default'			=> self::get_icon_16_svg(),
					'disabled'			=> self::get_icon_16_svg('disabled'),
					'download'			=> self::get_icon_16_svg('download'),
					'edit'				=> self::get_icon_16_svg('edit'),
					'expand'			=> self::get_icon_16_svg('expand'),
					'exchange'			=> self::get_icon_16_svg('exchange'),
					'file-code'			=> self::get_icon_16_svg('file-code'),
					'file-default'		=> self::get_icon_16_svg('file-default'),
					'file-font'			=> self::get_icon_16_svg('file-font'),
					'file-movie'		=> self::get_icon_16_svg('file-movie'),
					'file-presentation'	=> self::get_icon_16_svg('file-presentation'),
					'file-sound'		=> self::get_icon_16_svg('file-sound'),
					'file-table'		=> self::get_icon_16_svg('file-table'),
					'file-text'			=> self::get_icon_16_svg('file-text'),
					'file-zip'			=> self::get_icon_16_svg('file-zip'),
					'file-picture'		=> self::get_icon_16_svg('file-picture'),

					'hidden'			=> self::get_icon_16_svg('hidden'),
					'info-circle'		=> self::get_icon_16_svg('info-circle'),
					'first'				=> self::get_icon_16_svg('first'),
					'form'				=> self::get_icon_16_svg('settings'),
					'group'				=> self::get_icon_16_svg('group'),
					'last'				=> self::get_icon_16_svg('last'),
					'markup-circle'		=> self::get_icon_16_svg('markup-circle'),
					'menu'				=> self::get_icon_16_svg('menu'),
					'minus-circle'		=> self::get_icon_16_svg('minus-circle'),
					'next'				=> self::get_icon_16_svg('next'),
					'number'			=> self::get_icon_16_svg('number'),
					'picture'			=> self::get_icon_16_svg('picture'),
					'plus'				=> self::get_icon_16_svg('plus'),
					'plus-circle'		=> self::get_icon_16_svg('plus-circle'),
					'previous'			=> self::get_icon_16_svg('previous'),
					'question-circle'	=> self::get_icon_16_svg('question-circle'),
					'rating'			=> self::get_icon_16_svg('rating'),
					'readonly'			=> self::get_icon_16_svg('readonly'),
					'redo'				=> self::get_icon_16_svg('redo'),
					'reload'			=> self::get_icon_16_svg('reload'),
					'section'			=> self::get_icon_16_svg('section'),
					'settings'			=> self::get_icon_16_svg('settings'),
					'sort'				=> self::get_icon_16_svg('sort'),
					'table'				=> self::get_icon_16_svg('table'),
					'tools'				=> self::get_icon_16_svg('tools'),
					'undo'				=> self::get_icon_16_svg('undo'),
					'upload'			=> self::get_icon_16_svg('upload'),
					'visible'			=> self::get_icon_16_svg('visible'),
					'warning'			=> self::get_icon_16_svg('warning'),
					'wizard'			=> self::get_icon_16_svg('wizard'),
					'woo'				=> self::get_icon_16_svg('woo'),
				),

				// Language
				'language'	=> array(

					// Custom
					'custom'		=>	'%s',

					// Objects
					'form'				=>	__('Form', 'ws-form'),
					'forms'				=>	__('Forms', 'ws-form'),
					'group'				=>	__('Tab', 'ws-form'),
					'groups'			=>	__('Tabs', 'ws-form'),
					'section'			=>	__('Section', 'ws-form'),
					'sections'			=>	__('Sections', 'ws-form'),
					'field'				=>	__('Field', 'ws-form'),
					'fields'			=>	__('Fields', 'ws-form'),
					'action'			=>	__('Action', 'ws-form'),
					'actions'			=>	__('Actions', 'ws-form'),
					'submission'		=>	__('Submission', 'ws-form'),
					'id'				=>	__('ID', 'ws-form'),
					'unknown'			=>	__('Unknown', 'ws-form'),

					// Buttons
					'add_group'			=>	__('Add Tab', 'ws-form'),
					'add_section'		=>	__('Add Section', 'ws-form'),
					'save'				=>	__('Save', 'ws-form'),
					'save_and_close'	=>	__('Save & Close', 'ws-form'),
					'delete'			=>	__('Delete', 'ws-form'),
					'trash'				=>	__('Trash', 'ws-form'),
					'clone'				=>	__('Clone', 'ws-form'),
					'cancel'			=>	__('Cancel', 'ws-form'),
					'print'				=>	__('Print', 'ws-form'),
					'edit'				=>	__('Edit', 'ws-form'),
					'previous'			=>	__('Previous', 'ws-form'),
					'next'				=>	__('Next', 'ws-form'),
					'repost'			=>	__('Re-Run', 'ws-form'),
					'default'			=>	__('Default', 'ws-form'),
					'variables'			=>	__('Variables', 'ws-form'),
					'select_list'		=>	__('Select From List', 'ws-form'),
					'calc'				=>	__('Calculate', 'ws-form'),
					'auto_map'			=>	__('Auto Map Fields', 'ws-form'),
					'reset'				=>	__('Reset', 'ws-form'),
					'close'				=>	__('Close', 'ws-form'),
					'required'			=>	__('Required', 'ws-form'),
					'required_setting'	=>	__('Required Setting', 'ws-form'),
					'hidden'			=>	__('Hidden', 'ws-form'),
					'disabled'			=>	__('Disabled', 'ws-form'),
					'readonly'			=>	__('Read Only', 'ws-form'),

					// Tutorial
					'intro_learn_more'	=>	__('Learn More', 'ws-form'),
					'intro_skip'		=>	__('Skip Tutorial', 'ws-form'),

					// Form statuses
					'draft'				=>	__('Draft', 'ws-form'),
					'publish'			=>	__('Published', 'ws-form'),

					// Uses constants because these are used by the API also
					'default_label_form'		=>	__(WS_FORM_DEFAULT_FORM_NAME, 'ws-form'),
					'default_label_group'		=>	__(WS_FORM_DEFAULT_GROUP_NAME, 'ws-form'),
					'default_label_section'		=>	__(WS_FORM_DEFAULT_SECTION_NAME, 'ws-form'),
					'default_label_field'		=>	__(WS_FORM_DEFAULT_FIELD_NAME, 'ws-form'),

					// Error messages
					'error_field_type_unknown'			=>	__('Unknown field type', 'ws-form'),
					'error_admin_max_width'				=>	__('admin_max_width not defined for breakpoint: %s.', 'ws-form'),
					'error_object'						=>	__('Unable to find object', 'ws-form'),
					'error_object_data'					=>	__('Unable to retrieve object data', 'ws-form'),
					'error_object_meta_value'			=>	__('Unable to retrieve object meta', 'ws-form'),
					'error_object_type'					=>	__('Unable to determine object type', 'ws-form'),
					'error_meta_key'					=>	__('Unknown meta_key: %s', 'ws-form'),
					'error_data_grid'					=>	__('Data grid not specified', 'ws-form'),
					'error_data_grid_groups'			=>	__('Data grid has no groups', 'ws-form'),
					'error_data_grid_default_group'		=>	__('Default group missing in meta type', 'ws-form'),
					'error_data_grid_columns'			=>	__('Data grid has no columns', 'ws-form'),
					'error_data_grid_rows_per_page'		=>	__('Data grid has no rows per page value', 'ws-form'),
					'error_data_grid_csv_no_data'		=>	__('No data to export', 'ws-form'),
					'error_data_grid_row_id'			=>	__('Data grid row has no ID', 'ws-form'),
					'error_timeout_codemirror'			=>	__('Timeout waiting for CodeMirror to load', 'ws-form'),
					'error_auto_map_api_fields'			=>	__('No API fields to map'),
					'error_action_list_sub_get'			=>	__('Unable to retrieve list subs'),

					// Popover
					'confirm_group_delete'				=>	__('Are you sure you want to delete this tab?', 'ws-form'),
					'confirm_section_delete'			=>	__('Are you sure you want to delete this section?', 'ws-form'),
					'confirm_field_delete'				=>	__('Are you sure you want to delete this field?', 'ws-form'),
					'confirm_action_repost'				=>	__('Are you sure you want to re-run this action?', 'ws-form'),
					'confirm_breakpoint_reset'			=>	__('Are you sure you want to reset the widths and offsets?', 'ws-form'),
					'confirm_orientation_breakpoint_reset'	=>	__('Are you sure you want to reset the widths?', 'ws-form'),
					'confirm_submit_delete'				=>	__('Are you sure you want to trash this submission', 'ws-form'),

					// Blanks
					'blank_section'						=>	__('Drag a section here', 'ws-form'),
					'blank_field'						=>	__('Drag a field here', 'ws-form'),

					// Compatibility
					'attribute_compatibility'			=>	__('Compatibility', 'ws-form'),
					'field_compatibility'				=>	__('Compatibility', 'ws-form'),
					'field_kb_url'						=>	__('Knowledge Base', 'ws-form'),

					// CSV upload
					'data_grid_upload_csv'				=>	__('Drop file to upload', 'ws-form'),
					'form_upload_json'					=>	__('Drop file to upload', 'ws-form'),

					// Data grids - Data sources
					'data_grid_data_source_error'			=>	__('Error retrieving data source', 'ws-form'),
					'data_grid_data_source_error_s'			=>	__('Error retrieving data source: %s', 'ws-form'),
					'data_grid_data_source_error_last'			=>	__('Error retrieving data source<br />%s', 'ws-form'),
					'data_grid_data_source_error_last_field'	=>	__('Field: %s', 'ws-form'),
					'data_grid_data_source_error_last_field_id'	=>	__('ID: %s', 'ws-form'),
					'data_grid_data_source_error_last_source'	=>	__('Data source: %s', 'ws-form'),
					'data_grid_data_source_error_last_date'		=>	__('Last attempt: %s', 'ws-form'),
					'data_grid_data_source_error_last_error'	=>	__('Error: %s', 'ws-form'),

					// Data grids - Groups
					'data_grid_settings'				=>	__('Settings', 'ws-form'),
					'data_grid_groups_label'			=>	__('Label', 'ws-form'),
					'data_grid_groups_label_render'		=>	__('Render Label', 'ws-form'),
					'data_grid_group_add'				=>	__('Add Group', 'ws-form'),
					'data_grid_group_label_default'		=>	__('Group', 'ws-form'),
					'data_grid_group_auto_group'		=>	__('Auto Group By', 'ws-form'),
					'data_grid_group_auto_group_select'	=>	__('Select...', 'ws-form'),
					'data_grid_group_disabled'			=>	__('Disabled', 'ws-form'),
					'data_grid_groups_group'			=>	__('Group These Values', 'ws-form'),
					'data_grid_group_delete'			=>	__('Delete Group', 'ws-form'),
					'data_grid_group_delete_confirm'	=>	__('Are you sure you want to delete this group?', 'ws-form'),

					// Data grids - Columns
					'data_grid_column_add'				=>	__('Add Column', 'ws-form'),
					'data_grid_column_label_default'	=>	__('Column', 'ws-form'),
					'data_grid_column_delete'			=>	__('Delete Column', 'ws-form'),
					'data_grid_column_delete_confirm'	=>	__('Are you sure you want to delete this column?', 'ws-form'),

					// Data grids - Rows
					'data_grid_row_add'					=>	__('Add Row', 'ws-form'),
					'data_grid_row_sort'				=>	__('Sort Row', 'ws-form'),
					'data_grid_row_delete'				=>	__('Delete Row', 'ws-form'),
					'data_grid_row_delete_confirm'		=>	__('Are you sure you want to delete this row?', 'ws-form'),
					'data_grid_row_bulk_actions'		=>	__('Bulk Actions', 'ws-form'),
					'data_grid_row_default'				=>	__('Selected', 'ws-form'),
					'data_grid_row_required'			=>	__('Required', 'ws-form'),
					'data_grid_row_disabled'			=>	__('Disabled', 'ws-form'),
					'data_grid_row_hidden'				=>	__('Hidden', 'ws-form'),

					// Data grids - Bulk actions
					'data_grid_row_bulk_actions_select'			=>	__('Select...', 'ws-form'),
					'data_grid_row_bulk_actions_delete'			=>	__('Delete', 'ws-form'),
					'data_grid_row_bulk_actions_default'		=>	__('Set Default', 'ws-form'),
					'data_grid_row_bulk_actions_default_off'	=>	__('Set Not Default', 'ws-form'),
					'data_grid_row_bulk_actions_required'		=>	__('Set Required', 'ws-form'),
					'data_grid_row_bulk_actions_required_off'	=>	__('Set Not Required', 'ws-form'),
					'data_grid_row_bulk_actions_disabled'		=>	__('Set Disabled', 'ws-form'),
					'data_grid_row_bulk_actions_disabled_off'	=>	__('Set Not Disabled', 'ws-form'),
					'data_grid_row_bulk_actions_hidden'			=>	__('Set Hidden', 'ws-form'),
					'data_grid_row_bulk_actions_hidden_off'		=>	__('Set Not Hidden', 'ws-form'),
					'data_grid_row_bulk_actions_apply'			=>	__('Apply', 'ws-form'),

					// Data grids - Rows per page
					'data_grid_rows_per_page'				=>	__('Rows Per Page', 'ws-form'),
					'data_grid_rows_per_page_0'				=>	__('Show All', 'ws-form'),
					'data_grid_rows_per_page_apply'			=>	__('Apply', 'ws-form'),

					// Data grids - Upload
					'data_grid_group_upload_csv'			=>	__('Import CSV', 'ws-form'),

					// Data grids - Download
					'data_grid_group_download_csv'			=>	__('Export CSV', 'ws-form'),

					// Data grids - Actions
					'data_grid_action_edit'					=>	__('Edit', 'ws-form'),
					'data_grid_action_action'				=>	__('Action', 'ws-form'),
					'data_grid_action_event'				=>	__('When should this action run?', 'ws-form'),
					'data_grid_action_event_conditional'	=>	__('Actions can also be run by using conditional logic.', 'ws-form'),

					// Data grids - Conditional
					'data_grid_conditional_edit'			=>	__('Edit', 'ws-form'),
					'data_grid_conditional_clone'			=>	__('Clone', 'ws-form'),

					// Data grids - Actions
					'data_grid_action_edit'					=>	__('Edit', 'ws-form'),
					'data_grid_action_clone'				=>	__('Clone', 'ws-form'),

					// Data grids - Insert image
					'data_grid_insert_image'				=>	__('Insert Image', 'ws-form'),

					// Repeaters
					'repeater_row_add'						=>	__('Add Row', 'ws-form'),
					'repeater_row_delete'					=>	__('Delete Row', 'ws-form'),

					// Breakpoint size
					'breakpoint_reset'						=>	__('Reset', 'ws-form'),

					// Sidebar titles
					'sidebar_title_form'					=>	__('Form', 'ws-form'),
					'sidebar_title_group'					=>	__('Tab', 'ws-form'),
					'sidebar_title_section'					=>	__('Section', 'ws-form'),
					'sidebar_title_history'					=>	__('History', 'ws-form'),
					'sidebar_button_image'					=>	__('Select', 'ws-form'),

					// Sidebar - Expand / Contract
					'data_sidebar_expand'					=>	__('Expand', 'ws-form'),
					'data_sidebar_contract'					=>	__('Contract', 'ws-form'),

					// Actions
					'action_label_default'					=>	__('New action', 'ws-form'),
					'action_api_reload'						=>	__('Update', 'ws-form'),

					// Conditional
					'conditional_label_default'				=>	__('New condition', 'ws-form'),

					'conditional_if'						=>	__('If', 'ws-form'),
					'conditional_then'						=>	__('Then', 'ws-form'),
					'conditional_else'						=>	__('Else', 'ws-form'),

					'conditional_case_sensitive'			=>	__('Case sensitive', 'ws-form'),

					'conditional_group_add'					=>	__('Add Group', 'ws-form'),
					'conditional_group_delete'				=>	__('Delete Group', 'ws-form'),
					'conditional_group_sort'				=>	__('Sort Group', 'ws-form'),

					'conditional_condition_add'					=>	__('Add Condition', 'ws-form'),
					'conditional_condition_delete'				=>	__('Delete Condition', 'ws-form'),
					'conditional_condition_select'				=>	__('Select...', 'ws-form'),
					'conditional_condition_select_action'		=>	__('Select...', 'ws-form'),
					'conditional_condition_select_logic'		=>	__('Select...', 'ws-form'),
					'conditional_condition_select_row'			=>	__('Select...', 'ws-form'),
					'conditional_condition_select_placeholder'	=>	__('Placeholder', 'ws-form'),
					'conditional_condition_sort'				=>	__('Sort Condition', 'ws-form'),

					'conditional_then_add'						=>	__("Add 'THEN' Action", 'ws-form'),
					'conditional_then_delete'					=>	__("Delete 'THEN' Action", 'ws-form'),
					'conditional_then_select'					=>	__('Select...', 'ws-form'),
					'conditional_then_select_action'			=>	__('Select...', 'ws-form'),
					'conditional_then_select_row'				=>	__('Select...', 'ws-form'),
					'conditional_then_sort'						=>	__("Sort 'THEN' Action", 'ws-form'),

					'conditional_else_add'						=>	__("Add 'ELSE' Action", 'ws-form'),
					'conditional_else_delete'					=>	__("Delete 'ELSE' Action", 'ws-form'),
					'conditional_else_select'					=>	__('Select...', 'ws-form'),
					'conditional_else_select_action'			=>	__('Select...', 'ws-form'),
					'conditional_else_select_row'				=>	__('Select...', 'ws-form'),
					'conditional_else_sort'						=>	__("Sort 'ELSE' Action", 'ws-form'),

					'conditional_field_select'					=>	__('Select...', 'ws-form'),

					// Breakpoint options
					'breakpoint_offset_column_width'			=>	__('Width - Columns', 'ws-form'),
					'breakpoint_offset_column_offset'			=>	__('Offset - Columns', 'ws-form'),
					'breakpoint_option_default'					=>	__('Default ', 'ws-form'),
					'breakpoint_option_inherit'					=>	__('Inherit', 'ws-form'),
					'breakpoint_option_column_default_singular'	=>	'%s',
					'breakpoint_option_column_default_plural'	=>	'%s',
					'breakpoint_option_offset_default_singular'	=>	'%s',
					'breakpoint_option_offset_default_plural'	=>	'%s',
					'breakpoint_option_column_singular'			=>	'%s',
					'breakpoint_option_column_plural'			=>	'%s',
					'breakpoint_option_offset_singular'			=>	'%s',
					'breakpoint_option_offset_plural'			=>	'%s',

					// Orientation Breakpoint options
					'orientation_breakpoint_label_width'					=>	__('%s Width', 'ws-form'),
					'orientation_breakpoint_width'							=>	__(' = %s width', 'ws-form'),
					'orientation_breakpoint_width_full'						=>	__(' = Full width', 'ws-form'),
					'orientation_breakpoint_option_default'					=>	__('Default ', 'ws-form'),
					'orientation_breakpoint_option_inherit'					=>	__('Inherit', 'ws-form'),
					'orientation_breakpoint_option_column_default_singular'	=>	'%s column',
					'orientation_breakpoint_option_column_default_plural'	=>	'%s columns',
					'orientation_breakpoint_option_column_singular'			=>	'%s column',
					'orientation_breakpoint_option_column_plural'			=>	'%s columns',

					'column_size_change'						=>	__('Change column size', 'ws-form'),
					'offset_change'								=>	__('Change offset', 'ws-form'),

					// Submit
					'submit_status'								=>	__('Status', 'ws-form'),
					'submit_preview'							=>	__('Preview', 'ws-form'),
					'submit_date_added'							=>	__('Added', 'ws-form'),
					'submit_date_updated'						=>	__('Updated', 'ws-form'),
					'submit_user'								=>	__('User', 'ws-form'),
					'submit_status'								=>	__('Status', 'ws-form'),
					'submit_duration'							=>	__('Duration', 'ws-form'),
					'submit_duration_days'						=>	__('Days', 'ws-form'),
					'submit_duration_hours'						=>	__('Hours', 'ws-form'),
					'submit_duration_minutes'					=>	__('Minutes', 'ws-form'),
					'submit_duration_seconds'					=>	__('Seconds', 'ws-form'),
					'submit_tracking'							=>	__('Tracking', 'ws-form'),
					'submit_tracking_geo_location_permission_denied'	=>	__('User denied the request for geo location.', 'ws-form'),
					'submit_tracking_geo_location_position_unavailable'	=>	__('Geo location information was unavailable.', 'ws-form'),
					'submit_tracking_geo_location_timeout'				=>	__('The request to get user geo location timed out.', 'ws-form'),
					'submit_tracking_geo_location_default'				=>	__('An unknown error occurred whilst retrieving geo location.', 'ws-form'),
					'submit_actions'							=>	__('Actions', 'ws-form'),
					'submit_actions_column_index'				=>	'#',
					'submit_actions_column_action'				=>	__('Action', 'ws-form'),
					'submit_actions_column_meta_label'			=>	__('Setting', 'ws-form'),
					'submit_actions_column_meta_value'			=>	__('Value', 'ws-form'),
					'submit_actions_column_logs'				=>	__('Log', 'ws-form'),
					'submit_actions_column_errors'				=>	__('Error', 'ws-form'),
					'submit_actions_repost'						=>	__('Run Again', 'ws-form'),
					'submit_actions_meta'						=>	__('Settings', 'ws-form'),
					'submit_actions_logs'						=>	__('Logs', 'ws-form'),
					'submit_actions_errors'						=>	__('Errors', 'ws-form'),
					'submit_action_logs'						=>	__('Action Logs', 'ws-form'),
					'submit_action_errors'						=>	__('Action Errors', 'ws-form'),
					'submit_ecommerce'							=>	__('E-Commerce', 'ws-form'),
					'submit_encrypted'							=>	__('Encrypted', 'ws-form'),

					// Add form
					'form_add_create'		=>	__('Create', 'ws-form'),
					'form_import_confirm'	=>	__("Are you sure you want to import this file?\n\nImporting a form file will overwrite the existing form and create new field ID's.\n\nIt is not recommended that you use this feature for forms that are in use on your website.", 'ws-form'),

					// Sidebar - Expand / Contract
					'sidebar_expand'	=>	__('Expand', 'ws-form'),
					'sidebar_contract'	=>	__('Contract', 'ws-form'),

					// Knowledge Base
					'knowledgebase_search_label'		=>	__('Enter keyword(s) to search', 'ws-form'),
					'knowledgebase_search_button'		=>	__('Search', 'ws-form'),
					'knowledgebase_search_placeholder'	=>	__('Keyword(s)', 'ws-form'),
					'knowledgebase_popular'				=>	__('Popular Articles', 'ws-form'),
					'knowledgebase_view_all'			=>	__('View Full Knowledge Base', 'ws-form'),

					// Contact
					'support_contact_thank_you'			=>	__('Thank you for your inquiry.', 'ws-form'),
					'support_contact_error'				=>	__('An error occurred when submitting your support inquiry. Please email support@wsform.com (%s)', 'ws-form'),

					// Starred
					'starred_on'						=>	__('Starred', 'ws-form'),
					'starred_off'						=>	__('Not Starred', 'ws-form'),

					// Viewed
					'viewed_on'							=>	__('Mark as Unread', 'ws-form'),
					'viewed_off'						=>	__('Mark as Read', 'ws-form'),

					// Form location
					'form_location_not_found'			=>	__('Form not found in content', 'ws-form'),

					// Shortcode copy
					'shortcode_copied'					=>	__('Shortcode copied', 'ws-form'),

					// API - List subs
					'list_subs_call'		=>	__('Retrieving...', 'ws-form'),
					'list_subs_select'		=>	__('Select...', 'ws-form'),

					// Options
					'options_select'		=>	__('Select...', 'ws-form')
				)
			);

			// Set icons
			foreach($settings_form_admin['group']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['group']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}
			foreach($settings_form_admin['section']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['section']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}
			foreach($settings_form_admin['field']['buttons'] as $key => $buttons) {

				$method = $buttons['method'];
				$settings_form_admin['field']['buttons'][$key]['icon'] = self::get_icon_16_svg($method);
			}

			// Apply filter
			$settings_form_admin = apply_filters('wsf_config_settings_form_admin', $settings_form_admin);

			// Cache
			self::$settings_form_admin = $settings_form_admin;

			return $settings_form_admin;
		}

		// Configuration - Settings - Public
		public static function get_settings_form_public() {

			$settings_form_public = array();

			// Check if debug is enabled
			$debug = WS_Form_Common::debug_enabled();

			// Debug
			if($debug) {

				// Additional language strings for the public debug feature
				$language_extra = array(

					'debug_form'						=>	__('Form', 'ws-form'),
					'debug_form_rendered'				=>	__('Form rendered', 'ws-form'),

					'debug_minimize'					=>	__('Minimize', 'ws-form'),
					'debug_restore'						=>	__('Restore', 'ws-form'),

					'debug_tools'						=>	__('Tools', 'ws-form'),
					'debug_css'							=>	__('Design', 'ws-form'),
					'debug_log'							=>	__('Log', 'ws-form'),
					'debug_error'						=>	__('Errors', 'ws-form'),

					'debug_tools_populate'				=>	__('Populate', 'ws-form'),
					'debug_tools_identify'				=>	__('Identify', 'ws-form'),
					'debug_tools_edit'					=>	__('Edit', 'ws-form'),
					'debug_tools_submissions'			=>	__('Submissions', 'ws-form'),
					'debug_tools_submit'				=>	__('Submit', 'ws-form'),
					'debug_tools_save'					=>	__('Save', 'ws-form'),
					'debug_tools_populate_submit'		=>	__('Populate & Submit', 'ws-form'),
					'debug_tools_reload'				=>	__('Reload', 'ws-form'),
					'debug_tools_form_clear'			=>	__('Clear', 'ws-form'),
					'debug_tools_form_reset'			=>	__('Reset', 'ws-form'),
					'debug_tools_clear_hash'			=>	__('Clear Session ID', 'ws-form'),
					'debug_tools_clear_log'				=>	__('Clear Log', 'ws-form'),
					'debug_tools_clear_error'			=>	__('Clear Errors', 'ws-form'),

					'debug_info_label'					=>	__('Form Name', 'ws-form'),
					'debug_info_id'						=>	__('Form ID', 'ws-form'),
					'debug_info_instance'				=>	__('Instance', 'ws-form'),
					'debug_info_hash'					=>	__('Session ID', 'ws-form'),
					'debug_info_checksum'				=>	__('Checksum', 'ws-form'),
					'debug_info_duration'				=>	__('Rendering Time', 'ws-form'),
					'debug_info_framework'				=>	__('Framework', 'ws-form'),
					'debug_info_submit_count'			=>	__('Submit Count', 'ws-form'),
					'debug_info_submit_duration_user'	=>	__('Submit Duration (User)', 'ws-form'),
					'debug_info_submit_duration_client'	=>	__('Submit Duration (Client)', 'ws-form'),
					'debug_info_submit_duration_server'	=>	__('Submit Duration (Server)', 'ws-form'),

					'debug_hash_empty'					=>	__('New Form', 'ws-form'),
					'debug_tools_publish_pending'		=>	__('Publish Pending', 'ws-form'),

					'debug_action_type'					=>	__('Type', 'ws-form'),
					'debug_action_row'					=>	__('Row', 'ws-form'),
					'debug_action_form'					=>	__('Form', 'ws-form'),
					'debug_action_group'				=>	__('Tab', 'ws-form'),
					'debug_action_section'				=>	__('Section', 'ws-form'),
					'debug_action_action'				=>	__('Action', 'ws-form'),
					'debug_action_reset'				=>	__('Reset', 'ws-form'),
					'debug_action_focussed'				=>	__('Focussed', 'ws-form'),
					'debug_action_clicked'				=>	__('Clicked', 'ws-form'),
					'debug_action_added'				=>	__('Added', 'ws-form'),
					'debug_action_removed'				=>	__('Removed', 'ws-form'),
					'debug_action_selected'				=>	__('Selected', 'ws-form'),
					'debug_action_deselected'			=>	__('Deselected', 'ws-form'),
					'debug_action_selected_value'		=>	__('Selected row by value', 'ws-form'),
					'debug_action_deselected_value'		=>	__('Deselected row by value', 'ws-form'),
					'debug_action_checked'				=>	__('Checked', 'ws-form'),
					'debug_action_unchecked'			=>	__('Unchecked', 'ws-form'),
					'debug_action_checked_value'		=>	__('Checked by value', 'ws-form'),
					'debug_action_unchecked_value'		=>	__('Unchecked by value', 'ws-form'),
					'debug_action_required'				=>	__('Required', 'ws-form'),
					'debug_action_not_required'			=>	__('Not required', 'ws-form'),
					'debug_action_disabled'				=>	__('Disabled', 'ws-form'),
					'debug_action_enabled'				=>	__('Enabled', 'ws-form'),
					'debug_action_hide'					=>	__('Hide', 'ws-form'),
					'debug_action_show'					=>	__('Show', 'ws-form'),
					'debug_action_read_only'			=>	__('Read only', 'ws-form'),
					'debug_action_not_read_only'		=>	__('Not read only', 'ws-form'),

					'debug_submit_loaded'				=>	__('Retrieved submit data', 'ws-form'),

					'debug_action_get'					=>	__('Retrieved %s action data', 'ws-form'),

					'debug_tracking_geo_location_permission_denied'		=>	__('User denied the request for geo location', 'ws-form'),
					'debug_tracking_geo_location_position_unavailable'	=>	__('Geo location information was unavailable', 'ws-form'),
					'debug_tracking_geo_location_timeout'				=>	__('The request to get user geo location timed out', 'ws-form'),
					'debug_tracking_geo_location_default'				=>	__('An unknown error occurred whilst retrieving geo location', 'ws-form'),

					// Log
					'log_hash_set'								=>	__('Session ID received: %s', 'ws-form'),
					'log_hash_clear'							=>	__('Session ID cleared', 'ws-form'),
					'log_conditional_fired_then'				=>	__("Conditional matched, running 'THEN': %s", 'ws-form'),
					'log_conditional_fired_else'				=>	__("Conditional matched, running 'ELSE': %s", 'ws-form'),
					'log_conditional_action_then'				=>	__("THEN run: %s", 'ws-form'),
					'log_conditional_action_else'				=>	__("ELSE run: %s", 'ws-form'),
					'log_conditional_action_not_found_then'		=>	__("THEN not found", 'ws-form'),
					'log_conditional_action_not_found_else'		=>	__("ELSE not found", 'ws-form'),
					'log_conditional_event'						=>	__("Added event handler for %s", 'ws-form'),
					'log_analytics_google_loaded_analytics_js'	=>	__('Google Analytics successfully loaded (analytics.js)', 'ws-form'),
					'log_analytics_google_loaded_gtag_js'		=>	__('Google Analytics successfully loaded (gtag.js)', 'ws-form'),
					'log_analytics_google_loaded_ga_js'			=>	__('Google Analytics successfully loaded (ga.js)', 'ws-form'),
					'log_analytics_facebook_loaded_fbevents_js'	=>	__('Facebook Analytics successfully loaded (fbevents.js)', 'ws-form'),
					'log_analytics_event_field'					=>	__('Analytics field events initialized: %s', 'ws-form'),
					'log_analytics_event_field_fired'			=>	__('Analytics field event ran: %s', 'ws-form'),
					'log_analytics_event_field_failed'			=>	__('Analytics field event failed: %s (Function does not exist)', 'ws-form'),
					'log_analytics_event_tab'					=>	__('Analytics tab events initialized: %s', 'ws-form'),
					'log_analytics_event_tab_fired'				=>	__('Analytics tab event ran: %s', 'ws-form'),
					'log_recaptcha_v3_action_fired'				=>	__('reCAPTCHA V3 action ran: %s', 'ws-form'),
					'log_javascript'							=>	__('Javascript ran', 'ws-form'),
					'log_honeypot'								=>	__('Spam protection - Honeypot initialized', 'ws-form'),
					'log_tracking_geo_location'					=>	__('Tracking - Geo location: %s', 'ws-form'),
					'log_submit_lock'							=>	__('Duplication protection - Button(s) locked', 'ws-form'),
					'log_submit_unlock'							=>	__('Duplication protection - Button(s) unlocked', 'ws-form'),
					'log_form_submit'							=>	__('Form submitted', 'ws-form'),
					'log_form_save'								=>	__('Form saved', 'ws-form'),
					'log_group_index'							=>	__('Set tab index to: %s', 'ws-form'),
					'log_action'								=>	__('Actions - %s', 'ws-form'),
					'log_trigger'								=>	__("JQuery $(document).trigger('%s', form, form_id, instance_id, form_obj, form_canvas_obj) ran"),
					'log_ecommerce_status'						=>	__('Ecommerce - Status set to: %s', 'ws-form'),
					'log_ecommerce_transaction_id'				=>	__('Ecommerce - Transaction ID set to: %s', 'ws-form'),
					'log_ecommerce_payment_method'				=>	__('Ecommerce - Payment method set to: %s', 'ws-form'),
					'log_ecommerce_payment_amount'				=>	__('Ecommerce - Payment amount set to: %s', 'ws-form'),
					'log_payment'								=>	__('Payments - %s', 'ws-form'),
					'log_calc_registered'						=>	__('Calculation registered: %s', 'ws-form'),
					'log_calc_registered_triggered'				=>	__('Triggered by: %s', 'ws-form'),
					'log_calc_fired'							=>	__('Calculation fired: %s', 'ws-form'),
					'log_calc_fired_triggered'					=>	__('Triggered by: %s', 'ws-form'),
					'log_calc_init'								=>	__('Initial calculation', 'ws-form'),

					// Errors
					'error_data_grid_source_type'				=>	__('Data grid source type not specified', 'ws-form'),
					'error_data_grid_source_id'					=>	__('Data grid source ID not specified', 'ws-form'),
					'error_data_source_data'					=>	__('Data source data not found', 'ws-form'),
					'error_data_source_columns'					=>	__('Data source columns not found', 'ws-form'),
					'error_data_source_groups'					=>	__('Data source groups not found', 'ws-form'),
					'error_data_source_group_label'				=>	__('Data source group label not found', 'ws-form'),
					'error_data_group_rows'						=>	__('Data source group rows not found', 'ws-form'),
					'error_data_group_label'					=>	__('Data source group label not found', 'ws-form'),
					'error_mask_help'							=>	__('No help mask defined: %s', 'ws-form'),
					'error_mask_invalid_feedback'				=>	__('No invalid feedback mask defined', 'ws-form'),
					'error_api_call_hash'						=>	__('Hash not returned in API call', 'ws-form'),
					'error_api_call_hash_invalid'				=>	__('Invalid hash returned in API call', 'ws-form'),
					'error_api_call_framework_invalid'			=>	__('Framework config not found', 'ws-form'),
					'error_recaptcha_v2_hidden'					=>	__('reCAPTCHA V2 hidden error', 'ws-form'),
					'error_timeout_recaptcha'					=>	__('Timeout waiting for reCAPTCHA to load', 'ws-form'),
					'error_timeout_signature'					=>	__('Timeout waiting for signature component to load', 'ws-form'),
					'error_timeout_analytics_google'			=>	__('Timeout waiting for Google Analytics to load', 'ws-form'),
					'error_timeout_datetimepicker'				=>	__('Timeout waiting for datetimepicker component to load', 'ws-form'),
					'error_timeout_minicolors'					=>	__('Timeout waiting for minicolors component to load', 'ws-form'),
					'error_timeout_inputmask'					=>	__('Timeout waiting for inputmask component to load', 'ws-form'),
					'error_datetime_default_value'				=>	__('Default date/time value invalid (%s)', 'ws-form'),
					'error_framework_tabs_activate_js'			=>	__('Framework tab activate JS not defined', 'ws-form'),
					'error_form_draft'							=>	__('Form is in draft', 'ws-form'),
					'error_form_future'							=>	__('Form is scheduled', 'ws-form'),
					'error_form_trash'							=>	__('Form is trashed', 'ws-form'),

					// Calc error
					'error_calc'						=>	__('Calculation error: %s'),

					// Errors - Word and character counts
					'error_min_length'					=>	__('Minimum character count: %s', 'ws-form'),
					'error_max_length'					=>	__('Maximum character count: %s', 'ws-form'),
					'error_min_length_words'			=>	__('Minimum word count: %s', 'ws-form'),
					'error_max_length_words'			=>	__('Maximum word count: %s', 'ws-form'),

					'error_framework_plugin'			=>	__('Framework plugin not found: %s', 'ws-form'),

					'error_tracking_geo_location'		=>	__('Tracking - Geo location error: %s', 'ws-form'),

					'error_action'						=>	__('Actions - %s', 'ws-form'),

					'error_payment'						=>	__('Payments - %s', 'ws-form'),

					'error_termageddon'					=>	__('Error retrieving Termageddon content', 'ws-form'),
					'error_termageddon_404'				=>	__('Invalid Termageddon key', 'ws-form'),

					'section_button_no_section'			=>	__('No section assigned to this button', 'ws-form'),
					'section_icon_no_section'			=>	__('No section assigned to these icons', 'ws-form'),
					'section_icon_not_in_own_section'	=>	__('Icon %s must be in its own assigned section', 'ws-form'),

					// Submissions
					'submit_duration_hours'				=>	__('Hours', 'ws-form'),
					'submit_duration_minutes'			=>	__('Minutes', 'ws-form'),
					'submit_duration_seconds'			=>	__('Seconds', 'ws-form'),

					// Shortcode
					'shortcode_failed'					=>	__('Unable to retrieve shortcode for field ID: %s', 'ws-form')
				);

				// Add to language array
				$settings_form_public['language'] = array();
				foreach($language_extra as $key => $value) {

					$settings_form_public['language'][$key] = $value;
				}
			}

			// Add conditional debug
			$settings_form_admin = self::get_settings_form_admin();
			$settings_form_public['conditional'] = $settings_form_admin['conditional'];

			// Group, section, field
			if(!$debug) {

				foreach($settings_form_public['conditional']['objects'] as $object_key => $object) {

					// Remove object text
					unset($settings_form_public['conditional']['objects'][$object_key]['text']);

					$public_attributes_strip = array(

						'logic'		=>	array('text', 'values', 'type', 'case_sensitive', 'min', 'max', 'unit'),
						'action'	=>	array('text', 'values', 'auto_else', 'auto_else_copy', 'value_row_ids', 'type')
					);

					// Logic, Action to filter
					foreach($public_attributes_strip as $key => $attributes) {

						// Individual logics and actions
						foreach($object[$key] as $key_inner => $attributes_inner) {

							// Attributes of logic or action
							foreach($attributes as $attribute) {
								
								if(isset($settings_form_public['conditional']['objects'][$object_key][$key][$key_inner][$attribute])) {
									unset($settings_form_public['conditional']['objects'][$object_key][$key][$key_inner][$attribute]);
								}
							}

							if(count($settings_form_public['conditional']['objects'][$object_key][$key][$key_inner]) == 0) {

								unset($settings_form_public['conditional']['objects'][$object_key][$key][$key_inner]);
							}
						}
					}
				}
			}

			// Apply filter
			$settings_form_public = apply_filters('wsf_config_settings_form_public', $settings_form_public);

			return $settings_form_public;
		}

		// Configuration - Settings (Shared with admin and public)
		public static function get_settings_form($public = true) {

			// Check if debug is enabled
			$debug = WS_Form_Common::debug_enabled();
			$settings_form = array(

				// Language
				'language'	=> array(

					// Errors
					'error_attributes'					=>	__('No attributes specified.', 'ws-form'),
					'error_attributes_obj'				=>	__('No attributes object specified.', 'ws-form'),
					'error_attributes_form_id'			=>	__('No attributes form ID specified.', 'ws-form'),
					'error_form_id'						=>	__('Form ID not specified.', 'ws-form'),
					'error_bad_request'					=>	__('400 Bad request response from server.', 'ws-form'),
					'error_bad_request_message'			=>	__('400 Bad request response from server: %s', 'ws-form'),
					'error_forbidden'					=>	__('403 Forbidden response from server. <a href="https://wsform.com/knowledgebase/403-forbidden/" target="_blank">Learn more</a>.', 'ws-form'),
					'error_not_found'					=>	__('404 Not found response from server.', 'ws-form'),
					'error_server'						=>	__('500 Server error response from server.', 'ws-form'),
					'error_pro_required'				=>	__('WS Form PRO required.', 'ws-form'),

					// Error message
					'dismiss'							=>  __('Dismiss', 'ws-form'),

					// Comments
					'comment_group_tabs'				=>	__('Tabs', 'ws-form'),
					'comment_groups'					=>	__('Tabs Content', 'ws-form'),
					'comment_group'						=>	__('Tab', 'ws-form'),
					'comment_sections'					=>	__('Sections', 'ws-form'),
					'comment_section'					=>	__('Section', 'ws-form'),
					'comment_fields'					=>	__('Fields', 'ws-form'),
					'comment_field'						=>	__('Field', 'ws-form'),

					// Word and character counts
					'character_singular'				=>	__('character', 'ws-form'),
					'character_plural'					=>	__('characters', 'ws-form'),
					'word_singular'						=>	__('word', 'ws-form'),
					'word_plural'						=>	__('words', 'ws-form'),

					// Select all
					'select_all_label'					=>	__('Select All', 'ws-form'),
					// Password strength
					'password_strength_unknown'			=> __( 'Password strength unknown', 'ws-form'),
					'password_strength_short'			=> __( 'Very weak', 'ws-form'),
					'password_strength_bad'				=> __( 'Weak', 'ws-form'),
					'password_strength_good'			=> __( 'Medium', 'ws-form'),
					'password_strength_strong'			=> __( 'Strong', 'ws-form'),
					'password_strength_mismatch'		=> __( 'Mismatch', 'ws-form' ),

					// Section icons
					'section_icon_add'					=>  __('Add', 'ws-form'),
					'section_icon_delete'				=>  __('Remove', 'ws-form'),
					'section_icon_move-up'				=>  __('Move Up', 'ws-form'),
					'section_icon_move-down'			=>  __('Move Down', 'ws-form'),
					'section_icon_drag'					=>  __('Drag', 'ws-form'),

					// Parse variables
					'error_parse_variable_syntax_error_brackets'			=>	__('Syntax error, missing brackets: %s'),
					'error_parse_variable_syntax_error_bracket_closing'		=>	__('Syntax error, missing closing bracket: %s'),
					'error_parse_variable_syntax_error_attribute'			=>	__('Syntax error, missing attribute: %s'),
					'error_parse_variable_syntax_error_attribute_invalid'	=>	__('Syntax error, invalid attribute: %s'),
					'error_parse_variable_syntax_error_depth'				=>	__('Syntax error, too many iterations'),
					'error_parse_variable_syntax_error_field_id'			=>	__('Syntax error, invalid field ID: %s'),
					'error_parse_variable_syntax_error_section_id'			=>	__('Syntax error, invalid section ID: %s'),
					'error_parse_variable_syntax_error_calc_loop'			=>	__('Syntax error, calculated fields cannot contain references to themselves: %s'),
					'error_parse_variable_syntax_error_calc_in'				=>	__('Syntax error, calculated fields cannot be added to this field: %s'),
					'error_parse_variable_syntax_error_calc_out'			=>	__('Syntax error, calculated fields cannot be retrieved from this field: %s'),
				)
			);

			// Conditional
			if(!$public || $debug) {

				// Additional language strings for admin or public debug feature
				$language_extra = array(

					'error_conditional_if'				=>	__('Condition [if] not found', 'ws-form'),
					'error_conditional_then'			=>	__('Condition [then] not found', 'ws-form'),
					'error_conditional_else'			=>	__('Condition [else] not found', 'ws-form'),
					'error_conditional_settings'		=>	__('Conditional settings not found', 'ws-form'),
					'error_conditional_data_grid'		=>	__('Condition field data not found', 'ws-form'),
					'error_conditional_object'			=>	__('Condition object not found', 'ws-form'),
					'error_conditional_object_id'		=>	__('Condition object ID not found', 'ws-form'),
					'error_conditional_logic'			=>	__('Condition logic not found: %s', 'ws-form'),
					'error_conditional_logic_previous'	=>	__('Condition logic previous not found: %s', 'ws-form'),
					'error_conditional_logic_previous_group'	=>	__('Group logic previous not found', 'ws-form'),
				);

				// Add to language array
				foreach($language_extra as $key => $value) {

					$settings_form['language'][$key] = $value;
				}
			}
			// Apply filter
			$settings_form = apply_filters('wsf_config_settings_form', $settings_form);

			return $settings_form;
		}

		// Get plug-in settings
		public static function get_settings_plugin($public = true) {

			// Check cache
			if(isset(self::$settings_plugin[$public])) { return self::$settings_plugin[$public]; }

			$settings_plugin = [];

			// Plugin options
			$options = self::get_options();

			// Set up options with default values
			foreach($options as $tab => $data) {

				if(isset($data['fields'])) {

					self::get_settings_plugin_process($data['fields'], $public, $settings_plugin);
				}

				if(isset($data['groups'])) {

					$groups = $data['groups'];

					foreach($groups as $group) {

						self::get_settings_plugin_process($group['fields'], $public, $settings_plugin);
					}
				}
			}

			// Apply filter
			$settings_plugin = apply_filters('wsf_config_settings_plugin', $settings_plugin);

			// Cache
			self::$settings_plugin[$public] = $settings_plugin;

			return $settings_plugin;
		}

		// Get plug-in settings process
		public static function get_settings_plugin_process($fields, $public, &$settings_plugin) {

			foreach($fields as $field => $attributes) {

				// Skip field if public only?
				$field_skip = false;
				if($public) {

					$field_skip = !isset($attributes['public']) || !$attributes['public'];
				}
				if($field_skip) { continue; }

				// Get default value (if available)
				if(isset($attributes['default'])) { $default_value = $attributes['default']; } else { $default_value = ''; }

				// Get option value
				$settings_plugin[$field] = WS_Form_Common::option_get($field, $default_value);
			}
		}

		// Configuration - Meta Keys
		public static function get_meta_keys($form_id = 0, $public = false) {

			// Check cache
			if(isset(self::$meta_keys[$public])) { return self::$meta_keys[$public]; }

			$label_position = array(

				array('value' => 'top', 'text' => __('Top', 'ws-form')),
				array('value' => 'right', 'text' => __('Right', 'ws-form')),
				array('value' => 'bottom', 'text' => __('Bottom', 'ws-form')),
				array('value' => 'left', 'text' => __('Left', 'ws-form'))
			);

			$button_types = array(

				array('value' => '', 			'text' => __('Default', 'ws-form')),
				array('value' => 'primary', 	'text' => __('Primary', 'ws-form')),
				array('value' => 'secondary', 	'text' => __('Secondary', 'ws-form')),
				array('value' => 'success', 	'text' => __('Success', 'ws-form')),
				array('value' => 'information', 'text' => __('Information', 'ws-form')),
				array('value' => 'warning', 	'text' => __('Warning', 'ws-form')),
				array('value' => 'danger', 		'text' => __('Danger', 'ws-form'))
			);

			$message_types = array(

				array('value' => 'success', 	'text' => __('Success', 'ws-form')),
				array('value' => 'information', 'text' => __('Information', 'ws-form')),
				array('value' => 'warning', 	'text' => __('Warning', 'ws-form')),
				array('value' => 'danger', 		'text' => __('Danger', 'ws-form'))
			);

			$vertical_align = array(

				array('value' => '', 'text' => __('Top', 'ws-form')),
				array('value' => 'middle', 'text' => __('Middle', 'ws-form')),
				array('value' => 'bottom', 'text' => __('Bottom', 'ws-form'))
			);

			$meta_keys = array(

				// Forms

				// Should tabs be remembered?
				'cookie_tab_index'		=>	array(

					'label'		=>	__('Remember Last Tab Clicked', 'ws-form'),
					'type'		=>	'checkbox',
					'help'		=>	__('Should the last tab clicked be remembered?', 'ws-form'),
					'default'	=>	true
				),

				'tab_validation'		=>	array(

					'label'		=>	__('Tab Validation', 'ws-form'),
					'type'		=>	'checkbox',
					'help'		=>	__('Prevent the user from advancing to the next tab until the current tab is validated.', 'ws-form'),
					'default'	=>	false
				),

				// Add HTML to required labels
				'label_required'		=>	array(

					'label'			=>	__("Render 'Required' HTML", 'ws-form'),
					'type'			=>	'checkbox',
					'default'		=>	true,
					'help'			=>	__("Should '*' be added to labels if a field is required?", 'ws-form')
				),

				// Add HTML to required labels
				'label_mask_required'	=>	array(

					'label'			=>	__("Custom 'Required' HTML", 'ws-form'),
					'type'			=>	'text',
					'default'		=>	'',
					'help'			=>	__('Example: &apos; &lt;small&gt;Required&lt;/small&gt;&apos;.', 'ws-form'),
					'select_list'				=>	array(

						array('text' => __('&lt;small&gt;Required&lt;/small&gt;', 'ws-form'), 'value' => ' <small>Required</small>')
					),
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'label_required',
							'meta_value'	=>	'on'
						)
					)
				),

				// Hidden
				'hidden'		=>	array(

					'label'						=>	__('Hidden', 'ws-form'),
					'mask'						=>	'data-hidden',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),

				'hidden_section'				=> array(

					'label'						=>	__('Hidden', 'ws-form'),
					'mask'						=>	'data-hidden',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),

				// Fields
				// Recaptcha
				'recaptcha'		=> array(

					'label'						=>	__('reCAPTCHA', 'ws-form'),
					'type'						=>	'recaptcha',
					'dummy'						=>	true
				),

				// Breakpoint sizes grid
				'breakpoint_sizes'		=> array(

					'label'						=>	__('Breakpoint Sizes', 'ws-form'),
					'type'						=>	'breakpoint_sizes',
					'dummy'						=>	true,
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'invisible'
						)
					)
				),

				// Spam Protection - Honeypot
				'honeypot'		=> array(

					'label'						=>	__('HoneyPot', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Adds a hidden field to fool spammers.', 'ws-form'),
				),

				// Spam Protection - Threshold
				'spam_threshold'	=> array(

					'label'						=>	__('Spam Threshold', 'ws-form'),
					'type'						=>	'range',
					'default'					=>	50,
					'min'						=>	0,
					'max'						=>	100,
					'help'						=>	__('If your form is configured to check for spam (e.g. Akismet or reCAPTCHA), each submission will be given a score between 0 (Not spam) and 100 (Blatant spam). Use this setting to determine the minimum score that will move a submission into the spam folder.', 'ws-form'),
				),

				// Duplicate Protection - Lock submit
				'submit_lock'		=> array(

					'label'						=>	__('Lock Save &amp; Submit Buttons', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('Lock save and submit buttons when form is saved or submitted so that they cannot be double clicked.', 'ws-form')
				),

				// Duplicate Protection - Lock submit
				'submit_unlock'		=> array(

					'label'						=>	__('Unlock Save &amp; Submit Buttons', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('Unlock save and submit buttons after form is saved or submitted.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'submit_lock',
							'meta_value'		=>	'on'
						)
					)
				),

				// Legal - Source
				'legal_source'		=> array(

					'label'						=>	__('Source', 'ws-form'),
					'type'						=>	'select',
					'mask'						=>	'data-wsf-legal-source="#value"',
					'mask_disregard_on_empty'	=>	true,
					'default'					=>	'termageddon',
					'options'					=>	array(

						array('value' => 'termageddon', 'text' => __('Termageddon', 'ws-form')),
						array('value' => '', 'text' => __('Own Copy', 'ws-form'))
					)
				),

				// Legal - Termageddon - Key
				'legal_termageddon_intro'		=> array(

					'type'						=>	'html',
					'html'						=>	sprintf('<a href="http://app.termageddon.com?fp_ref=westguard" target="_blank"><img src="%s/includes/third-party/termageddon/images/logo.gif" width="150" height="22" alt="Termageddon" /></a><div class="wsf-helper">%s</div>', WS_FORM_PLUGIN_DIR_URL, __('Termageddon is a third party service that generates policies for U.S. websites and apps and updates them whenever the laws change. WS Form has no control over and accepts no liability in respect of this service and content.', 'ws-form')),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'legal_source',
							'meta_value'		=>	'termageddon'
						)
					)
				),

				// Legal - Termageddon - Key
				'legal_termageddon_key'		=> array(

					'label'						=>	__('Key', 'ws-form'),
					'type'						=>	'text',
					'mask'						=>	'data-wsf-termageddon-key="#value"',
					'mask_disregard_on_empty'	=>	true,
					'default'					=>	'',
					'help'						=>	__('Need a key? <a href="http://app.termageddon.com?fp_ref=westguard" target="_blank">Register</a>'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'legal_source',
							'meta_value'		=>	'termageddon'
						)
					)
				),

				// Legal - Termageddon - Hide title
				'legal_termageddon_hide_title'		=> array(

					'label'						=>	__('Hide Title', 'ws-form'),
					'type'						=>	'checkbox',
					'mask'						=>	'data-wsf-termageddon-extra="no-title=true"',
					'mask_disregard_on_empty'	=>	true,
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'legal_source',
							'meta_value'		=>	'termageddon'
						)
					)
				),

				// Legal - Own copy
				'legal_text_editor'		=> array(

					'label'						=>	__('Legal Copy', 'ws-form'),
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Enter the legal copy you would like to display.', 'ws-form'),
					'select_list'				=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'legal_source',
							'meta_value'		=>	''
						)
					),
					'key'						=>	'text_editor'
				),

				// Legal - Style - Height
				'legal_style_height'	=> array(

					'label'						=>	__('Height (pixels)', 'ws-form'),
					'type'						=>	'number',
					'mask'						=>	'style="height:#valuepx;overflow-y:scroll;"',
					'mask_disregard_on_empty'	=>	true,
					'default'					=>	'200',
					'help'						=>	__('Setting this to blank will remove the height restriction.', 'ws-form')
				),

				// Analytics - Google
				'analytics_google'		=> array(

					'label'						=>	__('Enable Google Analytics Tracking', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	''
				),

				// Analytics - Google - Field events
				'analytics_google_event_field'		=> array(

					'label'						=>	__('Fire Field Events', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'analytics_google',
							'meta_value'	=>	'on'
						)
					),
					'indent'					=>	true
				),

				// Analytics - Google - Tab events
				'analytics_google_event_tab'		=> array(

					'label'						=>	__('Fire Tab Events', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'analytics_google',
							'meta_value'	=>	'on'
						)
					),
					'indent'					=>	true
				),

				// Tracking - Remote IP address
				'tracking_remote_ip'		=> array(

					'label'						=>	__('Remote IP Address', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Users remote IP address.', 'ws-form')
				),

				// Tracking - Geo Location
				'tracking_geo_location'		=> array(

					'label'						=>	__('Geographical Location (Browser)', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Latitude & longitude (User may be prompted to grant you permissions to this information).', 'ws-form')
				),

				// Tracking - Referrer
				'tracking_referrer'		=> array(

					'label'						=>	__('Referrer', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Referring page.', 'ws-form')
				),

				// Tracking - OS
				'tracking_os'		=> array(

					'label'						=>	__('Operating System', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Users operating system.', 'ws-form')
				),

				// Tracking - Agent
				'tracking_agent'		=> array(

					'label'						=>	__('Agent', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Users web browser type.', 'ws-form')
				),

				// Tracking - Hostname
				'tracking_host'			=> array(

					'label'						=>	__('Hostname', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Server hostname.', 'ws-form')
				),

				// Tracking - Pathname
				'tracking_pathname'	=> array(

					'label'						=>	__('Pathname', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Pathname of the URL.', 'ws-form')
				),

				// Tracking - Query String
				'tracking_query_string'	=> array(

					'label'						=>	__('Query String', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Query string of the URL.', 'ws-form')
				),

				// Tracking - UTM - Campaign source
				'tracking_utm_source'	=> array(

					'label'						=>	__('UTM Source', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Campaign source (e.g. website name).', 'ws-form')
				),

				// Tracking - UTM - Campaign medium
				'tracking_utm_medium'	=> array(

					'label'						=>	__('UTM Medium', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Campaign medium (e.g. email).', 'ws-form')
				),

				// Tracking - UTM - Campaign name
				'tracking_utm_campaign'	=> array(

					'label'						=>	__('UTM Campaign', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Campaign name.', 'ws-form')
				),

				// Tracking - UTM - Campaign term
				'tracking_utm_term'	=> array(

					'label'						=>	__('UTM Term', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Campaign term (e.g. keyword).', 'ws-form')
				),

				// Tracking - UTM - Campaign content
				'tracking_utm_content'	=> array(

					'label'						=>	__('UTM Content', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Campaign content (e.g. text link).', 'ws-form')
				),

				// Tracking - IP Lookup - City
				'tracking_ip_lookup_city'	=> array(

					'label'						=>	__('City (By IP)', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Attempt to get city from users IP address.', 'ws-form')
				),

				// Tracking - IP Lookup - Region
				'tracking_ip_lookup_region'	=> array(

					'label'						=>	__('Region (By IP)', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Attempt to get region from users IP address.', 'ws-form')
				),

				// Tracking - IP Lookup - Country
				'tracking_ip_lookup_country'	=> array(

					'label'						=>	__('Country (By IP)', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Attempt to get country from users IP address.', 'ws-form')
				),

				// Tracking - IP Lookup - Country
				'tracking_ip_lookup_latlon'	=> array(

					'label'						=>	__('Geographical Location (By IP)', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Attempt to get latitude and longitude from users IP address.', 'ws-form')
				),
				// Focus on invalid fields
				'invalid_field_focus'		=> array(

					'label'						=>	__('Focus Invalid Fields', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('On form submit, should a field be focussed?', 'ws-form')
				),
				// Submission limit
				'submit_limit'		=> array(

					'label'						=>	__('Limit By Submission Count', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Limit number of submissions for this form.', 'ws-form')
				),

				'submit_limit_count'		=> array(

					'label'						=>	__('Maximum Count', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	1,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'submit_limit',
							'meta_value'		=>	'on'
						)
					)
				),

				'submit_limit_period'		=> array(

					'label'						=>	__('Duration', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	array(

						array('value' => '', 'text' => __('All Time', 'ws-form')),
						array('value' => 'hour', 'text' => __('Per Hour', 'ws-form')),
						array('value' => 'day', 'text' => __('Per Day', 'ws-form')),
						array('value' => 'week', 'text' => __('Per Week', 'ws-form')),
						array('value' => 'month', 'text' => __('Per Month', 'ws-form')),
						array('value' => 'year', 'text' => __('Per Year', 'ws-form'))
					),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'submit_limit',
							'meta_value'		=>	'on'
						)
					)
				),

				'submit_limit_message'			=> array(

					'label'						=>	__('Limit Reached Message', 'ws-form'),
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Enter the message you would like to show if the submisson limit is reached. Leave blank to hide form.', 'ws-form'),
					'select_list'				=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'submit_limit',
							'meta_value'		=>	'on'
						)
					)
				),

				'submit_limit_message_type'		=> array(

					'label'						=>	__('Message Style', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'success', 'text' => __('Success', 'ws-form')),
						array('value' => 'information', 'text' => __('Information', 'ws-form')),
						array('value' => 'warning', 'text' => __('Warning', 'ws-form')),
						array('value' => 'danger', 'text' => __('Danger', 'ws-form'))
					),
					'default'					=>	'information',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'submit_limit',
							'meta_value'		=>	'on'
						)
					)
				),

				// Form scheduling
				'schedule_start'			=> array(

					'label'						=>	__('Schedule Start', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Schedule a start date/time for the form.', 'ws-form')
				),

				'schedule_start_datetime'	=> array(

					'label'						=>	__('Start Date/Time', 'ws-form'),
					'type'						=>	'datetime',
					'default'					=>	'',
					'help'						=>	__('Date/time form is scheduled to start.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_start',
							'meta_value'		=>	'on'
						)
					)
				),

				'schedule_start_message'		=> array(

					'label'						=>	__('Before Message', 'ws-form'),
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Message shown before the form start date/time. Leave blank to hide form.', 'ws-form'),
					'select_list'				=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_start',
							'meta_value'		=>	'on'
						)
					)
				),

				'schedule_start_message_type'	=> array(

					'label'						=>	__('Before Message Style', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'success', 'text' => __('Success', 'ws-form')),
						array('value' => 'information', 'text' => __('Information', 'ws-form')),
						array('value' => 'warning', 'text' => __('Warning', 'ws-form')),
						array('value' => 'danger', 'text' => __('Danger', 'ws-form'))
					),
					'default'					=>	'information',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_start',
							'meta_value'		=>	'on'
						)
					)
				),

				'schedule_end'					=> array(

					'label'						=>	__('Schedule End', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Schedule an end date/time for the form.', 'ws-form')
				),

				'schedule_end_datetime'			=> array(

					'label'						=>	__('End Date/Time', 'ws-form'),
					'type'						=>	'datetime',
					'default'					=>	'',
					'help'						=>	__('Date/time form is scheduled to end.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_end',
							'meta_value'		=>	'on'
						)
					)
				),

				'schedule_end_message'	=> array(

					'label'						=>	__('After Message', 'ws-form'),
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Message shown after the form end date/time. Leave blank to hide form.', 'ws-form'),
					'select_list'				=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_end',
							'meta_value'		=>	'on'
						)
					)
				),

				'schedule_end_message_type'		=> array(

					'label'						=>	__('After Message Style', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'success', 'text' => __('Success', 'ws-form')),
						array('value' => 'information', 'text' => __('Information', 'ws-form')),
						array('value' => 'warning', 'text' => __('Warning', 'ws-form')),
						array('value' => 'danger', 'text' => __('Danger', 'ws-form'))
					),
					'default'					=>	'information',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'schedule_end',
							'meta_value'		=>	'on'
						)
					)
				),

				// User limits
				'user_limit_logged_in'	=> array(

					'label'						=>	__('User Status', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	array(

						array('value' => '', 'text' => __('Any', 'ws-form')),
						array('value' => 'on', 'text' => __('Is Logged In', 'ws-form')),
						array('value' => 'out', 'text' => __('Is Logged Out', 'ws-form'))
					),
					'help'						=>	__('Only show the form under certain user conditions.', 'ws-form')
				),

				'user_limit_logged_in_message'	=> array(

					'label'						=>	__('User Invalid Status Message', 'ws-form'),
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Message shown if the user does not meet the user status condition. Leave blank to hide form.', 'ws-form'),
					'select_list'				=>	true,
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'user_limit_logged_in',
							'meta_value'		=>	''
						)
					)
				),

				'user_limit_logged_in_message_type'		=> array(

					'label'						=>	__('Message Style', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'success', 'text' => __('Success', 'ws-form')),
						array('value' => 'information', 'text' => __('Information', 'ws-form')),
						array('value' => 'warning', 'text' => __('Warning', 'ws-form')),
						array('value' => 'danger', 'text' => __('Danger', 'ws-form'))
					),
					'default'					=>	'danger',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'user_limit_logged_in',
							'meta_value'		=>	'on'
						)
					)
				),
				// Submit on enter
				'submit_on_enter'	=> array(

					'label'						=>	__('Enable Form Submit On Enter', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Allow the form to be submitted if someone types Enter/Return. Not advised for e-commerce forms.', 'ws-form')
				),

				// Reload on submit
				'submit_reload'		=> array(

					'label'						=>	__('Reset Form After Submit', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('Should the form be reset to its default state after it is submitted?', 'ws-form')
				),

				// Form action
				'form_action'		=> array(

					'label'						=>	__('Custom Form Action', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Enter a custom action for this form. Leave blank to use WS Form (Recommended).', 'ws-form')
				),

				// Show errors on submit
				'submit_show_errors'			=> array(

					'label'						=>	__('Show Error Messages', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('If a server side error occurs when a form is submitted, should WS Form show those as form error messages?', 'ws-form')
				),

				// Render label checkbox (On by default)
				'label_render'					=> array(

					'label'						=>	__('Render Label', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on'
				),

				// Render label checkbox (Off by default)
				'label_render_off'				=> array(

					'label'						=>	__('Render Label', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'key'						=>	'label_render'
				),

				// Label position (Form)
				'label_position_form'			=> array(

					'label'						=>	__('Default Label Position', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Select the default position of field labels.', 'ws-form'),
					'options'					=>	$label_position,
					'options_framework_filter'	=>	'label_positions',
					'default'					=>	'top'
				),

				// Label position
				'label_position'		=> array(

					'label'						=>	__('Label Position', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Select the position of the field label.', 'ws-form'),
					'options'					=>	$label_position,
					'options_default'			=>	'label_position_form',
					'options_framework_filter'	=>	'label_positions',
					'default'					=>	'default',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'label_render',
							'meta_value'		=>	'on'
						)
					)
				),

				// Label column width
				'label_column_width_form'				=> array(

					'label'						=>	__('Default Label Width (Columns)', 'ws-form'),
					'type'						=>	'select_number',
					'default'					=>	3,
					'minimum'					=>	1,
					'maximum'					=>	'framework_column_count',
					'help'						=>	__('Column width of labels if positioned left or right.', 'ws-form')
				),

				// Label column width
				'label_column_width'				=> array(

					'label'						=>	__('Label Width (Columns)', 'ws-form'),
					'type'						=>	'select_number',
					'options_default'			=>	'label_column_width_form',
					'default'					=>	'default',
					'minimum'					=>	1,
					'maximum'					=>	'framework_column_count',
					'help'						=>	__('Column width of label.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'label_position',
							'meta_value'		=>	'left'
						),

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'label_position',
							'meta_value'		=>	'right',
							'logic_previous'	=>	'||'
						)
					)
				),

				// reCAPTCHA - Site key
				'recaptcha_site_key'	=> array(

					'label'						=>	__('Site Key', 'ws-form'),
					'mask'						=>	'data-site-key="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('reCAPTCHA site key.', 'ws-form'),
					'required_setting'			=>	true,
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),

				// reCAPTCHA - Secret key
				'recaptcha_secret_key'	=> array(

					'label'						=>	__('Secret Key', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('reCAPTCHA secret key.', 'ws-form'),
					'required_setting'			=>	true,
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),

				// reCAPTCHA - reCAPTCHA type
				'recaptcha_recaptcha_type'		=> array(

					'label'						=>	__('reCAPTCHA Type', 'ws-form'),
					'mask'						=>	'data-recaptcha-type="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Select the reCAPTCHA version your site key relates to.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'v2_default', 'text' => __('Version 2 - Default', 'ws-form')),
						array('value' => 'v2_invisible', 'text' => __('Version 2 - Invisible', 'ws-form')),
						array('value' => 'v3_default', 'text' => __('Version 3', 'ws-form')),
					),
					'default'					=>	'v2_default'
				),

				// reCAPTCHA - Badge
				'recaptcha_badge'		=> array(

					'label'						=>	__('Badge Position', 'ws-form'),
					'mask'						=>	'data-badge="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Position of the reCAPTCHA badge (Invisible only).', 'ws-form'),
					'options'					=>	array(

						array('value' => 'bottomright', 'text' => __('Bottom Right', 'ws-form')),
						array('value' => 'bottomleft', 'text' => __('Bottom Left', 'ws-form')),
						array('value' => 'inline', 'text' => __('Inline', 'ws-form'))
					),
					'default'					=>	'bottomright',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v2_invisible'
						)
					)
				),

				// reCAPTCHA - Type
				'recaptcha_type'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'mask'						=>	'data-type="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Image or audio?', 'ws-form'),
					'options'					=>	array(

						array('value' => 'image', 'text' => __('Image', 'ws-form')),
						array('value' => 'audio', 'text' => __('Audio', 'ws-form')),
					),
					'default'					=>	'image',
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v3_default'
						)
					)
				),

				// reCAPTCHA - Theme
				'recaptcha_theme'		=> array(

					'label'						=>	__('Theme', 'ws-form'),
					'mask'						=>	'data-theme="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Light or dark theme?', 'ws-form'),
					'options'					=>	array(

						array('value' => 'light', 'text' => __('Light', 'ws-form')),
						array('value' => 'dark', 'text' => __('Dark', 'ws-form')),
					),
					'default'					=>	'light',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v2_default'
						)
					)
				),

				// reCAPTCHA - Size
				'recaptcha_size'		=> array(

					'label'						=>	__('Size', 'ws-form'),
					'mask'						=>	'data-size="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Normal or compact size?', 'ws-form'),
					'options'					=>	array(

						array('value' => 'normal', 'text' => __('Normal', 'ws-form')),
						array('value' => 'compact', 'text' => __('Compact', 'ws-form')),
					),
					'default'					=>	'normal',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v2_default'
						)
					)
				),

				// reCAPTCHA - Language (Language Culture Name)
				'recaptcha_language'	=> array(

					'label'						=>	__('Language', 'ws-form'),
					'mask'						=>	'data-language="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Force the reCAPTCHA to render in a specific language?', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => 'Auto Detect'),
						array('value' => 'ar', 'text' => 'Arabic'),
						array('value' => 'af', 'text' => 'Afrikaans'),
						array('value' => 'am', 'text' => 'Amharic'),
						array('value' => 'hy', 'text' => 'Armenian'),
						array('value' => 'az', 'text' => 'Azerbaijani'),
						array('value' => 'eu', 'text' => 'Basque'),
						array('value' => 'bn', 'text' => 'Bengali'),
						array('value' => 'bg', 'text' => 'Bulgarian'),
						array('value' => 'ca', 'text' => 'Catalan'),
						array('value' => 'zh-HK', 'text' => 'Chinese (Hong Kong)'),
						array('value' => 'zh-CN', 'text' => 'Chinese (Simplified)'),
						array('value' => 'zh-TW', 'text' => 'Chinese (Traditional)'),
						array('value' => 'hr', 'text' => 'Croatian'),
						array('value' => 'cs', 'text' => 'Czech'),
						array('value' => 'da', 'text' => 'Danish'),
						array('value' => 'nl', 'text' => 'Dutch'),
						array('value' => 'en-GB', 'text' => 'English (UK)'),
						array('value' => 'en', 'text' => 'English (US)'),
						array('value' => 'et', 'text' => 'Estonian'),
						array('value' => 'fil', 'text' => 'Filipino'),
						array('value' => 'fi', 'text' => 'Finnish'),
						array('value' => 'fr', 'text' => 'French'),
						array('value' => 'fr-CA', 'text' => 'French (Canadian)'),
						array('value' => 'gl', 'text' => 'Galician'),
						array('value' => 'ka', 'text' => 'Georgian'),
						array('value' => 'de', 'text' => 'German'),
						array('value' => 'de-AT', 'text' => 'German (Austria)'),
						array('value' => 'de-CH', 'text' => 'German (Switzerland)'),
						array('value' => 'el', 'text' => 'Greek'),
						array('value' => 'gu', 'text' => 'Gujarati'),
						array('value' => 'iw', 'text' => 'Hebrew'),
						array('value' => 'hi', 'text' => 'Hindi'),
						array('value' => 'hu', 'text' => 'Hungarain'),
						array('value' => 'is', 'text' => 'Icelandic'),
						array('value' => 'id', 'text' => 'Indonesian'),
						array('value' => 'it', 'text' => 'Italian'),
						array('value' => 'ja', 'text' => 'Japanese'),
						array('value' => 'kn', 'text' => 'Kannada'),
						array('value' => 'ko', 'text' => 'Korean'),
						array('value' => 'lo', 'text' => 'Laothian'),
						array('value' => 'lv', 'text' => 'Latvian'),
						array('value' => 'lt', 'text' => 'Lithuanian'),
						array('value' => 'ms', 'text' => 'Malay'),
						array('value' => 'ml', 'text' => 'Malayalam'),
						array('value' => 'mr', 'text' => 'Marathi'),
						array('value' => 'mn', 'text' => 'Mongolian'),
						array('value' => 'no', 'text' => 'Norwegian'),
						array('value' => 'fa', 'text' => 'Persian'),
						array('value' => 'pl', 'text' => 'Polish'),
						array('value' => 'pt', 'text' => 'Portuguese'),
						array('value' => 'pt-BR', 'text' => 'Portuguese (Brazil)'),
						array('value' => 'pt-PT', 'text' => 'Portuguese (Portugal)'),
						array('value' => 'ro', 'text' => 'Romanian'),
						array('value' => 'ru', 'text' => 'Russian'),
						array('value' => 'sr', 'text' => 'Serbian'),
						array('value' => 'si', 'text' => 'Sinhalese'),
						array('value' => 'sk', 'text' => 'Slovak'),
						array('value' => 'sl', 'text' => 'Slovenian'),
						array('value' => 'es', 'text' => 'Spanish'),
						array('value' => 'es-419', 'text' => 'Spanish (Latin America)'),
						array('value' => 'sw', 'text' => 'Swahili'),
						array('value' => 'sv', 'text' => 'Swedish'),
						array('value' => 'ta', 'text' => 'Tamil'),
						array('value' => 'te', 'text' => 'Telugu'),
						array('value' => 'th', 'text' => 'Thai'),
						array('value' => 'tr', 'text' => 'Turkish'),
						array('value' => 'uk', 'text' => 'Ukrainian'),
						array('value' => 'ur', 'text' => 'Urdu'),
						array('value' => 'vi', 'text' => 'Vietnamese'),
						array('value' => 'zu', 'text' => 'Zul')
					),
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v3_default'
						)
					)
				),

				// reCAPTCHA - Action
				'recaptcha_action'		=> array(

					'label'						=>	__('Action', 'ws-form'),
					'mask'						=>	'data-recaptcha-action="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Actions run on form load. Actions may only contain alphanumeric characters and slashes, and must not be user-specific.', 'ws-form'),
					'default'					=>	'ws_form/#form_id/load',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'recaptcha_recaptcha_type',
							'meta_value'	=>	'v3_default'
						)
					)
				),

				// Signature - Dot Size
				'signature_dot_size'			=> array(

					'label'						=>	__('Pen Size', 'ws-form'),
					'mask'						=>	'data-dot-size="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'number',
					'help'						=>	__('Radius of a single dot.', 'ws-form'),
					'default'					=>	'2'
				),

				// Signature - Pen Color
				'signature_pen_color'			=> array(

					'label'						=>	__('Pen Color', 'ws-form'),
					'mask'						=>	'data-pen-color="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'help'						=>	__('Color used to draw the lines.', 'ws-form'),
					'default'					=>	'#000000'
				),

				// Signature - Background Color
				'signature_background_color'	=> array(

					'label'						=>	__('Background Color', 'ws-form'),
					'mask'						=>	'data-background-color="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'help'						=>	__('Color used for background (JPG only).', 'ws-form'),
					'default'					=>	'#ffffff',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'signature_mime',
							'meta_value'	=>	'image/jpeg'
						)
					)
				),

				// Signature - Type
				'signature_mime'			=> array(

					'label'						=>	__('Type', 'ws-form'),
					'mask'						=>	'data-mime="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Output format of signature image.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('PNG (Transparent)', 'ws-form')),
						array('value' => 'image/jpeg', 'text' => __('JPG', 'ws-form')),
						array('value' => 'image/svg+xml', 'text' => __('SVG', 'ws-form')),
					),
					'default'					=>	''
				),

				// Signature - Height
				'signature_height'			=> array(

					'label'						=>	__('Height', 'ws-form'),
					'mask'						=>	'style="height:#value; padding: 0; width: 100%;"',
					'mask_disregard_on_empty'	=>	false,
					'type'						=>	'text',
					'help'						=>	__('Height of signature canvas.', 'ws-form'),
					'default'					=>	'76px'
				),

				// Signature - Crop
				'signature_crop'			=> array(

					'label'						=>	__('Crop', 'ws-form'),
					'mask'						=>	'data-crop',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'help'						=>	__('Should the signature be cropped prior to submitting it?', 'ws-form'),
					'default'					=>	'on'
				),

				// Input Type - Date/Time
				'input_type_datetime'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'mask'						=>	'type="#datetime_type" data-date-type="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Type of date to display.', 'ws-form'),
					'data_change'				=>	array('event' => 'change', 'action' => 'reload'),

					'options'					=>	array(

						array('value' => 'date', 'text' => __('Date', 'ws-form')),
						array('value' => 'time', 'text' => __('Time', 'ws-form')),
						array('value' => 'datetime-local', 'text' => __('Date/Time', 'ws-form')),
						array('value' => 'week', 'text' => __('Week', 'ws-form')),
						array('value' => 'month', 'text' => __('Month', 'ws-form')),
					),
					'default'					=>	'date',
					'compatibility_id'			=> 'input-datetime'
				),

				// Date format
				'format_date' => array(

					'label'						=>	__('Date Format', 'ws-form'),
					'mask'						=>	'data-date-format="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __(sprintf('Default (%s)', date_i18n(get_option('date_format'))), 'ws-form'))
					),
					'default'					=>	'',
					'help'						=>	__('Format used for selected date.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'date'
						),

						array(

							'logic_previous'	=>	'||',
							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'datetime-local'
						)
					)
				),

				// Time format
				'format_time' => array(

					'label'						=>	__('Time Format', 'ws-form'),
					'mask'						=>	'data-time-format="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => '', 'text' => __(sprintf('Default (%s)', date_i18n(get_option('time_format'))), 'ws-form'))
					),
					'default'					=>	'',
					'help'						=>	__('Format used for selected time.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'time'
						),

						array(

							'logic_previous'	=>	'||',
							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'datetime-local'
						)
					)
				),

				// Input Type - Text Area
				'input_type_textarea'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'mask'						=>	'data-textarea-type="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Type of text editor to display.', 'ws-form'),
					'data_change'				=>	array('event' => 'change', 'action' => 'reload'),

					'options'					=>	array(

						array('value' => '', 'text' => __('Text Area', 'ws-form'))
					),
					'default'					=> '',
					'help'						=> __('If a user has visual editor or syntax highlighting disabled, those editors will not render.', 'ws-form')
				),

				// Input Type - Text Area
				'input_type_textarea_toolbar'		=> array(

					'label'						=>	__('Visual Editor Toolbar', 'ws-form'),
					'mask'						=>	'data-textarea-toolbar="#value"',
					'type'						=>	'select',
					'help'						=>	__('Type of text editor to display.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'full', 'text' => __('Full', 'ws-form')),
						array('value' => 'compact', 'text' => __('Compact', 'ws-form'))
					),
					'default'					=> '',
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'input_type_textarea',
							'meta_value'	=>	'tinymce'
						)
					),
				),

				// Progress Data Source
				'progress_source'		=> array(

					'label'						=>	__('Source', 'ws-form'),
					'mask'						=>	'data-source="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Source of progress data.', 'ws-form'),

					'options'					=>	array(

						array('value' => '', 'text' => __('No source', 'ws-form')),
						array('value' => 'form_progress', 'text' => __('Form Progress', 'ws-form')),
						array('value' => 'tab_progress', 'text' => __('Tab Progress', 'ws-form')),
						array('value' => 'post_progress', 'text' => __('Upload Progress', 'ws-form')),
					),
					'default'					=>	'form_progress'
				),

				'class_field_full_button_remove'	=> array(

					'label'						=>	__('Remove Full Width Class', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	''
				),

				'class_field_message_type'			=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'information',
					'options'					=>	$message_types,
					'help'						=>	__('Style of message to use', 'ws-form')
				),

				'class_field_button_type'			=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'default',
					'options'					=>	$button_types,
					'fallback'					=>	'default'
				),

				'class_field_button_type_primary'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'primary',
					'options'					=>	$button_types,
					'key'						=>	'class_field_button_type',
					'fallback'					=>	'primary'
				),

				'class_field_button_type_danger'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'danger',
					'options'					=>	$button_types,
					'key'						=>	'class_field_button_type',
					'fallback'					=>	'danger'
				),

				'class_field_button_type_success'		=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'success',
					'options'					=>	$button_types,
					'key'						=>	'class_field_button_type',
					'fallback'					=>	'success'
				),

				'class_fill_lower_track'			=> array(

					'label'						=>	__('Fill Lower Track', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'mask'						=>	'data-fill-lower-track',
					'mask_disregard_on_empty'	=>	true,
					'help'						=>	__('WS Form skin only.', 'ws-form'),
				),

				'class_single_vertical_align'			=> array(

					'label'						=>	__('Vertical Alignment', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	$vertical_align
				),

				'class_single_vertical_align_bottom'	=> array(

					'label'						=>	__('Vertical Alignment', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'bottom',
					'options'					=>	$vertical_align,
					'key'						=>	'class_single_vertical_align',
					'fallback'					=>	''
				),

				// Sets default value attribute (unless saved value exists)
				'default_value'			=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Default value entered in field.', 'ws-form'),
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_number'	=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'number',
					'type_advanced'				=>	'text',
					'default'					=>	'',
					'help'						=>	__('Default number entered in field.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_range' => array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'range',
					'type_advanced'				=>	'text',
					'default'					=>	'',
					'help'						=>	__('Default value of range slider.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_price_range' => array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'range',
					'type_advanced'				=>	'text',
					'default'					=>	'0',
					'help'						=>	__('Default value of price range slider.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_color' => array(

					'label'						=>	__('Default Color', 'ws-form'),
					'type'						=>	'color',
					'type_advanced'				=>	'text',
					'default'					=>	'#000000',
					'help'						=>	__('Default color selected in field.', 'ws-form'),
					'key'						=>	'default_value',
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_datetime' => array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'datetime',
					'type_advanced'				=>	'text',
					'default'					=>	'',
					'help'						=>	__('Default date entered in field.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_email'		=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'email',
					'default'					=>	'',
					'help'						=>	__('Default email entered in field.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_tel'		=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'tel',
					'default'					=>	'',
					'help'						=>	__('Default phone number entered in field.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_url'		=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'url',
					'default'					=>	'',
					'help'						=>	__('Default URL entered in field.', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Sets default value attribute (unless saved value exists)
				'default_value_textarea'		=> array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'textarea',
					'default'					=>	'',
					'help'						=>	__('Default value entered in field', 'ws-form'),
					'key'						=>	'default_value',
					'select_list'				=>	true,
					'calc'						=>	true
				),

				// Orientation
				'orientation'			=> array(

					'label'						=>	__('Orientation', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	array(

						array('value' => '', 'text' => __('Vertical', 'ws-form')),
						array('value' => 'horizontal', 'text' => __('Horizontal', 'ws-form')),
						array('value' => 'grid', 'text' => __('Grid', 'ws-form'))
					),
					'key_legacy'				=>	'class_inline'
				),

				// Orientation sizes grid
				'orientation_breakpoint_sizes'		=> array(

					'label'						=>	__('Grid Breakpoint Sizes', 'ws-form'),
					'type'						=>	'orientation_breakpoint_sizes',
					'dummy'						=>	true,
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'orientation',
							'meta_value'	=>	'grid'
						)
					)
				),

				// Form label mask (Allows user to define custom mask)
				'label_mask_form'		=> array(

					'label'						=>	__('Custom Form Heading HTML', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Example: &apos;&lt;h2&gt;#label&lt;/h2&gt;&apos;.', 'ws-form'),
					'placeholder'				=>	'&lt;h2&gt;#label&lt;/h2&gt'
				),

				// Group label mask (Allows user to define custom mask)
				'label_mask_group'		=> array(

					'label'						=>	__('Custom Tab Heading HTML', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Example: &apos;&lt;h3&gt;#label&lt;/h3&gt;&apos;.', 'ws-form'),
					'placeholder'				=>	'&lt;h3&gt;#label&lt;/h3&gt'
				),

				// Section label mask (Allows user to define custom mask)
				'label_mask_section'		=> array(

					'label'						=>	__('Custom Section Legend HTML', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Example: &apos;&lt;legend&gt;#label&lt;/legend&gt;&apos;.', 'ws-form'),
					'placeholder'				=>	'&lt;legend&gt;#label&lt;/legend&gt;'
				),

				// Wrapper classes
				'class_form_wrapper'		=> array(

					'label'						=>	__('Wrapper CSS Classes', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Separate multiple classes by a space.', 'ws-form')
				),

				'class_group_wrapper'		=> array(

					'label'						=>	__('Wrapper CSS Classes', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Separate multiple classes by a space.', 'ws-form')
				),

				'class_section_wrapper'		=> array(

					'label'						=>	__('Wrapper CSS Classes', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Separate multiple classes by a space.', 'ws-form')
				),

				'class_field_wrapper'		=> array(

					'label'						=>	__('Wrapper CSS Classes', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Separate multiple classes by a space.', 'ws-form')
				),

				// Classes
				'class_field'			=> array(

					'label'						=>	__('Field CSS Classes', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Separate multiple classes by a space.', 'ws-form')
				),

				'contact_first_name'	=> array(

					'label'						=>	__('First Name', 'ws-form'),
					'type'						=>	'text',
					'required'					=>	true
				),

				'contact_last_name'	=> array(

					'label'						=>	__('Last Name', 'ws-form'),
					'type'						=>	'text',
					'required'					=>	true
				),

				'contact_email'	=> array(

					'label'						=>	__('Email', 'ws-form'),
					'type'						=>	'email',
					'required'					=>	true
				),

				'contact_push_form'	=> array(

					'label'						=>	__('Attach form (Recommended)', 'ws-form'),
					'type'						=>	'checkbox'
				),

				'contact_push_system'	=> array(

					'label'						=>	sprintf('<a href="%s" target="_blank">%s</a> (%s).', WS_Form_Common::get_admin_url('ws-form-settings', false, 'tab=system'), __('Attach system info', 'ws-form'), __('Recommended', 'ws-form')),
					'type'						=>	'checkbox'
				),

				'contact_inquiry'	=> array(

					'label'						=>	__('Inquiry', 'ws-form'),
					'type'						=>	'textarea',
					'required'					=>	true
				),

				'contact_gdpr'	=> array(

					'label'						=>	__('I consent to having WS Form store my submitted information so they can respond to my inquiry.', 'ws-form'),
					'type'						=>	'checkbox',
					'required'					=>	true
				),

				'contact_submit'	=> array(

					'label'						=>	__('Request Support', 'ws-form'),
					'type'						=>	'button',
					'data-action'				=>	'wsf-contact-us'
				),

				'help'						=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field.', 'ws-form'),
					'select_list'				=>	true
				),

				'help_progress'				=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. You can use #progress_percent to inject the current progress percentage.', 'ws-form'),
					'default'					=>	'#progress_percent',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'help_range'				=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. You can use #value to inject the current range value.', 'ws-form'),
					'default'					=>	'#value',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'help_price_range'				=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. You can use #value to inject the current range value.', 'ws-form'),
					'default'					=>	'#ecommerce_currency_symbol#value',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'help_count_char'	=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. Use #character_count to inject the current character count.', 'ws-form'),
					'default'					=>	'',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'help_count_char_word'	=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. Use #character_count or #word_count to inject the current character or word count.', 'ws-form'),
					'default'					=>	'',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'help_count_char_word_with_default'	=> array(

					'label'						=>	__('Help Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Help text to show alongside this field. Use #character_count or #word_count to inject the current character or word count.', 'ws-form'),
					'default'					=>	'#character_count #character_count_label / #word_count #word_count_label',
					'key'						=>	'help',
					'select_list'				=>	true
				),

				'html_editor'				=> array(

					'label'						=>	__('HTML', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'html_editor',
					'default'					=>	'',
					'help'						=>	__('Enter raw HTML to be output at this point on the form.', 'ws-form'),
					'select_list'				=>	true,
					'calc'						=>	true
				),

				'shortcode'					=> array(

					'label'						=>	__('Shortcode', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Enter the shortcode to insert.', 'ws-form'),
					'select_list'				=>	true
				),

				'invalid_feedback'			=> array(

					'label'						=>	__('Invalid Feedback Text', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Text to show if this field is incorrectly completed.', 'ws-form'),
					'mask_placeholder'			=>	__('Please provide a valid #label_lowercase.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'			=>	'==',
							'meta_key'		=>	'invalid_feedback_render',
							'meta_value'	=>	'on'
						)
					),
					'variables'					=> true
				),

				'invalid_feedback_render'	=> array(

					'label'						=>	__('Render Invalid Feedback', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Show invalid feedback text?', 'ws-form'),
					'default'					=>	'on'
				),

				'text_editor'			=> array(

					'label'						=>	__('Content', 'ws-form'),
					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text_editor',
					'default'					=>	'',
					'help'						=>	__('Enter paragraphs of text.', 'ws-form'),
					'select_list'				=>	true,
					'calc'						=>	true
				),

				'required_message'		=> array(

					'label'						=>	__('Required Message', 'ws-form'),
					'type'						=>	'required_message',
					'help'						=>	__('Enter a custom message to show if this field is not completed.', 'ws-form'),
					'select_list'				=>	true
				),

				// Class for the wrapper
				'accept'		=> array(

					'label'						=>	__('File Type(s)', 'ws-form'),
					'mask'						=>	'accept="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	'',
					'help'						=>	__('Specify the mime types of files that are accepted separate by commas.', 'ws-form'),
					'placeholder'				=>	__('e.g. application/pdf,image/jpeg', 'ws-form'),
					'compatibility_id'			=>	'input-file-accept',
					'select_lust'				=>	array()
				),

				// Field - HTML 5 attributes

				'cols'						=> array(

					'label'						=>	__('Columns', 'ws-form'),
					'mask'						=>	'cols="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	true,
					'type'						=>	'number',
					'help'						=>	__('Number of columns.', 'ws-form')
				),

				'disabled'				=> array(

					'label'						=>	__('Disabled', 'ws-form'),
					'mask'						=>	'disabled',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'required',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'readonly',
							'meta_value'		=>	'on',
							'logic_previous'	=>	'&&'
						)
					),
				),

				'section_repeatable'			=> array(

					'label'						=>	__('Enabled', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'fields_toggle'				=>	array(

						array(

							'type'				=> 'section_icons',
							'width_factor'		=> 0.25
						)
					),
					'fields_ignore'				=>	array(

						'section_add',
						'section_delete',
						'section_icons'
					)
				),

				'section_repeat_label'			=> array(

					'label'						=>	__('Repeat Label', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'label_render',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					),
				),

				'section_repeat_min'			=> array(

					'label'						=>	__('Minimum Row Count', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	1,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					)
				),

				'section_repeat_max'			=> array(

					'label'						=>	__('Maximum Row Count', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	1,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					)
				),

				'section_repeat_default'		=> array(

					'label'						=>	__('Default Row Count', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	1,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					)
				),

				// Section icons - Style
				'section_icons_style'		=> array(

					'label'						=>	__('Icon', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'circle',
					'help'						=>	__('Select the style of the icons.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'circle', 'text' => __('Circle', 'ws-form')),
						array('value' => 'circle-solid', 'text' => __('Circle - Solid', 'ws-form')),
						array('value' => 'square', 'text' => __('Square', 'ws-form')),
						array('value' => 'square-solid', 'text' => __('Square - Solid', 'ws-form')),
						array('value' => 'text', 'text' => __('Text', 'ws-form')),
						array('value' => 'custom', 'text' => __('Custom HTML', 'ws-form'))
					)
				),

				// Section icons
				'section_icons'	=> array(

					'label'						=>	__('Icons', 'ws-form'),
					'type'						=>	'repeater',
					'help'						=>	__('Select the icons to show.', 'ws-form'),
					'meta_keys'					=>	array(

						'section_icons_type',
						'section_icons_label'
					),
					'default'					=>	array(

						array(

							'section_icons_type' => 'add',
							'section_icons_label' => __('Add row', 'ws-form')
						),

						array(

							'section_icons_type' => 'delete',
							'section_icons_label' => __('Remove row', 'ws-form')
						),

						array(

							'section_icons_type' => 'move-up',
							'section_icons_label' => __('Move row up', 'ws-form')
						),

						array(

							'section_icons_type' => 'move-down',
							'section_icons_label' => __('Move row down', 'ws-form')
						),

						array(

							'section_icons_type' => 'drag',
							'section_icons_label' => __('Drag row up or down', 'ws-form')
						)
					)
				),

				// Section icons - Types
				'section_icons_type'	=> array(

					'label'						=>	__('Type', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'help'						=>	__('Select the style of the add icon.', 'ws-form'),
					'options'					=>	array(

						array('value' => 'add', 'text' => __('Add', 'ws-form')),
						array('value' => 'delete', 'text' => __('Remove', 'ws-form')),
						array('value' => 'move-up', 'text' => __('Move Up', 'ws-form')),
						array('value' => 'move-down', 'text' => __('Move Down', 'ws-form')),
						array('value' => 'drag', 'text' => __('Drag', 'ws-form'))
					),
					'options_blank'					=>	__('Select...', 'ws-form'),
				),

				// Section icons - Label
				'section_icons_label'	=> array(

					'label'						=>	__('ARIA Label', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	''
				),

				// Section icons - Size
				'section_icons_size'	=> array(

					'label'						=>	__('Size (Pixels)', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	24,
					'min'						=>	1,
					'help'						=>	__('Size of section icons in pixels.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'text'
						)
					)
				),

				// Section icons - Color - Off
				'section_icons_color_off'	=> array(

					'label'						=>	__('Disabled Color', 'ws-form'),
					'mask'						=>	'data-rating-color-off="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'default'					=>	'#888888',
					'help'						=>	__('Color of section icons when disabled.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - Color - On
				'section_icons_color_on'	=> array(

					'label'						=>	__('Active Color', 'ws-form'),
					'mask'						=>	'data-rating-color-on="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'default'					=>	'#000000',
					'help'						=>	__('Color of section icons when active.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - HTML - Add
				'section_icons_html_add'	=> array(

					'label'						=>	__('Add Icon HTML', 'ws-form'),
					'type'						=>	'html_editor',
					'default'					=>	'<span title="Add">Add</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - HTML - Delete
				'section_icons_html_delete'	=> array(

					'label'						=>	__('Remove Icon HTML', 'ws-form'),
					'type'						=>	'html_editor',
					'default'					=>	'<span>Remove</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - HTML - Move Up
				'section_icons_html_move_up'	=> array(

					'label'						=>	__('Move Up Icon HTML', 'ws-form'),
					'type'						=>	'html_editor',
					'default'					=>	'<span>Move Up</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - HTML - Move Down
				'section_icons_html_move_down'	=> array(

					'label'						=>	__('Move Down Icon HTML', 'ws-form'),
					'type'						=>	'html_editor',
					'default'					=>	'<span>Move Down</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Section icons - HTML - Drag
				'section_icons_html_drag'	=> array(

					'label'						=>	__('Drag Icon HTML', 'ws-form'),
					'type'						=>	'html_editor',
					'default'					=>	'<span>Drag</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_icons_style',
							'meta_value'		=>	'custom'
						)
					)
				),

				'section_repeatable_section_id'	=> array(

					'label'						=>	__('Repeatable Section', 'ws-form'),
					'mask'						=>	'data-repeatable-section-id="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'options'					=>	'sections',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'section_filter_attribute'	=>	'section_repeatable',
					'help'						=>	__('Select the repeatabled section this field is assigned to.', 'ws-form'),
					'required_setting'			=>	true,
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'default'					=>	'#section_id'
				),

				// Horizontal Align
				'horizontal_align'	=> array(

					'label'						=>	__('Horizontal Alignment', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'flex-start',
					'options'					=>	array(

						array('value' => 'flex-start', 'text' => __('Left', 'ws-form')),
						array('value' => 'center', 'text' => __('Center', 'ws-form')),
						array('value' => 'flex-end', 'text' => __('Right', 'ws-form')),
						array('value' => 'space-around', 'text' => __('Space Around', 'ws-form')),
						array('value' => 'space-between', 'text' => __('Space Between', 'ws-form')),
						array('value' => 'space-evenly', 'text' => __('Space Evenly', 'ws-form'))
					)
				),

				'section_repeatable_delimiter_section'		=> array(

					'label'						=>	__('Row Delimiter', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('String used to delimit rows in combined field values.', 'ws-form'),
					'default'					=>	WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					),
					'placeholder'				=>	WS_FORM_SECTION_REPEATABLE_DELIMITER_SECTION
				),

				'section_repeatable_delimiter_row'			=> array(

					'label'						=>	__('Item Delimiter', 'ws-form'),
					'type'						=>	'text',
					'help'						=>	__('String used to delimit items (e.g. Checkboxes) in combined field values.', 'ws-form'),
					'default'					=>	WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'section_repeatable',
							'meta_value'		=>	'on'
						)
					),
					'placeholder'				=>	WS_FORM_SECTION_REPEATABLE_DELIMITER_ROW

				),
				'disabled_section'				=> array(

					'label'						=>	__('Disabled', 'ws-form'),
					'mask'						=>	'disabled',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'compatibility_id'			=>	'fieldset-disabled'
				),

				'text_align'	=> array(

					'label'						=>	__('Text Align', 'ws-form'),
					'mask'						=>	'style="text-align: #value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Select the alignment of text in the field.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('Not Set', 'ws-form')),
						array('value' => 'left', 'text' => __('Left', 'ws-form')),
						array('value' => 'right', 'text' => __('Right', 'ws-form')),
						array('value' => 'center', 'text' => __('Center', 'ws-form')),
						array('value' => 'justify', 'text' => __('Justify', 'ws-form')),
						array('value' => 'inherit', 'text' => __('Inherit', 'ws-form')),
					),
					'default'					=>	'',
					'key'						=>	'text_align'
				),

				'text_align_right'	=> array(

					'label'						=>	__('Text Align', 'ws-form'),
					'mask'						=>	'style="text-align: #value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Select the alignment of text in the field.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('Not Set', 'ws-form')),
						array('value' => 'left', 'text' => __('Left', 'ws-form')),
						array('value' => 'right', 'text' => __('Right', 'ws-form')),
						array('value' => 'center', 'text' => __('Center', 'ws-form')),
						array('value' => 'justify', 'text' => __('Justify', 'ws-form')),
						array('value' => 'inherit', 'text' => __('Inherit', 'ws-form')),
					),
					'default'					=>	'right',
					'key'						=>	'text_align'
				),

				'text_align_center'	=> array(

					'label'						=>	__('Text Align', 'ws-form'),
					'mask'						=>	'style="text-align: #value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Select the alignment of text in the field.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('Not Set', 'ws-form')),
						array('value' => 'left', 'text' => __('Left', 'ws-form')),
						array('value' => 'right', 'text' => __('Right', 'ws-form')),
						array('value' => 'center', 'text' => __('Center', 'ws-form')),
						array('value' => 'justify', 'text' => __('Justify', 'ws-form')),
						array('value' => 'inherit', 'text' => __('Inherit', 'ws-form')),
					),
					'default'					=>	'center',
					'key'						=>	'text_align'
				),

				'autocomplete_new_password'	=> array(

					'label'						=>	__('Auto Complete Off', 'ws-form'),
					'mask'						=>	'autocomplete="new-password"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('Adds autocomplete="new-password" attribute.', 'ws-form')
				),

				'password_strength_meter' => array(

					'label'						=>	__('Password Strength Meter', 'ws-form'),
					'type'						=>	'checkbox',
					'mask'						=>	'data-password-strength-meter',
					'mask_disregard_on_empty'	=>	true,
					'help'						=>	__('Show the WordPress password strength meter?', 'ws-form'),
					'default'					=>	'on',
				),

				'ecommerce_price_negative'	=> array(

					'label'						=>	__('Allow Negative Value', 'ws-form'),
					'mask'						=>	'data-ecommerce-negative',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	''
				),

				'ecommerce_price_min'		=> array(

					'label'						=>	__('Minimum', 'ws-form'),
					'mask'						=>	'data-ecommerce-min="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'type'						=>	'text',
					'help'						=>	__('Minimum value this field can have.', 'ws-form'),
					'select_list'				=>	true
				),

				'ecommerce_price_max'		=> array(

					'label'						=>	__('Maximum', 'ws-form'),
					'mask'						=>	'data-ecommerce-max="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'type'						=>	'text',
					'help'						=>	__('Maximum value this field can have.', 'ws-form'),
					'select_list'				=>	true
				),

				'ecommerce_quantity_min'	=> array(

					'label'						=>	__('Minimum', 'ws-form'),
					'mask'						=>	'min="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'default'					=>	0,
					'type'						=>	'text',
					'help'						=>	__('Minimum value this field can have.', 'ws-form')
				),

				'ecommerce_field_id'	=> array(

					'label'						=>	__('Related Price Field', 'ws-form'),
					'mask'						=>	'data-ecommerce-field-id="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_attribute'	=>	array('ecommerce_price'),
					'help'						=>	__('Price field that this field relates to.', 'ws-form'),
					'required_setting'			=>	true,
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),

				'ecommerce_quantity_default_value' => array(

					'label'						=>	__('Default Value', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'1',
					'help'						=>	__('Default quantity value.', 'ws-form'),
					'select_list'				=>	true,
					'key'						=>	'default_value'
				),

				// Price type
				'ecommerce_cart_price_type'	=> array(

					'label'						=>	__('Type', 'ws-form'),
					'mask'						=>	'data-ecommerce-cart-price-#value',
					'type'						=>	'select',
					'help'						=>	__('Select the type of cart detail.', 'ws-form'),
					'options'					=>	'ecommerce_cart_price_type',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'required_setting'			=>	true,
					'data_change'				=>	array('event' => 'change', 'action' => 'update')
				),
				'max_length'			=> array(

					'label'						=>	__('Maximum Characters', 'ws-form'),
					'mask'						=>	'maxlength="#value"',
					'mask_disregard_on_empty'	=>	true,
					'min'						=>	0,
					'type'						=>	'number',
					'default'					=>	'',
					'help'						=>	__('Maximum length for this field in characters.', 'ws-form'),
					'compatibility_id'			=>	'maxlength'
				),

				'min_length'			=> array(

					'label'						=>	__('Minimum Characters', 'ws-form'),
					'mask'						=>	'minlength="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'number',
					'min'						=>	0,
					'default'					=>	'',
					'help'						=>	__('Minimum length for this field in characters.', 'ws-form'),
					'compatibility_id'			=>	'input-minlength'
				),

				'max_length_words'			=> array(

					'label'						=>	__('Maximum Words', 'ws-form'),
					'type'						=>	'number',
					'min'						=>	0,
					'default'					=>	'',
					'help'						=>	__('Maximum words allowed in this field.', 'ws-form')
				),

				'min_length_words'			=> array(

					'label'						=>	__('Minimum Words', 'ws-form'),
					'min'						=>	0,
					'type'						=>	'number',
					'default'					=>	'',
					'help'						=>	__('Minimum words allowed in this field.', 'ws-form')
				),

				'min'						=> array(

					'label'						=>	__('Minimum', 'ws-form'),
					'mask'						=>	'min="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'type'						=>	'number',
					'help'						=>	__('Minimum value this field can have.', 'ws-form')
				),

				'max'						=> array(

					'label'						=>	__('Maximum', 'ws-form'),
					'mask'						=>	'max="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'type'						=>	'number',
					'help'						=>	__('Maximum value this field can have.', 'ws-form')
				),

				'min_date'						=> array(

					'label'						=>	__('Minimum', 'ws-form'),
					'mask'						=>	'min="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'datetime',
					'help'						=>	__('Minimum date/time that can be chosen.', 'ws-form')
				),

				'max_date'						=> array(

					'label'						=>	__('Maximum', 'ws-form'),
					'mask'						=>	'max="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'datetime',
					'help'						=>	__('Maximum date/time that can be chosen.', 'ws-form')
				),

				'year_start'						=> array(

					'label'						=>	__('Start Year', 'ws-form'),
					'mask'						=>	'data-year-start="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'number',
					'help'						=>	__('Defaults to 1950', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'date'
						),

						array(

							'logic_previous'	=>	'||',
							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'datetime-local'
						)
					)
				),

				'year_end'						=> array(

					'label'						=>	__('End Year', 'ws-form'),
					'mask'						=>	'data-year-end="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'number',
					'help'						=>	__('Defaults to 2050', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'date'
						),

						array(

							'logic_previous'	=>	'||',
							'logic'				=>	'==',
							'meta_key'			=>	'input_type_datetime',
							'meta_value'		=>	'datetime-local'
						)
					)
				),

				'multiple'						=> array(

					'label'						=>	__('Multiple', 'ws-form'),
					'mask'						=>	'multiple',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'help'						=>	__('Can multiple options can be selected at once?', 'ws-form'),
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'select_cascade',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'price_select_cascade',
							'meta_value'		=>	'on',
							'logic_previous'	=>	'||'
						)
					)
				),

				'multiple_email'		=> array(

					'label'						=>	__('Multiple', 'ws-form'),
					'mask'						=>	'multiple',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Can multiple email addresses be entered?', 'ws-form'),
				),

				'multiple_file'		=> array(

					'label'						=>	__('Multiple', 'ws-form'),
					'mask'						=>	'multiple',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Allow users to select multiple files in the file picker.', 'ws-form'),
					'compatibility_id'			=>	'input-file-multiple',
				),

				'file_image_max_width'	=> array(

					'label'						=>	__('Max Width (Pixels)', 'ws-form'),
					'type'						=>	'number',
					'min'						=>	1,
					'help'						=>	__('Enter the maximum width in pixels the saved file should be. Leave blank for no change.', 'ws-form')
				),

				'file_image_max_height'	=> array(

					'label'						=>	__('Max Height (Pixels)', 'ws-form'),
					'type'						=>	'number',
					'min'						=>	1,
					'help'						=>	__('Enter the maximum height in pixels the saved file should be. Leave blank for no change.', 'ws-form')
				),

				'file_image_crop'	=> array(

					'label'						=>	__('Crop', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('If checked, images will be cropped to the maximum dimensions above using center positions.', 'ws-form'),
					'default'					=>	''
				),

				'file_image_compression'	=> array(

					'label'						=>	__('Quality', 'ws-form'),
					'type'						=>	'number',
					'min'						=>	1,
					'max'						=>	100,
					'help'						=>	__('Sets image compression quality on a 1-100 scale. Leave blank for no change.', 'ws-form')
				),

				'file_image_mime'	=> array(

					'label'						=>	__('File Format', 'ws-form'),
					'type'						=>	'select',
					'help'						=>	__('Select the file format image uploads should be saved as.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 'text' => __('Same as original', 'ws-form')),
						array('value' => 'image/jpeg', 'text' => __('JPG', 'ws-form')),
						array('value' => 'image/png', 'text' => __('PNG', 'ws-form')),
						array('value' => 'image/gif', 'text' => __('GIF', 'ws-form'))
					)
				),

				'directory'		=> array(

					'label'						=>	__('Directory', 'ws-form'),
					'mask'						=>	'webkitdirectory mozdirectory',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Allow entire directory with file contents (and any subdirectories) to be selected.', 'ws-form'),
					'compatibility_id'			=>	'input-file-directory',
				),
				'input_mask'			=> array(

					'label'						=>	__('Input Mask', 'ws-form'),
					'mask'						=>	'data-inputmask="\'mask\': \'#value\'"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Input mask for the field, e.g. (999) 999-9999', 'ws-form'),
					'select_list'				=>	array(

						array('text' => __('US/Canadian Phone Number', 'ws-form'), 'value' => '(999) 999-9999'),
						array('text' => __('US/Canadian Phone Number (International)', 'ws-form'), 'value' => '+1 (999) 999-9999'),
						array('text' => __('US Zip Code', 'ws-form'), 'value' => '99999'),
						array('text' => __('US Zip Code +4', 'ws-form'), 'value' => '99999[-9999]'),
						array('text' => __('Canadian Post Code', 'ws-form'), 'value' => 'A9A-9A9'),
						array('text' => __('Short Date', 'ws-form'), 'value' => '99/99/9999'),
						array('text' => __('Social Security Number', 'ws-form'), 'value' => '999-99-9999')
					)
				),

				'pattern'			=> array(

					'label'						=>	__('Pattern', 'ws-form'),
					'mask'						=>	'pattern="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Regular expression value is checked against.', 'ws-form'),
					'select_list'				=>	array(

						array('text' => __('Alpha', 'ws-form'), 'value' => '^[a-zA-Z]+$'),
						array('text' => __('Alphanumeric', 'ws-form'), 'value' => '^[a-zA-Z0-9]+$'),
						array('text' => __('Color', 'ws-form'), 'value' => '^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$'),
						array('text' => __('Country Code (2 Character)', 'ws-form'), 'value' => '[A-Za-z]{2}'),
						array('text' => __('Country Code (3 Character)', 'ws-form'), 'value' => '[A-Za-z]{3}'),
						array('text' => __('Date (mm/dd)', 'ws-form'), 'value' => '(0[1-9]|1[012]).(0[1-9]|1[0-9]|2[0-9]|3[01])'),
						array('text' => __('Date (dd/mm)', 'ws-form'), 'value' => '(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012])'),
						array('text' => __('Date (mm.dd.yyyy)', 'ws-form'), 'value' => '(0[1-9]|1[012]).(0[1-9]|1[0-9]|2[0-9]|3[01]).[0-9]{4}'),
						array('text' => __('Date (dd.mm.yyyy)', 'ws-form'), 'value' => '(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}'),
						array('text' => __('Date (yyyy-mm-dd)', 'ws-form'), 'value' => '(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31))'),
						array('text' => __('Date (mm/dd/yyyy)', 'ws-form'), 'value' => '(?:(?:0[1-9]|1[0-2])[\/\\-. ]?(?:0[1-9]|[12][0-9])|(?:(?:0[13-9]|1[0-2])[\/\\-. ]?30)|(?:(?:0[13578]|1[02])[\/\\-. ]?31))[\/\\-. ]?(?:19|20)[0-9]{2}'),
						array('text' => __('Date (dd/mm/yyyy)', 'ws-form'), 'value' => '^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$'),
						array('text' => __('Email', 'ws-form'), 'value' => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$'),
						array('text' => __('IP (Version 4)', 'ws-form'), 'value' => '^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?).){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$'),
						array('text' => __('IP (Version 6)', 'ws-form'), 'value' => '((^|:)([0-9a-fA-F]{0,4})){1,8}$'),
						array('text' => __('ISBN', 'ws-form'), 'value' => '(?:(?=.{17}$)97[89][ -](?:[0-9]+[ -]){2}[0-9]+[ -][0-9]|97[89][0-9]{10}|(?=.{13}$)(?:[0-9]+[ -]){2}[0-9]+[ -][0-9Xx]|[0-9]{9}[0-9Xx])'),
						array('text' => __('Latitude or Longitude', 'ws-form'), 'value' => '-?\d{1,3}\.\d+'),
						array('text' => __('MD5 Hash', 'ws-form'), 'value' => '[0-9a-fA-F]{32}'),
						array('text' => __('Numeric', 'ws-form'), 'value' => '^[0-9]+$'),
						array('text' => __('Password (Numeric, lower, upper)', 'ws-form'), 'value' => '^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$'),
						array('text' => __('Password (Numeric, lower, upper, min 8)', 'ws-form'), 'value' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}'),
						array('text' => __('Phone - UK', 'ws-form'), 'value' => '^\s*\(?(020[7,8]{1}\)?[ ]?[1-9]{1}[0-9{2}[ ]?[0-9]{4})|(0[1-8]{1}[0-9]{3}\)?[ ]?[1-9]{1}[0-9]{2}[ ]?[0-9]{3})\s*$'),
						array('text' => __('Phone - US: 123-456-7890', 'ws-form'), 'value' => '\d{3}[\-]\d{3}[\-]\d{4}'),
						array('text' => __('Phone - US: (123)456-7890', 'ws-form'), 'value' => '\([0-9]{3}\)[0-9]{3}-[0-9]{4}'),
						array('text' => __('Phone - US: (123) 456-7890', 'ws-form'), 'value' => '\([0-9]{3}\) [0-9]{3}-[0-9]{4}'),
						array('text' => __('Phone - US: Flexible', 'ws-form'), 'value' => '(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}'),
						array('text' => __('Postal Code (UK)', 'ws-form'), 'value' => '[A-Za-z]{1,2}[0-9Rr][0-9A-Za-z]? [0-9][ABD-HJLNP-UW-Zabd-hjlnp-uw-z]{2}'),
						array('text' => __('Price (1.23)', 'ws-form'), 'value' => '\d+(\.\d{2})?'),
						array('text' => __('Slug', 'ws-form'), 'value' => '^[a-z0-9-]+$'),
						array('text' => __('Time (hh:mm:ss)', 'ws-form'), 'value' => '(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}'),
						array('text' => __('URL', 'ws-form'), 'value' => 'https?://.+'),
						array('text' => __('Zip Code', 'ws-form'), 'value' => '(\d{5}([\-]\d{4})?)')						
					),
					'compatibility_id'			=>	'input-pattern'
				),

				'pattern_tel'			=> array(

					'label'						=>	__('Pattern', 'ws-form'),
					'mask'						=>	'pattern="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Regular expression value is checked against.', 'ws-form'),
					'select_list'				=>	array(

						array('text' => __('Phone - UK', 'ws-form'), 'value' => '^\s*\(?(020[7,8]{1}\)?[ ]?[1-9]{1}[0-9{2}[ ]?[0-9]{4})|(0[1-8]{1}[0-9]{3}\)?[ ]?[1-9]{1}[0-9]{2}[ ]?[0-9]{3})\s*$'),
						array('text' => __('Phone - US: 123-456-7890', 'ws-form'), 'value' => '\d{3}[\-]\d{3}[\-]\d{4}'),
						array('text' => __('Phone - US: (123)456-7890', 'ws-form'), 'value' => '\([0-9]{3}\)[0-9]{3}-[0-9]{4}'),
						array('text' => __('Phone - US: (123) 456-7890', 'ws-form'), 'value' => '\([0-9]{3}\) [0-9]{3}-[0-9]{4}'),
						array('text' => __('Phone - US: Flexible', 'ws-form'), 'value' => '(?:\(\d{3}\)|\d{3})[- ]?\d{3}[- ]?\d{4}')						
					),
					'compatibility_id'			=>	'input-pattern'
				),

				'pattern_date'			=> array(

					'label'						=>	__('Pattern', 'ws-form'),
					'mask'						=>	'pattern="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Regular expression value is checked against.', 'ws-form'),
					'select_list'				=>	array(

						array('text' => __('mm.dd.yyyy', 'ws-form'), 'value' => '(0[1-9]|1[012]).(0[1-9]|1[0-9]|2[0-9]|3[01]).[0-9]{4}'),
						array('text' => __('dd.mm.yyyy', 'ws-form'), 'value' => '(0[1-9]|1[0-9]|2[0-9]|3[01]).(0[1-9]|1[012]).[0-9]{4}'),
						array('text' => __('mm/dd/yyyy', 'ws-form'), 'value' => '(0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])[- /.](19|20)\d\d'),
						array('text' => __('dd/mm/yyyy', 'ws-form'), 'value' => '(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d'),
						array('text' => __('yyyy-mm-dd', 'ws-form'), 'value' => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])'),
						array('text' => __('hh:mm:ss', 'ws-form'), 'value' => '(0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}'),
						array('text' => __('yyyy-mm-ddThh:mm:ssZ', 'ws-form'), 'value' => '/([0-2][0-9]{3})\-([0-1][0-9])\-([0-3][0-9])T([0-5][0-9])\:([0-5][0-9])\:([0-5][0-9])(Z|([\-\+]([0-1][0-9])\:00))/')						
					),
					'compatibility_id'			=>	'input-pattern'
				),

				'placeholder'			=> array(

					'label'						=>	__('Placeholder', 'ws-form'),
					'mask'						=>	'placeholder="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'help'						=>	__('Short hint that describes the expected value of the input field.', 'ws-form'),
					'compatibility_id'			=>	'input-placeholder',
					'select_list'				=>	true,
					'calc_type'					=>	'field_placeholder'
				),

				'placeholder_row'			=> array(

					'label'						=>	__('First Row Placeholder (Blank for none)', 'ws-form'),
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'text',
					'default'					=>	__('Select...', 'ws-form'),
					'help'						=>	__('First value in the select pulldown.', 'ws-form')
				),

				'readonly'				=> array(

					'label'						=>	__('Read Only', 'ws-form'),
					'mask'						=>	'readonly',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'default'					=>	'',
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'required',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'disabled',
							'meta_value'		=>	'on',
							'logic_previous'	=>	'&&'
						)
					),
					'compatibility_id'			=>	'readonly-attr'
				),

				'readonly_on'				=> array(

					'label'						=>	__('Read Only', 'ws-form'),
					'mask'						=>	'readonly',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'required',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'disabled',
							'meta_value'		=>	'on',
							'logic_previous'	=>	'&&'
						)
					),
					'compatibility_id'			=>	'readonly-attr',
					'key'						=>	'readonly'
				),

				'scroll_to_top'				=> array(

					'label'						=>	__('Scroll To Top', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	array(

						array('value' => '', 'text' => __('None', 'ws-form')),
						array('value' => 'instant', 'text' => __('Instant', 'ws-form')),
						array('value' => 'smooth', 'text' => __('Smooth', 'ws-form'))
					)
				),

				'scroll_to_top_offset'		=> array(

					'label'						=>	__('Offset (Pixels)', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'0',
					'help'						=>	__('Number of pixels to offset the final scroll position by. Useful for sticky headers, e.g. if your header is 100 pixels tall, enter 100 into this setting.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'scroll_to_top',
							'meta_value'		=>	''
						)
					)
				),

				'scroll_to_top_duration'	=> array(

					'label'						=>	__('Duration (ms)', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'400',
					'help'						=>	__('Duration of the smooth scroll in ms.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'scroll_to_top',
							'meta_value'		=>	'smooth'
						)
					)
				),

				'required'				=> array(

					'label'						=>	__('Required', 'ws-form'),
					'mask'						=>	'required data-required aria-required="true"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'compatibility_id'			=>	'form-validation',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'disabled',
							'meta_value'		=>	'on'
						),

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'readonly',
							'meta_value'		=>	'on',
							'logic_previous'	=>	'&&'
						)
					)
				),

				'required_on'			=> array(

					'label'						=>	__('Required', 'ws-form'),
					'mask'						=>	'required data-required aria-required="true"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'compatibility_id'			=>	'form-validation',
					'key'						=>	'required',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'condition'					=>	array(

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'disabled',
							'meta_value'	=>	'on'
						),

						array(

							'logic'			=>	'!=',
							'meta_key'		=>	'readonly',
							'meta_value'	=>	'on',
							'logic_previous'	=>	'&&'
						)
					)
				),
				
				'required_attribute_no'	=> array(

					'label'						=>	__('Required', 'ws-form'),
					'mask'						=>	'',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'checkbox',
					'default'					=>	'',
					'compatibility_id'			=>	'form-validation',
					'data_change'				=>	array('event' => 'change', 'action' => 'update'),
					'key'						=>	'required'
				),

				'required_row'				=> array(

					'mask'						=>	'required data-required aria-required="true"',
					'mask_disregard_on_empty'	=>	true
				),

				'rows'						=> array(

					'label'						=>	__('Rows', 'ws-form'),
					'mask'						=>	'rows="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	true,
					'type'						=>	'number',
					'help'						=>	__('Number of rows.', 'ws-form')
				),

				'size'						=> array(

					'label'						=>	__('Size', 'ws-form'),
					'mask'						=>	'size="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	true,
					'type'						=>	'number',
					'attributes'				=>	array('min' => 0),
					'help'						=>	__('The number of visible options.', 'ws-form')
				),

				'select_all'				=> array(

					'label'						=>	__('Enable Select All', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Show a \'Select All\' option above the first row.', 'ws-form')
				),

				'select_all_label'			=> array(

					'label'						=>	__('Select All Label', 'ws-form'),
					'type'						=>	'text',
					'default'					=>	'',
					'placeholder'				=>	__('Select All', 'ws-form'),
					'help'						=>	__('Enter custom label for \'Select All\' row.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'select_all',
							'meta_value'		=>	'on'
						)
					),
				),

				'spellcheck'	=> array(

					'label'						=>	__('Spell Check', 'ws-form'),
					'mask'						=>	'spellcheck="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'select',
					'help'						=>	__('Spelling and grammar checking.', 'ws-form'),
					'options'					=>	array(

						array('value' => '', 		'text' => __('Browser default', 'ws-form')),
						array('value' => 'true', 	'text' => __('Enabled', 'ws-form')),
						array('value' => 'false', 	'text' => __('Disabled', 'ws-form'))
					),
					'compatibility_id'			=>	'spellcheck-attribute'
				),

				'step'						=> array(

					'label'						=>	__('Step', 'ws-form'),
					'mask'						=>	'step="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
					'type'						=>	'number',
					'help'						=>	__('Increment/decrement by this value.', 'ws-form')
				),

				// Fields - Sidebars
				'field_select'	=> array(

					'type'					=>	'field_select'
				),

				'form_history'	=> array(

					'type'					=>	'form_history'
				),

				'knowledgebase'	=> array(

					'type'					=>	'knowledgebase'
				),

				'contact'	=> array(

					'type'					=>	'contact'
				),

				'ws_form_field'					=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form')
				),

				'ws_form_field_choice'		=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('select', 'checkbox', 'radio'),
					'key'						=>	'ws_form_field'
				),

				'ws_form_field_file'		=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('signature', 'file'),
					'key'						=>	'ws_form_field'
				),

				'ws_form_field_save'		=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_attribute'	=>	array('submit_save'),
					'key'						=>	'ws_form_field'
				),

				'ws_form_field_edit'		=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_attribute'	=>	array('submit_edit'),
					'key'						=>	'ws_form_field'
				),

				'ws_form_field_ecommerce_price_cart'	=> array(

					'label'						=>	__('Form Field', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_attribute'	=>	array('ecommerce_cart_price')
				),

				// Fields - Data grids
				'conditional'	=>	array(

					'label'					=>	__('Conditions', 'ws-form'),
					'type'					=>	'data_grid',
					'type_sub'				=>	'conditional',	// Sub type
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'max_columns'			=>	1,		// Maximum number of columns
					'groups_label'			=>	false,	// Is the group label feature enabled?
					'groups_label_render'	=>	false,	// Is the group label render feature enabled?
					'groups_auto_group'		=>	false,	// Is auto group feature enabled?
					'groups_disabled'		=>	false,	// Is the group disabled attribute?
					'groups_group'			=>	false,	// Is the group mask supported?
					'field_wrapper'			=>	false,
					'upload_download'		=>	false,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Condition', 'ws-form')),
							array('id' => 1, 'label' => __('Data', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Conditions', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
								)
							)
						)
					)
				),

				'action'	=>	array(

					'label'					=>	__('Actions', 'ws-form'),
					'type'					=>	'data_grid',
					'type_sub'				=>	'action',	// Sub type
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'max_columns'			=>	1,		// Maximum number of columns
					'groups_label'			=>	false,	// Is the group label feature enabled?
					'groups_label_render'	=>	false,	// Is the group label render feature enabled?
					'groups_auto_group'		=>	false,	// Is auto group feature enabled?
					'groups_disabled'		=>	false,	// Is the group disabled attribute?
					'groups_group'			=>	false,	// Is the group mask supported?
					'field_wrapper'			=>	false,
					'upload_download'		=>	false,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Action', 'ws-form')),
							array('id' => 1, 'label' => __('Data', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Actions', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
								)
							)
						)
					)
				),

				'data_source_id' => array(

					'label'						=>	__('Data Source', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	'data_source',
					'class_wrapper'				=>	'wsf-field-wrapper-header'
				),

				'data_source_recurrence' => array(

					'label'						=>	__('Update Frequency', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'hourly',
					'options'					=>	array(),
					'help'						=>	__('This setting only applies to published forms. Previews show data in real-time.')
				),

				'data_source_get' => array(

					'label'						=>	__('Get Data', 'ws-form'),
					'type'						=>	'button'
				),

				'data_grid_datalist'	=>	array(

					'label'					=>	__('Datalist', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	false,	// Is the default attribute supported on rows?
					'row_disabled'			=>	false,	// Is the disabled attribute supported on rows?
					'row_required'			=>	false,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'groups_label'			=>	false,	// Is the group label feature enabled?
					'groups_label_render'	=>	false,	// Is the group label render feature enabled?
					'groups_auto_group'		=>	false,	// Is auto group feature enabled?
					'groups_disabled'		=>	false,	// Is the disabled attribute supported on groups?
					'groups_group'			=>	false,	// Can user add groups?
					'mask_group'			=>	false,	// Is the group mask supported?
					'field_wrapper'			=>	false,
					'upload_download'		=>	true,
					'compatibility_id'		=>	'datalist',

					'meta_key_value'		=>	'datalist_field_value',
					'meta_key_label'		=>	'datalist_field_text',
					'data_source'			=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Value', 'ws-form')),
							array('id' => 1, 'label' => __('Label', 'ws-form'))
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Values', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array()
							)
						)
					)
				),

				'datalist_field_value'	=> array(

					'label'						=>	__('Values', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_datalist',
					'default'					=>	0,
					'html_encode'				=>	true
				),

				'datalist_field_text'		=> array(

					'label'						=>	__('Labels', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_datalist',
					'default'					=>	1
				),

				'data_grid_select'	=>	array(

					'label'					=>	__('Options', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	false,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	false,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Optgroup', 'ws-form'),

					'field_wrapper'			=>	false,
					'meta_key_value'			=>	'select_field_value',
					'meta_key_label'			=>	'select_field_label',
					'meta_key_parse_variable'	=>	'select_field_parse_variable',
					'data_source'			=>	true,

					'upload_download'		=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Options', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Option 1', 'ws-form'))
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Option 2', 'ws-form'))
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'disabled'	=> '',
										'required'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Option 3', 'ws-form'))
									)
								)
							)
						)
					)
				),

				'select_field_value'			=> array(

					'label'						=>	__('Values', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select',
					'default'					=>	0,
					'html_encode'				=>	true
				),

				'select_field_label'			=> array(

					'label'						=>	__('Labels', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select',
					'default'					=>	0
				),

				'select_field_parse_variable'	=> array(

					'label'						=>	__('Action Variables', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select',
					'default'					=>	0,
					'help'						=>	__('Choose which column to use for variables in actions (e.g. #field or #email_submission in email or message actions).')
				),

				'select_min'	=> array(

					'label'						=>	__('Minimum Selected', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	0,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'multiple',
							'meta_value'		=>	'on'
						)
					)
				),

				'select_max'	=> array(

					'label'						=>	__('Maximum Selected', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	0,
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'multiple',
							'meta_value'		=>	'on'
						)
					)
				),

				'select_cascade'				=> array(

					'label'						=>	__('Cascade', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter this data grid using a value from another field.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'multiple',
							'meta_value'		=>	'on'
						)
					)
				),

				'select_cascade_field_id'		=> array(

					'label'						=>	__('Filter Value', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('select', 'price_select', 'radio', 'price_radio'),
					'help'						=>	__('Select the field to use as the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'select_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'select_cascade_field_filter'	=> array(

					'label'						=>	__('Filter Column', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select',
					'default'					=>	0,
					'help'						=>	__('Select the column to filter with the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'select_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'data_grid_checkbox'	=>	array(

					'label'					=>	__('Checkboxes', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	true,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'row_default_multiple'	=>	true,	// Can multiple defaults be selected?
					'row_required_multiple'	=>	true,	// Can multiple requires be selected?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	true,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Fieldset', 'ws-form'),

					'field_wrapper'				=>	false,
					'upload_download'			=>	true,
					'meta_key_value'			=>	'checkbox_field_value',
					'meta_key_label'			=>	'checkbox_field_label',
					'meta_key_parse_variable'	=>	'checkbox_field_parse_variable',
					'data_source'				=>	true,
					'insert_image'				=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form'))
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Checkboxes', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',
								'label_render'	=> 'on',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(

									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Checkbox 1', 'ws-form'))
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Checkbox 2', 'ws-form'))
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Checkbox 3', 'ws-form'))
									)
								)
							)
						)
					)
				),

				'checkbox_field_value'	=> array(

					'label'						=>	__('Values', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_checkbox',
					'default'					=>	0,
					'html_encode'				=>	true
				),

				'checkbox_field_label'		=> array(

					'label'						=>	__('Labels', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_checkbox',
					'default'					=>	0
				),

				'checkbox_field_parse_variable'			=> array(

					'label'						=>	__('Action Variables', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_checkbox',
					'default'					=>	0,
					'help'						=>	__('Choose which column to use for variables in actions (e.g. #field or #email_submission in email or message actions).', 'ws-form')
				),

				'checkbox_min'	=> array(

					'label'						=>	__('Minimum Checked', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	0
				),

				'checkbox_max'	=> array(

					'label'						=>	__('Maximum Checked', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	'',
					'min'						=>	0
				),

				'data_grid_radio'	=>	array(

					'label'					=>	__('Radios', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	false,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'row_default_multiple'	=>	false,	// Can multiple defaults be selected?
					'row_required_multiple'	=>	false,	// Can multiple requires be selected?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	true,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Fieldset', 'ws-form'),

					'field_wrapper'			=>	false,
					'upload_download'		=>	true,
					'meta_key_value'			=>	'radio_field_value',
					'meta_key_label'			=>	'radio_field_label',
					'meta_key_parse_variable'	=>	'radio_field_parse_variable',
					'data_source'			=>	true,
					'insert_image'				=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form'))
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Radios', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',
								'label_render'	=> 'on',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(

									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Radio 1', 'ws-form'))
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Radio 2', 'ws-form'))
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Radio 3', 'ws-form'))
									)
								)
							)
						)
					)
				),

				'radio_field_value'				=> array(

					'label'						=>	__('Values', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio',
					'default'					=>	0,
					'html_encode'				=>	true
				),

				'radio_field_label'				=> array(

					'label'						=>	__('Labels', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio',
					'default'					=>	0
				),

				'radio_field_parse_variable'	=> array(

					'label'						=>	__('Action Variables', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio',
					'default'					=>	0,
					'help'						=>	__('Choose which column to use for variables in actions (e.g. #field or #email_submission in email or message actions).', 'ws-form')
				),

				'radio_cascade'				=> array(

					'label'						=>	__('Cascade', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter this data grid using a value from another field.', 'ws-form')
				),

				'radio_cascade_field_id'		=> array(

					'label'						=>	__('Filter Value', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('select', 'price_select', 'radio', 'price_radio'),
					'help'						=>	__('Select the field to use as the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'radio_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'radio_cascade_field_filter'	=> array(

					'label'						=>	__('Filter Column', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio',
					'default'					=>	0,
					'help'						=>	__('Select the column to filter with the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'radio_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'data_grid_rows_randomize'	=> array(

					'label'						=>	__('Randomize Rows', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	''
				),

				'data_grid_select_price'	=>	array(

					'label'					=>	__('Options', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	false,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	false,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Optgroup', 'ws-form'),

					'field_wrapper'			=>	false,
					'upload_download'		=>	true,
					'meta_key_value'			=>	'select_price_field_value',
					'meta_key_label'			=>	'select_price_field_label',
					'meta_key_parse_variable'	=>	'select_price_field_parse_variable',
					'data_source'			=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form')),
							array('id' => 1, 'label' => __('Price', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Options', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 1', 'ws-form'), '1')
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 2', 'ws-form'), '2')
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'disabled'	=> '',
										'required'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 3', 'ws-form'), '3')
									)
								)
							)
						)
					)
				),

				'select_price_field_label'	=> array(

					'label'						=>	__('Label', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select_price',
					'default'					=>	0
				),

				'select_price_field_value'		=> array(

					'label'						=>	__('Price', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select_price',
					'default'					=>	1,
					'html_encode'				=>	true,
					'price'						=>	true
				),

				'price_select_cascade'				=> array(

					'label'						=>	__('Cascade', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter this data grid using a value from another field.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'multiple',
							'meta_value'		=>	'on'
						)
					)
				),

				'price_select_cascade_field_id'		=> array(

					'label'						=>	__('Filter Value', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('select', 'price_select', 'radio', 'price_radio'),
					'help'						=>	__('Select the field to use as the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'price_select_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'price_select_cascade_field_filter'	=> array(

					'label'						=>	__('Filter Column', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_select_price',
					'default'					=>	0,
					'help'						=>	__('Select the column to filter with the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'price_select_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'data_grid_checkbox_price'	=>	array(

					'label'					=>	__('Checkboxes', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	true,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'row_default_multiple'	=>	true,	// Can multiple defaults be selected?
					'row_required_multiple'	=>	true,	// Can multiple requires be selected?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	true,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Fieldset', 'ws-form'),

					'field_wrapper'				=>	false,
					'upload_download'			=>	true,
					'meta_key_value'			=>	'checkbox_price_field_value',
					'meta_key_label'			=>	'checkbox_price_field_label',
					'meta_key_parse_variable'	=>	'checkbox_price_field_parse_variable',
					'data_source'				=>	true,
					'insert_image'				=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form')),
							array('id' => 1, 'label' => __('Price', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Checkboxes', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',
								'label_render'	=> 'on',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 1', 'ws-form'), '1')
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 2', 'ws-form'), '2')
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'disabled'	=> '',
										'required'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 3', 'ws-form'), '3')
									)
								)
							)
						)
					)
				),

				'checkbox_price_field_label'		=> array(

					'label'						=>	__('Label', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_checkbox_price',
					'default'					=>	0
				),

				'checkbox_price_field_value'	=> array(

					'label'						=>	__('Price', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_checkbox_price',
					'default'					=>	1,
					'html_encode'				=>	true,
					'price'						=>	true
				),

				'data_grid_radio_price'	=>	array(

					'label'					=>	__('Radios', 'ws-form'),
					'type'					=>	'data_grid',
					'row_default'			=>	true,	// Is the default attribute supported on rows?
					'row_disabled'			=>	true,	// Is the disabled attribute supported on rows?
					'row_required'			=>	false,	// Is the required attribute supported on rows?
					'row_hidden'			=>	true,	// Is the hidden supported on rows?
					'row_default_multiple'	=>	false,	// Can multiple defaults be selected?
					'row_required_multiple'	=>	false,	// Can multiple requires be selected?
					'groups_label'			=>	true,	// Is the group label feature enabled?
					'groups_label_label'	=>	__('Label', 'ws-form'),
					'groups_label_render'	=>	true,	// Is the group label render feature enabled?
					'groups_label_render_label'	=>	__('Render Label', 'ws-form'),
					'groups_auto_group'		=>	true,	// Is auto group feature enabled?
					'groups_disabled'		=>	true,	// Is the group disabled attribute?
					'groups_group'			=>	true,	// Is the group mask supported?
					'groups_group_label'	=>	__('Wrap In Fieldset', 'ws-form'),

					'field_wrapper'				=>	false,
					'upload_download'			=>	true,
					'meta_key_value'			=>	'radio_price_field_value',
					'meta_key_label'			=>	'radio_price_field_label',
					'meta_key_parse_variable'	=>	'radio_price_field_parse_variable',
					'data_source'				=>	true,
					'insert_image'				=>	true,

					'default'			=>	array(

						// Config
						'rows_per_page'		=>	10,
						'group_index'		=>	0,
						'default'			=>	array(),

						// Columns
						'columns' => array(

							array('id' => 0, 'label' => __('Label', 'ws-form')),
							array('id' => 1, 'label' => __('Price', 'ws-form')),
						),

						// Group
						'groups' => array(

							array(

								'label' 		=> __('Radios', 'ws-form'),
								'page'			=> 0,
								'disabled'		=> '',
								'mask_group'	=> '',
								'label_render'	=> 'on',

								// Rows (Only injected for a new data grid, blank for new groups)
								'rows' 		=> array(
									array(

										'id'		=> 1,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 1', 'ws-form'), '1')
									),
									array(

										'id'		=> 2,
										'default'	=> '',
										'required'	=> '',
										'disabled'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 2', 'ws-form'), '2')
									),
									array(

										'id'		=> 3,
										'default'	=> '',
										'disabled'	=> '',
										'required'	=> '',
										'hidden'	=> '',
										'data'		=> array(__('Product 3', 'ws-form'), '3')
									)
								)
							)
						)
					)
				),

				'radio_price_field_label'		=> array(

					'label'						=>	__('Label', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio_price',
					'default'					=>	0
				),

				'radio_price_field_value'	=> array(

					'label'						=>	__('Price', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio_price',
					'default'					=>	1,
					'html_encode'				=>	true,
					'price'						=>	true
				),

				'price_radio_cascade'				=> array(

					'label'						=>	__('Cascade', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('Filter this data grid using a value from another field.', 'ws-form')
				),

				'price_radio_cascade_field_id'		=> array(

					'label'						=>	__('Filter Value', 'ws-form'),
					'type'						=>	'select',
					'default'					=>	'',
					'options'					=>	'fields',
					'options_blank'				=>	__('Select...', 'ws-form'),
					'fields_filter_type'		=>	array('select', 'price_select', 'radio', 'price_radio'),
					'help'						=>	__('Select the field to use as the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'price_radio_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				'price_radio_cascade_field_filter'	=> array(

					'label'						=>	__('Filter Column', 'ws-form'),
					'type'						=>	'data_grid_field',
					'data_grid'					=>	'data_grid_radio_price',
					'default'					=>	0,
					'help'						=>	__('Select the column to filter with the filter value.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'price_radio_cascade',
							'meta_value'		=>	'on'
						)
					)
				),

				// Email
				'exclude_email'	=> array(

					'label'						=>	__('Exclude from emails', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'',
					'help'						=>	__('If checked, this field will not appear in emails containing the #email_submission variable.', 'ws-form')
				),

				'exclude_email_on'	=> array(

					'label'						=>	__('Exclude from emails', 'ws-form'),
					'type'						=>	'checkbox',
					'default'					=>	'on',
					'help'						=>	__('If checked, this field will not appear in emails containing the #email_submission variable.', 'ws-form'),
					'key'						=>	'exclude_email'
				),

				// Custom attributes
				'custom_attributes'	=> array(

					'type'						=>	'repeater',
					'help'						=>	__('Add additional attributes to this field type.', 'ws-form'),
					'meta_keys'					=>	array(

						'custom_attribute_name',
						'custom_attribute_value'
					)
				),

				// Custom attributes - Name
				'custom_attribute_name'	=> array(

					'label'							=>	__('Name', 'ws-form'),
					'type'							=>	'text'
				),

				// Custom attributes - Value
				'custom_attribute_value'	=> array(

					'label'							=>	__('Value', 'ws-form'),
					'type'							=>	'text'
				),
				// Rating - Size
				'rating_max'	=> array(

					'label'						=>	__('Maximum Rating', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	5,
					'min'						=>	1
				),

				// Rating - Icon
				'rating_icon'	=> array(

					'label'						=>	__('Icon', 'ws-form'),
					'type'						=>	'select',
					'options'					=>	array(

						array('value' => 'check', 	'text' => __('Check', 'ws-form')),
						array('value' => 'circle', 	'text' => __('Circle', 'ws-form')),
						array('value' => 'flag', 	'text' => __('Flag', 'ws-form')),
						array('value' => 'heart', 	'text' => __('Heart', 'ws-form')),
						array('value' => 'smiley', 	'text' => __('Smiley', 'ws-form')),
						array('value' => 'square', 	'text' => __('Square', 'ws-form')),
						array('value' => 'star', 	'text' => __('Star', 'ws-form')),
						array('value' => 'thumb', 	'text' => __('Thumbs Up', 'ws-form')),
						array('value' => 'custom', 	'text' => __('Custom HTML', 'ws-form'))
					),
					'default'					=>	'star'
				),

				// Rating - Icon - HTML
				'rating_icon_html'				=> array(

					'label'						=>	__('HTML', 'ws-form'),
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'html_editor',
					'default'					=>	'<span>*</span>',
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'rating_icon',
							'meta_value'		=>	'custom'
						)
					),
					'help'						=>	__('Custom rating icon HTML.', 'ws-form')
				),

				// Rating - Size
				'rating_size'	=> array(

					'label'						=>	__('Size (Pixels)', 'ws-form'),
					'type'						=>	'number',
					'default'					=>	24,
					'min'						=>	1,
					'help'						=>	__('Size of unselected rating icons in pixels.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'rating_icon',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Rating - Color - Off
				'rating_color_off'	=> array(

					'label'						=>	__('Unselected Color', 'ws-form'),
					'mask'						=>	'data-rating-color-off="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'default'					=>	'#CECED2',
					'help'						=>	__('Color of unselected rating icons.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'rating_icon',
							'meta_value'		=>	'custom'
						)
					)
				),

				// Rating - Color - On
				'rating_color_on'	=> array(

					'label'						=>	__('Selected Color', 'ws-form'),
					'mask'						=>	'data-rating-color-on="#value"',
					'mask_disregard_on_empty'	=>	true,
					'type'						=>	'color',
					'default'					=>	'#FFCC00',
					'help'						=>	__('Color of selected rating icons.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'!=',
							'meta_key'			=>	'rating_icon',
							'meta_value'		=>	'custom'
						)
					)
				),
				// No duplicates
				'dedupe'	=> array(

					'label'						=>	__('No Duplicates', 'ws-form'),
					'type'						=>	'checkbox',
					'help'						=>	__('Do not allow duplicate values for this field.', 'ws-form')
				),

				// No duplications - Message
				'dedupe_message'	=> array(

					'label'						=>	__('Duplication Message', 'ws-form'),
					'placeholder'				=>	__('The value entered for #label_lowercase has already been used.', 'ws-form'),
					'type'						=>	'textarea',
					'help'						=>	__('Enter a message to be shown if a duplicate value is entered for this field. Leave blank for the default message.', 'ws-form'),
					'condition'					=>	array(

						array(

							'logic'				=>	'==',
							'meta_key'			=>	'dedupe',
							'meta_value'		=>	'on'
						)
					)
				),

				// Hidden (Never rendered but either have default values or are special attributes)

				'breakpoint'			=> array(

					'default'					=>	25
				),

				'tab_index'				=> array(

					'default'					=>	0
				),

				'list'					=> array(

					'mask'						=>	'list="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_disregard_on_zero'	=>	false,
				),

				'aria_label'			=> array(

					'label'						=>	__('ARIA Label', 'ws-form'),
					'mask'						=>	'aria-label="#value"',
					'mask_disregard_on_empty'	=>	true,
					'mask_placeholder'			=>	'#label',
					'compatibility_id'			=>	'wai-aria',
					'select_list'				=>	true
				),

				'aria_labelledby'		=> array(

					'mask'						=>	'aria-labelledby="#value"',
					'mask_disregard_on_empty'	=>	true
				),

				'aria_describedby'		=> array(

					'mask'						=>	'aria-describedby="#value"',
					'mask_disregard_on_empty'	=>	true
				),

				'class'					=> array(

					'mask'						=>	'class="#value"',
					'mask_disregard_on_empty'	=>	true,
				),

				'default'						=> array(

					'mask'						=>	'#value',
					'mask_disregard_on_empty'	=>	true,
				)
			);

			// Text editor types
			global $wp_version;
			if(version_compare($wp_version, '4.8', '>=')) {
				$meta_keys['input_type_textarea']['options'][] = array('value' => 'tinymce', 'text' => __('Visual Editor', 'ws-form'));
			}
			if(version_compare($wp_version, '4.9', '>=')) {
				$meta_keys['input_type_textarea']['options'][] = array('value' => 'html', 'text' => __('HTML Editor', 'ws-form'));
			}

			// Add mime types to accept
			$file_types = self::get_file_types();
			$mime_select_list = array();
			foreach($file_types as $mime_type => $file_type) {

				if($mime_type == 'default') { continue; }
				$mime_select_list[] = array('text' => $mime_type, 'value' => $mime_type);
			}
			usort($mime_select_list, function($a, $b) { if ($a['text'] == $b['text']) { return 0; } return ($a['text'] < $b['text']) ? -1 : 1; });
			$meta_keys['accept']['select_list'] = $mime_select_list;

			// Date format
			$date_formats = array_unique( apply_filters( 'date_formats', array( __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );
			foreach($date_formats as $date_format) {

				$meta_keys['format_date']['options'][] = array('value' => esc_attr($date_format), 'text' => date_i18n($date_format));	
			}

			// Time format
			$time_formats = array_unique( apply_filters( 'time_formats', array( __( 'g:i a' ), 'g:i A', 'H:i' ) ) );
			foreach($time_formats as $time_format) {

				$meta_keys['format_time']['options'][] = array('value' => esc_attr($time_format), 'text' => date_i18n($time_format));	
			}

			// Data source update frequencies

			// Add real-time
			$meta_keys['data_source_recurrence']['options'][] = array('value' => 'wsf_realtime', 'text' => __('Real-Time'));

			// Get registered schedules
			$schedules = wp_get_schedules();

			// Order by interval
			uasort($schedules, function ($schedule_1, $schedule_2) {
				if ($schedule_1['interval'] == $schedule_2['interval']) return 0;
				return $schedule_1['interval'] < $schedule_2['interval'] ? -1 : 1;
			});

			// IDs to include (also includes any schedule ID's beginning with wsf_)
			$wordpress_schedule_ids = array('hourly', 'twicedaily', 'daily', 'weekly');

			// Process schedules
			foreach($schedules as $schedule_id => $schedule_config) {

				if(
					!in_array($schedule_id, $wordpress_schedule_ids) &&
					(strpos($schedule_id, WS_FORM_DATA_SOURCE_SCHEDULE_ID_PREFIX) === false)
				) {
					continue;
				}

				$meta_keys['data_source_recurrence']['options'][] = array('value' => $schedule_id, 'text' => $schedule_config['display']);
			}

			// Apply filter
			$meta_keys = apply_filters('wsf_config_meta_keys', $meta_keys, $form_id);

			// Public parsing (To cut down on only output needed to render form
			if($public) {

				$public_attributes_public = array('key' => 'k', 'mask' => 'm', 'mask_disregard_on_empty' => 'e', 'mask_disregard_on_zero' => 'z', 'mask_placeholder' => 'p', 'html_encode' => 'h', 'price' => 'pr', 'default' => 'd', 'calc_type' => 'c');

				foreach($meta_keys as $key => $meta_key) {

					$meta_key_keep = false;

					foreach($public_attributes_public as $attribute => $attribute_public) {

						if(isset($meta_keys[$key][$attribute])) {

							$meta_key_keep = true;
							break;
						}
					}

					// Remove this meta key from public if it doesn't contain the keys we want for public
					if(!$meta_key_keep) { unset($meta_keys[$key]); }
				}

				$meta_keys_new = array();

				foreach($meta_keys as $key => $meta_key) {

					$meta_key_source = $meta_keys[$key];
					$meta_key_new = array();

					foreach($public_attributes_public as $attribute => $attribute_public) {

						if(isset($meta_key_source[$attribute])) {

							unset($meta_key_new[$attribute]);
							$meta_key_new[$attribute_public] = $meta_key_source[$attribute];
						}
					}

					$meta_keys_new[$key] = $meta_key_new;
				}

				$meta_keys = $meta_keys_new;
			}

			// Parse compatibility meta_keys
			if(!$public) {

				foreach($meta_keys as $key => $meta_key) {

					if(isset($meta_key['compatibility_id'])) {

						$meta_keys[$key]['compatibility_url'] = str_replace('#compatibility_id', $meta_key['compatibility_id'], WS_FORM_COMPATIBILITY_MASK);
						unset($meta_keys[$key]['compatibility_id']);
					}
				}
			}

			// Cache
			self::$meta_keys[$public] = $meta_keys;

			return $meta_keys;
		}

		// SVG - Logo
		public static function get_logo_svg() {

			return '<svg id="wsf_logo" viewBox="0 0 1500 428"><style>.st0{fill:#002d5d}.st1{fill:#a7a8aa}</style><path class="st0" d="M215.2 422.9l-44.3-198.4c-.4-1.4-.7-3-1-4.6-.3-1.6-3.4-18.9-9.3-51.8h-.6l-4.1 22.9-6.8 33.5-45.8 198.4H69.7L0 130.1h28.1L68 300.7l18.6 89.1h1.8c3.5-25.7 9.3-55.6 17.1-89.6l39.9-170H175l40.2 170.6c3.1 12.8 8.8 42.5 16.8 89.1h1.8c.6-5.9 3.5-20.9 8.7-44.8 5.2-23.9 21.9-95.5 50.1-214.8h27.8l-72.1 292.8h-33.1zM495 349.5c0 24.7-7.1 44-21.3 57.9-14.2 13.9-34.7 20.9-61.5 20.9-14.6 0-27.4-1.7-38.4-5.1-11-3.4-19.6-7.2-25.7-11.3l12.3-21.3c8.3 5.1 5.9 3.6 16.6 7.4 12 4.2 24.3 6.1 36.9 6.1 16.5 0 29.6-4.9 39-14.8 9.5-9.9 14.2-23.1 14.2-39.7 0-13-3.4-23.9-10.2-32.8-6.8-8.9-19.8-19-38.9-30.4-21.9-12.6-36.8-22.7-44.8-30.2-8-7.6-14.2-16-18.6-25.4-4.4-9.4-6.6-20.5-6.6-33.5 0-21.1 7.8-38.5 23.3-52.2 15.6-13.8 35.4-20.6 59.4-20.6 25.8 0 45.2 6.7 62.6 17.8L481 163.6c-16.2-9.9-33.3-14.8-51.4-14.8-16.6 0-29.8 4.5-39.6 13.4-9.9 8.9-14.8 20.6-14.8 35.2 0 13 3.3 23.8 10 32.5s20.9 19.3 42.6 31.7c21.3 12.8 35.9 23 43.7 30.6 7.9 7.6 13.7 16.1 17.6 25.4 4 9.2 5.9 19.9 5.9 31.9z"/><path class="st1" d="M643.8 152.8h-50.2V423h-27.8V152.8H525l.2-22.3h40.3l.3-25.5c0-37.2 3.6-60.9 13.4-77.2C589.5 10.7 606.6 0 630.5 0h28.9v23.6c-6.4 0-18.9.2-27.3.4-13.9.2-20.1 4.5-25.1 9.7-4.9 5.2-7.5 11.5-9.9 23.2-2.4 11.7-3.5 27.9-3.5 48.6v24.6h50.2v22.7zM857.1 275.8c0 49.3-8.5 87-25.6 113.2-17 26.2-41.4 39.3-73.1 39.3-31.3 0-55.3-13.1-72-39.3-16.7-26.2-25-63.9-25-113.2 0-100.9 32.7-151.4 98.1-151.4 30.7 0 54.7 13.2 71.8 39.7 17.2 26.4 25.8 63.7 25.8 111.7zm-166.4 0c0 42.3 5.5 74.2 16.6 95.8 11 21.6 28.3 32.4 51.7 32.4 45.9 0 68.9-42.7 68.9-128.2 0-84.7-23-127.1-68.9-127.1-24 0-41.4 10.6-52.2 31.8-10.7 21.3-16.1 53.1-16.1 95.3zM901.8 196.5c0-35.5 42.9-71.7 88.5-72 30.9-.3 42 8.6 53.2 13.7l-13.9 21.6c-9.7-5.1-18.8-9.2-39.9-9.9-13.3-.4-24.1 1.4-35.9 9.3-9.7 6.4-20.4 12.9-23.6 40.8-2.2 19-.8 45.9-.8 67.8V423h-28.1M1047.6 191.4c5.6-48.2 49.8-67.2 80.6-67.2 17.7 0 39.6 6.4 50.2 14.5 9.5 7.2 14.7 13.4 20.3 32.2 7.7-18 13.9-23.4 25.1-31.3 11.2-7.9 25.8-14.9 43.7-14.9 24.2 0 48.4 7.5 62.9 28.5 11.6 16.7 16.8 41 16.8 78.4V423h-27.8V223.5c.7-56.9-14.3-75.2-52-75.2-18.7 0-32.2 4.7-42.2 21.9-9.8 17-14.3 47.9-14.3 81.3v171.4h-27.8V223.5c0-24.8-3.8-43.3-11.5-55.5s-26.7-18.6-42.8-18.6c-21.3 0-35.6 10.4-45.3 28-9.7 17.6-8.6 45.1-8.6 84.6v160.9h-28.1M1467.2 109h-2.1l.4-28.5-6.1-.1v-2l14.3.2v2l-6.1-.1-.4 28.5zM1487.1 109.3l-7.8-27.8h-.2c.1 2.9.1 4.8.1 5.5l-.3 22.2h-2l.4-30.4h3l6.6 23.4c.6 2.1 1 3.8 1.1 5.3h.2c.2-1 .6-2.8 1.4-5.2l7.2-23.2h3.1l-.4 30.4h-2.1l.3-22c0-.9.1-2.7.3-5.7h-.2l-8.5 27.5h-2.2z"/><circle class="st1" cx="1412.6" cy="149.4" r="25.3"/><circle class="st1" cx="1412.6" cy="273" r="25.3"/><circle class="st1" cx="1412.6" cy="395" r="25.3"/></svg>';
		}

		// SVG - Icon 24 x 24 pixel
		public static function get_icon_24_svg($id = '') {

			$return_value = false;

			switch($id) {

				case 'bp-25' :

					$return_value = '<path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"></path>';
					break;

				case 'bp-50' :

					$return_value = '<path d="M18.5 0h-14C3.12 0 2 1.12 2 2.5v19C2 22.88 3.12 24 4.5 24h14c1.38 0 2.5-1.12 2.5-2.5v-19C21 1.12 19.88 0 18.5 0zm-7 23c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm7.5-4H4V3h15v16z"></path>';
					break;

				case 'bp-75' :

					$return_value = '<path d="M20 18c1.1 0 1.99-.9 1.99-2L22 5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2H0c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2h-4zM4 5h16v11H4V5zm8 14c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"></path>';
					break;

				case 'bp-100' :

					$return_value = '<path d="M21 2H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h7l-2 3v1h8v-1l-2-3h7c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 12H3V4h18v10z"></path>';
					break;

				case 'bp-125' :

					$return_value = '<path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"></path>';
					break;

				case 'bp-150' :

					$return_value = '<path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5v2h8v-2h5c1.1 0 1.99-.9 1.99-2L23 5c0-1.1-.9-2-2-2zm0 14H3V5h18v12z"></path>';
					break;
			}

			// Apply filter
			$return_value = apply_filters('wsf_config_icon_24_svg', '<svg height="24" width="24" viewBox="0 0 24 24">' . $return_value . '</svg>', $id);

			return $return_value;
		}
		// SVG - Icon 16 x 16 pixel
		public static function get_icon_16_svg($id = '') {

			$return_value = false;

			switch($id) {

				case 'actions' :

					$return_value = '<path d="M7.99 0l-7.010 9.38 6.020-0.42-4.96 7.040 12.96-10-7.010 0.47 7.010-6.47h-7.010z"></path>';
					break;

				case 'asterisk' :

					$return_value = '<path d="M15.9 5.7l-2-3.4-3.9 2.2v-4.5h-4v4.5l-4-2.2-2 3.4 3.9 2.3-3.9 2.3 2 3.4 4-2.2v4.5h4v-4.5l3.9 2.2 2-3.4-4-2.3z"></path>';
					break;

				case 'button' :

					$return_value = '<path d="M15 12h-14c-0.6 0-1-0.4-1-1v-6c0-0.6 0.4-1 1-1h14c0.6 0 1 0.4 1 1v6c0 0.6-0.4 1-1 1z"></path>';
					break;

				case 'calc' :

					$return_value = '<path d="M9 3h6v2h-6v-2z"></path><path d="M9 11h6v2h-6v-2z"></path><path d="M5 1h-2v2h-2v2h2v2h2v-2h2v-2h-2z"></path><path d="M7 10.4l-1.4-1.4-1.6 1.6-1.6-1.6-1.4 1.4 1.6 1.6-1.6 1.6 1.4 1.4 1.6-1.6 1.6 1.6 1.4-1.4-1.6-1.6z"></path><path d="M13 14.5c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path><path d="M13 9.5c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path>';
					break;

				case 'calculator' :

					$return_value = '<path d="M14.1 1.7v12.7c-.1.4-.2.8-.5 1-.4.4-.8.6-1.3.6h-9c-.4-.1-.8-.3-1.1-.6-.2-.4-.4-.7-.4-1.2V1.9c0-.3.1-.7.3-1 .3-.5.7-.8 1.2-.9h9.1c.2 0 .4.1.6.2.5.3.9.7 1 1.3.1.1.1.2.1.2zM7.9 5.6H12.3c.3-.1.5-.3.5-.6V1.9c0-.1 0-.2-.1-.3-.1-.2-.3-.3-.6-.3H3.6c-.3 0-.6.2-.6.6V5c0 .2.1.3.2.4.1.1.3.2.5.2h4.2zm3.7 3h.6c.3 0 .5-.3.6-.5v-.7-.1c-.1-.3-.3-.5-.6-.5H11h-.1c-.2 0-.5.3-.5.5V8.1c.1.3.3.5.6.5h.6zm0 3.1h.7c.3 0 .5-.3.6-.5v-.7c0-.3-.3-.6-.6-.6h-1.2-.1c-.3 0-.5.3-.5.5V11.2c.1.3.3.4.6.4.1.1.3.1.5.1zm0 3.1h.8c.3-.1.5-.3.5-.5v-.7c0-.3-.3-.6-.6-.6h-1.2-.1c-.2 0-.5.3-.5.5v.8c0 .3.3.5.6.5h.5zM4.3 8.6h.6c.3 0 .6-.3.6-.6v-.7c0-.3-.3-.6-.6-.6H3.7c-.4.1-.7.4-.7.7v.7c.1.3.3.5.6.5h.7zm0 3.1h.6c.3 0 .6-.3.6-.6v-.7c0-.3-.3-.6-.6-.6H3.6c-.3.1-.6.4-.6.7v.7c.1.3.3.5.6.5h.7zm3.6 3.1h.6c.3 0 .6-.3.6-.6v-.6c0-.3-.3-.6-.6-.6H7.2c-.3 0-.6.3-.6.6V14.4c.1.3.3.5.6.5.3-.1.5-.1.7-.1zm0-3.1h.6c.3 0 .6-.3.6-.6v-.6-.1c0-.3-.3-.5-.6-.5H7.2c-.3 0-.6.3-.6.6v.7c0 .3.3.6.6.6.3-.1.5-.1.7-.1zm-3.6 3.1H5c.3 0 .6-.3.6-.6v-.7c0-.3-.3-.6-.6-.6H3.7h-.1c-.4.1-.6.4-.6.7v.7c.1.3.3.5.6.5h.7zm3.6-6.2h.7c.3 0 .5-.2.6-.5v-.4-.4c0-.3-.3-.6-.6-.6H7.4h-.1c-.3 0-.5.3-.5.6V8.1c.1.3.3.5.6.5h.5z"/><path d="M3.5 0h-.2c-.6.2-1 .5-1.2 1-.2.3-.3.6-.3 1v12.3c0 .4.2.8.4 1.2.3.3.6.5 1.1.6h9c.5 0 1-.2 1.3-.6.3-.3.4-.6.5-1v-.1 1.7H1.8V.1C2.4 0 2.9 0 3.5 0zM14.1 1.7v-.2c-.1-.6-.5-1-1-1.3-.2-.1-.4-.2-.6-.2h1.7c-.1.6-.1 1.2-.1 1.7z"/><path d="M9.8 3.4v-.9c0-.3.2-.6.5-.6h1.3c.3 0 .5.2.6.5v2c0 .3-.2.5-.5.6h-1.3c-.3 0-.6-.2-.6-.5v-.1-1zm1.8.9V2.5h-1.2v1.8h1.2z"/><path d="M11.6 4.3h-1.2V2.5h1.2v1.8z"/>';
					break;

				case 'check' :

					$return_value = '<path d="M7.3 14.2l-7.1-5.2 1.7-2.4 4.8 3.5 6.6-8.5 2.3 1.8z"></path>';
					break;

				case 'checkbox' :

					$return_value = '<path d="M14 6.2v7.8h-12v-12h10.5l1-1h-12.5v14h14v-9.8z"></path><path d="M7.9 10.9l-4.2-4.2 1.5-1.4 2.7 2.8 6.7-6.7 1.4 1.4z"></path>';
					break;

				case 'clear' :

					$return_value = '<path d="M8.1 14l6.4-7.2c0.6-0.7 0.6-1.8-0.1-2.5l-2.7-2.7c-0.3-0.4-0.8-0.6-1.3-0.6h-1.8c-0.5 0-1 0.2-1.4 0.6l-6.7 7.6c-0.6 0.7-0.6 1.9 0.1 2.5l2.7 2.7c0.3 0.4 0.8 0.6 1.3 0.6h11.4v-1h-7.9zM6.8 13.9c0 0 0-0.1 0 0l-2.7-2.7c-0.4-0.4-0.4-0.9 0-1.3l3.4-3.9h-1l-3 3.3c-0.6 0.7-0.6 1.7 0.1 2.4l2.3 2.3h-1.3c-0.2 0-0.4-0.1-0.6-0.2l-2.8-2.8c-0.3-0.3-0.3-0.8 0-1.1l3.5-3.9h1.8l3.5-4h1l-3.5 4 3.1 3.7-3.5 4c-0.1 0.1-0.2 0.1-0.3 0.2z"></path>';
					break;

				case 'clone' :

					$return_value = '<path d="M6 0v3h3z"></path><path d="M9 4h-4v-4h-5v12h9z"></path><path d="M13 4v3h3z"></path><path d="M12 4h-2v9h-3v3h9v-8h-4z"></path>';
					break;

				case 'close-circle' :

					$return_value = '<path d="M8,0 C3.6,0 0,3.6 0,8 C0,12.4 3.6,16 8,16 C12.4,16 16,12.4 16,8 C16,3.6 12.4,0 8,0 Z"></path><polygon fill="#FFFFFF" points="12.2 10.8 10.8 12.2 8 9.4 5.2 12.2 3.8 10.8 6.6 8 3.8 5.2 5.2 3.8 8 6.6 10.8 3.8 12.2 5.2 9.4 8 12.2 10.8"></polygon>';
					break;

				case 'color' :

					$return_value = '<path d="M15 1c-1.8-1.8-3.7-0.7-4.6 0.1-0.4 0.4-0.7 0.9-0.7 1.5v0c0 1.1-1.1 1.8-2.1 1.5l-0.1-0.1-0.7 0.8 0.7 0.7-6 6-0.8 2.3-0.7 0.7 1.5 1.5 0.8-0.8 2.3-0.8 6-6 0.7 0.7 0.7-0.6-0.1-0.2c-0.3-1 0.4-2.1 1.5-2.1v0c0.6 0 1.1-0.2 1.4-0.6 0.9-0.9 2-2.8 0.2-4.6zM3.9 13.6l-2 0.7-0.2 0.1 0.1-0.2 0.7-2 5.8-5.8 1.5 1.5-5.9 5.7z"></path>';
					break;

				case 'conditional' :

					$return_value = '<path d="M14 13v-1c0-0.2 0-4.1-2.8-5.4-2.2-1-2.2-3.5-2.2-3.6v-3h-2v3c0 0.1 0 2.6-2.2 3.6-2.8 1.3-2.8 5.2-2.8 5.4v1h-2l3 3 3-3h-2v-1c0 0 0-2.8 1.7-3.6 1.1-0.5 1.8-1.3 2.3-2 0.5 0.8 1.2 1.5 2.3 2 1.7 0.8 1.7 3.6 1.7 3.6v1h-2l3 3 3-3h-2z" transform="translate(8.000000, 8.000000) rotate(-180.000000) translate(-8.000000, -8.000000)"></path>';
					break;

				case 'contract' :

					$return_value = '<path d="M12 0h-12v12l1-1v-10h10z"></path><path d="M4 16h12v-12l-1 1v10h-10z"></path><path d="M7 9h-5l1.8 1.8-3.8 3.8 1.4 1.4 3.8-3.8 1.8 1.8z"></path><path d="M16 1.4l-1.4-1.4-3.8 3.8-1.8-1.8v5h5l-1.8-1.8z"></path>';
					break;

				case 'datetime' :

					$return_value = '<path d="M3 0h1v3h-1v-3z"></path><path d="M11 0h1v3h-1v-3z"></path><path d="M6.6 14h-5.6v-8h13v0.6c0.4 0.2 0.7 0.4 1 0.7v-6.3h-2v3h-3v-3h-5v3h-3v-3h-2v14h7.3c-0.3-0.3-0.5-0.6-0.7-1z"></path><path d="M14 12h-3v-3h1v2h2z"></path><path d="M11.5 8c1.9 0 3.5 1.6 3.5 3.5s-1.6 3.5-3.5 3.5-3.5-1.6-3.5-3.5 1.6-3.5 3.5-3.5zM11.5 7c-2.5 0-4.5 2-4.5 4.5s2 4.5 4.5 4.5 4.5-2 4.5-4.5-2-4.5-4.5-4.5v0z"></path>';
					break;

				case 'delete' :

					$return_value = '<path d="M13 3s0-0.51-2-0.8v-0.7c-0.017-0.832-0.695-1.5-1.53-1.5-0 0-0 0-0 0h-3c-0.815 0.017-1.47 0.682-1.47 1.5 0 0 0 0 0 0v0.7c-0.765 0.068-1.452 0.359-2.007 0.806l-0.993-0.006v1h12v-1h-1zM6 1.5c0.005-0.274 0.226-0.495 0.499-0.5l3.001-0c0 0 0.001 0 0.001 0 0.282 0 0.513 0.22 0.529 0.499l0 0.561c-0.353-0.042-0.763-0.065-1.178-0.065-0.117 0-0.233 0.002-0.349 0.006-0.553-0-2.063-0-2.503 0.070v-0.57z"></path><path d="M2 5v1h1v9c1.234 0.631 2.692 1 4.236 1 0.002 0 0.003 0 0.005 0h1.52c0.001 0 0.003 0 0.004 0 1.544 0 3.002-0.369 4.289-1.025l-0.054-8.975h1v-1h-12zM6 13.92q-0.51-0.060-1-0.17v-6.75h1v6.92zM9 14h-2v-7h2v7zM11 13.72c-0.267 0.070-0.606 0.136-0.95 0.184l-0.050-6.904h1v6.72z"></path>';
					break;

				case 'disabled' :

					$return_value = '<path d="M8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM8 2c1.3 0 2.5 0.4 3.5 1.1l-8.4 8.4c-0.7-1-1.1-2.2-1.1-3.5 0-3.3 2.7-6 6-6zM8 14c-1.3 0-2.5-0.4-3.5-1.1l8.4-8.4c0.7 1 1.1 2.2 1.1 3.5 0 3.3-2.7 6-6 6z"></path>';
					break;

				case 'divider' :

					$return_value = '<path d="M0 7.38h16v1.44H0z"/>';
					break;

				case 'down' :

					$return_value = '<path d="M15 3H1l7 10 7-10z"/>';
					break;

				case 'download' :

					$return_value = '<path d="M12,9h3V0L1,0v9h3V8H2V1h12v7h-2V9z"/><path d="M8,16l4-5h-2V6H6v5H4L8,16z"/>';
					break;

				case 'edit' :

					$return_value = '<path d="M16 9v-2l-1.7-0.6c-0.2-0.6-0.4-1.2-0.7-1.8l0.8-1.6-1.4-1.4-1.6 0.8c-0.5-0.3-1.1-0.6-1.8-0.7l-0.6-1.7h-2l-0.6 1.7c-0.6 0.2-1.2 0.4-1.7 0.7l-1.6-0.8-1.5 1.5 0.8 1.6c-0.3 0.5-0.5 1.1-0.7 1.7l-1.7 0.6v2l1.7 0.6c0.2 0.6 0.4 1.2 0.7 1.8l-0.8 1.6 1.4 1.4 1.6-0.8c0.5 0.3 1.1 0.6 1.8 0.7l0.6 1.7h2l0.6-1.7c0.6-0.2 1.2-0.4 1.8-0.7l1.6 0.8 1.4-1.4-0.8-1.6c0.3-0.5 0.6-1.1 0.7-1.8l1.7-0.6zM8 12c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"></path><path d="M10.6 7.9c0 1.381-1.119 2.5-2.5 2.5s-2.5-1.119-2.5-2.5c0-1.381 1.119-2.5 2.5-2.5s2.5 1.119 2.5 2.5z"></path>';
					break;

				case 'email' :

					$return_value = '<path d="M0 3h16v2.4l-8 4-8-4z"></path><path d="M0 14l5.5-4.8 2.5 1.4 2.5-1.4 5.5 4.8z"></path><path d="M4.6 8.8l-4.6-2.3v6.5z"></path><path d="M11.4 8.8l4.6-2.3v6.5z"></path>';
					break;

				case 'exchange' :

					$return_value = '<path d="M16 5v2h-13v2l-3-3 3-3v2z"></path><path d="M0 12v-2h13v-2l3 3-3 3v-2z"></path>';
					break;

				case 'expand' :

					$return_value = '<path d="M11 2h-9v9l1-1v-7h7z"></path><path d="M5 14h9v-9l-1 1v7h-7z"></path><path d="M16 0h-5l1.8 1.8-4.5 4.5 1.4 1.4 4.5-4.5 1.8 1.8z"></path><path d="M7.7 9.7l-1.4-1.4-4.5 4.5-1.8-1.8v5h5l-1.8-1.8z"></path>';
					break;

				case 'file' :

					$return_value = '<path d="M2.7 15.3c-0.7 0-1.4-0.3-1.9-0.8-0.9-0.9-1.2-2.5 0-3.7l8.9-8.9c1.4-1.4 3.8-1.4 5.2 0s1.4 3.8 0 5.2l-7.4 7.4c-0.2 0.2-0.5 0.2-0.7 0s-0.2-0.5 0-0.7l7.4-7.4c1-1 1-2.7 0-3.7s-2.7-1-3.7 0l-8.9 8.9c-0.8 0.8-0.6 1.7 0 2.2 0.6 0.6 1.5 0.8 2.2 0l8.9-8.9c0.2-0.2 0.2-0.5 0-0.7s-0.5-0.2-0.7 0l-7.4 7.4c-0.2 0.2-0.5 0.2-0.7 0s-0.2-0.5 0-0.7l7.4-7.4c0.6-0.6 1.6-0.6 2.2 0s0.6 1.6 0 2.2l-8.9 8.9c-0.6 0.4-1.3 0.7-1.9 0.7z"></path>';
					break;

				case 'file-code' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M6.2 13h-0.7l-2-2.5 2-2.5h0.7l-2 2.5z"></path><path d="M9.8 13h0.7l2-2.5-2-2.5h-0.7l2 2.5z"></path><path d="M6.7 14h0.6l2.1-7h-0.8z"></path>';
					break;

				case 'file-default' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path>';
					break;

				case 'file-font' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M5 7v2h2v5h2v-5h2v-2z"></path>';
					break;

				case 'file-movie' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M10 10v-2h-6v5h6v-2l2 2v-5z"></path>';
					break;

				case 'file-picture' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M4 11.5v2.5h8v-1.7c0 0 0.1-1.3-1.3-1.5-1.3-0.2-1.5 0.4-2.5 0.5-0.8 0-0.6-1.3-2.2-1.3-1.2 0-2 1.5-2 1.5z"></path><path d="M12 8.5c0 0.828-0.672 1.5-1.5 1.5s-1.5-0.672-1.5-1.5c0-0.828 0.672-1.5 1.5-1.5s1.5 0.672 1.5 1.5z"></path>';
					break;

				case 'file-presentation' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM13 15h-10v-14h6v4h4v10zM10 4v-3l3 3h-3z"></path><path d="M9 6h-2v1h-3v6h2v1h1v-1h2v1h1v-1h2v-6h-3v-1zM11 8v4h-6v-4h6z"></path><path d="M7 9v2l2-1z"></path>';
					break;

				case 'file-sound' :

					$return_value = '<path d="M11.4 10.5c0 1.2-0.4 2.2-1 3l0.4 0.5c0.7-0.9 1.2-2.1 1.2-3.5s-0.5-2.6-1.2-3.5l-0.4 0.5c0.6 0.8 1 1.9 1 3z"></path><path d="M9.9 8l-0.4 0.5c0.4 0.5 0.7 1.2 0.7 2s-0.3 1.5-0.7 2l0.4 0.5c0.5-0.6 0.8-1.5 0.8-2.5s-0.3-1.8-0.8-2.5z"></path><path d="M9.1 9l-0.4 0.5c0.2 0.3 0.3 0.6 0.3 1s-0.1 0.7-0.3 1l0.4 0.5c0.3-0.4 0.5-0.9 0.5-1.5s-0.2-1.1-0.5-1.5z"></path><path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M6 9h-2v3h2l2 2v-7z"></path>';
					break;

				case 'file-table' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M4 7v6h8v-6h-8zM6 12h-1v-1h1v1zM6 10h-1v-1h1v1zM9 12h-2v-1h2v1zM9 10h-2v-1h2v1zM11 12h-1v-1h1v1zM11 10h-1v-1h1v1z"></path>';
					break;

				case 'file-text' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 5h4v10h-10v-14h6v4zM10 4v-3l3 3h-3z"></path><path d="M4 7h8v1h-8v-1z"></path><path d="M4 9h8v1h-8v-1z"></path><path d="M4 11h8v1h-8v-1z"></path>';
					break;

				case 'file-zip' :

					$return_value = '<path d="M10 0h-8v16h12v-12l-4-4zM9 15h-4v-2.8l0.7-2.2h2.4l0.9 2.2v2.8zM13 15h-3v-3l-1-3h-2v-1h-2v1l-1 3v3h-1v-14h4v1h2v1h-2v1h2v1h4v10zM10 4v-3l3 3h-3z"></path><path d="M5 6h2v1h-2v-1z"></path><path d="M5 2h2v1h-2v-1z"></path><path d="M5 4h2v1h-2v-1z"></path><path d="M7 5h2v1h-2v-1z"></path><path d="M7 7h2v1h-2v-1z"></path><path d="M6 12h2v2h-2v-2z"></path>';
					break;

				case 'first' :

					$return_value = '<path d="M14 15v-14l-10 7z"></path><path d="M2 1h2v14h-2v-14z"></path>';
					break;

				case 'group' :

					$return_value = '<path d="M14 4v-2h-14v12h16v-10h-2zM10 3h3v1h-3v-1zM6 3h3v1h-3v-1zM15 13h-14v-10h4v2h10v8z"></path>';
					break;

				case 'hidden' :

					$return_value = '<path d="M12.9 5.2l-0.8 0.8c1.7 0.9 2.5 2.3 2.8 3-0.7 0.9-2.8 3.1-7 3.1-0.7 0-1.2-0.1-1.8-0.2l-0.8 0.8c0.8 0.3 1.7 0.4 2.6 0.4 5.7 0 8.1-4 8.1-4s-0.6-2.4-3.1-3.9z"></path><path d="M12 7.1c0-0.3 0-0.6-0.1-0.8l-4.8 4.7c0.3 0 0.6 0.1 0.9 0.1 2.2 0 4-1.8 4-4z"></path><path d="M15.3 0l-4.4 4.4c-0.8-0.2-1.8-0.4-2.9-0.4-6.7 0-8 5.1-8 5.1s1 1.8 3.3 3l-3.3 3.2v0.7h0.7l15.3-15.3v-0.7h-0.7zM4 11.3c-1.6-0.7-2.5-1.8-2.9-2.3 0.3-0.7 1.1-2.2 3.1-3.2-0.1 0.4-0.2 0.8-0.2 1.3 0 1.1 0.5 2.2 1.3 2.9l-1.3 1.3zM6.2 7.9l-1 0.2c0 0-0.3-0.5-0.3-1.2 0-0.8 0.4-1.5 0.4-1.5 0.5-0.3 1.3-0.3 1.3-0.3s-0.5 0.9-0.5 1.7c-0.1 0.7 0.1 1.1 0.1 1.1z"></path>';
					break;

				case 'html' :

					$return_value = '<path d="M5.2 14l4.5-12h1.1l-4.5 12z"></path><path d="M11.1 13h1.2l3.7-5-3.7-5h-1.3l3.8 5z"></path><path d="M4.9 13h-1.2l-3.7-5 3.7-5h1.3l-3.8 5z"></path>';
					break;

				case 'info-circle' :

					$return_value = '<path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 13H7V6h2v7zm0-8H7V3h2v2z"/>';
					break;

				case 'last' :

					$return_value = '<path d="M2 1v14l10-7z"></path><path d="M12 1h2v14h-2v-14z"></path>';
					break;

				case 'legal' :

					$return_value = '<path d="M5.2 5.3c.5 1 .9 2 1.4 3.1-.1 0-.2-.1-.3-.1-.4-.1-.7-.2-1-.3-.1 0-.1 0-.2.1-.2.3-.3.7-.5 1.1 0 0 0 .1-.1.1-.4-.9-.8-1.7-1.2-2.6-.4.9-.8 1.7-1.2 2.6l-.3-.6-.3-.6C1.4 8 1.4 8 1.3 8l-1.2.3H0c.5-1 1-2 1.4-3.1-.1 0-.1-.1-.2-.1-.3-.1-.5-.3-.5-.7 0-.3-.1-.6-.3-.8-.2-.3-.2-.6 0-.9.2-.3.3-.6.3-1 0-.3.2-.5.4-.6.4-.1.7-.3.9-.6.2-.2.5-.3.7-.2.4.1.7.1 1.1 0 .3-.1.5 0 .7.2.2.3.5.5.8.6.3.1.5.4.5.7 0 .3.1.6.3.8.1.1.1.2.1.3.1.2.1.3 0 .5 0 .1-.1.2-.1.3-.1.2-.2.4-.2.7v.3c0 .2-.2.4-.4.5-.1 0-.2 0-.3.1zm-.1-2.2c0-1-.8-1.9-1.8-1.9-1.1 0-1.9.8-1.9 1.9 0 1 .8 1.9 1.8 1.9 1.1 0 1.9-.9 1.9-1.9zM2.8 9.2c-.1.2-.2.5-.2.8v5.6h9.8v-3.9c-.4.4-.7.8-1.1 1.2V14.4H3.9V14 9.3v-.1c-.3-.4-.4-.7-.6-1.1-.2.4-.3.7-.5 1.1zm6.2 2l1.4 1.4c1.6-1.6 3.2-3.3 4.8-4.9l-1.4-1.4C12.2 8 10.6 9.6 9 11.2zm2.3-3.5l1-1c.1-.1.1-.1.1-.2V2.3v-.1H6.6c.1.2.2.4.2.6.1.2 0 .4.1.6h4.3c0 1.4 0 2.9.1 4.3zm-6.2 3.4h3.2s.1 0 .1-.1c.3-.3.7-.6 1-.9.1-.1.1-.1.2-.1H5.1c-.1.3-.1.7 0 1.1zM6.6 5v1.2H10V5H6.6zm.2 2.6c.1.3.3.7.4 1 0 .1.1.1.1.1h2.6V7.5c-1 0-2 0-3.1.1zm8.7-.3c.1-.1.3-.2.4-.4.2-.2.1-.4 0-.6l-.8-.8c-.1-.1-.4-.2-.5 0l-.4.4c.4.5.9 1 1.3 1.4zm-7.1 5.1c0 .1 0 .1 0 0 .2.3.4.5.6.7h.1c.3-.1.6-.1.8-.2-.4-.5-.9-.9-1.3-1.4-.1.4-.1.6-.2.9zm-.2 1c.3-.1.5-.1.7-.2-.2-.2-.4-.4-.5-.6-.1.3-.2.5-.2.8z"/>';
					break;

				case 'markup-circle' :

					$return_value = '<path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM6.3 6.2L3.3 8l3 1.8v1.9L1.9 8.8V7.2l4.3-2.9v1.9zM7.6 13h-.9L8.3 3h.9L7.6 13zm2-1.3V9.8l3-1.8-3-1.8V4.3L14 7.2v1.6l-4.4 2.9z"/>';
					break;

				case 'menu' :

					$return_value = '<path d="M0 1h16v3h-16v-3z"></path><path d="M0 6h16v3h-16v-3z"></path><path d="M0 11h16v3h-16v-3z"></path>';
					break;

				case 'minus' :

					$return_value = '<path d="M2 7h12v2h-12v-2z"></path>';
					break;

				case 'minus-circle' :

					$return_value = '<path d="M8,0 C3.6,0 0,3.6 0,8 C0,12.4 3.6,16 8,16 C12.4,16 16,12.4 16,8 C16,3.6 12.4,0 8,0 Z"></path><polygon fill="#FFFFFF" points="13 9 3 9 3 7 13 7"></polygon>';
					break;

				case 'next' :

					$return_value = '<path d="M3 1v14l10-7L3 1z"/>';
					break;

				case 'number' :

					$return_value = '<path d="M15 6v-2h-2.6l0.6-2.8-2-0.4-0.7 3.2h-3l0.7-2.8-2-0.4-0.7 3.2h-3.3v2h2.9l-0.9 4h-3v2h2.6l-0.6 2.8 2 0.4 0.7-3.2h3l-0.7 2.8 2 0.4 0.7-3.2h3.3v-2h-2.9l0.9-4h3zM9 10h-3l1-4h3l-1 4z"></path>';
					break;

				case 'password' :

					$return_value = '<path d="M16 5c0-0.6-0.4-1-1-1h-14c-0.6 0-1 0.4-1 1v6c0 0.6 0.4 1 1 1h14c0.6 0 1-0.4 1-1v-6zM15 11h-14v-6h14v6z"></path><path d="M6 8c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path><path d="M9 8c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path><path d="M12 8c0 0.552-0.448 1-1 1s-1-0.448-1-1c0-0.552 0.448-1 1-1s1 0.448 1 1z"></path>';
					break;

				case 'picture' :

					$return_value = '<path d="M16 14h-16v-12h16v12zM1 13h14v-10h-14v10z"></path><path d="M2 10v2h12v-1c0 0 0.2-1.7-2-2-1.9-0.3-2.2 0.6-3.8 0.6-1.1 0-0.9-1.6-3.2-1.6-1.7 0-3 2-3 2z"></path><path d="M13 6c0 1.105-0.895 2-2 2s-2-0.895-2-2c0-1.105 0.895-2 2-2s2 0.895 2 2z"></path>';
					break;

				case 'plus' :

					$return_value = '<path d="M14 7h-5v-5h-2v5h-5v2h5v5h2v-5h5v-2z"></path>';
					break;

				case 'plus-circle' :

					$return_value = '<path d="M8,0 C3.6,0 0,3.6 0,8 C0,12.4 3.6,16 8,16 C12.4,16 16,12.4 16,8 C16,3.6 12.4,0 8,0 Z"></path><polygon fill="#FFFFFF" points="13 9 9 9 9 13 7 13 7 9 3 9 3 7 7 7 7 3 9 3 9 7 13 7"></polygon>';
					break;

				case 'previous' :

					$return_value = '<path d="M14 15V1L4 8z"/>';
					break;

				case 'price' :

					$return_value = '<path d="M5.6 16h-.2c-.2 0-.3-.1-.5-.3l-1.3-1.3-3.3-3.3c-.1-.2-.3-.4-.3-.6 0-.3.1-.6.3-.9l.1-.1C2.6 7.2 4.9 5 7.1 2.7c.1-.1.2-.1.3-.1h2.5v.3c0 .2 0 .4.1.6-.1.3 0 .5 0 .7-.2.3-.2.5-.2.8 0 .6.5 1.1 1.1 1.2.4.1.8 0 1.1-.4.3-.3.4-.6.4-1s-.2-.7-.5-1v-.1c0-.2-.1-.4-.1-.6v-.5h.2c.2 0 .4 0 .5.1.1 0 .3.1.4.1.1.2.2.2.2.4s.1.3.1.5.1.4.1.7c0 .2 0 .4.1.6 0 .2 0 .4.1.6V8.2c0 .2 0 .4-.1.5 0 .1-.1.1-.1.2l-6.8 6.8c-.2.2-.4.3-.7.3h-.2z"/><path d="M16 3.2c0 .3 0 .7-.1 1-.1.9-.4 1.8-.7 2.6-.1.2-.2.3-.3.5-.1.2-.2.3-.4.3s-.3-.1-.4-.2c-.1-.2-.1-.4 0-.5.3-.5.5-1.1.7-1.7.1-.3.1-.6.2-1 0-.4.1-.8 0-1.2 0-.4-.1-.8-.4-1.2-.2-.5-.6-.7-1.1-.8-.4-.1-.8 0-1.1.1-.6.3-.9.7-1.1 1.3-.1.2-.1.5 0 .7 0 .3.1.6.1.8 0 .3.1.6.2.9.1.3-.1.6-.4.6-.2 0-.4-.1-.5-.4-.1-.3-.1-.7-.2-1-.1-.4-.1-.7-.1-1.1 0-.7.2-1.3.6-1.8.4-.5.8-.8 1.4-1 .3-.1.6-.1.9-.1.8 0 1.4.4 1.9.9.4.4.6.9.7 1.5.1.3.1.5.1.8z"/>';
					break;

				case 'progress' :

					$return_value = '<path d="M0 5v6h16v-6h-16zM15 10h-14v-4h14v4z"></path><path d="M2 7h7v2h-7v-2z"></path>';
					break;

				case 'publish' :

					$return_value = '<path d="M14.1 10.9c0-0.2 0-0.4 0-0.6 0-2.4-1.9-4.3-4.2-4.3-0.3 0-0.6 0-0.9 0.1v-2.1h2l-3-4-3 4h2v1.5c-0.4-0.2-0.9-0.3-1.3-0.3-1.6 0-2.9 1.2-2.9 2.8 0 0.3 0.1 0.6 0.2 0.9-1.6 0.2-3 1.8-3 3.5 0 1.9 1.5 3.6 3.3 3.6h10.3c1.4 0 2.4-1.4 2.4-2.6s-0.8-2.2-1.9-2.5zM13.6 15h-10.3c-1.2 0-2.3-1.2-2.3-2.5s1.1-2.5 2.3-2.5c0.1 0 0.3 0 0.4 0l1.3 0.3-0.8-1.2c-0.2-0.3-0.4-0.7-0.4-1.1 0-1 0.8-1.8 1.8-1.8 0.5 0 1 0.2 1.3 0.6v3.2h2v-2.8c0.3-0.1 0.6-0.1 0.9-0.1 1.8 0 3.2 1.5 3.2 3.3 0 0.3 0 0.6-0.1 0.9l-0.2 0.6h0.8c0.7 0 1.4 0.7 1.4 1.5 0.1 0.7-0.5 1.6-1.3 1.6z"></path>';
					break;

				case 'quantity' :

					$return_value = '<path d="M16 5.2c0 .2-.1.3-.1.5-.4 1.7-1.9 2.8-3.6 2.7-1.7-.1-3-1.3-3.2-3-.3-2 1.1-3.8 3.1-4 1.8-.1 3.5 1.2 3.8 3.2v.6zm-4 .3v1.1c0 .3.1.4.4.4h.3c.2 0 .3-.1.3-.4V5.5h1.2c.3 0 .4-.1.4-.4v-.3c0-.3-.1-.4-.4-.4h-1.1v-.2-1c-.1-.1-.2-.2-.4-.2h-.4c-.2 0-.3 0-.3.3v1.1h-1.2c-.2 0-.3.1-.3.3V5c0 .3.1.3.3.3.4.2.8.2 1.2.2z"/><path d="M11.8 9.2c.3 0 .7 0 1 .1-.1.4-.2.7-.3 1.1-.1.5-.2.6-.8.6H4c-.3.1-.5.4-.5.7 0 .3.3.6.6.6 0 0 0-.1.1-.1.3-.5.7-.8 1.4-.8.6 0 1 .3 1.3.9.1.1.1.1.2.1H9c.1 0 .1 0 .2-.1.3-.6.7-.9 1.4-.9.6 0 1.1.3 1.4.9 0 .1.1.1.2.1h.5c.3 0 .5.2.5.5s-.2.5-.5.5h-.5c-.1 0-.2 0-.2.1-.5 1-1.7 1.2-2.5.4-.1-.1-.2-.2-.2-.4 0-.1-.1-.1-.2-.1h-2c-.1 0-.1 0-.1.1-.3.6-.7.9-1.4.9-.6 0-1.1-.3-1.3-.9 0-.1-.1-.1-.2-.1-.7-.2-1.3-.7-1.4-1.4-.1-.8.3-1.6 1.1-1.9.1 0 .1 0 .2-.1-.3-.4-.4-.8-.6-1.3C2.9 6.9 2.3 5 1.7 3.1c0-.1-.1-.2-.2-.2-.4.1-.7.1-1 .1-.3-.1-.5-.3-.5-.5 0-.3.2-.5.5-.5h1.4c.3 0 .5.1.5.4.2.5.3 1 .4 1.4.2.1.2.2.3.2h5.2c0 .3-.1.7-.1 1H7v1h1.3c.1.3.2.7.4 1H7v1H9.6c.4.4.9.7 1.4.9h.1-.5v1h1l.1-.1c0-.2.1-.4.1-.6zM6 5H3.3l.3.9s0 .1.1.1h2.2c.1-.4.1-.7.1-1zm1 5h2.5V9H7v1zM3.9 7c.1.3.2.5.3.8 0 .1.1.2.2.2H6V7H3.9zM6 10V9H4.7h-.1l.3.9.1.1h1zm.1 3c0-.3-.3-.6-.6-.6s-.6.3-.6.6.3.6.6.6c.3-.1.6-.3.6-.6zm5 0c0-.3-.3-.6-.6-.6s-.6.3-.6.6.3.6.6.6c.3-.1.6-.3.6-.6z"/>';
					break;

				case 'question-circle' :

					$return_value = '<path d="M8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM8.9 13h-2v-2h2v2zM11 8.1c-0.4 0.4-0.8 0.6-1.2 0.7-0.6 0.4-0.8 0.2-0.8 1.2h-2c0-2 1.2-2.6 2-3 0.3-0.1 0.5-0.2 0.7-0.4 0.1-0.1 0.3-0.3 0.1-0.7-0.2-0.5-0.8-1-1.7-1-1.4 0-1.6 1.2-1.7 1.5l-2-0.3c0.1-1.1 1-3.2 3.6-3.2 1.6 0 3 0.9 3.6 2.2 0.4 1.1 0.2 2.2-0.6 3z"></path>';
					break;

				case 'radio' :

					$return_value = '<path d="M8 4c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4z"></path><path d="M8 1c3.9 0 7 3.1 7 7s-3.1 7-7 7-7-3.1-7-7 3.1-7 7-7zM8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8v0z"></path>';
					break;

				case 'range' :

					$return_value = '<path d="M16 6h-3.6c-0.7-1.2-2-2-3.4-2s-2.8 0.8-3.4 2h-5.6v4h5.6c0.7 1.2 2 2 3.4 2s2.8-0.8 3.4-2h3.6v-4zM1 9v-2h4.1c0 0.3-0.1 0.7-0.1 1s0.1 0.7 0.1 1h-4.1zM9 11c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3c0 1.7-1.3 3-3 3z"></path>';
					break;

				case 'rating' :

					$return_value = '<path d="M12.9 15.8c-1.6-1.2-3.2-2.5-4.9-3.7-1.6 1.3-3.3 2.5-4.9 3.7 0 0-.1 0-.1-.1.6-2 1.2-4 1.9-6C3.3 8.4 1.7 7.2 0 5.9h6C6.7 3.9 7.3 2 8 0h.1c.7 1.9 1.3 3.9 2 5.9H16V6c-1.6 1.3-3.2 2.5-4.9 3.8.6 1.9 1.3 3.9 1.8 6 .1-.1 0 0 0 0z"></path>';
					break;

				case 'readonly' :

					$return_value = '<path d="M12 8v-3.1c0-2.2-1.6-3.9-3.8-3.9h-0.3c-2.1 0-3.9 1.7-3.9 3.9v3.1h-1l0.1 5c0 0-0.1 3 4.9 3s5-3 5-3v-5h-1zM9 14h-1v-2c-0.6 0-1-0.4-1-1s0.4-1 1-1 1 0.4 1 1v3zM10 8h-4v-3.1c0-1.1 0.9-1.9 1.9-1.9h0.3c1 0 1.8 0.8 1.8 1.9v3.1z"></path>';
					break;

				case 'recaptcha' :

					$return_value = '<path d="M15.9918286,7.6696 L15.9918286,1.19512381 L14.2019048,2.98504762 C12.9340679,1.4331632 11.0942831,0.36651433 9.00406542,0.107566742 L9.00625984,4.27593888 C9.94543519,4.53172702 10.7423473,5.13244509 11.2527238,5.93344381 L9.1759619,8.01020571 C11.8064381,7.9998819 14.7780571,7.99382476 15.9997714,8.01153905 C15.999619,7.89727238 15.9969714,7.7831619 15.9918286,7.6696 Z"></path><path d="M7.62312381,0.0550285714 L1.14864762,0.0550285714 L2.93857143,1.84495238 C1.39662338,3.10467157 0.333717053,4.92903803 0.0661637514,7.00267304 L4.23979134,7.00341189 C4.50117426,6.08039453 5.09656172,5.29776041 5.88696762,4.79413333 L7.96372952,6.87089524 C7.95340571,4.24041905 7.94732952,1.2688 7.96506286,0.0470857143 C7.85079619,0.0472380952 7.73668571,0.0498857143 7.62312381,0.0550285714 Z"></path><path d="M0.000380952381,8.03447619 C0.000761904762,8.14920381 0.00340952381,8.26331429 0.00855238095,8.37687619 L0.00855238095,14.8513524 L1.79847619,13.0614286 C3.26342857,14.8545905 5.492,15.9999048 7.98819048,15.9999048 C10.5859048,15.9999048 12.8937143,14.7599619 14.3525714,12.8397143 L11.4186667,9.87495238 C11.1311429,10.4067048 10.7226857,10.8634286 10.2301905,11.208381 C9.71798095,11.6080952 8.99222857,11.9349143 7.98828571,11.9349143 C7.8670019,11.9349143 7.77339048,11.9207429 7.70460952,11.8940419 C6.46072381,11.7958648 5.38251429,11.1093943 4.74765714,10.1130324 L6.82441905,8.03627048 C4.19394286,8.04659429 1.22232381,8.05265143 0.00060952381,8.03493714 L8.19047619,11.2380952"></path>';
					break;

				case 'redo' :

					$return_value = '<path d="M16 7v-4l-1.1 1.1c-1.3-2.5-3.9-4.1-6.9-4.1-4.4 0-8 3.6-8 8s3.6 8 8 8c2.4 0 4.6-1.1 6-2.8l-1.5-1.3c-1.1 1.3-2.7 2.1-4.5 2.1-3.3 0-6-2.7-6-6s2.7-6 6-6c2.4 0 4.5 1.5 5.5 3.5l-1.5 1.5h4z"></path><text class="count" font-size="7" line-spacing="7" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"></text>';
					break;

				case 'reload' :

					$return_value = '<path d="M2.6,5.6 C3.5,3.5 5.6,2 8,2 C11,2 13.4,4.2 13.9,7 L15.9,7 C15.4,3.1 12.1,0 8,0 C5,0 2.4,1.6 1.1,4.1 L-8.8817842e-16,3 L-8.8817842e-16,7 L4,7 L2.6,5.6 L2.6,5.6 Z" id="Shape" transform="translate(7.950000, 3.500000) scale(-1, 1) translate(-7.950000, -3.500000) "></path><path d="M16,9 L11.9,9 L13.4,10.4 C12.5,12.5 10.4,14 7.9,14 C5,14 2.5,11.8 2,9 L0,9 C0.5,12.9 3.9,16 7.9,16 C10.9,16 13.5,14.3 14.9,11.9 L16,13 L16,9 Z" id="Shape" transform="translate(8.000000, 12.500000) scale(-1, 1) translate(-8.000000, -12.500000) "></path>';
					break;

				case 'reset' :

					$return_value = '<path d="M8 0c-3 0-5.6 1.6-6.9 4.1l-1.1-1.1v4h4l-1.5-1.5c1-2 3.1-3.5 5.5-3.5 3.3 0 6 2.7 6 6s-2.7 6-6 6c-1.8 0-3.4-0.8-4.5-2.1l-1.5 1.3c1.4 1.7 3.6 2.8 6 2.8 4.4 0 8-3.6 8-8s-3.6-8-8-8z"></path>';
					break;

				case 'save' :

					$return_value = '<path d="M15.791849,4.41655721 C15.6529844,4.08336982 15.4862083,3.8193958 15.2916665,3.625 L12.3749634,0.708260362 C12.1806771,0.513974022 11.916703,0.347234384 11.5833697,0.208260362 C11.2502188,0.0694322825 10.9445781,0 10.666849,0 L1.00003637,0 C0.722343724,0 0.486171803,0.0971614127 0.291703035,0.291630181 C0.0972342664,0.485989492 0.000109339408,0.722124927 0.000109339408,0.999963514 L0.000109339408,15.0002189 C0.000109339408,15.2781305 0.0972342664,15.5142659 0.291703035,15.7086617 C0.486171803,15.902948 0.722343724,16.0002189 1.00003637,16.0002189 L15.0002553,16.0002189 C15.2782033,16.0002189 15.5143023,15.902948 15.7086981,15.7086617 C15.9029844,15.5142659 16.0001093,15.2781305 16.0001093,15.0002189 L16.0001093,5.3334063 C16.0001093,5.05553123 15.9307135,4.75 15.791849,4.41655721 Z M6.66684898,1.66655721 C6.66684898,1.57629159 6.69986853,1.49832166 6.76587116,1.43220957 C6.83180082,1.36638938 6.90995318,1.3334063 7.0002188,1.3334063 L9.00032825,1.3334063 C9.09037496,1.3334063 9.16849083,1.3663164 9.23445698,1.43220957 C9.30060554,1.49832166 9.33358862,1.57629159 9.33358862,1.66655721 L9.33358862,4.99996351 C9.33358862,5.09037507 9.30038663,5.16845447 9.23445698,5.23445709 C9.16849083,5.30024081 9.09037496,5.33326036 9.00032825,5.33326036 L7.0002188,5.33326036 C6.90995318,5.33326036 6.83176433,5.30035026 6.76587116,5.23445709 C6.69986853,5.16834501 6.66684898,5.09037507 6.66684898,4.99996351 L6.66684898,1.66655721 Z M12.0003647,14.6669221 L4.00003637,14.6669221 L4.00003637,10.6667761 L12.0003647,10.6667761 L12.0003647,14.6669221 Z M14.6672503,14.6669221 L13.3336251,14.6669221 L13.3333697,14.6669221 L13.3333697,10.3334063 C13.3333697,10.0554947 13.2362083,9.81950525 13.0418125,9.62496351 C12.8474167,9.43056772 12.6112813,9.33329685 12.3336251,9.33329685 L3.66673952,9.33329685 C3.38893742,9.33329685 3.1527655,9.43056772 2.95829673,9.62496351 C2.76393742,9.81935931 2.66670303,10.0554947 2.66670303,10.3334063 L2.66670303,14.6669221 L1.33333322,14.6669221 L1.33333322,1.33326036 L2.66666655,1.33326036 L2.66666655,5.66670315 C2.66666655,5.94454174 2.76379148,6.18056772 2.95826024,6.37503649 C3.15272901,6.5693958 3.38890093,6.66666667 3.66670303,6.66666667 L9.66699492,6.66666667 C9.94465108,6.66666667 10.1810419,6.5693958 10.3751823,6.37503649 C10.5694687,6.18067717 10.666849,5.94454174 10.666849,5.66670315 L10.666849,1.33326036 C10.7709792,1.33326036 10.9063046,1.36792177 11.0731537,1.43735406 C11.2399663,1.50674985 11.3579611,1.57618214 11.4273933,1.64561442 L14.3547138,4.57286194 C14.4241096,4.64229422 14.4935784,4.76222271 14.5629742,4.93228255 C14.6326254,5.10248832 14.6672138,5.23620841 14.6672138,5.3334063 L14.6672138,14.6669221 L14.6672503,14.6669221 Z"></path>';
					break;

				case 'search' :

					$return_value = '<path d="M10.7 1.8C8.3-.6 4.3-.6 1.8 1.8c-2.4 2.4-2.4 6.4 0 8.9 2.2 2.2 5.2 2.6 7.7.9.1.2.5.3.7.5l3.6 3.6c.5.5 1.4.5 1.9 0s.5-1.4 0-1.9l-3.6-3.6c-.2-.2-.2-.6-.5-.6 1.7-2.5 1.3-5.6-.9-7.8zM9.6 9.6c-1.8 1.8-4.8 1.8-6.6 0C1.1 7.7 1.1 4.8 3 3c1.8-1.8 4.8-1.8 6.6 0 1.8 1.8 1.8 4.7 0 6.6z"/>';
					break;

				case 'section' :

					$return_value = '<path d="M0 1.8h1.8V0C.8 0 0 .8 0 1.8zm0 7.1h1.8V7.1H0v1.8zM3.6 16h1.8v-1.8H3.6V16zM0 5.3h1.8V3.6H0v1.7zM8.9 0H7.1v1.8h1.8V0zm5.3 0v1.8H16c0-1-.8-1.8-1.8-1.8zM1.8 16v-1.8H0c0 1 .8 1.8 1.8 1.8zM0 12.4h1.8v-1.8H0v1.8zM5.3 0H3.6v1.8h1.8V0zm1.8 16h1.8v-1.8H7.1V16zm7.1-7.1H16V7.1h-1.8v1.8zm0 7.1c1 0 1.8-.8 1.8-1.8h-1.8V16zm0-10.7H16V3.6h-1.8v1.7zm0 7.1H16v-1.8h-1.8v1.8zM10.7 16h1.8v-1.8h-1.8V16zm0-14.2h1.8V0h-1.8v1.8z"/>';
					break;

				case 'section-icons' :

					$return_value = '<path d="M11.5 16c-1.2 0-2.3-.5-3.2-1.3S7 12.7 7 11.5s.5-2.3 1.3-3.2 2-1.3 3.2-1.3 2.3.5 3.2 1.3 1.3 2 1.3 3.2-.5 2.3-1.3 3.2-2 1.3-3.2 1.3zm0-8.3c-2.1 0-3.8 1.7-3.8 3.8s1.7 3.8 3.8 3.8 3.8-1.7 3.8-3.8-1.7-3.8-3.8-3.8zm1.9 3.4H9.6v.7h3.9v-.7zM7.7 1.3C6.8.5 5.7 0 4.5 0S2.2.5 1.3 1.3 0 3.3 0 4.5s.5 2.3 1.3 3.2S3.3 9 4.5 9s2.3-.5 3.2-1.3S9 5.7 9 4.5s-.5-2.3-1.3-3.2zm-3.2 7C2.4 8.3.7 6.6.7 4.5S2.4.7 4.5.7s3.8 1.7 3.8 3.8-1.7 3.8-3.8 3.8zm.4-4.2h1.6v.7H4.9v1.6h-.8V4.9H2.6v-.8h1.6V2.6h.7v1.5z"/>';
					break;

				case 'select' :

					$return_value = '<path d="M15 4h-14c-0.6 0-1 0.4-1 1v6c0 0.6 0.4 1 1 1h14c0.6 0 1-0.4 1-1v-6c0-0.6-0.4-1-1-1zM10 11h-9v-6h9v6zM13 8.4l-2-1.4h4l-2 1.4z"></path>';
					break;

				case 'settings' :

					$return_value = '<path d="M16 9v-2l-1.7-0.6c-0.2-0.6-0.4-1.2-0.7-1.8l0.8-1.6-1.4-1.4-1.6 0.8c-0.5-0.3-1.1-0.6-1.8-0.7l-0.6-1.7h-2l-0.6 1.7c-0.6 0.2-1.2 0.4-1.7 0.7l-1.6-0.8-1.5 1.5 0.8 1.6c-0.3 0.5-0.5 1.1-0.7 1.7l-1.7 0.6v2l1.7 0.6c0.2 0.6 0.4 1.2 0.7 1.8l-0.8 1.6 1.4 1.4 1.6-0.8c0.5 0.3 1.1 0.6 1.8 0.7l0.6 1.7h2l0.6-1.7c0.6-0.2 1.2-0.4 1.8-0.7l1.6 0.8 1.4-1.4-0.8-1.6c0.3-0.5 0.6-1.1 0.7-1.8l1.7-0.6zM8 12c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"></path><path d="M10.6 7.9c0 1.381-1.119 2.5-2.5 2.5s-2.5-1.119-2.5-2.5c0-1.381 1.119-2.5 2.5-2.5s2.5 1.119 2.5 2.5z"></path>';
					break;

				case 'signature' :

					$return_value = '<path d="M13.3 3.9l-.6-.2a1 1 0 00-.6.2c-1 .8-1.7 1.8-2.1 3-.3.6-.4 1.2-.3 1.7.9-.3 1.8-.7 2.5-1.3.8-.6 1.3-1.4 1.5-2.3v-.6l-.4-.5zM0 12.4h15.6v1.2H0v-1.2zM2.1 8l1.3-1.3.8.8-1.3 1.3 1.3 1.3-.8.8-1.3-1.3-1.3 1.3-.8-.8 1.3-1.3L0 7.5l.8-.8L2.1 8zm13.6 2.8v.4h-1.2l-.1-.7c-.3-.2-.9-.1-1.8.2l-.4.1c-.6.2-1.2.2-1.8.1-.6-.1-1.1-.5-1.5-1-.9.2-2 .2-3.5.2V8.9l3.1-.1c-.1-.7 0-1.5.2-2.3.3-.8.7-1.5 1.2-2.2.5-.7 1.1-1.2 1.7-1.5.8-.5 1.6-.4 2.4.2.4.3.7.8.9 1.4.1.3 0 .6-.1 1s-.3.9-.6 1.3c-.3.5-.7.9-1.1 1.2-.8.7-1.8 1.2-2.8 1.6.5.3 1.2.3 1.8.1l1-.3c.7-.1 1.2-.1 1.6 0 .5.2.7.4.8.8l.2.7z"/>';
					break;

				case 'sort' :

					$return_value = '<path d="M11 7h-6l3-4z"></path><path d="M5 9h6l-3 4z"></path>';
					break;

				case 'spacer' :

					$return_value = '<path d="M7 7h1v1h-1v-1z"></path><path d="M5 7h1v1h-1v-1z"></path><path d="M3 7h1v1h-1v-1z"></path><path d="M1 7h1v1h-1v-1z"></path><path d="M6 6h1v1h-1v-1z"></path><path d="M4 6h1v1h-1v-1z"></path><path d="M2 6h1v1h-1v-1z"></path><path d="M0 6h1v1h-1v-1z"></path><path d="M7 5h1v1h-1v-1z"></path><path d="M5 5h1v1h-1v-1z"></path><path d="M3 5h1v1h-1v-1z"></path><path d="M1 5h1v1h-1v-1z"></path><path d="M6 4h1v1h-1v-1z"></path><path d="M4 4h1v1h-1v-1z"></path><path d="M2 4h1v1h-1v-1z"></path><path d="M0 4h1v1h-1v-1z"></path><path d="M7 3h1v1h-1v-1z"></path><path d="M5 3h1v1h-1v-1z"></path><path d="M3 3h1v1h-1v-1z"></path><path d="M1 3h1v1h-1v-1z"></path><path d="M6 2h1v1h-1v-1z"></path><path d="M4 2h1v1h-1v-1z"></path><path d="M2 2h1v1h-1v-1z"></path><path d="M0 2h1v1h-1v-1z"></path><path d="M7 1h1v1h-1v-1z"></path><path d="M5 1h1v1h-1v-1z"></path><path d="M3 1h1v1h-1v-1z"></path><path d="M1 1h1v1h-1v-1z"></path><path d="M6 0h1v1h-1v-1z"></path><path d="M4 0h1v1h-1v-1z"></path><path d="M2 0h1v1h-1v-1z"></path><path d="M0 0h1v1h-1v-1z"></path><path d="M15 7h1v1h-1v-1z"></path><path d="M13 7h1v1h-1v-1z"></path><path d="M11 7h1v1h-1v-1z"></path><path d="M9 7h1v1h-1v-1z"></path><path d="M14 6h1v1h-1v-1z"></path><path d="M12 6h1v1h-1v-1z"></path><path d="M10 6h1v1h-1v-1z"></path><path d="M8 6h1v1h-1v-1z"></path><path d="M15 5h1v1h-1v-1z"></path><path d="M13 5h1v1h-1v-1z"></path><path d="M11 5h1v1h-1v-1z"></path><path d="M9 5h1v1h-1v-1z"></path><path d="M14 4h1v1h-1v-1z"></path><path d="M12 4h1v1h-1v-1z"></path><path d="M10 4h1v1h-1v-1z"></path><path d="M8 4h1v1h-1v-1z"></path><path d="M15 3h1v1h-1v-1z"></path><path d="M13 3h1v1h-1v-1z"></path><path d="M11 3h1v1h-1v-1z"></path><path d="M9 3h1v1h-1v-1z"></path><path d="M14 2h1v1h-1v-1z"></path><path d="M12 2h1v1h-1v-1z"></path><path d="M10 2h1v1h-1v-1z"></path><path d="M8 2h1v1h-1v-1z"></path><path d="M15 1h1v1h-1v-1z"></path><path d="M13 1h1v1h-1v-1z"></path><path d="M11 1h1v1h-1v-1z"></path><path d="M9 1h1v1h-1v-1z"></path><path d="M14 0h1v1h-1v-1z"></path><path d="M12 0h1v1h-1v-1z"></path><path d="M10 0h1v1h-1v-1z"></path><path d="M8 0h1v1h-1v-1z"></path><path d="M7 15h1v1h-1v-1z"></path><path d="M5 15h1v1h-1v-1z"></path><path d="M3 15h1v1h-1v-1z"></path><path d="M1 15h1v1h-1v-1z"></path><path d="M6 14h1v1h-1v-1z"></path><path d="M4 14h1v1h-1v-1z"></path><path d="M2 14h1v1h-1v-1z"></path><path d="M0 14h1v1h-1v-1z"></path><path d="M7 13h1v1h-1v-1z"></path><path d="M5 13h1v1h-1v-1z"></path><path d="M3 13h1v1h-1v-1z"></path><path d="M1 13h1v1h-1v-1z"></path><path d="M6 12h1v1h-1v-1z"></path><path d="M4 12h1v1h-1v-1z"></path><path d="M2 12h1v1h-1v-1z"></path><path d="M0 12h1v1h-1v-1z"></path><path d="M7 11h1v1h-1v-1z"></path><path d="M5 11h1v1h-1v-1z"></path><path d="M3 11h1v1h-1v-1z"></path><path d="M1 11h1v1h-1v-1z"></path><path d="M6 10h1v1h-1v-1z"></path><path d="M4 10h1v1h-1v-1z"></path><path d="M2 10h1v1h-1v-1z"></path><path d="M0 10h1v1h-1v-1z"></path><path d="M7 9h1v1h-1v-1z"></path><path d="M5 9h1v1h-1v-1z"></path><path d="M3 9h1v1h-1v-1z"></path><path d="M1 9h1v1h-1v-1z"></path><path d="M6 8h1v1h-1v-1z"></path><path d="M4 8h1v1h-1v-1z"></path><path d="M2 8h1v1h-1v-1z"></path><path d="M0 8h1v1h-1v-1z"></path><path d="M15 15h1v1h-1v-1z"></path><path d="M13 15h1v1h-1v-1z"></path><path d="M11 15h1v1h-1v-1z"></path><path d="M9 15h1v1h-1v-1z"></path><path d="M14 14h1v1h-1v-1z"></path><path d="M12 14h1v1h-1v-1z"></path><path d="M10 14h1v1h-1v-1z"></path><path d="M8 14h1v1h-1v-1z"></path><path d="M15 13h1v1h-1v-1z"></path><path d="M13 13h1v1h-1v-1z"></path><path d="M11 13h1v1h-1v-1z"></path><path d="M9 13h1v1h-1v-1z"></path><path d="M14 12h1v1h-1v-1z"></path><path d="M12 12h1v1h-1v-1z"></path><path d="M10 12h1v1h-1v-1z"></path><path d="M8 12h1v1h-1v-1z"></path><path d="M15 11h1v1h-1v-1z"></path><path d="M13 11h1v1h-1v-1z"></path><path d="M11 11h1v1h-1v-1z"></path><path d="M9 11h1v1h-1v-1z"></path><path d="M14 10h1v1h-1v-1z"></path><path d="M12 10h1v1h-1v-1z"></path><path d="M10 10h1v1h-1v-1z"></path><path d="M8 10h1v1h-1v-1z"></path><path d="M15 9h1v1h-1v-1z"></path><path d="M13 9h1v1h-1v-1z"></path><path d="M11 9h1v1h-1v-1z"></path><path d="M9 9h1v1h-1v-1z"></path><path d="M14 8h1v1h-1v-1z"></path><path d="M12 8h1v1h-1v-1z"></path><path d="M10 8h1v1h-1v-1z"></path><path d="M8 8h1v1h-1v-1z"></path>';
					break;

				case 'submit' :

					$return_value = '<path d="M16 7.9l-6-4.9v3c-0.5 0-1.1 0-2 0-8 0-8 8-8 8s1-4 7.8-4c1.1 0 1.8 0 2.2 0v2.9l6-5z"></path>';
					break;

				case 'table' :

					$return_value = '<path d="M0 1v15h16v-15h-16zM5 15h-4v-2h4v2zM5 12h-4v-2h4v2zM5 9h-4v-2h4v2zM5 6h-4v-2h4v2zM10 15h-4v-2h4v2zM10 12h-4v-2h4v2zM10 9h-4v-2h4v2zM10 6h-4v-2h4v2zM15 15h-4v-2h4v2zM15 12h-4v-2h4v2zM15 9h-4v-2h4v2zM15 6h-4v-2h4v2z"></path>';
					break;

				case 'tel' :

					$return_value = '<path d="M12.2 10c-1.1-0.1-1.7 1.4-2.5 1.8-1.3 0.7-3.7-1.8-3.7-1.8s-2.5-2.4-1.9-3.7c0.5-0.8 2-1.4 1.9-2.5-0.1-1-2.3-4.6-3.4-3.6-2.4 2.2-2.6 3.1-2.6 4.9-0.1 3.1 3.9 7 3.9 7 0.4 0.4 3.9 4 7 3.9 1.8 0 2.7-0.2 4.9-2.6 1-1.1-2.5-3.3-3.6-3.4z"></path>';
					break;

				case 'text' :

					$return_value = '<path d="M16 5c0-0.6-0.4-1-1-1h-14c-0.6 0-1 0.4-1 1v6c0 0.6 0.4 1 1 1h14c0.6 0 1-0.4 1-1v-6zM15 11h-14v-6h14v6z"></path><path d="M2 6h1v4h-1v-4z"></path>';
					break;

				case 'textarea' :

					$return_value = '<path d="M2 2h1v4h-1v-4z"></path><path d="M1 0c-0.6 0-1 0.4-1 1v14c0 0.6 0.4 1 1 1h15v-16h-15zM13 15h-12v-14h12v14zM15 15v0h-1v-1h1v1zM15 13h-1v-10h1v10zM15 2h-1v-1h1v1z"></path>';
					break;

				case 'texteditor' :

					$return_value = '<path d="M16 4c0 0 0-1-1-2s-1.9-1-1.9-1l-1.1 1.1v-2.1h-12v16h12v-8l4-4zM6.3 11.4l-0.6-0.6 0.3-1.1 1.5 1.5-1.2 0.2zM7.2 9.5l-0.6-0.6 5.2-5.2c0.2 0.1 0.4 0.3 0.6 0.5zM14.1 2.5l-0.9 1c-0.2-0.2-0.4-0.3-0.6-0.5l0.9-0.9c0.1 0.1 0.3 0.2 0.6 0.4zM11 15h-10v-14h10v2.1l-5.9 5.9-1.1 4.1 4.1-1.1 2.9-3v6z"></path>';
					break;

				case 'tools' :

					$return_value = '<path d="M10.3 8.2l-0.9 0.9 0.9 0.9-1.2 1.2 4.3 4.3c0.6 0.6 1.5 0.6 2.1 0s0.6-1.5 0-2.1l-5.2-5.2zM14.2 15c-0.4 0-0.8-0.3-0.8-0.8 0-0.4 0.3-0.8 0.8-0.8s0.8 0.3 0.8 0.8c0 0.5-0.3 0.8-0.8 0.8z"></path><path d="M3.6 8l0.9-0.6 1.5-1.7 0.9 0.9 0.9-0.9-0.1-0.1c0.2-0.5 0.3-1 0.3-1.6 0-2.2-1.8-4-4-4-0.6 0-1.1 0.1-1.6 0.3l2.9 2.9-2.1 2.1-2.9-2.9c-0.2 0.5-0.3 1-0.3 1.6 0 2.1 1.6 3.7 3.6 4z"></path><path d="M8 10.8l0.9-0.8-0.9-0.9 5.7-5.7 1.2-0.4 1.1-2.2-0.7-0.7-2.3 1-0.5 1.2-5.6 5.7-0.9-0.9-0.8 0.9c0 0 0.8 0.6-0.1 1.5-0.5 0.5-1.3-0.1-2.8 1.4-0.5 0.5-2.1 2.1-2.1 2.1s-0.6 1 0.6 2.2 2.2 0.6 2.2 0.6 1.6-1.6 2.1-2.1c1.4-1.4 0.9-2.3 1.3-2.7 0.9-0.9 1.6-0.2 1.6-0.2zM4.9 10.4l0.7 0.7-3.8 3.8-0.7-0.7z"></path>';
					break;

				case 'undo' :

					$return_value = '<path d="M8 0c-3 0-5.6 1.6-6.9 4.1l-1.1-1.1v4h4l-1.5-1.5c1-2 3.1-3.5 5.5-3.5 3.3 0 6 2.7 6 6s-2.7 6-6 6c-1.8 0-3.4-0.8-4.5-2.1l-1.5 1.3c1.4 1.7 3.6 2.8 6 2.8 4.4 0 8-3.6 8-8s-3.6-8-8-8z"></path><text class="count" font-size="7" line-spacing="7" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"></text>';
					break;

				case 'up' :

					$return_value = '<path d="M1 13h14L8 3 1 13z"/>';
					break;

				case 'upload' :

					$return_value = '<path d="M15,7h-2l-1,1h2v7H2V8h2L3,7H1v9h14V7z"/><path d="M8,10l4-5h-2V0L6,0v5H4L8,10z"/>';
					break;

				case 'url' :

					$return_value = '<path d="M14.9 1.1c-1.4-1.4-3.7-1.4-5.1 0l-4.4 4.3c-1.4 1.5-1.4 3.7 0 5.2 0.1 0.1 0.3 0.2 0.4 0.3l1.5-1.5c-0.1-0.1-0.3-0.2-0.4-0.3-0.6-0.6-0.6-1.6 0-2.2l4.4-4.4c0.6-0.6 1.6-0.6 2.2 0s0.6 1.6 0 2.2l-1.3 1.3c0.4 0.8 0.5 1.7 0.4 2.5l2.3-2.3c1.5-1.4 1.5-3.7 0-5.1z"></path><path d="M10.2 5.1l-1.5 1.5c0 0 0.3 0.2 0.4 0.3 0.6 0.6 0.6 1.6 0 2.2l-4.4 4.4c-0.6 0.6-1.6 0.6-2.2 0s-0.6-1.6 0-2.2l1.3-1.3c-0.4-0.8-0.1-1.3-0.4-2.5l-2.3 2.3c-1.4 1.4-1.4 3.7 0 5.1s3.7 1.4 5.1 0l4.4-4.4c1.4-1.4 1.4-3.7 0-5.1-0.2-0.1-0.4-0.3-0.4-0.3z"></path>';
					break;

				case 'visible' :

					$return_value = '<path d="M8 3.9c-6.7 0-8 5.1-8 5.1s2.2 4.1 7.9 4.1 8.1-4 8.1-4-1.3-5.2-8-5.2zM5.3 5.4c0.5-0.3 1.3-0.3 1.3-0.3s-0.5 0.9-0.5 1.6c0 0.7 0.2 1.1 0.2 1.1l-1.1 0.2c0 0-0.3-0.5-0.3-1.2 0-0.8 0.4-1.4 0.4-1.4zM7.9 12.1c-4.1 0-6.2-2.3-6.8-3.2 0.3-0.7 1.1-2.2 3.1-3.2-0.1 0.4-0.2 0.8-0.2 1.3 0 2.2 1.8 4 4 4s4-1.8 4-4c0-0.5-0.1-0.9-0.2-1.3 2 0.9 2.8 2.5 3.1 3.2-0.7 0.9-2.8 3.2-7 3.2z"></path>';
					break;

				case 'warning' :

					$return_value = '<path d="M8 1l-8 14h16l-8-14zM8 13c-0.6 0-1-0.4-1-1s0.4-1 1-1 1 0.4 1 1c0 0.6-0.4 1-1 1zM7 10v-4h2v4h-2z"></path>';
					break;

				case 'wizard' :

					$return_value = '<path d="M0 5h3v1h-3v-1z"></path><path d="M5 0h1v3h-1v-3z"></path><path d="M6 11h-1v-2.5l1 1z"></path><path d="M11 6h-1.5l-1-1h2.5z"></path><path d="M3.131 7.161l0.707 0.707-2.97 2.97-0.707-0.707 2.97-2.97z"></path><path d="M10.131 0.161l0.707 0.707-2.97 2.97-0.707-0.707 2.97-2.97z"></path><path d="M0.836 0.199l3.465 3.465-0.707 0.707-3.465-3.465 0.707-0.707z"></path><path d="M6.1 4.1l-2.1 2 9.8 9.9 2.2-2.1-9.9-9.8zM6.1 5.5l2.4 2.5-0.6 0.6-2.5-2.5 0.7-0.6z"></path>';
					break;

				case 'woo' :

					$return_value = '<path d="M2 0h12c.6 0 1 .2 1.4.6.4.3.6.8.6 1.3v10.2c0 .5-.2 1-.6 1.4-.4.4-.9.6-1.4.6H9.5V16l-2-1.9H2c-.6 0-1-.2-1.4-.6-.4-.4-.6-.8-.6-1.4V1.9C0 1.4.2.9.6.5 1 .2 1.4 0 2 0zm5.6 4.7c0-.2 0-.3-.1-.3-.1-.1-.2-.1-.3-.1-.2 0-.4.2-.5.5-.1.3-.2.7-.3 1-.2.4-.2.7-.3 1.1v.6c-.1-.3-.2-.5-.2-.7-.1-.3-.1-.5-.2-.6 0-.1-.1-.3-.2-.6 0-.3-.1-.4-.3-.4-.1 0-.2.1-.4.4-.2.3-.4.5-.5.9-.2.3-.3.6-.4.9-.2.2-.2.4-.3.4v-.1-.1c-.1-.5-.2-1-.2-1.5-.1-.5-.1-1-.2-1.4 0-.1-.1-.2-.2-.2s-.2-.1-.2-.1c-.2 0-.3.1-.4.2 0 .1-.1.3-.1.4 0 .1.1.4.2 1s.2 1.1.3 1.6c0 .1.1.4.2 1s.3.9.5.9c.1 0 .3-.1.5-.4.1-.1.3-.4.4-.7.2-.3.3-.6.4-.9l.2-.4s.1.2.1.5l.3.9c.2.2.4.5.6.8.2.3.4.4.5.4.1 0 .2 0 .3-.1 0-.1.1-.2.1-.3V8.1c0-.3 0-.6.1-1s.2-.8.2-1.1c.1-.3.2-.6.2-.9l.2-.4zm2.6.7c-.2-.4-.6-.6-1-.6s-.8.1-1 .4c-.3.3-.5.8-.6 1.3V7.8c0 .1.1.3.2.5.2.4.4.5.7.6 0 .1.1.1.2.1h.2c.6 0 1-.3 1.3-.9.3-.6.4-1.1.4-1.5-.1-.4-.2-.8-.4-1.2zm-.6 1.3c0 .1-.1.4-.2.8s-.3.6-.5.6-.3-.1-.4-.4c-.1-.4-.1-.5-.1-.6 0-.1.1-.3.2-.7.1-.4.3-.6.6-.6.2 0 .3.1.4.4v.5zm3.9-1.3c-.2-.4-.6-.6-1-.6s-.8.1-1 .4c-.3.3-.5.8-.6 1.3 0 .3-.1.5-.1.7 0 .2 0 .4.1.6 0 .1.1.3.2.5s.3.4.6.5c.1 0 .1 0 .2.1h.2c.6 0 1-.3 1.3-.9.3-.6.4-1.1.4-1.5 0-.3-.1-.7-.3-1.1zm-.8 2.1c-.1.4-.3.6-.6.6-.2 0-.3-.1-.4-.4 0-.3-.1-.5-.1-.5 0-.1 0-.3.1-.7.1-.4.3-.6.6-.6.2 0 .3.1.4.4 0 .2.1.4.1.4.1.1 0 .4-.1.8z"/><path fill="none" d="M174.6 238.5h-8.5 8.5z"/>';
					break;

				default :

					$return_value = '<path d="M9 11h-3c0-3 1.6-4 2.7-4.6 0.4-0.2 0.7-0.4 0.9-0.6 0.5-0.5 0.3-1.2 0.2-1.4-0.3-0.7-1-1.4-2.3-1.4-2.1 0-2.5 1.9-2.5 2.3l-3-0.4c0.2-1.7 1.7-4.9 5.5-4.9 2.3 0 4.3 1.3 5.1 3.2 0.7 1.7 0.4 3.5-0.8 4.7-0.5 0.5-1.1 0.8-1.6 1.1-0.9 0.5-1.2 1-1.2 2z"></path><path d="M9.5 14c0 1.105-0.895 2-2 2s-2-0.895-2-2c0-1.105 0.895-2 2-2s2 0.895 2 2z"></path>';
			}

			// Apply filter
			$return_value = apply_filters('wsf_config_icon_16_svg', $return_value, $id);

			return '<svg height="16" width="16" viewBox="0 0 16 16">' . $return_value . '</svg>';
		}

		// Configuration - File Types
		public static function get_file_types() {

			// Check cache
			if(self::$file_types !== false) { return self::$file_types; }

			$file_types = array(

				'default'						=>	array('icon' => 'file-default'),

				'application/x-javascript'		=>	array('icon' => 'file-code'),
				'application/json'				=>	array('icon' => 'file-code'),
				'application/xml'				=>	array('icon' => 'file-code'),
				'text/css'						=>	array('icon' => 'file-code'),
				'text/html'						=>	array('icon' => 'file-code'),
				'application/xhtml+xml'			=>	array('icon' => 'file-code'),

				'application/vnd.ms-fontobject' =>	array('icon' => 'file-font'),
				'font/otf'						=>	array('icon' => 'file-font'),
				'font/ttf'						=>	array('icon' => 'file-font'),
				'font/woff'						=>	array('icon' => 'file-font'),
				'font/woff2'					=>	array('icon' => 'file-font'),

				'application/x-troff-msvideo'	=>	array('icon' => 'file-movie'),
				'video/avi'						=>	array('icon' => 'file-movie'),
				'video/mpeg'					=>	array('icon' => 'file-movie'),
				'video/msvideo'					=>	array('icon' => 'file-movie'),
				'video/ogg'						=>	array('icon' => 'file-movie'),
				'video/x-msvideo'				=>	array('icon' => 'file-movie'),
				'video/webm'					=>	array('icon' => 'file-movie'),

				'image/bmp'						=>	array('icon' => 'file-picture'),
				'image/gif'						=>	array('icon' => 'file-picture'),
				'image/jpeg'					=>	array('icon' => 'file-picture'),
				'image/heic'					=>	array('icon' => 'file-picture'),
				'image/heif'					=>	array('icon' => 'file-picture'),
				'image/png'						=>	array('icon' => 'file-picture'),
				'image/svg+xml'					=>	array('icon' => 'file-picture'),
				'image/tiff'					=>	array('icon' => 'file-picture'),

				'application/vnd.ms-powerpoint'	=>	array('icon' => 'file-presentation'),
				'application/vnd.oasis.opendocument.presentation' =>	array('icon' => 'file-presentation'),
				'application/vnd.openxmlformats-officedocument.presentationml.presentation' =>	array('icon' => 'file-presentation'),

				'audio/aac'						=>	array('icon' => 'file-sound'),
				'audio/aiff'					=>	array('icon' => 'file-sound'),
				'audio/midi'					=>	array('icon' => 'file-sound'),
				'audio/mpeg'					=>	array('icon' => 'file-sound'),
				'audio/mpeg3'					=>	array('icon' => 'file-sound'),
				'audio/ogg'						=>	array('icon' => 'file-sound'),
				'audio/x-mpeg-3'				=>	array('icon' => 'file-sound'),
				'audio/x-wav'					=>	array('icon' => 'file-sound'),
				'audio/webm'					=>	array('icon' => 'file-sound'),

				'application/vnd.ms-excel'		=>	array('icon' => 'file-table'),
				'application/vnd.oasis.opendocument.spreadsheet' =>	array('icon' => 'file-table'),
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' =>	array('icon' => 'file-table'),
				'text/csv'						=>	array('icon' => 'file-table'),

				'application/msword'			=>	array('icon' => 'file-text'),
				'application/pdf'				=>	array('icon' => 'file-text'),
				'application/rtf'				=>	array('icon' => 'file-text'),
				'application/vnd.oasis.opendocument.text' =>	array('icon' => 'file-text'),
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document'	=>	array('icon' => 'file-text'),
				'text/plain'					=>	array('icon' => 'file-text'),

				'application/octet-stream'		=>	array('icon' => 'file-zip'),
				'application/x-rar-compressed' 	=>	array('icon' => 'file-zip'),
				'application/x-tar'				=>	array('icon' => 'file-zip'),
				'application/zip'				=>	array('icon' => 'file-zip')
			);

			// Apply filter
			$file_types = apply_filters('wsf_config_file_types', $file_types);

			// Cache
			self::$file_types = $file_types;

			return $file_types;
		}

		// Configuration - Frameworks
		public static function get_frameworks($public = true) {

			// Check cache
			if(isset(self::$frameworks[$public])) { return self::$frameworks[$public]; }

			$framework_foundation_init_js =	"if(typeof $(document).foundation === 'function') {

				// Abide
				if(typeof(Foundation.Abide) === 'function') {

					if($('[data-abide]').length) { Foundation.reInit($('[data-abide]')); }

				} else {

					if(typeof $('#form_canvas_selector')[0].ws_form_log_error === 'function') {
						$('#form_canvas_selector')[0].ws_form_log_error('error_framework_plugin', 'Abide', 'framework');
					}
				}

				// Tabs
				if(typeof(Foundation.Tabs) === 'function') {

					if($('[data-tabs]').length) { var wsf_foundation_tabs = new Foundation.Tabs($('[data-tabs]')); }

				} else {

					if(typeof($('#form_canvas_selector')[0].ws_form_log_error) === 'function') {

						$('#form_canvas_selector')[0].ws_form_log_error('error_framework_plugin', 'Tabs', 'framework');
					}
				}
			}";

			$frameworks = array(

				'icons'	=> array(

					'25'	=>	self::get_icon_24_svg('bp-25'),
					'50'	=>	self::get_icon_24_svg('bp-50'),
					'75'	=>	self::get_icon_24_svg('bp-75'),
					'100'	=>	self::get_icon_24_svg('bp-100'),
					'125'	=>	self::get_icon_24_svg('bp-125'),
					'150'	=>	self::get_icon_24_svg('bp-150')
				),

				'types' => array(

					'ws-form'		=> array(

						'name'						=>	__('WS Form', 'ws-form'),

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'minicolors_args' 			=>	array(

							'theme' 					=> 'ws-form'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'				=>	'wsf-#id-#size',
							'column_css_selector'		=>	'.wsf-#id-#size',
							'offset_class'				=>	'wsf-offset-#id-#offset',
							'offset_css_selector'		=>	'.wsf-offset-#id-#offset'
						),

						'breakpoints'				=>	array(

							25	=>	array(
								'id'					=>	'extra-small',
								'name'					=>	__('Extra Small', 'ws-form'),
								'admin_max_width'		=>	575,
								'column_size_default'	=>	'column_count'
							),

							50	=>	array(
								'id'				=>	'small',
								'name'				=>	__('Small', 'ws-form'),
								'min_width'			=>	576,
								'admin_max_width'	=>	767
							),

							75	=>	array(
								'id'				=>	'medium',
								'name'				=>	__('Medium', 'ws-form'),
								'min_width'			=>	768,
								'admin_max_width'	=>	991
							),

							100	=>	array(
								'id'				=>	'large',
								'name'				=>	__('Large', 'ws-form'),
								'min_width'			=>	992,
								'admin_max_width'	=>	1199
							),

							150	=>	array(
								'id'				=>	'extra-large',
								'name'				=>	__('Extra Large', 'ws-form'),
								'min_width'			=>	1200
							),
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
							),
						),

						'tabs' => array(

							'admin' => array(

								'mask_wrapper'		=>	'<ul class="wsf-group-tabs">#tabs</ul>',
								'mask_single'		=>	'<li class="wsf-group-tab" data-id="#data_id" title="#label"><a href="#href"><input type="text" value="#label" data-label="#data_id" readonly></a></li>'
							),

							'public' => array(

								'mask_wrapper'		=>	'<ul class="wsf-group-tabs" role="tablist">#tabs</ul>',
								'mask_single'		=>	'<li class="wsf-group-tab" data-id="#data_id"><a href="#href" role="tab">#label</a></li>',
								'activate_js'		=>	"$('#form .wsf-group-tabs .wsf-group-tab:eq(#index) a').click();",
								'event_js'			=>	'tab_show',
								'event_type_js'		=>	'tab',
								'class_disabled'	=>	'wsf-tab-disabled'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="wsf-alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'wsf-alert-success', 'text_class' => 'wsf-text-success'),
									'information'	=>	array('mask_wrapper_class' => 'wsf-alert-information', 'text_class' => 'wsf-text-information'),
									'warning'		=>	array('mask_wrapper_class' => 'wsf-alert-warning', 'text_class' => 'wsf-text-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'wsf-alert-danger', 'text_class' => 'wsf-text-danger')
								)
							)
						),

						'groups' => array(

							'admin' => array(

								// mask_wrapper is placed around all of the groups
								'mask_wrapper'	=>	'<div class="wsf-groups">#groups</div>',

								// mask_single is placed around each individual group
								'mask_single'	=>	'<div class="wsf-group" id="#id" data-id="#data_id" data-group-index="#data_group_index">#group</div>',
							),

							'public' => array(

								'mask_wrapper'	=>	'<div class="wsf-groups">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'wsf-group'
							)
						),

						'sections' => array(

							'admin' => array(

								'mask_wrapper' 	=> '<ul class="wsf-sections" id="#id" data-id="#data_id">#sections</ul>',
								'mask_single' 	=> sprintf('<li class="#class" id="#id" data-id="#data_id"><div class="wsf-section-inner">#label<div class="wsf-section-type">%s#section_id</div>#section</div></li>', __('Section', 'ws-form')),
								'mask_label' 	=> '<div class="wsf-section-label"><span class="wsf-section-repeatable">' . self::get_icon_16_svg('redo') . '</span><span class="wsf-section-hidden">' . self::get_icon_16_svg('hidden') . '</span><span class="wsf-section-disabled">' . self::get_icon_16_svg('disabled') . '</span><input type="text" value="#label" data-label="#data_id" readonly></div>',
								'class_single'	=> array('wsf-section')
							),

							'public' => array(

								'mask_wrapper'	=> '<div class="wsf-grid wsf-sections" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
								'class_single'	=> array('wsf-tile', 'wsf-section')
							)
						),

						'fields' => array(

							'admin' => array(

								'mask_wrapper' 	=> '<ul class="wsf-fields" id="#id" data-id="#data_id">#fields</ul>',
								'mask_single' 	=> '<li class="#class" id="#id" data-id="#data_id" data-type="#type"></li>',
								'mask_label' 	=> '<h4>#label</h4>',
								'class_single'	=> array('wsf-field-wrapper')
							),

							'public' => array(

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="wsf-grid wsf-fields">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="wsf-extra-small-#column_width_label wsf-tile wsf-label-wrapper">#label</div>',
									'mask_field_wrapper'			=>	'<div class="wsf-extra-small-#column_width_field wsf-tile">#field</div>',
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="wsf-grid wsf-fields">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="wsf-extra-small-#column_width_label wsf-tile wsf-label-wrapper">#label</div>',
									'mask_field_wrapper'			=>	'<div class="wsf-extra-small-#column_width_field wsf-tile">#field</div>',
								),

								'mask_wrapper' 			=> '#label<div class="wsf-grid wsf-fields" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

								// Required
								'mask_required_label'	=> ' <strong class="wsf-text-danger">*</strong>',

								// Help
								'mask_help'				=>	'<small id="#help_id" class="#help_class">#help#help_append</small>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class">#invalid_feedback</div>',

								// Classes - Default
								'class_single'					=> array('wsf-tile', 'wsf-field-wrapper'),
								'class_field'					=> array('wsf-field'),
								'class_field_label'				=> array('wsf-label'),
								'class_help'					=> array('wsf-help'),
								'class_invalid_feedback'		=> array('wsf-invalid-feedback'),
								'class_inline' 					=> array('wsf-inline'),
								'class_form_validated'			=> array('wsf-validated'),
								'class_orientation_wrapper'		=> array('wsf-grid'),
								'class_orientation_row'			=> array('wsf-tile'),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'wsf-middle',
									'bottom'	=>	'wsf-bottom'
								),
								'class_field_button_type'	=> array(

									'primary'		=>	'wsf-button-primary',
									'secondary'		=>	'wsf-button-secondary',
									'success'		=>	'wsf-button-success',
									'information'	=>	'wsf-button-information',
									'warning'		=>	'wsf-button-warning',
									'danger'		=>	'wsf-button-danger'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'wsf-alert-success',
									'information'	=>	'wsf-alert-information',
									'warning'		=>	'wsf-alert-warning',
									'danger'		=>	'wsf-alert-danger'
								),

								// Custom settings by field type
								'field_types'		=> array(

									'checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row_field'		=> array('wsf-field'),
										'class_row_field_label'	=> array('wsf-label'),
										'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_field'			=> array(),
										'class_row_field'		=> array('wsf-field'),
										'class_row_field_label'	=> array('wsf-label'),
										'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" data-label-required-id="#label_id" for="#row_id"#attributes>#radio_field_label</label>#invalid_feedback',
									),

									'spacer' 	=> array(
										'class_single'			=> array('wsf-tile'),
									),

									'price_checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row_field'		=> array('wsf-field'),
										'class_row_field_label'	=> array('wsf-label'),
										'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_field'			=> array(),
										'class_row_field'		=> array('wsf-field'),
										'class_row_field_label'	=> array('wsf-label'),
										'mask_group'			=> '<fieldset class="wsf-fieldset"#disabled>#group_label#group</fieldset>',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" data-label-required-id="#label_id" for="#row_id"#attributes>#radio_price_field_label</label>#invalid_feedback',
									),

									'signature' => array(

										'class_invalid_field'	=> array('wsf-invalid'),
										'class_valid_field'		=> array('wsf-valid'),
										'class_disabled'		=> array('wsf-disabled')
									),

									'recaptcha' => array(

										'class_invalid_field'	=> array('wsf-invalid'),
										'class_valid_field'		=> array('wsf-valid')
									),

									'submit' 	=> array(
										'class_field'						=> array('wsf-button'),
										'class_field_full_button'			=> array('wsf-button-full'),
										'class_field_button_type_fallback'	=> 'primary',
									),

									'clear' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'reset' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'tab_previous' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'tab_next' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'section_add' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'section_delete' 	=> array(
										'class_field'						=> array('wsf-button'),
										'class_field_full_button'			=> array('wsf-button-full'),
										'class_field_button_type_fallback'	=> 'danger',
									),

									'section_up' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'section_down' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'save' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'button' 	=> array(
										'class_field'				=> array('wsf-button'),
										'class_field_full_button'	=> array('wsf-button-full')
									),

									'message' 	=> array(
										'class_field'				=> array('wsf-alert')
									),

									'progress'	=> array(
										'class_field'		=> array('wsf-progress'),
										'class_complete'	=> array('wsf-progress-success'),
									)
								)
							)
						)
					),

					'bootstrap3'	=> array(

						'name'						=>	__('Bootstrap 3.x', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'bootstrap3.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'minicolors_args'			=>	array(

							'changeDelay' 	=> 200,
							'letterCase' 	=> 'uppercase',
							'theme' 		=> 'bootstrap'
						),

						'columns'					=>	array(

							'column_count' 				=> 	12,
							'column_class'				=>	'col-#id-#size',
							'column_css_selector'		=>	'.col-#id-#size',
							'offset_class'				=>	'col-#id-offset-#offset',
							'offset_css_selector'		=>	'.col-#id-offset-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 767px
							25	=>	array(
								'id'				=>	'xs',
								'name'				=>	__('Extra Small', 'ws-form'),
								'admin_max_width'	=>	767,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 991px
							75	=>	array(
								'id'				=>	'sm',
								'name'				=>	__('Small', 'ws-form'),
								'admin_max_width'	=>	991,
								'min_width'			=>	768
							),

							// Up to 1199px
							100	=>	array(
								'id'				=>	'md',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	1199,
								'min_width'			=>	992
							),

							// 1200+
							125	=>	array(
								'id'				=>	'large',
								'name'				=>	__('Large', 'ws-form'),
								'min_width'			=>	1200
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'		=>	'<ul class="nav nav-tabs" role="tablist">#tabs</ul>',
								'mask_single'		=>	'<li><a class="nav-link" href="#href" data-toggle="tab" role="tab">#label</a></li>',
								'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').tab('show');",
								'event_js'			=>	'shown.bs.tab',
								'event_type_js'		=>	'tab',
								'class_disabled'	=>	'disabled'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tab-pane',
								'class_active'	=> 'active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
								'class_single'	=> array('col')
							)
						),

						'fields' => array(

							'public' => array(

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-xs-#column_width_label control-label text-right">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-xs-#column_width_field">#field</div>',
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-xs-#column_width_label control-label">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-xs-#column_width_field">#field</div>',
								),

								'mask_wrapper' 		=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

								// Required
								'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

								// Help
								'mask_help'			=>	'<span id="#help_id" class="#help_class">#help#help_append</span>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class">#invalid_feedback</div>',

								// Classes - Default
								'class_single'				=> array('form-group'),
								'class_field'				=> array('form-control'),
								'class_field_label'			=> array(),
								'class_help'				=> array('help-block'),
								'class_invalid_feedback'	=> array('help-block', 'wsf-invalid-feedback'),
								'class_inline' 				=> array('form-inline'),
								'class_form_validated'		=> array('wsf-validated'),
								'class_orientation_wrapper'	=> array('row'),
								'class_orientation_row'		=> array(),
								'class_field_button_type'	=> array(

									'default'		=>	'btn-default',
									'primary'		=>	'btn-primary',
									'success'		=>	'btn-success',
									'information'	=>	'btn-info',
									'warning'		=>	'btn-warning',
									'danger'		=>	'btn-danger'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'alert-success',
									'information'	=>	'alert-info',
									'warning'		=>	'alert-warning',
									'danger'		=>	'alert-danger'
								),

								// Classes - Custom by field type
								'field_types'		=> array(

									'checkbox' 	=> array(

										'class_field'			=> array(),
										'mask_row_label'		=> '<label id="#label_row_id" for="#row_id"#attributes>#row_field#checkbox_field_label#required#invalid_feedback</label>',
										'class_row'				=> array('checkbox'),
										'class_row_disabled'	=> array('disabled'),
										'class_inline' 			=> array('checkbox-inline'),
									),

									'radio' 	=> array(

										'class_field'			=> array(),
										'mask_row_label'		=> '<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#row_field#radio_field_label#required#invalid_feedback</label>',
										'class_row'				=> array('radio'),
										'class_row_disabled'	=> array('disabled'),
										'class_inline' 			=> array('radio-inline'),
									),

									'spacer' 	=> array(
										'class_single'			=> array(),
									),
									'section_icons' 	=> array(

										'class_field'			=> array(),
									),

									'price_checkbox' 	=> array(

										'class_field'			=> array(),
										'mask_row_label'		=> '<label id="#label_row_id" for="#row_id"#attributes>#row_field#checkbox_price_field_label#required#invalid_feedback</label>',
										'class_row'				=> array('checkbox'),
										'class_row_disabled'	=> array('disabled'),
										'class_inline' 			=> array('checkbox-inline'),
									),

									'price_radio' 	=> array(

										'class_field'			=> array(),
										'mask_row_label'		=> '<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#row_field#radio_price_field_label#required#invalid_feedback</label>',
										'class_row'				=> array('radio'),
										'class_row_disabled'	=> array('disabled'),
										'class_inline' 			=> array('radio-inline'),
									),

									'file' 	=> array(

										'mask_field_label'		=>	'',
									),

									'signature' => array(

										'class_invalid_field'	=> array('wsf-invalid'),
										'class_valid_field'		=> array('wsf-valid')
									),

									'recaptcha' => array(

										'class_field'			=> array(),
										'class_invalid_field'	=> array('wsf-invalid'),
										'class_valid_field'		=> array('wsf-valid')
									),

									'submit' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'primary'
									),

									'clear' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'reset' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'tab_previous' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'tab_next' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'section_add' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'section_delete' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'section_down' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'save' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'button' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'message' 	=> array(
										'class_field'						=> array('alert')
									),

									'progress'	=> array(
										'class_field'					=> array('progress-bar'),
										'class_complete'				=> array('progress-bar-success'),
										'mask_field'					=>	'<div class="progress" id="#id"><div data-progress-bar data-progress-bar-value data=value="0" role="progressbar" style="width: 0%" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100"#attributes></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'bootstrap4'	=> array(

						'name'						=>	__('Bootstrap 4.0', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'bootstrap4.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'minicolors_args'			=>	array(

							'changeDelay' 	=> 200,
							'letterCase' 	=> 'uppercase',
							'theme' 		=> 'bootstrap'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'			=>	'col-#id-#size',
							'column_css_selector'	=>	'.col-#id-#size',
							'offset_class'			=>	'offset-#id-#offset',
							'offset_css_selector'	=>	'.offset-#id-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 575px
							25	=>	array(
								'id'					=>	'xs',
								'name'					=>	__('Extra Small', 'ws-form'),
								'column_class'			=>	'col-#size',
								'column_css_selector'	=>	'.col-#size',
								'offset_class'			=>	'offset-#offset',
								'offset_css_selector'	=>	'.offset-#offset',
								'admin_max_width'		=>	575,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 767px
							50	=>	array(
								'id'				=>	'sm',
								'name'				=>	__('Small', 'ws-form'),
								'admin_max_width'	=>	767,
								'min_width'			=>	576
							),

							// Up to 991px
							75	=>	array(
								'id'				=>	'md',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	991,
								'min_width'			=>	768
							),

							// Up to 1199px
							100	=>	array(
								'id'				=>	'lg',
								'name'				=>	__('Large', 'ws-form'),
								'admin_max_width'	=>	1199,
								'min_width'			=>	992
							),

							// 1200+
							125	=>	array(
								'id'				=>	'xl',
								'name'				=>	__('Extra Large', 'ws-form'),
								'min_width'			=>	1200
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'		=>	'<ul class="nav nav-tabs mb-3" role="tablist">#tabs</ul>',
								'mask_single'		=>	'<li class="nav-item"><a class="nav-link" href="#href" data-toggle="tab" role="tab">#label</a></li>',
								'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').tab('show');",
								'event_js'			=>	'shown.bs.tab',
								'event_type_js'		=>	'tab',
								'class_disabled'	=>	'disabled',
								'class_active'		=>	'active'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
								)
							)
						),

						'action_js' => array(

							'message'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tab-pane',
								'class_active'	=> 'active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
							)
						),

						'fields' => array(

							'public' => array(

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label text-right">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

								// Required
								'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

								// Help
								'mask_help'				=>	'<small id="#help_id" class="#help_class">#help#help_append</small>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class">#invalid_feedback</div>',

								// Classes - Default
								'class_single'					=> array('form-group'),
//								'class_single_required'			=> array('required'),
								'class_field'					=> array('form-control'),
								'class_field_label'				=> array(),
								'class_help'					=> array('form-text', 'text-muted'),
								'class_invalid_feedback'		=> array('invalid-feedback'),
								'class_inline' 					=> array('form-inline'),
								'class_form_validated'			=> array('was-validated'),
								'class_orientation_wrapper'		=> array('row'),
								'class_orientation_row'			=> array(),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'align-self-center',
									'bottom'	=>	'align-self-end'
								),
								'class_field_button_type'	=> array(

									'default'		=>	'btn-secondary',
									'primary'		=>	'btn-primary',
									'secondary'		=>	'btn-secondary',
									'success'		=>	'btn-success',
									'information'	=>	'btn-info',
									'warning'		=>	'btn-warning',
									'danger'		=>	'btn-danger'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'alert-success',
									'information'	=>	'alert-info',
									'warning'		=>	'alert-warning',
									'danger'		=>	'alert-danger'
								),

								// Classes - Custom by field type
								'field_types'		=> array(

									'select' 	=> array(
										'class_field'			=> array('custom-select')
									),

									'checkbox' 	=> array(
										
										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label></div>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label></div>#invalid_feedback'
									),

									'spacer' 	=> array(
										'class_single'			=> array(),
									),

									'section_icons' 	=> array(

										'class_field'			=> array(),
									),

									'price_select' 	=> array(
										'class_field'			=> array('custom-select'),
									),

									'price_checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label></div>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label></div>#invalid_feedback'
									),

									'signature' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'recaptcha' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'file' 	=> array(

										'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"><div class="custom-file">#field</div></div>',
										'class_field'		=> array('custom-file-input'),
										'class_field_label'		=> array('custom-file-label')
									),

									'submit' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'primary'
									),

									'clear' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(
										'class_field'		=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(
										'class_field'						=> array('alert')
									),

									'progress'	=> array(
										'class_field'					=> array('progress-bar'),
										'class_complete'				=> array('bg-success'),
										'mask_field'					=>	'<div class="progress" id="#id"><div data-progress-bar data-progress-bar-value data=value="0" role="progressbar" style="width: 0%" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100"#attributes></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'bootstrap41'	=> array(

						'name'						=>	__('Bootstrap 4.1-4.5', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'bootstrap41.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'minicolors_args'			=>	array(

							'changeDelay' 	=> 200,
							'letterCase' 	=> 'uppercase',
							'theme' 		=> 'bootstrap'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'			=>	'col-#id-#size',
							'column_css_selector'	=>	'.col-#id-#size',
							'offset_class'			=>	'offset-#id-#offset',
							'offset_css_selector'	=>	'.offset-#id-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 575px
							25	=>	array(
								'id'					=>	'xs',
								'name'					=>	__('Extra Small', 'ws-form'),
								'column_class'			=>	'col-#size',
								'column_css_selector'	=>	'.col-#size',
								'offset_class'			=>	'offset-#offset',
								'offset_css_selector'	=>	'.offset-#offset',
								'admin_max_width'		=>	575,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 767px
							50	=>	array(
								'id'				=>	'sm',
								'name'				=>	__('Small', 'ws-form'),
								'admin_max_width'	=>	767,
								'min_width'			=>	576
							),

							// Up to 991px
							75	=>	array(
								'id'				=>	'md',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	991,
								'min_width'			=>	768
							),

							// Up to 1199px
							100	=>	array(
								'id'				=>	'lg',
								'name'				=>	__('Large', 'ws-form'),
								'admin_max_width'	=>	1199,
								'min_width'			=>	992
							),

							// 1200+
							125	=>	array(
								'id'				=>	'xl',
								'name'				=>	__('Extra Large', 'ws-form'),
								'min_width'			=>	1200
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'		=>	'<ul class="nav nav-tabs mb-3" role="tablist">#tabs</ul>',
								'mask_single'		=>	'<li class="nav-item"><a class="nav-link" href="#href" data-toggle="tab" role="tab">#label</a></li>',
								'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').tab('show');",
								'event_js'			=>	'shown.bs.tab',
								'event_type_js'		=>	'tab',
								'class_disabled'	=>	'disabled',
								'class_active'		=>	'active'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
								)
							)
						),

						'action_js' => array(

							'message'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tab-pane',
								'class_active'	=> 'active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
							)
						),

						'fields' => array(

							'public' => array(

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label text-right">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								'mask_wrapper' 		=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

								// Required
								'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

								// Help
								'mask_help'			=>	'<small id="#help_id" class="#help_class">#help#help_append</small>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class">#invalid_feedback</div>',

								// Classes - Default
								'class_single'					=> array('form-group'),
//								'class_single_required'			=> array('required'),
								'class_field'					=> array('form-control'),
								'class_field_label'				=> array(),
								'class_help'					=> array('form-text', 'text-muted'),
								'class_invalid_feedback'		=> array('invalid-feedback'),
								'class_inline' 					=> array('form-inline'),
								'class_form_validated'			=> array('was-validated'),
								'class_orientation_wrapper'		=> array('row'),
								'class_orientation_row'			=> array(),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'align-self-center',
									'bottom'	=>	'align-self-end'
								),
								'class_field_button_type'	=> array(

									'default'		=>	'btn-secondary',
									'primary'		=>	'btn-primary',
									'secondary'		=>	'btn-secondary',
									'success'		=>	'btn-success',
									'information'	=>	'btn-info',
									'warning'		=>	'btn-warning',
									'danger'		=>	'btn-danger'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'alert-success',
									'information'	=>	'alert-info',
									'warning'		=>	'alert-warning',
									'danger'		=>	'alert-danger'
								),

								// Classes - Custom by field type
								'field_types'		=> array(

									'select' 	=> array(
										'class_field'			=> array('custom-select')
									),

									'checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label></div>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label></div>#invalid_feedback'
									),

									'spacer' 	=> array(
										'class_single'			=> array(),
									),
									'range' 	=> array(
										'class_field'			=> array('custom-range'),
									),

									'section_icons' 	=> array(

										'class_field'			=> array(),
									),

									'price_select' 	=> array(
										'class_field'			=> array('custom-select'),
									),

									'price_checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '<div class="custom-control custom-checkbox">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label></div>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('custom-control-input'),
										'class_row_field_label'	=> array('custom-control-label'),
										'class_inline' 			=> array('custom-control-inline'),
										'mask_row_label'		=> '<div class="custom-control custom-radio">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label></div>#invalid_feedback'
									),

									'price_range' 	=> array(
										'class_field'			=> array('custom-range'),
									),

									'signature' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'recaptcha' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'file' 	=> array(

										'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"><div class="custom-file">#field</div></div>',
										'class_field'		=> array('custom-file-input'),
										'class_field_label'		=> array('custom-file-label')
									),

									'submit' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'primary'
									),

									'clear' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(
										'class_field'	=> array('alert')
									),

									'progress'	=> array(
										'class_field'					=> array('progress-bar'),
										'class_complete'				=> array('bg-success'),
										'mask_field'					=>	'<div class="progress" id="#id"><div data-progress-bar data-progress-bar-value data=value="0" role="progressbar" style="width: 0%" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100"#attributes></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'bootstrap5'	=> array(

						'name'						=>	__('Bootstrap 5.0+', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'bootstrap5.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'minicolors_args'			=>	array(

							'changeDelay' 	=> 200,
							'letterCase' 	=> 'uppercase',
							'theme' 		=> 'bootstrap'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'			=>	'col-#id-#size',
							'column_css_selector'	=>	'.col-#id-#size',
							'offset_class'			=>	'offset-#id-#offset',
							'offset_css_selector'	=>	'.offset-#id-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 575px
							25	=>	array(
								'id'					=>	'xs',
								'name'					=>	__('Extra Small', 'ws-form'),
								'column_class'			=>	'col-#size',
								'column_css_selector'	=>	'.col-#size',
								'offset_class'			=>	'offset-#offset',
								'offset_css_selector'	=>	'.offset-#offset',
								'admin_max_width'		=>	575,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 767px
							50	=>	array(
								'id'				=>	'sm',
								'name'				=>	__('Small', 'ws-form'),
								'admin_max_width'	=>	767,
								'min_width'			=>	576
							),

							// Up to 991px
							75	=>	array(
								'id'				=>	'md',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	991,
								'min_width'			=>	768
							),

							// Up to 1199px
							100	=>	array(
								'id'				=>	'lg',
								'name'				=>	__('Large', 'ws-form'),
								'admin_max_width'	=>	1199,
								'min_width'			=>	992
							),

							// Up to 1399px
							125	=>	array(
								'id'				=>	'xl',
								'name'				=>	__('Extra Large', 'ws-form'),
								'admin_max_width'	=>	1399,
								'min_width'			=>	1200
							),

							// 1400px+
							150	=>	array(
								'id'				=>	'xxl',
								'name'				=>	__('Extra Extra Large', 'ws-form'),
								'min_width'			=>	1400
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'		=>	'<ul class="nav nav-tabs mb-3" role="tablist">#tabs</ul>',
								'mask_single'		=>	'<li class="nav-item" role="presentation"><a class="nav-link" href="#href" data-toggle="tab" role="tab">#label</a></li>',
								'activate_js'		=>	"$('#form ul.nav-tabs li:eq(#index) a').addClass('active');",
								'event_js'			=>	'shown.bs.tab',
								'event_type_js'		=>	'tab',
								'class_disabled'	=>	'disabled',
								'class_active'		=>	'active'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success', 'text_class' => 'text-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info', 'text_class' => 'text-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning', 'text_class' => 'text-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger', 'text_class' => 'text-danger')
								)
							)
						),

						'action_js' => array(

							'message'	=>	array(

								'mask_wrapper'		=>	'<div class="alert #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'alert-success'),
									'information'	=>	array('mask_wrapper_class' => 'alert-info'),
									'warning'		=>	array('mask_wrapper_class' => 'alert-warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert-danger')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tab-content">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index" role="tabpanel">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tab-pane',
								'class_active'	=> 'active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
							)
						),

						'fields' => array(

							'public' => array(

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label text-right">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="col-#column_width_label col-form-label">#label</div>',
									'mask_field_wrapper'			=>	'<div class="col-#column_width_field">#field</div>',
								),

								'mask_wrapper' 		=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 		=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',

								// Required
								'mask_required_label'	=> ' <strong class="text-danger">*</strong>',

								// Help
								'mask_help'			=>	'<div id="#help_id" class="#help_class">#help#help_append</div>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<div id="#invalid_feedback_id" class="#invalid_feedback_class">#invalid_feedback</div>',

								// Classes - Default
								'class_single'					=> array('mb-3'),
//								'class_single_required'			=> array('required'),
								'class_field'					=> array('form-control'),
								'class_field_label'				=> array('form-label'),
								'class_help'					=> array('form-text'),
								'class_invalid_feedback'		=> array('invalid-feedback'),
								'class_inline' 					=> array('form-check-inline'),
								'class_form_validated'			=> array('was-validated'),
								'class_orientation_wrapper'		=> array('row'),
								'class_orientation_row'			=> array(),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'align-self-center',
									'bottom'	=>	'align-self-end'
								),
								'class_field_button_type'	=> array(

									'default'		=>	'btn-secondary',
									'primary'		=>	'btn-primary',
									'secondary'		=>	'btn-secondary',
									'success'		=>	'btn-success',
									'information'	=>	'btn-info',
									'warning'		=>	'btn-warning',
									'danger'		=>	'btn-danger'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'alert-success',
									'information'	=>	'alert-info',
									'warning'		=>	'alert-warning',
									'danger'		=>	'alert-danger'
								),

								// Classes - Custom by field type
								'field_types'		=> array(

									'select' 	=> array(
										'class_field'			=> array('form-select')
									),

									'checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('form-check-input'),
										'class_row_field_label'	=> array('form-check-label'),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label></div>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('form-check-input'),
										'class_row_field_label'	=> array('form-check-label'),
										'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label></div>#invalid_feedback'
									),

									'spacer' 	=> array(
										'class_single'			=> array(),
									),
									'range' 	=> array(
										'class_field'			=> array('form-range'),
									),

									'section_icons' 	=> array(

										'class_field'			=> array(),
									),

									'price_select' 	=> array(
										'class_field'			=> array('form-select'),
									),

									'price_checkbox' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('form-check-input'),
										'class_row_field_label'	=> array('form-check-label'),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label></div>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_field'			=> array(),
										'class_row'				=> array(),
										'class_row_disabled'	=> array('disabled'),
										'class_row_field'		=> array('form-check-input'),
										'class_row_field_label'	=> array('form-check-label'),
										'mask_row_label'		=> '<div class="form-check">#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label></div>#invalid_feedback'
									),

									'price_range' 	=> array(
										'class_field'			=> array('form-range'),
									),

									'signature' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'recaptcha' => array(

										'class_invalid_field'	=> array('is-invalid'),
										'class_valid_field'		=> array('is-valid')
									),

									'file' 	=> array(

										'mask_field_label'			=>	sprintf('<label id="#label_id" for="#id" data-label-target="span" #attributes><span class="form-file-text">#label</span><span class="form-file-button">%s</span></label>', __('Browse', 'ws-form')),
										'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"><div class="form-file">#field</div></div>',
										'class_field'			=> array('form-file-input'),
										'class_field_label'		=> array('form-file-label')
									),

									'submit' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'primary'
									),

									'clear' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(
										'class_field'						=> array('btn'),
										'class_field_full_button'			=> array('btn-block'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(
										'class_field'	=> array('alert')
									),

									'progress'	=> array(
										'class_field'					=> array('progress-bar'),
										'class_complete'				=> array('bg-success'),
										'mask_field'					=>	'<div class="progress" id="#id"><div data-progress-bar data-progress-bar-value data=value="0" role="progressbar" style="width: 0%" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100"#attributes></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'foundation5'	=> array(

						'name'						=>	__('Foundation 5.x', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'foundation5.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'init_js'					=>	"if(typeof($(document).foundation) === 'function') { $(document).foundation('tab', 'reflow'); }",

						'minicolors_args'			=>	array(

							'theme' 					=> 'foundation'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'				=>	'#id-#size',
							'column_css_selector'		=>	'.#id-#size',
							'offset_class'				=>	'#id-offset-#offset',
							'offset_css_selector'		=>	'.#id-offset-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 639px
							25	=>	array(
								'id'				=>	'small',
								'name'				=>	__('Small', 'ws-form'),
								'column_class'			=>	'#id-#size',
								'column_css_selector'	=>	'.#id-#size',
								'admin_max_width'	=>	640,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 1023px
							75	=>	array(
								'id'				=>	'medium',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	1024,
								'min_width'			=>	641
							),

							// 1024+
							125	=>	array(
								'id'				=>	'large',
								'name'				=>	__('Large', 'ws-form'),
								'min_width'			=>	1025
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
								'attributes' => array('data-abide' => '')
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'				=>	'<dl class="tabs" data-tab id="#id">#tabs</dl>',
								'mask_single'				=>	'<dd class="tab-title#active"><a href="#href">#label</a></dd>',
								'active'					=>	' active',
								'activate_js'				=>	"$('#form .tabs .tab-title:eq(#index) a').click();",
								'event_js'					=>	'toggled',
								'event_type_js'				=>	'wrapper',
								'event_selector_wrapper_js'	=>	'dl[data-tab]',
								'event_selector_active_js'	=>	'dd.active',
								'class_parent_disabled'		=>	'wsf-tab-disabled'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="alert-box #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'success'),
									'information'	=>	array('mask_wrapper_class' => 'info'),
									'warning'		=>	array('mask_wrapper_class' => 'warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tabs-content" data-tabs-content="#id">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'content',
								'class_active'	=> 'active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
								'class_single'	=> array('columns')
							)
						),

						'fields' => array(

							'public' => array(

								// Honeypot attributes
								'honeypot_attributes' => array('data-abide-ignore'),

								// Label position - Left
								'left' => array(

									'mask'									=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'				=>	'<div class="small-#column_width_label columns">#label</div>',
									'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'					=>	'<div class="small-#column_width_field columns">#field</div>',
									'class_field_label'						=>	array('text-right', 'middle'),
								),

								// Label position - Right
								'right' => array(

									'mask'									=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'				=>	'<div class="small-#column_width_label columns">#label</div>',
									'mask_field_label'						=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'					=>	'<div class="small-#column_width_field columns">#field</div>',
									'class_field_label'						=>	array('middle'),
								),

								'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',
								'mask_field_label'		=>	'<label id="#label_id" for="#id"#attributes>#label#field</label>',

								// Required
								'mask_required_label'	=> ' <small>Required</small>',

								// Help
								'mask_help'				=>	'<p id="#help_id">#help#help_append</p>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<small id="#invalid_feedback_id" data-form-error-for="#id" class="#invalid_feedback_class">#invalid_feedback</small>',

								// Classes - Default
								'class_single'				=> array('columns'),
								'class_field'				=> array(),
								'class_field_label'			=> array(),
								'class_help'				=> array(),
								'class_invalid_feedback'	=> array('error'),
								'class_inline' 				=> array('form-inline'),
								'class_form_validated'		=> array('was-validated'),
								'class_orientation_wrapper'	=> array('row'),
								'class_orientation_row'		=> array('columns'),
								'class_field_button_type'	=> array(

									'secondary'		=>	'secondary',
									'success'		=>	'success',
									'information'	=>	'info',
									'danger'		=>	'alert'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'success',
									'information'	=>	'info',
									'warning'		=>	'warning',
									'danger'		=>	'alert'
								),

								// Attributes
								'attribute_field_match'		=> array('data-equalto' => '#field_match_id'),

								// Classes - Custom by field type
								'field_types'		=> array(

									'checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback',
									),

									'spacer'	=> array(

										'mask_field_label'		=>	'',
									),

									'texteditor'	=> array(

										'mask_field_label'		=>	'',
									),

									'section_icons'	=> array(

										'mask_field_label'		=>	'',
									),

									'price_checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label>#invalid_feedback',
									),

									'file' 	=> array(

										'mask_field_label'	=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'		=> array('show-for-sr'),
										'class_field_label'	=> array('button', 'expand')
									),

									'signature' => array(

										'mask_field_label'					=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'						=> array('panel'),
										'class_invalid_label'				=> array('error'),
										'class_invalid_field'				=> array(),
										'class_invalid_invalid_feedback'	=> array('error')
									),

									'recaptcha' => array(

										'mask_field_label'					=>	'',
										'class_invalid_invalid_feedback'	=> array('is-visible')
									),

									'submit' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> 	array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'clear' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> 	array('expand'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expand'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(

										'mask_field_label'	=>	'',
										'class_field'		=> array('alert-box')
									),

									'hidden'	=> array(

										'mask_field_label'	=> 	''
									),

									'html' => array(

										'mask_field_label'	=>	''
									),

									'progress'	=> array(

										'class_field'					=> array('progress'),
										'class_complete'				=> array('success'),
										'mask_field'					=>	'<div data-progress-bar id="#id"#attributes><div data-progress-bar-value data=value="0" class="meter"></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'foundation6'	=> array(

						'name'						=>	__('Foundation 6.0-6.3.1', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'foundation6.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'init_js'					=>	$framework_foundation_init_js,

						'minicolors_args'			=>	array(

							'theme' 				=> 'foundation'
						),

						'columns'					=>	array(

							'column_count' 				=> 	12,
							'column_class'				=>	'#id-#size',
							'column_css_selector'		=>	'.#id-#size',
							'offset_class'				=>	'#id-offset-#offset',
							'offset_css_selector'		=>	'.#id-offset-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 639px
							25	=>	array(
								'id'					=>	'small',
								'name'					=>	__('Small', 'ws-form'),
								'column_class'			=>	'#id-#size',
								'column_css_selector'	=>	'.#id-#size',
								'admin_max_width'		=>	639,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 1023px
							75	=>	array(
								'id'				=>	'medium',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	1023,
								'min_width'			=>	640
							),

							// 1024+
							125	=>	array(
								'id'				=>	'large',
								'name'				=>	__('Large', 'ws-form'),
								'min_width'			=>	1024
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
								'attributes' => array('data-abide' => '')
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'				=>	'<ul class="tabs" data-tabs id="#id">#tabs</ul>',
								'mask_single'				=>	'<li class="tabs-title#active"><a href="#href">#label</a></li>',
								'active'					=>	' is-active',
								'activate_js'				=>	"$('#form .tabs .tabs-title:eq(#index) a').click();",
								'event_js'					=>	'change.zf.tabs',
								'event_type_js'				=>	'wrapper',
								'event_selector_wrapper_js'	=>	'ul[data-tabs]',
								'event_selector_active_js'	=>	'li.is-active',
								'class_parent_disabled'		=>	'wsf-tab-disabled'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="callout #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'success'),
									'information'	=>	array('mask_wrapper_class' => 'primary'),
									'warning'		=>	array('mask_wrapper_class' => 'warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tabs-content" data-tabs-content="#id">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tabs-panel',
								'class_active'	=> 'is-active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="row" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',
								'class_single'	=> array('columns')
							)
						),

						'fields' => array(

							'public' => array(

								// Honeypot attributes
								'honeypot_attributes' => array('data-abide-ignore'),

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label columns">#label</div>',
									'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'			=>	'<div class="small-#column_width_field columns">#field</div>',
									'class_field_label'				=>	array('text-right', 'middle'),
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="row">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label columns">#label</div>',
									'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'			=>	'<div class="small-#column_width_field columns">#field</div>',
									'class_field_label'				=>	array('middle'),
								),

								'mask_wrapper' 			=> '#label<div class="row" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',
								'mask_field_label'		=>	'<label id="#label_id" for="#id"#attributes>#label#field</label>',


								// Required
								'mask_required_label'	=> ' <small>Required</small>',

								// Help
								'mask_help'				=>	'<p id="#help_id" class="#help_class">#help#help_append</p>',

								// Invalid feedback
								'mask_invalid_feedback'	=>	'<span id="#invalid_feedback_id" data-form-error-for="#id" class="#invalid_feedback_class">#invalid_feedback</span>',

								// Classes - Default
								'class_single'					=> array('columns'),
								'class_field'					=> array(),
								'class_field_label'				=> array(),
								'class_help'					=> array('help-text'),
								'class_invalid_feedback'		=> array('form-error'),
								'class_inline' 					=> array('form-inline'),
								'class_form_validated'			=> array('was-validated'),
								'class_orientation_wrapper'		=> array('row'),
								'class_orientation_row'			=> array('columns'),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'align-self-middle',
									'bottom'	=>	'align-self-bottom'
								),
								'class_field_button_type'	=> array(

									'primary'		=>	'primary',
									'secondary'		=>	'secondary',
									'success'		=>	'success',
									'warning'		=>	'warning',
									'danger'		=>	'alert'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'success',
									'information'	=>	'primary',
									'warning'		=>	'warning',
									'danger'		=>	'alert'
								),

								// Attributes
								'attribute_field_match'		=> array('data-equalto' => '#field_match_id'),

								// Classes - Custom by field type
								'field_types'				=> array(

									'checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
									),

									'radio' 				=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback',
									),

									'spacer'	=> array(

										'mask_field_label'		=>	'',
									),

									'texteditor'	=> array(

										'mask_field_label'		=>	'',
									),
									'section_icons'	=> array(

										'mask_field_label'		=>	'',
									),

									'price_checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label>#invalid_feedback',
									),

									'price_radio' 				=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label>#invalid_feedback',
									),

									'file' 	=> array(

										'mask_field_label'		=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'			=> array('show-for-sr'),
										'class_field_label'		=> array('button', 'expanded')
									),

									'signature' => array(

										'mask_field_label'					=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'						=> array('callout'),
										'class_invalid_label'				=> array('is-invalid-label'),
										'class_invalid_field'				=> array('is-invalid-input'),
										'class_invalid_invalid_feedback'	=> array('is-visible')
									),

									'recaptcha' => array(

										'mask_field_label'					=>	'',
										'class_invalid_invalid_feedback'	=> array('is-visible')
									),

									'submit' 	=> array(

										'mask_field_label'					=>	'#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'primary'
									),
									'clear' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(

										'mask_field_label'	=>	'',
										'class_field'		=> array('callout')
									),

									'hidden' => array(

										'mask_field_label'	=>	''
									),

									'html' => array(

										'mask_field_label'	=>	''
									),

									'progress'	=> array(

										'class_field'					=> array('progress'),
										'class_complete'				=> array('success'),
										'mask_field'					=>	'<div data-progress-bar role="progressbar" tabindex="0" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100" id="#id"#attributes><div data-progress-bar-value data=value="0" class="progress-meter"></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					),

					'foundation64'	=> array(

						'name'						=>	__('Foundation 6.4+', 'ws-form'),

						'default'					=>	false,

						'css_file'					=>	'foundation64.css',

						'label_positions'			=>	array('default', 'top', 'left', 'right', 'bottom'),

						'init_js'					=>	$framework_foundation_init_js,

						'minicolors_args'			=>	array(

							'theme' => 'foundation'
						),

						'columns'					=>	array(

							'column_count' 			=> 	12,
							'column_class'				=>	'#id-#size',
							'column_css_selector'		=>	'.#id-#size',
							'offset_class'				=>	'#id-offset-#offset',
							'offset_css_selector'		=>	'.#id-offset-#offset'
						),

						'breakpoints'				=>	array(

							// Up to 639px
							25	=>	array(
								'id'					=>	'small',
								'name'					=>	__('Small', 'ws-form'),
								'column_class'			=>	'#id-#size',
								'column_css_selector'	=>	'.#id-#size',
								'admin_max_width'		=>	639,
								'column_size_default'	=>	'column_count'	// Set to column count if XS framework breakpoint size is not set in object meta
							),
							// Up to 1023px
							75	=>	array(
								'id'				=>	'medium',
								'name'				=>	__('Medium', 'ws-form'),
								'admin_max_width'	=>	1023,
								'min_width'			=>	640
							),

							// 1024+
							125	=>	array(
								'id'				=>	'large',
								'name'				=>	__('Large', 'ws-form'),
								'min_width'			=>	1024
							)
						),

						'form' => array(

							'admin' => array('mask_single' => '#form'),
							'public' => array(

								'mask_single' 	=> '#label#form',
								'mask_label'	=> '<h2>#label</h2>',
								'attributes' 	=> array('data-abide' => '')
							),
						),

						'tabs' => array(

							'public' => array(

								'mask_wrapper'				=>	'<ul class="tabs" data-tabs id="#id">#tabs</ul>',
								'mask_single'				=>	'<li class="tabs-title#active"><a href="#href">#label</a></li>',
								'active'					=>	' is-active',
								'activate_js'				=>	"$('#form .tabs .tabs-title:eq(#index) a').click();",
								'event_js'					=>	'change.zf.tabs',
								'event_type_js'				=>	'wrapper',
								'event_selector_wrapper_js'	=>	'ul[data-tabs]',
								'event_selector_active_js'	=>	'li.is-active',
								'class_parent_disabled'		=>	'wsf-tab-disabled'
							),
						),

						'message' => array(

							'public'	=>	array(

								'mask_wrapper'		=>	'<div class="callout #mask_wrapper_class">#message</div>',

								'types'	=>	array(

									'success'		=>	array('mask_wrapper_class' => 'success'),
									'information'	=>	array('mask_wrapper_class' => 'primary'),
									'warning'		=>	array('mask_wrapper_class' => 'warning'),
									'danger'		=>	array('mask_wrapper_class' => 'alert')
								)
							)
						),

						'groups' => array(

							'public' => array(

								'mask_wrapper'	=>	'<div class="tabs-content" data-tabs-content="#id">#groups</div>',
								'mask_single' 	=> '<div class="#class" id="#id" data-id="#data_id" data-group-index="#data_group_index">#label#group</div>',
								'mask_label' 	=> '<h3>#label</h3>',
								'class'			=> 'tabs-panel',
								'class_active'	=> 'is-active',
							)
						),

						'sections' => array(

							'public' => array(

								'mask_wrapper'	=> '<div class="grid-x grid-margin-x" id="#id" data-id="#data_id">#sections</div>',
								'mask_single' 	=> '<fieldset#attributes class="#class" id="#id" data-id="#data_id">#section</fieldset>',

								'class_single'	=> array('cell')
							)
						),

						'fields' => array(

							'public' => array(

								// Honeypot attributes
								'honeypot_attributes' => array('data-abide-ignore'),

								// Label position - Left
								'left' => array(

									'mask'							=>	'<div class="grid-x grid-padding-x">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label cell">#label</div>',
									'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'			=>	'<div class="small-#column_width_field cell">#field</div>',
									'class_field_label'				=>	array('text-right', 'middle'),
								),

								// Label position - Right
								'right' => array(

									'mask'							=>	'<div class="grid-x grid-padding-x">#field</div>',
									'mask_field_label_wrapper'		=>	'<div class="small-#column_width_label cell">#label</div>',
									'mask_field_label'				=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
									'mask_field_wrapper'			=>	'<div class="small-#column_width_field cell">#field</div>',
									'class_field_label'				=>	array('middle'),
								),

								'mask_wrapper' 			=> '#label<div class="grid-x grid-margin-x" id="#id" data-id="#data_id">#fields</div>',
								'mask_wrapper_label'	=> '<legend>#label</legend>',
								'mask_single' 			=> '<div class="#class" id="#id" data-id="#data_id" data-type="#type"#attributes>#field</div>',
								'mask_field_label'		=> '<label id="#label_id" for="#id"#attributes>#label#field</label>',

								// Required
								'mask_required_label'	=> ' <small>Required</small>',

								// Help
								'mask_help'			=>	'<p id="#help_id" class="#help_class">#help#help_append</p>',

								// Invalid feedback
								'mask_invalid_feedback'		=>	'<span id="#invalid_feedback_id" data-form-error-for="#id" class="#invalid_feedback_class">#invalid_feedback</span>',

								// Classes - Default
								'class_single'				=> array('cell'),
								'class_field'				=> array(),
								'class_field_label'			=> array(),
								'class_help'				=> array('help-text'),
								'class_invalid_feedback'	=> array('form-error'),
								'class_inline' 				=> array('form-inline'),
								'class_form_validated'		=> array('was-validated'),
								'class_orientation_wrapper'		=> array('grid-x', 'grid-margin-x'),
								'class_orientation_row'			=> array('cell'),
								'class_single_vertical_align'	=> array(

									'middle'	=>	'align-self-middle',
									'bottom'	=>	'align-self-bottom'
								),
								'class_field_button_type'	=> array(

									'primary'		=>	'primary',
									'secondary'		=>	'secondary',
									'success'		=>	'success',
									'warning'		=>	'warning',
									'danger'		=>	'alert'
								),
								'class_field_message_type'	=> array(

									'success'		=>	'success',
									'information'	=>	'primary',
									'warning'		=>	'warning',
									'danger'		=>	'alert'
								),

								// Attributes
								'attribute_field_match'		=> array('data-equalto' => '#field_match_id'),

								// Classes - Custom by field type
								'field_types'		=> array(

									'checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#invalid_feedback#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_field_label</label>#invalid_feedback',
									),

									'radio' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_field_label</label>#invalid_feedback',
									),

									'spacer'	=> array(

										'mask_field_label'		=>	'',
									),

									'texteditor'	=> array(

										'mask_field_label'		=>	'',
									),
									'section_icons'	=> array(

										'mask_field_label'		=>	'',
									),

									'price_checkbox' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id"#attributes>#checkbox_price_field_label</label>#invalid_feedback',
									),

									'price_radio' 	=> array(

										'class_inline' 			=> array(),
										'mask_field'			=> '<div#attributes>#data</div>#help',
										'mask_row_label'		=> '#row_field<label id="#label_row_id" for="#row_id" data-label-required-id="#label_id"#attributes>#radio_price_field_label</label>#invalid_feedback',
									),

									'file' 	=> array(

										'mask_field_label'		=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'			=> array('show-for-sr'),
										'class_field_label'		=> array('button', 'expanded')
									),

									'signature' => array(

										'mask_field_label'					=>	'<label id="#label_id" for="#id"#attributes>#label</label>',
										'class_field'						=> array('callout'),
										'class_invalid_label'				=> array('is-invalid-label'),
										'class_invalid_field'				=> array('is-invalid-input'),
										'class_invalid_invalid_feedback'	=> array('is-visible')
									),

									'recaptcha' => array(

										'mask_field_label'					=> '',
										'class_invalid_invalid_feedback'	=> array('is-visible')
									),

									'submit' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'default'
									),

									'clear' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'reset' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_previous' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'tab_next' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_add' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_delete' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'danger'
									),

									'section_up' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'section_down' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=>	array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'save' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'button' 	=> array(

										'mask_field_label'					=> '#label',
										'class_field'						=> array('button'),
										'class_field_full_button'			=> array('expanded'),
										'class_field_button_type_fallback'	=> 'secondary'
									),

									'message' 	=> array(
										'mask_field_label'	=>	'',
										'class_field'		=> array('callout')
									),

									'hidden' => array(

										'mask_field_label'	=>	''
									),

									'html' => array(

										'mask_field_label'	=>	''
									),

									'progress'	=> array(

										'class_field'					=> array('progress'),
										'class_complete'				=> array('success'),
										'mask_field'					=>	'<div data-progress-bar role="progressbar" tabindex="0" aria-valuenow="#value" aria-valuemin="0" aria-valuemax="100" id="#id"#attributes><div data-progress-bar-value data=value="0" class="progress-meter"></div></div>',
										'mask_field_attributes'			=>	array('class', 'progress_source', 'aria_describedby', 'aria_labelledby', 'aria_label'),
									)
								)
							)
						)
					)
				),

				// Auto detection of framework based on string searching in CSS files for a website
				'auto_detect'	=> array(

					// Exclude filenames containing the following strings
					'exclude_filenames' => array(

						'ws-form',
						'jquery',
						'plugins',
						'uploads',
						'wp-includes'
					),

					// Strings to look for in CSS for each framework type
					'types'	=> array(

						'bootstrap5'	=> array(

							'Bootstrap v5',
							'.form-check',
							'.form-file',
							'.form-range'
						),

						'bootstrap41'	=> array(

							'Bootstrap v4',
							'.col-form-label',
							'.form-control-plaintext',
							'.row',
							'.custom-range'
						),

						'bootstrap4'	=> array(

							'Bootstrap v4',
							'.col-form-label',
							'.form-control-plaintext',
							'.row'
						),

						'bootstrap3'	=> array(

							'Bootstrap v3',
							'.control-label',
							'.form-control-static'
						),

						'foundation64'	=> array(

							'.cell',
							'.grid-x',
							'.grid-y' 
						),

						'foundation6'	=> array(

							'.columns',
							'.hide-for-small-only'
						),

						'foundation5'	=> array(

							'.hide-for-small',
							'.tab-title'
						)				
					)
				)
			);

			// Apply filter
			$frameworks = apply_filters('wsf_config_frameworks', $frameworks);

			// Public filter
			if($public) {

				// Get current framework
				$framework = WS_Form_Common::option_get('framework', 'ws-form');

				// Remove irrelevant frameworks
				foreach($frameworks['types'] as $type => $value) {

					if($type != $framework) { unset($frameworks['types'][$type]); }
				}

				// Remove icons
				unset($frameworks['icons']);
			}

			// Cache
			self::$frameworks[$public] = $frameworks;

			return $frameworks;
		}

		// Get analytics
		public static function get_analytics() {

			$analytics = array(

				'google'	=>	array(

					'label'	=>	__('Google Analytics', 'ws-form'),

					'functions'	=> array(

						'gtag'	=> array(

							'label'		=>	'gtag.js',
							'log_found'	=>	'log_analytics_google_loaded_gtag_js',

							// Base 64 encoded function otherwise Google's tag assistant thinks this is actual javascript
							'analytics_event_function' => base64_encode("gtag('event','#action',{'event_category': '#category', 'event_label': '#label', 'value': '#value'});")
						),

						'ga'	=> array(

							'label'		=>	'analytics.js',
							'log_found'	=>	'log_analytics_google_loaded_analytics_js',

							// Base 64 encoded function otherwise Google's tag assistant thinks this is actual javascript
							'analytics_event_function' => base64_encode("ga('send','event','#action','#category','#label','#value');"),
						),

						'_gaq'	=> array(

							'label'		=>	'ga.js',
							'log_found'	=>	'log_analytics_google_loaded_ga_js',

							// Base 64 encoded function otherwise Google's tag assistant thinks this is actual javascript
							'analytics_event_function' => base64_encode("_gaq.push(['_trackEvent', '#action', '#category', '#label', '#value']);")
						),
					)
				),

				'facebook_standard'	=>	array(

					'label'	=>	__('Facebook (Standard)', 'ws-form'),

					'functions'	=> array(

						'fbq'	=> array(

							'label'		=>	'fbevents.js',
							'log_found'	=>	'log_analytics_facebook_loaded_fbevents_js',

							// Base 64 encoded function otherwise Google's tag assistant thinks this is actual javascript
							'analytics_event_function' => base64_encode("fbq('track','#event'#params);")
						)
					)
				),

				'facebook_custom'	=>	array(

					'label'	=>	__('Facebook (Custom)', 'ws-form'),

					'functions'	=> array(

						'fbq'	=> array(

							'label'		=>	'fbevents.js',
							'log_found'	=>	'log_analytics_facebook_loaded_fbevents_js',

							// Base 64 encoded function otherwise Google's tag assistant thinks this is actual javascript
							'analytics_event_function' => base64_encode("fbq('trackCustom','#event'#params);")
						)
					)
				)
			);

			// Apply filter
			$analytics = apply_filters('wsf_config_analytics', $analytics);

			return $analytics;
		}

		// Get tracking
		public static function get_tracking($public = true) {

			// Check cache
			if(isset(self::$tracking[$public])) { return self::$tracking[$public]; }

			$tracking = array(

				'tracking_remote_ip'	=>	array(

					'label'				=>	__('Remote IP Address', 'ws-form'),
					'server_source'		=>	'http_env',
					'server_http_env'	=>	array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'),
					'type'				=>	'ip',
					'description'		=>	__('Stores the website visitors remote IP address, e.g. 123.45.56.789', 'ws-form')
				),

				'tracking_geo_location'	=>	array(

					'label'				=>	__('Location (By browser)', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_geo_location',
					'client_source'		=>	'geo_location',
					'type'				=>	'latlon',
					'description'		=>	__('If a website visitors device supports geo location (GPS) this option will prompt and request permission for that data and store the latitude and longitude to a submission.', 'ws-form')
				),

				'tracking_ip_lookup_latlon'	=>	array(

					'label'				=>	__('Location (By IP)', 'ws-form'),
					'server_source'		=>	'ip_lookup',
					'server_json_var'	=>	array('geoplugin_latitude', 'geoplugin_longitude'),
					'type'				=>	'latlon',
					'description'		=>	__('This will obtain an approximate latitude and longitude of a website visitor by their IP address.', 'ws-form')
				),

				'tracking_referrer'	=>	array(

					'label'				=>	__('Referrer', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_referrer',
					'client_source'		=>	'referrer',
					'type'				=>	'url',
					'description'		=>	__('Stores the web page address a website visitor was on prior to completing the submitted form.', 'ws-form')
				),

				'tracking_os'	=>	array(

					'label'				=>	__('Operating System', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_os',
					'client_source'		=>	'os',
					'type'				=>	'text',
					'description'		=>	__('Stores the website visitors operating system.', 'ws-form')
				),

				'tracking_agent'		=>	array(

					'label'				=>	__('Agent', 'ws-form'),
					'server_source'		=>	'http_env',
					'server_http_env'	=>	array('HTTP_USER_AGENT'),
					'type'				=>	'text',
					'description'		=>	__('Stores the website visitors agent (browser type).', 'ws-form')
				),

				'tracking_host'	=>	array(

					'label'				=>	__('Hostname', 'ws-form'),
					'server_source'		=>	'http_env',
					'server_http_env'	=>	array('HTTP_HOST', 'SERVER_NAME'),
					'client_source'		=>	'pathname',
					'type'				=>	'text',
					'description'		=>	__('Stores the server hostname.', 'ws-form')

				),

				'tracking_pathname'	=>	array(

					'label'				=>	__('Pathname', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_pathname',
					'client_source'		=>	'pathname',
					'type'				=>	'text',
					'description'		=>	__('Pathname of the URL.', 'ws-form')

				),

				'tracking_query_string'	=>	array(

					'label'				=>	__('Query String', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_query_string',
					'client_source'		=>	'query_string',
					'type'				=>	'text',
					'description'		=>	__('Query string of the URL.', 'ws-form')

				),

				'tracking_ip_lookup_city'	=>	array(

					'label'				=>	__('City (By IP)', 'ws-form'),
					'server_source'		=>	'ip_lookup',
					'server_json_var'	=>	'geoplugin_city',
					'type'				=>	'text',
					'description'		=>	__('When enabled, WS Form PRO will perform an IP lookup and obtain the city located closest to their approximate location.', 'ws-form')

				),

				'tracking_ip_lookup_region'	=>	array(

					'label'				=>	__('Region (By IP)', 'ws-form'),
					'server_source'		=>	'ip_lookup',
					'server_json_var'	=>	'geoplugin_region',
					'type'				=>	'text',
					'description'		=>	__('When enabled, WS Form PRO will perform an IP lookup and obtain the region located closest to their approximate location.', 'ws-form')
				),

				'tracking_ip_lookup_country'	=>	array(

					'label'				=>	__('Country (By IP)', 'ws-form'),
					'server_source'		=>	'ip_lookup',
					'server_json_var'	=>	'geoplugin_countryName',
					'type'				=>	'text',
					'description'		=>	__('When enabled, WS Form PRO will perform an IP lookup and obtain the country located closest to their approximate location.', 'ws-form')
				),

				'tracking_utm_source'	=>	array(

					'label'				=>	__('UTM Source', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_utm_source',
					'client_source'		=>	'query_var',
					'client_query_var'	=>	'utm_source',
					'type'				=>	'text',
					'description'		=>	__('This can be used to store the UTM (Urchin Tracking Module) source parameter.', 'ws-form')
				),

				'tracking_utm_medium'	=>	array(

					'label'				=>	__('UTM Medium', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_utm_medium',
					'client_source'		=>	'query_var',
					'client_query_var'	=>	'utm_medium',
					'type'				=>	'text',
					'description'		=>	__('This can be used to store the UTM (Urchin Tracking Module) medium parameter.', 'ws-form')
				),

				'tracking_utm_campaign'	=>	array(

					'label'				=>	__('UTM Campaign', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_utm_campaign',
					'client_source'		=>	'query_var',
					'client_query_var'	=>	'utm_campaign',
					'type'				=>	'text',
					'description'		=>	__('This can be used to store the UTM (Urchin Tracking Module) campaign parameter.', 'ws-form')
				),

				'tracking_utm_term'	=>	array(

					'label'				=>	__('UTM Term', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_utm_term',
					'client_source'		=>	'query_var',
					'client_query_var'	=>	'utm_term',
					'type'				=>	'text',
					'description'		=>	__('This can be used to store the UTM (Urchin Tracking Module) term parameter.', 'ws-form')
				),

				'tracking_utm_content'	=>	array(

					'label'				=>	__('UTM Content', 'ws-form'),
					'server_source'		=>	'query_var',
					'server_query_var'	=>	'wsf_utm_content',
					'client_source'		=>	'query_var',
					'client_query_var'	=>	'utm_content',
					'type'				=>	'text',
					'description'		=>	__('This can be used to store the UTM (Urchin Tracking Module) content parameter.', 'ws-form')
				)
			);

			// Apply filter
			$tracking = apply_filters('wsf_config_tracking', $tracking);

			// Public filtering
			if($public) {

				foreach($tracking as $key => $tracking_config) {

					if(!isset($tracking_config['client_source'])) {

						unset($tracking[$key]);

					} else {

						unset($tracking[$key]['label']);
						unset($tracking[$key]['description']);
						unset($tracking[$key]['type']);
					}
				}
			}

			// Cache
			self::$tracking[$public] = $tracking;

			return $tracking;
		}

		// Get debug
		public static function get_debug() {

			$debug = array(

				'words' => array(
'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur','adipiscing', 'elit', 'curabitur', 'vel', 'hendrerit', 'libero','eleifend', 'blandit', 'nunc', 'ornare', 'odio', 'ut','orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia','a', 'pretium', 'quis', 'congue', 'praesent', 'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros','dictum', 'proin', 'accumsan', 'sapien', 'nec', 'massa','volutpat', 'venenatis', 'sed', 'eu', 'lacus','quisque', 'porttitor', 'ligula', 'dui', 'mollis', 'tempus','at', 'magna', 'vestibulum', 'turpis', 'ac', 'diam','tincidunt', 'id', 'condimentum', 'enim', 'sodales', 'in','hac', 'habitasse', 'platea', 'dictumst', 'aenean', 'neque','fusce', 'augue', 'leo', 'eget', 'semper', 'mattis', 'tortor', 'scelerisque', 'nulla', 'interdum', 'tellus', 'malesuada','rhoncus', 'porta', 'sem', 'aliquet', 'et', 'nam','suspendisse', 'potenti', 'vivamus', 'luctus', 'fringilla', 'erat','donec', 'justo', 'vehicula', 'ultricies', 'varius', 'ante','primis', 'faucibus', 'ultrices', 'posuere', 'cubilia', 'curae','etiam', 'cursus', 'aliquam', 'quam', 'dapibus', 'nisl','feugiat', 'egestas', 'class', 'aptent', 'taciti', 'sociosqu','ad', 'litora', 'torquent', 'per', 'conubia', 'nostra','inceptos', 'himenaeos', 'phasellus', 'nibh', 'pulvinar', 'vitae','urna', 'iaculis', 'lobortis', 'nisi', 'viverra', 'arcu','morbi', 'pellentesque', 'metus', 'commodo', 'ut', 'facilisis','felis', 'tristique', 'ullamcorper', 'placerat', 'aenean', 'convallis','sollicitudin', 'integer', 'rutrum', 'duis', 'est', 'etiam','bibendum', 'donec', 'pharetra', 'vulputate', 'maecenas', 'mi','fermentum', 'consequat', 'suscipit', 'aliquam', 'habitant', 'senectus','netus', 'fames', 'quisque', 'euismod', 'curabitur', 'lectus','elementum', 'tempor', 'risus', 'cras')

			);

			// Apply filter
			$debug = apply_filters('wsf_config_debug', $debug);

			return $debug;
		}

		// Parse variables
		public static function get_parse_variables($public = true) {

			// Check cache
			if(isset(self::$parse_variables[$public])) { return self::$parse_variables[$public]; }

			// Get email logo
			$email_logo = '';
			$action_email_logo = intval(WS_Form_Common::option_get('action_email_logo'));
			$action_email_logo_size = WS_Form_Common::option_get('action_email_logo_size');
			if($action_email_logo_size == '') { $action_email_logo_size = 'full'; }
			if($action_email_logo > 0) {

				$email_logo = wp_get_attachment_image($action_email_logo, $action_email_logo_size);
			}

			// Get currency symbol
			$currencies = WS_Form_Config::get_currencies();
			$currency = WS_Form_Common::option_get('currency', WS_Form_Common::get_currency_default());
			$currency_found = isset($currencies[$currency]) && isset($currencies[$currency]['symbol']);
			$currency_symbol = $currency_found ? $currencies[$currency]['symbol'] : '$';
			// Parse variables
			$parse_variables = array(

				// Blog
				'blog'	=>	array(

					'label'		=> __('Blog', 'ws-form'),

					'variables'	=> array(

						'blog_url'			=> array('label' => __('URL', 'ws-form'), 'value' => get_bloginfo('url')),
						'blog_name'			=> array('label' => __('Name', 'ws-form'), 'value' => get_bloginfo('name')),
						'blog_language'		=> array('label' => __('Language', 'ws-form'), 'value' => get_bloginfo('language')),
						'blog_charset'		=> array('label' => __('Character Set', 'ws-form'), 'value' => get_bloginfo('charset')),
						'blog_admin_email'	=> array('label' => __('Admin Email', 'ws-form'), 'value' => get_bloginfo('admin_email')),

						'blog_time' => array('label' => __('Current Time', 'ws-form'), 'value' => date(get_option('time_format'), current_time('timestamp')), 'description' => __('Returns the blog time in the format configured in WordPress.', 'ws-form')),

						'blog_date_custom' => array(

							'label' => __('Custom Date', 'ws-form'),

							'value' => date('Y-m-d H:i:s', current_time('timestamp')),

							'attributes' => array(

								array('id' => 'format', 'required' => false, 'default' => 'm/d/Y H:i:s'),
							),

							'kb_slug' => 'date-formats',

							'description' => __('Returns the blog date and time in a specified format (PHP date format).', 'ws-form')
						),

						'blog_date' => array('label' => __('Current Date', 'ws-form'), 'value' => date(get_option('date_format'), current_time('timestamp')), 'description' => __('Returns the blog date in the format configured in WordPress.', 'ws-form')),
					)
				),

				// Client
				'client'	=>	array(

					'label'		=>__('Client', 'ws-form'),

					'variables'	=> array(

						'client_time' => array('label' => __('Current Time', 'ws-form'), 'limit' => 'in client-side', 'description' => __('Returns the users web browser local time in the format configured in WordPress.', 'ws-form')),

						'client_date_custom' => array(

							'label' => __('Custom Date', 'ws-form'),

							'attributes' => array(

								array('id' => 'format', 'required' => false, 'default' => 'm/d/Y H:i:s'),
							),

							'kb_slug' => 'date-formats',

							'limit' => 'in client-side',

							'description' => __('Returns the users web browser local date and time in a specified format (PHP date format).', 'ws-form')
						),

						'client_date' => array('label' => __('Current Date', 'ws-form'), 'limit' => 'in client-side', 'description' => __('Returns the users web browser local date in the format configured in WordPress.', 'ws-form')),
					)
 				),

				// Server
				'server'	=>	array(

					'label'		=>__('Server', 'ws-form'),

					'variables'	=> array(

						'server_time' => array('label' => __('Current Time', 'ws-form'), 'value' => date(get_option('time_format')), 'description' => __('Returns the server time in the format configured in WordPress.', 'ws-form')),

						'server_date_custom' => array(

							'label' => __('Custom Date', 'ws-form'),

							'value' => date('Y-m-d H:i:s'),

							'attributes' => array(

								array('id' => 'format', 'required' => false, 'default' => 'm/d/Y H:i:s'),
							),

							'kb_slug' => 'date-formats',

							'description' => __('Returns the server date and time in a specified format (PHP date format).', 'ws-form')
						),

						'server_date' => array('label' => __('Current Date', 'ws-form'), 'value' => date(get_option('date_format')), 'description' => __('Returns the server date in the format configured in WordPress.', 'ws-form'))
					)
 				),

				// Form
				'form' 		=> array(

					'label'		=> __('Form', 'ws-form'),

					'variables'	=> array(

						'form_obj_id'		=>	array('label' => __('DOM Selector ID', 'ws-form')),
						'form_label'		=>	array('label' => __('Label', 'ws-form')),
						'form_hash'			=>	array('label' => __('Session ID', 'ws-form')),
						'form_instance_id'	=>	array('label' => __('Instance ID', 'ws-form')),
						'form_id'			=>	array('label' => __('ID', 'ws-form')),
						'form_framework'	=>	array('label' => __('Framework', 'ws-form')),
						'form_checksum'		=>	array('label' => __('Checksum', 'ws-form')),
					)
				),

				// Submit
				'submit' 		=> array(

					'label'		=> __('Submission', 'ws-form'),

					'variables'	=> array(

						'submit_id'			=>	array('label' => __('ID', 'ws-form')),
						'submit_hash'		=>	array('label' => __('Hash', 'ws-form')),
						'submit_user_id'	=>	array('label' => __('User ID', 'ws-form')),
						'submit_admin_url'	=>	array('label' => __('Link to submission in WordPress admin', 'ws-form'))
					)
				),

				// Skin
				'skin'			=> array(

					'label'		=> __('Skin', 'ws-form'),

					'variables' => array(

						// Color
						'skin_color_default'		=>	array('label' => __('Color - Default', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_default')),
						'skin_color_default_inverted'		=>	array('label' => __('Color - Default (Inverted)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_default_inverted')),
						'skin_color_default_light'		=>	array('label' => __('Color - Default (Light)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_default_light')),
						'skin_color_default_lighter'		=>	array('label' => __('Color - Default (Lighter)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_default_lighter')),
						'skin_color_default_lightest'		=>	array('label' => __('Color - Default (Lightest)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_default_lightest')),
						'skin_color_primary'		=>	array('label' => __('Color - Primary', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_primary')),
						'skin_color_secondary'		=>	array('label' => __('Color - Secondary', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_secondary')),
						'skin_color_success'		=>	array('label' => __('Color - Success', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_success')),
						'skin_color_information'		=>	array('label' => __('Color - Information', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_information')),
						'skin_color_warning'		=>	array('label' => __('Color - Warning', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_warning')),
						'skin_color_danger'		=>	array('label' => __('Color - Danger', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_color_danger')),

						// Font
						'skin_font_family'		=>	array('label' => __('Font - Family', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_font_family')),
						'skin_font_size'		=>	array('label' => __('Font - Size', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_font_size')),
						'skin_font_size_large'		=>	array('label' => __('Font - Size (Large)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_font_size_large')),
						'skin_font_size_small'		=>	array('label' => __('Font - Size (Small)', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_font_size_small')),
						'skin_font_weight'		=>	array('label' => __('Font - Weight', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_font_weight')),
						'skin_line_height'		=>	array('label' => __('Line Height', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_line_height')),

						// Border
						'skin_border_width'		=>	array('label' => __('Border - Width', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_border_width')),
						'skin_border_style'		=>	array('label' => __('Border - Style', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_border_style')),
						'skin_border_radius'		=>	array('label' => __('Border - Style', 'ws-form'), 'kb_slug' => 'customize-appearance', 'value' => WS_Form_Common::option_get('skin_border_radius'))
					)
				),
				// Progress
				'progress' 		=> array(

					'label'		=> __('Progress', 'ws-form'),

					'variables'	=> array(

						'progress'						=>	array('label' => __('Number (0 to 100)', 'ws-form'), 'limit' => __('in the Help setting for Progress fields', 'ws-form'), 'kb_slug' => 'progress'),
						'progress_percent'				=>	array('label' => __('Percent (0% to 100%)', 'ws-form'), 'limit' => __('in the Help setting for Progress fields', 'ws-form'), 'kb_slug' => 'progress'),
						'progress_remaining'			=>	array('label' => __('Number Remaining (100 to 0)', 'ws-form'), 'limit' => __('in the Help setting for Progress fields', 'ws-form'), 'kb_slug' => 'progress'),
						'progress_remaining_percent'	=>	array('label' => __('Percent Remaining (100% to 0%)', 'ws-form'), 'limit' => __('in the Help setting for Progress fields', 'ws-form'), 'kb_slug' => 'progress')
					)
				),

				// E-Commerce
				'ecommerce' 	=> array(

					'label'		=> __('E-Commerce', 'ws-form'),

					'variables'	=> array(

						'ecommerce_currency_symbol'		=>	array(

							'label' => __('Currency Symbol', 'ws-form'),

							'value' => $currency_symbol,

							'description' => __('Use this variable to show the current currency symbol.', 'ws-form')
						),

						'ecommerce_field_price'			=>	array(

							'label' => __('Field Value as Price', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
							),

							'description' => __('Use this variable to pull back the value of a price field on your form. For example: <code>#field(123)</code> where \'123\' is the field ID shown in the layout editor. This variable will neatly format a currency value according to your E-Commerce settings. An example output might be: 123.00', 'ws-form')
						)
					)
				),
				// Section
				'section' 	=> array(

					'label'		=> __('Section', 'ws-form'),

					'variables'	=> array(

						'section_row_count'	=>	array(

							'label' => __('Section Row Count', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
							),

							'description' => __('This variable returns the total number of rows in a repeatable section.', 'ws-form')
						),
					)
				),

				// Time
				'seconds' 	=> array(

					'label'		=> __('Seconds', 'ws-form'),

					'variables'	=> array(

						'seconds_epoch' => array('label' => __('Seconds since Epoch', 'ws-form'), 'value' => date('U'), 'description' => __('Returns the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).', 'ws-form')),

						'seconds_minute' => array('label' => __('Seconds in a minute', 'ws-form'), 'value' => '60', 'description' => __('Returns the number of seconds in a minute.', 'ws-form')),

						'seconds_hour' => array('label' => __('Seconds in an hour', 'ws-form'), 'value' => '3600', 'description' => __('Returns the number of seconds in an hour.', 'ws-form')),

						'seconds_day' => array('label' => __('Seconds in a day', 'ws-form'), 'value' => '86400', 'description' => __('Returns the number of seconds in a day.', 'ws-form')),

						'seconds_week' => array('label' => __('Seconds in a week', 'ws-form'), 'value' => '604800', 'description' => __('Returns the number of seconds in a week.', 'ws-form')),

						'seconds_year' => array('label' => __('Seconds in a year', 'ws-form'), 'value' => '31536000', 'description' => __('Returns the number of seconds in a year.', 'ws-form'))
					)
				),
				// Calculated
				'calc' 	=> array(

					'label'		=> __('Calculation', 'ws-form'),

					'variables'	=> array(

						'calc'			=>	array(

							'label' => __('Calculation', 'ws-form'),

							'attributes' => array(

								array('id' => 'calculation', 'required' => false),
							),

							'description' => __('Calculated value.', 'ws-form')
						)
					),

					'priority' => 100
				),
				// Math
				'math' 	=> array(

					'label'		=> __('Math', 'ws-form'),

					'variables'	=> array(

						'abs'			=>	array(

							'label' => __('Absolute', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
							),

							'description' => __('Returns the absolute value of a number.', 'ws-form')
						),

						'ceil'			=>	array(

							'label' => __('Ceiling', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
							),

							'description' => __('Rounds a number up to the next largest whole number.', 'ws-form')
						),

						'cos'			=>	array(

							'label' => __('Cosine', 'ws-form'),

							'attributes' => array(

								array('id' => 'radians', 'required' => false),
							),

							'description' => __('Returns the cosine of a radian number.', 'ws-form')
						),

						'exp'			=>	array(

							'label' => __("Euler's", 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
							),

							'description' => __('Returns E to the power of a number.', 'ws-form')
						),

						'floor'			=>	array(

							'label' => __("Floor", 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
							),

							'description' => __('Returns the largest integer value that is less than or equal to a number.', 'ws-form')
						),

						'log'			=>	array(

							'label' => __('Logarithm', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
							),

							'description' => __('Returns the natural logarithm of a number.', 'ws-form')
						),

						'round'			=>	array(

							'label' => __('Round', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false),
								array('id' => 'decimals', 'required' => false)
							),

							'description' => __('Returns the rounded value of a number.', 'ws-form')
						),

						'sin'			=>	array(

							'label' => __('Sine', 'ws-form'),

							'attributes' => array(

								array('id' => 'radians', 'required' => false)
							),

							'description' => __('Returns the sine of a radian number.', 'ws-form')
						),

						'sqrt'			=>	array(

							'label' => __('Square Root', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'required' => false)
							),

							'description' => __('Returns the square root of the number.', 'ws-form')
						),

						'tan'			=>	array(

							'label' => __('Tangent', 'ws-form'),

							'attributes' => array(

								array('id' => 'radians', 'required' => false)
							),

							'description' => __('Returns the tangent of a radian number.', 'ws-form')
						),

						'avg'			=>	array(

							'label' => __('Average', 'ws-form'),

							'attributes' => array(

								array('id' => 'number', 'recurring' => true)
							),

							'description' => __('Returns the average of all the input numbers.', 'ws-form')
						),

						'pi'			=>	array(

							'label' => __('PI', 'ws-form'),

							'value' => M_PI,

							'description' => __('Returns an approximate value of PI.', 'ws-form')
						),

						'avg'			=>	array(

							'label' => __('Average', 'ws-form'),

							'attributes' => array(

								array('id' => 'number')
							),

							'description' => __('Returns the average of all the input numbers.', 'ws-form')
						)
					),

					'ignore_prefix' => true,

					'priority' => 50
				),

				// Field
				'field' 	=> array(

					'label'		=> __('Field', 'ws-form'),

					'variables'	=> array(

						'field'			=>	array(

							'label' => __('Field Value', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
							),

							'description' => __('Use this variable to pull back the value of a field on your form. For example: <code>#field(123)</code> where \'123\' is the field ID shown in the layout editor.', 'ws-form')
						),
					)
				),

				// Select option text
				'select' 	=> array(

					'label'		=> __('Select', 'ws-form'),

					'variables'	=> array(

						'select_option_text'			=>	array(

							'label' => __('Select Option Text', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
								array('id' => 'delimiter', 'required' => false, 'trim' => false)
							),

							'description' => __('Use this variable to pull back the selected option text of a select field on your form. For example: <code>#select_option_text(123)</code> where \'123\' is the field ID shown in the layout editor.', 'ws-form'),

							'limit' => 'in client-side'
						),
					)
				),

				// Checkbox label
				'checkbox' 	=> array(

					'label'		=> __('Checkbox', 'ws-form'),

					'variables'	=> array(

						'checkbox_label'	=>	array(

							'label' => __('Checkbox Label', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
								array('id' => 'delimiter', 'required' => false, 'trim' => false)
							),

							'description' => __('Use this variable to pull back the label of a checkbox field on your form. For example: <code>#checkbox_label(123)</code> where \'123\' is the field ID shown in the layout editor.', 'ws-form'),

							'limit' => 'in client-side'
						),
					)
				),

				// Radio label
				'radio' 	=> array(

					'label'		=> __('Radio', 'ws-form'),

					'variables'	=> array(

						'radio_label'	=>	array(

							'label' => __('Radio Label', 'ws-form'),

							'attributes' => array(

								array('id' => 'id'),
								array('id' => 'delimiter', 'required' => false, 'trim' => false)
							),

							'description' => __('Use this variable to pull back the label of a radio field on your form. For example: <code>#radio_label(123)</code> where \'123\' is the field ID shown in the layout editor.', 'ws-form'),

							'limit' => 'in client-side'
						),
					)
				),

				// Email
				'email' 	=> array(

					'label'		=> __('Email', 'ws-form'),

					'variables'	=> array(

						'email_subject'			=>	array('label' => __('Subject', 'ws-form'), 'limit' => __('in the Send Email action', 'ws-form'), 'kb_slug' => 'send-email'),
						'email_content_type'	=>	array('label' => __('Content type', 'ws-form'), 'limit' => __('in the Send Email action', 'ws-form'), 'kb_slug' => 'send-email'),
						'email_charset'			=>	array('label' => __('Character set', 'ws-form'), 'limit' => __('in the Send Email action', 'ws-form'), 'kb_slug' => 'send-email'),
						'email_submission'		=>	array(

							'label' => __('Submitted Fields', 'ws-form'),

							'attributes' => array(

								array('id' => 'tab_labels', 'required' => false, 'default' => WS_Form_Common::option_get('action_email_group_labels', 'auto'), 'valid' => array('true', 'false', 'auto')),
								array('id' => 'section_labels', 'required' => false, 'default' => WS_Form_Common::option_get('action_email_section_labels', 'auto'), 'valid' => array('true', 'false', 'auto')),
								array('id' => 'field_labels', 'required' => false, 'default' => WS_Form_Common::option_get('action_email_field_labels', 'true'), 'valid' => array('true', 'false', 'auto')),
								array('id' => 'blank_fields', 'required' => false, 'default' => (WS_Form_Common::option_get('action_email_exclude_empty') ? 'false' : 'true'), 'valid' => array('true', 'false')),
								array('id' => 'static_fields', 'required' => false, 'default' => (WS_Form_Common::option_get('action_email_static_fields') ? 'true' : 'false'), 'valid' => array('true', 'false')),
							),

							'kb_slug' => 'send-email',

							'limit' => __('in the Send Email action', 'ws-form'),

							'description' => __('This variable outputs a list of the fields captured during a submission. You can either use: <code>#email_submission</code> or provide additional parameters to toggle tab labels, section labels, blank fields and static fields (such as text or HTML areas of your form). Specify \'true\' or \'false\' for each parameter, for example: <code>#email_submission(true, true, false, true)</code>', 'ws-form')
						),
						'email_ecommerce'		=>	array(

							'label' => __('E-Commerce Values', 'ws-form'),

							'kb_slug' => 'e-commerce',

							'limit' => __('in the Send Email action', 'ws-form'),

							'description' => __('This variable outputs a list of the e-commerce transaction details such as total, transaction ID and status fields.', 'ws-form')
						),
						'email_tracking'		=>	array('label' => __('Tracking data', 'ws-form'), 'limit' => __('in the Send Email action', 'ws-form'), 'kb_slug' => 'send-email'),
						'email_logo'			=>	array('label' => __('Logo', 'ws-form'), 'value' => $email_logo, 'limit' => __('in the Send Email action', 'ws-form'), 'kb_slug' => 'send-email'),
						'email_pixel'			=>	array('label' => __('Pixel'), 'value' => '<img src="' . WS_FORM_PLUGIN_DIR_URL . 'public/images/email/p.gif" width="100%" height="5" />', 'description' => __('Outputs a transparent gif. We use this to avoid Mac Mail going into dark mode when viewing emails.', 'ws-form'))
					)
				),

				// Query
				'query' 	=> array(

					'label'		=> __('Query Variable', 'ws-form'),

					'variables'	=> array(

						'query_var'		=>	array(

							'label' => __('Variable', 'ws-form'),

							'attributes' => array(

								array('id' => 'variable')
							)
						)
					)
				),

				// Post
				'post' 	=> array(

					'label'		=> __('Post Variable', 'ws-form'),

					'variables'	=> array(

						'post_var'	=>	array(

							'label' => __('Variable', 'ws-form'),

							'attributes' => array(

								array('id' => 'variable')
							)
						)
					)
				),

				// Random Numbers
				'random_number' 	=> array(

					'label'		=> __('Random Numbers', 'ws-form'),

					'variables'	=> array(

						'random_number'	=>	array(

							'label' => __('Random Number', 'ws-form'),

							'attributes' => array(

								array('id' => 'min', 'required' => false, 'default' => 0),
								array('id' => 'max', 'required' => false, 'default' => 100)
							),

							'description' => __('Outputs an integer between the specified minimum and maximum attributes. This function does not generate cryptographically secure values, and should not be used for cryptographic purposes.', 'ws-form'),

							'single_parse' => true
						)
					)
				),

				// Random Strings
				'random_string' 	=> array(

					'label'		=> __('Random Strings', 'ws-form'),

					'variables'	=> array(

						'random_string'	=>	array(

							'label' => __('Random String', 'ws-form'),

							'attributes' => array(

								array('id' => 'length', 'required' => false, 'default' => 32),
								array('id' => 'characters', 'required' => false, 'default' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
							),

							'description' => __('Outputs a string of random characters. Use the length attribute to control how long the string is and use the characters attribute to control which characters are randomly selected. This function does not generate cryptographically secure values, and should not be used for cryptographic purposes.', 'ws-form'),

							'single_parse' => true
						)
					)
				),

				// Character
				'character'	=> array(

					'label'		=> __('Character', 'ws-form'),

					'variables' => array(

						'character_count'	=>	array(

							'label'	=> __('Count', 'ws-form'),
							'description' => __('The total character count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_count_label'	=>	array(

							'label'	=> __('Count Label', 'ws-form'),
							'description' => __("Shows 'character' or 'characters' depending on the character count.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_remaining'	=>	array(

							'label'	=> __('Count Remaining', 'ws-form'),
							'description' => __('If you set a maximum character length for a field, this will show the total remaining character count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_remaining_label'	=>	array(

							'label'	=> __('Count Remaining Label', 'ws-form'),
							'description' => __('If you set a maximum character length for a field, this will show the total remaining character count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_min'	=>	array(

							'label'	=> __('Minimum', 'ws-form'),
							'description' => __('Shows the minimum character length that you set for a field.'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_min_label'	=>	array(

							'label'	=> __('Minimum Label', 'ws-form'),
							'description' => __("Shows 'character' or 'characters' depending on the minimum character length.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_max'	=>	array(

							'label'	=> __('Maximum', 'ws-form'),
							'description' => __('Shows the maximum character length that you set for a field.'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'character_max_label'	=>	array(

							'label'	=> __('Maximum Label', 'ws-form'),
							'description' => __("Shows 'character' or 'characters' depending on the maximum character length.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						)
					)
				),

				// Word
				'word'	=> array(

					'label'		=> __('Word', 'ws-form'),

					'variables' => array(

						'word_count'	=>	array(

							'label'	=> __('Count', 'ws-form'),
							'description' => __('The total word count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_count_label'	=>	array(

							'label'	=> __('Count Label', 'ws-form'),
							'description' => __("Shows 'word' or 'words' depending on the word count.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_remaining'	=>	array(

							'label'	=> __('Count Remaining', 'ws-form'),
							'description' => __('If you set a maximum word length for a field, this will show the total remaining word count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_remaining_label'	=>	array(

							'label'	=> __('Count Remaining Label', 'ws-form'),
							'description' => __('If you set a maximum word length for a field, this will show the total remaining word count.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_min'	=>	array(

							'label'	=> __('Minimum', 'ws-form'),
							'description' => __('Shows the minimum word length that you set for a field.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_min_label'	=>	array(

							'label'	=> __('Minimum Label', 'ws-form'),
							'description' => __("Shows 'word' or 'words' depending on the minimum word length.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_max'	=>	array(

							'label'	=> __('Maximum', 'ws-form'),
							'description' => __('Shows the maximum word length that you set for a field.', 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						),

						'word_max_label'	=>	array(

							'label'	=> __('Maximum Label', 'ws-form'),
							'description' => __("Shows 'word' or 'words' depending on the maximum word length.", 'ws-form'),
							'limit'	=> __('in the Help setting for text based Fields', 'ws-form'),
							'kb_slug' => 'word-and-character-count'
						)
					)
				)
			);

			// Post
			$post = WS_Form_Common::get_post_root();

			$parse_variables['post'] = array(

				'label'		=> __('Post', 'ws-form'),

				'variables'	=> array(

					'post_url_edit'		=>	array('label' => __('Admin URL', 'ws-form'), 'value' => !is_null($post) ? get_edit_post_link($post->ID) : ''),
					'post_url'			=>	array('label' => __('Public URL', 'ws-form'), 'value' => !is_null($post) ? get_permalink($post->ID) : ''),
					'post_type'			=>	array('label' => __('Type', 'ws-form'), 'value' => !is_null($post) ? $post->post_type : ''),
					'post_title'		=>	array('label' => __('Title', 'ws-form'), 'value' => !is_null($post) ? $post->post_title : ''),
					'post_content'		=>	array('label' => __('Content', 'ws-form'), 'value' => !is_null($post) ? $post->post_content : ''),
					'post_excerpt'		=>	array('label' => __('Excerpt', 'ws-form'), 'value' => !is_null($post) ? $post->post_excerpt : ''),
					'post_time'			=>	array('label' => __('Time', 'ws-form'), 'value' => !is_null($post) ? date(get_option('time_format'), strtotime($post->post_date)) : ''),
					'post_id'			=>	array('label' => __('ID', 'ws-form'), 'value' => !is_null($post) ? $post->ID : ''),
					'post_date'			=>	array('label' => __('Date', 'ws-form'), 'value' => !is_null($post) ? date(get_option('date_format'), strtotime($post->post_date)) : ''),

					// http://blog.stevenlevithan.com/archives/date-time-format
					'post_date_custom'	=>	array(

						'label' => __('Post Custom Date', 'ws-form'),

						'value' => !is_null($post) ? date('c', strtotime($post->post_date)) : '',

						'attributes' => array(

							array('id' => 'format', 'required' => false, 'default' => 'F j, Y, g:i a')
						),

						'kb_slug' => 'date-formats'
					),
					'post_meta'			=>	array(

						'label' => __('Meta Value', 'ws-form'),

						'attributes' => array(

							array('id' => 'key')
						),

						'description' => __('Returns the post meta value for the key specified.', 'ws-form'),

						'scope' => array('form_parse')
					)
				)
			);

			// Author
			$post_author_id = !is_null($post) ? $post->post_author : 0;
			$parse_variables['author'] = array(

				'label'		=> __('Author', 'ws-form'),

				'variables'	=> array(

					'author_id'				=>	array('label' => __('ID', 'ws-form'), 'value' => $post_author_id),
					'author_display_name'	=>	array('label' => __('Display Name', 'ws-form'), 'value' => get_the_author_meta('display_name', $post_author_id)),
					'author_first_name'		=>	array('label' => __('First Name', 'ws-form'), 'value' => get_the_author_meta('first_name', $post_author_id)),
					'author_last_name'		=>	array('label' => __('Last Name', 'ws-form'), 'value' => get_the_author_meta('last_name', $post_author_id)),
					'author_nickname'		=>	array('label' => __('Nickname', 'ws-form'), 'value' => get_the_author_meta('nickname', $post_author_id)),
					'author_email'			=>	array('label' => __('Email', 'ws-form'), 'value' => get_the_author_meta('user_email', $post_author_id)),
				)
			);

			// URL
			$parse_variables['url'] = array(

				'label'		=> __('URL', 'ws-form'),

				'variables'	=> array(

					'url_login'				=>	array('label' => __('Login', 'ws-form'), 'value' => wp_login_url()),
					'url_logout'			=>	array('label' => __('Logout', 'ws-form'), 'value' => wp_logout_url()),
					'url_lost_password'				=>	array('label' => __('Login', 'ws-form'), 'value' => wp_lostpassword_url()),
					'url_register'				=>	array('label' => __('Register', 'ws-form'), 'value' => wp_registration_url()),
				)
			);

			// ACF
			if(class_exists('acf')) { 

				$parse_variables['acf'] =  array(

					'label'		=> __('ACF', 'ws-form'),

					'variables'	=> array(

						'acf_repeater_field'	=>	array(

							'label' => __('Repeater Field', 'ws-form'),

							'attributes' => array(

								array('id' => 'parent_field'),
								array('id' => 'sub_field'),
							),

							'description' => __('Used to obtain an ACF repeater field. You can separate parent_fields with commas to access deep variables.', 'ws-form'),

							'scope' => array('form_parse')
						),
					)
				);
			}

			if(!$public) {

				// Tracking
				$tracking_array = self::get_tracking($public);
				$parse_variables['tracking'] = array(

					'label'		=> __('Tracking', 'ws-form'),
					'variables'	=> array()
				);

				foreach($tracking_array as $meta_key => $tracking) {

					$parse_variables['tracking']['variables'][$meta_key] = array('label' => $tracking['label'], 'description' => $tracking['description']);
				}
			}

			// Get e-commerce config
			$ecommerce_config = self::get_ecommerce();

			foreach($ecommerce_config['cart_price_types'] as $meta_key => $cart_price_type) {

				$parse_variables['ecommerce']['variables']['ecommerce_cart_' . $meta_key . '_span'] = array(

					'label' 		=> sprintf('%s (%s)', $cart_price_type['label'], __('Span', 'ws-form')),
					'value' 		=> sprintf('<span data-ecommerce-cart-price-%s>#ecommerce_cart_%1$s</span>', $meta_key),
					'description' 	=> __('Excludes currency symbol. This variable outputs a span that can be used in Text Editor or HTML fields.', 'ws-form')
				);
				$parse_variables['ecommerce']['variables']['ecommerce_cart_' . $meta_key . '_span_currency'] = array(

					'label' 		=> sprintf('%s (%s)', $cart_price_type['label'], __('Span Currency', 'ws-form')),
					'value' 		=> sprintf('<span data-ecommerce-cart-price-%1$s data-ecommerce-price-currency>#ecommerce_cart_%1$s_currency</span>', $meta_key),
					'description' 	=> __('Includes currency symbol. This variable outputs a span that can be used in Text Editor or HTML fields.', 'ws-form')
				);
				$parse_variables['ecommerce']['variables']['ecommerce_cart_' . $meta_key] = array(

					'label' 		=> $cart_price_type['label'],
					'description' 	=> __('Excludes currency symbol. Use this in conditional logic or email templates.', 'ws-form')
				);
				$parse_variables['ecommerce']['variables']['ecommerce_cart_' . $meta_key . '_currency'] = array(

					'label' 		=> sprintf('%s (%s)', $cart_price_type['label'], __('Currency', 'ws-form')),
					'description' 	=> __('Includes currency symbol. Use this in conditional logic or email templates.', 'ws-form')
				);
			}

			foreach($ecommerce_config['meta_keys'] as $meta_key => $meta_key_config) {

				$type = isset($meta_key_config['type']) ? $meta_key_config['type'] : false;

				if($type == 'price') {

					$parse_variables['ecommerce']['variables'][$meta_key . '_span'] = array(

						'label' 		=> sprintf('%s (%s)', $meta_key_config['label'], __('Span', 'ws-form')),
						'value' 		=> sprintf('<span data-%1$s>%1$s</span>', str_replace('_', '-', $meta_key)),
						'description' 	=> __('Excludes currency symbol. This variable outputs a span that can be used in Text Editor or HTML fields', 'ws-form')
					);
					$parse_variables['ecommerce']['variables'][$meta_key . '_span_currency'] = array(

						'label' 		=> sprintf('%s (%s)', $meta_key_config['label'], __('Span Currency', 'ws-form')),
						'value' 		=> sprintf('<span data-%1$s data-ecommerce-price-currency>%1$s_currency</span>', str_replace('_', '-', $meta_key)),
						'description'	=> __('Includes currency symbol. This variable outputs a span that can be used in Text Editor or HTML fields', 'ws-form')
					);
					$parse_variables['ecommerce']['variables'][$meta_key . '_currency'] = array(

						'label'			=> sprintf('%s (%s)', $meta_key_config['label'], __('Currency', 'ws-form')),
						'description' 	=> __('Includes currency symbol. Use this in conditional logic or email templates', 'ws-form')
					);
				}

				$parse_variables['ecommerce']['variables'][$meta_key] = array(

					'label' 		=> $meta_key_config['label'],
					'description' 	=> __('Excludes currency symbol. Use this in conditional logic or email templates.', 'ws-form')
				);
			}
			// User
			$user = WS_Form_Common::get_user();

			$user_id = (($user === false) ? 0 : $user->ID);

			$parse_variables['user'] = array(

				'label'		=> __('User', 'ws-form'),

				'variables'	=> array(

					'user_id' 			=>	array('label' => __('ID', 'ws-form'), 'value' => $user_id, 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_login' 		=>	array('label' => __('Login', 'ws-form'), 'value' => ($user_id > 0) ? $user->user_login : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_nicename' 	=>	array('label' => __('Nice Name', 'ws-form'), 'value' => ($user_id > 0) ? $user->user_nicename : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_email' 		=>	array('label' => __('Email', 'ws-form'), 'value' => ($user_id > 0) ? $user->user_email : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_display_name' =>	array('label' => __('Display Name', 'ws-form'), 'value' => ($user_id > 0) ? $user->display_name : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_url' 			=>	array('label' => __('URL', 'ws-form'), 'value' => ($user_id > 0) ? $user->user_url : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_registered' 	=>	array('label' => __('Registration Date', 'ws-form'), 'value' => ($user_id > 0) ? $user->user_registered : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_first_name'	=>	array('label' => __('First Name', 'ws-form'), 'value' => ($user_id > 0) ? get_user_meta($user_id, 'first_name', true) : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_last_name'	=>	array('label' => __('Last Name', 'ws-form'), 'value' => ($user_id > 0) ? get_user_meta($user_id, 'last_name', true) : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_bio'			=>	array('label' => __('Bio', 'ws-form'), 'value' => ($user_id > 0) ? get_user_meta($user_id, 'description', true) : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_nickname' 	=>	array('label' => __('Nickname', 'ws-form'), 'value' => ($user_id > 0) ? get_user_meta($user_id, 'nickname', true) : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_admin_color' 	=>	array('label' => __('Admin Color', 'ws-form'), 'value' => ($user_id > 0) ? get_user_meta($user_id, 'admin_color', true) : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_lost_password_key' => array('label' => __('Lost Password Key', 'ws-form'), 'value' => ($user_id > 0) ? $user->lost_password_key : '', 'limit' => __('if a user is currently signed in', 'ws-form')),
					'user_lost_password_url' => array(

						'label'			=> __('Lost Password URL', 'ws-form'),
						'attributes'	=> array(

							array('id' => 'path', 'required' => false, 'default' => '')
						),
						'limit' => __('if a user is currently signed in', 'ws-form')
					),
					'user_meta'			=>	array(

						'label' => __('Meta Value', 'ws-form'),

						'attributes' => array(

							array('id' => 'key')
						),

						'description' => __('Returns the user meta value for the key specified.', 'ws-form'),

						'scope' => array('form_parse')
					)
				)
			);

			// Search
			$parse_variables['search'] = array(

				'label'		=> __('Search', 'ws-form'),

				'variables'	=> array(

					'search_query' => array('label' => __('Query', 'ws-form'), 'value' => get_search_query())
				)
			);

			// Apply filter
			$parse_variables = apply_filters('wsf_config_parse_variables', $parse_variables);

			// Public - Optimize
			if($public) {

				$parameters_exclude = array('label', 'description', 'limit', 'kb_slug');

				foreach($parse_variables as $variable_group => $variable_group_config) {

					foreach($variable_group_config['variables'] as $variable => $variable_config) {

						unset($parse_variables[$variable_group]['label']);

						foreach($parameters_exclude as $parameter_exclude) {

							if(isset($parse_variables[$variable_group]['variables'][$variable][$parameter_exclude])) {

								unset($parse_variables[$variable_group]['variables'][$variable][$parameter_exclude]);
							}
						}
					}
				}
			}

			// Cache
			self::$parse_variables[$public] = $parse_variables;

			return $parse_variables;
		}

		// Parse variable
		public static function get_parse_variable_help($form_id = 0, $public = true, $group = false, $group_first = false) {

			// Check cache
			if(
				isset(self::$parse_variable_help[$public]) &&
				isset(self::$parse_variable_help[$public][$group])

			) { return self::$parse_variable_help[$public][$group]; }

			$parse_variable_help = array();

			// Get admin variables
			$parse_variables_config = self::get_parse_variables($public);

			// Get all parse variables
			$parse_variables = [];

			foreach($parse_variables_config as $parse_variable_group_id => $parse_variable_group) {

				if(!isset($parse_variable_group['label'])) { continue; }

				if(($group !== false) && (strpos($group, $parse_variable_group_id) === false)) { continue; }

				$group_label = $parse_variable_group['label'];

				foreach($parse_variable_group['variables'] as $parse_variable_key => $parse_variables_single) {

					$parse_variables_single['group_id'] = $parse_variable_group_id;
					$parse_variables_single['group_label'] = $group_label;
					$parse_variables_single['key'] = $parse_variable_key;
					$parse_variables[] = $parse_variables_single;
				}
			}

			// Sort parse variables
			uasort($parse_variables, function ($parse_variable_1, $parse_variable_2) use ($group_first) {

				if($parse_variable_1['group_label'] == $parse_variable_2['group_label']) {

					if($parse_variable_1['label'] == $parse_variable_2['label']) return 0;
					return $parse_variable_1['label'] < $parse_variable_2['label'] ? -1 : 1;
				}

				if($group_first !== false) {

					if($parse_variable_1['group_label'] == $group_first) { return -1; }
					if($parse_variable_2['group_label'] == $group_first) { return 1; }
				}

				return $parse_variable_1['group_label'] < $parse_variable_2['group_label'] ? -1 : 1;
			});

			// Process variables
			foreach($parse_variables as $parse_variable) {

				if(!isset($parse_variable['label'])) { continue; }

				$parse_variable_key = $parse_variable['key'];

				// Has attributes?
				if(isset($parse_variable['attributes'])) {

					// Functions
					$attributes_text = [];
					$attributes_value = [];
					foreach($parse_variable['attributes'] as $parse_variable_attribute) {

						$parse_variable_attribute_id = $parse_variable_attribute['id'];
						$parse_variable_attribute_required = isset($parse_variable_attribute['required']) ? $parse_variable_attribute['required'] : false;
						$parse_variable_attribute_default = isset($parse_variable_attribute['default']) ? $parse_variable_attribute['default'] : false;

						$attributes_text[] = $parse_variable_attribute_id . ($parse_variable_attribute_required ? '*' : '');

						$attributes_value[] = $parse_variable_attribute_id;
					}

					$value = $parse_variable_key . '(' . implode(', ', $attributes_value) . ')';
					$parse_variable_help_single = array('text' => $parse_variable['label'], 'value' => '#' . $value, 'group_id' => $parse_variable['group_id'], 'group_label' => $parse_variable['group_label'], 'description' => isset($parse_variable['description']) ? $parse_variable['description'] : '');

					if(isset($parse_variable['kb_slug'])) { $parse_variable_help_single['kb_slug'] = $parse_variable['kb_slug']; }

					if(isset($parse_variable['limit'])) { $parse_variable_help_single['limit'] = $parse_variable['limit']; }

					self::parse_variable_help_add($parse_variable_help, $parse_variable_help_single);

				} else {

					// No attributes
					$value = $parse_variable_key;
					$parse_variable_help_single = array('text' => $parse_variable['label'], 'value' => '#' . $value, 'group_id' => $parse_variable['group_id'], 'group_label' => $parse_variable['group_label'], 'description' => isset($parse_variable['description']) ? $parse_variable['description'] : '');

					if(isset($parse_variable['kb_slug'])) { $parse_variable_help_single['kb_slug'] = $parse_variable['kb_slug']; }

					if(isset($parse_variable['limit'])) { $parse_variable_help_single['limit'] = $parse_variable['limit']; }

					self::parse_variable_help_add($parse_variable_help, $parse_variable_help_single);
				}
			}

			// Apply filter
			$parse_variable_help = apply_filters('wsf_config_parse_variable_help', $parse_variable_help);

			// Cache
			self::$parse_variable_help[$public][$group] = $parse_variable_help;

			return $parse_variable_help;
		}

		// Parse variables help add
		public static function parse_variable_help_add(&$parse_variable_help, $parse_variable_help_single) {

			$passthrough_attributes = array('description', 'limit', 'kb_slug');

			// Passthrough attributes
			foreach($passthrough_attributes as $passthrough_attribute) {

				if(isset($parse_variable[$passthrough_attribute])) { $parse_variable_help_single[$passthrough_attribute] = $parse_variable[$passthrough_attribute]; }

			}

			$parse_variable_help[] = $parse_variable_help_single;
		}

		// Calc
		public static function get_calc() {

			// Check cache
			if(self::$calc !== false) { return self::$calc; }

			$calc = array(

				// Row 1
				array(

					array('type' => 'select', 'source' => 'field', 'colspan' => 2, 'label' => __('Insert Field', 'ws-form'), 'action' => 'insert-select'),
					array('type' => 'button', 'label' => __('del', 'ws-form'), 'class' => 'wsf-button-danger', 'title' => __('Delete', 'ws-form'), 'action' => 'delete'),
					array('type' => 'button', 'label' => __('AC', 'ws-form'), 'class' => 'wsf-button-danger', 'title' => __('All Clear', 'ws-form'), 'action' => 'clear'),
				),

				// Row 2
				array(

					array('type' => 'button', 'label' => '(', 'title' => __('Opening Parentheses', 'ws-form'), 'action' => 'insert', 'insert' => '('),
					array('type' => 'button', 'label' => ')', 'title' => __('Closing Parentheses', 'ws-form'), 'action' => 'insert', 'insert' => ')'),
					array('type' => 'button', 'label' => ',', 'title' => __('Percentage', 'ws-form'), 'action' => 'insert', 'insert' => ','),
					array('type' => 'select', 'source' => 'variables', 'label' => 'f', 'class' => 'wsf-button-primary', 'title' => __('Variables', 'ws-form'), 'action' => 'insert-select-highlight-parameters', 'variables_group_id' => 'math'),
				),

				// Row 3
				array(

					array('type' => 'button', 'label' => '7', 'action' => 'insert', 'insert' => '7'),
					array('type' => 'button', 'label' => '8', 'action' => 'insert', 'insert' => '8'),
					array('type' => 'button', 'label' => '9', 'action' => 'insert', 'insert' => '9'),
					array('type' => 'button', 'label' => '/', 'class' => 'wsf-button-primary', 'title' => __('Divide', 'ws-form'), 'action' => 'insert', 'insert' => '/'),
				),

				// Row 4
				array(

					array('type' => 'button', 'label' => '4', 'action' => 'insert', 'insert' => '4'),
					array('type' => 'button', 'label' => '5', 'action' => 'insert', 'insert' => '5'),
					array('type' => 'button', 'label' => '6', 'action' => 'insert', 'insert' => '6'),
					array('type' => 'button', 'label' => '*', 'class' => 'wsf-button-primary', 'title' => __('Multiply', 'ws-form'), 'action' => 'insert', 'insert' => '*'),
				),

				// Row 5
				array(

					array('type' => 'button', 'label' => '1', 'action' => 'insert', 'insert' => '1'),
					array('type' => 'button', 'label' => '2', 'action' => 'insert', 'insert' => '2'),
					array('type' => 'button', 'label' => '3', 'action' => 'insert', 'insert' => '3'),
					array('type' => 'button', 'label' => '-', 'class' => 'wsf-button-primary', 'title' => __('Subtract', 'ws-form'), 'action' => 'insert', 'insert' => '-'),
				),

				// Row 6
				array(

					array('type' => 'button', 'label' => '0', 'colspan' => 2, 'action' => 'insert', 'insert' => '0'),
					array('type' => 'button', 'label' => '.', 'title' => __('Decimal', 'ws-form'), 'action' => 'insert', 'insert' => '.'),
					array('type' => 'button', 'label' => '+', 'class' => 'wsf-button-primary', 'title' => __('Add', 'ws-form'), 'action' => 'insert', 'insert' => '+'),
				)
			);

			// Apply filter
			$calc = apply_filters('wsf_config_calc', $calc);

			// Cache
			self::$calc = $calc;

			return $calc;
		}

		// System report
		public static function get_system() {

			global $wpdb, $required_mysql_version;

			// Get MySQL max_allowed_packet
			$mysql_max_allowed_packet = $wpdb->get_var('SELECT @@global.max_allowed_packet;');
			if(is_null($mysql_max_allowed_packet)) { $mysql_max_allowed_packet = 0; }

			$ws_form_encryption = new WS_Form_Encryption();
			$system = array(

				// WS Form
				'ws_form' => array(

					'label'		=> WS_FORM_NAME_PRESENTABLE,
					'variables'	=> array(

						'version'		=> array('label' => __('Version', 'ws-form'), 'value' => WS_FORM_VERSION),
						'edition'		=> array('label' => __('Edition', 'ws-form'), 'value' => WS_FORM_EDITION, 'type' => 'edition'),
						'framework'		=> array('label' => __('Framework', 'ws-form'), 'value' => WS_Form_Common::option_get('framework')),
						'encryption_status'	=> array('label' => __('Encryption', 'ws-form'), 'value' => $ws_form_encryption->can_encrypt, 'type' => 'boolean'),
					)
				),

				// WordPress
				'wordpress' => array(

					'label'		=> __('WordPress', 'ws-form'),
					'variables'	=> array(

						'version' 			=> array('label' => __('Version', 'ws-form'), 'value' => get_bloginfo('version'), 'valid' => (version_compare(get_bloginfo('version'), WS_FORM_MIN_VERSION_WORDPRESS) >= 0), 'min' => WS_FORM_MIN_VERSION_WORDPRESS),
						'multisite'			=> array('label' => __('Multisite Enabled', 'ws-form'), 'value' => is_multisite(), 'type' => 'boolean'),
						'home_url' 			=> array('label' => __('Home URL', 'ws-form'), 'value' => get_home_url(), 'type' => 'url'),
						'site_url' 			=> array('label' => __('Site URL', 'ws-form'), 'value' => get_site_url(), 'type' => 'url'),
						'theme_active' 		=> array('label' => __('Theme', 'ws-form'), 'value' => wp_get_theme(), 'type' => 'theme'),
						'plugins_active' 	=> array('label' => __('Plugins', 'ws-form'), 'value' => get_option('active_plugins', array()), 'type' => 'plugins'),
						'memory_limit'		=> array('label' => __('Memory Limit', 'ws-form'), 'value' => (defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : 0)),
						'debug'				=> array('label' => __('Debug', 'ws-form'), 'value' => (defined('WP_DEBUG') ? WP_DEBUG : false), 'type' => 'boolean'),
						'locale'			=> array('label' => __('Locale', 'ws-form'), 'value' => get_locale()),
						'max_upload_size'	=> array('label' => __('Max Upload Size', 'ws-form'), 'value' => wp_max_upload_size(), 'type' => 'size'),
					)
				),

				// Web Server
				'web_server' => array(

					'label'		=>	__('Web Server', 'ws-form'),
					'variables'	=> array(

						'name'				=> array('label' => __('Name', 'ws-form'), 'value' => WS_Form_Common::get_http_env('SERVER_SOFTWARE')),
						'ip'				=> array('label' => __('IP', 'ws-form'), 'value' => WS_Form_Common::get_http_env(array('SERVER_ADDR', 'LOCAL_ADDR'))),
						'post_max_size'	=> array('label' => __('Max Upload Size', 'ws-form'), 'value' => ini_get('post_max_size')),
						'max_input_vars'	=> array('label' => __('Max Input Variables', 'ws-form'), 'value' => ini_get('max_input_vars'), 'valid' => (ini_get('max_input_vars') >= WS_FORM_MIN_INPUT_VARS), 'min' => WS_FORM_MIN_INPUT_VARS),
						'max_execution_time'	=> array('label' => __('Max Execution Time', 'ws-form'), 'value' => ini_get('max_execution_time'), 'suffix' => __(' seconds', 'ws-form')),
					)
				),

				// SMTP
				'smtp' => array(

					'label'		=>	__('SMTP', 'ws-form'),
					'variables'	=> array(

						'smtp'				=> array('label' => __('SMTP Hostname', 'ws-form'), 'value' => ini_get('SMTP')),
						'smtp_port'			=> array('label' => __('SMTP Port', 'ws-form'), 'value' => ini_get('smtp_port')),
					)
				),

				// PHP
				'php' => array(

					'label'		=>	__('PHP', 'ws-form'),
					'variables'	=> array(

						'version'				=> array('label' => __('Version', 'ws-form'), 'value' => phpversion(), 'valid' => (version_compare(phpversion(), WS_FORM_MIN_VERSION_PHP) >= 0), 'min' => WS_FORM_MIN_VERSION_PHP),
						'curl'					=> array('label' => __('CURL Installed', 'ws-form'), 'value' => (function_exists('curl_init') && function_exists('curl_setopt')), 'type' => 'boolean', 'valid' => true),
						'suhosin'				=> array('label' => __('SUHOSIN Extension Loaded', 'ws-form'), 'value' => extension_loaded('suhosin'), 'type' => 'boolean'),
						'date_default_timezone'	=> array('label' => __('Default Timezone', 'ws-form'), 'value' => date_default_timezone_get()),
					)
				),

				// MySQL
				'mysql' => array(

					'label'		=>	__('MySQL', 'ws-form'),
					'variables'	=> array(

						'version'	=> array('label' => __('Version', 'ws-form'), 'value' => $wpdb->db_version(), 'valid' => version_compare($wpdb->db_version(), $required_mysql_version, '>'), 'min' => $required_mysql_version),
						'max_allowed_packet' => array('label' => __('Max Allowed Packet', 'ws-form'), 'value' => $mysql_max_allowed_packet, 'type' => 'size', 'valid' => ($mysql_max_allowed_packet >= WS_FORM_MIN_MYSQL_MAX_ALLOWED_PACKET), 'min' => '4 MB')
					)
				)
			);

			// License key
			$license_key = WS_Form_Common::option_get('license_key', '') ;
			$system['ws_form']['variables']['license_key'] = array('label' => __('License Key', 'ws-form'), 'value' => ($license_key != '') ? $license_key : __('Unlicensed', 'ws-form'));

			// License activated
			$license_activated = WS_Form_Common::option_get('license_activated', '');
			$system['ws_form']['variables']['license_activated'] = array('label' => __('License Activated', 'ws-form'), 'value' => $license_activated, 'type' => 'boolean');

			// License expires
			$license_expires = WS_Form_Common::option_get('license_expires', '');
			$system['ws_form']['variables']['license_expires'] = array('label' => __('License Expires', 'ws-form'), 'value' => $license_expires, 'type' => 'date');

			// Apply filter
			$system = apply_filters('wsf_config_system', $system);

			return $system;
		}

		// Javascript
		public static function get_external() {

			// CDN or local source?
			$jquery_source = WS_Form_Common::option_get('jquery_source', 'cdn');

			$external = array(

				// Signature Pad - v2.3.2
				'signature_pad_js'	=> (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/js/external/signature_pad.min.js?ver=2.3.2' :
					'https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.js'
				),

				// Date Time Picker - v2.4.5
				'datetimepicker_js'	=> (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/js/external/jquery.datetimepicker.min.js?ver=2.4.5' :
					'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.4.5/jquery.datetimepicker.min.js'
				),

				'datetimepicker_css'	=> (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/css/external/jquery.datetimepicker.min.css?ver=2.4.5' :
					'https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.4.5/jquery.datetimepicker.min.css'
				),

				// MiniColors - v2.3.2
				'minicolors_js'	=> (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/js/external/jquery.minicolors.min.js?ver=2.3.2' :
					'https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.2/jquery.minicolors.min.js'
				),

				'minicolors_css' => (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/css/external/jquery.minicolors.min.css?ver=2.3.2' :
					'https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.2/jquery.minicolors.min.css'
				),

				// Password Strength Meter (WordPress admin file)
				'zxcvbn'					=> WS_FORM_PLUGIN_INCLUDES .'js/zxcvbn.min.js',
				'password_strength_meter'	=> WS_FORM_PLUGIN_DIR_URL . 'public/js/wp/password-strength-meter.min.js',
				// Input mask bundle - v5.0.3
				'inputmask_js' => (($jquery_source == 'local') ? 

					WS_FORM_PLUGIN_DIR_URL . 'public/js/external/jquery.inputmask.min.js?ver=5.0.3' :
					'https://cdn.jsdelivr.net/gh/RobinHerbots/jquery.inputmask@5.0.3/dist/jquery.inputmask.min.js'
				)
			);

			// Apply filter
			$external = apply_filters('wsf_config_external', $external);

			return $external;
		}

		public static function get_currencies() {

			$currencies = array(

				'AFN' => array(
					'symbol' => 'Af',
					'code' => 'AFN',
					'name' => 'Afghanistan Afghani',
				) ,
				'ALL' => array(
					'symbol' => 'Lek',
					'code' => 'ALL',
					'name' => 'Albania Lek',
				) ,
				'ARS' => array(
					'symbol' => '$',
					'code' => 'ARS',
					'name' => 'Argentina Peso',
				) ,
				'AWG' => array(
					'symbol' => 'ƒ',
					'code' => 'AWG',
					'name' => 'Aruba Guilder',
				) ,
				'AUD' => array(
					'symbol' => '$',
					'code' => 'AUD',
					'name' => 'Australia Dollar',
				) ,
				'AZN' => array(
					'symbol' => 'ман',
					'code' => 'AZN',
					'name' => 'Azerbaijan New Manat',
				) ,
				'BSD' => array(
					'symbol' => '$',
					'code' => 'BSD',
					'name' => 'Bahamas Dollar',
				) ,
				'BBD' => array(
					'symbol' => '$',
					'code' => 'BBD',
					'name' => 'Barbados Dollar',
				) ,
				'BDT' => array(
					'symbol' => '৳',
					'code' => 'BDT',
					'name' => 'Bangladeshi taka',
				) ,
				'BYR' => array(
					'symbol' => 'p.',
					'code' => 'BYR',
					'name' => 'Belarus Ruble',
				) ,
				'BZD' => array(
					'symbol' => 'BZ$',
					'code' => 'BZD',
					'name' => 'Belize Dollar',
				) ,
				'BMD' => array(
					'symbol' => '$',
					'code' => 'BMD',
					'name' => 'Bermuda Dollar',
				) ,
				'BOB' => array(
					'symbol' => '$b',
					'code' => 'BOB',
					'name' => 'Bolivia Boliviano',
				) ,
				'BAM' => array(
					'symbol' => 'KM',
					'code' => 'BAM',
					'name' => 'Bosnia and Herzegovina Convertible Marka',
				) ,
				'BWP' => array(
					'symbol' => 'P',
					'code' => 'BWP',
					'name' => 'Botswana Pula',
				) ,
				'BGN' => array(
					'symbol' => 'лв',
					'code' => 'BGN',
					'name' => 'Bulgaria Lev',
				) ,
				'BRL' => array(
					'symbol' => 'R$',
					'code' => 'BRL',
					'name' => 'Brazil Real',
				) ,
				'BND' => array(
					'symbol' => '$',
					'code' => 'BND',
					'name' => 'Brunei Darussalam Dollar',
				) ,
				'KHR' => array(
					'symbol' => '៛',
					'code' => 'KHR',
					'name' => 'Cambodia Riel',
				) ,
				'CAD' => array(
					'symbol' => '$',
					'code' => 'CAD',
					'name' => 'Canada Dollar',
				) ,
				'KYD' => array(
					'symbol' => '$',
					'code' => 'KYD',
					'name' => 'Cayman Islands Dollar',
				) ,
				'CLP' => array(
					'symbol' => '$',
					'code' => 'CLP',
					'name' => 'Chile Peso',
				) ,
				'CNY' => array(
					'symbol' => '¥',
					'code' => 'CNY',
					'name' => 'China Yuan Renminbi',
				) ,
				'COP' => array(
					'symbol' => '$',
					'code' => 'COP',
					'name' => 'Colombia Peso',
				) ,
				'CRC' => array(
					'symbol' => '₡',
					'code' => 'CRC',
					'name' => 'Costa Rica Colon',
				) ,
				'HRK' => array(
					'symbol' => 'kn',
					'code' => 'HRK',
					'name' => 'Croatia Kuna',
				) ,
				'CUP' => array(
					'symbol' => '⃌',
					'code' => 'CUP',
					'name' => 'Cuba Peso',
				) ,
				'CZK' => array(
					'symbol' => 'Kč',
					'code' => 'CZK',
					'name' => 'Czech Republic Koruna',
				) ,
				'DKK' => array(
					'symbol' => 'kr',
					'code' => 'DKK',
					'name' => 'Denmark Krone',
				) ,
				'DOP' => array(
					'symbol' => 'RD$',
					'code' => 'DOP',
					'name' => 'Dominican Republic Peso',
				) ,
				'XCD' => array(
					'symbol' => '$',
					'code' => 'XCD',
					'name' => 'East Caribbean Dollar',
				) ,
				'EGP' => array(
					'symbol' => '£',
					'code' => 'EGP',
					'name' => 'Egypt Pound',
				) ,
				'SVC' => array(
					'symbol' => '$',
					'code' => 'SVC',
					'name' => 'El Salvador Colon',
				) ,
				'EEK' => array(
					'symbol' => '',
					'code' => 'EEK',
					'name' => 'Estonia Kroon',
				) ,
				'EUR' => array(
					'symbol' => '€',
					'code' => 'EUR',
					'name' => 'Euro Member Countries',
				) ,
				'FKP' => array(
					'symbol' => '£',
					'code' => 'FKP',
					'name' => 'Falkland Islands (Malvinas) Pound',
				) ,
				'FJD' => array(
					'symbol' => '$',
					'code' => 'FJD',
					'name' => 'Fiji Dollar',
				) ,
				'GHC' => array(
					'symbol' => '',
					'code' => 'GHC',
					'name' => 'Ghana Cedis',
				) ,
				'GIP' => array(
					'symbol' => '£',
					'code' => 'GIP',
					'name' => 'Gibraltar Pound',
				) ,
				'GTQ' => array(
					'symbol' => 'Q',
					'code' => 'GTQ',
					'name' => 'Guatemala Quetzal',
				) ,
				'GGP' => array(
					'symbol' => '',
					'code' => 'GGP',
					'name' => 'Guernsey Pound',
				) ,
				'GYD' => array(
					'symbol' => '$',
					'code' => 'GYD',
					'name' => 'Guyana Dollar',
				) ,
				'HNL' => array(
					'symbol' => 'L',
					'code' => 'HNL',
					'name' => 'Honduras Lempira',
				) ,
				'HKD' => array(
					'symbol' => '$',
					'code' => 'HKD',
					'name' => 'Hong Kong Dollar',
				) ,
				'HUF' => array(
					'symbol' => 'Ft',
					'code' => 'HUF',
					'name' => 'Hungary Forint',
				) ,
				'ISK' => array(
					'symbol' => 'kr',
					'code' => 'ISK',
					'name' => 'Iceland Krona',
				) ,
				'INR' => array(
					'symbol' => '₹',
					'code' => 'INR',
					'name' => 'India Rupee',
				) ,
				'IDR' => array(
					'symbol' => 'Rp',
					'code' => 'IDR',
					'name' => 'Indonesia Rupiah',
				) ,
				'IRR' => array(
					'symbol' => '﷼',
					'code' => 'IRR',
					'name' => 'Iran Rial',
				) ,
				'IMP' => array(
					'symbol' => '',
					'code' => 'IMP',
					'name' => 'Isle of Man Pound',
				) ,
				'ILS' => array(
					'symbol' => '₪',
					'code' => 'ILS',
					'name' => 'Israel Shekel',
				) ,
				'JMD' => array(
					'symbol' => 'J$',
					'code' => 'JMD',
					'name' => 'Jamaica Dollar',
				) ,
				'JPY' => array(
					'symbol' => '¥',
					'code' => 'JPY',
					'name' => 'Japan Yen',
				) ,
				'JEP' => array(
					'symbol' => '£',
					'code' => 'JEP',
					'name' => 'Jersey Pound',
				) ,
				'KZT' => array(
					'symbol' => 'лв',
					'code' => 'KZT',
					'name' => 'Kazakhstan Tenge',
				) ,
				'KPW' => array(
					'symbol' => '₩',
					'code' => 'KPW',
					'name' => 'Korea (North) Won',
				) ,
				'KRW' => array(
					'symbol' => '₩',
					'code' => 'KRW',
					'name' => 'Korea (South) Won',
				) ,
				'KGS' => array(
					'symbol' => 'лв',
					'code' => 'KGS',
					'name' => 'Kyrgyzstan Som',
				) ,
				'LAK' => array(
					'symbol' => '₭',
					'code' => 'LAK',
					'name' => 'Laos Kip',
				) ,
				'LVL' => array(
					'symbol' => 'Ls',
					'code' => 'LVL',
					'name' => 'Latvia Lat',
				) ,
				'LBP' => array(
					'symbol' => '£',
					'code' => 'LBP',
					'name' => 'Lebanon Pound',
				) ,
				'LRD' => array(
					'symbol' => '$',
					'code' => 'LRD',
					'name' => 'Liberia Dollar',
				) ,
				'LTL' => array(
					'symbol' => 'Lt',
					'code' => 'LTL',
					'name' => 'Lithuania Litas',
				) ,
				'MKD' => array(
					'symbol' => 'ден',
					'code' => 'MKD',
					'name' => 'Macedonia Denar',
				) ,
				'MYR' => array(
					'symbol' => 'RM',
					'code' => 'MYR',
					'name' => 'Malaysia Ringgit',
				) ,
				'MUR' => array(
					'symbol' => '₨',
					'code' => 'MUR',
					'name' => 'Mauritius Rupee',
				) ,
				'MXN' => array(
					'symbol' => '$',
					'code' => 'MXN',
					'name' => 'Mexico Peso',
				) ,
				'MNT' => array(
					'symbol' => '₮',
					'code' => 'MNT',
					'name' => 'Mongolia Tughrik',
				) ,
				'MZN' => array(
					'symbol' => 'MT',
					'code' => 'MZN',
					'name' => 'Mozambique Metical',
				) ,
				'NAD' => array(
					'symbol' => '$',
					'code' => 'NAD',
					'name' => 'Namibia Dollar',
				) ,
				'NPR' => array(
					'symbol' => '₨',
					'code' => 'NPR',
					'name' => 'Nepal Rupee',
				) ,
				'ANG' => array(
					'symbol' => 'ƒ',
					'code' => 'ANG',
					'name' => 'Netherlands Antilles Guilder',
				) ,
				'NZD' => array(
					'symbol' => '$',
					'code' => 'NZD',
					'name' => 'New Zealand Dollar',
				) ,
				'NIO' => array(
					'symbol' => 'C$',
					'code' => 'NIO',
					'name' => 'Nicaragua Cordoba',
				) ,
				'NGN' => array(
					'symbol' => '₦',
					'code' => 'NGN',
					'name' => 'Nigeria Naira',
				) ,
				'NOK' => array(
					'symbol' => 'kr',
					'code' => 'NOK',
					'name' => 'Norway Krone',
				) ,
				'OMR' => array(
					'symbol' => '﷼',
					'code' => 'OMR',
					'name' => 'Oman Rial',
				) ,
				'PKR' => array(
					'symbol' => '₨',
					'code' => 'PKR',
					'name' => 'Pakistan Rupee',
				) ,
				'PAB' => array(
					'symbol' => 'B/.',
					'code' => 'PAB',
					'name' => 'Panama Balboa',
				) ,
				'PYG' => array(
					'symbol' => 'Gs',
					'code' => 'PYG',
					'name' => 'Paraguay Guarani',
				) ,
				'PEN' => array(
					'symbol' => 'S/.',
					'code' => 'PEN',
					'name' => 'Peru Nuevo Sol',
				) ,
				'PHP' => array(
					'symbol' => '₱',
					'code' => 'PHP',
					'name' => 'Philippines Peso',
				) ,
				'PLN' => array(
					'symbol' => 'zł',
					'code' => 'PLN',
					'name' => 'Poland Zloty',
				) ,
				'QAR' => array(
					'symbol' => '﷼',
					'code' => 'QAR',
					'name' => 'Qatar Riyal',
				) ,
				'RON' => array(
					'symbol' => 'lei',
					'code' => 'RON',
					'name' => 'Romania New Leu',
				) ,
				'RUB' => array(
					'symbol' => 'руб',
					'code' => 'RUB',
					'name' => 'Russia Ruble',
				) ,
				'SHP' => array(
					'symbol' => '£',
					'code' => 'SHP',
					'name' => 'Saint Helena Pound',
				) ,
				'SAR' => array(
					'symbol' => '﷼',
					'code' => 'SAR',
					'name' => 'Saudi Arabia Riyal',
				) ,
				'RSD' => array(
					'symbol' => 'Дин.',
					'code' => 'RSD',
					'name' => 'Serbia Dinar',
				) ,
				'SCR' => array(
					'symbol' => '₨',
					'code' => 'SCR',
					'name' => 'Seychelles Rupee',
				) ,
				'SGD' => array(
					'symbol' => '$',
					'code' => 'SGD',
					'name' => 'Singapore Dollar',
				) ,
				'SBD' => array(
					'symbol' => '$',
					'code' => 'SBD',
					'name' => 'Solomon Islands Dollar',
				) ,
				'SOS' => array(
					'symbol' => 'S',
					'code' => 'SOS',
					'name' => 'Somalia Shilling',
				) ,
				'ZAR' => array(
					'symbol' => 'R',
					'code' => 'ZAR',
					'name' => 'South Africa Rand',
				) ,
				'LKR' => array(
					'symbol' => '₨',
					'code' => 'LKR',
					'name' => 'Sri Lanka Rupee',
				) ,
				'SEK' => array(
					'symbol' => 'kr',
					'code' => 'SEK',
					'name' => 'Sweden Krona',
				) ,
				'CHF' => array(
					'symbol' => 'CHF',
					'code' => 'CHF',
					'name' => 'Switzerland Franc',
				) ,
				'SRD' => array(
					'symbol' => '$',
					'code' => 'SRD',
					'name' => 'Suriname Dollar',
				) ,
				'SYP' => array(
					'symbol' => '£',
					'code' => 'SYP',
					'name' => 'Syria Pound',
				) ,
				'TWD' => array(
					'symbol' => 'NT$',
					'code' => 'TWD',
					'name' => 'Taiwan New Dollar',
				) ,
				'THB' => array(
					'symbol' => '฿',
					'code' => 'THB',
					'name' => 'Thailand Baht',
				) ,
				'TTD' => array(
					'symbol' => '$',
					'code' => 'TTD',
					'name' => 'Trinidad and Tobago Dollar',
				) ,
				'TRY' => array(
					'symbol' => '₤',
					'code' => 'TRY',
					'name' => 'Turkey Lira',
				) ,
				'TRL' => array(
					'symbol' => '',
					'code' => 'TRL',
					'name' => 'Turkey Lira',
				) ,
				'TVD' => array(
					'symbol' => '',
					'code' => 'TVD',
					'name' => 'Tuvalu Dollar',
				) ,
				'UAH' => array(
					'symbol' => '₴',
					'code' => 'UAH',
					'name' => 'Ukraine Hryvna',
				) ,
				'GBP' => array(
					'symbol' => '£',
					'code' => 'GBP',
					'name' => 'United Kingdom Pound',
				) ,
				'USD' => array(
					'symbol' => '$',
					'code' => 'USD',
					'name' => 'United States Dollar',
				) ,
				'UYU' => array(
					'symbol' => '$U',
					'code' => 'UYU',
					'name' => 'Uruguay Peso',
				) ,
				'UZS' => array(
					'symbol' => 'лв',
					'code' => 'UZS',
					'name' => 'Uzbekistan Som',
				) ,
				'VEF' => array(
					'symbol' => 'Bs',
					'code' => 'VEF',
					'name' => 'Venezuela Bolivar',
				) ,
				'VND' => array(
					'symbol' => '₫',
					'code' => 'VND',
					'name' => 'Viet Nam Dong',
				) ,
				'YER' => array(
					'symbol' => '﷼',
					'code' => 'YER',
					'name' => 'Yemen Rial',
				) ,
				'ZWD' => array(
					'symbol' => '',
					'code' => 'ZWD',
					'name' => 'Zimbabwe Dollar',
				) ,
			);

			// Apply filter
			$currencies = apply_filters('wsf_config_currencies', $currencies);

			return $currencies;
		}

		public static function get_ecommerce() {

			// Check cache
			if(self::$ecommerce !== false) { return self::$ecommerce; }			

			$ecommerce = array(

				'cart_price_types' => array(

					'subtotal' 			=> array('label' => __('Subtotal', 'ws-form'), 'priority' => 10, 'multiple' => false),
					'shipping' 			=> array('label' => __('Shipping', 'ws-form'), 'priority' => 20),
					'discount'			=> array('label' => __('Discount', 'ws-form'), 'priority' => 30),
					'handling_fee'		=> array('label' => __('Handling Fee', 'ws-form'), 'priority' => 40),
					'shipping_discount'	=> array('label' => __('Shipping Discount', 'ws-form'), 'priority' => 50),
					'insurance'			=> array('label' => __('Insurance', 'ws-form'), 'priority' => 60),
					'gift_wrap'			=> array('label' => __('Gift Wrap', 'ws-form'), 'priority' => 70),
					'other'				=> array('label' => __('Other', 'ws-form'), 'priority' => 80),
					'tax'				=> array('label' => __('Tax', 'ws-form'), 'priority' => 100)
				),

				'status' => array(

					'new'				=> array('label' =>	__('New', 'ws-form')),
					'pending_payment'	=> array('label' =>	__('Pending Payment', 'ws-form')),
					'processing'		=> array('label' =>	__('Processing', 'ws-form')),
					'active'			=> array('label' =>	__('Active', 'ws-form')),
					'cancelled'			=> array('label' =>	__('Cancelled', 'ws-form')),
					'authorized'		=> array('label' =>	__('Authorized', 'ws-form')),
					'completed'			=> array('label' =>	__('Completed', 'ws-form')),
					'failed'			=> array('label' =>	__('Failed', 'ws-form')),
					'refunded'			=> array('label' =>	__('Refunded', 'ws-form')),
					'voided'			=> array('label' =>	__('Voided', 'ws-form'))
				),

				'meta_keys' => array(

					'ecommerce_cart_total'		=> array('label' =>	__('Total', 'ws-form'), 'type' => 'price', 'priority' => 200),
					'ecommerce_status'			=> array('label' =>	__('Status', 'ws-form'), 'lookup' => 'status', 'priority' => 5),
					'ecommerce_transaction_id'	=> array('label' =>	__('Transaction ID', 'ws-form'), 'priority' => 1010),
					'ecommerce_payment_method'	=> array('label' =>	__('Payment Method', 'ws-form'), 'priority' => 1020)
				)
			);

			// Apply filter
			$ecommerce = apply_filters('wsf_config_ecommerce', $ecommerce);

			// Cache
			self::$ecommerce = $ecommerce;

			return $ecommerce;
		}
	}