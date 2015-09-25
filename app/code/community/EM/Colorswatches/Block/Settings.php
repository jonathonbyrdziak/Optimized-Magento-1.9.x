<?php
class EM_Colorswatches_Block_Settings extends Mage_Core_Block_Template
{
	
	
	public function get_option_swatches() {
		return $this->parse_swatches(Mage::getStoreConfig('colorswatches/image/option_swatches'));
	}
	
	protected function parse_swatches($s) {
		$swatches = array();
		if ($s) {
			if (preg_match_all("/^(.*)\:(.*)=(.*)$/m", $s, $m, PREG_SET_ORDER)) {
				foreach ($m as $_ln)
					$swatches[] = array(
						'key' => trim($_ln[1]),
						'value' => trim($_ln[2]),
						'img' => trim($_ln[3])
					);
			}
		}
		return $swatches;
	}
	
	
}