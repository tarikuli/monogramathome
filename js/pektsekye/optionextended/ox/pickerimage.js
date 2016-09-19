
var PickerImage = {};
var pickerImage = {};
PickerImage.Main = Class.create({

	uploaders : {},         	  		
	
	initialize : function(){
		Object.extend(this, PickerImage.Config);
	},


  addRow : function(){ 

    var rowTemplate = new Template(this.row, /(^|.|\r|\n)(\[\[(\w+)\]\])/);

    var nextImageId = this.lastImageId + 1;

    Element.insert($('ox_add_image_row'),{'before': rowTemplate.evaluate({'image_id' : nextImageId})});
    
    this.lastImageId++;		    	
  },
  
  
  loadImage : function(selectId, src){ 
    var img = $('ox_preview_'+selectId); 
    img.src = src;
    img.show(); 
    if (this.uploaders[selectId] != undefined)
      $('ox_link_'+selectId+'_file-flash').hide();
    $('ox_delete_button_'+selectId).show();		    	
  },


  loadUploader : function(selectId){ 
    this.addUploader(selectId);
    $(selectId+'_uploader_place_holder').hide();
    $(selectId+'_uploader_row').show();
  },


  addUploader : function(selectId){


     var uploader = new Flex.Uploader('ox_link_'+selectId+'_file', PickerImage.Config.uploaderUrl, this.uploaderConfig);
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
          alert(PickerImage.Config.expiredMessage); 
          return;
        }

        var response = item.response.evalJSON();
        if (response.error) {
          return;
        }

        this.removeFile(item.id);

        $('ox_value_'+this.selectId+'_image').value = response.file;
        $('ox_link_'+this.selectId+'_file-new').hide();

        pickerImage.loadImage(this.selectId, response.url);

        $('ox_link_'+this.selectId+'_file-old').show();
      };

      this.uploaders[selectId] = 1;

  },


  deleteImage : function(selectId){
    $('ox_value_'+selectId+'_delete_image').value = 1;
    $('ox_value_'+selectId+'_image').value = '';
    $('ox_preview_'+selectId).hide();
    $('ox_delete_button_'+selectId).hide();
    if (this.uploaders[selectId] != undefined)
      $('ox_link_'+selectId+'_file-flash').show(); 
    else
      this.loadUploader(selectId); 
  },
  
  
	changePopup : function(){
		var popupCheckbox = $('popup');
		var layout = $('layout').value;
		if (layout == 'swap'){
			popupCheckbox.checked = false;
			popupCheckbox.disabled = true;
		} else {
			popupCheckbox.disabled = false;			
		}	
	}
		
}); 



