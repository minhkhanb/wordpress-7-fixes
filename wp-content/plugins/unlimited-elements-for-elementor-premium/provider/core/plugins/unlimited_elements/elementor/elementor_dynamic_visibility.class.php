<?php

defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorDynamicVisibility{
	
	const PREFIX = "uc_dynamic_visibility_";
	private $arrSettings;
	private $arrHiddenIDs = array();
	
	
	/**
	 * get setting by key
	 */
	private function getSetting($key){
				
		$value =  UniteFunctionsUC::getVal($this->arrSettings, self::PREFIX.$key);
		
		return($value);
	}
	
	/**
	 * is hide by archive term ids
	 */
	private function isHideElement_archiveTermIDs(){
		
		$termIDs = $this->getSetting("term_ids");
		
		if(empty($termIDs))
			return(false);
		
		$termID = $termIDs;	//temporary
			
		if(is_archive() == false)
			return(true);

		$objTerm = get_queried_object();
		
		if($objTerm instanceof WP_Term == false)
			return(true);
		
		if(isset($objTerm->term_id) == false)
			return(true);

		//check current term
		
		$currentTermID = $objTerm->term_id;
		
		$arrCurrentIDs = array($currentTermID);
		
		$parents = get_ancestors( $currentTermID, $objTerm->taxonomy, 'taxonomy' );
		if(!empty($parents))
			$arrCurrentIDs = array_merge($arrCurrentIDs, $parents);
		
		
		
		dmp("check by ids, write function to get all parent ids");
		dmp($arrCurrentIDs);
		exit();
		
		
		//show if terms match
		if($currentTermID == $termID)
			return(false);
		
		//if no parent term - don't match, hide
		if(isset($objTerm->parent) == false)
			return(true);
					
		dmp($objTerm);
		
			
		dmp("check all parents");
		
		//should check all in the conditions	
		return(false);
	}
	
	
	/**
	 * check if hide element or not
	 */
	private function isHideElement($type){
		
		switch($type){
			case "archive_terms":
				
				$isHide = $this->isHideElement_archiveTermIDs();
				
				return($isHide);
			break;
			default:
				return(false);
			break;
		}
		
		
		return(false);
	}
	
	
	/**
	 * on before render
	 */
	public function onBeforeRenderElement($element){
		
		$this->arrSettings = $element->get_settings_for_display();		
		
		$type = $this->getSetting("type");
		
		if($type == "none" || empty($type))
			return(true);
		
		$isHide = $this->isHideElement($type);

		if($isHide == false)
			return(false);
		
		$elementID = $element->get_id();

		$this->arrHiddenIDs[$elementID] = true;

		//start hiding
		
		ob_start();
	}
	
	
	/**
	 * on after render element
	 */
	public function onAfterRenderElement($element){
		
		$elementID = $element->get_id();
		
		$isHidden = isset($this->arrHiddenIDs[$elementID]);
		
		if($isHidden == false)
			return(true);

		//finish hiding
		
		ob_end_clean();
	}
	
	
	/**
	 * add visibility controls section
	 */
	public function addVisibilityControls($objControls){
		
		$objControls->start_controls_section(
			'section_visibility_uc',
			[
				'label' => __( 'Visibility Conditions', 'unlimited_elements' ),
				'tab' => "advanced",
			]
		);
		
		//condition type
		
		$prefix = self::PREFIX;
		
		$objControls->add_control(
			$prefix.'type',
				array(
					'label' => __( 'Visibility By', 'unlimited-elements-for-elementor' ),
					'type' => \Elementor\Controls_Manager::SELECT,
					'default' => 'none',
					'options' => array(
						'none' => __( 'None', 'unlimited-elements-for-elementor' ),
						'archive_terms'  => __( 'Archive Terms', 'unlimited-elements-for-elementor' )
					),
				)
	      );
		
		$objControls->add_control(
			$prefix.'term_ids',
			[
				'label' => __( 'Term IDs', 'unlimited-elements-for-elementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => ""
			]
		);
		
		
        $objControls->end_controls_section();
	}
	
	
	
}