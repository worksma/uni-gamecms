function send_very(index) {
	var form_data = new FormData;
	form_data.append("send_very", "1");
	form_data.append("index", index);

	send_post("/ajax/addons/verification/actions_admin.php", form_data, function(result) {
		if(result.status == 1) {
			$("#list_verifications").html(result.html);
		}
	});
}

function send_not_very(index) {
	var message = prompt("Укажите причину отказа:", "");

	if(message && message != '') {
		var form_data = new FormData;
		form_data.append("send_not_very", "1");
		form_data.append("index", index);
		form_data.append("message", message);

		send_post("/ajax/addons/verification/actions_admin.php", form_data, function(result) {
			if(result.status == 1) {
				$("#list_verifications").html(result.html);
			}
		});
	}
}

function off_very(index) {
	var form_data = new FormData;
	form_data.append("edit_very", "1");
	form_data.append("user_id", index);
	form_data.append("value", "0");

	send_post("/ajax/addons/verification/actions_admin.php", form_data, function(result) {
		if(result.alert == 'success') {
			alert(result.message);
			location.reload();
		}
		else {
			alert("Произошла ошибка со стороны сервера.");
		}
	});
}