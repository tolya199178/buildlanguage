jQuery(document).ready(function($) {
	
	// Header Changer
	$("#light-header" ).click(function(){
			$("#header-switch").attr("href", "css/headers/light.css" );
			$("#logo").attr("src", "img/logo-dark.png" );
			return false;
		});
		$("#dark-header" ).click(function(){
			$("#header-switch").attr("href", "css/headers/dark.css" );
			$("#logo").attr("src", "img/logo.png" );
			return false;
		});
		// Color Changer
		$("#default" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/default.css" );
			return false;
		});
		$("#metro" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/metro.css" );
			return false;
		});
		$("#midnight" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/midnight.css" );
			return false;
		});
		$("#tangerine" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/tangerine.css" );
			return false;
		});
		$("#lime" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/lime.css" );
			return false;
		});
		$("#aqua" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/aqua.css" );
			return false;
		});
		$("#purple" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/purple.css" );
			return false;
		});
		$("#gold" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/gold.css" );
			return false;
		});
		$("#fire" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/fire.css" );
			return false;
		});
		$("#pink" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/pink.css" );
			return false;
		});
			$("#marine" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/marine.css" );
			return false;
		});
			$("#green" ).click(function(){
			$("#style-switch").attr("href", "css/color-schemes/green.css" );
			return false;
		});
		});