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
	  
      class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenugroup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
	  {
	  
		protected function _prepareForm()
		{
		  
			 $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/savegroup', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   ));
			
			 $helper = Mage::helper('jmmegamenu');
				 
			 $this->setForm($form);
		     $fieldset = $form->addFieldset('jmmegamenugroup_form', array('legend'=>Mage::helper('jmmegamenu')->__('jmmegamenu group information')));
			   
				$fieldset->addField('title', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Group title'),
					'class' => 'required-entry',
					'required' => true,
					'name' => 'title',
				));

                
                $fieldset->addField('menutype', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Group Unique'),
					'class' => 'required-entry',
					'required' => true,
					'name' => 'menutype',
				));
                $stores = Mage::app()->getStores();

                $fieldset->addField('description', 'textarea', array(
					'label' => Mage::helper('jmmegamenu')->__('Group Description'),
					'name' => 'description',
				));
			
			    $fieldset->addField('storeid', 'select', array(
	                'name'      => 'storeid',
	                'label'     => Mage::helper('cms')->__('Store View'),
	                'title'     => Mage::helper('cms')->__('Store View'),
	                'required'  => true,
	                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
	                'disabled'  => false
                ));
				if ( Mage::getSingleton('adminhtml/session')->getJmMegamenugroupData() )
				{
				     
					$form->setValues(Mage::getSingleton('adminhtml/session')->getJmMegamenugroupData());
					Mage::getSingleton('adminhtml/session')->setJmMegamenugroupData(null);
				} elseif ( Mage::registry('jmmegamenugroup_data') ) { 
						
					$form->setValues(Mage::registry('jmmegamenugroup_data')->getData());
				}
				return parent::_prepareForm();
			}
			
}   