var optionExtendedInstanceId = 0;
var optionExtendedInstances = [];

OptionExtended.Main = Class.create(OptionExtended.Dependent, {


	
	initialize : function($super){
				
		$super();	
		
    this.dependecyIsSet = false;	    
    this.load();
    
    this.reloadElements();    
    
    this.setDependency();
    this.dependecyIsSet = true;		
    this.preloadSwapImages(this.oIds, this.valsByOption);

	},
	
	
	reloadElements : function($super){
	
    var previousOptionId = -1;
    var isNewOption = true;
	
		$('product_composite_configure_form_fields').select('.product-custom-option').each(function(element){		
			 var optionId = 0;
			 element.name.sub(/[0-9]+/, function(match){
				  optionId = parseInt(match[0]);
			 });
			 
      if (optionId != previousOptionId)
        isNewOption = true;		        
		
	  	$super(element, optionId, isNewOption);	
	  	
			if (element.type == 'radio') {			
					element.observe('click', this.observeRadio.bind(this, optionId, element.value));								
			} else if(element.type == 'checkbox') {
					element.observe('click', this.observeCheckbox.bind(this, element, optionId, element.value));					
			} else if(element.type == 'select-one' && !Element.hasClassName(element,'datetime-picker')) {
					element.observe('change', this.observeSelectOne.bind(this, element, optionId));					
			} else if(element.type == 'select-multiple') {	
					element.observe('change', this.observeSelectMultiple.bind(this, element, optionId));		
			}	
		
	    previousOptionId = optionId;
	    isNewOption = false;
	  
		}.bind(this));		
	},
	
	
	load : function($super){
		$('product_composite_configure_form_fields').select('.product-custom-option').each(function(element){
			 var optionId = 0;
			 element.name.sub(/[0-9]+/, function(match){
				  optionId = parseInt(match[0]);
			 });
			
			$super(element, optionId);
			
		}.bind(this));		
	},
	
	onDataReady : function(){
    this.selectDefault();
    this.preloadPopupImages(this.oIds, this.valsByOption);    
	},
			
			
	selectDefault : function(){
  	var i,element,group,checkedIds,ids,ll;
		var l = this.oIds.length;	
		for (i=0;i<l;i++){
		
  		if (this.oldO[this.oIds[i]].visible){
  		
			  if (this.oldO[this.oIds[i]].element){
			    element = this.oldO[this.oIds[i]].element;
			    group = 'select';				  
			  } else {
			    group = '';			  
			  }
			  
		    checkedIds = this.config[0][this.oIds[i]][3];
		    ids = this.valsByOption[this.oIds[i]];
			  ll = ids.length;		
			  while (ll--){
				  if (this.oldV[ids[ll]].visible && checkedIds.indexOf(ids[ll]) != -1){
            if (group == 'select'){	
              if (element.type == 'select-one')
                element.selectedIndex = this.indByValue[ids[ll]];   
              else
                element.options[this.indByValue[ids[ll]]].selected = true;                       
            } else {
              element = this.oldV[ids[ll]].element;
              element.checked = true;
              if(element.type == 'radio')
               this.observeRadio(this.oIds[i], element.value);
              else 
               this.observeCheckbox(element, this.oIds[i], element.value);                                
            }
				  }		
			  }	
			  
        if (group == 'select'){
          if(element.type == 'select-one')
				    this.observeSelectOne(element, this.oIds[i]);		
				  else	  		
					  this.observeSelectMultiple(element, this.oIds[i]);        
        }
        
			}
		}		
	}
	

});
















