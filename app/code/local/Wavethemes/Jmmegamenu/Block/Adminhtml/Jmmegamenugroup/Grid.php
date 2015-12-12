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
   class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenugroup_Grid extends Mage_Adminhtml_Block_Widget_Grid
   {
       
		var $CurPage = 1;
		var $LastPage = 1;
		
		public function __construct()
		{
			parent::__construct();
			$this->setId('jmmegamenugroupGrid');
			$this->setDefaultSort('id');
			$this->setDefaultDir('ASC');
			$this->setSaveParametersInSession(true);
			
		}
		
		protected function _prepareCollection()
		{
		   
			$collections = Mage::getModel('jmmegamenu/jmmegamenugroup')->getCollection()->setOrder("id", "DESC");
			
			$this->setCollection($collections);
			parent::_prepareCollection();
			return $this;
		}
		
		protected function _prepareColumns()
		{
		    
			$this->addColumn('id', array(
				'header' => Mage::helper('jmmegamenu')->__('ID'),
				'align' =>'right',
				'width' => '50px',
				'index' => 'id',
			));
			$this->addColumn('title', array(
				'header' => Mage::helper('jmmegamenu')->__('Title'),
				'align' =>'left',
				'index' => 'title',
			));
		
			$this->addColumn('action',
				array(
				'header' => Mage::helper('jmmegamenu')->__('Action'),
				'width' => '100',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array(
				array(
				'caption' => Mage::helper('jmmegamenu')->__('Edit'),
				'url' => array('base'=> '*/*/editgroup'),
				'field' => 'id'
				)
				),
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'is_system' => true,
			));
			return parent::_prepareColumns();
		}
		public function getRowUrl($row)
		{
		    return $this->getUrl('*/*/index', array('groupid' => $row->getId()));
		}
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('menu_id');
			$this->getMassactionBlock()->setFormFieldName('jmmegamenu');
			$groupList = Mage::getSingleton('jmmegamenu/listmenugroup')->getOptionArray();
			$this->getMassactionBlock()->addItem('duplicate', array(
					'label'    => Mage::helper('jmmegamenu')->__('Duplicate'),
					'url'  => $this->getUrl('*/*/massDuplicateGroup', array('_current'=>true)),
					'additional' => array(
							'visibility' => array(
									'name' => 'duplicate_to',
									'type' => 'select',
									'class' => 'required-entry',
									'label' => Mage::helper('jmmegamenu')->__('Duplicate To'),
									'values' => $groupList
							)
					)
			));
			return $this;
		}
}