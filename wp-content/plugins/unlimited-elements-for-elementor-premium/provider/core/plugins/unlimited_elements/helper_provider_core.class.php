<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net / Valiano
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class HelperProviderCoreUC_EL{
	
   	
	public static $pathCore;
	public static $urlCore;
	public static $filepathGeneralSettings;
	public static $operations;
	public static $arrWidgetNames;
	public static $arrImages;
	public static $arrGlobalColors;
	
	
	
	/**
	 * register post types of elementor library
	 */
	public static function registerPostType_UnlimitedLibrary(){
		
		$arrLabels = array(
						'name' => __( 'Unlimited Elements Library' ,"unlimited-elements-for-elementor"),
						'singular_name' => __( 'Unlimited Elements Library' ,"unlimited-elements-for-elementor"),
						'add_new_item' => __( 'Add New Template' ,"unlimited-elements-for-elementor"),
						'edit_item' => __( 'Edit Template' ,"unlimited-elements-for-elementor"),
						'new_item' => __( 'New Template' ,"unlimited-elements-for-elementor"),
						'view_item' => __( 'View Template' ,"unlimited-elements-for-elementor"),
						'view_items' => __( 'View Template' ,"unlimited-elements-for-elementor"),
						'search_items' => __( 'Search Template' ,"unlimited-elements-for-elementor"),
						'not_found' => __( 'No Template Found' ,"unlimited-elements-for-elementor"),
						'not_found_in_trash' => __( 'No Template found in trash' ,"unlimited-elements-for-elementor"),
						'all_items' => __( 'All Templates' ,"unlimited-elements-for-elementor")
				);
		
		$arrSupports = array(
			"title",
		//	"editor",
			"author",
			"thumbnail",
			"revisions",
			"page-attributes",
		);
		
		$arrPostType =	array(
							'labels' => $arrLabels,
							'public' => true,
							'rewrite' => false,
							
							'show_ui' => true,
							'show_in_menu' => true,		//set to true for show
							'show_in_nav_menus' => true,	//set to true for show
		
							'exclude_from_search' => true,
							'capability_type' => 'post',
							'hierarchical' => true,
							'description' => __("Unlimited Elements Template", "unlimited-elements-for-elementor"),
							'supports' => $arrSupports,
							//'show_in_admin_bar' => true		
					);
		
		
		register_post_type( GlobalsUnlimitedElements::POSTTYPE_UNLIMITED_ELEMENS_LIBRARY, $arrPostType);
		
		add_post_type_support( GlobalsUnlimitedElements::POSTTYPE_UNLIMITED_ELEMENS_LIBRARY, 'elementor' );
		
	}
	
	
	/**
	 * process param value by type
	 */
	public static function processParamValueByType($value, $type, $param){
		    		
    		switch($type){
    			
    			case UniteCreatorDialogParam::PARAM_RADIOBOOLEAN:
    			    
    				$trueValue = UniteFunctionsUC::getVal($param, "true_value");
    				$falseValue = UniteFunctionsUC::getVal($param, "false_value");
					
    				switch($value){
    					case $trueValue:		//don't change true or false
    					case $falseValue:
    					break;
    					case "yes":
    						$value = $trueValue;
    					break;
    					default:
    						$value = $falseValue;
    					break;
    				}
    				
    				
    			break;
    		}
    	
    		
		return($value);
	}
	
	
	/**
	 * get general settings values
	 */
	public static function getGeneralSettingsValues(){
		
		$arrValues = self::$operations->getCustomSettingsObjectValues(self::$filepathGeneralSettings, GlobalsUnlimitedElements::GENERAL_SETTINGS_KEY);
		
		return($arrValues);
	}
	
	
	/**
	 * get general setting value
	 */
	public static function getGeneralSetting($name){
		
		$arrSettings = self::getGeneralSettingsValues();
		if(isset($arrSettings[$name]) == false)
			UniteFunctionsUC::throwError("Setting: $name does not exists in unlimited elements");
		
		$value = $arrSettings[$name];
		
		return($value);
	}
		
	
	/**
	 * register widget by it's name for outside uses
	 */
	public static function registerWidgetByName($name){
		
		$isAlphaNumeric = UniteFunctionsUC::isAlphaNumeric($name);
		if($isAlphaNumeric == false)
			return(false);
			
		$className = "UCAddon_".$name;
        
		if(class_exists($className) == true)
			return(false);
        
		$code = "class {$className} extends UniteCreatorElementorWidget{}";
		eval($code);
        
		$widget = new $className();
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type($widget);
		
	}
		
	
	/**
	 * get imported elementor templates
	 * if param given. stop after this param reached
	 */
	public static function getImportedElementorTemplates($paramName = null){
		
		$args = array();
		$args["post_type"] = GlobalsUnlimitedElements::POSTTYPE_ELEMENTOR_LIBRARY;
		$args["posts_per_page"] = 1000;
		$args["meta_key"] = GlobalsUnlimitedElements::META_TEMPLATE_SOURCE;
		$args["meta_value"] = "unlimited";
		
		$arrPosts = get_posts($args);
		
		$arrImportedTemplates = array();
		
		foreach($arrPosts as $post){
						
			$postID = $post->ID;
			
			$sourceName = get_post_meta($postID, GlobalsUnlimitedElements::META_TEMPLATE_SOURCE_NAME, true);
			
			if(empty($sourceName))
				continue;
			
			$arrImportedTemplates[$sourceName] = $postID;
			
			if($sourceName == $paramName)
				break;
		}
		
		return($arrImportedTemplates);
	}
	
	
	/**
	 * get imported elementor template by name
	 */
	public static function getImportedElementorTemplateID($name){
		
		$arrTemplates = self::getImportedElementorTemplates($name);
				
		$templateID = UniteFunctionsUC::getVal($arrTemplates, $name);
				
		if(empty($templateID))
			return(null);
		
		return($templateID);
	}
	
	/**
	 * get images array from elementor content
	 */
	public static function getImagesFromElementorContent($arrContent){
		
		if(is_array($arrContent) == true){
			//foreach($arrContent)
		}
		
		dmp("get imges");
		dmp($arrContent);
		exit();
		
	}
	
	/**
	 * get addons list from elementor content
	 */
	private static function getWidgetNamesFromElementorContent_iterate($arrContent){
		
		if(is_array($arrContent) == false)
			return(false);
		
		foreach($arrContent as $item){
			
			if(is_array($item) == false)
				continue;
			
			$type = UniteFunctionsUC::getVal($item, "elType");
						
			if($type == "widget"){
				
				$widgetName = UniteFunctionsUC::getVal($item, "widgetType");
				
				if(strpos($widgetName, "ucaddon_") !== false){
										
					$addonName = str_replace("ucaddon_", "", $widgetName);
					
					self::$arrWidgetNames[$addonName] = true;
				}
				
			}
			
			self::getWidgetNamesFromElementorContent_iterate($item);
				
		}
		
	}
	
	/**
	 * get addons names from elementor content
	 */
	public static function getWidgetNamesFromElementorContent($arrContent){
		
		self::$arrWidgetNames = array();
		
		if(is_array($arrContent) == false)
			return(self::$arrWidgetNames);
		
		self::getWidgetNamesFromElementorContent_iterate($arrContent);
				
		return(self::$arrWidgetNames);
	}
	
	/**
	 * get hover animation classes
	 */
	public static function getHoverAnimationClasses($addNotChosen = false){
		
		$arrAnimations = \Elementor\Control_Hover_Animation::get_animations();
		
		$arrAnimationsNew = array();
		
		if($addNotChosen == true)
			$arrAnimationsNew[""] = __("Not Chosen","unlimited-elements-for-elementor");
		
		foreach($arrAnimations as $key=>$value)
			$arrAnimationsNew["elementor-animation-".$key] = $value;
		
		return($arrAnimationsNew);
	}
	
	
	/**
	 * global init
	 */
	public static function globalInit(){
		
		self::$operations = new UCOperations();
				
		//set path and url
		self::$pathCore = dirname(__FILE__)."/";
		self::$urlCore = HelperUC::pathToFullUrl(self::$pathCore);
				
		self::$filepathGeneralSettings = self::$pathCore."settings/general_settings_el.xml";
		
		GlobalsProviderUC::$pluginName = "unlimited_elementor";
		
		GlobalsUC::$currentPluginTitle = GlobalsUnlimitedElements::PLUGIN_TITLE;
		
		//add_action("init", array("HelperProviderCoreUC_EL", "onInitAction"));
		
	}
		
	
}