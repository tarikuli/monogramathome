
OptionExtended.Main.addMethods({

    preopenAccordionItems : function(){
		  var l = this.optionIds.length;
		  for (var i=0;i<l;i++)
		    if (this.hiddenOptionIds.indexOf(this.optionIds[i]) == -1)
          this.showAccordionItem(this.optionIds[i]);
        		             
    },
    
    accordionOnClick : function(optionId){
      if (this.optionIsNotVisible[optionId] == true)
        this.showAccordionItem(optionId);
      else
        this.hideAccordionItem(optionId);         
    },

    showAccordionItem : function(optionId){
      if (this.optionIsNotLoaded[optionId])
        this.loadAccordionItem(optionId);      
      Element.addClassName($('dt_option_'+optionId), 'open');
      Element.addClassName($('dd_option_'+optionId), 'open');      
      this.optionIsNotVisible[optionId] = false;    
    },
    
    hideAccordionItem : function(optionId){
      Element.removeClassName($('dt_option_'+optionId), 'open');
      Element.removeClassName($('dd_option_'+optionId), 'open');      
      this.optionIsNotVisible[optionId] = true;                               
    }, 
       
    loadAccordionItem : function(optionId){
      new Ajax.Request(this.accordionOptionUrl, {
          method:       "get",
          asynchronous: false, 
		      parameters: {isAjax: 'true', form_key: FORM_KEY, 'product_id': this.productId, 'option_id': optionId, 'store': this.storeId},               
          onSuccess: function(transport) {
            try {
	             if (transport.responseText.isJSON()) {
		              var response = transport.responseText.evalJSON()
		              if (response.error) {
				            alert(response.message);
		              }
		              if(response.ajaxExpired && response.ajaxRedirect) {
				            setLocation(response.ajaxRedirect);
		              }
	             } else {
		              $('dd_option_'+optionId).update(transport.responseText);
	             }
            }
            catch (e) {
	             $('dd_option_'+optionId).update(transport.responseText);
            }
         }
      });
    

    },
    
	  getOpenedOptionIds : function(){
  	  var t = [];
      var l = this.optionIds.length;	
      while(l--)
        if (this.optionIsNotVisible[this.optionIds[l]])
          t.push(this.optionIds[l]);		  	  
      return t.length > 0 ? 'hidden_ids/'+ t.join('_') +'/' : '';    	 	  
	  }   
  
});
