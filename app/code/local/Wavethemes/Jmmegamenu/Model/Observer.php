<?php
    class Wavethemes_Jmmegamenu_Model_Observer
	{

          public function prefarestoreForm($block){
            if(Mage::registry('store_type') == 'store'){
          	   $form = $block["block"]->getForm();
               $storeid = Mage::app()->getRequest()->getParam("store_id");

               $collections = Mage::getModel('jmmegamenu/jmmegamenugroup')->getCollection()->addFieldToFilter("storeid",array("eq" => $storeid))->setOrder("id", "DESC");
               $listgroup = array();

               foreach ($collections as $collection) {
                  $listgroup[$collection->id] = $collection->title;
               }
               
               //add the menugoup field
               if(!empty($listgroup)){
                   
                   $resource = Mage::getSingleton('core/resource');
                   $read= $resource->getConnection('core_read');
                   $menutable = $resource->getTableName('jmmegamenu_store_menugroup');
                   $query = 'SELECT menugroupid '
                    . ' FROM '.$menutable
                    . ' WHERE store_id = '.(int) $storeid
                  
                    . ' ORDER BY id';
                   $rows = $read->fetchRow($query);
                   $fieldset = $form->addFieldset('jmmegamenu_fieldset', array(
                       'legend' => Mage::helper('core')->__('Jm megamenu Information')
                   ));

                  //print_r($listgroup);die();
                   $fieldset->addField('menugroup', 'select', array(
                      'name'      => 'menugroup',
                      'label'     => Mage::helper('jmmegamenu')->__('Menu Group'),
                      'no_span'   => true,
                      'values'     => $listgroup
                   ));
                   $menugroup = $form->getElement("menugroup");
                   $menugroup->setValue($rows['menugroupid']);
                   if($rows['menugroupid']){
                      //die($rows['menugroupid']);
                      $form->setValue("menugroup",$rows['menugroupid']);
                   }
                   $block["block"]->setForm($form);
                  // print_r($block);die();   
               }

               
            }  
              
          }   


          public function storeedit($store){

              if(Mage::app()->getRequest()->isPost() && $postData = Mage::app()->getRequest()->getPost() ){
                  if($postData['store_type'] == 'store'){
                    
                	   $storegroupmodel = Mage::getModel('jmmegamenu/jmmegamenustoregroup');
                     $storecollection = Mage::getModel('jmmegamenu/jmmegamenustoregroup')->getCollection()->addFieldToFilter("store_id",array("eq" => $postData['store']['store_id']));

                     foreach($storecollection as $collection){
                       $id = $collection->id;
                       break;
                     }
                     if($id){
                        $storegroupmodel->load($id);
                     }
                   
                    
                    $save['menugroupid'] = $postData['menugroup'];
                    $save['store_id'] = $postData['store']['store_id'];
                    
                    $storegroupmodel->setData('menugroupid',$postData['menugroup']);
                    $storegroupmodel->setData('store_id',$postData['store']['store_id']);
                    $storegroupmodel->save(); 
                  }
              }
          }

	}




?>