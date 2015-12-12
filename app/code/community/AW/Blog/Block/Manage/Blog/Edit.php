<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento professional edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Blog_Block_Manage_Blog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'blog';
        $this->_controller = 'manage_blog';

        $this->_updateButton('save', 'label', Mage::helper('blog')->__('Save Post'));
        $this->_updateButton('delete', 'label', Mage::helper('blog')->__('Delete Post'));

        $this->_addButton(
            'saveandcontinue',
            array(
                 'label'   => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                 'onclick' => 'saveAndContinueEdit()',
                 'class'   => 'save',
            ),
            -100
        );

        if ($this->getRequest()->getParam('id')) {
            $this->_addButton(
                'diplicate',
                array(
                     'label'   => Mage::helper('blog')->__('Duplicate Post'),
                     'onclick' => 'duplicate()',
                     'class'   => 'save'
                ),
                0
            );
        }

        $this->_formScripts[]
            = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('blog_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function duplicate() {
                $(editForm.formId).action = '" . $this->getUrl('*/*/duplicate') . "';
                editForm.submit();
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('blog_data') && Mage::registry('blog_data')->getId()) {
            return Mage::helper('blog')->__(
                "Edit Post '%s'", $this->escapeHtml(Mage::registry('blog_data')->getTitle())
            );
        } else {
            return Mage::helper('blog')->__('Add Post');
        }
    }
}