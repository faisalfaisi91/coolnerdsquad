!function(t){"use strict";jQuery(window).on("elementor/frontend/init",function(){elementorFrontend.hooks.addAction("frontend/element_ready/ws-form.default",function(t,n){n(".wsf-form").each(function(){n(this).off().html("");var t=n(this).attr("id"),e=n(this).attr("data-id"),o=n(this).attr("data-instance-id"),i=new n.WS_Form;window.wsf_form_instances[o]=i,i.render({obj:"#"+t,form_id:e})})})})}();