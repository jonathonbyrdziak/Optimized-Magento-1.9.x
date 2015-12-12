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
   class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenu extends Mage_Adminhtml_Block_Widget_Grid_Container
   {
        public function __construct()
        {
			$this->_controller = 'adminhtml_jmmegamenu';
			$this->_blockGroup = 'jmmegamenu';
			$groupid = $this->getRequest()->getParam('groupid');
			$group = Mage::getModel('jmmegamenu/jmmegamenugroup')->load($groupid);
			$this->_headerText = Mage::helper('jmmegamenu')->__('Item Manager for '.$group->gettitle());
			$this->_addButtonLabel = Mage::helper('jmmegamenu')->__('Add Menu Item');
			
			parent::__construct();
        }

        public function getCreateUrl()
        {
          $groupid = $this->getRequest()->getParam('groupid');

          return $this->getUrl('*/*/new/',array("groupid" => $groupid));
        }
   }		
   
 