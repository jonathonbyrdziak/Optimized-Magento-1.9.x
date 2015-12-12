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
   class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenugroup extends Mage_Adminhtml_Block_Widget_Grid_Container
   {
        public function __construct()
        {
			$this->_controller = 'adminhtml_jmmegamenugroup';
			$this->_blockGroup = 'jmmegamenu';
			$this->_headerText = Mage::helper('jmmegamenu')->__('Group Manager');
			$this->_addButtonLabel = Mage::helper('jmmegamenu')->__('Add Menu Group');
			
			parent::__construct();

        }
        
        public function getCreateUrl()
	    {
	        return $this->getUrl('*/*/newgroup');
	    }
   }		
   
 ?>  