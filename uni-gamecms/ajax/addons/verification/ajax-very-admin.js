function send_very(index) {
	var token = $("#token").val();
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/verification/actions_admin.php",
		data: "phpaction=1&token=" + token + "&send_very=1&index=" + index,
		dataType: "json",
		success: function(result) {
			if(result.status == 1) {
				$("#list_verifications").html(result.html);
			}
		}
	});
}

function send_not_very(index) {
	var token = $("#token").val();
	var message = prompt('Укажите причину отказа:', '');
	
	if(message && message != '') {
		$.ajax({
			type: "POST",
			url: "../ajax/addons/verification/actions_admin.php",
			data: "phpaction=1&token=" + token + "&send_not_very=1&index=" + index + "&message=" + message,
			dataType: "json",
			success: function(result) {
				if(result.status == 1) {
					$("#list_verifications").html(result.html);
				}
			}
		});
	}
}