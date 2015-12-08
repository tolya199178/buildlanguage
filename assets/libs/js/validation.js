/*
Created 09/27/09										
Questions/Comments: jorenrapini@gmail.com						
COPYRIGHT NOTICE		
Copyright 2009 Joren Rapini
*/
$(document).ready(function(){
	// Place ID's of all required fields here.
	required = ["name", "email", "message"];
	// If using an ID other than #email or #error then replace it here
	email = $("#email");
	errornotice = $("#error");
	// The text to show up within a field when it is incorrect
	
	$("form").submit(function(){	
		//Validate required fields
		for (i=0;i<required.length;i++) {
			var input = $('#'+required[i]);
			if (input.val() == "") {
				input.addClass("required-field");
				errornotice.fadeIn(750);
			} else if ($("input").length > 0) {
					input.addClass("success-field");
					input.removeClass("required-field");
			}
			else {
			input.removeClass("required-field");
			}
		}
	
		// Validate the e-mail.
		if (!/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(email.val())) {
			email.addClass("required-field");
		} else {
		email.addClass("success-field");
		email.removeClass("required-field");
		}

		
		//if any inputs on the page have the class 'needsfilled' the form will not submit
		if ($(":input").hasClass("required-field")) {
			return false;
		} else {
			errornotice.hide();
			return true;
		}
	});
	
	
});	