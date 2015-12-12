<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'Mage'.DIRECTORY_SEPARATOR.'Checkout'.DIRECTORY_SEPARATOR.'Block'.DIRECTORY_SEPARATOR.'Cart'.DIRECTORY_SEPARATOR.'Totals.php';
class Widgetized_Recorder_Block_Account_Totals extends Mage_Checkout_Block_Cart_Totals
{
    public function _construct(){
        parent::_construct();
        $this->setTemplate('recurring/order/totals.phtml');
    }
    
    public function getQuote() {
        return $this->getRecurring()->getQuote();
    }
    
    public function getRecurring() {
        if ($this->__recurring==null) {
            $this->__recurring = Mage::registry('recurring_order');
        }
        return $this->__recurring;
    }
    
    public function setRecurring( $recurring ) {
        $this->__recurring = $recurring;
        Mage::unregister('recurring_order');
        Mage::register('recurring_order', $this->__recurring);
    }

    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array('_relative'=>false);
        $area = $this->getArea();
        if ($area) {
            $params['_area'] = $area;
        }
        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        $templateName = str_replace('adminhtml\base\default', 'frontend\rwd\4schools', $templateName);
        return $templateName;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        $html = $this->renderView();
        return $html;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        Varien_Profiler::start($fileName);

        // EXTR_SKIP protects from overriding
        // already defined variables
        extract ($this->_viewVars, EXTR_SKIP);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo <<<HTML
<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'"
onmouseout="this.style.zIndex='998'" title="{$fileName}">{$fileName}</div>
HTML;
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo <<<HTML
<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'" onmouseout="this.style.zIndex='998'"
title="{$thisClass}">{$thisClass}</div>
HTML;
            }
        }

        try {
            $includeFilePath = $fileName;
            if (strpos($includeFilePath, realpath($this->_viewDir)) === 0 || $this->_getAllowSymlinks()) {
                include $includeFilePath;
            } else {
                Mage::log('Not valid template file:'.$fileName, Zend_Log::CRIT, null, null, true);
            }

        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        Varien_Profiler::stop($fileName);
        return $html;
    }
}