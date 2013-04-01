/*
	check
*/
$().ready(function() {
	$('a.media').media( { width: 230, height: 35 } );

	//-----------------------------------dialog for system
	$(".showRegistration").click(function(){loadpopDialog($(this).attr("func"));});

	//-----------------------------------TOOLTIP
    // default settings
    $.Tooltip.defaults = $.extend( $.Tooltip.defaults, {
        delay      : 1,
        showURL    : false,
        showBody   : " - ",
        // left       : -100,
        track      : true
    });

    // initialize
    $('.tipmsg').Tooltip();

	// color saine for button
	$("input[@id='btn1']").mouseover(function(){
		$(this).css('background','#0C8AD6');
	});
	$("input[@id='btn1']").mouseout(function(){
		$(this).css('background','#37678D');
	});
	$("input[@id='btn2']").mouseover(function(){
		$(this).css('background','#FF4242');
	});
	$("input[@id='btn2']").mouseout(function(){
		$(this).css('background','#BD0F25');
	});

	//-------------------------------------START POPUP
	//centering with css
	centerpopDialog();

	//-------------------------------------CLOSING POPUP
	//Click the x event!
	$("#popDialogClose,#popBgDialog").click(function(){
		disablepopDialog();
	});
	//Press Escape event!
	$(document).keypress(function(e){
		if(e.keyCode==27 && popDialogStatus==1){
			disablepopDialog();
		}
	});
	$("#popDialogClose").mouseover(function() {
		$("#popDialogClose").css({ cursor: 'pointer' });
	});

});


/***************************/
//@Author: Adrian "yEnS" Mato Gondelle
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!					
/***************************/

//SETTING UP OUR POPUP
//0 means disabled; 1 means enabled;
var popDialogStatus = 0;

//loading popup with jQuery magic!
function loadpopDialog(clicker){
	//loads popup only if it is disabled
	if(popDialogStatus==0){
		$("#popBgDialog").css({
			"opacity": "0.3"
		});
		$("#popBgDialog").fadeIn("slow");
//		$("#popDialog").fadeIn("slow");
		$("#popDialog").slideDown('slow');
		popDialogStatus = 1;

		//iframe load
		$("#loadfunc").attr("src",clicker);
	}
}

//disabling popup with jQuery magic!
function disablepopDialog(){
	//disables popup only if it is enabled
	if(popDialogStatus==1){
		$("#popBgDialog").fadeOut("slow");
//		$("#popDialog").fadeOut("slow");
		$("#popDialog").slideUp('slow');
		popDialogStatus = 0;

		//iframe unload
		$("#loadfunc").attr("src",'#');
	}
}

//centering popup
function centerpopDialog(){
	//request data for centering
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $("#popDialog").height();
	var popupWidth = $("#popDialog").width();

	var top = (($(window).height() / 2) - (popupHeight / 2)) + 30;
	var left = (($(window).width() / 2) - (popupWidth / 2)) + 0;
	if( top < 0 ) top = 0;
	if( left < 0 ) left = 0;
	
	// IE6 fix
	if( $.browser.msie && parseInt($.browser.version) <= 6 ) top = top + $(window).scrollTop();
	
	$("#popDialog").css({
		top: top + 'px',
		left: left + 'px'
	});
	$("#popBgDialog").height( $(document).height() );

	$("#popDialog").draggable();
	
}
