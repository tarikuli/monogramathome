

OptionExtended.Main.addMethods({
	

	
  getSdColumnInput : function(type, optionId){
    var input;	
    if (type == 'drop_down' || type == 'radio'){
	    input = '<input onclick="optionExtended.uncheckAllRadio('+optionId+')" type="radio" name="optionextended_'+optionId+'_sd" id="optionextended_'+optionId+'_sd" checked="checked" class="radio" value=""/>';
    } else {
	    input = '<input onclick="optionExtended.checkAllCheckboxes(this,'+optionId+')" type="checkbox" name="optionextended_'+optionId+'_sd[]" id="optionextended_'+optionId+'_sd" class="checkbox"  title="'+this.selectAll+'" value=""/>';			
    }	
    return input;
  },
  
	getSdCellInput: function(optionId, selectId, rowId, type, checked){	    
    var input;
    if (checked == undefined) 
      checked = '';
    else if (checked == 1) 
      checked = 'checked="checked"';
             				
    if (type == 'drop_down' || type == 'radio'){
      input = '<input onclick="optionExtended.onRadioCheck('+optionId+','+selectId+')" type="radio" class="radio optionextended-sd-input" name="optionextended_'+optionId+'_sd" id="optionextended_value_'+selectId+'_sd" title="'+this.sdTitle+'" value="" '+checked+'/>';				  			              
    } else {
      input = '<input onclick="optionExtended.onCheckboxCheck(this,'+optionId+','+selectId+')" type="checkbox" class="checkbox optionextended-sd-input" name="optionextended_'+optionId+'_sd[]" id="optionextended_value_'+selectId+'_sd" title="'+this.sdTitle+'" value="" '+checked+'/>';				  			            
    }	

    return input;         
	},  


  uncheckAllRadio : function(optionId){
    $('optionextended_'+optionId+'_sd_field').value = '';
    var l = this.rowIdsByOption[optionId].length;	      
    for (var i=0;i<l;i++){      
      rId = this.rowIdsByOption[optionId][i];					
	    sId = this.selectIdByRowId[rId];	
      if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined){ 
        $('optionextended_value_'+ sId +'_sd_sell').update(0);	     
        this.rowsData[rId].sd_cell_input = '<input onclick="optionExtended.onRadioCheck('+optionId+','+sId+')" type="radio" class="radio optionextended-sd-input" name="optionextended_'+optionId+'_sd" id="optionextended_value_'+sId+'_sd" title="'+this.sdTitle+'" value=""/>';	      
	      this.rowsData[rId].sdIsChecked = false;
      }
    }     
  },	


  checkAllCheckboxes : function(input, optionId){
    var rId,sId;
    var sd = $('optionextended_'+optionId+'_sd_field');
    var l = this.rowIdsByOption[optionId].length;	      
    if (input.checked){
      sd.value = this.rowIdsByOption[optionId].join(','); 
      for (var i=0;i<l;i++){      
        rId = this.rowIdsByOption[optionId][i];					
		    sId = this.selectIdByRowId[rId];	
        if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined){ 
	        $('optionextended_value_'+ sId +'_sd_sell').update(1);
  	      this.rowsData[rId].sd_cell_input = '<input onclick="optionExtended.onCheckboxCheck(this,'+optionId+','+sId+')" type="checkbox" class="checkbox optionextended-sd-input" name="optionextended_'+optionId+'_sd[]" id="optionextended_value_'+sId+'_sd" title="'+this.sdTitle+'" value="" checked="checked"/>';            	        		                   	          
  	      this.rowsData[rId].sdIsChecked = true;
        } else {
	        $('optionextended_value_'+ sId +'_sd').checked = true;
	      }
	    }  	      
    } else {
      sd.value = '';  
      for (var i=0;i<l;i++){      
        rId = this.rowIdsByOption[optionId][i];					
		    sId = this.selectIdByRowId[rId];	
        if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined){ 
	        $('optionextended_value_'+ sId +'_sd_sell').update(0);
  	      this.rowsData[rId].sd_cell_input = '<input onclick="optionExtended.onCheckboxCheck(this,'+optionId+','+sId+')" type="checkbox" class="checkbox optionextended-sd-input" name="optionextended_'+optionId+'_sd[]" id="optionextended_value_'+sId+'_sd" title="'+this.sdTitle+'" value=""/>';            	        		                   	          
  	      this.rowsData[rId].sdIsChecked = false;
        } else {
	        $('optionextended_value_'+ sId +'_sd').checked = false;
	      }
	    } 
    }
  },
  
	
  onRadioCheck : function(optionId, selectId){
    $('optionextended_'+optionId+'_sd_field').value = this.rowIdBySelectId[selectId];
    var l = this.rowIdsByOption[optionId].length;	      
    for (var i=0;i<l;i++){      
      rId = this.rowIdsByOption[optionId][i];					
	    sId = this.selectIdByRowId[rId];	
      if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined){ 
        $('optionextended_value_'+ sId +'_sd_sell').update(0);	     
        this.rowsData[rId].sd_cell_input = '<input onclick="optionExtended.onRadioCheck('+optionId+','+sId+')" type="radio" class="radio optionextended-sd-input" name="optionextended_'+optionId+'_sd" id="optionextended_value_'+sId+'_sd" title="'+this.sdTitle+'" value=""/>';	      
	      this.rowsData[rId].sdIsChecked = false;
      }
    }    
  },
  
  
  onCheckboxCheck : function(input, optionId, selectId){
    var ids = [];
    var sd = $('optionextended_'+optionId+'_sd_field');
    if (sd.value != ''){
  		var s = '['+sd.value+']';
  	  ids = s.evalJSON();
	  }    
    if (input.checked)
      ids.push(this.rowIdBySelectId[selectId]);      
    else 
      ids = ids.without(this.rowIdBySelectId[selectId])
     
    sd.value = ids.join(','); 
  },  
  
  addSdId : function(optionId, rowId){  
    var sd = $('optionextended_'+optionId+'_sd_field');	       
    sd.value += (sd.value != '' ? ',' : '') + rowId;
  },
    
  deleteSdId : function(optionId, rowId){
    var sd = $('optionextended_'+optionId+'_sd_field');
    if (sd.value != ''){
  		var s = '['+sd.value+']';
  	  var ids = s.evalJSON();	       
      sd.value = ids.without(rowId).join(','); 
    }
  },
  
  reloadSd : function(optionId, type){	 	 
		if (this.sdEnabled){
			
      var sdInputType,arraySign,onclick,input;
      		
	    if (type == 'radio' || type == 'drop_down'){	
		    sdInputType = 'radio';	
		    arraySign = '';
		    onclick = 'onRadioCheck(';				    				  			  			
		  } else {    			      														
		    sdInputType = 'checkbox';
		    arraySign = '[]';
		    onclick = 'onCheckboxCheck(this,';				  				  			
	    }			  

	    var element = $('optionextended_'+optionId+'_sd');
	    if ((sdInputType == 'radio' && element.type != 'radio') || (sdInputType == 'checkbox' && element.type != 'checkbox')){
	    
	      var defaultInput;	    
	      if (sdInputType == 'radio')
		      defaultInput = '<input onclick="optionExtended.uncheckAllRadio('+optionId+')" type="radio" name="optionextended_'+optionId+'_sd" id="optionextended_'+optionId+'_sd" checked="checked" class="radio" value=""/>';
	      else 
		      defaultInput = '<input onclick="optionExtended.checkAllCheckboxes(this,'+optionId+')" type="checkbox" name="optionextended_sd[]" id="optionextended_'+optionId+'_sd" class="checkbox"  title="'+this.selectAll+'" value=""/>';			 
		      
		    Element.replace(element, defaultInput);
			      	        
        var l = this.rowIdsByOption[optionId].length;	      
        for (var i=0;i<l;i++){             
          rId = this.rowIdsByOption[optionId][i];					
	        sId = this.selectIdByRowId[rId];
          input = '<input onclick="optionExtended.'+ onclick + optionId +','+sId+')" type="'+sdInputType+'" class="'+sdInputType+' optionextended-sd-input" name="optionextended_'+optionId+'_sd'+arraySign+'" id="optionextended_value_'+sId+'_sd" title="'+this.sdTitle+'" value="" />';	        	
          if (this.accordionEnabled && !this.optionIsNew(optionId) && this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined){ 
            $('optionextended_value_'+ sId +'_sd_sell').update(0);	     
            this.rowsData[rId].sd_cell_input = input;
	          this.rowsData[rId].sdIsChecked = false;
          } else {				            
          	Element.replace($('optionextended_value_'+ sId +'_sd'), input);          
          }
        }
        
        $('optionextended_'+optionId+'_sd_field').value = ''; 
                
	    } 
	     
    }	 
	}
	 
});



