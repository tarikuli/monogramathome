

OptionExtended.Main.addMethods({
		
	isSelect : function(optionId){
	  var type;
	  if (this.accordionEnabled && !this.optionIsNew(optionId) && this.optionIsNotLoaded[optionId]){
	    type = this.optionTypes[optionId];	
	  } else {
	    type = $('product_option_'+optionId+'_type').value;
	  }	
	  switch(type){
		  case "radio":							
		  case "checkbox":							
		  case "drop_down":						
		  case "multiple":	
			  return true;				
	  }	  	
		 return false;		
	},
	
	optionIsNew : function (optionId)
	{
		return this.optionTypes[optionId] == undefined;
	},
	
	rowIsNew : function (rowId)
	{
		return this.valueTitles[rowId] == undefined;
	},
		
  arrayToInt : function (a){
    var t = [];
    var l = a.length;
    for(var i=0;i<l;i++)
      if (a[i] != '')
        t.push(parseInt(a[i]));
    return t;
  },	
     
	unique : function(a){
		var l=a.length,b=[],c=[];
		while (l--)
			if (c[a[l]] == undefined) b[b.length] = c[a[l]] = a[l];
		return b;
	}	
	 
});



