
OptionExtended.Main.addMethods({


  optionIds : [],
  optionByChild : [],

  selectIdByRowId    : [], 

  parentRowIdsOfRowId : [],
  
  rowIds : [],
  rowsData : [],
  rowIdIsset : [],
  rowIdByOption : [],
  rowIdsByOption : [], 
  rowIdsByOptionIsset : [],
  rowIdBySelectId : [],
     	  			
  childrenByRowId : [],
  optionByRowId : [],
	
	rowsToActivate : [],
	
	setOptionIds : function(optionId, rowId, group){
  	if (group == 'select'){
      this.rowIdsByOption[optionId] = [];
      this.rowIdsByOptionIsset[optionId] = [];	        
    } else { 
      this.rowIdIsset[rowId] = 1;             
	    this.rowIdByOption[optionId] = rowId;		            
    }
	},
	

	setOptionValueIds : function(optionId, selectId, rowId){
    this.rowIdIsset[rowId] = 1;							
		this.selectIdByRowId[rowId] = selectId;		
		this.rowIdsByOption[optionId].push(rowId);
		this.rowIdsByOptionIsset[optionId][rowId] = 1;		
		this.rowIdBySelectId[selectId] = rowId;	
	},	
	
	
	unsetOptionIds : function(optionId){
  	var rowId;
		this.optionIds = this.optionIds.without(optionId);
		if (this.isSelect(optionId)){
		  var l = this.rowIdsByOption[optionId].length;
			while (l--){
			  rowId = this.rowIdsByOption[optionId][l];			
        delete this.rowIdIsset[rowId];			
        this.unsetChildren(rowId);			
			}					
			this.rowIdsByOption[optionId] = null;			
		} else {
  		rowId = this.rowIdByOption[optionId];
      delete this.rowIdIsset[rowId];			
      this.unsetChildren(rowId);			
			this.rowIdByOption[optionId] = null;
		}
	},
	
	
	unsetOptionValueIds : function(optionId, rowId){    
		this.rowIds = this.rowIds.without(rowId);
    delete this.rowIdIsset[rowId];		
		this.rowIdsByOption[optionId] = this.rowIdsByOption[optionId].without(rowId);
		delete this.rowIdsByOptionIsset[optionId][rowId];		
		
    this.unsetChildren(rowId);			
	},
	
	
	unsetChildren : function(rowId){
	  var rId,oId;
	  var valueWasSet = false;	
	  if (this.parentRowIdsOfRowId[rowId] != undefined){
	    var loadOption = null;
      var l = this.parentRowIdsOfRowId[rowId].length;		    
      while (l--){
        rId = this.parentRowIdsOfRowId[rowId][l];
        this.childrenByRowId[rId] = this.childrenByRowId[rId].without(rowId);		
        
        if (this.accordionEnabled){
          oId = this.optionByRowId[rId];
          if (!this.optionIsNew(oId)){
            if (this.inactiverowsEnabled && this.rowIsActive[rId] == undefined){
              if (this.optionIsNotLoaded[oId]){    
                this.rowsToActivate.push(rId);
                loadOption = oId;                
              } else {
                this.activateRow(null, oId, null, rId);
              }
              valueWasSet = true;              
            }                          
          }
        }
       
        if (loadOption)
          this.loadAccordionItem(loadOption);
        
        if (!valueWasSet)
          $('optionextended_'+this.selectIdByRowId[rId]+'_children').value = this.childrenByRowId[rId].join(',');        
      }
    }	
	},
	
	
	setChildrenOfRow : function(rowId, ids){
	  var l;
    var previousIds = this.childrenByRowId[rowId] != undefined ? this.childrenByRowId[rowId].slice(0) : []; 
       	
		this.childrenByRowId[rowId] = ids;
		
	  l = previousIds.length;	     
    while (l--)
      if (ids.indexOf(previousIds[l]) == -1)
        this.parentRowIdsOfRowId[previousIds[l]] = this.parentRowIdsOfRowId[previousIds[l]].without(rowId);
        		
	  l = ids.length;
    while (l--){
      if (this.parentRowIdsOfRowId[ids[l]] == undefined)
        this.parentRowIdsOfRowId[ids[l]] = [];
      this.parentRowIdsOfRowId[ids[l]].push(rowId);
    }  
	    		
  }	
    
});

