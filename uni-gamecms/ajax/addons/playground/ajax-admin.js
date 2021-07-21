/*
	Загрузка категорий
*/
function load_category() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_category", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
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
	Загрузка категорий
*/
function load_opt_category() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_category", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#id_category").html(result.html);
		}
	});
}

/*
	Загрузка товаров
*/
function load_product() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_product", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#product").html(result.html);
		}
	});
}

/*
	Удаление продукта
*/
function remove(index) {
	alert("Удаление товара повлечёт за собой и удаление всех купленных товаров пользователей.");
	if(confirm("Вы действительно хотите удалить?")) {
		var form_data = new FormData;
		form_data.append("phpaction", "1");
		form_data.append("token", $("#token").val());
		form_data.append("remove", "1");
		form_data.append("id_product", index);
		
		$.ajax({
			type: "POST",
			url: "../ajax/addons/playground/actions/admin.php",
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: function(result) {
				load_product();
			}
		});
	}
}

/*
	Удаление категории
*/
function remove_category() {
	alert("Удаление категории повлечёт удаление всех товаров находящихся в ней.");
	if(confirm("Вы действительно хотите удалить?")) {
		var form_data = new FormData;
		form_data.append("phpaction", "1");
		form_data.append("token", $("#token").val());
		form_data.append("remove_category", "1");
		form_data.append("id_category", $("#id_category").val());
		
		$.ajax({
			type: "POST",
			url: "../ajax/addons/playground/actions/admin.php",
			processData: false,
			contentType: false,
			data: form_data,
			dataType: "json",
			success: function(result) {
				load_category();
				load_opt_category();
				load_product();
			}
		});
	}
}

/*
	Добавление категории
*/
function add_category() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("add_category", "1");
	
	var name = $("#name_category").val();
	if(!name || name == '') {
		alert("Имя категории не может быть пустым!");
		return;
	}
	
	form_data.append("name", name);
	
	var code = $("#code_category").val();
	if(!code || code == '') {
		alert("Кодовое слово не может быть пустым!");
		return;
	}
	
	form_data.append("code", code);
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#name_category").val('');
			$("#code_category").val('');
			
			load_category();
			load_opt_category();
		}
	});
}

/*
	Добавление продукта
*/
function add_product() {
	var toast = new Toasty({
		classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true
	});
	
	var resource = $("#resource").prop('files')[0];
	if(!resource) {
		toast.warning("Вы должны выбрать загружаемый ресурс!");
		return;
	}
	
	var name = $("#name").val();
	if(!name || name == '') {
		toast.warning("Наименование товара не может быть пустым!");
		return;
	}
	
	var category = $("#category").val();
	if(!category || category <= 0) {
		toast.warning("Укажите категорию!");
		return;
	}
	
	var price = $("#price").val();
	if(!price || price < 0) {
		toast.warning("Стоимость продукта не может быть ниже 0");
		return;
	}
	
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("add_product", "1");
	
	form_data.append("name", name);
	form_data.append("category", category);
	form_data.append("price", price);
	
	var executor = $("#executor").val();
	if(!executor || executor == '') {
		form_data.append("executor", "none");
	}
	else {
		form_data.append("executor", executor);
	}
	
	form_data.append("resource", resource);
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			if(result.status == 1) {
				$("#name").val("");
				$("#executor").val("");
				$("#resource").val("");
				
				load_product();
				toast.success("Товар успешно загружен!");
			}
			else {
				toast.error(result.message);
			}
		}
	});
}

/*
	Добавление продаж
*/
function add_sels() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("add_sels", "1");
	
	form_data.append("id_product", $("#sels_id").val());
	form_data.append("value", $("#sels_count").val());
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			var toast = new Toasty({
				classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true
			});
			
			toast.info("Количество продаж успешно начислено!");
		}
	});
}

/*
	Удаление продаж
*/
function remove_sels() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("remove_sels", "1");
	
	form_data.append("id_product", $("#remove_id").val());
	form_data.append("value", $("#remove_count").val());
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			var toast = new Toasty({
				classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true
			});
			
			if(result.status == 1) {
				toast.info(result.message);
			}
			else {
				toast.warning(result.message);
			}
		}
	});
}

/*
	Загрузка товаров
*/
function load_sels_product() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("load_sels_product", "1");
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			$("#sels_id").html(result.html);
			$("#remove_id").html(result.html);
		}
	});
}

/*
	Изменение наименования валюты
*/
function edit_currency() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("edit_currency", "1");
	form_data.append("name", $("#currency").val());
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			var toast = new Toasty({
				classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true
			});
			
			toast.info("Наименование валюты было изменено!");
		}
	});
}

/*
	Изменение наименования валюты
*/
function edit_course() {
	var form_data = new FormData;
	form_data.append("phpaction", "1");
	form_data.append("token", $("#token").val());
	form_data.append("edit_course", "1");
	form_data.append("course", $("#course").val());
	
	$.ajax({
		type: "POST",
		url: "../ajax/addons/playground/actions/admin.php",
		processData: false,
		contentType: false,
		data: form_data,
		dataType: "json",
		success: function(result) {
			var toast = new Toasty({
				classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true
			});
			
			toast.info("Курс валюты был изменён!");
		}
	});
}