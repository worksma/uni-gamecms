<div class="col-lg-9 order-is-first">
	<div class="block" id="add_admin_area">
		<div class="block_head">
			Выдать права
		</div>

		<div class="form-group">
			<label>
				<h4>
					Выберите услугу
				</h4>
			</label>
			<select class="form-control mb-10" id="store_services" onchange="local_change_service();"></select>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Выберите тариф
				</h4>
			</label>
			<select class="form-control mb-10" id="store_tarifs"></select>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Выберите тип привязки
				</h4>
			</label>
			<select class="form-control" id="store_type" onchange="local_change_store_type();">
				{if($binds[0])}<option value="1">Ник + пароль</option>{/if}
				{if($binds[1])}<option value="2">STEAM ID</option>{/if}
				{if($binds[2])}<option value="3">STEAM ID + пароль</option>{/if}
			</select>
		</div>

		<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_nick" placeholder="Введите ник">
		<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_steam_id" placeholder="Введите STEAM ID">
		<input type="text" class="form-control mt-10 disp-n" maxlength="32" id="player_pass" placeholder="Придумайте пароль">
		<div class="clearfix mt-3"></div>

		<div class="form-group">
			<label>
				<h4>
					ID профиля на сайте
				</h4>
			</label>
			<input type="number" class="form-control disp-b" maxlength="5" id="player_user_id" placeholder="Введите ID">
		</div>

		<div id="add_result" class="mt-1"></div>
		<div id="button" class="mt-3">
			<button id="store_buy_btn" class="btn btn-primary" onclick="add_admin();">Выдать</button>
			<button id="store_answer_btn" class="btn btn-outline-primary disp-n" onclick="">Нет</button>
		</div>
	</div>

	<div id="admins">
		<div class="loader"></div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<div class="block_head">
			Выберите сервер
		</div>
		<select class="form-control" onchange="server_change();" id="store_server">
			{servers}
		</select>
	</div>

	{include file="/home/navigation.tpl"}
</div>

<script>
	function server_change() {
		location.href = '../edit_admins?server='+$('#store_server').val();
	}

	get_services_adm({server});
	local_change_store_type();
	load_servers_admins();

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

<script type="text/javascript" src="{site_host}templates/admin/js/timepicker/timepicker.js"></script>
<script type="text/javascript" src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="{site_host}templates/admin/js/timepicker/jquery-ui-timepicker-addon-i18n.min.js"></script>
<script type="text/javascript" src="{site_host}templates/admin/js/timepicker/jquery-ui-sliderAccess.js"></script>