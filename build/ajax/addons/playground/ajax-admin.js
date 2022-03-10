function load_category() {
    var form_data = new FormData();
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
        success: function (result) {
            $("#category").html(result.html);
        },
    });
}

function load_opt_category() {
    var form_data = new FormData();
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
        success: function (result) {
            $("#id_category").html(result.html);
        },
    });
}

function load_product() {
    var form_data = new FormData();
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
        success: function (result) {
            $("#product").html(result.html);
			
			$(".rcon").bind('click', function() {
				let pid = $(this).data('product');
				
				send_post($uri, serializeform(new FormData, {
					'getRcon': 1, 'pid': pid
				}), (result) => {
					$("#rcon_list").html(result.content);
					$("input[name='pid']").val(pid);
					$("#modal").modal('show');
				});
			});
        },
    });
}

function remove(index) {
    alert("Удаление товара повлечёт за собой и удаление всех купленных товаров пользователей.");
    if (confirm("Вы действительно хотите удалить?")) {
        var form_data = new FormData();
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
            success: function (result) {
                load_product();
            },
        });
    }
}

function remove_category() {
    alert("Удаление категории повлечёт удаление всех товаров находящихся в ней.");
    if (confirm("Вы действительно хотите удалить?")) {
        var form_data = new FormData();
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
            success: function (result) {
                load_category();
                load_opt_category();
                load_product();
            },
        });
    }
}

function add_category() {
    var form_data = new FormData();
    form_data.append("phpaction", "1");
    form_data.append("token", $("#token").val());
    form_data.append("add_category", "1");
    var name = $("#name_category").val();
    if (!name || name == "") {
        alert("Имя категории не может быть пустым!");
        return;
    }
    form_data.append("name", name);
    var code = $("#code_category").val();
    if (!code || code == "") {
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
        success: function (result) {
            $("#name_category").val("");
            $("#code_category").val("");
            load_category();
            load_opt_category();
        },
    });
}

function add_product() {
    var toast = new Toasty({ classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true });
    var resource = $("#resource").prop("files")[0];
    if (!resource) {
        toast.warning("Вы должны выбрать загружаемый ресурс!");
        return;
    }
    var name = $("#name").val();
    if (!name || name == "") {
        toast.warning("Наименование товара не может быть пустым!");
        return;
    }
    var category = $("#category").val();
    if (!category || category <= 0) {
        toast.warning("Укажите категорию!");
        return;
    }
    var price = $("#price").val();
    if (!price || price < 0) {
        toast.warning("Стоимость продукта не может быть ниже 0");
        return;
    }
    var form_data = new FormData();
    form_data.append("phpaction", "1");
    form_data.append("token", $("#token").val());
    form_data.append("add_product", "1");
    form_data.append("name", name);
    form_data.append("category", category);
    form_data.append("price", price);
    var executor = $("#executor").val();
    if (!executor || executor == "") {
        form_data.append("executor", "none");
    } else {
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
        success: function (result) {
            if (result.status == 1) {
                $("#name").val("");
                $("#executor").val("");
                $("#resource").val("");
                load_product();
                toast.success("Товар успешно загружен!");
            } else {
                toast.error(result.message);
            }
        },
    });
}

function load_sels_product() {
    var form_data = new FormData();
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
        success: function (result) {
            $("#sels_id").html(result.html);
            $("#remove_id").html(result.html);
        },
    });
}

function edit_currency() {
    var form_data = new FormData();
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
        success: function (result) {
            var toast = new Toasty({ classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true });
            toast.info("Наименование валюты было изменено!");
        },
    });
}

function edit_course() {
    var form_data = new FormData();
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
        success: function (result) {
            var toast = new Toasty({ classname: "toast", transition: "slideLeftRightFade", insertBefore: false, progressBar: true, enableSounds: true });
            toast.info("Курс валюты был изменён!");
        },
    });
}

function edit_secret() {
    var form_data = new FormData();
    form_data.append("edit_secret", "1");
    form_data.append("secret", $("#secret").val());
    send_post(get_url() + "ajax/addons/playground/actions/admin.php", form_data, function (result) {
        if (result.status == 1) {
            toasty("info", "Секретный ключ обновлён!");
        } else {
            toasty("error", "Произошла ошибка..");
        }
    });
}

function edit_limit_product() {
    var form_data = new FormData();
    form_data.append("edit_limit_product", "1");
    form_data.append("limit_product", $("#limit_product").val());
    send_post(get_url() + "ajax/addons/playground/actions/admin.php", form_data, function (result) {
        if (result.status == 1) {
            toasty("info", "Лимит товаров обновлён!");
        } else {
            toasty("error", "Произошла ошибка..");
        }
    });
}

function edit_bonuses() {
    var form_data = new FormData();
    form_data.append("edit_bonuses", "1");
    form_data.append("value", $("#bonuses").val());
    send_post(get_url() + "ajax/addons/playground/actions/admin.php", form_data, function (result) {
        if (result.alert == "success") {
            toasty("info", "Бонусы пополнения баланса обновлены!");
        } else {
            toasty("error", "Произошла ошибка..");
        }
    });
}

function onEditor(pid) {
	$("#name" + pid).html("<input id=\"_name" + pid + "\" type=\"text\" class=\"form-control\" value=\"" + $("#name" + pid).html() + "\">");
	$("#price" + pid).html("<input id=\"_price" + pid + "\" type=\"text\" class=\"form-control\" value=\"" + $("#price" + pid).html() + "\">");
	$("#availability" + pid).html("<input id=\"_availability" + pid + "\" type=\"text\" class=\"form-control\" value=\"" + $("#availability" + pid).html() + "\">");
	
	$("#onEditor" + pid).removeAttr("onclick");
	$("#onIcon" + pid).toggleClass("glyphicon-edit glyphicon-floppy-save");
	
	$("#onEditor" + pid).unbind("click");
	$("#onEditor" + pid).bind("click", function() {
		onSave(pid);
	});
}

$uri = url() + "ajax/addons/playground/actions/admin.php";

function onSave(pid) {
	var name = $("#_name" + pid).val();
	var price = $("#_price" + pid).val();
	var availability = $("#_availability" + pid).val();
	
	send_post($uri, serializeform(new FormData, {
		edit_product: 1,
		pid: pid,
		name: name,
		price: price,
		availability: availability
	}), (result) => {
		if(result.alert == 'success') {
			$("#name" + pid).html(name);
			$("#price" + pid).html(price);
			$("#availability" + pid).html(availability);
			
			$("#onIcon" + pid).toggleClass("glyphicon-floppy-save glyphicon-edit");
			$("#onEditor" + pid).unbind("click");
			$("#onEditor" + pid).bind("click", function() {
				onEditor(pid);
			});
			
			return;
		}
		
		push(result.message, result.alert);
	});
}

$(function() {
	$("#form_rcon_add").submit(function(e) {
		e.preventDefault();
		
		send_post($uri, serializeform(new FormData(this), {
			addRcon: 1
		}), (result) => {
			if(result.alert != 'success') {
				push(result.message, result.alert);
				return;
			}
			
			$("#rcon_list").html(result.content);
		});
	});
});

$(document).on('click', 'button[data-id]', function(){ 
	send_post($uri, serializeform(new FormData, {
		removeCommand: 1,
		id: $(this).data('id'),
		pid: $("input[name='pid']").val()
	}), (result) => {
		$("#rcon_list").html(result.content);
	});
});