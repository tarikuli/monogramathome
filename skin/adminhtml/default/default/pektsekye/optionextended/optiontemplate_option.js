

var optionExtended = {


  onTypeChange : function() {
    var type = $('type').value;
    switch(type){
        case 'field':
        case 'area':
            $('price').up('tr').show();
            $('price_type').up('tr').show();
            $('sku').up('tr').show();
            $('max_characters').up('tr').show();          
            $('file_extension').clear();            
            $('file_extension').up('tr').hide();
            $('image_size_x').clear();            
            $('image_size_x').up('tr').hide();
            $('image_size_y').clear();            
            $('image_size_y').up('tr').hide();
            $('layout').selectedIndex = 0;            
            $('layout').up('tr').hide();
            $('popup').checked = false;            
            $('popup').up('tr').hide();
            this.hideSd();               
            break;
        case 'file':
            $('price').up('tr').show();
            $('price_type').up('tr').show();
            $('sku').up('tr').show();          
            $('max_characters').clear();                     
            $('max_characters').up('tr').hide();
            $('file_extension').up('tr').show();
            $('image_size_x').up('tr').show();
            $('image_size_y').up('tr').show();              
            $('layout').selectedIndex = 0;            
            $('layout').up('tr').hide();
            $('popup').checked = false;            
            $('popup').up('tr').hide();
            this.hideSd();                 
            break;
        case 'drop_down':
        case 'radio':        
        case 'checkbox':
        case 'multiple':
            $('price').clear();        
            $('price').up('tr').hide();
            $('price_type').selectedIndex = 0;            
            $('price_type').up('tr').hide();
            $('sku').clear();               
            $('sku').up('tr').hide();
            $('max_characters').clear();                     
            $('max_characters').up('tr').hide();
            $('file_extension').clear();            
            $('file_extension').up('tr').hide();
            $('image_size_x').clear();            
            $('image_size_x').up('tr').hide();
            $('image_size_y').clear();            
            $('image_size_y').up('tr').hide();
                                    
            var layout = $('layout');
            this.reloadLayoutSelect(layout, type);           
            layout.up('tr').show();
                                        
            this.changePopup(layout.value);            
            $('popup').up('tr').show();
              
            this.switchSd(type);                                                                                                                                                                 
            break;
        case 'date':
        case 'date_time':
        case 'time':        
            $('price').up('tr').show();            
            $('price_type').up('tr').show();        
            $('sku').up('tr').show(); 
            $('max_characters').clear();                     
            $('max_characters').up('tr').hide();
            $('file_extension').clear();            
            $('file_extension').up('tr').hide();
            $('image_size_x').clear();            
            $('image_size_x').up('tr').hide();
            $('image_size_y').clear();            
            $('image_size_y').up('tr').hide();
            $('layout').selectedIndex = 0;            
            $('layout').up('tr').hide();
            $('popup').checked = false;            
            $('popup').up('tr').hide();
            this.hideSd();             
            break;              
        default:
            $('price').clear();        
            $('price').up('tr').hide();
            $('price_type').selectedIndex = 0;            
            $('price_type').up('tr').hide();
            $('sku').clear();               
            $('sku').up('tr').hide();
            $('max_characters').clear();                     
            $('max_characters').up('tr').hide();
            $('file_extension').clear();            
            $('file_extension').up('tr').hide();
            $('image_size_x').clear();            
            $('image_size_x').up('tr').hide();
            $('image_size_y').clear();            
            $('image_size_y').up('tr').hide();
            $('layout').selectedIndex = 0;            
            $('layout').up('tr').hide();
            $('popup').checked = false;            
            $('popup').up('tr').hide();
            this.hideSd();                           
    }

  },


  changePopup : function(layout) {
		var popupCheckbox = $('popup');
		if (layout == 'swap'){
			popupCheckbox.checked = false;
			popupCheckbox.disabled = true;
		} else if(popupCheckbox.disabled){
			popupCheckbox.disabled = false;			
		}	
  },    

    
  reloadLayoutSelect : function(layoutSelect, type){

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
          case 'below'        :layoutSelect.selectedIndex = 1;break;
          case 'grid'         :layoutSelect.selectedIndex = 2;break;   
          case 'gridcompact'  :layoutSelect.selectedIndex = 3;break;             
          case 'list'         :layoutSelect.selectedIndex = 4  
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
  },

    hideSd : function() {

        var sd = $('sd');
        var sdM = $('sd_multiple');
                
        sd.selectedIndex = 0;
        var l = sdM.options.length;
        while (l--)
          sdM.options[l].selected = false;
          
        sd.up('tr').hide();          
        sdM.up('tr').hide();              

    },
    
    switchSd : function(type) {
      var sd = $('sd');
      var sdM = $('sd_multiple');       
      if (type == 'radio' || type == 'drop_down'){
        var l = sdM.options.length;
        while (l--)
          sdM.options[l].selected = false;        	
        sdM.up('tr').hide(); 
        sd.up('tr').show(); 			    				  			  			
      } else {    		
        sd.selectedIndex = 0;        	      														
        sd.up('tr').hide();            
        sdM.up('tr').show();    			  				  			
      }	
    }	 
};



