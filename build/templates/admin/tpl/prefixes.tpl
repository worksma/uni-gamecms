<div class="page">
	<div class="col-md-12">
		<div class="block">
			<div class="block_head">Выберите сервер</div>

			<select class="form-control" id="id_serv" onchange="getTerm(); getSpeech();">
				{servers}
			</select>
		</div>
	</div>

	<div class="col-md-6">
		<div class="block">
			<div class="block_head">Метод привязки</div>
			
			<div class="btn-group" data-toggle="buttons">
				<label class="btn btn-default btn-sm" id="bind_nick_pass_btn" for="bind_nick_pass">
					<input type="checkbox" id="bind_nick_pass" autocomplete="off"> Ник + пароль
				</label>
				
				<label class="btn btn-default btn-sm" id="bind_steam_btn" for="bind_steam">
					<input type="checkbox" id="bind_steam" autocomplete="off"> STEAM ID
				</label>
				
				<label class="btn btn-default btn-sm" id="bind_steam_pass_btn" for="bind_steam_pass">
					<input type="checkbox" id="bind_steam_pass" autocomplete="off"> STEAM ID + пароль
				</label>
			</div>
			
			<script>
				{if('{bind_nick_pass}' == '1')}
					$("#bind_nick_pass").prop("checked", true);
					$("#bind_nick_pass_btn").addClass("active");
				{/if}
				
				{if('{bind_steam}' == '1')}
					$("#bind_steam").prop("checked", true);
					$("#bind_steam_btn").addClass("active");
				{/if}
				
				{if('{bind_steam_pass}' == '1')}
					$("#bind_steam_pass").prop("checked", true);
					$("#bind_steam_pass_btn").addClass("active");
				{/if}
				
				$(function() {
					$("#bind_nick_pass").bind("change", function() {
						if($(this).is(':checked')) {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_nick_pass',
								value: '1'
							}), (result) => toasty(result.alert, result.message));
						}
						else {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_nick_pass',
								value: '0'
							}), (result) => toasty(result.alert, result.message));
						}
					});
					
					$("#bind_steam").bind("change", function() {
						if($(this).is(':checked')) {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_steam',
								value: '1'
							}), (result) => toasty(result.alert, result.message));
						}
						else {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_steam',
								value: '0'
							}), (result) => toasty(result.alert, result.message));
						}
					});
					
					$("#bind_steam_pass").bind("change", function() {
						if($(this).is(':checked')) {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_steam_pass',
								value: '1'
							}), (result) => toasty(result.alert, result.message));
						}
						else {
							send_post(get_url() + "ajax/actions_panel.php", serializeform(new FormData, {
								send_prefixes_conf: '1',
								type: 'bind_steam_pass',
								value: '0'
							}), (result) => toasty(result.alert, result.message));
						}
					});
				});
			</script>
		</div>
	
		<div class="block">
			<div class="block_head">Запрещенные слова</div>

			<div class="input-group">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="addSpeech();">Добавить</button>
				</span>
				<input class="form-control mb-10" type="text" id="speech" placeholder="Запрещенное слово" autocomplete="off">
			</div>

			<table class="table table-bordered table-condensed mb-0">
				<thead>
					<tr>
						<td>#</td>
						<td>Слово</td>
						<td>Действие</td>
					</tr>
				</thead>
				<tbody id="speech_ban">
					<tr class="text-center"><td colspan="3">Список чист</td></tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="col-md-6">
		<div class="block">
			<div class="block_head">Добавление тарифа</div>

			<input class="form-control mt-10" type="text" maxlength="7" id="time" min="0" placeholder="Время (в днях, 0 - навсегда)" autocomplete="off">
			<input class="form-control mt-10" type="number" maxlength="6" min="0" id="price" placeholder="Цена покупки" autocomplete="off">
			<input class="form-control mt-10" type="number" maxlength="2" min="0" max="99" id="discount" placeholder="Скидка (в % от 0 до 99)" autocomplete="off">
			<input class="form-control mt-10" type="text" id="rcon" placeholder="RCON команда для выполнения" autocomplete="off">
			<div class="bs-callout bs-callout-info mt-10">
				<p class="mb-0">
					В случае если требуется отправить дополнительные данные на сервер, можно воспользоваться следующими параметрами:<br><br>
					{identifier} - SteamID/Nickname<br>
					{password} - Пароль<br>
					{time} - срок аренды (в днях)<br>
					{prefix} - префикс, который указал пользователь<br><br>
					Пример команды: amx_give_prefix {identifier} {time} {prefix}
				</p>
			</div>
			<button class="btn2 mt-10" onclick="addTerm();">Добавить</button>
		</div>
	</div>

	<div class="col-md-12">
		<div class="block">
			<div class="block_head">Тарифы сервера</div>
			<table class="table table-bordered table-condensed mb-0">
				<thead>
					<tr>
						<td>#</td>
						<td>Время</td>
						<td>Цена покупки</td>
						<td>Скидка</td>
						<td>RCON</td>
						<td>Действие</td>
					</tr>
				</thead>
				<tbody id="term_list">
					<tr class="text-center"><td colspan="6">Нет тарифов</td></tr>
				</tbody>
			</table>
		</div>
	</div>
</div>