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

$uri = url() + "ajax/addons/playground/actions/main.php";

$(function() {
	$(".product-buy-trading").on('click', function() {
		var pid = $(this).data("product");
		
		$.confirm({
			title: 'Покупка товара',
			content: 'Вы действительно хотите купить данный товар?',
			type: 'blue',
			typeAnimated: true,
			buttons: {
				confirm: {
					text: 'Да, купить',
					btnClass: 'btn-blue',
					action: function() {
						send_post($uri, serializeform(new FormData, {
							buy: 1,
							pid: pid
						}), (result) => {
							if(result.alert != 'error' && result.alert != 'warning') {
								$("#availability" + pid).html(result.count);
							}
							
							push(result.message, result.alert);
						})
					}
				},
				close: {
					text: 'Отмена'
				}
			}
		});
	});
	
	const con = document.getElementsByClassName("card-body");
	Array.from(con).map((item) => {
		const img = new Image();
		img.src = item.dataset.src;
		
		img.onload = () => {
			return item.nodeName === "IMG" ?
				item.src = item.dataset.src :
				item.style.background = `url('${item.dataset.src}')`;
		}
	});
	
	$(".trading-item").bind('click', function() {
		var item = $(this);
		
		if(item.hasClass('active')) {
			$.confirm({
				title: 'Отключение товара', content: 'Вы действительно хотите отключить предмет?', type: 'blue', typeAnimated: true,
				buttons: {
					confirm: {
						text: 'Да',
						btnClass: 'btn-blue',
						action: function() {
							item.removeClass("active");
							send_post($uri, serializeform(new FormData, {off: 1, pid: item.data('purchases')}), (result) => {});
						}
					},
					close: {
						text: 'Отмена'
					}
				}
			});
		}
		else {
			$(".trading-item[data-category='" + item.data('category') + "']").removeClass('active');
			item.addClass("active");
			send_post($uri, serializeform(new FormData, {on: 1, pid: item.data('purchases')}), (result) => {});
		}
	});
	
	$(".footer").remove();
});