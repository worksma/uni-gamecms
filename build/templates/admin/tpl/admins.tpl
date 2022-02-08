<div class="page">
	<div class="col-md-12">
		<div class="block">
			<div class="block_head">
				Выберите сервер
			</div>
			<select class="form-control" onchange="server_change();" id="store_server">
				{servers}
			</select>
		</div>
	</div>
	<div class="col-md-6">
		<div class="block">
			<div class="block_head">
				Синхронизация
			</div>
			<div id="ftp" class="disp-n">
				<b>Импорт</b>
				<div class="bs-callout bs-callout-info mt-10 fs-14">
					Предварительно удалите из файла весь мусор (комментарии и т.д), приведите его в исходный вид (<a target="_blank" href="{site_host}templates/admin/img/users.png"><font class="c-D9534F"><span class="glyphicon glyphicon-link"></span> нажмите, чтобы посмотреть пример</font></a>, обратите внимание на то, что используются <font class="c-D9534F">двойные кавычки</font> и в конце строки ставится <font class="c-D9534F">:end:</font>)
				</div>
				<button class="btn2 mt-5" onclick="import_admins();">Импортировать админов из users.ini в базу данных сайта</button>
				<br><br>
				<b>Экспорт</b>
				<div class="bs-callout bs-callout-info mt-10 fs-14">
				Если был утерян по каким-либо причинам файл users.ini на сервере, то можно экспортировать админов из базы данных сайта в users.ini
				</div>
				<button class="btn2 mt-5" onclick="export_admins();">Экспортировать админов из базы данных сайта в users.ini</button>
			</div>
			<div id="db" class="disp-n">
				<b>Импорт</b>
				<div class="bs-callout bs-callout-info mt-10 fs-14">
					Внимание! Выключенные администраторы не импортируются!
				</div>
				<button class="btn2 mt-5" onclick="import_admins();">Импортировать админов из db в базу данных сайта</button>
				<br>
				<br>
				<b>Экспорт</b>
				<div class="bs-callout bs-callout-info mt-10 fs-14">
				Если была утеряна по каким-либо причинам база админов в бансе, то можно экспортировать админов из базы данных сайта в базу банса
				</div>
				<button class="btn2 mt-5" onclick="export_admins();">Экспортировать админов из базы данных сайта в базу банса</button>
			</div>
			<div id="none" class="disp-n">
				<div class="bs-callout bs-callout-info mt-10 fs-14">
					Данный способ интеграции сервера не поддерживает импорта/экспорта.
				</div>
			</div>
			<div id="timing_result"></div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="block" id="add_admin_area">
			<div class="block_head">
				Выдать права
			</div>
			<script>
				function local_change_service() {
					var service = $('#store_services option:selected').val();
					get_tarifs_adm(service);
				}
				function local_change_store_type() {
					var type = $('#store_type option:selected').val();
					change_store_bind_type(type);
				}
				function local_change_admin_type(id) {
					var type = $('#store_type_'+id+' option:selected').val();
					change_admin_bind_type(type, id);
				}
			</script>

			Выберите услугу:
			<select class="form-control mb-10" id="store_services" onchange="local_change_service();"></select>

			Выберите тариф:
			<select class="form-control mb-10" id="store_tarifs"></select>

			Выберите тип привязки:
			<select class="form-control" id="store_type" onchange="local_change_store_type();">
				{if($binds[0])}<option value="1">Ник + пароль</option>{/if}
				{if($binds[1])}<option value="2">STEAM ID</option>{/if}
				{if($binds[2])}<option value="3">STEAM ID + пароль</option>{/if}
			</select>
			<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_nick" placeholder="Введите ник">
			<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_steam_id" placeholder="Введите STEAM ID">
			<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_pass" placeholder="Придумайте пароль">
			<div class="clearfix mt-10"></div>

			ID профиля на сайте:
			<input type="number" class="form-control disp-b" maxlength="5" id="player_user_id" placeholder="Введите ID">

			<div id="add_result" class="mt-10"></div>
			<div id="button">
				<button id="store_buy_btn" class="btn2 btn-big mt-10" onclick="add_admin();">Выдать</button>
				<button id="store_answer_btn" class="btn2 btn-cancel btn-big mt-10 disp-n" onclick="">Нет</button>
			</div>
		</div>
	</div>
	<div class="col-md-12">
		<div id="admins">
			<center><img src="{site_host}templates/admin/img/loader.gif"></center>
		</div>
		<script>load_servers_admins();</script>
	</div>
</div>

<script>
	function server_change() {
		var server = $('#store_server').val();
		location.href = '../admin/admins?server='+server;
	}
	get_services_adm({server});
	timing_serv_type({server_type});
	local_change_store_type();
</script>
<script src="{site_host}templates/admin/js/timepicker/timepicker.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script src="{site_host}templates/admin/js/timepicker/jquery-ui-sliderAccess.js"></script>