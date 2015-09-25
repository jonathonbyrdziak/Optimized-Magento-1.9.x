<?php

class AnattaDesign_AbandonedCarts_WidgetController extends Mage_Adminhtml_Controller_Action {

	public function renderAction() {
		$html = $this->getLayout()->createBlock( 'anattadesign_abandonedcarts/widget', 'root' )->setTemplate( 'anattadesign/abandonedcarts/widget.phtml' )->toHtml();
		$this->getResponse()->setBody( $html );
	}
}