<?php
/*------------------------------------------------------------------------
# $JA#PRODUCT_NAME$ - Version $JA#VERSION$ - Licence Owner $JA#OWNER$
# ------------------------------------------------------------------------
# Copyright (C) 2004-2009 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: J.O.O.M Solutions Co., Ltd
# Websites: http://www.joomlart.com - http://www.joomlancers.com
# This file may not be redistributed in whole or significant part.
-------------------------------------------------------------------------*/

  class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenugroup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
  {
			public function __construct()
			{
			parent::__construct();
				$this->_objectId = 'id';
				$this->_blockGroup = 'jmmegamenu';
				$this->_controller = 'adminhtml_jmmegamenugroup';
				$this->_updateButton('save', 'label', Mage::helper('jmmegamenu')->__('Save Menu Group'));
				$this->_updateButton('delete', 'label', Mage::helper('jmmegamenu')->__('Delete Menu Group'));
				$this->_addButton('saveandcontinue', array(
					'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
					'onclick'   => 'saveAndContinueEdit()',
					'class'     => 'save',
				), -100);
                $objId = $this->getRequest()->getParam($this->_objectId);

			    if (! empty($objId)) {
			        $this->_addButton('delete', array(
			            'label'     => Mage::helper('adminhtml')->__('Delete'),
			            'class'     => 'delete',
			            'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('Are you sure you want to remove this menu group and menu items belong to it?')
			                .'\', \'' . $this->getDeleteUrl() . '\')',
			        ));
			    }


				$this->_formScripts[] = "
				      
				      function saveAndContinueEdit(){
                         editForm.submit($('edit_form').action+'back/editgroup/');
                      };

				    ";
			}
			
			public function getHeaderText()
			{
				if( Mage::registry('jmmegamenugroup_data') && Mage::registry('jmmegamenugroup_data')->getId() ) {
				   return Mage::helper('jmmegamenu')->__("Edit Group '%s'", $this->htmlEscape(Mage::registry('jmmegamenugroup_data')->getTitle()));
				} else {
				   return Mage::helper('jmmegamenu')->__('Add Menu Group');
			    }

			
		    }
		    public function getDeleteUrl()
            {
                  return $this->getUrl('*/*/deletegroup', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
            }
  }