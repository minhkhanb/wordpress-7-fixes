<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');


$headerTitle = esc_html__("Assets Manager", "unlimited-elements-for-elementor");
require HelperUC::getPathTemplate("header");


$objAssets = new UniteCreatorAssetsWork();
$objAssets->initByKey("assets_manager");

?>
<div class="uc-assets-manager-wrapper">

	<?php 
	$objAssets->putHTML();
	?>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
	
		var objAdmin = new UniteCreatorAdmin();
		objAdmin.initAssetsManagerView();
	
	});

</script>
<?php 