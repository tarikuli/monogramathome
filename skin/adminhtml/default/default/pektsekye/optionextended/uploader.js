

OptionExtended.Main.addMethods({
    
    loadImage : function(selectId, src){
        var img = $('optionextended_link_'+selectId+'_file_img');
		    img.src = src;
		    img.show();
		    if (this.uploaders[selectId] != undefined)
          $('optionextended_link_'+selectId+'_file-flash').hide();		    
		    $('optionextended_link_'+selectId+'_file_delete').show();		    	
		},
		 
		    	 
    loadUploader : function(selectId){
      var value = $('optionextended_link_'+selectId+'_file_save').value;
      if (value.isJSON())
        this.loadImage(selectId, value.evalJSON().url);      
      else 
        this.addUploader(selectId);		    					  				         
		  $(selectId+'_uploader_place-holder').hide();      
			$(selectId+'_uploader_row').show();
    },
    
    
    
    addUploader : function(selectId){
		    
       uploaderOITemplate = new Template(OptionExtended.Config.uploaderTemplate, /(^|.|\r|\n)(\[\[(\w+)\]\])/);

       Element.insert('optionextended_image_cell_'+selectId, {'top' : uploaderOITemplate.evaluate({'idName' : 'optionextended_link_'+selectId+'_file'})});


       var uploader = new Flex.Uploader('optionextended_link_'+selectId+'_file', OptionExtended.Config.uploaderUrl, this.uploaderConfig)
       uploader.selectId = selectId;
         
        uploader.handleSelect = function(event) {
         this.files = event.getData().files;
         this.checkFileSize();
         this.updateFiles();
         this.upload();
        };
        
        
        uploader.onFilesComplete = function (files) {
          var item = files[0];
          if (!item.response.isJSON()) {
          alert(optionExtended.expiredMessage); 
          return;
          }
          
          var response = item.response.evalJSON();
          if (response.error) {
             return;
          }

          this.removeFile(item.id);

          $('optionextended_link_'+this.selectId+'_file_save').value= Object.toJSON(response);       
          $('optionextended_link_'+this.selectId+'_file-new').hide();

          optionExtended.loadImage(this.selectId, response.url);
              
          $('optionextended_link_'+this.selectId+'_file-old').show();
        };
                           
        this.uploaders[selectId] = 1;		    
    
    },
    
        
    deleteImage : function(selectId){

      $('optionextended_link_'+selectId+'_file_save').value = '{}';
	    $('optionextended_link_'+selectId+'_file_img').hide();
	    $('optionextended_link_'+selectId+'_file_delete').hide();
	    if (this.uploaders[selectId] != undefined)
        $('optionextended_link_'+selectId+'_file-flash').show();
      else    	      
        this.addUploader(selectId);	
			    					  				   	
    }	    
	
}); 



