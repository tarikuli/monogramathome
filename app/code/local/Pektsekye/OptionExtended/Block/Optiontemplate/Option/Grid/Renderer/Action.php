<?php

class Pektsekye_OptionExtended_Block_Optiontemplate_Option_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {   
        $count = 0;
        $valueCount = $this->getColumn()->getValueCount();
        if (isset($valueCount[$row->getOptionId()]))
          $count = (int) $valueCount[$row->getOptionId()]; 
          
        $actions = array();        
        if (is_null($this->getColumn()->getOnlyValues())){                                 
          $actions[] = array(
                          '@'	=>  array(
                              'href'  => $this->getUrl('*/*/edit', array('option_id' => $row->getOptionId(), '_current'=>true))
                          ),
                          '#'	=> Mage::helper('adminhtml')->__('Edit')           
                      );
        }
        
        $group  = Mage::getModel('catalog/product_option')->getGroupByType($row->getType());
                  
        if ($group == 'select'){        
           $actions[] = array(
                            '@'	=>  array(
                                'href'  => $this->getUrl('*/optiontemplate_value/index', array('option_id' => $row->getOptionId(), '_current'=>true))
                            ),
                            '#'	=> Mage::helper('optionextended')->__('View Values') . ' ('. $count . ')'           
                        ); 
        }   
        
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
