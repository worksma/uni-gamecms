<div class="col-lg-6">
	{if(is_worthy("g"))}
	<div class="block">
		<div class="block_head">
			Очистка / удаление
		</div>
		<div class="input-group">
			<div class="input-group-prepend">
				<button onclick="dell_user({id}, 1);" class="btn btn-outline-primary" type="button">Выполнить</button>
			</div>
			<select id="clear_type" class="form-control">
				<option value="2">Очистить активность пользователя</option>
				<option value="3">Удалить все сообщения из чата</option>
				<option value="4">Удалить все сообщения и темы с форума</option>
				<option value="5">Удалить все комментарии</option>
				<option value="1">Удалить пользователя</option>
			</select>
		</div>
	</div>
	{/if}
	<div class="block">
		<div class="block_head">
			Редактирование профиля
		</div>
		{if(!in_array("{id}", $admins))}
		<div class="form-group">
			<label>
				<h4>
					Группа
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_group({id});">Изменить</button>
				</div>
				<select id="user_group" class="form-control">
					{user_groups}
				</select>
			</div>
		</div>
		{/if}

		<div class="form-group">
			<label>
				<h4>
					Мут
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_gag({id});">Изменить</button>
				</div>
				<select id="user_gag" class="form-control">
					<option value="1" {if('{gag}' == '1')}selected{/if}>Включен</option>
					<option value="2" {if('{gag}' == '2')}selected{/if}>Выключен</option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Логин
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_login({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_login" maxlength="30" autocomplete="off" value="{login}">
			</div>
			<div id="login_result"></div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Пароль
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_password({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_password" maxlength="15" autocomplete="off" value="">
			</div>
			<div id="password_result"></div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Адрес страницы
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="editUserRouteByAdmin({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_route" maxlength="32" autocomplete="off" value="{route}">
			</div>
			<div id="route_result"></div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Имя
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_name({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_name" maxlength="15" autocomplete="off" value="{name}">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Ник на сервере
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_nick({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_nick" maxlength="30" autocomplete="off" value="{nick}">
			</div>
		</div>

		<!--
		<div class="form-group">
			<label>
				<h4>
					Префикс в серверном чате
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_prefix({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_prefix" maxlength="16" autocomplete="off" value="{{$profile->prefix}}">
			</div>
		</div>
		-->

		<div class="form-group">
			<label>
				<h4>
					Steam ID
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_steam_id({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_steam_id" maxlength="32" autocomplete="off" value="{steam_id}">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Дата рождения
				</h4>
			</label>
			<div class="input-group editing-date">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_birth({id});">Изменить</button>
				</div>
				<select class="form-control" id="birth_day">{birth_day}</select>
				<select class="form-control" id="birth_month">{birth_month}</select>
				<select class="form-control" id="birth_year">{birth_year}</select>
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Скайп
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_skype({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_skype" maxlength="32" autocomplete="off" value="{skype}" placeholder="Введите логин скайпа">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Телеграм
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_telegram({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_telegram" maxlength="50" autocomplete="off" value="{telegram}" placeholder="Введите логин телеграма">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					Discord
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_discord({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_discord" maxlength="50" autocomplete="off" value="{discord}" placeholder="Введите логин Discord">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					ID Вконтакте
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_vk({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_vk" maxlength="15" autocomplete="off" value="{vk}" placeholder="Введите ID в Вконтакте">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					ID Facebook
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_fb({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_fb" maxlength="20" autocomplete="off" value="{fb}" placeholder="Введите ID в Facebook">
			</div>
		</div>

		<div class="form-group">
			<label>
				<h4>
					E-mail
				</h4>
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<button class="btn btn-outline-primary" type="button" onclick="admin_change_email({id});">Изменить</button>
				</div>
				<input type="text" class="form-control" id="user_email" maxlength="255" autocomplete="off" value="{email}" placeholder="Введите email">
			</div>
			{if("{active}" == "0")}
			<div class="noty-block" id="activate_user">
				<p>E-mail не подтвержден, пользователь не активирован! <a href="#" onclick="admin_activate_user({id});">Активировать</a></p>
			</div>
			{/if}
		</div>

		<div class="form-group mb-1">
			<label>
				<h4>
					Аватар
				</h4>
			</label>
			<div class="row">
				<div class="col-3">
					<img id="avatar" src="../{avatar}" class="w-100 mb-1">
				</div>
				<div class="col-9">
					<form enctype="multipart/form-data" id="edit_user_avatar_form">
						<input type="hidden" id="token" name="token" value="{token}">
						<input type="hidden" id="id" name="id" value="{id}">
						<input type="hidden" id="admin_change_avatar" name="admin_change_avatar" value="1">
						<input type="hidden" id="phpaction" name="phpaction" value="1">

						<input type="file" id="user_avatar" accept="image/*" name="user_avatar"/><br>
						<input class="btn btn-outline-primary" type="submit" value="Загрузить">

						<div id="edit_user_avatar_result"></div>
					</form>
				</div>
			</div>
		</div>
		<br>

		<div class="form-group">
			<label>
				<h4>
					Подпись
				</h4>
			</label>
			<textarea id="signature" maxlength="500">{signature}</textarea>
			<input id="send_btn" class="btn btn-outline-primary mt-3" type="button" onclick="admin_change_signature({id});" value="Отправить">
		</div>
	</div>

	<script>
		$(document).ready(function() {
			init_tinymce("signature", "lite", "{file_manager_theme}", "", "");
		});
		$("#edit_user_avatar_form").submit(function (event){
			NProgress.start();
			event.preventDefault();
			var data = new FormData($('#edit_user_avatar_form')[0]);
			$.ajax({
				type: "POST",
				url: "../ajax/actions_z.php",
				data: data,
				contentType: false,
				processData: false,
			}).done(function (html) {
				$("#edit_user_avatar_result").empty();
				$("#edit_user_avatar_result").append(html);
				$('#edit_user_avatar_form')[0].reset();
				NProgress.done();
			});
		});
	</script>
</div>
<div class="col-lg-6">
	<div class="block block-table">
		<div class="block_head">
			Информация
		</div>
		<div class="table-responsive mb-0">
			<table class="table table-striped table-bordered">
				<tr>
					<td width="45%;">Профиль</td>
					<td><a href="../profile?id={id}" target="_blank"><b>{login}</b></a></td>
				</tr>
				<tr>
					<td>Дата регистрации</td>
					<td>{regdate}</td>
				</tr>
				<tr>
					<td>Уведомления на почту</td>
					<td>
						{if('{email_notice}'=='1')}
							<p class="text-success mb-0">Включено</p>
						{else}
							<p class="text-danger mb-0">Выключено</p>
						{/if}
					</td>
				</tr>
				<tr>
					<td>Доступ к лс</td>
					<td>
						{if('{im}'=='1')}
							<p class="text-success mb-0">Всем</p>
						{else}
							<p class="text-danger mb-0">Только друзья</p>
						{/if}
					</td>
				</tr>
				<tr>
					<td>Приглашен</td>
					<td>
						{if('{invited}'=='0')}
							Не является рефералом
						{else}
							<a href="../profile?id={invited}" target="_blank"><b>{invited_login}</b></a>
						{/if}
					</td>
				</tr>
				<tr>
					<td>Прибыль с пользователя</td>
					<td><b>{shilings}</b></td>
				</tr>
				<tr>
					<td>Последний IP</td>
					<td>
						{if('{ip}'=='127.0.0.1')}
							Неизвестно
						{else}
							{ip}
						{/if}
					</td>
				</tr>
				{if('{ip}'!='127.0.0.1')}
					<td>Местоположение</td>
					<td id="place">
						Неизвестно
					</td>
					<script>
						$.getJSON('//api.sypexgeo.net/json/{ip}', function(resp){
							$('#place').html(resp.country.name_ru+', '+resp.region.name_ru+', '+resp.city.name_ru);
						});
					</script>
				{/if}
			</table>
		</div>
	</div>

	<div class="block block-table">
		<div class="block_head">
			Другие аккаунты пользователя
		</div>

		<div class="noty-block info">
			<p>Представленная информация не дает 100%-й гарантии, что данные аккаунты являются мульти-аккаунтами!</p>
		</div>

		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Профиль</th>
						<th>Комментарий</th>
					</tr>
				</thead>
				<tbody>
					{if('{multi_account}' == '0')}
					<tr>
						<td colspan="3">Профили не найдены</td>
					</tr>
					{else}
						{for($i=0; $i < count($multi_accounts); $i++)}
							{if($multi_accounts[$i][0] != 0)}
							<tr id="multi-account-{{$multi_accounts[$i][0]}}">
								<td>
									<a href="../edit_user?id={{$multi_accounts[$i][0]}}" target="_blank">{{$multi_accounts[$i][2]}}</a>
									<span class="m-icon icon-remove" tooltip="yes" onclick="dell_multi_account_relation({id}, {{$multi_accounts[$i][0]}}); dell_block('multi-account-{{$multi_accounts[$i][0]}}');" title="Очистить связь с профилем"></span>
									<span class="m-icon icon-trash" tooltip="yes" onclick="dell_user({{$multi_accounts[$i][0]}}, 2, 1); dell_block('multi-account-{{$multi_accounts[$i][0]}}');" title="Удалить профиль"></span>
								</td>
								<td>
									{if($multi_accounts[$i][1] == 1)}
										Совпадение по IP
									{/if}
									{if($multi_accounts[$i][1] == 2)}
										<p class="text-warning mb-0">Совпадение по ОС и информации браузера</p>
									{/if}
									{if($multi_accounts[$i][1] == 3)}
										<p class="text-danger mb-0">Совпадение по ОС, информации браузера и IP</p>
									{/if}
								</td>
							</tr>
							{/if}
						{/for}
					{/if}
				</tbody>
			</table>
		</div>
	</div>

	<div class="block block-table">
		<div class="block_head">
			Денежные операции пользователя
		</div>
		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td>Сумма</td>
						<td>Тип</td>
						<td>Дата</td>
					</tr>
				</thead>
				<tbody id="operations">
					<tr>
						<td colspan="10">
							<div class="loader"></div>	
						</td>
					</tr>
					<script>get_user_shilings_operations({id});</script>
				</tbody>
			</table>
		</div>
	</div>
</div>