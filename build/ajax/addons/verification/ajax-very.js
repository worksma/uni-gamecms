function get_very() {
	var form_data = new FormData;
	form_data.append("get_very", "1");

	send_post("/ajax/addons/verification/actions.php", form_data, function(result) {
		if(result.status == 1) {
			window.location.reload();
		}
	});
}

function close_event(index) {
	if(confirm("Вы уверены?")) {
		var form_data = new FormData;
		form_data.append("close_event", "1");
		form_data.append("index", index);

		send_post("/ajax/addons/verification/actions.php", form_data, function(result) {
			$("#very_info_" + index).hide();
		});
	}
}

function edit_very(index) {
	var value = prompt("Установите значение 1 - если выдать и 0 - если забрать.");

	if(value) {
		var form_data = new FormData;
		form_data.append("edit_very", "1");
		form_data.append("user_id", index);

		if(value == 0) {
			if(!confirm("Вы действительно хотите забрать верификацию?")) {
				return;
			}

			form_data.append("value", value);
		}
		else {
			if(!confirm("Вы действительно хотите выдать верификацию?")) {
				return;
			}

			form_data.append("value", value);
		}

		send_post("/ajax/addons/verification/actions_admin.php", form_data, function(result) {
			if(result.alert == 'success') {
				alert(result.message);
			}
			else {
				alert("Произошла неизвестная ошибка со стороны сервера.");
			}
		});
	}
}