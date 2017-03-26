<?php 

class IWD_Opc_Block_Page_Html_Topmenu extends Mage_Page_Block_Html_Topmenu
{
	protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            $childId = $child->getId();

            $categoryObject = Mage::getModel('catalog/category');
            $displayMenu = true;            
            if (strpos($childId, 'category-node-') !== false) {
                $categoryId = intval(str_replace('category-node-', '', $childId));
                $categoryObject->load($categoryId);                
                if($categoryObject->getId())
                {
                    $includeMenuSite = array(IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_BOTH, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_MAIN_MENU, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_MAIN_MEMBER_MENU);
                    $ambassadorObject = Mage::getSingleton('core/session')->getAmbassadorObject();
                    if(isset($ambassadorObject))
                    {
                        if(Mage::getSingleton('customer/session')->isLoggedIn())
                        {
                            $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
                            if($currentCustomer->getId() == $ambassadorObject->getId())
                                $includeMenuSite = array(IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_BOTH, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_AMBASSADOR_MENU, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_AMBASSADOR_ONLY);
                            else
                                $includeMenuSite = array(IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_BOTH, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_AMBASSADOR_MENU, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_MAIN_MEMBER_MENU, IWD_Opc_Model_Attribute_Source_Menu_Type::TYPE_AMBASSADOR_MEMBER_MENU);
                        }
                    }

                    $includeMenu = $categoryObject->getIncludeMenu();

                    if(!in_array($includeMenu, $includeMenuSite))
                        $displayMenu = false;
                }
            }

            if($displayMenu)
            {
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>'
                    . $this->escapeHtml($child->getName()) . '</span></a>';

                if ($child->hasChildren()) {
                    if (!empty($childrenWrapClass)) {
                        $html .= '<div class="' . $childrenWrapClass . '">';
                    }
                    $html .= '<ul class="level' . $childLevel . '">';
                    $html .= $this->_getHtml($child, $childrenWrapClass);
                    $html .= '</ul>';

                    if (!empty($childrenWrapClass)) {
                        $html .= '</div>';
                    }
                }
                $html .= '</li>';

                $counter++;
            }
            
        }

        return $html;
    }
}

?>