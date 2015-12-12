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

  class Wavethemes_Jmmegamenu_Block_Adminhtml_Jmmegamenu_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
  {
		public function __construct()
		{
			parent::__construct();
				$this->_objectId = 'id';
				$this->_blockGroup = 'jmmegamenu';
				$this->_controller = 'adminhtml_jmmegamenu';
				$this->_updateButton('save', 'label', Mage::helper('jmmegamenu')->__('Save Menu Item'));
				$this->_updateButton('delete', 'label', Mage::helper('jmmegamenu')->__('Delete Menu Item'));
				$this->_updateButton('back', array('label' => Mage::helper('jmmegamenu')->__('back to group')));
				$this->_addButton('saveandcontinue', array(
					'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
					'onclick'   => 'saveAndContinueEdit()',
					'class'     => 'save',
				), -100);
                
                //The root category id of default store 
	            $parent = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId(); 
                $helper = Mage::helper('jmmegamenu');
                /**
				 * Check if parent node of the store still exists
				 */
				 $category = Mage::getModel('catalog/category');
				 /* @var $category Mage_Catalog_Model_Category */
				 if ($category->checkId($parent)) {
					
					 $recursionLevel  = max(0, (int) Mage::app()->getWebsite(true)->getDefaultStore()->getConfig('catalog/navigation/max_depth'));
					 $storeCategories = $category->getCategories($parent, $recursionLevel, false, true, true);
					
					 $storeCategories =  $storeCategories->load()->addAttributeToSelect("*");
					 
					 //categories list
					 $catlist = $helper->getoutputList($parent,$storeCategories,"name","entity_id","parent_id");
					 $clist = "var mycats = new Array();  \n";

					 foreach($catlist as $id => $cat){
					    $category = Mage::getModel('catalog/category')->load($id);
					 	$url =  $category->getUrl();
					  
					    $clist .=  "mycats['".$url."']  = ".$id."\n";
					 }
				 }
				 
				 if(Mage::getStoreConfig("web/secure/use_in_adminhtml")){
                    $baseurl = Mage::getStoreConfig("web/secure/base_url");
				 }else{
                    $baseurl = Mage::getBaseUrl();  
				 }
				 if(!strpos($baseurl,"index.php")) $baseurl .= "index.php/";
				 $this->getUrl('*/*/index', array('groupid' => $this->getRequest()->getParam('id')));
		      // print_r(Mage::registry('jmmegamenu_data')->getData());die();

				$this->_formScripts[] = "
				      var baseurl = '".$baseurl."';
				     ".$clist.";
				      function saveAndContinueEdit(){
                         editForm.submit($('edit_form').action+'back/edit/');
                      }
                    
					  function updateurl(url){
					     url = url.replace(baseurl,'');
						 $('url').setValue(url);      
					  }
                      
                      function updatecatid(id){
                      	$('catid').setValue(id);
                      }

				      function getStatics()
					  {
						 var Form = document.forms.edit_form;
						
						 var statics = '';
						 var x = 0;
				         var first = 1;
						 for (x=0;x<Form.staticblocks.length;x++)
						 {
						    
							if (Form.staticblocks[x].selected)
							{
							 if(first) {
							    first = 0;
							 }else{
							    statics = statics + ',';
							 }
							 statics = statics  + Form.staticblocks[x].value ;
							}
						 }
					
						 return statics;
					  }
					  
                      function setStatics(exstring)
					  {
					   
						 var Form = document.forms.edit_form;
						 var y = 0;
						 var arr = exstring.split(',');
						
						 for(y=0;y<$('staticblocks').length;y++)
						 {
						      arr.each(function(value){
							     if($('staticblocks')[y].value == value)  $('staticblocks')[y].selected = true
							  })
						 }
						 
						 return true;
					  }
 
				   
				      var first = 1;
				      $('menugroup').observe('change',function(){
					           
						    	group = $('menugroup');
                                category = $('category');
                                cms = $('cms');
                                order = $('parent');
                                groupurl = baseurl+'/jmmegamenu/adminhtml_jmmegamenu/ajax?menugroup='+group.getValue()+'&menuid='+$('menu_id').getValue();
                                groupurl = groupurl+'&activecat='+category.getValue();
                                var cmstext = cms.selectedIndex >= 0 ? cms.options[cms.selectedIndex].innerHTML : undefined;
                             	groupurl = groupurl+'&activecms='+cmstext;
                             	
                             	new Ajax.Request(groupurl, {
								  onSuccess: function(response) {
								    // Handle the response content...
                                    var response = response.responseText.evalJSON();
								   
								    parentcategory = category.up('td');
								    parentcms = cms.up('td');
								    parentorder = order.up('td');
                                    Element.remove(category);
                                    Element.remove(cms);
                                    Element.remove(order);
                                    parentcategory.insert(response.category);
                                    parentcms.insert(response.cmspage);
                                    parentorder.insert(response.parent);
                                    //rebind change event for the category select
                                      
								       $('category').observe('change',function(){
							            $('link').setValue($('category').getValue());
							            updatecatid(mycats[$('link').getValue()]);
							          	updateurl($('link').getValue());
					                   });
                                   
                                       $('cms').observe('change',function(){
					                       $('link').setValue($('cms').getValue());
								           updateurl($('link').getValue());
					                   });
								    },
								    'Content-type':'application/json'
								});
										   
					  });
			          $('menutype').observe('change',function(){
					           
						    	type = $('menutype');
								if(first){
								   first = 0;
								}else{
								   $('link').value = '';
								}
								$('link').setStyle({'opacity':0.3});
						        if($(type).getValue() == 2){
									  $('category').up('tr').setStyle({'display':'none'});
									  $('cms').up('tr').setStyle({'display':'none'});
									  $('link').removeAttribute('readonly');
									  $('link').setStyle({'opacity':1});
							    }else if($(type).getValue() == 0){
								       $('cms').up('tr').setStyle({'display':'none'});
									   $('category').up('tr').setStyle({'display':''});
									   $('category').simulate('change');
									   $('link').setAttribute('readonly','true');
								}else if($(type).getValue() == 1){
								        $('category').up('tr').setStyle({'display':'none'});
									    $('cms').up('tr').setStyle({'display':''});
										 $('cms').simulate('change');
										$('link').setAttribute('readonly','true');
								}	
										   
					   });
					   $('category').observe('change',function(){
					            $('link').setValue($('category').getValue());
					            updatecatid(mycats[$('link').getValue()]);
					          	updateurl($('link').getValue());
					   });
					   $('menugroup').simulate('change');
					   $('cms').observe('change',function(){
					            $('link').setValue($('cms').getValue());
								updateurl($('link').getValue());
					   });
				       $('menutype').simulate('change');
					   
					   $('staticblocks').observe('change',function(){
					     	$('static_block').setValue(getStatics());
					   });
					 
					   setStatics($('static_block').getValue());
					   $$('.mega_subcontent').each(function(item){
					   
					        
					       $(item).observe('click',function(){
						      var itemvalue = $(item).getValue();  
						      if(itemvalue == 1){
							       $('staticblocks').up('tr').setStyle({'display':'none'});
								   $('contentxml').up('tr').setStyle({'display':'none'});
							  }else if(itemvalue == 2){
							       $('staticblocks').up('tr').setStyle({'display':'none'});
								   $('contentxml').up('tr').setStyle({'display':''});
							  }else{
							        $('staticblocks').up('tr').setStyle({'display':''});
								   $('contentxml').up('tr').setStyle({'display':'none'});
							  }
							  
						   });
						
						   if($(item).getValue() !== null){
						      $(item).simulate('click');
						   }
						   
					   })
                       
				    ";
			}
			
			public function getHeaderText()
			{
				if( Mage::registry('jmmegamenu_data') && Mage::registry('jmmegamenu_data')->getId() ) {
				   return Mage::helper('jmmegamenu')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('jmmegamenu_data')->getTitle()));
				} else {
				   return Mage::helper('jmmegamenu')->__('Add Menu Item');
			    }
			
		   }

		   public function getBackUrl()
           {
              
               if(Mage::registry('jmmegamenu_data')){
               	  $groupid = Mage::registry('jmmegamenu_data')->getData("menugroup");
               }
               if(!$groupid)  $groupid = Mage::app()->getRequest()->getParam('groupid');
               return $this->getUrl('*/*/index/groupid/'.$groupid);
           }
           public function getDeleteUrl()
           {
           	   if(Mage::registry('jmmegamenu_data')){
               	  $groupid = Mage::registry('jmmegamenu_data')->getData("menugroup");
               }
               if(!$groupid)  $groupid = Mage::app()->getRequest()->getParam('groupid');
               return $this->getUrl('*/*/delete', array($this->_objectId => $this->getRequest()->getParam($this->_objectId),"group" => $groupid));
           }
  }