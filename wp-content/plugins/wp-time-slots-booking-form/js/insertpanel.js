var cptslotsbk_appointments_fpanel = function($){
            var cptslotsbk_counter = 0;
          	function loadWindow(){
          	    cptslotsbk_counter++;
                const formOptions = cptimeslotsbk_formsclassic.forms;
                var myoptions = '';            
                for (var i=0; i<formOptions.length; i++)
                    myoptions += '<option value="'+formOptions[i].value+'">'+formOptions[i].label+'</option>';
          		$(' <div title="WP Time Slots Booking Form"><div style="padding:20px;">'+
          		   'Select Calendar:<br /><select id="cptslotsbk_calendar_sel'+cptslotsbk_counter+'" name="cptslotsbk_calendar_sel'+cptslotsbk_counter+'">'+myoptions+'</select>'+
          		   '</div></div>'
          		  ).dialog({
          			dialogClass: 'wp-dialog',
                      modal: true,
                      closeOnEscape: true,
                      buttons: [
                          {text: "Insert", click: function() {
          						if(send_to_editor){
          							var id = $('#cptslotsbk_calendar_sel'+cptslotsbk_counter)[0].options[$('#cptslotsbk_calendar_sel'+cptslotsbk_counter)[0].options.selectedIndex].value;
                                    send_to_editor('[CP_TIME_SLOTS_BOOKING id="'+id+'"]');
          						}
          						$(this).dialog("close");
          				}}
                      ]
                  });
          	}
          	var obj = {};
          	obj.open = loadWindow;
          	return obj;
          }(jQuery);