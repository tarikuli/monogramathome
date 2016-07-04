<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $count = 0;
        $optionCount = $this->getColumn()->getOptionCount();
        if (isset($optionCount[$row->getTemplateId()]))        
          $count = (int) $optionCount[$row->getTemplateId()];

        $actions = array(
                          array(
                              '@'	=>  array(
                                  'href'  => $this->getUrl('*/*/edit', array('template_id' => $row->getTemplateId()))
                              ),
                              '#'	=> Mage::helper('adminhtml')->__('Edit')          
                          ),        
                          array(
                              '@'	=>  array(
                                  'href'  => $this->getUrl('*/optiontemplate_option/index', array('template_id' => $row->getTemplateId()))
                              ),
                              '#'	=> Mage::helper('optionextended')->__('View Options') . ' ('. $count . ')'           
                          )        
        );


        return $this->_actionsToHtml($actions);
    }

    protected function _getEscapedValue($value)
    {
        return addcslashes(htmlspecialchars($value),'\\\'');
    }

    protected function _actionsToHtml(array $actions)
    {
        $html = array();
        $attributesObject = new Varien_Object();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        $value = implode('<span class="separator">&nbsp;|&nbsp;</span>', $html);
        return '<span class="nobr">' . $value . '</span>';
    }

}
