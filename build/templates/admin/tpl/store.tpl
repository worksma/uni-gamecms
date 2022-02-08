<div class="page">
	<div class="col-md-12">
		<div class="block">
			<div class="block_head">
				Выберите сервер
			</div>
			<select class="form-control" onchange="server_change();" id="server">
				{servers}
			</select>
		</div>
	</div>
	<div class="col-md-6">
		<div class="block">
			<div class="block_head">
				Добавить услугу
			</div>

			<select class="form-control mt-10" id="sale">
				<option value="1">Продажа: Включена</option>
				<option value="2">Продажа: Выключена</option>
			</select>
			<select class="form-control mt-10" id="show">
				<option value="1">Отображение на странице администраторов: Включено</option>
				<option value="2">Отображение на странице администраторов: Выключено</option>
			</select>
			<select class="form-control mt-10" id="user_groups">
				{user_groups}
			</select>
			<select class="form-control mt-10" onchange="change_group_or_flags('');" id="flags_or_group">
				<option value="1">Привилегия: По флагам</option>
				<option value="2">Привилегия: По группе</option>
			</select>
			<input class="form-control mt-10" type="text" maxlength="255" id="name" placeholder="Название услуги" autocomplete="off">
			<input class="form-control mt-10" type="text" maxlength="25" id="flags" placeholder="Флаги" autocomplete="off">
			<input id="group" class="form-control mt-10" type="text" maxlength="120" placeholder="Название группы в sourcebans" autocomplete="off">
			<input id="immunity" class="form-control mt-10" type="number" maxlength="3" placeholder="Иммунитет" autocomplete="off">
			<input id="discount" class="form-control mt-10" type="number" maxlength="2" placeholder="Скидка (в % от 0 до 99)" autocomplete="off">
			<br>

			<textarea id="text" class="form-control maxMinW100" rows="5">Описание</textarea>
			<button class="btn2 mt-10" onclick="add_service();">Добавить</button>
		</div>
	</div>
	<div class="col-md-6">
		<div class="block">
			<div class="block_head">
				Добавить тариф
			</div>
			Выберите услугу:
			<select class="form-control" id="services"></select>
			<input class="form-control mt-10" type="text" maxlength="7" id="time" placeholder="Время (в днях, 0 - навсегда)" autocomplete="off">
			<input class="form-control mt-10" type="number" maxlength="6" id="price" placeholder="Цена покупки" autocomplete="off">
			<input class="form-control mt-10" type="number" maxlength="6" id="priceRenewal" placeholder="Цена продления (0 - соответствует цене покупки)" autocomplete="off">
			<input class="form-control mt-10" type="number" maxlength="2" id="tarif_discount" placeholder="Скидка (в % от 0 до 99)" autocomplete="off">
			<button class="btn2 mt-10" onclick="add_tarif();">Добавить</button>
		</div>
	</div>
	<div class="col-md-12 mt-10" id="services2">
		<center><img src="{site_host}templates/admin/img/loader.gif"></center>
	</div>
</div>
<script>
	function server_change() {
		var server = $('#server').val();
		location.href = '../admin/store?server='+server;
	}
	function change_group_or_flags(id) {
		var selectBox = document.getElementById("flags_or_group"+id);
		var selectedValue = selectBox.options[selectBox.selectedIndex].value;
		if(selectedValue == 1) {
			$('#group'+id).fadeOut(1);
			$('#flags'+id).fadeIn(1);
			$('#immunity'+id).fadeIn(1);
		} else {
			$('#group'+id).fadeIn(1);
			$('#flags'+id).fadeOut(1);
			$('#immunity'+id).fadeOut(1);
		}
	}
	if($.trim($("#server").html()) == '') {
		selectedValue == 1;
	} else {
		var selectBox = document.getElementById("server");
		var selectedValue = selectBox.options[selectBox.selectedIndex].title;
	}
	if(selectedValue == 4) {
		$('#flags_or_group').fadeIn(1);
		change_group_or_flags('');
	} else {
		$('#immunity').fadeOut(1);
		$('#flags_or_group').fadeOut(1);
		$('#group').fadeOut(1);
		$('#flags').fadeIn(1);
	}
	get_services();
	get_services2('{{md5($conf->code)}}');
</script>