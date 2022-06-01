
function UEListingFilters(){
	
	var g_objFilters, g_objListing, g_listingData, g_urlBase;
	 
	var g_types = {
		CHECKBOX:"checkbox"
	};
	
	
	/**
	 * console log some string
	 */
	function trace(str){
		console.log(str);
	}
	
	/**
	 * get object property
	 */
	function getVal(obj, name, defaultValue){
		
		if(!defaultValue)
			var defaultValue = "";
		
		var val = "";
		
		if(!obj || typeof obj != "object")
			val = defaultValue;
		else if(obj.hasOwnProperty(name) == false){
			val = defaultValue;
		}else{
			val = obj[name];			
		}
		
		return(val);
	}
	
	
	/**
	 * get filter type
	 */
	function getFilterType(objFilter){
		
		if(objFilter.is(":checkbox"))
			return(g_types.CHECKBOX);
		
		return(null);
	}
	
	
	/**
	 * clear filter
	 */
	function clearFilter(objFilter){
		
		var type = getFilterType(objFilter);
		
		switch(type){
			case g_types.CHECKBOX:
				objFilter.prop("checked", false);
			break;
		}
		
	}
	
	
	/**
	 * clear filters
	 */
	function clearFilters(){
		
		jQuery.each(g_objFilters,function(index, filter){
			var objFilter = jQuery(filter);
			clearFilter(objFilter);
		});
		
	}
	
	/**
	 * build url query from the filters
	 */
	function buildUrlQuery(){
		
		//product_cat
		//shoes, dress
		
		//product_cat~shoes,dress;cat~123,43;
		//ucfilters=product_cat~shoes,dress;cat~123,43;
		
		$query = "query";
		
		return($query);
	}
	
	/**
	 * on filters change
	 */
	function onFiltersChange(){
		
		var query = buildUrlQuery();
		
		trace(query);
		
	}
	
	
	/**
	 * init events
	 */
	function initEvents(){
		
		var objCheckboxes = g_objFilters.filter("input[type=checkbox]");
		
		objCheckboxes.on("click", onFiltersChange);
		
	}
	
	
	/**
	 * init
	 */
	function init(){
		
		g_objFilters = jQuery(".uc-listing-filter");
		
		if(g_objFilters.length == 0){
			return(false);
		}
		
		//init the listing
		
		g_objListing = jQuery(".uc-filterable-listing");
		
		if(g_objListing.length == 0){
			trace("fitlers not loaded, no listing available on page");
			return(false);
		}
		
		//get first listing
		if(g_objListing.length > 1)
			g_objListing = jQuery(g_objListing[0]);
		
		g_listingData = g_objListing.data("ucfilters");
		if(!g_listingData)
			g_listingData = {};
		
		g_urlBase = getVal(g_listingData, "urlbase");
		
		if(!g_urlBase){
			trace("ue filters error - base url not inited");
			return(false);
		}
		
		clearFilters();
		
		initEvents();
		
	}
	
	
	/**
	 * init the class
	 */
	function construct(){
		
		if(!jQuery){
			trace("Filters not loaded, jQuery not loaded");
			return(false);
		}
				
		jQuery("document").ready(init);
		
	}
	
	construct();
}

new UEListingFilters();

