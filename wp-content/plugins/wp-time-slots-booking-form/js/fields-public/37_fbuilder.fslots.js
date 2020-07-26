$.fbuilder.controls[ 'fslots' ] = function(){};
$.extend( 
	$.fbuilder.controls[ 'fslots' ].prototype, 
	$.fbuilder.controls[ 'ffields' ].prototype,
	{
	    title:"Slots booking",
	    ftype:"fslots",
	  	slotsavailInit:{"d1":[{h1:8,m1:0,c:1,p:0,min:8*60}],
	  		        "d2":[{h1:8,m1:0,c:1,p:0,min:8*60}],
	  		        "d3":[{h1:8,m1:0,c:1,p:0,min:8*60}],
	  		        "d4":[{h1:8,m1:0,c:1,p:0,min:8*60}],
	  		        "d5":[{h1:8,m1:0,c:1,p:0,min:8*60}]},
	  	slots_special:new Array(),
		min_quantity:1,
		max_quantity:5,
		quantity1:1,
	  	quantity1start:0,
		label1:"",
		quantity2:1,
		label2:"",
		quantity3:1,
		label3:"",
		quantity4:1,
		label4:"",
		quantity5:1,
		label5:"",
		prices:new Array({name:"default",price1:new Array(),price2:new Array(),price3:new Array(),price4:new Array(),price5:new Array()}),
		cacheArr:new Array(),
		usedSlots:new Array(),
		dateFormat:"mm/dd/yy",
		showDropdown:false,
		showTotalCost:false,
		showTotalCostFormat:"$ {0}",
		dropdownRange:"-10:+10",
		working_dates:[true,true,true,true,true,true,true],
		numberOfMonths:1,
		firstDay:0,
		minDate:"0",
		maxDate:"",
		defaultDate:"",
		invalidDates:"",
		required:true,
		formId:0,
		extras:0,			
		size:"medium",
		current_quantity:1,
		invalidDatesByQuantity:new Array(),
		pctByDay:new Array(),
		loadOK:false,		
		initialapp:"",
		initialID:0,
		arr:new Array(),
		arrTotal:new Array(),
		militaryTime:1,
		percent:0,
		formattime:function(t,mt)//mt=2 for database 09:00
		{
			  var h = Math.floor(t/60);
		    var m = t%60;
		    var suffix = "";
		    if (mt==0)
		    {
		        if (h>12)
		        {
		            h = h-12;
		            suffix = " PM";
		        }
		        else if (h==12)
		              suffix = " PM"; 
		        else
		        {   
		            if (h==0 && mt!=2) h=12;
		            suffix = " AM";  
		        }      
		    }
			  return (((h<10)?((mt==2)?"0":""):"")+h+":"+(m<10?"0":"")+m)+suffix;									
		},
	    disableSpecificDates: function(d) {
            var currentdate = d.getTime();
            if ($.inArray(currentdate, this.tmpinvalidDatestime) > -1 ) 
                return false;
            var day = $.datepicker.formatDate('yy-mm-dd', d);    
            if (this.working_dates[d.getDay()]==0  && ($.inArray(day, this.special_days) == -1))
                return false; 
            return true;
        },
        getAvailableSlots: function(d) {
            me  = this;
            var str = ""; 
            var c = "c"+me.current_quantity;
            var dday = $.datepicker.parseDate("yy-mm-dd", d);
            var item = $.datepicker.formatDate("yy-mm-dd",dday);
            /*verify if not working_dates or not invalidDates */
            if (me.working_dates[dday.getDay()]==0  && ($.inArray(item, this.special_days) == -1))
                return str; 
            for( var i = 0, l = me.invalidDates.length; i < l; i++ )
            {
            	if (dday.getTime() === me.invalidDates[i].getTime())
            	    return str; 
            } 
			me.arr[d] = new Array();
			var day = $.datepicker.formatDate('yy-mm-dd', dday);
			var ind = -1;
			for (var i=0;ind==-1 && i<me.slots_special.length;i++)
			    if (me.slots_special[i].type==day)
			        ind = i;
			
			if (ind==-1)
			{
			      me.slotsavail["d"+dday.getDay()] = me.slotsavail["d"+dday.getDay()] || [];
				  me.arr[d] = (me.working_dates[dday.getDay()]==0)?new Array(): me.slotsavail["d"+dday.getDay()].slice();
			}	  
			else
				  me.arr[d] = me.slots_special[ind].slots.slice();
			me.arrTotal[d] =  me.arr[d].slice();
			for (var j=0;j<me.arr[d].length;j++)
			    me.arr[d][j] = jQuery.extend({}, me.arr[d][j]);
			  
            if ($.inArray(item, me.invalidDatesByQuantity[c]) == -1)
            {
			    me.usedSlots[d] = me.usedSlots[d] || [];
			  	me.cacheArr[d] = me.cacheArr[d] || [];
				var used = $.merge(me.cacheArr[d].slice(),me.usedSlots[d]);
				var totalC = 0, usedC = 0;
				for (var j=0;me.arr[d] && j<me.arr[d].length;j++)
				    totalC += me.arr[d][j].c*1;
				for (var i=0;i<used.length;i++)
				{
					  used[i].min = used[i].h1 * 60 + used[i].m1*1;
					  used[i].c = used[i].quantity1 * 1 + used[i].quantity2 * 1 + used[i].quantity3 * 1 + used[i].quantity4 * 1 + used[i].quantity5 * 1;
					  usedC += used[i].c*1;
				    for (var j=0;j<me.arr[d].length;j++)
				    {
				         if (me.arr[d][j].min==used[i].min)
				         {
				             if (me.arr[d][j].c>=used[i].c)
				                 me.arr[d][j].c -= used[i].c;
				             else
				                 me.arr[d][j].c = 0;    
				         }
				    }
				}
				me.pctByDay[day] = Math.floor(10*usedC/totalC);
				var dt = $.datepicker.parseDate("yy-mm-dd",d).getTime();
				var dmin = (typeof me.minDate.getMonth === 'function')?me.minDate.getTime():0;
				var dmax = (typeof me.maxDate.getMonth === 'function')?me.maxDate.getTime():0;
				for (var j=0;me.arr[d] && j<me.arr[d].length;j++)
			  	    if (((dmin==0) ||  (dt+me.arr[d][j].min*60*1000 > dmin)) && ((dmax==0) ||  (dt+me.arr[d][j].min*60*1000 < dmax)))
			  	    {	
			  	        var currentSelection = "";
			  	        for (var k=0;k<me.usedSlots[d].length;k++)
			  	            if (me.usedSlots[d][k].h1==me.arr[d][j].h1 && me.usedSlots[d][k].m1==me.arr[d][j].m1)
			  	                currentSelection = "currentSelection";
			            if (me.arr[d][j].c >= me.current_quantity || currentSelection == "currentSelection")
			                str += '<div class=\"availableslot '+currentSelection+'\"><a href=\"\" h1="'+me.arr[d][j].h1+'" m1="'+me.arr[d][j].m1+'" c="'+me.arr[d][j].c+'" p="'+((me.arr[d][j].p)?me.arr[d][j].p:0)+'">'+me.formattime(me.arr[d][j].h1*60+me.arr[d][j].m1*1,me.militaryTime)+'<span class=\"ts_slot_availability\"><span class=\"p\">'+me.arr[d][j].c+'</span><span class=\"t\">'+me.arrTotal[d][j].c+'</span></span></a></div>';	 
			        }   
			  	if (str=="")
			  	{
			  	    me.invalidDatesByQuantity[c] = me.invalidDatesByQuantity[c] || [];
			  	    me.invalidDatesByQuantity[c][me.invalidDatesByQuantity[c].length]= item;
			  	}			  	  	   
			 }
			 return str;			  		       
        },
		show:function()
		{
		      var str1 = "", str2 = "", str3 = "", str4 = "", str5 = "";
		      str1 = '<input type="hidden" class="sbquantity1" value="1" />';
			  if (this.quantity1>1)
			  {
			      str1 = "";
			      for (var i = this.quantity1start*1;i<=this.quantity1;i++)
			          str1 += '<option value="'+i+'" '+(i==1?"selected":"")+'>'+i+'</option>';
			      str1 = '<div id="field_q2_1"><label>'+this.label1+'</label><div class="dfield"><select class="sbquantity sbquantity1 '+this.size+'" >'+str1+'</select><span class="uh"></span></div><div class="clearer"></div></div>';
			  }				
			  str2 = '<input type="hidden" class="sbquantity2" value="0" />';
			  if (this.quantity1>1 && this.quantity2>1)
			  {
			      str2 = "";
			      for (var i=0;i<=this.quantity2;i++)
			          str2 += '<option value="'+i+'" '+(str2==""?"selected":"")+'>'+i+'</option>';
			      str2 = '<div id="field_q2_2"><label>'+this.label2+'</label><div class="dfield"><select class="sbquantity sbquantity2 '+this.size+'" >'+str2+'</select><span class="uh"></span></div><div class="clearer"></div></div>';
			  }
			  str3 = '<input type="hidden" class="sbquantity3" value="0" />';
			  if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1)
			  {
			      str3 = "";
			      for (var i=0;i<=this.quantity3;i++)
			          str3 += '<option value="'+i+'" '+(str3==""?"selected":"")+'>'+i+'</option>';
			      str3 = '<div id="field_q2_3"><label>'+this.label3+'</label><div class="dfield"><select class="sbquantity sbquantity3 '+this.size+'" >'+str3+'</select><span class="uh"></span></div><div class="clearer"></div></div>';
			  }
			  str4 = '<input type="hidden" class="sbquantity4" value="0" />';
			  if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1)
			  {
			      str4 = "";
			      for (var i=0;i<=this.quantity4;i++)
			          str4 += '<option value="'+i+'" '+(str4==""?"selected":"")+'>'+i+'</option>';
			      str4 = '<div id="field_q2_4"><label>'+this.label4+'</label><div class="dfield"><select class="sbquantity sbquantity4 '+this.size+'" >'+str4+'</select><span class="uh"></span></div><div class="clearer"></div></div>';
			  }
			  str5 = '<input type="hidden" class="sbquantity5" value="0" />';
			  if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1 && this.quantity5>1)
			  {
			      str5 = "";
			      for (var i=0;i<=this.quantity4;i++)
			          str5 += '<option value="'+i+'" '+(str5==""?"selected":"")+'>'+i+'</option>';
			      str5 = '<div id="field_q2_5"><label>'+this.label5+'</label><div class="dfield"><select class="sbquantity sbquantity5 '+this.size+'" >'+str5+'</select><span class="uh"></span></div><div class="clearer"></div></div>';
			  }
		    return '<div class="fields '+this.csslayout+'" id="field'+this.form_identifier+'-'+this.index+'">'+str1+str2+str3+str4+str5+'<label for="'+this.name+'">'+this.title+''+((this.required)?"<span class='r'>*</span>":"")+'</label><div class="dfield"><input class="field avoid_overlapping_before '+((this.quantity1>1)?" sbquantity_min":"")+' '+((this.required)?" required":"")+'" id="'+this.name+'" name="'+this.name+'" type="hidden" value="" summary="usedSlots"/><input id="'+this.name+'_services" name="'+this.name+'_services" type="hidden" value="0"/><input class="" id="tcost'+this.name+'" name="tcost'+this.name+'" type="hidden" value=""/><div class="sbfieldCalendar'+this.name+'"></div><div class="slotsCalendar'+this.name+'"></div><div class="usedSlots'+this.name+'"></div><span class="uh">'+this.userhelp+'</span></div><div class="clearer"></div></div>';
		},
	    after_show:function()
		{
		    try {$.fn.datepicker.noConflict();} catch (e) {}
		    function closeOtherDatepicker(){
		        $('#ui-datepicker-div').css("display","none");
		    }
		    setTimeout(closeOtherDatepicker,100);
		    function renderCalendarCallback(d) {
			    var day = $.datepicker.formatDate('yy-mm-dd', d);
                var c =  new Array(day,"d"+day);
                if (me.working_dates[d.getDay()]==0  && ($.inArray(day, me.special_days) == -1))
                    c.push("nonworking  ui-datepicker-unselectable ui-state-disabled nonworkingweekday");
                for( var i = 0, l = me.invalidDates.length; i < l; i++ )
                {
                    if (d.getTime() === me.invalidDates[i].getTime())
                	    c.push("nonworking  ui-datepicker-unselectable ui-state-disabled invalidDate");
                }
			    me.invalidDatesByQuantity["c"+me.current_quantity] = me.invalidDatesByQuantity["c"+me.current_quantity] || [];
			    me.getAvailableSlots($.datepicker.formatDate("yy-mm-dd", d));
			    if ($.inArray(day, me.invalidDatesByQuantity["c"+me.current_quantity]) > -1)
                    c.push("nonworking  ui-datepicker-unselectable ui-state-disabled tmpinvalidDate nonavailableslots");
                var cclass = c.join(" ");
                if (cclass.indexOf("nonworking")==-1) 
                    cclass +=" ts_booked"+me.pctByDay[day];            
                return [true,cclass];
		    }
		    function onChangeDateOrQuantity(d)
			{
			    function loadSlots(d)
			    {		
			        if (!me.loadOK)
			  		{  	
			  		    me.formId = $(".sbfieldCalendar"+me.name).parents("form").find('input[type="hidden"][name$="_id"]').val();			  		            
			  		    $.ajax(
			  		    {
			  		        dataType : 'json',
			  		        type: "POST",
			  		        url : document.location.href,
			  		        cache : true,
			  		        data :  { cp_slots_action: 'get_slots',
			  		            formid: me.formId,
		  			            initialID: me.initialID,
			  		        	formfield: me.name.replace(me.form_identifier, "")   
			  		        },
			  		        success : function( data ){
			  		            for (var i=0;i<data.length;i++)
			  		            {
			  		                var dd = data[i].d;
			  		                me.cacheArr[dd] = me.cacheArr[dd] || [];
			  		                me.cacheArr[dd][me.cacheArr[dd].length] = data[i];	
			  		            }
			  		            me.cacheArr[d] = me.cacheArr[d] || [];
			  		            me.loadOK = true;
			  		            getSlots($.datepicker.formatDate("yy-mm-dd", me.getD));					      			
			  		        }
			  		    });			  		 	          
			  		}
			  		else
					{	
						me.cacheArr[d] = me.cacheArr[d] || [];
					    getSlots(d);
					}
			    }
			    function getSlots(d)
			    {
			        var str = "";	
		            var c = "c"+me.current_quantity;	
			        var nextdateAvailable = $.datepicker.parseDate("yy-mm-dd", d);
			        str = me.getAvailableSlots(d);				      	
		            var s = $( '#field' + me.form_identifier + '-' + me.index + ' .slotsCalendar'+me.name );
			        if (str=="")
			        {
                        while (str=="" && me.invalidDatesByQuantity[c].length<1000)
                        {
                            nextdateAvailable.setDate(nextdateAvailable.getDate() + 1);
                            str = me.getAvailableSlots($.datepicker.formatDate("yy-mm-dd",nextdateAvailable));
                        }  
                        if (str!="")  
                        {
                            e.datepicker("setDate", nextdateAvailable);
                            me.getD = nextdateAvailable;
		                        onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", nextdateAvailable));  
                        } 
                        else if (me.invalidDatesByQuantity[c].length>=1000)
                        {
                             e.datepicker("setDate", me.startdate);
                             s.html("<div class=\"slots\">"+cp_tslotsbk_nomore_label+"</div>");                           
                        }
			        }
			        else			  		        
			            s.html("<div class=\"slots\" d=\""+d+"\"><span>"+$.datepicker.formatDate(me.dateFormat, $.datepicker.parseDate("yy-mm-dd", d))+"</span>"+str+"</div>");			  		        
			        var str1="",str2="";
			        me.allUsedSlots = me.allUsedSlots || [];
			        j = 0;
			        var total = 0, subtotal = 0;
				    me.allUsedSlots.sort(function(a, b){ return ($.datepicker.parseDate("yy-mm-dd", a.d).getTime()+a.min*60*1000) - ($.datepicker.parseDate("yy-mm-dd", b.d).getTime()+b.min*60*1000)});
			        for (var i=0;i<me.allUsedSlots.length;i++)
			        {       
			              subtotal = 0;
			              if (me.allUsedSlots[i].p*1>me.prices.length-1)
			                  me.allUsedSlots[i].p = 0;
                          try {
			                  subtotal += me.prices[me.allUsedSlots[i].p*1].price1[i]*me.allUsedSlots[i].quantity1;
			                  subtotal += me.prices[me.allUsedSlots[i].p*1].price2[i]*me.allUsedSlots[i].quantity2;
			                  subtotal += me.prices[me.allUsedSlots[i].p*1].price3[i]*me.allUsedSlots[i].quantity3;
			                  subtotal += me.prices[me.allUsedSlots[i].p*1].price4[i]*me.allUsedSlots[i].quantity4;
			                  subtotal += me.prices[me.allUsedSlots[i].p*1].price5[i]*me.allUsedSlots[i].quantity5;
                          } catch (e) {}
			        	  total += subtotal;
			        	  str1 += "<div class=\"sb_item\" d=\""+me.allUsedSlots[i].d+"\" h=\""+me.allUsedSlots[i].h1+"\" m=\""+me.allUsedSlots[i].m1+"\"><span class=\"sb_item_time\">"+$.datepicker.formatDate(me.dateFormat, $.datepicker.parseDate("yy-mm-dd", me.allUsedSlots[i].d))+" "+me.formattime(me.allUsedSlots[i].h1*60+me.allUsedSlots[i].m1*1,me.militaryTime)+"</span> <a href=\"\" i=\""+j+"\" iall=\""+i+"\">["+cp_tslotsbk_cancel_label+"]</a></div>";
			        	  str2 += ((str2=="")?"":";")+me.allUsedSlots[i].d+" "+me.formattime(me.allUsedSlots[i].h1*60+me.allUsedSlots[i].m1*1,2)+" "+me.allUsedSlots[i].quantity1+" "+me.allUsedSlots[i].quantity2+" "+me.allUsedSlots[i].quantity3+" "+me.allUsedSlots[i].quantity4+" "+me.allUsedSlots[i].quantity5+" "+subtotal+" "+me.allUsedSlots[i].p;
			        	  if (me.allUsedSlots[i].d==d)
			        	      j++;
			        }			  		        	
			        getExtras();
			        if (me.showTotalCost && (str1!=""))
			            str1 += '<div class="totalCost"><span>'+cp_tslotsbk_cost_label+'</span><span class="n"> '+me.showTotalCostFormat.replace("{0}", ((me.extras+total)*(1+me.percent/100)).toFixed(2))+'</span></div>';
			        $( '#field' + me.form_identifier + '-' + me.index + ' .usedSlots'+me.name ).html(str1);
			        $( '#field' + me.form_identifier + '-' + me.index + ' #'+me.name ).val(str2);
			        $( '#field' + me.form_identifier + '-' + me.index + ' #'+me.name ).change();
			        $( '#field' + me.form_identifier + '-' + me.index + ' #tcost'+me.name ).val(total);
			        
			        $( '#field' + me.form_identifier + '-' + me.index + ' .slots a').off("click").on("click", function(){
			            if ($(this).parents("fieldset").hasClass("ahbgutenberg_editor"))
			                return false;
		  		          $( "#field" + me.form_identifier + "-" + me.index + " div.cpefb_error").remove();	
		  		        $( '#field' + me.form_identifier + '-' + me.index + ' #'+me.name ).removeClass("required"); 
		  		        if ($( '#field' + me.form_identifier + '-' + me.index ).parents( "form" ).validate().element('#'+me.name))
			            {
			                
			                if (me.max_quantity==1 && me.allUsedSlots.length==me.max_quantity) //cancel previous slot
		  			        {
		  			            me.usedSlots[me.allUsedSlots[0].d] = new Array();
		  			            me.allUsedSlots = new Array();
		  			            me.invalidDatesByQuantity["c"+me.current_quantity] = new Array();
		  			        }
			                me.allUsedSlots = me.allUsedSlots || [];
			                if (me.max_quantity==0 || me.allUsedSlots.length<me.max_quantity)
			                {
			                    var d = $(this).parents(".slots").attr("d");
			                      me.usedSlots[d] = me.usedSlots[d] || [];
			                      obj = {h1:$(this).attr("h1")*1,m1:$(this).attr("m1")*1,quantity1:$(this).parents(".fields").find(".sbquantity1").val()*1,quantity2:$(this).parents(".fields").find(".sbquantity2").val()*1,quantity3:$(this).parents(".fields").find(".sbquantity3").val()*1,quantity4:$(this).parents(".fields").find(".sbquantity4").val()*1,quantity5:$(this).parents(".fields").find(".sbquantity5").val()*1,d:d,p:$(this).attr("p")*1};	            	
			                      me.usedSlots[d][me.usedSlots[d].length] = obj;
			                      me.allUsedSlots[me.allUsedSlots.length] = obj;
			                      onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.getD));
			                }
			                else
			                    alert($.validator.messages.maxapp.replace("{0}",me.max_quantity));
			                   
			            }
			            $( '#field' + me.form_identifier + '-' + me.index + ' #'+me.name ).addClass("required");    
			            return false;
			        }); 
			        $( '#field' + me.form_identifier + '-' + me.index + ' .usedSlots'+me.name+ ' a').off("click").on("click", function(){
			            var d = $(this).parents(".sb_item").attr("d");
			            var h = $(this).parents(".sb_item").attr("h");
			            var m = $(this).parents(".sb_item").attr("m");
			            me.usedSlots[d] = me.usedSlots[d] || [];
			            var find = false;
			            for (var i = 0; (i<me.usedSlots[d].length && !find); i++)
			                if (me.usedSlots[d][i].d==d && me.usedSlots[d][i].h1==h && me.usedSlots[d][i].m1==m)
			                {
			                    find = true;
			                    me.usedSlots[d].splice(i, 1);    
			                }	
			            var find = false;
			            for (var i = 0; (i<me.allUsedSlots.length && !find); i++)
			                if (me.allUsedSlots[i].d==d && me.allUsedSlots[i].h1==h && me.allUsedSlots[i].m1==m)
			                {
			                    find = true;
			                    me.allUsedSlots.splice(i, 1);    
			                }			  		            
			            var currentday = me.getD;    			  		            
			            e.datepicker("setDate", me.startdate);
			            me.invalidDatesByQuantity["c"+me.current_quantity] = new Array();
			            onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.startdate));			  		            
			            e.datepicker("setDate", currentday);
			            me.getD = currentday;
			            onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.getD)); 
			            return false;
			        });
			    }
			    loadSlots(d);
		    }
			var me  = this, e   = $( '#field' + me.form_identifier + '-' + me.index + ' .sbfieldCalendar'+me.name ),
			d = e.parents(".fields"),
			str = "",str1 = "",str2 = "", op = "";
			e.addClass("notranslate");
			var dd = "";
			for (var i=0;i<me.prices.length;i++)
			{
			    me.prices[i].price3 = me.prices[i].price3 || [];
			    me.prices[i].price4 = me.prices[i].price4 || [];
			    me.prices[i].price5 = me.prices[i].price5 || [];
			    for (var j=0;j<me.prices[i].price1.length;j++)
			    {
			        if (me.prices[i].price3.length<=j)
			            me.prices[i].price3[j] = 0;
			        if (me.prices[i].price4.length<=j)
			            me.prices[i].price4[j] = 0;
			        if (me.prices[i].price5.length<=j)
			            me.prices[i].price5[j] = 0;    
			    }    
			}
			if (me.initialapp!="")
			{
			    me.allUsedSlots = me.allUsedSlots || [];
			    var s = me.initialapp.split(";");
			    var s2 = "";
			    for (var i=0;i<s.length;i++)
			    {
			        if (s[i]!="")
			        {
			            s2 = s[i].split(" ");
			            var tt = s2[1].split(":");
			            var quantity1 = s2[2]*1;
			            var quantity2 = s2[3]*1;
			            var quantity3 = s2[4]*1;			            
			            var quantity4 = s2[5]*1; 			            
			            var quantity5 = s2[6]*1; 
			            dd = s2[0];
			            me.usedSlots[dd] = me.usedSlots[dd] || [];
			            obj = {h1:tt[0]*1,m1:tt[1]*1,quantity1:quantity1*1,quantity2:quantity2*1,quantity3:quantity3*1,quantity4:quantity4*1,quantity5:quantity5*1,d:dd,p:s2[7]*1};
		  			    me.usedSlots[dd][me.usedSlots[dd].length] = obj;
		  			    me.allUsedSlots[me.allUsedSlots.length] = obj;
		  			}
		  			d.find(".sbquantity1").val(quantity1);
		  			d.find(".sbquantity2").val(quantity2);
		  			d.find(".sbquantity3").val(quantity3);
		  			d.find(".sbquantity4").val(quantity4);
		  			d.find(".sbquantity5").val(quantity5);
			    }
			}
			if (typeof me.slotsavail === 'undefined')
	  	  	    me.slotsavail = $.extend({}, me.slotsavailInit);
			if (me.minDate.toLowerCase().indexOf("h")!=-1)
			{
			    var h = me.minDate.toLowerCase().replace(/h/gi, "");
			    me.minDate = new Date(((new Date).getTime() + h * 60 * 60 * 1000) )
			}
			else if (me.minDate == parseInt(me.minDate, 10))
			{
			    me.minDate = new Date(((new Date).getTime() + parseInt(me.minDate) * 24 * 60 * 60 * 1000) )    
			}
			if (me.maxDate.toLowerCase().indexOf("h")!=-1)
			{
			    var h = me.maxDate.toLowerCase().replace(/h/gi, "");
			    me.maxDate = new Date(((new Date).getTime() + h * 60 * 60 * 1000) )
			}
			else if (me.maxDate == parseInt(me.maxDate, 10))
			{
			    me.maxDate = new Date(((new Date).getTime() + parseInt(me.maxDate) * 24 * 60 * 60 * 1000) )    
			}
			if (typeof wptimesbk_in_admin !== 'undefined')
	  	  	{	  	  	      
	  	        me.minDate = "";
	  	        me.maxDate = "";
	  	  	}
			this.invalidDates = this.invalidDates.replace( /\s+/g, '' );
			if( !/^\s*$/.test( this.invalidDates ) )
			{
			    var	dateRegExp = new RegExp( /^\d{1,2}\/\d{1,2}\/\d{4}$/ ),
			        counter = 0,
			        dates = this.invalidDates.split( ',' );
			    this.invalidDates = [];
			    for( var i = 0, h = dates.length; i < h; i++ )
			    {
			    	 var range = dates[ i ].split( '-' );                    
			    	 if( range.length == 2 && range[0].match( dateRegExp ) != null && range[1].match( dateRegExp ) != null )
			    	 {
			    	 	  var fromD = new Date( range[ 0 ] ),
			    	 	  	  toD = new Date( range[ 1 ] );
			    	 	  while( fromD <= toD )
			    	 	  {
			    	 	  	  this.invalidDates[ counter ] = fromD;
			    	 	  	  var tmp = new Date( fromD.valueOf() );
			    	 	  	  tmp.setDate( tmp.getDate() + 1 );
			    	 	  	  fromD = tmp;
			    	 	  	  counter++;
			    	 	  }
			    	 }
			    	 else
			    	 {
			    	 	  for( var j = 0, k = range.length; j < k; j++ )
			    	 	  {
			    	 	  	  if( range[ j ].match( dateRegExp ) != null )
			    	 	  	  {
			    	 	  	  	  this.invalidDates[ counter ] = new Date( range[ j ] );
			    	 	  	  	  counter++;
			    	 	  	  }
			    	 	  }
			    	 }
			    }
			}
			if ($.validator.messages.date_format && $.validator.messages.date_format!="")	
			    me.dateFormat = $.validator.messages.date_format;
			d.find(".sbquantity1,.sbquantity2,.sbquantity3,.sbquantity4,.sbquantity5").on("change", function(){
			    me.allUsedSlots = new Array();
			    me.usedSlots = new Array();
			    me.current_quantity = ($( '#field' + me.form_identifier + '-' + me.index  ).find(".sbquantity1").val()*1 + $( '#field' + me.form_identifier + '-' + me.index  ).find(".sbquantity2").val()*1 + $( '#field' + me.form_identifier + '-' + me.index  ).find(".sbquantity3").val()*1 + $( '#field' + me.form_identifier + '-' + me.index  ).find(".sbquantity4").val()*1 + $( '#field' + me.form_identifier + '-' + me.index  ).find(".sbquantity5").val()*1);
		        e.datepicker("setDate", me.startdate);
		        me.getD = me.startdate;
		        onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.startdate));
			});
			me.special_days = new Array();
			for (var i=0;i<me.slots_special.length;i++)
			    me.special_days[me.special_days.length] = me.slots_special[i].type;		    
			e.datepicker({numberOfMonths:parseInt(me.numberOfMonths),
			    firstDay:parseInt(me.firstDay),
			    minDate:me.minDate,
			    maxDate:me.maxDate,
			    dateFormat:me.dateFormat,
			    defaultDate:me.defaultDate,
			    changeMonth: me.showDropdown, 
			    changeYear: me.showDropdown,
			    yearRange: ((me.showDropdown)?me.dropdownRange:""),
			    onSelect: function(d,inst) {
			        me.getD = e.datepicker("getDate");
			        onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.getD));
		  	        $( "#field" + me.form_identifier + "-" + me.index + " div.cpefb_error").remove();
                },
			  	beforeShowDay: renderCalendarCallback
		    });
		    e.datepicker("option", $.datepicker.regional[$.validator.messages.language]);
		    e.datepicker("option", "firstDay", me.firstDay );
		    e.datepicker("option", "dateFormat", me.dateFormat );
		    e.datepicker("option", "minDate", me.minDate );
		    e.datepicker("option", "maxDate", me.maxDate );
		    if (me.minDate!=="" && (typeof me.minDate.getMonth !== 'function'))
		    {	
		        try{
		          me.minDate = $.datepicker.parseDate(me.dateFormat,e.datepicker("option", "minDate"))		        
		        } catch (e) {}
		        
		    } 	
		    if (me.maxDate!=="")
		    {	
		        try{
		          me.maxDate = $.datepicker.parseDate(me.dateFormat,e.datepicker("option", "maxDate"))
		          me.maxDate = new Date((me.maxDate.getTime() + 23 * 60 * 60 * 1000 + 59 * 60 * 1000 ) );		        
		        } catch (e) {}
		        
		    }
		    me.tmpinvalidDatestime = new Array();
            try {
                for (var i=0;i<me.invalidDates.length;i++)
                    me.tmpinvalidDatestime[i]=me.invalidDates[i].getTime();              
            } catch (e) {}
            
            var sum = 0;
            for (var i=0;i<7;i++)
                sum += me.working_dates[i];
            if (sum>0 || me.slots_special.length>0)//
            {
		        var nextdateAvailable = e.datepicker("getDate");
                while (!me.disableSpecificDates(nextdateAvailable))
                    nextdateAvailable.setDate(nextdateAvailable.getDate() + 1);
                e.datepicker("setDate", nextdateAvailable);
                me.getD = new Date(nextdateAvailable.getTime());
                me.startdate = new Date(nextdateAvailable.getTime());
                onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.getD)); 
		        function ifLoadOk()
                {
                    if (!me.loadOK)
		                setTimeout(ifLoadOk,100);
		            else
		            { 
		                $( '#field' + me.form_identifier + '-' + me.index + ' .sbfieldCalendar'+me.name ).datepicker( "option", "beforeShowDay", renderCalendarCallback );
		                onChangeDateOrQuantity($.datepicker.formatDate("yy-mm-dd", me.getD));
		                $( '#field' + me.form_identifier + '-' + me.index + ' .sbfieldCalendar'+me.name ).datepicker( "option", "beforeShowDay", renderCalendarCallback );
		            }    
                } 
                ifLoadOk();
		    }
		    if (me.initialapp!="" && dd!="")
		    {
		        onChangeDateOrQuantity(dd);
		    }
		    getExtras=function()
		    {
		        var f = $( '#field' + me.form_identifier + '-' + me.index ).parents( "form" );
		        var v = 0;
		        var e = f.find(".tsb_service").find(':checked:not(.ignore)');
		        if( e.length )
			    {
			    	e.each( function(){
			    		v += this.value*1;
			    	});
			    }
			    me.percent = 0;
			    var e = f.find(".tsb_service_percent").find(':checked:not(.ignore)');
		        if( e.length )
			    {
			    	e.each( function(){
			    		me.percent += this.value*1;
			    	});
			    }
			    e = f.find(".tsb_service_per_slot").find(':checked:not(.ignore)');
			    me.allUsedSlots = me.allUsedSlots || [];
			    var s = me.allUsedSlots.length;
		        if( e.length )
			    {
			    	e.each( function(){
			    		v += this.value * s;
			    	});
			    }
			    e = f.find(".tsb_service_per_quantity_selection").find(':checked:not(.ignore)');
			    var q = f.find(".sbquantity1").val() * 1 + f.find(".sbquantity2").val() * 1 + f.find(".sbquantity3").val() * 1 + f.find(".sbquantity4").val() * 1 + f.find(".sbquantity5").val() * 1;
		        if( e.length )
			    {
			    	e.each( function(){
			    		v += this.value * q;
			    	});
			    }
			    f.find('#'+me.name+'_services').val(v);
			    me.extras = v;
			    var total = $( '#field' + me.form_identifier + '-' + me.index + ' #tcost'+me.name ).val()*1+ v;
			    total = total*(1+me.percent/100);
			    $( '#field' + me.form_identifier + '-' + me.index ).find(".totalCost .n").html(" " +me.showTotalCostFormat.replace("{0}",total.toFixed(2)));
			    $( '#field' + me.form_identifier + '-' + me.index + ' #'+me.name ).change();
		    }    
		    $( '#field' + me.form_identifier + '-' + me.index ).parents( "form" ).find(".tsb_service,.tsb_service_percent,.tsb_service_per_slot,.tsb_service_per_quantity_selection").on("click", function(){
		        getExtras();
			}); 
		    $( '#field' + me.form_identifier + '-' + me.index ).parents( "form" ).submit(function(  ) {
		        getExtras();  
            });
            
            if (typeof cp_tslotsbk_overlapping_label != "undefined")
                $.extend($.validator.messages, {avoid_overlapping: $.validator.format(cp_tslotsbk_overlapping_label)});        
			if(!('avoid_overlapping' in $.validator.methods))
			{ 
			    function avoid_over_function(value, element){
                    var validator = this,
                        previous = validator.previousValue( element );
                    if ( previous.old === value ) {
                        return previous.valid;
                    }
                    previous.old = value;
                    validator.startRequest( element );
                    
                    var p = element.id.split( '_' ),
					    _index = ( p.length > 1 ) ? '_'+p[ 1 ] : '',
					    me = ( 
						    typeof $.fbuilder[ 'forms' ] != 'undefined' && 
						    typeof $.fbuilder[ 'forms' ][ _index ] != 'undefined'  
					        ) ? $.fbuilder[ 'forms' ][ _index ].getItem( p[ 0 ]+'_'+p[ 1 ] ) : null;
                    
                    if( me != null )  
                    {
                        $.ajax({
                            dataType : 'json',
		  		            type: "POST",
		  		            url : document.location.href,
		  		            data :  { cp_slots_action: 'get_slots',
		  		                formid: me.formId,
		  		                initialID: me.initialID,
		  		                formfield: me.name.replace(me.form_identifier, "")   
		  		            },
                            success: function(data) {
                                var overlapping = false;
                                var find = false;
                                me.ignoreUsedSlots = true;
                                me.cacheArr = new Array(); 
                                for (var i=0;i<data.length;i++)
		  		                {
		  		                    var dd = data[i].d;
		  		                    me.cacheArr[dd] = me.cacheArr[dd] || [];
		  		                    me.cacheArr[dd][me.cacheArr[dd].length] = data[i];	
		  		                }
		  			            me.loadOK = true;
		  			            var arrAllDays = new Array();
			  	                
		  			            for (var i = 0; i<me.allUsedSlots.length; i++)
		  			                if ($.inArray(me.allUsedSlots[i].d, arrAllDays) == -1)
		  			                    arrAllDays[arrAllDays.length] = me.allUsedSlots[i].d;   
		  			            for (var i = 0; (i<arrAllDays.length); i++)
		  			            {
		  			                var d = arrAllDays[i];
		  			                for (var j=0;(j<me.arrTotal[d].length);j++) 
		  			                    me.arrTotal[d][j].available = me.arrTotal[d][j].c;
		  			            }            
		  			            for (var k = 0; (k<arrAllDays.length && !overlapping); k++)
                                {
                                    var d = arrAllDays[k];
                                    me.cacheArr[d] = me.cacheArr[d] || [];
                                    var used = $.merge(me.cacheArr[d].slice(),me.usedSlots[d]);
                                    for (var j=0;(j<me.arrTotal[d].length);j++) 
		  			                        me.arrTotal[d][j].available = me.arrTotal[d][j].c;
                                    for (var i=0;(i<used.length && !overlapping);i++)
				                    {
				                    	used[i].min = used[i].h1 * 60 + used[i].m1*1;
				                    	used[i].c = used[i].quantity1 * 1 + used[i].quantity2 * 1 + used[i].quantity3 * 1 + used[i].quantity4 * 1 + used[i].quantity5 * 1;
				                        for (var j=0;(j<me.arrTotal[d].length  && !overlapping);j++)
				                        {
				                             if (me.arrTotal[d][j].min==used[i].min)
				                             {
				                                 me.arrTotal[d][j].available = me.arrTotal[d][j].available - used[i].c;
				                                 if (me.arrTotal[d][j].available < 0)
				                                 {
				                                     // check if fails with me.usedSlots[d]
				                                     for (var x=0;(x<me.usedSlots[d].length && !overlapping);x++)
				                                         if (me.arrTotal[d][j].min==me.usedSlots[d][x].min)
				                                             overlapping = true;
				                                 }    
				                             }
				                        }
				                    }
                                }
                                var isValid = !overlapping;
                                if (true === isValid) {
                                    var submitted = validator.formSubmitted;
                                    validator.prepareElement( element );
                                    validator.formSubmitted = submitted;
                                    validator.successList.push( element );
                                    delete validator.invalid[ element.name ];
                                    validator.showErrors();
                                  
                                } else {
                                    for (var i = 0; (i<arrAllDays.length); i++)
		  			                {
		  			                    var d = arrAllDays[i];
		  			                    for (var j=0;(j<me.arrTotal[d].length);j++) 
		  			                        me.arrTotal[d][j].available = me.arrTotal[d][j].c;
		  			                }
		  			                me.loadOK = true;
                                    var errors = {};
                                    errors[ element.name ] = validator.defaultMessage( element, "avoid_overlapping" );
                                    validator.invalid[ element.name ] = true;
                                    validator.showErrors( errors );
                                    element.focus();
                                }
                                previous.valid = isValid;
                                validator.stopRequest( element, isValid );
                                cp_tslotsbk_avoid_overlapping--;    
                            }
                        });
                        return "pending";
                    }
					return true;    
                }
			    $.validator.addMethod('avoid_overlapping', avoid_over_function);
			    $.validator.addMethod("sbquantity_min", function(value, element) {
			        var f = $( '#field' + me.form_identifier + '-' + me.index ).parents( "form" );
			    	var v = 0;
			        f.find(".sbquantity").each( function(){
			            v += $(this).val();
			    	});
                  return (v>0)?true:false;
                }, "Please select a quantity");
			}
		},
		val:function()
		{
		    return 0;
		}		
	}
);