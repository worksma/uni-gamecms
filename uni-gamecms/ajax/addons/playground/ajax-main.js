/*
	Загрузка категорий
*/
function load_category() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_category", "1");
	
	var category = $("#search_category").val();
	if(category && category != '') {
		form_data.append("category", category);
	}
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#category").html(result.html);
		}
	});
}

/*
	Загрузка товара на продажу
*/
function load_product_sell() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_product_sell", "1");
	
	var category = $("#search_category").val();
	if(category && category != '') {
		form_data.append("category", category);
	}
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#product_sell").html(result.html);
		}
	});
}

/*
	Покупка товара
*/
function playground_buy(index) {
	if(confirm("Вы действительно хотите купить?")) {
		var form_data = new FormData;
		form_data.append("phpaction", "1");
		form_data.append("token", $("#token").val());
		form_data.append("playground_buy", "1");
		form_data.append("id_product", index);
		
		$.ajax({
			type: "POST",
			url: "../ajax/addons/playground/actions/main.php",
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: function(result) {
				var toast = new Toasty({
					classname: "toast",
					transition: "slideLeftRightFade",
					insertBefore: false,
					progressBar: true,
					enableSounds: true
				});
				
				if(result.status == 1) {
					toast.success(result.message);
					load_product_sell();
				}
				else {
					toast.error(result.message);
				}
			}
		});
	}
}

/*
	Загрузка предметов пользователя
*/
function load_items() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_items", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#items").html(result.html);
		}
	});
}

/*
	Включение товара
*/
function playground_enable(index) {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("playground_enable", "1");
	form_data.append("id_purchases", index);
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			var toast = new Toasty({
				classname: "toast",
				transition: "slideLeftRightFade",
				insertBefore: false,
				progressBar: true,
				enableSounds: true
			});
			
			if(result.status == 1) {
				load_items();
				
				if(result.info == 'info') {
					toast.warning(result.message);
				}
				else if(result.info == 'success') {
					toast.success(result.message);
				}
			}
			else {
				toast.error(result.message);
			}
		}
	});
}

/*
	Загрузка предметов на продажу
*/
function sell_load_items() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("sell_load_items", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#items").html(result.html);
		}
	});
}

/*
	Продажа предмета пользователем
*/
function sell_product(index) {
	if(confirm("Вы действительно хотите продать предмет?")) {
		var form_data = new FormData;
		form_data.append("phpaction", "1");
		form_data.append("token", $("#token").val());
		form_data.append("sell_product", "1");
		form_data.append("id_purchases", index);
		
		$.ajax({
			type: "POST",
			url: "../ajax/addons/playground/actions/main.php",
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: function(result) {
				var toast = new Toasty({
					classname: "toast",
					transition: "slideLeftRightFade",
					insertBefore: false,
					progressBar: true,
					enableSounds: true
				});
				
				if(result.status == 1) {
					sell_load_items();
					load_product_sell();
					toast.success(result.message);
				}
				else {
					toast.error(result.message);
				}
			}
		});
	}
}

/*
	Калькулятор
*/
function clc() {
	var val = $("#exchangerValue").val();
	if(!val || val <= 0 || val == '') {
		$("#result_clc").html("<font class=\"text-danger\">Сумма должна быть больше 0</font>");
		return;
	}
	
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("clc", "1");
	form_data.append("value", val);
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#recoil").html(result.recoil);
			$("#receiving").html(result.receiving);
			$("#result_clc").html('');
		}
	});
}

/*
	Обмен валюты
*/
function on_exchange() {
	var val = $("#exchangerValue").val();
	if(!val || val <= 0 || val == '') {
		$("#result_clc").html("<font class=\"text-danger\">Сумма должна быть больше 0</font>");
		return;
	}
	
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("on_exchange", "1");
	form_data.append("value", val);
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/main.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#result_clc").html(result.html);
		}
	});
}