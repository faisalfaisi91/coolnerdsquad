<?php

	class WS_Form_Schedule {

		public function __construct() {

			// Data source cron processes
			add_action(WS_FORM_DATA_SOURCE_SCHEDULE_HOOK, array($this, 'wsf_data_source_update'), 10, 1);
		}

		// Data source cron processing
		public function wsf_data_source_update($args) {

			echo "CRON RUNNING\n";
		}
	}

