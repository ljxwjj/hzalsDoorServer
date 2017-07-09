$(function(){
    var dialogApprove;
    dialogDelay = $("#ajax-approve_div").dialog({
    		"autoOpen": false,
    		"height": 300,
    		"width": 600,
    		"modal": true,
    		"buttons": {
    			//"取消": function(){
    				//dialog.dialog("close");
    			//}
    		}
    });
})

//批量审核
function dealAll(){
	var params = gerParams();
	keyValue = getSelectCheckboxValues();
	$('#approveId').val(keyValue);
	$("#ajax-approve_div").dialog( "open" );
	
}