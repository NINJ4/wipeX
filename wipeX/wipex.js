$(document).ready(function(){
	var OpsPending = new Array();
	var count = 0;
	var submitted = false;
	var error = false;
	var cleared = false;
	var logLim = 0;
	
	function sendCommand( opNum, newCommand ) {			//execute query
		if ( !newCommand )
			var execCommand = OpsPending[ opNum ];
		else
			var execCommand = newCommand;
		$.get("wipeX_engine.php" + execCommand + "&queryNum=" + opNum, function(result){
			var splitStr = String(result).split(',');
			var isSuccess = splitStr[0];
			var replaceNum = splitStr[1];
			var infoMsg = splitStr[2];
			//get success/fail
			if ( isSuccess == "true" ) {
				$("#itemLoad" + replaceNum ).html("<img src='images/done.png' title='performed action!' alt='performed action!' />"
												+ ( ( infoMsg ) ? " <b>Notice:</b> " + String(infoMsg) : "" ) );		
			}
			else if ( isSuccess == "retry" ) {
				sendCommand( replaceNum, String(infoMsg).replace( "+group", "&group" ) );
			}
			else if ( isSuccess == "false" ) {
				error = true;
				$("#itemLoad" + replaceNum ).html(
								"<img src='images/failed.png' title='failed action!' alt='failed action!' /> <b>Error:</b> " 
								+ infoMsg );
			}
		});
	}
	$(".Submit").click(function(){
		submitted = true;
		//start loading wheel
		$(".itemLoading").html("<img src='images/loading.gif' title='pending actions' alt='pending actions' />");
		$("#submitLoading").html("<img src='images/loading.gif' title='Executing actions' alt='Executing actions' />");
		//while execute OpsPending...
		for( var opNum in OpsPending ) {
		
			sendCommand( opNum );

		}
	});
	$('.finishedOps').ajaxStop(function(){
		//is this a submit?
		if ( submitted ) {
			$(".wipex-topbar").slideUp("normal");
			$(".slidePanel").slideUp("normal");
			//change the overall loader to Done or Failed
			//change this background color
			$("h2#OpsTitle").text("Operations Completed:");
			$(".Submit").hide();
			$(".ClearPending").text("Clear Completed Operations");
			if ( !error ) {
				$("#submitLoading").html("All actions will take in-game effect once <b>/pex reload</b> has been run.&nbsp;<img src='images/done.png' title='Performed Actions' alt='Performed Actions' />");
				$('.finishedOps').css("background-color", "green");
			}
			else {
				$('.finishedOps').css("background-color", "red");
				$("#submitLoading").html("<img src='images/failed.png' title='Actions Failed!' alt='Actions Failed!' />");
			}
			error = false;
			submitted = false;
			$.get("wipeX_engine.php?data=groups", function(result){
			  $("#column1").html(result);
			});
			$.get("wipeX_engine.php?data=permissions&group=" + $("#column1").val(), function(result){
			  $("#column2").html(result);
			});
			$.get("wipeX_engine.php?data=members&group=" + $("#column1").val(), function(result){
			  $("#column3").html(result);
			});
		}
	});
	
	//Expanded Overlay
	$(".expand").click(function(){
		if ( String($(this).attr("class")).indexOf("col1") != -1 ) {
			//$.get("wipeX_engine.php?data=permissions&group=" + $("#column1").val(), function(result){
			  $("#colExpanded").html("<option value='-1'>Feature not implemented yet.</option>");
			//});
		}
		if ( String($(this).attr("class")).indexOf("col2") != -1 ) {
			$("#expanded-title").text("Complete Permissions for " + $("#column1").val());
			$.get("wipeX_engine.php?data=permissions&inherited=true&group=" + $("#column1").val(), function(result){
				$("#colExpanded").html(result);
			});
		}
		if ( String($(this).attr("class")).indexOf("col3") != -1 ) {
			$("#expanded-title").text("Members of " + $("#column1").val());
			$.get("wipeX_engine.php?data=members&group=" + $("#column1").val(), function(result){
			  $("#colExpanded").html(result);
			});
		}
  		$(".expand-overlay").fadeIn("slow");
	});
	
	$(".cancelOverlay").click(function(){
  		$(".input-overlay,.expand-overlay").fadeOut("normal");
	});
	$("#resetOverlay").click(function(){
		$(".input-area").val('');
	});
	$("#column3").change(function(){
		if ( $("#column3").val() != -1 ) {
			$("#selectedMembers").html( "&bull; " + String($(this).val()).replace(/,/g,"<br />\n&bull; ") );
			$(".memberConfig").slideDown("slow");
		}
		else {
			$(".memberConfig").slideUp("slow");
		}
	});
	$("#column2").change(function(){
		var element = $("option:selected", "#column2");
		var myTag = element.attr("class");
			if ( String($(this).val()) == String($("#column1").val()) ) {
			$(".permsConfig").slideUp("slow");
		}
		else if ( ( $("#column2").val() != -1 ) && ( myTag != "parent" ) && ( $("#column2").val() != null ) ) {
			$("#selectedPerms").html( "&bull; " + String($(this).val()).replace(/,/g,"<br />\n&bull; ") );
			$(".permsConfig").slideDown("slow");
		}
	});
	$(".ClearPending").click(function(){
		count = 0;
		var OpsPending = "";
		$("#OpsContent").html("");
		$("#submitLoading").html("");
		$(".finishedOps").css("background-color", "#BBD2E0");
		$(".finishedOps").slideUp("slow");
		$(".Submit").show();
		$(".wipex-topbar").slideDown("slow");
	});
	$(".Ask").click(function(){
		//request input
		var whoami = $(this).attr("id");
		switch ( whoami ) {
			case "addParent":
				$(".overlay-items").html("parent");
				break;
			case "addMem":
				$(".overlay-items").html("member");
				break;
			case "addPerm":
				$(".overlay-items").html("permission");
				break;
			case "newGrp":
				$(".overlay-items").html("group");
				$(".overlay-action").html("add to the Database");
				break;
			default:
				alert('what the fuck did you do?\n' + whoami );
		}
		$(".AskSubmit").attr("id", $(this).attr("id"));
		$(".input-overlay").fadeIn("slow");
		$(".input-area").focus()
	});
	$(".AskSubmit").click(function(){
		var choices = $.trim(String($(".input-area").val())).replace(/\n/g, ", ");
		var group = "";
		if ( String($( ".Ask#" + $(this).attr("id") ).attr("class")).indexOf("col2") != -1 ) 
			group = String($("#column1").val());
		else if ( String($( ".Ask#" + $(this).attr("id") ).attr("class")).indexOf("col3") != -1 )
			group = String($("#column1").val());
		
		//add to pending panel
		$("#OpsContent").html($("#OpsContent").html() + "<b>" + $( ".Ask#" + $(this).attr("id") ).html() 
								+ ( ( group != "" ) ? " <u>" + group  + "</u>" : "" ) 
								+ ":</b> " + choices + "<span class='itemLoading' id='itemLoad" + count + "'></span><br />");
		//add stuff to Ops variable:
		OpsPending[ count ] = "?do=" + String($(this).attr("id"))
						 + "&data=" + choices
						 + ( ( group ) ? "&group=" + group : "" );
		count++;
		//fade out overlay
		$(".input-overlay").fadeOut("normal");
		//appear panel
		$(".finishedOps").slideDown("slow");
		//clear input area
		$(".input-area").val('');
	});
	
	$(".Pop").click(function(){		//#################
		var choices = $.trim(prompt( $(this).text() + " to:" , ""));
		if ( choices ) {
			var group = "";
			if ( String($(this).attr("class")).indexOf("col2") != -1 ) 
				group = String($("#column1").val());
			else if ( String($(this).attr("class")).indexOf("col3") != -1 )
				group = String($("#column1").val());
			
			group = group.replace("-", "\-");
			//add stuff to Ops variable:
			OpsPending[ count ] = "?do=" + String($(this).attr("id"))
							 + "&data=" + choices
							 + ( ( group != "" ) ? "&group=" + group : "" );
		//throw( OpsPending[ count ] );
			//add stuff to Ops Panel
			$("#OpsContent").html($("#OpsContent").html() + "<b>" + $(this).html()
									+ ( ( group != "" ) ? ( " for <u>" + group  + "</u>" ) : "" ) 
									+ " to</b> " + choices + "<span class='itemLoading' id='itemLoad" + count + "'></span><br />");
			count++;
			//appear
			$(".finishedOps").slideDown("slow");
		}
	});
	$(".Confirm").click(function(){ //for column1 removals only
		var response = confirm('Are you sure you want to delete group "' + String($("#column1").val()) + '" and all its permissions, inheritances, members, etc?\n\nCAUTION: THIS CANNOT BE UNDONE.');
		if ( !response )
			return 0;
		var group = "";
		var choices = "";
		if ( String($(this).attr("class")).indexOf("col1") != -1 )
			choices = String($("#column1").val()); 
			
		//add to human ops panel
		$("#OpsContent").html($("#OpsContent").html() + "<b>" + $(this).html() 
								+ ( ( group != "" ) ? " <u>" + group  + "</u>" : "" ) 
								+ ":</b> " + choices + "<span class='itemLoading' id='itemLoad" + count + "'></span><br />");
		//add stuff to Ops variable:
		OpsPending[ count ] = "?do=" + String($(this).attr("id"))
						 + "&data=" + choices
						 + ( ( group ) ? "&group=" + group : "" );
		count++;
		//appear
		$(".finishedOps").slideDown("slow");
	});
	$(".Do").click(function(){
		//add stuff to Ops Panel
		var group = "";
		var choices;
		if ( String($(this).attr("class")).indexOf("col1") != -1 )
			choices = String($("#column1").val()); 
		else if ( String($(this).attr("class")).indexOf("col2") != -1 ) {
			choices = String($("#column2").val()); 
			group = String($("#column1").val());
		}
		else if ( String($(this).attr("class")).indexOf("col3") != -1 ) {
			choices = String($("#column3").val()); 
			group = String($("#column1").val());
		}
		//add to human ops panel
		$("#OpsContent").html($("#OpsContent").html() + "<b>" + $(this).html() 
								+ ( ( group != "" ) ? " <u>" + group  + "</u>" : "" ) 
								+ ":</b> " + choices + "<span class='itemLoading' id='itemLoad" + count + "'></span><br />");
		//add stuff to Ops variable:
		OpsPending[ count ] = "?do=" + String($(this).attr("id"))
						 + "&data=" + choices
						 + ( ( group ) ? "&group=" + group : "" );
		count++;
		//appear
		$(".finishedOps").slideDown("slow");
	});
	$(document).keyup(function(e) {
  		if ( ( e.keyCode == 27 ) || ( e.which == 27 ) ) { 
  			$('.cancelOverlay').click();
  		}
	});
	
	$("#logAction").change(function(){
		//if undo, add a rollback to the queue
		if ($(this).val() == "undo") {
			//foreach selected checkbox
			$("input.undoThis:checked").each(function(i){
				//add to the Ops panel
				$("#OpsContent").html($("#OpsContent").html() + "<b><span style='color:#A80B00;'>UNDO" 
										+ ":</b></span> " + $("#Haction" + String($(this).val()) ).text() 
										+ "<span class='itemLoading' id='itemLoad" + count + "'></span><br />");
				//add stuff to Ops variable:
				OpsPending[ count ] = "?do=undo&data=" + $(this).val();
				count++;
				//appear
				$(".finishedOps").slideDown("slow");
				$("#logAction").val('-1');
			});
		}
	});
	$(".loadLogs").click(function(){
		//if ( $(this).attr("id") == "showLogs" )
			logLim = 0;
		
		//change teh button text and toggle visibility:
		var newText = $(".wipex-container:visible").attr("id");
		$(".wipex-container:visible").fadeOut("normal");
		$(".wipex-container:hidden").fadeIn("normal");
		$(this).text(newText + " wipeX Logs");
		
		if ( newText == "Show" ) {
			$.get("wipeX_engine.php?data=groups", function(result){
			  $("#column1").html(result);
			  $("#column2").html("");
			  $("#column3").html("");
			});
		}
		$("div.logLoader").click();
	});
	$("div.logLoader").click(function(){
		$.get("wipeX_engine.php?data=getlogs&start=" + logLim , function(result){
			var append = "";
			if ( logLim != 0 )
				append = $(".logs-rows").html();
			$(".logs-rows").html( append + result );
			logLim += 50;
			
			if ( $("#logRow1").html() ) {
				$(".logLoader").html("All logs have been loaded.");
				//$("div.logLoader").attr("class", "null");
			}
		});
	});
	$(".undoThis").live("click", function(){
		$("tr#logRow" + $(this).val()).css("background-color","#BBD2E0");
	});
	
  $("#column1").change(function(){
	//$("#pHead").html("Loading...");
	//$("#mHead").html("Loading...");
	$(".rgAddItems").slideDown("slow");
	$(".permsConfig").slideUp("slow");
	$(".memberConfig").slideUp("slow");
	$(".expand.col3,.expand.col2").fadeIn("slow");
	$.get("wipeX_engine.php?data=permissions&group=" + $(this).val(), function(result){
	  $("#column2").html(result);
	  //$("#pHead").html("");
	});
	$.get("wipeX_engine.php?data=members&group=" + $(this).val(), function(result){
	  $("#column3").html(result);
	  //$("#mHead").html("");
	});
	$("#selectedGroups").html( "&bull; " + String($(this).val()).replace(/,/g,"<br />\n&bull; ") );
	$(".grpConfig").slideDown("slow");
	
		
  });
});
