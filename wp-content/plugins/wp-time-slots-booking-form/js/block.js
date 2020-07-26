jQuery(function()
	{
		(function( blocks, element ) {
            var el = wp.element.createElement,
                source 		= blocks.source,
	            InspectorControls   = ('blockEditor' in wp) ? wp.blockEditor.InspectorControls : wp.editor.InspectorControls;
		    var category 	= {slug:'wp-time-slots-booking-form', title : 'WP Time Slots Booking Form'};
            
				
       		var _wp$components = wp.components,
                SelectControl = _wp$components.SelectControl,
                ServerSideRender = _wp$components.ServerSideRender;
        
            var iconWPTSB = el('img', { width: 20, height: 20, src:'data:image/gif;base64,R0lGODlhFQAVAMQAAP//////AP8A//8AAAD//wD/AAAA/wAAAAU6gAY8gAlFiwlFigtMkA9TlhZioxhkpBtqqRxrqSR1sSd6tCp8tDuPwUibyEueymGu1HS73IzK5JvS6KjZ6+75/P///wAAACH5BAEAAB4ALAAAAAAVABUAAAWDYCd229Jko4glD5emUnQx71JN1DtCAIC8iB4kZUEYg77jsae0FJdJJbL5NDoiUuOkQc16s86v2Ogc6nSAXbnTa7vfaci6A6mL6mb8fa6n2/15a3CDPXR8f32Jh3mIiGWEhIYWjJR3jpOWlYB7mCJpI5+eahYKeKanpwoWGk6trq+vGiEAOw==' } );               
                
			/* Plugin Category */
			blocks.getCategories().push({slug: 'cptimeslotsbk', title:'WP Time Slots Booking Form'}) ;
        
			
			/* Form's shortcode */
			blocks.registerBlockType( 'cptimeslots/form-rendering', {
                title: 'WP Time Slots Booking Form', 
                icon: iconWPTSB,    
                category: 'cptimeslotsbk',
				supports: {
					customClassName: false,
					className: false
				},
				 attributes: {
			      	  formId: {
			            type: 'string'
		              },
			      	  instanceId: {
			            type: 'string'
		              }
			      },           
	        edit: function( { attributes, className, isSelected, setAttributes }  ) {             
                    const formOptions = cptimeslots_forms.forms;
                    if (!formOptions.length)
                        return el("div", null, 'Please create a booking form first.' );
                    var iId = attributes.instanceId;
                    if (!iId)
                    {                        
                        iId = formOptions[0].value+parseInt(Math.random()*100000);
                        setAttributes({instanceId: iId });
                    }
                    if (!attributes.formId)
                        setAttributes({formId: formOptions[0].value });
                    cp_timeslots_renderForm(iId);
			    	var focus = isSelected;
					return [
						!!focus && el(
							InspectorControls,
							{
								key: 'cptimeslotsbk_inspector'
							},
							[
								el(
									'span',
									{
										key: 'cptimeslotsbk_inspector_help',
										style:{fontStyle: 'italic'}
									},
									'If you need help: '
								),
								el(
									'a',
									{
										key		: 'cptimeslotsbk_inspector_help_link',
										href	: 'https://wptimeslot.dwbooster.com/contact-us',
										target	: '_blank'
									},
									'CLICK HERE'
								)
							]
						),
						el(SelectControl, {
                                value: attributes.formId,
                                options: formOptions,
                                onChange: function(evt){         
                                    setAttributes({formId: evt});
                                    iId = evt+parseInt(Math.random()*100000);
                                    setAttributes({instanceId: iId });
                                    cp_timeslots_renderForm(iId);                                   
			    				},
                        }),
                        el(ServerSideRender, {
                             block: "cptimeslots/form-rendering",
                             attributes: attributes
                        })		
					];
				},

				save: function( props ) {
					return null;
				}
			});

		} )(
			window.wp.blocks,
			window.wp.element
		);
	}
);
function cp_timeslots_renderForm(id) {      
    if (jQuery("#form_structure"+id).length)
    {
        try 
        {
            var cp_appbooking_fbuilder_myconfig = {"obj":"{\"pub\":true,\"identifier\":\"_"+id+"\",\"messages\": {}}"};
            var f = jQuery("#fbuilder_"+id).fbuilder(jQuery.parseJSON(cp_appbooking_fbuilder_myconfig.obj));
            f.fBuild.loadData("form_structure"+id);                     
        } catch (e) { setTimeout ('cp_timeslots_renderForm('+id+')',150); }
    }
    else
    {
        setTimeout ('cp_timeslots_renderForm('+id+')',50);
    }
} 