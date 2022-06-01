<?php
/**
 * @package Unlimited Elements
 * @author UniteCMS.net
 * @copyright (C) 2017 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
defined('UNLIMITED_ELEMENTS_INC') or die('Restricted access');

class UniteCreatorFiltersProcess{

	private static $filters = null;
	private static $isFilesAdded = false;
	
	
	/**
	 * get request array
	 */
	private function getArrRequest(){
		
		$request = $_GET;
		if(!empty($_POST))
			$request = array_merge($request, $_POST);
		
		return($request);
	}
	
	/**
	 * add single category filter
	 */
	private function addSingleCategoryFilter($slug){
		
		$slug = trim($slug);
		
		$term = get_term_by("slug", $slug, "category");
		
		$termID = "0";
		if(!empty($term))
			$termID = $term->term_id;
		
		self::$filters["category"] = $termID;
		
	}
	
	
	/**
	 * get filters arguments
	 */
	public function getRequestFilters(){
		
		if(self::$filters !== null)
			return(self::$filters);
		
		self::$filters = array();
			
		$request = $this->getArrRequest();
		
		self::$filters = array();
		
		foreach($request as $key=>$value){
			
			switch($key){
				case "filter-category":
					
					$this->addSingleCategoryFilter($value);
					
				break;
			}
				
		}
		
		
		return(self::$filters);
	}
	
	
	/**
	 * get fitler url from the given slugs
	 */
	private function getUrlFilter_term($term, $taxonomyName){
		
		$key = "filter-term";
		
		$taxPrefix = $taxonomyName."--";
		
		if($taxonomyName == "category"){
			$taxPrefix = "";
			$key="filter-category";
		}
				
		$slug = $term->slug;

		$value = $taxPrefix.$slug;
		
		$urlAddition = "{$key}=".urlencode($value);
				
		$urlCurrent = GlobalsUC::$current_page_url;
				
		$url = UniteFunctionsUC::addUrlParams($urlCurrent, $urlAddition);
		
		return($url);
	}
	
	/**
	 * check if the term is acrive
	 */
	private function isTermActive($term, $arrActiveFilters = null){
		
		if(empty($term))
			return(false);
		
		if($arrActiveFilters === null)
			$arrActiveFilters = $this->getRequestFilters();
		
		if(empty($arrActiveFilters))
			return(false);
			
		$taxonomy = $term->taxonomy;
		
		$selectedTermID = UniteFunctionsUC::getVal($arrActiveFilters, $taxonomy);
		
		if(empty($selectedTermID))
			return(false);
			
		if($selectedTermID === $term->term_id)
			return(true);
			
		return(false);
	}
	
	/**
	 * put ajax scripts
	 */
	private function putScriptsAjax(){
		
		?>
		
		<script>

/**
 * parse response html - get the body
 */
function parseResponseHtml(html){
	
	var objParsed = jQuery.parseHTML(html);
	
	var objBody = [];
	
	jQuery.each(objParsed, function(index){

		var item = objParsed[index];
		var type = typeof item;

		var tagName = item.tagName;
		if(!tagName)
			return(true);

		tagName = tagName.toLowerCase();

		switch(tagName){
			case "header":
			case "main":
			case "footer":
			break;
			default:
				return(true);
			break;
		}

		objBody.push(jQuery(item));
	});
	
	return(objBody);
}
		
/**
 * operate html response from ajax
 */
function operateHTMLResponse(html){

	var objBody = parseResponseHtml(html);

	var objPostList = jQuery(".uc_post_list");

	var objNewPostList = objBody[1].find(".uc_post_list");

	objPostList.html(objNewPostList.html());
	
	//var objBody = objDom.find("body");
	//trace(objDom);
	//trace(objBody);
	
}
		
/**
* on ajax filter click
*/
function onFilterAjaxClick(event){

	event.preventDefault();
	
	var objFilter = jQuery(this);

	var link = objFilter.prop("href");

	var ajaxSettings = {
		dataType:"html",
		complete:function(response){
			var responseText = response.responseText;
			if(responseText)
				operateHTMLResponse(responseText);
		}
	};
	
	jQuery.ajax(link, ajaxSettings);
		
}

jQuery(document).ready(function(){

	var objFilters = jQuery(".uc-filters .uc-filter-ajax");

	objFilters.click(onFilterAjaxClick);
	
});
		
		</script>
		
		<?php 
	}
	
	/**
	 * put filters by tabs
	 */
	public function putFiltersTabs(){
		
		$arrActiveFilters = self::getRequestFilters();
		
		$taxonomy = "category";
		
		$terms = get_terms($taxonomy);

		$isAjax = true;
		
		?>
		
		<style>
		.uc-filters{
			list-style:none;
			margin:0px;
			padding:0px;
			display:flex;
			box-sizing:border-box;
			margin-top:20px;
			margin-bottom:20px;
		}
		.uc-filters li{
			margin:0px;
			padding:0px;
		}
		.uc-filters li a{
			padding:10px;
			border:1px solid black;
		}
		.uc-filters li.active a{
			background-color:gold;
		}
		</style>
		
		<ul class="uc-filters">
			<?php 
				$isFirst = true;
				foreach($terms as $term):
					
					$isTermActive = $this->isTermActive($term, $arrActiveFilters);
					
					$classActive = "";
					
					$termID = $term->term_id;
					$name = $term->name;
					$slug = $term->slug;
										
					$urlFilter = $this->getUrlFilter_term($term, $taxonomy);
					
					$htmlAttribAddition = "";
					if($isAjax == true){
						$htmlAttribAddition = 'class="uc-filter-ajax"';
					}
					
					if($isTermActive == true)
						$classActive = " active";
					
					$isFirst = false;
			?>
			<li class="<?php echo $classActive?>">
				<a href="<?php echo $urlFilter?>" <?php echo $htmlAttribAddition?>><?php echo $name?></a>
			</li>
			
			<?php endforeach?>
		</ul>
		
		<?php 

		if($isAjax == true)
			$this->putScriptsAjax();
		
	}
	
	
	/**
	 * include the filters js files
	 */
	private function includeJSFiles(){
		
		if(self::$isFilesAdded == true)
			return(false);
		
		$urlFiltersJS = GlobalsUC::$url_assets_libraries."filters/ue_filters.js";
		HelperUC::addScriptAbsoluteUrl($urlFiltersJS, "ue_filters");		
		
		
		self::$isFilesAdded = true;
	}
	
	
	/**
	 * put checkbox filters test
	 */
	public function putCheckboxFiltersTest($data){
		
		$this->includeJSFiles();
		
		$taxonomy = UniteFunctionsUC::getVal($data, "taxonomy", "category");
		
		$terms = get_terms($taxonomy);
		
		if(empty($terms))
			return(false);
		
		$html = "";
		foreach($terms as $term){
			
			$arrTerm = (array)$term;
			
			$termID = UniteFunctionsUC::getVal($arrTerm, "term_id");
			$name = UniteFunctionsUC::getVal($arrTerm, "name");
			$slug = UniteFunctionsUC::getVal($arrTerm, "slug");
			$count = UniteFunctionsUC::getVal($arrTerm, "count");
			
			$slug = htmlspecialchars($slug);
			
			$checkboxName = "ucfilter_term__{$taxonomy}--{$slug}";
			
			$html .= "<label class='ucfilters-label-checkbox'>$name ($count)<input type='checkbox' class='uc-listing-filter uc-filter-checkbox' name='{$checkboxName}' data-type='term' data-taxonomy='{$taxonomy}' data-term='{$slug}'></label>";
		}
		
		echo $html;
	}
	
	/**
	 * add widget variables
	 * uc_listing_addclass, uc_listing_attributes
	 *
	 */
	public function addWidgetFilterableVariables($data){
		
		$urlBase = UniteFunctionsUC::getBaseUrl(GlobalsUC::$current_page_url);
		
		$arrData = array();
		$arrData["urlbase"] = $urlBase;

		
		$strAttributes = UniteFunctionsUC::jsonEncodeForHtmlData($arrData,"ucfilters");
		
		$data["uc_listing_attributes"] = $strAttributes;
		$data["uc_listing_addclass"] = " uc-filterable-listing";
		
		return($data);
	}
	
	/**
	 * init elementor ajax
	 */
	public function initElementorAjaxActions(){
		
		
		dmp("init ajax");
		exit();
		
	}
	
}