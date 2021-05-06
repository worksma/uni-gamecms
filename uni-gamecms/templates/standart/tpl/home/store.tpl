<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Покупка привилегий
		</div>
		{if('{servers}' == '0')}
		Услуг не найдено
		{else}
		<div class="row">
			{if('{discount}' != '0')}
				<div class="col-lg-12">
					<div class="noty-block success">
						<h4>Внимание! Действует скидка!</h4>
						На все услуги действует скидка в размере {discount}%
					</div>
				</div>
			{/if}
			<div class="col-lg-6" id="buy_service_area">
				<script>
				function local_change_serv() {
					var server = $('#store_server option:selected').val();
					get_services(server);
					get_server_store(server);
				}
				function local_change_service() {
					var service = $('#store_services option:selected').val();
					get_tarifs(service);
				}
				function local_change_type() {
					var type = $('#store_type option:selected').val();
					change_store_bind_type(type);
				}
				</script>

				<div class="form-group">
					<label>
						<h4>
							Выберите сервер
						</h4>
					</label>
					<select class="form-control" id="store_server" onchange="local_change_serv();">
						{servers}
					</select>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Выберите услугу
						</h4>
					</label>
					<select class="form-control" id="store_services" onchange="local_change_service();"></select>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Выберите тариф
						</h4>
					</label>
					<select class="form-control" id="store_tarifs"></select>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Выберите тип привязки
						</h4>
					</label>
					<select class="form-control" id="store_type" onchange="local_change_type();">
						<option value="0">Выберите способ привязки</option>
						<option value="1">Ник + пароль</option>
						<option value="2">STEAM ID</option>
						<option value="3">STEAM ID + пароль</option>
					</select>

					<input type="text" class="form-control disp-n" maxlength="32" id="player_nick" placeholder="Введите свой ник" value="{nick}">
					<input type="text" class="form-control disp-n" maxlength="32" id="player_steam_id" placeholder="Введите свой STEAM ID" value="{steam_id}">
					<input type="text" class="form-control disp-n" maxlength="32" id="player_pass" placeholder="Придумайте пароль">
				</div>

				{if(is_auth())}
				<div class="form-group">
					<label>
						<h4>
							Условия
						</h4>
					</label>

					<div class="form-check">
						<input class="form-check-input" id="store_checbox" data-status="2" type="checkbox" onclick="on_buying();">
						<label class="form-check-label" for="store_checbox">
							Я ознакомлен с <a target="_blank" href="../pages/rules">правилами</a> проекта и согласен с ними
						</label>
					</div>

					<div id="buy_result" class="mt-3"></div>
					<div id="button" class="mt-3">
						<button id="store_buy_btn" class="btn btn-primary disabled">Купить</button>
						<button id="store_answer_btn" class="btn btn-outline-primary disp-n">Нет</button>
					</div>
				</div>	
				{else}
				<div class="noty-block error">
					<p>Авторизуйтесь, чтобы приобрести услугу</p>
				</div>
				{/if}
			</div>
			<div class="col-lg-6">
				<div class="form-group">
					<label>
						<h4>
							Информация о услуге
						</h4>
					</label>
					<div id="store_service_info" class="with_code noty-block"></div>
					<div id="store_server_info" class="d-none"></div>
					<script>local_change_serv();</script>
				</div>
			</div>
		</div>
		{/if}
	</div>
</div>

<div class="col-lg-3 order-is-last">
	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{/if}
</div>