

var optionExtended = {


	
	  showSelect : function(type){
	    		
		  if (this.hasOptions){
		  
		    var select,i,l,ll,selected;
        var childrenField = $('children');

        if (this.rowIdIsSelected == undefined)
          this.checkChildren(childrenField);


		    if (type == 'detailed'){
          select = $('detailed_select');
          select.options[0].selected = false;
          l = select.options.length;
          for (i=1;i<l;i++){
            if (this.rowIdIsSelected[select.options[i].value] != undefined) 
              select.options[i].selected = true;
            else
              select.options[i].selected = false;
          }        
        } else {
          select = $('short_select');
          select.options[0].selected = false;          
          l = select.options.length;
          for (i=1;i<l;i++){
        		selected = true;
            ll = this.rowIdsByOption[select.options[i].value].length;	
            while (ll--){		
	            if (this.rowIdIsSelected[this.rowIdsByOption[select.options[i].value][ll]] == undefined){
                selected = false;
                break;
              }  
            }       						
            select.options[i].selected = selected;
          }         
        }
        
        childrenField.hide();
        select.show();
        select.focus();
			  $('show_link').hide();

		  }

	  },

	
	
	  showInput : function(type){
      var select;      	
		  var input = $('children');		  	
		  						
		  if (type == 'detailed'){		
			  select = $('detailed_select');
			  var ids = $F(select);
			  if (ids[0] == '')
			    ids.shift();
        this.resetSelected(ids);			  		  			  
	      input.value = ids.join(',');								  					
		  } else {		
        select = $('short_select');
        if (this.childrenShortSelectWasChanged != undefined){        	
		      var a = $F(select);
          var ids = [];		      	  
		      var l = a.length;
	        for (var i=0;i<l;i++)
		        if (a[i] != '')
				      ids = ids.concat(this.rowIdsByOption[a[i]]);
				  this.resetSelected(ids);           			      	           
	        input.value = ids.join(',');
          delete this.childrenShortSelectWasChanged;
        }	    				
		  }  	
   					
		  select.hide();      		
		  input.show();					       					
		  $('show_link').show();	
	  },


	
	  checkChildren : function(input){
	  
      var value = input.value;     
		  var ids = [];
		  
		  if (value != '') {						
		    var s = '['+value+']';
		    try {
			    var ch = s.evalJSON();
			    var t = [];
		      var l = ch.length;
		      for (var i=0;i<l;i++){
			      if (this.rowIdIsset[ch[i]] != undefined && t[ch[i]] == undefined){
              ids.push(ch[i]);
              t[ch[i]] = 1;           
            }
          }  
	        input.value = ids.join(',');                        		
        } catch (e){
			    input.value = '';      
        }            
		  }
		  
		  this.resetSelected(ids);
	  },

	
	  resetSelected : function(ids){
		  this.rowIdIsSelected = [];	  
      var l = ids.length;
      while (l--)
        this.rowIdIsSelected[ids[l]] = 1;
	  },

	  
	  onChildrenShortSelectChange : function(){
      this.childrenShortSelectWasChanged = 1; 	
	  },		



    
    loadImage : function(src){
        var img = $('optionextended_link_file_img');
		    img.src = src;
		    img.show();
		    if (this.uploaders != undefined)
          $('optionextended_link_file-flash').hide();		    
		    $('optionextended_link_file_delete').show();		    	
		},
		 
		    	 
    loadUploader : function(){
      var value = $('optionextended_link_file_save').value;
      if (value.isJSON())
        this.loadImage(value.evalJSON().url);      
      else 
        this.addUploader();		    					  				            
			$('uploader_row').show();
    },
    
    
    
    addUploader : function(){

       var uploader = new Flex.Uploader('optionextended_link_file', this.uploaderUrl, this.uploaderConfig)
         
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

          $('optionextended_link_file_save').value= Object.toJSON(response);       
          $('optionextended_link_file-new').hide();

          optionExtended.loadImage(response.url);
              
          $('optionextended_link_file-old').show();
        };
                           
        this.uploaders = 1;		    
    
    },
    
        
    deleteImage : function(){

      $('optionextended_link_file_save').value = '{}';
	    $('optionextended_link_file_img').hide();
	    $('optionextended_link_file_delete').hide();
	    if (this.uploaders != undefined)
        $('optionextended_link_file-flash').show();
      else    	      
        this.addUploader();	
			    					  				   	
    }	    
	
}; 



