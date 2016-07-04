

OptionExtended.Main.addMethods({
			
  previousParent : [],
  			
	resetParent : function() {
		this.previousParent = [];
		var l=this.optionIds.length,o=0,r=[],ll=0,c=[],lll=0,obc=[];
		while (l--){
			o = this.optionIds[l];
			if (this.isSelect(o)){
				r = this.rowIdsByOption[o];
				ll = r.length;
				while (ll--){  
					c = this.childrenByRowId[r[ll]];
					if (c){
						lll = c.length;
						while (lll--)
							obc[c[lll]] = o;	
					}	
				}
			}
		}		
		this.optionByChild = obc;
	},	
	
	
	getParent : function(optionId){

		if (this.previousParent[optionId])
			return false;
			
		this.previousParent[optionId] = 1;
		
		var o, p;		
		var parent = [];		
		var r = this.rowIdsByOption[optionId];
		var l =	r.length;
		while (l--){
			o = this.optionByChild[r[l]];
			if (o){
				parent.push(o)
				if (p = this.getParent(o)){
					parent = parent.concat(p);
					break;					
				} else { 
					return false;
				}	
			}
		}	

		return parent;
	},	
	
	
	hasParent : function(optionId, exclude){
	  var r,l;
		if (this.isSelect(optionId)){	
  		r = this.rowIdsByOption[optionId];
			l =	r.length;
			while (l--)
				if (this.optionByChild[r[l]] && this.optionByChild[r[l]] != exclude)
					return true;
		}	else {
  		r = this.rowIdByOption[optionId];	
			if (this.optionByChild[r] && this.optionByChild[r] != exclude)
				return true;  			
		}
		return false;
	}
	
	 
});



