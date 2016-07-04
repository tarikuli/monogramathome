

OptionExtended.Dependent = Class.create(OptionExtended.Images, {
  
  oIds : [],
  oldV : [],  
  oldO : [],
  childrenVals : [],
  indByValue : [],
  valsByOption : [],
  optionByValue : [],
  univValsByOption : [],
  childOIdsByO : [],
  previousIds : [],
  childrenByOption : [],

  initialize : function($super){
    $super(); 
  },

  
  load : function($super, element, optionId){
    
     $super(element, optionId);
     
     if (!this.oldO[optionId]){
      this.oldO[optionId] = {};
      this.oldO[optionId].dd = this.dd;     
      this.oldO[optionId].visible = true; 
      this.valsByOption[optionId] = [];
      this.oIds.push(optionId);
      this.isNewOption = true;        
      var c = 0;        
    }

    if (element.type == 'radio' || element.type == 'checkbox') {  
    
      element.checked = false;      
      this.setVars(optionId, element, c, null);         
      
    } else if(Element.hasClassName(element,'datetime-picker')) {
      
      if (!this.oldO[optionId].element)
        this.oldO[optionId].element = element;    
        
    } else if(element.type == 'select-one' || element.type == 'select-multiple') {  
    
      var options = $A(element.options);
      for (var i = 0, len = options.length; i < len; ++i){
        options[i].selected = false;      
        this.setVars(optionId, element, i, options[i]);
      }   
        
    } else {  
    
      this.oldO[optionId].element = element;  
      
    }
    
    c++;
  },


  setVars : function(optionId, element, i, option){
    if (i == 0){
      if (element.type == 'radio' || element.type == 'checkbox'){
        this.oldO[optionId].firstelement = element;
      } else {
        this.oldO[optionId].element = element;          
      }
    }
    
    var value = option ? option.value : element.value;
    if (value){
      var valueId = parseInt(value);          
      this.indByValue[valueId] = i;
      this.valsByOption[optionId].push(valueId);
      this.optionByValue[valueId] = optionId;
      this.oldV[valueId] = {};
      this.oldV[valueId].visible = true;
      this.oldV[valueId].selected = false;      
      if (option)
        this.oldV[valueId].name = option.text;      
      else
        this.oldV[valueId].element = element;
        
      if (this.config[1][valueId][2].length > 0){           
        if (!this.childOIdsByO[optionId])
          this.childOIdsByO[optionId] = [];
        this.childOIdsByO[optionId] = this.childOIdsByO[optionId].concat(this.config[1][valueId][2]);             
      } 
      if (this.config[1][valueId][3].length > 0){             
        this.childrenVals = this.childrenVals.concat(this.config[1][valueId][3]);               
        if (!this.childrenByOption[optionId])
          this.childrenByOption[optionId] = [];
        this.childrenByOption[optionId] = this.childrenByOption[optionId].concat(this.config[1][valueId][3]);
      }   
    }
  }, 
  
  
  setDependency : function(){
    var l = this.oIds.length; 
    for (var i=0;i<l;i++){
      var ll = this.valsByOption[this.oIds[i]].length;
      while (ll--){
        if (this.childrenVals.indexOf(this.valsByOption[this.oIds[i]][ll]) == -1){
          if (!this.univValsByOption[this.oIds[i]])
            this.univValsByOption[this.oIds[i]] = [];
          this.univValsByOption[this.oIds[i]].push(this.valsByOption[this.oIds[i]][ll]);
        }
      }
      var ids = this.getChildrenOptionIds(this.oIds[i]);
      if (ids.length > 0)
          this.childOIdsByO[this.oIds[i]] = ids;      
    }

    while (l--) 
      if (this.childOIdsByO[this.oIds[l]])
        this.reloadOptions(this.oIds[l], [], []);   
  },
  
  
  observeRadio : function($super, optionId, valueId){
    if (this.childOIdsByO[optionId]){
      if (!valueId)
        this.reloadOptions(optionId, [], [])      
      else
        this.reloadOptions(optionId, this.config[1][valueId][2], this.config[1][valueId][3]); 
      opConfig.reloadPrice();
    }
    this.oldO[optionId].value = valueId;  
    $super(optionId, valueId);    
  },
  
  observeCheckbox : function($super, element, optionId, valueId){
    if (this.childOIdsByO[optionId]){   
      var optionIds = [];
      var valueIds = [];    
      var l = this.valsByOption[optionId].length;
      while (l--){  
        if (this.oldV[this.valsByOption[optionId][l]].element.checked){
          if (this.config[1][this.valsByOption[optionId][l]][2].length > 0)       
            optionIds = optionIds.concat(this.config[1][this.valsByOption[optionId][l]][2]);  
          if (this.config[1][this.valsByOption[optionId][l]][3].length > 0)
            valueIds = valueIds.concat(this.config[1][this.valsByOption[optionId][l]][3]);                        
        }
      }       
      this.reloadOptions(optionId, this.unique(optionIds), this.unique(valueIds));
      opConfig.reloadPrice();
    }
    $super(element, optionId, valueId);   
  },
  
  observeSelectOne : function($super, element, optionId){
    var valueId = element.value;      
    if (this.childOIdsByO[optionId]){   
      if (!valueId){
        this.reloadOptions(optionId, [], []);
      } else {
        this.reloadOptions(optionId, this.config[1][valueId][2], this.config[1][valueId][3]); 
      } 
    }
    this.oldO[optionId].value = valueId;        
    opConfig.reloadPrice();
    $super(element, optionId);    
  },
  
  observeSelectMultiple : function($super, element, optionId){
    var options = $A(element.options);
    var optionIds = [];
    var valueIds = [];      
    var l = options.length;
    while (l--){      
      if (options[l].selected){
        if (this.config[1][options[l].value][2].length > 0)       
          optionIds = optionIds.concat(this.config[1][options[l].value][2]);            
        if (this.config[1][options[l].value][3].length > 0)
          valueIds = valueIds.concat(this.config[1][options[l].value][3]);                      
      }
      this.oldV[options[l].value].selected = options[l].selected;     
    } 
    if (this.childOIdsByO[optionId]){     
      this.reloadOptions(optionId, this.unique(optionIds), this.unique(valueIds));
      opConfig.reloadPrice();
    }
    $super(element, optionId);    
  },


  
  
  reloadOptions : function(id, optionIds, valueIds){
    var a = [];
    var l = valueIds.length;
    while (l--){
      if (!a[this.optionByValue[valueIds[l]]])
        a[this.optionByValue[valueIds[l]]] = [];        
      a[this.optionByValue[valueIds[l]]].push(valueIds[l]);
    }

    l = this.childOIdsByO[id].length;
    while (l--){
      if (a[this.childOIdsByO[id][l]]){
        if (this.univValsByOption[this.childOIdsByO[id][l]])
          a[this.childOIdsByO[id][l]] = a[this.childOIdsByO[id][l]].concat(this.univValsByOption[this.childOIdsByO[id][l]]);          
        this.reloadValues(this.childOIdsByO[id][l], a[this.childOIdsByO[id][l]]);         
      } else if(this.univValsByOption[this.childOIdsByO[id][l]]) {      
        this.reloadValues(this.childOIdsByO[id][l], this.univValsByOption[this.childOIdsByO[id][l]]);
      } else if (optionIds.indexOf(this.childOIdsByO[id][l]) != -1){
        this.showOption(this.childOIdsByO[id][l], this.oldO[this.childOIdsByO[id][l]].element);         
      } else {
        if (this.oldO[this.childOIdsByO[id][l]].element == undefined ||  this.oldO[this.childOIdsByO[id][l]].element.type == 'select-one' || this.oldO[this.childOIdsByO[id][l]].element.type == 'select-multiple')           
          this.reloadValues(this.childOIdsByO[id][l], []);    
        this.hideOption(this.childOIdsByO[id][l]);          
      } 
    } 
  },  

  
  showOption : function(id, element){
    if (!this.oldO[id].visible){
    
      this.oldO[id].dd.show();
      this.oldO[id].dd.previous().show();
      
      if (element.type == 'file'){
        var disabled = false;
        
        if (this.inPreconfigured){
          var inputBox = element.up('.input-box');
          if (!inputBox.visible()){
            var inputFileAction = inputBox.select('input[name="options_'+ id +'_file_action"]')[0];
            inputFileAction.value = 'save_old';
            disabled = true;
          } 
        }
            
        element.disabled = disabled;        
      }
      
      this.oldO[id].visible = true;
    }  
  },
  
  
  hideOption : function(id){
    if (this.oldO[id].visible){
    
      if (this.dependecyIsSet){
        var element = this.oldO[id].element ? this.oldO[id].element : this.oldO[id].firstelement;
        if (element.hasClassName('datetime-picker')){
          element.selectedIndex = 0; 
        } else if (element.type == 'text' || element.type == 'textarea') {        
          element.value = '';
        } else if (element.type == 'file') {

          if (this.inPreconfigured) {
            var inputBox = element.up('.input-box');
            if (!inputBox.visible()){
              var inputFileAction = inputBox.select('input[name="options_'+ id +'_file_action"]')[0];
              inputFileAction.value = '';                             
            }                 
          }
          
          element.disabled = true;
        }
      }
      
      this.oldO[id].dd.hide();
      this.oldO[id].dd.previous().hide();
      this.oldO[id].visible = false;
    }  
  },


  
  reloadValues : function(id, ids){

    var l = this.valsByOption[id].length;
    
    if (l == 0)
      return;  
      
    if (this.oldO[id].element != undefined){
      this.clearSelect(id);        
      for (var i=0;i<l;i++)   
        if (ids.indexOf(this.valsByOption[id][i]) != -1)      
            this.showValue(id, this.valsByOption[id][i]);                     
    } else {
      for (var i=0;i<l;i++){      
        if (ids.indexOf(this.valsByOption[id][i]) != -1){   
          if (!this.oldV[this.valsByOption[id][i]].visible)
            this.showValue(id, this.valsByOption[id][i], this.oldV[this.valsByOption[id][i]].element);
          else 
            this.resetValue(id, this.valsByOption[id][i], this.oldV[this.valsByOption[id][i]].element);
        } else if (ids.indexOf(this.valsByOption[id][i]) == -1 && this.oldV[this.valsByOption[id][i]].visible) {
          this.hideValue(id, this.valsByOption[id][i], this.oldV[this.valsByOption[id][i]].element);
        }
      }   
    }
  
  },
  
  
  showValue : function(optionId, valueId, element){
    if (element){       
      element.up('li').show();  
      this.showOption(optionId, element);       
    } else {
      var ind = this.oldO[optionId].element.options.length;     
      this.oldO[optionId].element.options[ind] = new Option(this.oldV[valueId].name, valueId);
      this.indByValue[valueId] = ind;
      this.showOption(optionId, this.oldO[optionId].element);
      if (this.oldO[optionId].element.type == 'select-one')
        this.showPickerImage(optionId, valueId);      
    } 
    this.oldV[valueId].visible = true;
  },

  clearSelect : function(optionId){  
    var l = this.valsByOption[optionId].length;

    for (var i=0;i<l;i++){
      if (this.oldV[this.valsByOption[optionId][i]].selected || this.oldO[optionId].value)
        this.resetImage(optionId, this.valsByOption[optionId][i], this.oldO[optionId].element.type);       
      this.indByValue[this.valsByOption[optionId][i]] = null;
      this.oldV[this.valsByOption[optionId][i]].visible = false;            
    }
    
    if (this.oldO[optionId].element.type == 'select-one'){
      while (l--) 
        this.hidePickerImage(optionId, this.valsByOption[optionId][l]);
      this.oldO[optionId].element.options.length = 1;                                         
    } else {
      this.oldO[optionId].element.options.length = 0;
    }   
     
  },
  
  hideValue : function(optionId, valueId, element){
    this.resetValue(optionId, valueId, element);
    if (element){
      element.up('li').hide();
    } else {
      var ind = this.indByValue[valueId];
      this.oldO[optionId].element.options[ind] = null;
      this.indByValue[valueId] = null;
      if (this.oldO[optionId].element.type == 'select-one')
        this.hidePickerImage(optionId, valueId);
    } 
    this.oldV[valueId].visible = false;
  },  
  
  
  resetValue : function(optionId, valueId, element){
    if (element){
       if (element.checked){  
        this.resetImage(optionId, valueId, element.type);       
        element.checked = false;
      }
    } else {
      var ind = this.indByValue[valueId];
      if ((this.oldV[valueId] && this.oldV[valueId].selected) || this.oldO[optionId].value){
        this.resetImage(optionId, valueId, this.oldO[optionId].element.type);
        if (this.oldO[optionId].element.type == 'select-one'){
          this.oldO[optionId].element.selectedIndex = 0;
        } else {
          this.oldO[optionId].element.options[ind].selected = false;
          this.oldV[valueId].selected = false;
        }
      }
    } 
  },  
  
  
  getChildrenOptionIds : function(id){
    if (this.previousIds[id])
      return [];
    this.previousIds[id] = true;
    if (!this.childrenByOption[id] && !this.childOIdsByO[id])
      return [];    
    var optionIds = [];
    if (this.childOIdsByO[id]){
      this.childOIdsByO[id] = this.unique(this.childOIdsByO[id]);
      optionIds = optionIds.concat(this.childOIdsByO[id]);
    }
    if (this.childrenByOption[id]){   
      var ids = this.unique(this.childrenByOption[id]);   
      var l = ids.length;
      while (l--)
        if (optionIds.indexOf(this.optionByValue[ids[l]]) == -1)
          optionIds.push(this.optionByValue[ids[l]]);
    }
    var l = optionIds.length;
    while (l--){
      var ids = this.getChildrenOptionIds(optionIds[l]);
      if (ids.length > 0){
          this.childOIdsByO[optionIds[l]] = ids;
          optionIds = optionIds.concat(ids);
      }   
    }
    return optionIds;   
  },
  
  
  unique : function(a){
    var l=a.length,b=[],c=[];
    while (l--)
      if (c[a[l]] == undefined) b[b.length] = c[a[l]] = a[l];
    return b;
  } 
  
});

