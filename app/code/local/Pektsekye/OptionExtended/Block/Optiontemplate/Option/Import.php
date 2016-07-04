<?php
class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
  public function __construct()
  {

    $this->_blockGroup = 'optionextended';
    $this->_controller = 'optiontemplate_option';    
    $this->_mode = 'import';  
      
    $this->_backButtonLabel = $this->__('Back to Options');

    parent::__construct();

        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', $this->__('Import Options'));        

        $this->_formScripts[] = "
                productGridCheckboxCheck = function(grid, element, checked) {
				          $('product_id').value = element.value;							
                };        
                productGridRowClick = function(grid, event) {
                    var trElement = Event.findElement(event, 'tr');
                    var isInput = Event.element(event).tagName == 'INPUT';
                    if (trElement) {
                        var radio = Element.select(trElement, 'input');
                        if (radio[0]) {
                            var checked = isInput ? radio[0].checked : !radio[0].checked;
                            grid.setCheckboxChecked(radio[0], checked);
                        }
                    }
                };                 
             "; 
            
  }
  
  public function getHeaderText()
  {
      return $this->__('Choose product to import options from');
  }
    
  public function getBackUrl()
  {
      return $this->getUrl('*/optiontemplate_option/index', array('_current'=>true));
  } 
    
}
