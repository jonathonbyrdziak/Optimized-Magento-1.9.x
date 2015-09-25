<?php

class AnattaDesign_AbandonedCarts_Model_Observer {

	public function uponAdminLogin() {
		$this->ping();
		$this->checkLatestVersion();
		$this->stopTrackingUser();
	}

	public function ping() {
		// Instead of using getStoreConfig make a direct sql query to bypass magento cache
		// $is_ping_rescheduled = Mage::getStoreConfig( 'anattadesign_abandonedcarts_ping_rescheduled' );
		$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
		$table = Mage::getSingleton('core/resource')->getTableName( 'core_config_data' );
		$stmt = $connection->query( "SELECT value FROM $table WHERE path='anattadesign_abandonedcarts_ping_rescheduled' AND scope = 'default' AND scope_id = 0 LIMIT 1;" );
		$data = $stmt->fetch();
		// If $data is false, then that means there is no row in the table, and no ping has been rescheduled
		if ( $data !== false )
			Mage::helper( 'anattadesign_abandonedcarts' )->ping();
	}

	public function checkLatestVersion() {
		$contents = file_get_contents( 'http://api.anattadesign.com/abandonedcart/1alpha/status/latestVersion' );
		$latest = json_decode( $contents );

		if ( $latest->status == "success" ) {

			if ( $latest->latestVersion == Mage::getStoreConfig( 'anattadesign_abandonedcart_latest_checked_version' ) )
				return;

			$connection = Mage::getSingleton( 'core/resource' )->getConnection( 'core_read' );
			$table = Mage::getSingleton('core/resource')->getTableName( 'core_resource' );
			$stmt = $connection->query( "SELECT version FROM $table WHERE code='anattadesign_abandonedcarts_setup'" );
			$data = $stmt->fetch();
			$version = $data['version'];

			if ( $latest->latestVersion != $version ) {
				Mage::getModel( 'adminnotification/inbox' )
					->setSeverity( Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE )
					->setTitle( Mage::helper( 'anattadesign_abandonedcarts' )->__( "My Abandoned Cart %s is now available", $latest->latestVersion ) )
					->setDateAdded( gmdate( 'Y-m-d H:i:s' ) )->setUrl( 'http://www.myabandonedcarts.com/' )
					->setDescription( Mage::helper( 'anattadesign_abandonedcarts' )->__( 'Your version of My Abandoned Cart is currently not up-to-date. Please <a href="%s">click here</a> to get the latest version.', 'http://www.myabandonedcarts.com/' ) )
					->save();
				Mage::getModel( 'core/config' )
					->saveConfig( 'anattadesign_abandonedcart_latest_checked_version', $latest->latestVersion );
			}
		}
	}

	public function stopTrackingUser() {
		$time = 60 * 60 * 24 * 7; // set a cookie for one week
		$name = Mage::helper( 'anattadesign_abandonedcarts' )->getCookieName();
		Mage::getModel( 'core/cookie' )->set( $name, 'no', $time, '/', null, null, true );
	}

	public function addJavascriptBlock( $observer ) {

		$controller = $observer->getAction();

		if ( !$controller instanceof Mage_Adminhtml_DashboardController )
			return;

		$layout = $controller->getLayout();
		$block = $layout->createBlock( 'core/text' );
		$block->setText( '<script type="text/javascript">
				var anattadesign_abandonedcarts = {
					url: "' . Mage::helper( 'adminhtml' )->getUrl( 'abandonedcarts/widget/render/' ) . '"
				};
			</script>' );

		$layout->getBlock( 'js' )->append( $block );
	}

	private function _formatStatisics( $stats ) {
		$stat = new stdClass();
		$stat->from = date( 'F j, Y', strtotime( $stats['from'] ) );
		$stat->to = date( 'F j, Y', strtotime( $stats['to'] ) );

		$stats = call_user_func_array( array( Mage::helper( 'anattadesign_abandonedcarts' )->getStatisticsModel(), 'getStatistics' ), $stats );

		$stat->entered = $stats[0]['reached'];
		$stat->completed = $stats[count( $stats ) - 1]['moved'];
		$stat->abandonment = $stat->entered == $stat->completed ? 0 : round( ( $stat->entered - $stat->completed ) * 100 / $stat->entered, 2 );

		$step_names = array(
			'login' => 'Login',
			'billing' => 'Billing',
			'shipping' => 'Shipping',
			'shipping_method' => 'Shipping Method',
			'payment' => 'Payment',
			'review' => 'Review'
		);
		for ( $i = 1; isset( $stats[$i - 1] ); $i++ ) {
			$step = 'step' . $i . '_name';
			$stat->$step = $step_names[$stats[$i - 1]['step']];
			$entered = 'step' . $i . '_entered';
			$stat->$entered = $stats[$i - 1]['reached'];
			$total = 'step' . $i . '_total';
			$stat->$total = $stat->entered ? ( 100 * $stats[$i - 1]['reached'] / $stat->entered ) : 0;
			$abandoned = 'step' . $i . '_abandoned';
			$stat->$abandoned = $stats[$i - 1]['loss'];
		}
		$entered = 'step' . $i . '_entered';
		$stat->$entered = $stats[$i - 2]['moved'];

		return $stat;
	}

	public function weeklyEmailReport() {
		$email_addresses = explode( ',', str_replace( ' ', '', Mage::getStoreConfig( 'abandonedcart/email_report/receiver_emails' ) ) ); // remove spaces before explode, cuz doesn't matter as they are list of emails separated by comma
		// return if we don't have an Email ID set in admin to send an email to.
		if ( empty( $email_addresses[0] ) )
			return;

		$last_sunday = strtotime( 'last sunday' );
		$vars = array();

		for ( $i = 0; $i < 5; $i++ ) {
			// week 1 through 5
			$stats = array(
				'from' => date( 'Y-m-d', strtotime( '-' . ( 1 + $i ) . ' week', $last_sunday ) ),
				'to' => date( 'Y-m-d', $i ? strtotime( '-' . $i . ' week', $last_sunday ) : $last_sunday )
			);
			$stats = $this->_formatStatisics( $stats );
			foreach ( (array) $stats as $key => $value ) {
				$vars['week' . ( 1 + $i ) . '_' . $key] = $value;
			}
		}

		// set differences
		for ( $i = 4; $i; $i-- ) {
			$diff = $vars['week' . $i . '_abandonment'] - $vars['week' . ( 1 + $i ) . '_abandonment'];
			$vars['week' . $i . '_difference'] = ( 0 < $diff ) ? '+ ' . $diff : ( $diff ? '- ' . abs( $diff ) : 0 );
			$vars['week' . $i . '_color'] = ( 0 < $diff ) ? 'c62026' : '0c9748';
		}

		// Get message to display user
		$tip = Mage::helper( 'anattadesign_abandonedcarts' )->getMessage();
		$tip = is_string( $tip ) ? $tip : '';
		$vars['tip'] = $tip;

		// Dashboard url
		$vars['dashboard_url'] = Mage::helper( 'adminhtml' )->getUrl( 'adminhtml/dashboard' );

		// Email footer
		$vars['email_footer'] = Mage::helper( 'anattadesign_abandonedcarts' )->getEmailFooter();

		// Email Subject
		$vars['email_subject'] = 'Abandoned Carts Report - Week of ' . $vars['week1_from'];

		/* @var $email Mage_Core_Model_Email_Template */
		$email = Mage::getModel( 'core/email_template' )->loadDefault( 'anattadesign_abandonedcarts_email_report_template' );
		$email->setDesignConfig( array( 'area' => 'frontend' ) );
		$email->setSenderName( 'My Abandoned Carts' );
		$email->setSenderEmail( 'myabandonedcarts@' . Mage::helper('anattadesign_abandonedcarts')->untrailingslashit( $_SERVER['HTTP_HOST'] ) ); // its not a good idea to use any other domain for sending emails unless you want them to reaching spam/junk folders (use one that's hosted on the same IP)
		$email->setTemplateType( Mage_Core_Model_Template::TYPE_HTML );
		// $email->getProcessedTemplate( $vars ); // this can be used for testing

		foreach ( $email_addresses as $email_id ) {
			if ( !empty( $email_id ) ) {
				$email->send( $email_id, '', $vars );
			}
		}
	}

}