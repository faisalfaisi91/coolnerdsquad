$.fbuilder.typeList.push({
	  id:"fslots",
	  name:"Slots booking",
	  control_category:1
});
$.fbuilder.controls[ 'fslots' ]=function(){  this.init();  };
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
	  	  dateFormat:"mm/dd/yy",
	  	  showDropdown:false,
	  	  showTotalCost:false,
	  	  flatPrice:false,
	  	  showTotalCostFormat:"$ {0}",
	  	  dropdownRange:"-10:+10",
	  	  working_dates:[true,true,true,true,true,true,true],
	  	  numberOfMonths:1,
	  	  firstDay:0,
	  	  minDate:"0",
	  	  maxDate:"",
	  	  defaultDate:"",
	  	  invalidDates:"",
	  	  tmpinvalidDates:[],
	  	  required:true,
		  militaryTime:1,
	  	  formattime:function(t)
		  {
		      var h = Math.floor(t/60);
		      var m = t%60;
		      var suffix = "";
		      if (this.militaryTime==0)
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
		              if (h==0) h=12;
		              suffix = " AM";
		          }        
		      }
		  	  return (((h<10)?"":"")+h+":"+(m<10?"0":"")+m)+suffix;									
		  },
	  	  showSlotsPrices:function()
	  	  {
	  	      var str = "", h1 = 50, h2 = 50;
	  	      if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1 && this.quantity5>1)
	  	      {
	  	          h1 = 25;
	  	          h2 = 15;
	  	      }
	  	      else if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1)
	  	      {
	  	          h1 = 20;
	  	          h2 = 20;
	  	      }
	  	      else if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1)
	  	      {
	  	          h1 = 40;
	  	          h2 = 20;
	  	      }
	  	      else if (this.quantity1>1 && this.quantity2>1)
	  	      {
	  	          h1 = 40;
	  	          h2 = 30;
	  	      }
	  	      for (var i=0;i<this.prices.length;i++)
	  	      {	
	  	          str += '<div class="slotspricediv '+((i%2)?"even":"odd")+'" >';	
	  	          if (i==0)			          
	  	  	          str += '<label>Slots Price: '+this.prices[i].name+'</label><div class="clearer"></div>';
	  	  	      else
	  	  	          str += '<label>Slots Price: <input type="text" class="nameSlotsprice" i="'+i+'" value="'+this.prices[i].name+'"></label> <input type="button" i="'+i+'" class="deleteSlotsprice" value="Delete"><div class="clearer"></div>';
	  	  	      str += '<div class="column width'+h1+'">Slots #</div>';
	  	  	      str += '<div class="column width'+h2+'">'+(this.quantity1>1?this.label1:"price")+'</div>';
	  	  	      if (this.quantity1>1 && this.quantity2>1)
	  	  	          str += '<div class="column width'+h2+'">'+this.label2+'</div>';
	  	  	      if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1)
	  	  	          str += '<div class="column width'+h2+'">'+this.label3+'</div>'; 
	  	  	      if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1)
	  	  	          str += '<div class="column width'+h2+'">'+this.label4+'</div>'; 
	  	  	      if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1 && this.quantity5>1)
	  	  	          str += '<div class="column width'+h2+'">'+this.label5+'</div>';    	  	  	      
	  	  	      str += '<div class="clearer"></div>';
	  	  	      if (this.prices[i].price1.length<this.max_quantity)
	  	          {
	  	              for (var k=this.prices[i].price1.length;k<this.max_quantity;k++)
	  	              {
	  	                  this.prices[i].price1[k]=0;
	  	                  this.prices[i].price2[k]=0;
	  	                  this.prices[i].price3[k]=0;
	  	                  this.prices[i].price4[k]=0;
	  	                  this.prices[i].price5[k]=0;
	  	              }					        
	  	          }			  	        
	  	          for (var j=0;j<this.max_quantity;j++)
	  	          {
	  	              str += '<div class="column width'+h1+'">slot #'+(j+1)+'</div>';					      	  
	  	              str += '<div class="column width'+h2+'"><input class="large price1" previousvalue="'+(this.prices[i].price1[j])+'" i="'+i+'" j="'+j+'" type="text" value="'+(this.prices[i].price1[j])+'"></div>'; 
	  	              if (this.quantity1>1 && this.quantity2>1)
	  	                  str += '<div class="column width'+h2+'"><input class="large price2" previousvalue="'+(this.prices[i].price2[j])+'" i="'+i+'" j="'+j+'" type="text" value="'+(this.prices[i].price2[j])+'"></div>';		        	  
	  	              if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1)
	  	                  str += '<div class="column width'+h2+'"><input class="large price3" previousvalue="'+(this.prices[i].price3[j])+'" i="'+i+'" j="'+j+'" type="text" value="'+(this.prices[i].price3[j])+'"></div>';		        	  
	  	              if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1)
	  	                  str += '<div class="column width'+h2+'"><input class="large price4" previousvalue="'+(this.prices[i].price4[j])+'" i="'+i+'" j="'+j+'" type="text" value="'+(this.prices[i].price4[j])+'"></div>';		        	  
	  	              if (this.quantity1>1 && this.quantity2>1 && this.quantity3>1 && this.quantity4>1 && this.quantity5>1)
	  	                  str += '<div class="column width'+h2+'"><input class="large price5" previousvalue="'+(this.prices[i].price5[j])+'" i="'+i+'" j="'+j+'" type="text" value="'+(this.prices[i].price5[j])+'"></div>';		        	  
	  	              str += '<div class="clearer"></div>';
	  	          }
	  	          str += '</div>';
	  	          str += '<hr>';
	  	      }
	  	      str += '<input type="button" class="addSlotsprice" value="Add Special Slots Price">';
	  	      var str2 = "";
	  	      for (var m=0;m<this.prices.length;m++)
	  	  		    str2 += '<option  value="'+m+'">'+this.prices[m].name+'</option>';
	  	      $(".p1").html(str2);		    
	  	      $("#slots_prices").html(str);
	  	      return str;
	  	  },
	  	  showQuantity:function(id,value)
	  	  {   
	  	  	  str = '<option value="1" '+((value==1)?"selected":"")+'> - No, don\'t use it - </option>';					  	  
	  	      for (var j=2;j<=500;j++)
	  	          str += '<option value="'+(j)+'" '+((value==j)?"selected":"")+'>Max selection: '+(j)+'</option>';
	  	      $("#"+id).html(str);
	  	      return str;
	  	  },
	  	  showStartQuantity:function(id,value,maxvalue)
	  	  {   
	  	  	  str = '';					  	  
	  	      for (var j=0;j<=maxvalue;j++)
	  	          str += '<option value="'+(j)+'" '+((value==j)?"selected":"")+'>'+(j)+'</option>';
	  	      $("#"+id).html(str);
	  	      return str;
	  	  },		  								
	  	  display:function()
	  	  {
	  	  	  return '<div class="fields '+this.name+' fslots" id="field'+this.form_identifier+'-'+this.index+'"><div class="arrow ui-icon ui-icon-play "></div><div title="Duplicate" class="copy ui-icon ui-icon-copy "></div><div title="Delete" class="remove ui-icon ui-icon-trash "></div><label>'+this.title+''+((this.required)?"*":"")+'</label><div class="dfield"><div class="sbfieldCalendar'+this.name+'"></div><div class="slotsCalendar'+this.name+'"></div><span class="uh">'+this.userhelp+'</span></div><div class="clearer"></div></div>';
	  	  },
	  	  showSlots:function()
	  	  {		
	          function isOpen(h,m,s)
	  	      {                
	  	          for (var i=0;i<s.length;i++)
	  	              if ((h*60+m>=s[i].h1*60+s[i].m1) && (h*60+m<=s[i].h2*60+s[i].m2))
	  	                  return "sopen";
	  	          return "";        
	  	      }			    
	  	      var slots = '';
	  	      for (var i=0;i<24;i++)
	  	      {
	  	          slots += '<div><div>'+i+'</div>';
	  	          for (var j=0;j<60;j=j+5)
	  	              slots += '<div id="d'+i+'-'+j+'" class="slot '+isOpen(i,j,this.slots)+'" ></div>';
	  	          slots += '</div>';
	  	      }
	  	      return slots+this.slots.length;
	  	  },	
	  	  editItemEvents:function() 
	  	  {
	  	      var me = this;
	  	      $(".dateSpecial").datepicker({dateFormat:me.dateFormat,
	  	          firstDay:parseInt(me.firstDay),
	  	      	  onSelect: function(d,inst) {							
	  	      		    $.fbuilder.reloadItems({'field':me});
                }
            });
	  	      function msortadd(arr,newObj){
	  	  	      if (!arr) arr = new Array();
	  	  		    var inserted = false;	
	  	  		    if (arr.length==0)
	  	  		    {
	  	  		        arr[0] = jQuery.extend({},newObj);
                    inserted = true; 
	  	  		    }
                for(var i =0;(i<arr.length && !inserted);i++){
                    if(i==0 && newObj.min<arr[i].min){
                	      arr.splice(i, 0, jQuery.extend({},newObj));
                	      inserted = true;                            
                    }
                    else if(newObj.min>arr[i].min && (arr.length==i+1 || newObj.min<arr[i+1].min )){
                	      arr.splice(i+1, 0, jQuery.extend({},newObj));
                	      inserted = true;
                    }	
                }              
	  	          return arr;
            }
            function msortSpecial(arr,d){
	  	  		    var inserted = false;	
	  	  		    if (arr.length==0)
	  	  		    {
	  	  		    	  arr[0] = {type:d,slots:new Array()};
                    inserted = true; 
	  	  		    }
                for(var i =0;(i<arr.length && !inserted);i++){
                    if(i==0 && d<arr[i].type){
                    	 arr.splice(i, 0, {type:d,slots:new Array()});
                    	 inserted = true;                            
                    }
                    else if(d>arr[i].type && (arr.length==i+1 || d<arr[i+1].type )){
                    	 arr.splice(i+1, 0, {type:d,slots:new Array()});
                    	 inserted = true;
                    }	
                }              
	  	  		    return arr;
            }            
	  	      function showSpecialDates()
	  	  	  {
	  	  	      str = "";
	  	  	      for (var i=0;i<me.slots_special.length;i++)	
	  	  	          str += '<div class="day_header">'+me.slots_special[i].type+'<a class="deleteSpecial" href="" ind="'+i+'">Delete</a><a href="" class="openSpecial"  n="'+me.slots_special[i].type+'">Open</a><div id="day_slots'+me.slots_special[i].type+'" i="'+me.slots_special[i].type+'" class="day_slots" ind="'+i+'"></div></div>';
	  	  	      $("#special_dates").html(str);
	  	  	      for (var i=0;i<me.slots_special.length;i++)
                    show_day_slots_form(me.slots_special[i].type,me.slots_special[i].slots,i);                
	  	      }
            function show_day_slots_form(i,arr,ind)
            {
            	  var str = "";			  	  
            	  var s = $( '#day_slots'+i );
	  	    	  	str += '<div class="add_slots_f" i="'+i+'" ind="'+ind+'">';
	  	    	  	str += '<label>Add a single appointment time</label><br />';
	  	    	  	str += '<div>From<br /><select class="h1">';
	  	    	  	for (var h=0;h<24;h++)
	  	  		        str += '<option '+((h==8)?"selected":"")+' value="'+h+'">'+((h<10)?"0":"")+h+'</option>';    
	  	  		    str += '</select>';
	  	  		    str += '<select class="m1">';
	  	    	  	for (var m=0;m<60;m=m+1)
	  	  		        str += '<option '+((m==0)?"selected":"")+' value="'+m+'">'+((m<10)?"0":"")+m+'</option>';    
	  	  		    str += '</select></div>';
	  	    	  	str += '<div>capacity<br /><input type="text" class="c1" value="1"></div>';
	  	    	  	str += '<div>price<br /><select class="p1">';
	  	    	  	for (var m=0;m<me.prices.length;m++)
	  	  		        str += '<option  value="'+m+'">'+me.prices[m].name+'</option>';    
	  	  		    str += '</select></div><input type="button" class="add1" value="Add">';
	  	  		    str += '<hr />';
	  	  		    str += '<label>Add multiple appointment times</label><br />';
	  	    	  	str += '<div>From<br /><select class="h2">';
	  	    	  	for (var h=0;h<24;h++)
	  	  		        str += '<option '+((h==8)?"selected":"")+' value="'+h+'">'+((h<10)?"0":"")+h+'</option>';    
	  	  		    str += '</select>';
	  	  		    str += '<select class="m2">';
	  	    	  	for (var m=0;m<60;m=m+5)
	  	  		        str += '<option '+((m==8)?"selected":"")+' value="'+m+'">'+((m<10)?"0":"")+m+'</option>';    
	  	  		    str += '</select></div>';
	  	    	  	str += '<div>capacity<br /><input type="text" class="c2" value="1"></div>';
	  	  		    str += '<div>each<br /><select class="s2">';
	  	  		    str += '<option  value="1">1 min</option>';
	  	    	  	for (var m=5;m<=60;m=m+5)
	  	    	  	{
	  	  		        str += '<option '+((m==30)?"selected":"")+' value="'+m+'">'+m+' min</option>'; 
	  	  		        if (m==10)
	  	  		            str += '<option  value="12">12 min</option>';
	  	  		    }    
	  	  	      str += '<option value="90">90 min</option>';
	              str += '<option value="120">120 min</option>';
	              str += '<option value="180">180 min</option>';
	              str += '<option value="240">240 min</option>';       
	  	  		    str += '</select></div>';
	  	  		
	  	  		    str += '<div>times<br /><select class="n2">';
	  	    	  	for (var c=1;c<100;c++)
	  	  		        str += '<option '+((c==2)?"selected":"")+' value="'+c+'">'+c+'</option>';    
	  	  		    str += '</select></div>';
	  	  		    str += '<div>price<br /><select class="p2">';
	  	    	  	for (var m=0;m<me.prices.length;m++)
	  	  		        str += '<option  value="'+m+'">'+me.prices[m].name+'</option>';    
	  	  		    str += '</select></div><input type="button" class="add2" value="Add">';
	  	  		    str += '</div>';
	  	  		    str += '<div class="onlySlots" i="'+i+'" ind="'+ind+'">';			  	  	  
	  	    	    str += '</div>';
	  	    	    str += '<div><input type="button" value="Delete marked slots" class="deletemarked"></div>';
	  	    	  	s.html(str);
	  	    	    show_day_slots(i,arr);
            }
            function show_day_slots(i,arr)
            {
                var str = "";
                if (arr)
	  	    	  	{
	  	    	  	    var s = $( '#day_slots'+i+' .onlySlots' );
	  	    	  	    str = '<hr />';
	  	    	  	    var ind = 0;
	  	    	  	    for (var j=0;j<arr.length;j++)
	  	    	  	    {
	  	    	  	        ind = arr[j].p;
	  	    	  	        str += '<div class="s">'+me.formattime(arr[j].h1*60+arr[j].m1*1)+'<br /><input type="checkbox">c='+arr[j].c+'<br />'+((ind>0 && me.prices[ind] && me.prices[ind].name)?me.prices[ind].name:"&nbsp;")+'</div>';
	  	    	  	    }
	  	    	  	    s.html(str);    
	  	    	    }	
            }	
	  	  	  for (var i=0;i<7;i++)
	  	      	    show_day_slots_form(i,me.slotsavail["d"+i],0)
	  	      showSpecialDates();
	  	  	  var evt = [				  
	  	  	          {s:"#sMin_quantity",e:"change", l:"min_quantity"},
	  	  	          {s:"#sMax_quantity",e:"change", l:"max_quantity", f:function(el){
	  	  	  	  	  var v = (el.val()>=1);$(".max_quantity_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.max_quantity = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},//max_quantity_scope
	  	  	  	  {s:"#sQuantity1",e:"change", l:"quantity1", f:function(el){
	  	  	  	  	  var v = (el.val()>1);$(".quantity1_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.quantity1 = el.val();
	  	  	  	      me.showStartQuantity('sQuantity1start',me.quantity1start,me.quantity1);	
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sQuantity1start",e:"change", l:"quantity1start", f:function(el){
	  	  	  	      me.quantity1start = el.val();  	  	  	  	  
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sLabel1",e:"keyup", l:"label1", f:function(el){
	  	  	  	  	  me.label1 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sQuantity2",e:"change", l:"quantity2", f:function(el){
	  	  	  	  	  var v = (el.val()>1);$(".quantity2_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.quantity2 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sLabel2",e:"keyup", l:"label2", f:function(el){
	  	  	  	  	  me.label2 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sQuantity3",e:"change", l:"quantity3", f:function(el){
	  	  	  	  	  var v = (el.val()>1);$(".quantity3_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.quantity3 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sLabel3",e:"keyup", l:"label3", f:function(el){
	  	  	  	  	  me.label3 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sQuantity4",e:"change", l:"quantity4", f:function(el){
	  	  	  	  	  var v = (el.val()>1);$(".quantity4_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.quantity4 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sLabel4",e:"keyup", l:"label4", f:function(el){
	  	  	  	  	  me.label4 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sQuantity5",e:"change", l:"quantity5", f:function(el){
	  	  	  	  	  var v = (el.val()>1);$(".quantity5_scope")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  me.quantity5 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	  	  {s:"#sLabel5",e:"keyup", l:"label5", f:function(el){
	  	  	  	  	  me.label5 = el.val();
	  	  	  	  	  me.showSlotsPrices();
	  	  	  	  	  return el.val();
	  	  	  	  }},	
	  	  	      {s:"#sDateFormat",e:"change", l:"dateFormat"},
	  	  	  	  {s:"#sNumberOfMonths",e:"change", l:"numberOfMonths"},	  	  		  
	  	  	  	  {s:"#sFirstDay",e:"change", l:"firstDay"},
				  {s:"#sMilitaryTime",e:"change", l:"militaryTime"},	  	  		  
	  	  	  	  {s:"#sMinDate",e:"change", l:"minDate"},
	  	  	  	  {s:"#sMaxDate",e:"change", l:"maxDate"},
	  	  	  	  {s:"#sInvalidDates",e:"change", l:"invalidDates"},
	  	  	  	  {s:"#sDefaultDate",e:"change", l:"defaultDate"},
	  	  	  	  {s:"#sDropdownRange",e:"keyup", l:"dropdownRange"},
	  	  	  	  {s:"#sShowTotalCostFormat",e:"keyup", l:"showTotalCostFormat"},	  	  		  
	  	  	  	  {s:"#sShowTotalCost",e:"click", l:"showTotalCost", f:function(el){
	  	  	  	  	  var v = el.is(':checked'); 
	  	  	  	  	  $("#divTotalCostFormat")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  return v;
	  	  	  	  }},
	  	  	  	  {s:"#sShowDropdown",e:"click", l:"showDropdown", f:function(el){
	  	  	  	  	  var v = el.is(':checked'); 
	  	  	  	  	  $("#divdropdownRange")[( v ) ? 'show' : 'hide']();
	  	  	  	  	  return v;
	  	  	  	  }},
	  	  	  	  {s:"#sFlatPrice",e:"click", l:"flatPrice", f:function(el){
	  	  	  	  	  return el.is(':checked');
	  	  	  	  }}
	  	  	  	  
	  	  	  ];
	  	  	  $(document).off("keyup", "input.price1").on("keyup", "input.price1", {obj: this}, function(e) {
	  	  	      var i = $(this).attr("i")*1,j = $(this).attr("j")*1,v = $(this).val(),pv = $(this).attr("previousvalue");
	  	  	      for (var k=j;k<e.data.obj.max_quantity;k++)
	  	  	          if ($("input.price1[i='"+i+"'][j='"+k+"']").attr("previousvalue")==pv)
	  	  	          {
	  	  	              $("input.price1[i='"+i+"'][j='"+k+"']").attr("previousvalue",pv);
	  	  	              $("input.price1[i='"+i+"'][j='"+k+"']").val(v);
	  	  	              e.data.obj.prices[i].price1[k] = v;
	  	  	          }
	  	  	      e.data.obj.prices[i].price1[j] = v;
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  });
	  	  	  $(document).off("keyup", "input.price2").on("keyup", "input.price2", {obj: this}, function(e) {
	  	  	      var i = $(this).attr("i")*1,j = $(this).attr("j")*1,v = $(this).val(),pv = $(this).attr("previousvalue");
	  	  	      for (var k=j;k<e.data.obj.max_quantity;k++)
	  	  	          if ($("input.price2[i='"+i+"'][j='"+k+"']").attr("previousvalue")==pv)
	  	  	          {
	  	  	              $("input.price2[i='"+i+"'][j='"+k+"']").attr("previousvalue",pv);
	  	  	              $("input.price2[i='"+i+"'][j='"+k+"']").val(v);
	  	  	              e.data.obj.prices[i].price2[k] = v;
	  	  	          }
	  	  	      e.data.obj.prices[$(this).attr("i")*1].price2[$(this).attr("j")*1] = $(this).val();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	  	  $(document).off("keyup", "input.price3").on("keyup", "input.price3", {obj: this}, function(e) {
	  	  	      var i = $(this).attr("i")*1,j = $(this).attr("j")*1,v = $(this).val(),pv = $(this).attr("previousvalue");
	  	  	      for (var k=j;k<e.data.obj.max_quantity;k++)
	  	  	          if ($("input.price3[i='"+i+"'][j='"+k+"']").attr("previousvalue")==pv)
	  	  	          {
	  	  	              $("input.price3[i='"+i+"'][j='"+k+"']").attr("previousvalue",pv);
	  	  	              $("input.price3[i='"+i+"'][j='"+k+"']").val(v);
	  	  	              e.data.obj.prices[i].price3[k] = v;
	  	  	          }
	  	  	      e.data.obj.prices[$(this).attr("i")*1].price3[$(this).attr("j")*1] = $(this).val();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	  	  $(document).off("keyup", "input.price4").on("keyup", "input.price4", {obj: this}, function(e) {
	  	  	      var i = $(this).attr("i")*1,j = $(this).attr("j")*1,v = $(this).val(),pv = $(this).attr("previousvalue");
	  	  	      for (var k=j;k<e.data.obj.max_quantity;k++)
	  	  	          if ($("input.price4[i='"+i+"'][j='"+k+"']").attr("previousvalue")==pv)
	  	  	          {
	  	  	              $("input.price4[i='"+i+"'][j='"+k+"']").attr("previousvalue",pv);
	  	  	              $("input.price4[i='"+i+"'][j='"+k+"']").val(v);
	  	  	              e.data.obj.prices[i].price4[k] = v;
	  	  	          }
	  	  	      e.data.obj.prices[$(this).attr("i")*1].price4[$(this).attr("j")*1] = $(this).val();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	  	  $(document).off("keyup", "input.price5").on("keyup", "input.price5", {obj: this}, function(e) {
	  	  	      var i = $(this).attr("i")*1,j = $(this).attr("j")*1,v = $(this).val(),pv = $(this).attr("previousvalue");
	  	  	      for (var k=j;k<e.data.obj.max_quantity;k++)
	  	  	          if ($("input.price5[i='"+i+"'][j='"+k+"']").attr("previousvalue")==pv)
	  	  	          {
	  	  	              $("input.price5[i='"+i+"'][j='"+k+"']").attr("previousvalue",pv);
	  	  	              $("input.price5[i='"+i+"'][j='"+k+"']").val(v);
	  	  	              e.data.obj.prices[i].price5[k] = v;
	  	  	          }
	  	  	      e.data.obj.prices[$(this).attr("i")*1].price5[$(this).attr("j")*1] = $(this).val();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	      $(document).off("click", "input.addSlotsprice").on("click", "input.addSlotsprice", {obj: this}, function(e) {
                e.data.obj.prices[e.data.obj.prices.length] = {name:"special",price1:new Array(),price2:new Array(),price3:new Array(),price4:new Array(),price5:new Array()};
                me.showSlotsPrices();
                $.fbuilder.reloadItems({'field':e.data.obj});
            });
	  	      $(document).off("click", "input.deleteSlotsprice").on("click", "input.deleteSlotsprice", {obj: this}, function(e) {
	  	          e.data.obj.prices.splice($(this).attr("i")*1, 1 ); 
	  	  	      me.showSlotsPrices();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	      $(document).off("keyup", "input.nameSlotsprice").on("keyup", "input.nameSlotsprice", {obj: this}, function(e) {		        
	  	  	      e.data.obj.prices[$(this).attr("i")*1].name = $(this).val();
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	      });
	  	  	  $(".sb_working_dates .day_header input[type='checkbox']").bind("click", {obj: this}, function(e) {
	  	  	      e.data.obj.working_dates[$(this).val()] = $(this).is(':checked');
	  	  	  	  $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  });
	  	      $(".sb_working_dates .day_header .openSpecial").bind("click", {obj: this}, function(e) {
	  	  	      var s = "#day_slots"+$(this).attr("n");
	  	  	      var c = ($(s).css('display') == 'none');
	  	  	      $(".day_slots").each( function( index, element ){
                        $(this).hide();$(this).parent().find(".openSpecial").html("Open");
                });
	  	  	      if (c){
	  	  	          $(s).show();$(this).html("Close")
	  	  	      }else {
	  	  	          $(s).hide();$(this).html("Open")
	  	  	      }
	  	  	      return false;
	  	  	  });
	  	  	  $(".sb_working_dates .addSpecial").bind("click", {obj: this}, function(e) {
	  	  	  	  if ($(".dateSpecial").val()!="")
	  	  	  	  {
	  	  	  	  	  var d = $.datepicker.formatDate('yy-mm-dd', $.datepicker.parseDate(me.dateFormat, $(".dateSpecial").val()));
	  	  	  	      e.data.obj.slots_special = msortSpecial(e.data.obj.slots_special,d);
	  	  	  	      showSpecialDates();
	  	  	  	      $.fbuilder.editItem(e.data.obj.index);
	  	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  	  }
	  	  	  });
	  	  	  $(".sb_working_dates .add_slots_f .add1").bind("click", {obj: this}, function(e) {
	  	  	  	  var f = $(this).parents(".add_slots_f");
	  	  	  	  var d = f.attr("i")+"";
	  	  	  	  var newObj = {h1:f.find(".h1").val(),m1:f.find(".m1").val(),c:f.find(".c1").val(),p:f.find(".p1").val(),min:f.find(".h1").val()*60+f.find(".m1").val()*1};
	  	  	  	  if (d.length==1)
	  	  	  	  {
	  	  	  	      e.data.obj.slotsavail["d"+d] = msortadd(e.data.obj.slotsavail["d"+d],newObj);
	  	  	  	      show_day_slots(d,e.data.obj.slotsavail["d"+d]);			
	  	  	  	  }
	  	  	  	  else
	  	  	  	  {
	  	  	  	  	  var ind = f.attr("ind");
	  	  	  	      e.data.obj.slots_special[ind].slots = msortadd(e.data.obj.slots_special[ind].slots,newObj);
	  	  	  	      show_day_slots(d,e.data.obj.slots_special[ind].slots);	  
	  	  	  	  }			                
	  	  	  	  $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  });
	  	  	  $(".sb_working_dates .add_slots_f .add2").bind("click", {obj: this}, function(e) {
	  	  	  	  var f = $(this).parents(".add_slots_f");
	  	  	  	  var n = f.find(".n2").val()*1;
	  	  	  	  var s = f.find(".s2").val()*1;
	  	  	  	  var h = f.find(".h2").val()*1;
	  	  	  	  var m = f.find(".m2").val()*1;
	  	  	  	  var initialMin = h*60+m;
	  	  	  	  var dayofweek = f.attr("i")+"";
	  	  	  	  if (dayofweek.length==1)
	  	  	  	  {
	  	  	  	      for (var i=0;i<n;i++)
	  	  	  	      {
	  	  	  	          min = initialMin + s*i;
	  	  	  	          h = Math.trunc(min/60);
	  	  	  	          m = min%60;
	  	  	  	          var newObj = {h1:h,m1:m,c:f.find(".c2").val(),p:f.find(".p2").val(),min:min};
	  	  	  	          e.data.obj.slotsavail["d"+dayofweek] = msortadd(e.data.obj.slotsavail["d"+dayofweek],newObj);	
	  	  	          }
	  	  	  	      show_day_slots(dayofweek,e.data.obj.slotsavail["d"+dayofweek]);	
	  	  	      }
	  	  	      else
	  	  	      {
	  	  	      	  var ind = f.attr("ind");
	  	  	  	      for (var i=0;i<n;i++)
	  	  	  	      {
	  	  	  	          min = initialMin + s*i;
	  	  	  	          h = Math.trunc(min/60);
	  	  	  	          m = min%60;
	  	  	  	          var newObj = {h1:h,m1:m,c:f.find(".c2").val(),p:f.find(".p2").val(),min:min};
	  	  	  	          e.data.obj.slots_special[ind].slots = msortadd(e.data.obj.slots_special[ind].slots,newObj);
	  	  	          }
	  	  	  	      show_day_slots(dayofweek,e.data.obj.slots_special[ind].slots);	
	  	  	      }					                
	  	  	  	  $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  });
	  	  	  $(".sb_working_dates .day_slots .deletemarked").bind("click", {obj: this}, function(e) {
	  	  	      var f = $(this).parents(".day_slots");
	  	  	      var dayofweek = f.attr("i");
	  	  	  	  var toDeleted = new Array();
	  	  	  	  f.find(".onlySlots input").each( function( index, element ){
                          if ($(this).is(":checked"))
                          toDeleted[toDeleted.length] = index;
                });
                if (dayofweek.length==1)
                {
                    for (var i=toDeleted.length-1;i>=0;i--)
                        e.data.obj.slotsavail["d"+dayofweek].splice(toDeleted[i], 1 );
	  	  	      	  show_day_slots(dayofweek,e.data.obj.slotsavail["d"+dayofweek]);
	  	  	      }
	  	  	      else
	  	  	      {
	  	  	          var ind = f.attr("ind");
                        for (var i=toDeleted.length-1;i>=0;i--)
                            e.data.obj.slots_special[ind].slots.splice(toDeleted[i], 1 );
	  	  	          show_day_slots(dayofweek,e.data.obj.slots_special[ind].slots);
	  	  	      }
	  	  	  	  $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	  });
	  	  	  $(".sb_working_dates .deleteSpecial").bind("click", {obj: this}, function(e) {
	  	  	      var ind = $(this).attr("ind")*1;
	  	  	      e.data.obj.slots_special.splice(ind, 1 );
	  	  	      showSpecialDates();
	  	  	      $.fbuilder.editItem(e.data.obj.index);
	  	  	      $.fbuilder.reloadItems({'field':e.data.obj});
	  	  	      return false;
	  	  	  });
	  	  	  $.fbuilder.controls[ 'ffields' ].prototype.editItemEvents.call(this, evt);
	  	  },
	  	  after_show:function()
	  	  {
	  	      //avoid conflict with a date field
		      function closeOtherDatepicker(){
		          $('#ui-datepicker-div').css("display","none");
		      }
		      setTimeout(closeOtherDatepicker,100);
		      //end avoid conflict with a date field
	  	      function onChangeDate(d)
	  	      {
	  	          var str = "";								
	  	          var day = $.datepicker.formatDate('yy-mm-dd', $.datepicker.parseDate(me.dateFormat, d));
	  	          var ind = -1;
	  	          for (var i=0;ind==-1 && i<me.slots_special.length;i++)
	  	              if (me.slots_special[i].type==day)
	  	                  ind = i;
	  	          if (ind==-1)
	  	          	 arr = me.slotsavail["d"+$.datepicker.parseDate(me.dateFormat, d).getDay()];
	  	          else
	  	          	 arr = me.slots_special[ind].slots;
	  	          for (var j=0;arr && j<arr.length;j++)
	  	            str += '<div>'+me.formattime(arr[j].h1*60+arr[j].m1*1)+'</div>';	 
	  	          var s = $( '#field' + me.form_identifier + '-' + me.index + ' .slotsCalendar'+me.name );
	  	          s.html("<div class=\"slots\">"+d+"<br />"+str+"</div>");
	  	      			
	  	      }
	  	    	var me  = this,
	  	  		e   = $( '#field' + me.form_identifier + '-' + me.index + ' .sbfieldCalendar'+me.name ),
	  	  		str = "",
	  	  		op = "";
	  	  	  if (typeof me.slotsavail === 'undefined')
	  	  	      me.slotsavail = $.extend({}, me.slotsavailInit);
	  	  	  me.minDateTmp = me.minDate ; 
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
	  	  	  if (me.minDate.toLowerCase().indexOf("h")!=-1)
			  {
			      var h = me.minDate.toLowerCase().replace(/h/gi, "");
			      me.minDateTmp = new Date(((new Date).getTime() + h * 60 * 60 * 1000) )
			  }
			  else if (me.minDate == parseInt(me.minDate, 10))
			  {
			      me.minDateTmp = new Date(((new Date).getTime() + parseInt(me.minDate) * 24 * 60 * 60 * 1000) )    
			  }    	  	  	      	
	  	  	  this.tmpinvalidDates = this.invalidDates;
	  	  	  this.tmpinvalidDates = this.tmpinvalidDates.replace( /\s+/g, '' );
	  	  	  if( !/^\s*$/.test( this.tmpinvalidDates ) )
	  	  	  {
	  	  	  	  var	dateRegExp = new RegExp( /^\d{1,2}\/\d{1,2}\/\d{4}$/ ),
	  	  	  	      counter = 0,
	  	  	  	      dates = this.tmpinvalidDates.split( ',' );
	  	  	  	  this.tmpinvalidDates = [];
	  	  	  	  for( var i = 0, h = dates.length; i < h; i++ )
	  	  	  	  {
	  	  	  	  	  var range = dates[ i ].split( '-' );
                  
	  	  	  	  	  if( range.length == 2 && range[0].match( dateRegExp ) != null && range[1].match( dateRegExp ) != null )
	  	  	  	  	  {
	  	  	  	  	  	  var fromD = new Date( range[ 0 ] ),
	  	  	  	  	  	  	  toD = new Date( range[ 1 ] );
	  	  	  	  	  	  while( fromD <= toD )
	  	  	  	  	  	  {
	  	  	  	  	  	  	  this.tmpinvalidDates[ counter ] = fromD;
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
	  	  	  	  	  	  	  	  this.tmpinvalidDates[ counter ] = new Date( range[ j ] );
	  	  	  	  	  	  	  	  counter++;
	  	  	  	  	  	  	  }
	  	  	  	  	  	  }
	  	  	  	  	  }
	  	  	  	  }
	  	  	  }
	  	  	  me.special_days = new Array();
			  for (var i=0;i<me.slots_special.length;i++)
			      me.special_days[me.special_days.length] = me.slots_special[i].type;
	  	      e.datepicker({numberOfMonths:parseInt(me.numberOfMonths),
	  	  	      firstDay:parseInt(me.firstDay),
	  	  	      minDate:me.minDateTmp,
	  	  	      maxDate:me.maxDate,
	  	  	      dateFormat:me.dateFormat,
	  	  	      defaultDate:me.defaultDate,
	  	  	      changeMonth: me.showDropdown, 
	  	  	      changeYear: me.showDropdown,
	  	  	      yearRange: ((me.showDropdown)?me.dropdownRange:""),
	  	  	      onSelect: function(d,inst) {
	  	  	      	  onChangeDate(d);
                },
	  	  	      beforeShowDay: function (d) {
	  	  	          var day = $.datepicker.formatDate('yy-mm-dd', d);
                    var c =  new Array(day);
                    if (me.working_dates[d.getDay()]==0  && ($.inArray(day, me.special_days) == -1))
                        c.push("nonworking");
                    for( var i = 0, l = me.tmpinvalidDates.length; i < l; i++ )
                    {
                    	if (d.getTime() === me.tmpinvalidDates[i].getTime())
                    	   c.push("nonworking invalidDate");
                    }    
                    return [true,c.join(" ")];
	              }
	          });
	          me.tmpinvalidDatestime = new Array();
            for (var i=0;i<me.tmpinvalidDates.length;i++)
                me.tmpinvalidDatestime[i]=me.tmpinvalidDates[i].getTime();
            function DisableSpecificDates(date) {
                var currentdate = date.getTime();
                if ($.inArray(currentdate, me.tmpinvalidDatestime) > -1 ) 
                    return false;
                if (me.working_dates[date.getDay()]==0)
                    return false; 
                return true;
            }
            var sum = 0;
            for (var i=0;i<7;i++)
                sum += me.working_dates[i];
            if (sum>0)
            {       
	  	          var nextdateAvailable = e.datepicker("getDate");
                while (!DisableSpecificDates(nextdateAvailable))
                    nextdateAvailable.setDate(nextdateAvailable.getDate() + 1);
                e.datepicker("setDate", nextdateAvailable);
                me.getD = nextdateAvailable;  
	  	          onChangeDate($.datepicker.formatDate(me.dateFormat, nextdateAvailable));
	  	      }
	  	      		    	        		    	        	        
	      },
	  	  showSpecialDataInstance: function()
	      {
	  	      var str = "", e   = $( '#field' + this.form_identifier + '-' + this.index + ' .sbfieldCalendar'+this.name ),dayNames  = e.datepicker( "option", "dayNames" );
	  	      str += '<div class="sb_working_dates">Working Dates';
	  	      for (var i=0;i<7;i++)
	  	      {
	  	          str += '<div class="day_header"><input name="sWD'+i+'" id="sWD'+i+'" value="'+i+'" type="checkbox" '+((this.working_dates[i])?"checked":"")+'/>'+dayNames[i]+'<a class="openSpecial" href="" n="'+i+'">Open</a><div id="day_slots'+i+'" i="'+i+'" class="day_slots"></div></div>';
	  	      }   
	  	      str += '<div>Special Dates</div>';
	  	      str += '<input class="dateSpecial" type="text"  value=""/><input type="button" class="addSpecial" value="Add"><div id="special_dates">';
	  	      str += '</div></div>';
	  	      //Minimum slots to be selected
	  	      str += '<div class="prices"><label>Slots Price Configuration[<a class="helpfbuilder" text="Add the price offered: service name, price (number only, without currency symbol or code) and its duration">help?</a>]</label><div class="clearer"></div>';
	  	      str += '<hr /><div class="column width50">Min slots to be selected</div><div class="column width50">';
	  	      str += '<select class="large" id="sMin_quantity">'; 
	  	      for (var j=1;j<=99;j++)
	  	          str += '<option value="'+(j)+'" '+((this.min_quantity==j)?"selected":"")+'>'+(j)+'</option>';    
	  	      str += '</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      str += '<div class="column width50">Max slots to be selected</div><div class="column width50">';
	  	      str += '<select class="large" id="sMax_quantity">'; 
	  	      for (var j=1;j<=99;j++)
	  	          str += '<option value="'+(j)+'" '+((this.max_quantity==j)?"selected":"")+'>'+(j)+'</option>';    
	  	      str += '</select>';
	  	      str += '</div><div class="clearer"></div>';					
	  	      str += '<div class="max_quantity_scope">';//begin max_quantity_scope					
	  	      str += '<hr /><div class="column width50">Use quantity selection field</div><div class="column width50">';
	  	      str += '<select class="large" id="sQuantity1">'+this.showQuantity('sQuantity1',this.quantity1)+'</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      					
	  	      str += '<div class="quantity1_scope" style="display:'+((this.quantity1>1)?"":"none")+'">';//begin quantity1_scope					
	  	      str += '<div class="column width50">Start quantity at</div><div class="column width50"><select class="large" id="sQuantity1start">'+this.showStartQuantity('sQuantity1start',this.quantity1start,this.quantity1)+'</select></div><div class="clearer"></div>';
	  	      str += '<div class="column width50">Quantity field label</div><div class="column width50"><input id="sLabel1" type="text" value="'+this.label1+'"></div><div class="clearer"></div>';
	  	      str += '<hr /><div class="column width50">Use 2nd quantity selection field</div><div class="column width50">';
	  	      str += '<select  class="large" id="sQuantity2">'+this.showQuantity('sQuantity2',this.quantity2)+'</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      
	  	      str += '<div class="quantity2_scope" style="display:'+((this.quantity2>1)?"":"none")+'">';//begin quantity2_scope					
	  	      str += '<div class="column width50">Quantity field label</div><div class="column width50"><input id="sLabel2" type="text" value="'+this.label2+'"></div><div class="clearer"></div>';
	  	      str += '<hr /><div class="column width50">Use 3rd quantity selection field</div><div class="column width50">';
	  	      str += '<select  class="large" id="sQuantity3">'+this.showQuantity('sQuantity3',this.quantity3)+'</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      
	  	      str += '<div class="quantity3_scope" style="display:'+((this.quantity3>1)?"":"none")+'">';//begin quantity3_scope					
	  	      str += '<div class="column width50">Quantity field label</div><div class="column width50"><input id="sLabel3" type="text" value="'+this.label3+'"></div><div class="clearer"></div>';
	  	      str += '<hr /><div class="column width50">Use 4th quantity selection field</div><div class="column width50">';
	  	      str += '<select  class="large" id="sQuantity4">'+this.showQuantity('sQuantity4',this.quantity4)+'</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      
	  	      str += '<div class="quantity4_scope" style="display:'+((this.quantity4>1)?"":"none")+'">';//begin quantity4_scope					
	  	      str += '<div class="column width50">Quantity field label</div><div class="column width50"><input id="sLabel4" type="text" value="'+this.label4+'"></div><div class="clearer"></div>';
	  	      str += '<hr /><div class="column width50">Use 5th quantity selection field</div><div class="column width50">';
	  	      str += '<select  class="large" id="sQuantity5">'+this.showQuantity('sQuantity5',this.quantity5)+'</select>';
	  	      str += '</div><div class="clearer"></div>';
	  	      					
	  	      str += '<div class="quantity5_scope" style="display:'+((this.quantity5>1)?"":"none")+'"><div class="column width50">Quantity field label</div><div class="column width50"><input id="sLabel5" type="text" value="'+this.label5+'"></div><div class="clearer"></div></div>';
	  	      
	  	      str += '</div>';//end quantity4_scope
	  	      str += '</div>';//end quantity3_scope
	  	      str += '</div>';//end quantity2_scope
	  	      str += '</div>';//end quantity1_scope
	  	      
	  	      str += '</div>';//end max_quantity_scope
	  	      str += '<hr /><div><input type="checkbox" name="sFlatPrice" id="sFlatPrice" '+((this.flatPrice)?"checked":"")+'/> Check if number of persons doesn\'t affect the price (use only flat price of the time-slot)</div>';
	  	      					
	  	      str += '<hr />';
	  	      str += '<div id="slots_prices">'+this.showSlotsPrices()+'</div>';					
	  	      str += '</div>';
	  	      var sfirst = "";
	  	      for (var i=0;i<7;i++)
	  	      {
	  	          sfirst += '<option value="'+i+'" '+((i==this.firstDay)?"selected":"")+'>'+dayNames[i]+'</option>';
	  	      }
	  	      str += '<div><label>First Date</label><br /><label><select name="sFirstDay" id="sFirstDay">'+sfirst+'</select></div>';
	  	      var snumberOfM = "";
	  	      for (var i=1;i<=12;i++)
	  	      {
	  	          snumberOfM += '<option value="'+i+'" '+((i==this.numberOfMonths)?"selected":"")+'>'+i+'</option>';
	  	      }
	  	      str += '<div><label>Military Time</label><br /><label><select name="sMilitaryTime" id="sMilitaryTime"><option value="1" '+((1==this.militaryTime)?"selected":"")+'>Yes (24 hours)</option><option value="0" '+((1!=this.militaryTime)?"selected":"")+'>No (12 hours AM/PM)</option></select></div>';
              str += '<div><label>Number of months</label><br /><label><select name="sNumberOfMonths" id="sNumberOfMonths">'+snumberOfM+'</select></div>';
	  	      str += '<div><label>Default date [<a class="helpfbuilder" text="You can put one of the following type of values into this field:\n\nEmpty: Leave empty for current date.\n\nDate: A Fixed date with the same date format indicated in the &quot;Date Format&quot; drop-down field.\n\nNumber: A number of days from today. For example 2 represents two days from today and -1 represents yesterday.\n\nString: A smart text indicating a relative date. Relative dates must contain value (number) and period pairs; valid periods are &quot;y&quot; for years, &quot;m&quot; for months, &quot;w&quot; for weeks, and &quot;d&quot; for days. For example, &quot;+1m +7d&quot; represents one month and seven days from today.">help?</a>]</label><br /><input class="medium" name="sDefaultDate" id="sDefaultDate" value="'+$.fbuilder.htmlEncode(this.defaultDate)+'" /></div>';
	  	      str += '<div><label>Min date [<a class="helpfbuilder" text="You can put one of the following type of values into this field:\n\nEmpty: No min Date.\n\nDate: A Fixed date with the same date format indicated in the &quot;Date Format&quot; drop-down field.\n\nNumber: A number of days from today. For example 2 represents two days from today and -1 represents yesterday. For hours interval put the character &quot;h&quot; after the number, example 6 h means 6 hours.\n\nString: A smart text indicating a relative date. Relative dates must contain value (number) and period pairs; valid periods are &quot;y&quot; for years, &quot;m&quot; for months, &quot;w&quot; for weeks, and &quot;d&quot; for days. For example, &quot;+1m +7d&quot; represents one month and seven days from today.">help?</a>]</label><br /><input class="medium" name="sMinDate" id="sMinDate" value="'+$.fbuilder.htmlEncode(this.minDate)+'" /></div>';
	  	      str += '<div><label>Max date [<a class="helpfbuilder" text="You can put one of the following type of values into this field:\n\nEmpty: No max Date.\n\nDate: A Fixed date with the same date format indicated in the &quot;Date Format&quot; drop-down field.\n\nNumber: A number of days from today. For example 2 represents two days from today and -1 represents yesterday.\n\nString: A smart text indicating a relative date. Relative dates must contain value (number) and period pairs; valid periods are &quot;y&quot; for years, &quot;m&quot; for months, &quot;w&quot; for weeks, and &quot;d&quot; for days. For example, &quot;+1m +7d&quot; represents one month and seven days from today.">help?</a>]</label><br /><input class="medium" name="sMaxDate" id="sMaxDate" value="'+$.fbuilder.htmlEncode(this.maxDate)+'" /></div>';
            str += '<div><label>Invalid Dates [<a class="helpfbuilder" text="To define some dates as invalid, enter the dates with the format: mm/dd/yyyy separated by comma; for example: 12/31/2014,02/20/2014 or by hyphen for intervals; for example: 12/20/2014-12/28/2014 ">help?</a>]</label><br /><input class="medium" name="sInvalidDates" id="sInvalidDates" value="'+$.fbuilder.htmlEncode(this.invalidDates)+'" /></div>';
            str += '<div><input type="checkbox" name="sShowDropdown" id="sShowDropdown" '+((this.showDropdown)?"checked":"")+'/><label>Show Dropdown Year and Month</label><div id="divdropdownRange" style="display:'+((this.showDropdown)?"":"none")+'">Year Range [<a class="helpfbuilder" text="The range of years displayed in the year drop-down: either relative to today\'s year (&quot;-nn:+nn&quot;), absolute (&quot;nnnn:nnnn&quot;), or combinations of these formats (&quot;nnnn:-nn&quot;)">help?</a>]: <input type="text" name="sDropdownRange" id="sDropdownRange" value="'+$.fbuilder.htmlEncode(this.dropdownRange)+'"/></div></div>';
	  	      str += '<div><input type="checkbox" name="sShowTotalCost" id="sShowTotalCost" '+((this.showTotalCost)?"checked":"")+'/><label>Show Total Cost</label><div id="divTotalCostFormat" style="display:'+((this.showTotalCost)?"":"none")+'">Total cost format [<a class="helpfbuilder" text="The string {0} will be replaced with the calculated cost. Keep the {0} reference. You can edit the currency symbol or add additional text.">help?</a>]: <input type="text" name="sShowTotalCostFormat" id="sShowTotalCostFormat" value="'+$.fbuilder.htmlEncode(this.showTotalCostFormat)+'"/></div></div>';
	  	      str += '<hr></hr>';
	  	      return str;
	      }
	  }
);