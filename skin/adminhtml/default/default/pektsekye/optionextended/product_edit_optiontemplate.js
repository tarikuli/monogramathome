

OptionExtended.Main.addMethods({

templateData : {},


	applyTemplate : function(){
	  var tSelect = $('optionextended_template_select');
	  var templateId = tSelect.value;
	  var tField = $('optionextended_template_ids');
    var ids = tField.value.split(',');
    if (ids.indexOf(templateId) == -1){	  
      tField.value += (tField.value != '' ? ',' : '') + templateId;
      
      var title = tSelect.options[tSelect.selectedIndex].text;
		  Element.insert($('optionextended_template_table'), {'bottom' : this.appliedTemplateRowTemplate.evaluate({'title':title,'templateId':templateId})});		    
		}
	},
	
	removeTemplate : function(templateId){
	  var tField = $('optionextended_template_ids');
	  var ids = tField.value.split(',');
	  tField.value = ids.without(templateId).join(',');

		Element.remove($('optionextended_template_table_row_' + templateId));	
	},




	insertTemplateOptions : function(){
    var	option,value,selectId,children,l,ll,i,ii;
	  var newRowIds = [];
	  var toAddChildren = [];
	  var templateId = $('optionextended_template_select').value;	

	  if (this.templateData[templateId] == undefined)
	    this.loadTemplate(templateId);
	    
	  if (this.templateData[templateId] != undefined){
	  	    
      l = this.templateData[templateId].length;
		  for (i=0;i<l;i++){
		
      	option = this.templateData[templateId][i];
      	
		    optionExtended.add();
	
		    $('product_option_'+this.lastOptionId+'_title').value = option.title;
		

		
		    $('product_option_'+this.lastOptionId+'_type').selectedIndex = option.typeIndex;
		
		    optionExtended.loadStepTwo(this.lastOptionId, true);
					
        $('product_option_'+this.lastOptionId+'_is_require').selectedIndex = option.isRequireIndex;      
        $('product_option_'+this.lastOptionId+'_sort_order').value = option.sortOrder;	
		    $('optionextended_'+this.lastOptionId+'_note').value = option.note;	

				
		    if (option.type == 'drop_down' || option.type == 'radio' || option.type == 'checkbox' || option.type == 'multiple'){
				
			    $('optionextended_'+this.lastOptionId+'_layout').selectedIndex = option.layoutIndex;	
			    $('optionextended_'+this.lastOptionId+'_popup').checked = option.popupChecked;
			    $('optionextended_'+this.lastOptionId+'_popup').disabled = option.popupDisabled;
			      

			
			    ll = option.values.length;	
			    for (ii=0;ii<ll;ii++){
			      value = option.values[ii];
				
		        optionExtended.addRow(this.lastOptionId, option.type, this.imageEnabled && value.imageObject.url != undefined);
		        
            if (this.dependencyEnabled){
              newRowIds[value.rowId] = this.lastRowId;
              if (value.children.length > 0)
                toAddChildren.push([this.lastRowId, i, ii]);
		        }
		        
            if (this.imageEnabled && value.imageObject.url != undefined)
			        $('optionextended_link_'+this.lastSelectId+'_file_save').value = Object.toJSON(value.imageObject);
			        
			      $('product_option_value_'+this.lastSelectId+'_title').value                = value.title;				
			      $('product_option_value_'+this.lastSelectId+'_price').value                = value.price;				
			      $('product_option_value_'+this.lastSelectId+'_price_type').selectedIndex   = value.priceTypeIndex;	
			      $('product_option_value_'+this.lastSelectId+'_sku').value                  = value.sku;	
			      $('product_option_value_'+this.lastSelectId+'_sort_order').value           = value.sortOrder;	

          	if (this.descriptionEnabled)					
			        $('optionextended_'+this.lastSelectId+'_description').value = value.description;
          	if (this.sdEnabled && value.sdIsChecked){						
			        $('optionextended_value_'+this.lastSelectId+'_sd').checked = true;
			        this.addSdId(this.lastOptionId, this.lastRowId);
			      } 				      
			    }		
		
		    } else {
		
          $('product_option_'+this.lastOptionId+'_price').value = option.price;			
          $('product_option_'+this.lastOptionId+'_price_type').selectedIndex = option.priceTypeIndex;
          $('product_option_'+this.lastOptionId+'_sku').value = option.sku;
          
		      switch(option.type){
			      case 'field' :	
			      case 'area'  :
              $('product_option_'+this.lastOptionId+'_max_characters').value = option.maxCharacters;
			        break;	
			      case 'file'  :
              $('product_option_'+this.lastOptionId+'_file_extension').value = option.fileExtension;
              $('product_option_'+this.lastOptionId+'_image_size_x').value = option.imageSizeX;  
              $('product_option_'+this.lastOptionId+'_image_size_y').value = option.imageSizeY;       											
		      }	
          if (this.dependencyEnabled)		            
			      newRowIds[option.rowId] = this.lastRowId;
		    }  
		
      }
      
      l = toAddChildren.length;
      while (l--){
        rowId = toAddChildren[l][0];
        i = toAddChildren[l][1];
        ii = toAddChildren[l][2];        
        children = this.templateData[templateId][i]['values'][ii].children;
        ll = children.length;
        while (ll--)
          children[ll] = newRowIds[children[ll]];
        $('optionextended_'+this.selectIdByRowId[rowId]+'_children').value = children.join(',');
        this.setChildrenOfRow(rowId, children);
      }
    
		}
			  
	},
	
  loadTemplate : function(templateId){
    new Ajax.Request(this.templateDataUrl, {
        method:       "get",
        asynchronous: false, 
	      parameters: {isAjax: 'true', form_key: FORM_KEY, 'template_id': templateId, 'product_id': this.productId, 'store': this.storeId},               
        onSuccess: function(transport) {
          try {
             if (transport.responseText.isJSON()) {
	              var response = transport.responseText.evalJSON();
	              if (response.error) {
			            alert(response.message);
	              } else if(response.ajaxExpired && response.ajaxRedirect) {
			            setLocation(response.ajaxRedirect);
	              } else {            
  	              Object.extend(optionExtended.templateData, response); 	            
	              }
             }
          } catch (e) {}
       }
    });
  
  },

  setTemplateData : function(data) {
  	Object.extend(this.templateData, data);														
	}   
	
	 
});



