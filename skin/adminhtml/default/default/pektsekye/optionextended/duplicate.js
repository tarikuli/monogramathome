

OptionExtended.Main.addMethods({


	duplicate : function(optionId){	
		
		var typeSelect = $('product_option_'+optionId+'_type');	
		if (typeSelect.value != ''){
		
		  optionExtended.add();
	
		  $('product_option_'+this.lastOptionId+'_title').value = $('product_option_'+optionId+'_title').value;
		
		  var ind = 0;		
		  switch(typeSelect.value){							
			  case 'field'		: ind = 1;break;	
			  case 'area' 		: ind = 2;break;	
			  case 'file' 		: ind = 3;break;	
			  case 'drop_down': ind = 4;break;	
			  case 'radio' 		: ind = 5;break;	
			  case 'checkbox' : ind = 6;break;	
			  case 'multiple' : ind = 7;break;				
			  case 'date' 		: ind = 8;break;					
			  case 'date_time': ind = 9;break;	
			  case 'time'			: ind = 10;break;												
		  }		
		
		  $('product_option_'+this.lastOptionId+'_type').selectedIndex = ind;
		
		  optionExtended.loadStepTwo(this.lastOptionId, true);
					
      $('product_option_'+this.lastOptionId+'_is_require').selectedIndex = $('product_option_'+optionId+'_is_require').selectedIndex;      
      $('product_option_'+this.lastOptionId+'_sort_order').value = $('product_option_'+optionId+'_sort_order').value;	
		  $('optionextended_'+this.lastOptionId+'_note').value = $('optionextended_'+optionId+'_note').value;	

				
		  if (typeSelect.value == 'drop_down' || typeSelect.value == 'radio' || typeSelect.value == 'checkbox' || typeSelect.value == 'multiple'){
				
			  $('optionextended_'+this.lastOptionId+'_layout').selectedIndex = $('optionextended_'+optionId+'_layout').selectedIndex;	
			  $('optionextended_'+this.lastOptionId+'_popup').checked = $('optionextended_'+optionId+'_popup').checked;
			  $('optionextended_'+this.lastOptionId+'_popup').disabled = $('optionextended_'+optionId+'_popup').disabled;
	      if (this.sdEnabled)				
			    $('optionextended_'+this.lastOptionId+'_sd').checked = $('optionextended_'+optionId+'_sd').checked;
			    
    		var selectId,rowId,value,image;			
			  var l = this.rowIdsByOption[optionId].length;	
			  for (var i=0;i<l;i++){
			    rowId = this.rowIdsByOption[optionId][i];
    			selectId = this.selectIdByRowId[rowId];
		      image = {};
		      
    			if (this.imageEnabled){
    			  if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rowId) && this.rowIsActive[rowId] == undefined){
				        image = this.rowsData[rowId].image_object;  			  		    			  
				     } else {
              value = $('optionextended_link_'+selectId+'_file_save').value;
              if (value != '')				   
				        image = value.evalJSON();	  	
				    }  
				    			    
				    if (image.url != undefined)
					    image.toduplicate = true;
				  }
				
		      optionExtended.addRow(this.lastOptionId, typeSelect.value, this.imageEnabled && image.url != undefined);
		        
          if (this.imageEnabled && image.url != undefined)
			      $('optionextended_link_'+this.lastSelectId+'_file_save').value = Object.toJSON(image);
			      
		      if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rowId) && this.rowIsActive[rowId] == undefined){

				    $('product_option_value_'+this.lastSelectId+'_title').value                = this.rowsData[rowId].title;				
				    $('product_option_value_'+this.lastSelectId+'_price').value                = this.rowsData[rowId].price;				
				    $('product_option_value_'+this.lastSelectId+'_price_type').selectedIndex   = this.rowsData[rowId].priceTypeIndex;	
				    $('product_option_value_'+this.lastSelectId+'_sku').value                  = this.rowsData[rowId].sku;	
				    $('product_option_value_'+this.lastSelectId+'_sort_order').value           = this.rowsData[rowId].sort_order;	

	        	if (this.descriptionEnabled)					
				      $('optionextended_'+this.lastSelectId+'_description').value = this.rowsData[rowId].description;
	        	if (this.sdEnabled && this.rowsData[rowId].sdIsChecked){						
				      $('optionextended_value_'+this.lastSelectId+'_sd').checked = true;
				      this.addSdId(this.lastOptionId, this.lastRowId);
				    } 				    
		      } else {
		        				
				    $('product_option_value_'+this.lastSelectId+'_title').value                = $('product_option_value_'+selectId+'_title').value;				
				    $('product_option_value_'+this.lastSelectId+'_price').value                = $('product_option_value_'+selectId+'_price').value;				
				    $('product_option_value_'+this.lastSelectId+'_price_type').selectedIndex   = $('product_option_value_'+selectId+'_price_type').selectedIndex;	
				    $('product_option_value_'+this.lastSelectId+'_sku').value                  = $('product_option_value_'+selectId+'_sku').value;	
				    $('product_option_value_'+this.lastSelectId+'_sort_order').value           = $('product_option_value_'+selectId+'_sort_order').value;	

	        	if (this.descriptionEnabled)					
				      $('optionextended_'+this.lastSelectId+'_description').value = $('optionextended_'+selectId+'_description').value;
	        	if (this.sdEnabled && $('optionextended_value_'+selectId+'_sd').checked){											    		    
				      $('optionextended_value_'+this.lastSelectId+'_sd').checked = true;
				      this.addSdId(this.lastOptionId, this.lastRowId);				    
				    } 
		      }
		      
			  }		

			
		  } else {
		
        $('product_option_'+this.lastOptionId+'_price').value = $('product_option_'+optionId+'_price').value;			
        $('product_option_'+this.lastOptionId+'_price_type').selectedIndex = $('product_option_'+optionId+'_price_type').selectedIndex;
        $('product_option_'+this.lastOptionId+'_sku').value = $('product_option_'+optionId+'_sku').value;
        
		    switch(typeSelect.value){
			    case 'field' :	
			    case 'area'  :
            $('product_option_'+this.lastOptionId+'_max_characters').value = $('product_option_'+optionId+'_max_characters').value;
			      break;	
			    case 'file'  :
            $('product_option_'+this.lastOptionId+'_file_extension').value = $('product_option_'+optionId+'_file_extension').value;
            $('product_option_'+this.lastOptionId+'_image_size_x').value = $('product_option_'+optionId+'_image_size_x').value;  
            $('product_option_'+this.lastOptionId+'_image_size_y').value = $('product_option_'+optionId+'_image_size_y').value;       											
		    }	      
			
		  }  
		

		}
		
	}
	
	 
});



