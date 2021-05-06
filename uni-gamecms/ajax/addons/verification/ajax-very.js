function get_very() {
	var token = $("#token").val();
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/verification/actions.php",
		data: "phpaction=1&token=" + token + "&get_very=1",
		dataType: "json",
		success: function(result) {
			if(result.status == 1) {
				window.location.reload();
			}
		}
	});
}

function close_event(index) {
	if(confirm("Вы уверены?")) {
		var token = $("#token").val();
		
		$.ajax({
			type: "POST",
			url: "../ajax/addons/verification/actions.php",
			data: "phpaction=1&token=" + token + "&close_event=1&index=" + index,
			dataType: "json",
			success: function(result) {
				$("#very_info_" + index).hide();
			}
		});
	}
}