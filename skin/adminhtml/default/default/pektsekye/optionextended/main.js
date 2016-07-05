
OptionExtended = {}

OptionExtended.Main = Class.create({

	uploaders : [],
		
	optionTypes  : [],
  optionTitles : [],
  valueTitles  : [], 
  
  lastOptionId : 0,	
	lastSelectId :-1,
	lastRowId    : 0,

  optionIsNotVisible : [],
  optionIsNotLoaded  : [],

  rowIsActive : [],
  optionWasSetToDelete : [],
  
  childrenShortSelectWasChanged : [],  
  	
  accordionOptionUrl  : '',
  hiddenOptionIds     : [],     
  isContinueEdit      : false,
  wasExpanded         : 0,        
    	  		
	templatePattern : /(^|.|\r|\n)({{(\w+)}})/,
	
	
	initialize : function(config){
		Object.extend(this, OptionExtended.Config);
		this.firstStepTemplate = new Template(this.firstStep, this.templatePattern);		
		this.lastOptionColumnsTemplate = new Template(this.lastOptionColumns, this.templatePattern);		
		this.lastOptionFieldsTemplate = new Template(this.lastOptionFields, this.templatePattern);
		this.childrenDetailedSelectTemplate = new Template(this.childrenDetailedSelect, this.templatePattern);		
    this.valuesRowTemplate = new Template(this.valuesRow, this.templatePattern);	
    this.valuesActivatedRowTemplate = new Template(this.valuesActivatedRow, this.templatePattern);
    this.checkboxScopeTemplate = new Template(this.checkboxScope, this.templatePattern);    
	  this.appliedTemplateRowTemplate = new Template(this.appliedTemplateRow, this.templatePattern);
	   
		this.addExpand();
  	this.overrideMagentoJs();		
	},	



  setData : function(data) {
  	Object.extend(this, data);														
	},	
  
  
  
  setRowsData : function(data) {
  	Object.extend(this.rowsData, data);
  
  	var rId;
  	var notActivatedIds = [];
  	var l = this.rowsToActivate.length;
  	while (l--){
  	  rId = this.rowsToActivate[l];
  	  if (this.rowsData[rId]){  	  
  	    this.activateRow(null, this.optionByRowId[rId], null, rId);
  	  } else {
  	    notActivatedIds.push(rId);
  	  }  
  	}
  	this.rowsToActivate = notActivatedIds;  												
	}, 
  	
  	
  	
  	
  add : function() {

    data = {};
    data.option_id = 0;
		data.id = this.lastOptionId + 1;	
         	      
    Element.insert($('product_options_container_top'), {'after': this.firstStepTemplate.evaluate(data)});
    
    this.lastOptionId++;  
  },




  loadStepTwo : function(optionId, isDuplicate){
    var ind,rowId;
    
		var selectType = $('product_option_'+optionId+'_type');
		if (selectType.value != ''){
							
		  var type = selectType.value;
		  var group = '';		  
      switch(type){
          case 'field':
          case 'area':
              group = 'text';
              break;
          case 'file':
              group = 'file';
              break;
          case 'drop_down':
          case 'radio':
          case 'checkbox':
          case 'multiple':
              group = 'select';
              break;
          case 'date':
          case 'date_time':
          case 'time':
              group = 'date';
              break;
          default:
              group = 'unknown';
              break;
      }	
								
      var valuesTable = this.valuesTableBof;
      			      
      var data = {};	
      data.id = optionId;        

		  var ind = 0;		
		  if (group == 'select'){
			
        Element.insert($('optionextended_option_table_'+optionId+'_first_tr'), {'bottom' : this.lastOptionColumnsTemplate.evaluate({'id' : optionId})});
	
        var options =	'<option value="above">'+this.aboveOption+'</option>';
        switch (type){
          case "radio":
            options +=  '<option value="before">'+this.beforeOption+'</option>'+
							          '<option value="below">'+this.belowOption+'</option>' + 
							          '<option value="swap">'+this.mainImage+'</option>'+
							          '<option value="grid">'+this.grid+'</option>' +
							          '<option value="gridcompact">'+this.gridcompact+'</option>' +							          
							          '<option value="list">'+this.list+'</option>';													
            break;														
          case "checkbox":
            options += '<option value="below">'+this.belowOption+'</option>' + 
							          '<option value="grid">'+this.grid+'</option>' +
							          '<option value="gridcompact">'+this.gridcompact+'</option>' +							          
							          '<option value="list">'+this.list+'</option>';
            break;				
          case "drop_down":
            options += '<option value="before">'+this.beforeOption+'</option>'+
							          '<option value="below">'+this.belowOption+'</option>' +  
							          '<option value="swap">'+this.mainImage+'</option>'+
							          '<option value="picker">'+this.colorPicker+'</option>'+
							          '<option value="pickerswap">'+this.colorPickerSwap+'</option>';							        
	          break;	
          case "multiple":
            options += '<option value="below">'+this.belowOption+'</option>';					
        }		
        						
        Element.insert($('optionextended_option_table_'+optionId+'_second_tr'), {'bottom' : this.lastOptionFieldsTemplate.evaluate({'id' : optionId, 'options' : options})});			

			  switch (type){
				  case 'drop_down': ind = 0;break;	
				  case 'radio' 		: ind = 1;break;	
				  case 'checkbox' : ind = 2;break;	
				  case 'multiple' : ind = 3;break;								
			  }	

  		  var selectTemplate = new Template(this.selectTypeBof + this.selectTypeOptionsByGroup.select + this.selectTypeEof, this.templatePattern);								 
			  Element.replace(selectType, selectTemplate.evaluate({'id' : optionId}));
			  selectType = $('product_option_'+optionId+'_type');
			  selectType.selectedIndex = ind;			
			  Event.observe(selectType, 'change', this.reloadLayoutSelect.bind(this, optionId, selectType));	
			  					        		          
        var tableWidth = this.valueTableWidth;    
                 
		    if (!this.dependencyEnabled)
          tableWidth =  tableWidth - 242; 		
		    if (!this.imageEnabled)
          tableWidth =  tableWidth - 155; 			    
	    	if (!this.descriptionEnabled)
          tableWidth =  tableWidth - 241;
	  	        	      	      	        
		    if (this.sdEnabled){
	        data.sdColumnInput = this.getSdColumnInput(type, optionId);			    	
		    } else {
          tableWidth =  tableWidth - 25;  		
		    }
		    
	      data.tableWidth = 'style="width:'+tableWidth+'px"';
	     
        valuesTable += this.valuesTableByGroup.select;            
	                    													
		  }	else {	
  
			  switch(type){
				  case 'field'     : ind = 0;break;	
				  case 'area'      : ind = 1;break;	
				  case 'file'      : ind = 0;break;	
				  case 'date'      : ind = 0;break;					
				  case 'date_time' : ind = 1;break;	
				  case 'time'      : ind = 2;break;												
			  }		
			  
  		  var selectTemplate = new Template(this.selectTypeBof + this.selectTypeOptionsByGroup[group] + this.selectTypeEof, this.templatePattern);				  			  			
			  Element.replace(selectType, selectTemplate.evaluate({'id' : optionId}));
			  $('product_option_'+optionId+'_type').selectedIndex = ind;
			  			
        rowId = this.lastRowId + 1;
        data.row_id = rowId;
        
        valuesTable += this.valuesTableByGroup[group];	

			  this.lastRowId++;	
		  }	

      valuesTable += this.valuesTableEof;
		  var valuesTableTemplate = new Template(valuesTable, this.templatePattern);        
          
      Element.insert($('optionextended_option_container_'+optionId), {'after' : valuesTableTemplate.evaluate(data)});	

 
      this.setOptionIds(optionId, rowId, group);
			this.optionIds.unshift(optionId);
			
      if (group == 'select' && isDuplicate == undefined)
		    optionExtended.addRow(optionId, type);						  	      	  
    }
  },	
 
  
  

	 
	 
	addRow : function(optionId, type, hasImage){
	
	  if (type == undefined)
      type = $('product_option_'+optionId+'_type').value;

		var selectId = this.lastSelectId + 1;
		var rowId = this.lastRowId + 1;	

    var data = {};
    data.id = optionId;
    data.row_id = rowId; 
    data.select_id = selectId;
	  data.option_type_id = -1;	
	  
    if (this.imageEnabled){	  
	    if (hasImage != undefined && hasImage)
        data.rollOver = this.rollOverPreview;	 
	    else 
        data.rollOver = this.rollOverUploader;
	  }
	        	      	        
    if (this.sdEnabled)
      data.sdCellInput = this.getSdCellInput(optionId, selectId, rowId, type);		    	
	    		
    Element.insert($('select_option_type_row_'+optionId), {'bottom' : this.valuesRowTemplate.evaluate(data)});

	  if (this.optionWasSetToDelete[optionId] != undefined){
      this.unsetOptionToDelete(optionId);
      delete this.optionWasSetToDelete[optionId];	
    } 
    
    this.setOptionValueIds(optionId, selectId, rowId);		   
	  this.lastSelectId++;
	  this.lastRowId++;	    
	},	
		  
		  
		  
	activateRow : function(row, optionId, selectId, rowId){
	  var checked;

  	if (rowId == undefined)
  	  rowId = this.rowIdBySelectId[selectId];
  	else
  	  row = $('product_option_value_'+ this.selectIdByRowId[rowId]);
  	  
	  var data = this.rowsData[rowId];
	  data.children = this.childrenByRowId[rowId].join(',');
	  
    if (this.storeId != 0){
      if (data.store_title_is_null){
	      data.title_disabled = 'disabled="disabled"';
	      checked = 'checked="checked"';
	    } else { 
	      checked = '';  	
	    } 
	    data.title_scope_checkbox = this.checkboxScopeTemplate.evaluate({'input_id':'product_option_value_'+selectId+'_title', 'input_name':'product[options]['+optionId+'][values]['+selectId+'][scope][title]', 'checked':checked, 'type':'title'});

      if (data.store_price_is_null != undefined){  	
        if (data.store_price_is_null){
      	  data.price_disabled = 'disabled="disabled"';
      	  checked = 'checked="checked"';
      	} else { 
      	  checked = '';  	
      	} 
      	data.price_scope_checkbox = this.checkboxScopeTemplate.evaluate({'input_id':'product_option_value_'+selectId+'_price', 'input_name':'product[options]['+optionId+'][values]['+selectId+'][scope][price]', 'checked':checked, 'type':'price'});
      } 
	
      if (data.store_description_is_null){
	      data.description_disabled = 'disabled="disabled"';
	      data.description_show_link_hidden = 'style="display:none"';	      
	      checked = 'checked="checked"';
	    } else { 
	      checked = '';  	
	    } 
	    data.description_scope_checkbox = this.checkboxScopeTemplate.evaluate({'input_id':'optionextended_'+selectId+'_description', 'input_name':'product[options]['+optionId+'][values]['+selectId+'][scope][optionextended_description]', 'checked':checked, 'type':'description'});
	      	
    } 	  
	  
    Element.replace(row, this.valuesActivatedRowTemplate.evaluate(data));    
    this.rowIsActive[rowId] = 1;          
	},		  
		  
		  
		  
	deleteOption : function(optionId){	
    this.setOptionToDelete(optionId);
	  var optionContainer = $('option_'+optionId);		
	  optionContainer.addClassName('no-display');
	  optionContainer.addClassName('ignore-validate');
	  optionContainer.hide();
    if (this.accordionEnabled && !this.optionIsNew(optionId))	  
      $('dt_option_'+optionId).hide();
      
   this.unsetOptionIds(optionId); 		
	},
	
	
	setOptionToDelete : function(optionId){	
	  $('product_option_'+optionId+'_'+'is_delete').value = '1';	
	},
		
  unsetOptionToDelete : function(optionId){	
	  $('product_option_'+optionId+'_'+'is_delete').value = '0';	
	},


	deleteRow : function(optionId, selectId){
	
    $('product_option_value_'+selectId+'_is_delete').value = '1';
    var row = $('product_option_value_'+selectId);
    row.addClassName('no-display');
    row.addClassName('ignore-validate');
    row.hide();	

    var rowId = this.rowIdBySelectId[selectId];
	  this.unsetOptionValueIds(optionId, rowId);
	  this.deleteSdId(optionId, rowId);
	  
	  if (this.rowIdsByOption[optionId].length == 0){
      this.setOptionToDelete(optionId);
      this.optionWasSetToDelete[optionId] = 1;	
    }  
	},

	
	onSubmit : function(url){
	  if (url && this.optionsScripLoaded != undefined){
	   url = url.replace(/\/is_expanded\/\d/,'');
	   url = url.replace(/\/hidden_ids\/[\d_]+/,'');	   
	   url += this.getOpenedOptionIds();	
	   url += this.getWasExpanded();		      
	  }	
	  return url;
	}
	 
});


