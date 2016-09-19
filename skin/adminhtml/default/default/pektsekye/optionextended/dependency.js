
OptionExtended.Main.addMethods({
	
	
	showSelect : function(optionId, selectId, type){
	  		
		if (this.optionIds.length > 1){

	    var option,select,oId,otI,vtI,oTitle,i,ii,ll,selected;	
	    var rowId = this.rowIdBySelectId[selectId];
		  var children = this.childrenByRowId[rowId] != undefined ? this.childrenByRowId[rowId] : [];
		    
		  if (type == 'detailed'){
		    select = $('optionextended_'+selectId+'_children_detailed_select');
	      var options = '';		    		    		  		  
		  } else {
		    select = $('optionextended_'+selectId+'_children_short_select');
		    select.options.length = 1;
			  select.options[0].selected = false;		    
		    var ind = 1;		    
		  }  
		  
		  this.resetParent();		  
		  var parent = this.getParent(optionId) || [] ;
		  	
	    var canShow = true;		  
		  
      var n = 1;		  
		  var l = this.optionIds.length;		  
		  for (i=0;i<l;i++){
		  
		    oId = this.optionIds[i];
			  if (oId != optionId && parent.indexOf(oId) == -1 && !this.hasParent(oId, optionId)){
			
	        if (this.accordionEnabled && !this.optionIsNew(oId) && this.optionIsNotLoaded[oId]){
	          oTitle = this.optionTitles[oId];		
	        } else {
			      otI = $('product_option_'+oId+'_title');
			      if (!Validation.validate(otI)){
				      otI.focus(); 
				      canShow = false;
				      break;
			      }	
            oTitle = otI.value;					
	        }				
					
					
					
		      if (type == 'detailed'){

			        oTitle = oTitle.escapeHTML().replace(/"/g, '&quot;');

				      if (!this.isSelect(oId)){
			          rId = this.rowIdByOption[oId];				      
					      options +=	'<option '+(children.indexOf(rId) != -1 ? 'selected' : '')+' value="'+rId+'">'+oTitle+' '+rId+'</option>';	
					      n++;
				      } else {	
				      
					      options +=	'<optgroup label="'+oTitle+'">';	
					      ll = this.rowIdsByOption[oId].length;	
					      for (ii=0;ii<ll;ii++){
					      
			            rId = this.rowIdsByOption[oId][ii];					
        			    		
	                if (this.accordionEnabled && !this.optionIsNew(oId) && (this.optionIsNotLoaded[oId] || (this.inactiverowsEnabled && !this.rowIsNew(rId) && this.rowIsActive[rId] == undefined))){ 
	                  vTitle = this.valueTitles[rId];		                   	          
	                } else {
						        vtI = $('product_option_value_'+ this.selectIdByRowId[rId] +'_title');				
						        if (!Validation.validate(vtI)){
							        vtI.focus();
				              canShow = false;
				              break;
						        }
                    vTitle = vtI.value;					
	                }	
	                					
						      options +=	'<option '+ (children.indexOf(rId) != -1 ? 'selected' : '') +' value="'+rId+'">'+vTitle+' '+rId+'</option>';
						      n++;
						      
					      }
					      
					      options +=	'</optgroup>';
					      n++;
					      
				      }		
				      				 
						       
						        					
		      } else {	
			
		        if (!this.isSelect(oId)){
			        select.options[ind] = new Option(oTitle + ' ' + this.rowIdByOption[oId], oId);				
			        if (children.indexOf(this.rowIdByOption[oId]) != -1)
				        select.options[ind].selected = true;						
		        } else {	
			        select.options[ind] = new Option(oTitle, oId);
			        selected = true; 				
			        ll = this.rowIdsByOption[oId].length;	
			        while (ll--){		
				        if (children.indexOf(this.rowIdsByOption[oId][ll]) == -1){
                  selected = false;
                  break;
                }  
              }       						
			        select.options[ind].selected = selected;		
		        }
		        
		        n++;
		        ind++;	 
		                      					
		      }						  		
			  }
		  }	
		  
			if (canShow && n > 1){
        if (type == 'detailed'){		        				
          Element.replace(select, this.childrenDetailedSelectTemplate.evaluate({'id':optionId, 'select_id':selectId, 'size':(n < 20 ? n : 20), 'options':options}));
          select = $('optionextended_'+selectId+'_children_detailed_select');	        	
        } else {	
          select.size = n < 20 ? n : 20;		
        }

				$('optionextended_'+selectId+'_children').hide();
				select.show();	
				select.focus();						
				$('optionextended_'+selectId+'_show_link').hide();		
			}
		}
	},

	
	
	showInput : function(optionId, selectId, type){
    var select;
    
    var ids = [];	
		var input = $('optionextended_'+selectId+'_children');	
    var rowId = this.rowIdBySelectId[selectId];  
						
		if (type == 'detailed'){		
			select = $('optionextended_'+selectId+'_children_detailed_select');
			ids = this.arrayToInt($F(select));
	    input.value = ids.join(',');
      this.setChildrenOfRow(rowId, ids); 								  					
		} else {		
      select = $('optionextended_'+selectId+'_children_short_select');
      if (this.childrenShortSelectWasChanged[rowId] != undefined){	
		    var a = $F(select);	  
		    var l = a.length;
	      for (var i=0;i<l;i++){
		      if (a[i] != ''){
			      if (this.isSelect(a[i]))
				      ids = ids.concat(this.rowIdsByOption[a[i]]);
			      else
				      ids.push(this.rowIdByOption[a[i]]);
		      }	
	      }      
	      input.value = ids.join(',');
        this.setChildrenOfRow(rowId, ids);
        delete this.childrenShortSelectWasChanged[rowId];
      }	    				
		}  	
					
		select.hide();      		
		input.show();	
				     					
		$('optionextended_'+selectId+'_show_link').show();	
	},

	
	updateChildren : function(input, optionId, selectId){
	
    var rowId = this.rowIdBySelectId[selectId];
    var value = input.value;
    	    
		if (value == ''){
		
      this.setChildrenOfRow(rowId, []);
      
		} else {	
					
		  var s = '['+value+']';
		  try {
			  var ch = s.evalJSON();
			  		  
		    var t = [];
		    var tt = [];
		    var l = ch.length;
		    for (var i=0;i<l;i++){
			    if (this.rowIdIsset[ch[i]] != undefined && this.rowIdsByOptionIsset[optionId][ch[i]] == undefined && tt[ch[i]] == undefined){
            t.push(ch[i]);
            tt[ch[i]] = 1;           
          }
        }  
	      input.value = t.join(','); 
        this.setChildrenOfRow(rowId, t);	                       		
      } catch (e){
			  input.value = '';
			  this.setChildrenOfRow(rowId, []);	      
      }
          
		}
	},
	
	
	onChildrenShortSelectChange : function(selectId){
    this.childrenShortSelectWasChanged[this.rowIdBySelectId[selectId]] = 1; 	
	}		
    
});

