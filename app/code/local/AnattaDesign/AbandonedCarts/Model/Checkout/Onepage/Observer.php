<?php

class AnattaDesign_AbandonedCarts_Model_Checkout_Onepage_Observer {

	protected $_model;

	public function __construct() {
		$this->_model = Mage::helper( 'anattadesign_abandonedcarts' )->getStatisticsModel();
	}

	public function getModel() {
		return $this->_model;
	}

	public function getQuoteId() {
		return Mage::getModel( 'checkout/cart' )->getQuote()->getId();
	}

	public function index( $observer ) {
		$statistics = $this->getModel();

		if ( !$this->getQuoteId() ) {
			return;
		}

		if ( Mage::helper( 'anattadesign_abandonedcarts' )->isAwesomeCheckoutActive() ) {
			$statistics->saveStepReached( 'shipping', $this->getQuoteId() );
		} elseif ( Mage::helper( 'anattadesign_abandonedcarts' )->isOneStepCheckoutActive() ) {
			$statistics->saveStepReached( 'review', $this->getQuoteId() );
		} else {
			if ( Mage::getSingleton( 'customer/session' )->isLoggedIn() ) {
				$statistics->saveStepReached( 'billing', $this->getQuoteId() );
			} else {
				$statistics->saveStepReached( 'login', $this->getQuoteId() );
			}
		}
	}

	public function merge( $observer ) {
		try {
			$this->getModel()->deleteByQuoteId( $observer->getSource()->getID() );
		} catch ( Exception $e ) {
			if ( Mage::getIsDeveloperMode() ) {
				echo $e->getMessage();
			}
			Mage::logException( $e );
		}
	}

	public function saveMethod( $observer ) {
		$statistics = $this->getModel();
		$statistics->saveStepMoved( 'login', $this->getQuoteId() );
		$statistics->saveStepReached( 'billing', $this->getQuoteId() );
	}

	public function loginPost( $observer ) {
		$location = Mage::getModel( 'customer/session' )->getBeforeAuthUrl();
		if ( $location ) {
			if ( strpos( $location, 'checkout/onepage' ) > 0 ) {
				$statistics = $this->getModel();
				$statistics->saveStepMoved( 'login', $this->getQuoteId() );
			}
		}
	}

	public function saveBilling( $observer ) {
		$response = $observer->getEvent()->getData( 'controller_action' )->getResponse();
		if ( $this->_isMovedToNextStep( $response ) ) {
			$statistics = $this->getModel();
			$statistics->saveStepMoved( 'billing', $this->getQuoteId() );
			$statistics->saveStepReached( $this->_getNextStep( $response ), $this->getQuoteId() );
		}
	}

	public function saveShipping( $observer ) {
		$response = $observer->getEvent()->getData( 'controller_action' )->getResponse();
		if ( $this->_isMovedToNextStep( $response ) ) {
			$statistics = $this->getModel();
			$statistics->saveStepMoved( 'shipping', $this->getQuoteId() );
			$statistics->saveStepReached( $this->_getNextStep( $response ), $this->getQuoteId() );
		}
	}

	public function saveShippingMethod( $observer ) {
		$response = $observer->getEvent()->getData( 'controller_action' )->getResponse();
		if ( $this->_isMovedToNextStep( $response ) ) {
			$statistics = $this->getModel();
			$statistics->saveStepMoved( 'shipping_method', $this->getQuoteId() );
			$statistics->saveStepReached( $this->_getNextStep( $response ), $this->getQuoteId() );
		}
	}

	public function savePayment( $observer ) {
		$response = $observer->getEvent()->getData( 'controller_action' )->getResponse();
		if ( $this->_isMovedToNextStep( $response ) ) {
			$statistics = $this->getModel();
			$statistics->saveStepMoved( 'payment', $this->getQuoteId() );
			$statistics->saveStepReached( $this->_getNextStep( $response ), $this->getQuoteId() );
		}
	}

	public function saveOrder( $observer ) {
		$response = $observer->getEvent()->getData( 'controller_action' )->getResponse();
		if ( $this->_isMovedToNextStep( $response ) ) {
			$statistics = $this->getModel();
			$statistics->saveStepMoved( 'review', $this->getQuoteId() );
		}
	}

	public function paypalReview() {
		$statistics = $this->getModel();
		$statistics->saveStepReached( 'review', $this->getQuoteId() );
	}

	public function paypalSaveOrder() {
		$statistics = $this->getModel();
		$statistics->saveStepMoved( 'review', $this->getQuoteId() );
	}

	public function finalsaveOrder( $observer ) {
		$quote_id = $observer->getEvent()->getQuote()->getId();
		$statistics = $this->getModel();
		$statistics->saveStepMoved( 'review', $quote_id );
	}

	protected function _getNextStep( Mage_Core_Controller_Response_Http $response ) {
		$body = json_decode( $response->getBody() );
		return isset( $body->goto_section ) ? $body->goto_section : '';
	}

	protected function _isMovedToNextStep( Mage_Core_Controller_Response_Http $response ) {
		if ( $response->getHttpResponseCode() == 200 ) {
			$body = json_decode( $response->getBody() );
			if ( empty( $body->error ) ) {
				return true;
			}
		}
		return false;
	}

}
