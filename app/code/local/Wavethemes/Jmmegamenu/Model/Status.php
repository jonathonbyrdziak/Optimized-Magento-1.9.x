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
       class Wavethemes_Jmmegamenu_Model_Status extends Varien_Object
       {
	        const STATUS_ENABLED = 1;
            const STATUS_DISABLED = 0;
			static public function getOptionArray()
			{
				return array(
				self::STATUS_ENABLED => Mage::helper('jmmegamenu')->__
				('Enabled'),
				self::STATUS_DISABLED => Mage::helper('jmmegamenu')->__
				('Disabled')
				);
			}
       }