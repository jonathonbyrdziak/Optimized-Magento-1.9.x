<?php
/*------------------------------------------------------------------------
# JM Fabian - Version 1.0 - Licence Owner JA155256
# ------------------------------------------------------------------------
# Copyright (C) 2004-2009 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
# @license - Copyrighted Commercial Software
# Author: J.O.O.M Solutions Co., Ltd
# Websites: http://www.joomlart.com - http://www.joomlancers.com
# This file may not be redistributed in whole or significant part.
-------------------------------------------------------------------------*/ 


class Wavethemes_Jmmegamenu_Model_System_Config_Source_ListAnimationType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'none', 'label'=>Mage::helper('jmmegamenu')->__('None')),
            array('value'=>'jaws', 'label'=>Mage::helper('jmmegamenu')->__('Fade')),
			array('value'=>'fence', 'label'=>Mage::helper('jmmegamenu')->__('fence')),
			array('value'=>'venitian', 'label'=>Mage::helper('jmmegamenu')->__('venitian')),
			array('value'=>'fly', 'label'=>Mage::helper('jmmegamenu')->__('fly')),
			array('value'=>'papercut', 'label'=>Mage::helper('jmmegamenu')->__('papercut')),
			array('value'=>'fan', 'label'=>Mage::helper('jmmegamenu')->__('fan')),
			array('value'=>'wave', 'label'=>Mage::helper('jmmegamenu')->__('wave')),
			array('value'=>'helix', 'label'=>Mage::helper('jmmegamenu')->__('helix')),
			array('value'=>'pop', 'label'=>Mage::helper('jmmegamenu')->__('pop')),
			array('value'=>'linear', 'label'=>Mage::helper('jmmegamenu')->__('linear')),
			array('value'=>'bounce', 'label'=>Mage::helper('jmmegamenu')->__('bounce')),
			array('value'=>'winding', 'label'=>Mage::helper('jmmegamenu')->__('winding')),
			array('value'=>'shield', 'label'=>Mage::helper('jmmegamenu')->__('shield'))
        );
    }    
}
