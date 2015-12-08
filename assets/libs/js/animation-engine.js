// ===================== SETTINGS ========================//
// Screen width in pixels in which the animations will be disabled 
// If you want them fully disabled set to a very high number like 10000 
// If you want them activate on all screen sizes, set the number to 0 
var scrollDisablePoint = 1024; // Scrolling Animations Disable Point
// ================== END SETTINGS =======================//


// ====================================================== //
// ================= SCROLLING ANIMATIONS =================== //
// ====================================================== //
(function($) { "use strict";

if ($(window).width() < scrollDisablePoint) {
$('.ae').addClass('visible')
}
else {

// DEFAULTS SETTINGS
var animationDelayDefault = '0'; // Default delay '0' = No Delay
var animationSpeedDefault = '1000'; // Default '1000' = 1 second
var animationOffsetDefault = '90%'; // Default '90%' = As element enters viewport
var animationTypeDefault = 'fadeIn'; // Default Animation Type = 'fadeIn'
// SETS ALL CRE-ANIMATE ELEMENTS TO INTIALLY HAVE OPACITY OF ZERO
$(document).ready(function() {
$('html, body').css('overflow-x', 'hidden');
  });

// ONSCROLL ANIMATION OF ELEMENTS
  $(window).on('scroll load', function() {
    $('.ae').each(function() {

// CALCULATES THE TRIGGER POINT USING THE OFFSET
      var animationOffset = $(this).data('offset');
	  	if (animationOffset == null) {
		animationOffset = animationOffsetDefault;
		}
      var parsePercent = parseFloat(animationOffset);
      var decimal = parsePercent / 100;
      var triggerpoint = $(window).height() * decimal + $(window).scrollTop(); // Call point in Viewport: viewport height * decimal(%) + pixels to top of window
      var element = $(this).offset().top;
	  
// ANIMATION OPTIONS ARE CREATED
	 var animationType = $(this).data('animation');
	   	if (animationType == null) {
		animationType = animationTypeDefault;
		}
		var animationDelay = $(this).data('delay');
		if (animationDelay == null) {
		animationDelay = animationDelayDefault;
		}
		var animationSpeed = $(this).data('speed');
		if (animationSpeed == null) {
		animationSpeed = animationSpeedDefault;
		}
		var animationOptions = {
        'animation-delay': animationDelay + 'ms',
        '-webkit-animation-delay': animationDelay + 'ms',
		'animation-duration': animationSpeed + 'ms',
		'-webkit-animation-duration': animationSpeed + 'ms',
      };
		
// TRIGGERS THE ANIMATIONS
      if (element < triggerpoint) {
          $(this).addClass('visible').addClass(animationType).css(animationOptions);
      }
    });
  });
  }
}(jQuery));
// ====================================================== //
// ================= LOOPING ANIMATIONS =================== //
// ====================================================== //	


// ====================================================== //
// ==================== DEMO ANIMATIONS =================== //
// ====================================================== //
(function($) { "use strict";
$(document).ready(function(){
  $('button.animation-demo').click(function(){
  var animationType = $(this).data('animation');
   $('#animate-me').removeClass();
  $('#animate-me').addClass('ae-demo').addClass(animationType);
  });
});
}(jQuery));