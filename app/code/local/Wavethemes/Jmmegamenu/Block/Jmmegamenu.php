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
    require_once "megamenu/mega.class.php";
    class Wavethemes_Jmmegamenu_Block_Jmmegamenu extends Mage_Page_Block_Html_Topmenu
	{
		
			var $children = null;
			var $items = null;
			var $menu = null;
			var $open = null;
			public function __construct($attributes = array()) {
			  
			   parent::__construct ();
              
              
			    $this->addData(array(
                   'cache_lifetime' => null,
               ));

			   $this->menu = new JAMenuMega();
			   $this->params = new stdclass(); 
			   
			   $this->params->megamenu = 1;
			   $this->params->startlevel = 0;
			   $this->params->endlevel = 10;
			}
			
			public function _prepareLayout()
			{
			  
				$headBlock = $this->getLayout()->getBlock('head');
				//$headBlock->addJs('joomlart/jmmegamenu/js/jmmegamenu.js');
				return parent::_prepareLayout();
			}
            
			
			public function _toHtml(){
			  
			   if(!Mage::helper('jmmegamenu')->get('show')){
			     return parent::_toHtml();
			   }
			   $this->setTemplate("joomlart/jmmegamenu/output.phtml");
			   $storeid = Mage::app()->getStore()->getStoreId();
			   $resource = Mage::getSingleton('core/resource');
               $read= $resource->getConnection('core_read');


               $menutable = $resource->getTableName('jmmegamenu_store_menugroup');
               $query = 'SELECT menugroupid '
                . ' FROM '.$menutable
                . ' WHERE store_id = '.(int) $storeid
              
                . ' ORDER BY id';
               $rows = $read->fetchRow($query); 

               if(!$rows["menugroupid"]){
               	 $rows["menugroupid"] = 0;
               }
               $collections = Mage::getModel('jmmegamenu/jmmegamenu')->getCollection()->setOrder("parent", "ASC")->setOrder("ordering","ASC")->addFilter("status",1,"eq")->addFilter("menugroup",$rows["menugroupid"]);

			   $tree = array();
			   foreach($collections as $collection){
			       $collection->tree = array();
				   $parent_tree  = array();
				   if(isset($tree[$collection->parent])){
				   	 $parent_tree = $tree[$collection->parent];
				   }
					//Create tree
				   array_push($parent_tree, $collection->menu_id);
				   $tree[$collection->menu_id] = $parent_tree;

				   $collection->tree   = $parent_tree;
			   }
			   $this->menu->getList($collections);
			   //$this->menu->genMenu();
			   ob_start();
                 $this->menu->genMenu();
                 $menuoutput = ob_get_contents();
                 $this->assign ( 'menuoutput', $menuoutput );
               ob_end_clean();
               return parent::_toHtml ();
			}
			
	 }

?>