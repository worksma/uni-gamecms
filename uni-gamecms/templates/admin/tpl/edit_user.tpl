<div class="page">
	<div class="row">
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Очистка / удаление
				</div>
				<div class="input-group w-100">
					<span class="input-group-btn">
						<button onclick="dell_user({id}, 1);" class="btn btn-default" type="button">Выполнить</button>
					</span>
					<span class="input-group-btn w-100">
						<select id="clear_type" class="form-control">
							<option value="2">Очистить активность пользователя</option>
							<option value="3">Удалить все сообщения из чата</option>
							<option value="4">Удалить все сообщения и темы с форума</option>
							<option value="5">Удалить все комментарии</option>
							<option value="1">Удалить пользователя</option>
						</select>
					</span>
				</div>
			</div>
			<div class="block">
				<div class="block_head">
					Редактирование профиля
				</div>
				<b>Группа</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_group({id});">Изменить</button>
					</span>
					<select id="user_group" class="form-control">
                        {user_groups}
					</select>
				</div>
				<br>

				<b>Мут</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_gag({id});">Изменить</button>
					</span>
					<select id="user_gag" class="form-control">
						<option value="1" {if('{gag}' == '1')}selected{/if}>Включен</option>
						<option value="2" {if('{gag}' == '2')}selected{/if}>Выключен</option>
					</select>
				</div>
				<br>

				<b>Логин</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_login({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_login" maxlength="30" autocomplete="off" value="{login}">
				</div>
				<div id="login_result"></div>
				<br>

				<b>Пароль</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_password({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_password" maxlength="15" autocomplete="off" value="">
				</div>
				<div id="password_result"></div>
				<br>

				<b>Адрес страницы</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="editUserRouteByAdmin({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_route" maxlength="32" autocomplete="off" value="{route}">
				</div>
				<div id="route_result"></div>
				<br>

				<b>Имя</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_name({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_name" maxlength="15" autocomplete="off" value="{name}">
				</div>
				<br>

				<b>Ник на сервере</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_nick({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_nick" maxlength="30" autocomplete="off" value="{nick}">
				</div>
				<br>

				<b>Steam ID</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_steam_id({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_steam_id" maxlength="32" autocomplete="off" value="{steam_id}">
				</div>
				<br>

				<b>Дата рождения</b>
				<div class="input-group">
					День: <select class="w-60 h-34" id="birth_day">{birth_day}</select>
					Месяц: <select class="w-60 h-34" id="birth_month">{birth_month}</select>
					Год: <select class="w-60 h-34" id="birth_year">{birth_year}</select>

					<button class="btn btn-default mt--3" type="button" onclick="admin_change_birth({id});">Изменить</button>
				</div>
				<br>

				<b>Скайп</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_skype({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_skype" maxlength="32" autocomplete="off" value="{skype}" placeholder="Введите логин скайпа">
				</div>
				<br>

				<b>Телеграм</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_telegram({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_telegram" maxlength="50" autocomplete="off" value="{telegram}" placeholder="Введите логин телеграма">
				</div>
				<br>

				<b>Discord</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_discord({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_discord" maxlength="50" autocomplete="off" value="{discord}" placeholder="Введите логин Discord">
				</div>
				<br>

				<b>ID Вконтакте</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_vk({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_vk" maxlength="15" autocomplete="off" value="{vk}" placeholder="Введите ID в Вконтакте">
				</div>
				<br>

				<b>ID Facebook</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_fb({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_fb" maxlength="20" autocomplete="off" value="{fb}" placeholder="Введите ID в Facebook">
				</div>
				<br>

				<b>E-mail</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="admin_change_email({id});">Изменить</button>
					</span>
					<input type="text" class="form-control" id="user_email" maxlength="255" autocomplete="off" value="{email}" placeholder="Введите email">
				</div>
				{if("{active}" == "0")}
					<div id="activate_user">
						<p>E-mail не подтвержден, пользователь не активирован! <a class="c-p" onclick="admin_activate_user({id});">Активировать</a></p>
					</div>
				{/if}
				<br>

				<b>Аватар</b>
				<div class="row">
					<div class="col-md-3">
						<img id="avatar" src="../{avatar}" class="w-100">
					</div>
					<div class="col-md-9">
						<div class="input-group">
							<form enctype="multipart/form-data" action="ajax/actions_a.php" method="POST" id="edit_user_avatar_form">
								<input type="hidden" id="token" name="token" value="{token}">
								<input type="hidden" id="id" name="id" value="{id}">
								<input type="hidden" id="admin_change_avatar" name="admin_change_avatar" value="1">
								<input type="hidden" id="phpaction" name="phpaction" value="1">
								<input class="input-file" type="file" id="user_avatar" accept="image/*" name="user_avatar"/>
								<input class="btn btn-default mt-5" type="submit" value="Загрузить">
								<img class="disp-n" id="edit_user_loader" src="{site_host}templates/admin/img/loader.gif">
								<div id="edit_user_avatar_result"></div>
							</form>
						</div>
					</div>
				</div>
				<br>

				<b>Подпись</b>
				<div class="input-group">
					<textarea id="signature" class="form-control maxMinW100" maxlength="500">{signature}</textarea>
					<input id="send_btn" class="btn btn-default mt-5" type="button" onclick="admin_change_signature({id});" value="Отправить">
				</div>
			</div>

			<script>
				$(document).ready(function() {
					init_tinymce('signature', '{{md5($conf->code)}}', 'lite');
				});
				$("#edit_user_avatar_form").submit(function (event){
					event.preventDefault();
					var data = new FormData($('#edit_user_avatar_form')[0]);
					$.ajax({
						type: "POST",
						url: "../ajax/actions_z.php",
						data: data,
						contentType: false,
						processData: false,
						beforeSend: function() {
							$('#edit_user_loader').show();
						}
					}).done(function (html) {
						$("#edit_user_avatar_result").empty();
						$("#edit_user_avatar_result").append(html);
						$('#edit_user_loader').hide();
						$('#edit_user_avatar_form')[0].reset();
					});
				});
			</script>
		</div>
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Информация
				</div>
				<table class="table table-striped table-bordered mb-0 v-m">
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

			<div class="block">
				<div class="block_head">
					Другие аккаунты пользователя
				</div>

				<div class="bs-callout bs-callout-info mt-10 mb-10">
					<h5>Внимание!</h5>
					<p>Представленная информация не дает 100%-й гарантии, что данные аккаунты являются мульти-аккаунтами!</p>
				</div>

				<table class="table table-striped table-bordered mb-0">
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
										<a href="../admin/edit_user?id={{$multi_accounts[$i][0]}}" target="_blank">{{$multi_accounts[$i][2]}}</a>
										<span class="glyphicon glyphicon-remove c-p fs-12" onclick="dell_multi_account_relation({id}, {{$multi_accounts[$i][0]}}); dell_block('multi-account-{{$multi_accounts[$i][0]}}');" title="Очистить связь с профилем"></span>
										<span class="glyphicon glyphicon-trash c-p fs-12" onclick="dell_user({{$multi_accounts[$i][0]}}, 2, 1); dell_block('multi-account-{{$multi_accounts[$i][0]}}');" title="Удалить профиль"></span>
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

			<div class="block">
				<div class="block_head">
					Денежные операции пользователя
				</div>
				<div class="table-responsive mb-0 mt-10">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>Сумма</td>
								<td>Тип</td>
								<td>Дата</td>
							</tr>
						</thead>
						<tbody id="operations">
							<tr><td colspan="4"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
							<script>get_user_shilings_operations({id});</script>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>