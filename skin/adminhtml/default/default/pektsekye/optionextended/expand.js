

OptionExtended.Main.addMethods({

	addExpand : function(){
	  if (!$('optionextended_expand_image'))
			Element.insert($$('div.content-header')[0], {'top' : this.expandContainer});	
	},	
	
  restoreExpand : function(){	
    if (this.wasExpanded == 1) 
     this.expand();
  },
	
	expand : function(){
	  if (!this.expanded){
			var sideCol = $$('div.side-col')[0];
			sideCol.addClassName('optionextended-expand-side-col');
			sideCol.up('div.columns').addClassName('optionextended-expand-columns');
			sideCol.up('div.middle').addClassName('optionextended-expand-middle');
			sideCol.next('div.main-col').addClassName('optionextended-expand-main-col');
			var expand = $$('div.optionextended-expand-container');
			expand[0].down('img').src = this.expandSrc;
			expand[0].down('img', 1).src = this.collapseOnSrc;
			expand[1].down('img').src = this.expandSrc;
			expand[1].down('img', 1).src = this.collapseOnSrc;			
			this.expanded = true;			
		}
	},
	
	
	collapse : function(){
	  if (this.expanded){
			var mainCol = $$('div.main-col')[0];
			mainCol.removeClassName('optionextended-expand-main-col');
			mainCol.up('div.middle').removeClassName('optionextended-expand-middle');
			mainCol.up('div.columns').removeClassName('optionextended-expand-columns');	
			mainCol.previous('div.side-col').removeClassName('optionextended-expand-side-col');
			var expand = $$('div.optionextended-expand-container');			
			expand[0].down('img').src = this.expandOnSrc;
			expand[0].down('img', 1).src = this.collapseSrc;		
			expand[1].down('img').src = this.expandOnSrc;
			expand[1].down('img', 1).src = this.collapseSrc;			
			this.expanded = false;			
		}
	},
	
	getWasExpanded : function(){
     return 'is_expanded/'+ (this.expanded ? 1 : 0)  +'/'; 	
	}
	 
});



