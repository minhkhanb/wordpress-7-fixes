<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


$operations = new ProviderOperationsUC();

$data = array();
$data["term"] = "a";
$data["q"] = "a";
$data["_type"] = "query";


//$response = $operations->getTermsListForSelectFromData($data);

//dmp($response);
exit();

//$font = new UniteFontManagerUC();
//$font->fetchIcons();
/*
$webAPI = new UniteCreatorWebAPI();

dmp("update catalog");

$response = $webAPI->checkUpdateCatalog();

dmp("update catalog response");

dmp($response);

$lastAPIData = $webAPI->getLastAPICallData();
$arrCatalog = $webAPI->getCatalogData();

//$arrNames = $webAPI->getArrAddonNames($arrCatalog["catalog"]);

dmp($arrCatalog);
*/
/*

dmp($lastAPIData);
dmp($arrCatalog);
exit();

*/