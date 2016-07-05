

OptionExtended.Main.addMethods({
	


  reloadLayoutSelect : function(optionId, element){

    var type = element.getValue();
    var layoutSelect = $('optionextended_'+optionId+'_layout');
    var layout = layoutSelect.getValue();  
		layoutSelect.options.length = 1;
	  switch (type){
		  case "radio":
			  layoutSelect.options[1] = new Option(this.beforeOption, "before");								
			  layoutSelect.options[2] = new Option(this.belowOption, "below");						
			  layoutSelect.options[3] = new Option(this.mainImage, "swap");					
			  layoutSelect.options[4] = new Option(this.grid, "grid");
			  layoutSelect.options[5] = new Option(this.gridcompact, "gridcompact");			  
			  layoutSelect.options[6] = new Option(this.list, "list");
	      switch (layout){     
          case 'before'     :layoutSelect.selectedIndex = 1;break;
          case 'below'      :layoutSelect.selectedIndex = 2;break;
          case 'swap'       :layoutSelect.selectedIndex = 3;break;
          case 'grid'       :layoutSelect.selectedIndex = 4;break;    
          case 'gridcompact':layoutSelect.selectedIndex = 5;break;           
          case 'list'       :layoutSelect.selectedIndex = 6  
		    }				  			  				
		    break;							
		  case "checkbox":							
			  layoutSelect.options[1] = new Option(this.belowOption, "below");					
			  layoutSelect.options[2] = new Option(this.grid, "grid");
			  layoutSelect.options[3] = new Option(this.gridcompact, "gridcompact");			  
			  layoutSelect.options[4] = new Option(this.list, "list");	
	      switch (layout){       
          case 'below'       :layoutSelect.selectedIndex = 1;break;
          case 'grid'        :layoutSelect.selectedIndex = 2;break; 
          case 'gridcompact' :layoutSelect.selectedIndex = 3;break;              
          case 'list'        :layoutSelect.selectedIndex = 4  
		    }						  			  		  							
		    break;				
		  case "drop_down":		
			  layoutSelect.options[1] = new Option(this.beforeOption, "before");								
			  layoutSelect.options[2] = new Option(this.belowOption, "below");					
			  layoutSelect.options[3] = new Option(this.mainImage, "swap");					
			  layoutSelect.options[4] = new Option(this.colorPicker, "picker");		
			  layoutSelect.options[5] = new Option(this.colorPickerSwap, "pickerswap");	
	      switch (layout){       
          case 'before'     :layoutSelect.selectedIndex = 1;break;
          case 'below'      :layoutSelect.selectedIndex = 2;break;
          case 'swap'       :layoutSelect.selectedIndex = 3;break;
          case 'picker'     :layoutSelect.selectedIndex = 4;break;    
          case 'pickerswap' :layoutSelect.selectedIndex = 5  
		    }					  			  		  			  			
		    break;							
		  case "multiple":									
			  layoutSelect.options[1] = new Option(this.belowOption, "below");
			  if (layout == 'below') 
			    layoutSelect.selectedIndex = 1;				  					  				  			
	  }	
	  
	  this.changePopup(optionId);	  
	  this.reloadSd(optionId, type);    
  },	

	
	
	showTextArea : function(element){
		var inputId = element.id.sub('_show', '');
		var input = $(inputId);
		var textArea = new Element('textarea', {'id' : inputId, 'name' : input.name}).addClassName('optionextended-textarea');
		textArea.value = input.value;
		element.hide();		
		Element.replace(input, textArea);
		$(inputId + '_hide').show();		
	},
	
	
	hideTextArea : function(element){
		var inputId = element.id.sub('_hide', '');
		var textArea = $(inputId);
		var input = new Element('input', {'id' : inputId, 'name' : textArea.name, 'type' : 'text', 'value' : textArea.value}).addClassName('optionextended-textfield');
		element.hide();
		Element.replace(textArea, input);
		$(inputId + '_show').show();
	},	
		
		
		
		
	changePopup : function(optionId){
		var popupCheckbox = $('optionextended_'+optionId+'_popup');
		var layout = $('optionextended_'+optionId+'_layout').value;
		if (layout == 'swap'){
			popupCheckbox.checked = false;
			popupCheckbox.disabled = true;
		} else if(popupCheckbox.disabled){
			popupCheckbox.disabled = false;			
		}	
	},
	
	
  checkCheckboxes : function(element){
    var container = element.up('table');
    var elements = Element.select(container, '.optionextended-sd-input');
    for(var i=0; i<elements.length;i++)
      elements[i].checked = element.checked;
  },
  
  
  setScope : function(element, inputId, type){
    var input = $(inputId);
    if (element.checked)
      input.disable();
    else
      input.enable();

    if (type == 'note' || type == 'description'){
      var clickToEditLink = $(inputId+'_show');    
      if (element.checked)
        clickToEditLink.hide();
      else 
        clickToEditLink.show();        
    }          
  } 
	 
});



