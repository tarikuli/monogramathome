<?php
class IWD_Opc_Block_Adminhtml_System_Config_Form_Field_Email extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    const LEFT_MENU = 1;
    const RIGHT_MENU = 2;

    public function __construct()
    {
        $this->addColumn('template', array(
            'label' => Mage::helper('adminhtml')->__('Template'),            
            'class' => 'required-entry'
        ));
        
        $this->addColumn('hours', array(
            'label' => Mage::helper('adminhtml')->__('Hours'),
            'style' => 'width:100px',
            'class' => 'required-entry validate-digits'
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Email Configuration');
        parent::__construct();

        $this->setTemplate('opc/system/config/form/field/email.phtml');
    }

    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }

        $column = $this->_columns[$columnName];
        
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($columnName == 'template') {
            $rendered = '<select name="' . $inputName . '" ' . ($column['style'] ? 'style="' . $column['style'] . '"' : '') . ($column['class'] ? 'class="' . $column['class'] . '"' : '') . '>';

            $newsletterTemplateCollection = Mage::getModel('newsletter/template')
                ->getCollection();
            $templateArray = array('' => Mage::helper('adminhtml')->__('[Please Select]'));
            foreach($newsletterTemplateCollection as $newsletterTemplate)
                $templateArray[$newsletterTemplate->getTemplateId()] = $newsletterTemplate->getTemplateCode();

            foreach ($templateArray as $att => $name) {
                $rendered .= '<option value="' . $att . '">' . $name . '</option>';
            }

            $rendered .= '</select>';
        } else {
            return '<input type="text" name="' . $inputName . '" ' . ($column['style'] ? 'style="' . $column['style'] . '"' : '') . ' value="#{' . $columnName . '}" ' . ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ($column['class'] ? 'class="' . $column['class'] . '"' : '') . '/>';
        }

        return $rendered;
    }

}
?>