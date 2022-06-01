<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


require HelperUC::getPathViewObject("addons_view.class");

$pathProviderAddons = GlobalsUC::$pathProvider."views/addons.php";

if(file_exists($pathProviderAddons) == true){
	require_once $pathProviderAddons;
	new UniteCreatorAddonsViewProvider();
}
else{
	new UniteCreatorAddonsView();
}

