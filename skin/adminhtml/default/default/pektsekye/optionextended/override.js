
OptionExtended.Main.addMethods({

    overrideMagentoJs : function(){
      productForm._orSubmit = productForm.submit;
      productForm.submit = function(url) {
        if (url != undefined){
          return productForm._orSubmit(optionExtended.onSubmit(url));	
        } else {
          optionExtended.onSubmit();
          return productForm._orSubmit();	  	
        }													
      } 
            
      productForm._validate = function(){
        new Ajax.Request(this.validationUrl,{
            asynchronous: false,         
            method: 'post',
            parameters: $(this.formId).serialize(),
            onComplete: this._processValidationResult.bind(this),
            onFailure: this._processFailure.bind(this)
        });
      }          
    }
  
});
