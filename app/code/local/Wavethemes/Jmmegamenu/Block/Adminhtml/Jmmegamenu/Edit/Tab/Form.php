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
	  
      class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenu_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
	  {
	  
		protected function _prepareForm()
		{
		  
			 $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   ));
			  
			 $collections = Mage::getModel('jmmegamenu/jmmegamenu')->getCollection()->addFieldToFilter("menu_id",array("neq" => $this->getRequest()->getParam('id')));
			 
			
			 $helper = Mage::helper('jmmegamenu');
	         
			 $statics =  Mage::getResourceModel('cms/block_collection')->load()->toOptionArray();
			 $menugroup = Mage::getModel('jmmegamenu/jmmegamenugroup')->getCollection()->toOptionArray();
			 //get out the orders array
			 if($this->getRequest()->getParam('id')) { 
			   $orders = $helper->getorders($this->getRequest()->getParam('id'));
			 }
			 //Get the menu items list		  
			 $parents = $helper->getoutputList(0,$collections,"title","menu_id","parent",true);
			 
			 //The root category id of default store 
	         $parent = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId(); 
			 
	        /**
			 * Check if parent node of the store still exists
			 */
			 $category = Mage::getModel('catalog/category');
			 /* @var $category Mage_Catalog_Model_Category */
			 if (!$category->checkId($parent)) {
				
				return array();
			 }
	
			 $recursionLevel  = max(0, (int) Mage::app()->getWebsite(true)->getDefaultStore()->getConfig('catalog/navigation/max_depth'));
			 $storeCategories = $category->getCategories($parent, $recursionLevel, false, true, true);
			
			 $storeCategories =  $storeCategories->load()->addAttributeToSelect("*");
			 
			 //categories list
			 $catlist = $helper->getoutputList($parent,$storeCategories,"name","entity_id","parent_id");
			 $clist = array();
			 foreach($catlist as $id => $cat){
			    $category = Mage::getModel('catalog/category')->load($id);
				$url =  str_replace ( Mage::getBaseUrl() , "" , $category->getUrl() );
			    $clist[$url] = $cat; 
			 }

			 if(Mage::getStoreConfig("web/secure/use_in_adminhtml")){
                    $baseurl = Mage::getStoreConfig("web/secure/base_url");
				 }else{
                    $baseurl = Mage::getBaseUrl();  
			 }
			 if(!strpos($baseurl,"index.php")) $baseurl .= "index.php/";
			 //cmspages list 
			 $cmspages = array();
			 foreach($helper->getListcms() as $page){
				$cmspages[$page] = $page;
			 } 
			 
			  $this->setForm($form);
		      $fieldset = $form->addFieldset('jmmegamenu_form', array('legend'=>Mage::helper('jmmegamenu')->__('jmmegamenu information')));
			   
				$fieldset->addField('title', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Menu title'),
					'class' => 'required-entry',
					'required' => true,
					'name' => 'title',
				));
				$fieldset->addField('image', 'image', array(
						'label'     => Mage::helper('jmmegamenu')->__('Image File'),
						'required'  => false,
						'name'      => 'image',
				));
				$fieldset->addField('menualias', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Alias'),
					'required' => false,
					'name' => 'menualias',
				));
				
				$fieldset->addField('showtitle', 'radios', array(
					'label'     => Mage::helper('jmmegamenu')->__('Show title'),
					 'name'      => 'showtitle',
					 'values' => array(
										array('value'=>'1','label'=>'Yes'),
										array('value'=>'0','label'=>'No'),
								   ),
					 'disabled' => false,
					 'readonly' => false,
					 'tabindex' => 0
				));

				$fieldset->addField('menugroup', 'select', array(
						'label' => Mage::helper('jmmegamenu')->__('Display In'),
						'required' => true,
						'size' => 10,
						'name' => 'menugroup',
						'values' => $menugroup,
			    ));

				$fieldset->addField('status', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Status'),
					'name' => 'status',
					'values' => array(
						array(
							'value' => 1,
							'label' => Mage::helper('jmmegamenu')->__('Active'),
						),
						array(
							'value' => 0,
							'label' => Mage::helper('jmmegamenu')->__('Inactive'),
							),
					),
				));
				if($this->getRequest()->getParam('id') ) {
					$fieldset->addField('ordering', 'select', array(
						'label' => Mage::helper('jmmegamenu')->__('Order'),
						'required' => false,
						'size' => 10,
						'name' => 'ordering',
						'values' => $orders,
					));
				}
				$fieldset->addField('parent', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Parent'),
					'required' => false,
					'size' => 10,
					'name' => 'parent',
					'values' => $parents,
				));
				
				
				$fieldset->addField('menutype', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Menu type'),
					'required' => false,
					'name' => 'menutype',
					'values' => array('0' => "Categories",'1' => "Cms pages",'2' => "Custom link"),
					'value' => 2,
				));
				
				$fieldset->addField('link', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Link'),
					'class' => 'required-entry',
					'required' => true,
					'name' => 'link',
				));
				$fieldset->addField('category', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Select a category'),
					'required' => false,
					'size' => 10,
					'name' => 'category',
					'values' => $clist,
				));

				$fieldset->addField('shownumproduct', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Show number products of Category'),
					'required' => false,
					'size' => 10,
					'name' => 'shownumproduct',
					'values' => array(
						array(
							'value' => 0,
							'label' => Mage::helper('jmmegamenu')->__('Use General Setting'),
						),
						array(
							'value' => 1,
							'label' => Mage::helper('jmmegamenu')->__('Enable'),
						),
						array(
							'value' => 2,
							'label' => Mage::helper('jmmegamenu')->__('Disable'),
							),
					),
				));
				
				$fieldset->addField('cms', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Select a cms Page'),
					'required' => false,
					'size' => 10,
					'name' => 'cms',
					'values' => $cmspages,
				));
				$fieldset->addField('mega_cols', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Columns'),
					'required' => false,
					'width' => '100px',
					 'value' => '1',
					'name' => 'mega_cols',
				));
				
				$fieldset->addField('mega_group', 'radios', array(
					'label' => Mage::helper('jmmegamenu')->__('Group'),
					
					'required' => false,
					'name' => 'mega_group',
					'values' => array(
										array('value'=>'0','label'=>'No'),
										array('value'=>'1','label'=>'Yes'),
										
								   ),
				));
				$fieldset->addField('mega_width', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Submenu Width'),
					'required' => false,
					'name' => 'mega_width',
				));
				$fieldset->addField('mega_colw', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Submenu Column Width'),
					'required' => false,
					'name' => 'mega_colw',
				));
				$fieldset->addField('mega_colxw', 'textarea', array(
					'label' => Mage::helper('jmmegamenu')->__('Submenu Column[i] Width'),
					'required' => false,
					'style'     => 'width:300px; height:100px;',
					'name' => 'mega_colxw',
				));
				$fieldset->addField('mega_class', 'text', array(
					'label' => Mage::helper('jmmegamenu')->__('Additional class'),
				    'required' => false,
					'name' => 'mega_class',
				));
				$fieldset->addField('browserNav', 'select', array(
					'label' => Mage::helper('jmmegamenu')->__('Target Window'),
					'required' => false,
					'size' => 10,
					'name' => 'browserNav',
					'values' =>  array(
										array('value'=>'0','label'=>'Parent'),
										array('value'=>'1','label'=>'New window With Navigation'),
										array('value'=>'2','label'=>'New without navigation'),
								   ),
				));
				
				$fieldset->addField('mega_subcontent', 'radios', array(
					'label'     => Mage::helper('jmmegamenu')->__('Submenu Content'),
					 'name'      => 'mega_subcontent',
					 'onclick' => "",
					 'onchange' => "",
					 'class'    => "mega_subcontent",
					 'value'  => '2',
					 'values' => array(
										array('value'=>'1','label'=>Mage::helper('jmmegamenu')->__('Child menu items'),'selected' => 'true'),
										array('value'=>'2','label'=>Mage::helper('jmmegamenu')->__('Custom xml block')),
										array('value'=>'3','label'=>Mage::helper('jmmegamenu')->__('Satic blocks')),
								   ),
					 'disabled' => false,
					 'readonly' => false,
					 'tabindex' => 0
				));
				
				$fieldset->addField('staticblocks', 'multiselect', array(
					'label' => Mage::helper('jmmegamenu')->__('Select Blocks'),
					'required' => false,
					'size' => 10,
					'name' => 'staticblocks',
					'values' => $statics,
				));
				
				$fieldset->addField('url', 'hidden', array(
					'required' => false,
					'size' => 10,
					'name' => 'url',
					
				));
                
                $fieldset->addField('catid', 'hidden', array(
					'required' => false,
					'size' => 10,
					'name' => 'catid',
					
				));

				$fieldset->addField('static_block', 'hidden', array(
					'required' => false,
					'size' => 10,
					'name' => 'static_block',
					
				));

				$fieldset->addField('menu_id', 'hidden', array(
					'required' => false,
					'size' => 10,
					'name' => 'menu_id',
					
				));

				$fieldset->addField('contentxml', 'editor', array(
				    'name'      => 'contentxml',
				    'label'     => Mage::helper('jmmegamenu')->__('Custom block xml'),
				     'title'     => Mage::helper('jmmegamenu')->__('Custom block xml'),
				     'style'     => 'width:400px; height:200px;',
				     'wysiwyg'   => false,
				     'required'  => false,
			     ));
				$fieldset->addField('description', 'editor', array(
						'label'     => Mage::helper('jmmegamenu')->__('Description'),
						'title'     => Mage::helper('jmmegamenu')->__('Description'),
						'style'     => 'width:400px; height:200px;',
						'required' => false,
						'wysiwyg'   => false,
						'name' => 'description',
				));

				// Append dependency javascript
				$this->setChild('form_after', $this->getLayout()
				    ->createBlock('adminhtml/widget_form_element_dependence')
				        ->addFieldMap('menutype', 'menutype')
				        ->addFieldMap('shownumproduct', 'shownumproduct')
				        ->addFieldDependence('shownumproduct', 'menutype', 0) // 2 = 'Specified'
				);
			
				if ( Mage::getSingleton('adminhtml/session')->getJmMegamenuData() )
				{
				     
					$form->setValues(Mage::getSingleton('adminhtml/session')->getJmMegamenuData());
					Mage::getSingleton('adminhtml/session')->setJmMegamenuData(null);
				} elseif ( Mage::registry('jmmegamenu_data') ) { 
						
					$form->setValues(Mage::registry('jmmegamenu_data')->getData());
				}
				return parent::_prepareForm();
			}
			
}   