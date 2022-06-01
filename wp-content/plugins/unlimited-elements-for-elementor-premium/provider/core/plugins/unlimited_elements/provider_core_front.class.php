<?php

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


class UniteProviderCoreFrontUC_Elementor extends UniteProviderFrontUC{
	
			
	
	/**
	 *
	 * the constructor
	 */
	public function __construct(){
		
		HelperProviderCoreUC_EL::globalInit();
		
		/*
		$disableFilters = HelperProviderCoreUC_EL::getGeneralSetting("disable_autop_filters");
		$disableFilters = UniteFunctionsUC::strToBool($disableFilters);
		
		if($disableFilters == true)
			$this->disableWpFilters();
		*/
		
		parent::__construct();
						
	}
	
	
}
