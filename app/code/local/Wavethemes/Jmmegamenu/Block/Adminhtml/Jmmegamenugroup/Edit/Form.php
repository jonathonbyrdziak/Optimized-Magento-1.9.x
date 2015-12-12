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
   class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenugroup_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
   {
		protected function _prepareForm()
		{
			$form = new Varien_Data_Form(array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/savegroup', array('id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
			)
			);
			$form->setUseContainer(true);
			$this->setForm($form);
			return parent::_prepareForm();
		}
	} 

