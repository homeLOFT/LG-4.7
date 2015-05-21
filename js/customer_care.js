$(document).ready(function() {
	
	$(".custcare-link").click(function (event) {
    
		$( "#cc-modal" ).dialog({
 	      autoOpen: false,
		  closeOnEscape: true,
		  width: 'auto',
    	  height: 'auto',
    	  maxWidth: 2000,
		  modal: true,
    	  fluid: true, //new option
    	  resizable: false,
		  draggable:false,
    	  show: {
    	    effect:'slide',
    	    duration:1500,
    	  },
    	  hide: {
    	    effect:'slide',
    	    duration:1500,
    	  },
		  dialogClass: 'ccmodal',
		  buttons: {
		  	"Close Window": function () {
            	$("#cc-modal").dialog("close");
         	 }
		  }
    	});
		
		$(".ui-dialog-titlebar").hide();
	
    	$.get( "/common/modal/pages/customer-care.html", function( html ) {
			
      		$( "#cc-modal" ).html( html ).dialog('open');
    	});
	});	
});