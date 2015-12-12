<?php

/**
 * zeonsolutions inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.zeonsolutions.com/shop/license-community.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * This package designed for Magento COMMUNITY edition
 * =================================================================
 * zeonsolutions does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * zeonsolutions does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   Zeon
 * @package    Zeon_Faq
 * @version    0.0.1
 * @copyright  @copyright Copyright (c) 2013 zeonsolutions.Inc. (http://www.zeonsolutions.com)
 * @license    http://www.zeonsolutions.com/shop/license-community.txt
 */

class Zeon_Faq_Block_Adminhtml_Faq_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize faq edit page. Set management buttons
     *
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_faq';
        $this->_blockGroup = 'zeon_faq';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('zeon_faq')->__('Save Faq'));
        $this->_updateButton('delete', 'label', Mage::helper('zeon_faq')->__('Delete Faq'));

        $this->_addButton(
            'save_and_edit_button', array(
            'label'   => Mage::helper('zeon_faq')->__('Save and Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class'   => 'save'
            ), 100
        );
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('faq_information_description') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'faq_information_description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'faq_information_description');
                }
            }
            function saveAndContinueEdit() {
            editForm.submit($('edit_form').action + 'back/edit/');}";
    }

    /**
     * Get current loaded faq ID
     *
     */
    public function getFaqId()
    {
        return Mage::registry('current_faq')->getId();
    }

    /**
     * Get header text for faq edit page
     *
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_faq')->getId()) {
            return $this->htmlEscape(Mage::registry('current_faq')->getTitle());
        } else {
            return Mage::helper('zeon_faq')->__('New Faq');
        }
    }

    /**
     * Get form action URL
     *
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save');
    }
}