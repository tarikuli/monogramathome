<?php
class Pektsekye_OptionExtended_Block_Optiontemplate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'optiontemplate';
    $this->_blockGroup = 'optionextended';
    $this->_headerText = $this->__('Option Templates');
    $this->_addButtonLabel = $this->__('Add Template');
    parent::__construct();

  }


}
