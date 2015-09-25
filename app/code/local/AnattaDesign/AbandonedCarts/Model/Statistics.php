<?php

class AnattaDesign_AbandonedCarts_Model_Statistics extends Mage_Core_Model_Abstract {

	protected function _construct() {
		$this->_init( 'anattadesign_abandonedcarts/statistics' );
	}

	/**
	 * Save moved to step for quote.
	 *
	 * @param string $step
	 * @param int $quoteId
	 *
	 * @return AnattaDesign_AbandonedCarts_Model_Statistics
	 */
	public function saveStepMoved( $step, $quoteId ) {
		return $this->_saveStepData( array( 'step' => $step, 'quoteId' => $quoteId, 'moved' => 1 ) );
	}

	/**
	 * Save reached step for quote.
	 *
	 * @param string $step
	 * @param int $quoteId
	 *
	 * @return AnattaDesign_AbandonedCarts_Model_Statistics
	 */
	public function saveStepReached( $step, $quoteId ) {
		$data = array( 'step' => $step, 'quoteId' => $quoteId, 'reached' => 1, 'year' => date( 'Y' ), 'month' => date( 'n' ), 'date' => date( 'Y-m-d h:i:s' ) );
		return $this->_saveStepData( $data );
	}

	/**
	 * delete all records of a particular quote ID
	 *
	 * @param int $quoteId
	 *
	 * @throws Exception
	 * @return AnattaDesign_AbandonedCarts_Model_Statistics
	 */
	public function deleteByQuoteId( $quoteId ) {
		$quoteId = abs( intval( $quoteId ) );
		if ( !$quoteId )
			throw new Exception( 'Quote ID is required & should be greater than 0' );

		$collection = $this->getCollection();
		$collection->addFieldToFilter( 'sales_flat_quote_id', $quoteId );
		$collection->load();

		if ( count( $collection ) ) {
			foreach ( $collection as $model ) {
				$model->delete();
			}
		} else {
			return $this->delete();
		}

		return $this;
	}

	/**
	 * Save step data.
	 * Step and quote id are required.
	 *
	 * @param array $data
	 *
	 * @throws Exception
	 * @return AnattaDesign_AbandonedCarts_Model_Statistics
	 */
	protected function _saveStepData( array $data ) {
		if ( !Mage::helper( 'anattadesign_abandonedcarts' )->canTrackUser() ) {
			return false;
		}

		if ( !array_key_exists( 'step', $data ) ) {
			throw new Exception( 'Step key is required' );
		}

		if ( !$data['step'] )
			return;

		// Prepare data
		$data = array_merge( $data, array( 'sales_flat_quote_id' => $data['quoteId'] ) );

		// Get existing statistics for this quote and step
		$collection = $this->getCollection();
		$collection->addFieldToFilter( 'sales_flat_quote_id', $data['quoteId'] );
		$collection->addFieldToFilter( 'step', $data['step'] );

		// TODO: Add date field to filter < 1 month

		$collection->addOrder( 'statistics_id' );
		$collection->load();

		if ( count( $collection ) ) {
			return $collection->fetchItem()->addData( $data )->save();
		} else {
			return $this->addData( $data )->save();
		}
	}

	public function getStatistics( $from, $to = null ) {
		// Select table name where data is stored
		if (Mage::helper('anattadesign_abandonedcarts')->isAwesomeCheckoutActive()) {
			$steps = array(
				0 => array(
					'step' => 'shipping'
				),
				1 => array(
					'step' => 'shipping_method'
				),
				2 => array(
					'step' => 'payment'
				),
				3 => array(
					'step' => 'review'
				)
			);

			$table = Mage::getSingleton('core/resource')->getTableName( 'anattadesign_abandonedcarts/statistics' );
		} else if ( Mage::helper( 'anattadesign_abandonedcarts' )->isOneStepCheckoutActive() ) {

			$steps = array(
				0 => array(
					'step' => 'review'
				)
			);

			$table = Mage::getSingleton( 'core/resource' )->getTableName( 'anattadesign_abandonedcarts/osstatistics' );
		} else {
			$steps = array(
				0 => array(
					'step' => 'login'
				),
				1 => array(
					'step' => 'billing'
				),
				2 => array(
					'step' => 'shipping'
				),
				3 => array(
					'step' => 'shipping_method'
				),
				4 => array(
					'step' => 'payment'
				),
				5 => array(
					'step' => 'review'
				)
			);

			$table = Mage::getSingleton('core/resource')->getTableName( 'anattadesign_abandonedcarts/opstatistics' );
		}

		// Add reached & moved values in array
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		if(!$to)
			$query = "SELECT SUM(reached) as `reached`, SUM(moved) as `moved`, `step` FROM `$table` WHERE DATE(date) > '$from' GROUP BY `step`;";
		else
			$query = "SELECT SUM(reached) as `reached`, SUM(moved) as `moved`, `step` FROM `$table` WHERE DATE(date) > '$from' AND DATE(date) < '$to' GROUP BY `step`;";
		$resource = $connection->query($query);
		$queried_data = $resource->fetchAll();

		$data = array();
		foreach($queried_data as $d)
			$data[$d['step']] = $d;

		foreach ($steps as &$step) {
			$step['reached'] = (isset($data[$step['step']]) && isset($data[$step['step']]['reached'])) ? intval($data[$step['step']]['reached']) : 0;
			$step['moved'] = (isset($data[$step['step']]) && isset($data[$step['step']]['moved'])) ? intval($data[$step['step']]['moved']) : 0;
			$step['loss'] = (0 == $step['reached'] && ($step['reached'] == $step['moved'])) ? 0 : (($step['reached'] - $step['moved']) * 100 / $step['reached']);
		}
		unset($step);

		// adjustments to data
		if (!Mage::helper('anattadesign_abandonedcarts')->isAwesomeCheckoutActive() && !Mage::helper( 'anattadesign_abandonedcarts' )->isOneStepCheckoutActive()) {
			// This is needed as a user already logged in will never reach the login step
			// Here we add the appropriate padding (for the sake of the funnel)
			$diff = $steps[1]['reached'] - $steps[0]['moved'];
			$steps[0]['reached'] += $diff;
			$steps[0]['moved'] += $diff;
			// make sure shipping step which is optional only depicts the loss, so add padding here (for the sake of funnel)
			$diff = $steps[1]['moved'] - $steps[2]['reached'];
			$steps[2]['reached'] += $diff;
			$steps[2]['moved'] += $diff;
		}

		// let's match the moved of each step to the reached of next step, this will show decent output even in case of
		// manual data changes via phpmyadmin. This won't affect anything if this module doesn't have any conflicts
		for($i = count($steps) - 1; $i >= 0; $i--) {
			// making sure that moved is never higher than reached
			if($steps[$i]['moved'] > $steps[$i]['reached'])
				$steps[$i]['reached'] = $steps[$i]['moved'];

			if($i)
				$steps[$i-1]['moved'] = $steps[$i]['reached'] = max($steps[$i-1]['moved'], $steps[$i]['reached']);
		}

		return $steps;
	}

}
