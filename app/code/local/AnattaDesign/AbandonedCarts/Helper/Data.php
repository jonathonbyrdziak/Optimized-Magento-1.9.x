<?php

class AnattaDesign_AbandonedCarts_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getStatisticsModel() {
		if ( $this->isAwesomeCheckoutActive() ) {
			return Mage::getModel( 'anattadesign_abandonedcarts/statistics' );
		} elseif ( $this->isOneStepCheckoutActive() ) {
			return Mage::getModel( 'anattadesign_abandonedcarts/osstatistics' );
		} else {
			return Mage::getModel( 'anattadesign_abandonedcarts/opstatistics' );
		}
	}

	public function isAwesomeCheckoutActive() {
		return Mage::getConfig()->getModuleConfig( 'AnattaDesign_AwesomeCheckout' )->is( 'active', 'true' );
	}

	public function isOneStepCheckoutActive() {
		return Mage::getConfig()->getModuleConfig( 'Idev_OneStepCheckout' )->is( 'active', 'true' );
	}

	public function getMessage() {

		$cache = Mage::getSingleton( 'core/cache' );
		$payload = $cache->load( 'abandonedcart_payload' );

		if ( $payload === false ) {

			$payload = file_get_contents( 'http://api.anattadesign.com/abandonedcart/1alpha/fetch/payload' );
			$contents = json_decode( $payload, true );

			if ( $contents['status'] == 'success' ) {
				$message_array = $this->isAwesomeCheckoutActive() ? $contents['data_array']['ac'] : $contents['data_array']['non-ac'];
				$message = $message_array[ array_rand( $message_array ) ];
				// cache data for 2 days
				$cache->save( $payload, 'abandonedcart_payload', array( 'abandonedcart' ), 2 * 24 * 60 * 60 );
			} else {
				$message = false;
			}
		} else {
			$contents = json_decode( $payload, true );
			$message_array = $this->isAwesomeCheckoutActive() ? $contents['data_array']['ac'] : $contents['data_array']['non-ac'];
			$message = $message_array[ array_rand( $message_array ) ];
		}

		return $message;
	}

	public function getEmailFooter() {

		$cache = Mage::getSingleton( 'core/cache' );
		$payload = $cache->load( 'abandonedcart_payload' );

		if ( $payload === false ) {

			$payload = file_get_contents( 'http://api.anattadesign.com/abandonedcart/1alpha/fetch/payload' );
			$contents = json_decode( $payload, true );

			if ( $contents['status'] == 'success' ) {
				$email_footer_array = $contents['email_footer'];
				$email_footer = $email_footer_array[ array_rand( $email_footer_array ) ];
				// cache data for 2 days
				$cache->save( $payload, 'abandonedcart_payload', array( 'abandonedcart' ), 2 * 24 * 60 * 60 );
			} else {
				$email_footer = false;
			}
		} else {
			$contents = json_decode( $payload, true );
			$email_footer_array = $contents['email_footer'];
			$email_footer = $email_footer_array[ array_rand( $email_footer_array ) ];
		}

		// fallback email footer message
		if ( $email_footer === false )
			$email_footer = '<p style="text-align:center;padding-bottom:25px;">Sponsored by <a href="https://awesomecheckout.com/?kme=Clicked%20Link&km_Email=MyAB">Awesome Checkout</a> - the highest converting checkout extension for Magento</p>';

		return $email_footer;
	}

	public function trailingslashit( $string ) {
		return $this->untrailingslashit( $string ) . '/';
	}

	public function untrailingslashit( $string ) {
		return rtrim( $string, '/' );
	}

	public function ping() {

		// Get current version of the extension
		$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
		$table = Mage::getSingleton('core/resource')->getTableName( 'core_resource' );
		$stmt = $connection->query( "SELECT version FROM $table WHERE code='anattadesign_abandonedcarts_setup';" );
		$data = $stmt->fetch();
		$version = $data['version'];

		$ping = array(
			'version' => $version,
			'site_name' => Mage::getStoreConfig( 'general/store_information/name' ),
			'url' => 'http://' . str_replace( array( 'http://', 'https://', '/index.php/', '/index.php' ), '', Mage::getUrl() ) // making sure the url is in format - http://domain.com/
		);

		$ping['url'] = Mage::helper( 'anattadesign_abandonedcarts' )->trailingslashit( $ping['url'] );

		// make call
		$client = new Varien_Http_Client( 'http://api.anattadesign.com/abandonedcart/1alpha/collect/ping' );
		$client->setMethod( Varien_Http_Client::POST );
		$client->setParameterPost( 'ping', $ping );

		try {
			$response = $client->request();
			if ( $response->isSuccessful() ) {
				$json_response = json_decode( $response->getBody(), true );
				$ping_success = $json_response['status'] == 'success' ? true : false;
			}
		} catch ( Exception $e ) {
			$ping_success = false;
		}

		if ( $ping_success ) {
			// make sure ping is not rescheduled anymore
			Mage::getModel( 'core/config' )->deleteConfig( 'anattadesign_abandonedcarts_ping_rescheduled' );
		} else {
			// reschedule ping, increment counts if its already scheduled, so that we can see how many times it has failed
			// $ping_rescheduled = Mage::getStoreConfig( 'anattadesign_abandonedcarts_ping_rescheduled' );
			// Fetch directly from database to bypass Magento config cache.
			// Its better to bypass cache and make a sql query in favor of performance, sql query is not gonna run up on frontend side, except when all the cache is refreshed & extension is upgraded
			$table = Mage::getSingleton('core/resource')->getTableName( 'core_config_data' );
			$stmt = $connection->query( "SELECT value FROM $table WHERE path='anattadesign_abandonedcarts_ping_rescheduled' AND scope = 'default' AND scope_id = 0 LIMIT 1;" );
			$data = $stmt->fetch();
			if ( $data === false )
				$ping_rescheduled = 1;
			else
				$ping_rescheduled = intval( $data['value'] ) + 1;

			Mage::getModel( 'core/config' )->saveConfig( 'anattadesign_abandonedcarts_ping_rescheduled', $ping_rescheduled );
		}
	}

	public function getCookieName() {
		return 'anatta_abcart_track';
	}

	public function canTrackUser() {
		$cookie = Mage::getModel( 'core/cookie' )->get( $this->getCookieName() );
		return !( 'no' === trim( $cookie ) );
	}

}