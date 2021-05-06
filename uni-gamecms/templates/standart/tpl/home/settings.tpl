<div class="col-lg-9 order-is-first profile-settings">
	<div class="row">
		<div class="col-lg-6">
			<div class="block">
				<div class="block_head">
					Редактирование логина
				</div>
				<div class="input-group">
					<div class="input-group-prepend">
						<button class="btn btn-outline-primary" type="button" onclick="edit_user_login();">Изменить</button>
					</div>
					<input type="text" class="form-control" id="user_login" maxlength="15" autocomplete="off" value="{login}" placeholder="Введите логин">
				</div>
				<div id="edit_user_login_result"></div>
			</div>

			<div class="block">
				<div class="block_head">
					Редактирование пароля
				</div>
				{if($user->password == 'none')}
				<div class="form-group">
					<label>
						<h4>
							Новый пароль
						</h4>
					</label>
					<input type="password" class="form-control" id="first_user_password" maxlength="15" autocomplete="off" placeholder="Введите новый пароль">
				</div>

				<div class="form-group">
					<label>
						<h4>
							Повторите
						</h4>
					</label>
					<input type="password" class="form-control" id="first_user_password2" maxlength="15" autocomplete="off" placeholder="Повторите новый пароль">
				</div>

				<button class="btn btn-outline-primary" type="button" onclick="edit_first_user_password();">Изменить</button>
				<div id="edit_first_user_password_result"></div>

				{else}

				<div class="form-group">
					<label>
						<h4>
							Текущий пароль
						</h4>
					</label>
					<input type="password" class="form-control" id="user_old_password" maxlength="15" autocomplete="off" placeholder="Введите текущий пароль">
				</div>

				<div class="form-group">
					<label>
						<h4>
							Новый пароль
						</h4>
					</label>
					<input type="password" class="form-control" id="user_password" maxlength="15" autocomplete="off" placeholder="Введите новый пароль">
				</div>

				<div class="form-group">
					<label>
						<h4>
							Повторите
						</h4>
					</label>
					<input type="password" class="form-control" id="user_password2" maxlength="15" autocomplete="off" placeholder="Повторите новый пароль">
				</div>

				<button class="btn btn-outline-primary" type="button" onclick="edit_user_password();">Изменить</button>
				<div id="edit_user_password_result"></div>
				{/if}
			</div>

			<div class="block">
				<div class="block_head">
					Редактирование аватара
				</div>
				<div class="row">
					<div class="col-4">
						<img id="avatar" src="{avatar}" class="w-100 mb-1">
					</div>
					<div class="col-8">
						<form enctype="multipart/form-data" id="edit_user_avatar_form">
							<input type="hidden" id="token" name="token" value="{token}">
							<input type="hidden" id="edit_user_avatar" name="edit_user_avatar" value="1">
							<input type="hidden" id="phpaction" name="phpaction" value="1">
							<input type="file" id="user_avatar" accept="image/*" name="user_avatar"/>
							<input class="btn btn-outline-primary" type="submit" value="Загрузить">
							<div id="edit_user_avatar_result"></div>
						</form>
					</div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Дополнительные настройки
				</div>
				<div class="form-group">
					<label>
						<h4>
							Личные сообщения могут писать
						</h4>
					</label>
					<div class="btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-default {im_radio_1}" onclick="on_im(1);">
							<input type="radio">
							Все
						</label>

						<label class="btn btn-default {im_radio_2}" onclick="on_im(2);">
							<input type="radio">
							Друзья
						</label>
					</div>
				</div>
				<div class="form-group">
					<label>
						<h4>
							Привязка cookies к ip адресу
						</h4>
					</label>
					<div class="btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-default {protect_radio_1}" onclick="on_ip_protect(1);">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {protect_radio_2}" onclick="on_ip_protect(2);">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="form-group">
					<label>
						<h4>
							Уведомления на почту
						</h4>
					</label>
					<div class="btn-group-toggle" data-toggle="buttons">
						<label class="btn btn-default {notice_radio_1}" onclick="on_email_notice(1);">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {notice_radio_2}" onclick="on_email_notice(2);">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Редактирование подписи
				</div>
				<textarea id="signature" maxlength="500">{signature}</textarea>
				<button id="send_btn" class="btn btn-primary mt-3" type="button" onclick="edit_signature();">Изменить</button>
				<div id="edit_signature_result"></div>
			</div>

			<div class="block">
				<div class="block_head">
					Черный список
				</div>

				<button class="btn btn-outline-primary w-100 mb-0"
				        data-target="#black-list"
				        data-toggle="modal"
				        onclick="getBlackList();">Открыть список</button>

				<script>$('#black-list').modal('hide');</script>
				<div id="black-list" class="modal fade">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
								<h4 class="modal-title">Черный список</h4>
							</div>
							<div class="modal-body">
								<div class="table-responsive mb-0">
									<table class="table table-bordered">
										<thead>
										<tr>
											<td>#</td>
											<td>Профиль</td>
											<td>Действие</td>
										</tr>
										</thead>
										<tbody id="black-list-content">
										<tr>
											<td colspan="10">
												<div class="loader"></div>
											</td>
										</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<div class="block">
				<div class="block_head">
					Редактирование профиля
				</div>

				<div class="form-group">
					<label>
						<h4>
							Имя
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_name();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_name" maxlength="30" autocomplete="off" value="{name}" placeholder="Введите свое имя">
					</div>
					<div id="edit_user_name_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Ник на сервере
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_nick();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_nick" maxlength="30" autocomplete="off" value="{nick}" placeholder="Введите свой ник">
					</div>
					<div id="edit_user_nick_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Steam ID
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_steam_id();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_steam_id" maxlength="32" autocomplete="off" value="{steam_id}" placeholder="Введите свой Steam ID">
					</div>
					<div id="edit_user_steam_id_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Дата рождения
						</h4>
					</label>
					<div class="input-group editing-date">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_birth();">Изменить</button>
						</div>
						<select class="form-control" id="birth_day">{birth_day}</select>
						<select class="form-control" id="birth_month">{birth_month}</select>
						<select class="form-control" id="birth_year">{birth_year}</select>
					</div>
					<div id="edit_user_birth_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Скайп
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_skype();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_skype" maxlength="32" autocomplete="off" value="{skype}" placeholder="Введите свой логин в скайпе">
					</div>
					<div id="edit_user_skype_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Telegram
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_telegram();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_telegram" maxlength="50" autocomplete="off" value="{telegram}" placeholder="Введите свой логин в Телеграме">
					</div>
					<div id="edit_user_telegram_result"></div>
				</div>

				<div class="form-group">
					<label>
						<h4>
							Discord
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_discord();">
								Изменить
							</button>
						</div>
						<input type="text"
						       class="form-control"
						       id="user_discord"
						       maxlength="32"
						       autocomplete="off"
						       value="{discord}"
						       placeholder="Введите свой логин в Discord">
					</div>
					<div id="edit_user_discord_result"></div>
				</div>
			</div>

			<div class="block" id="vk_area">
				<div class="block_head">
					Аккаунт Вконтакте
				</div>

				{if($auth_api->vk_api == '2')}
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_vk();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_vk" maxlength="30" autocomplete="off" value="{vk}" placeholder="Введите свой ID в Вконтакте">
					</div>
					<div id="edit_user_vk_result"></div>
				{else}
					{if($user->vk_api == '0')}
						<div class="noty-block">
							Если Ваш профиль будет прикреплен к аккаунту VK, то Вы сможете авторизовываться на сайте в один клик по кнопке "Войти через VK".
						</div>
						<a class="btn btn-outline-primary" id="vk_link" href="">Прикрепить профиль к VK</a><br>
						<script>attach_user_vk();</script>
					{else}
						<div class="noty-block info">
							<a target="_blank" href="https://vk.com/{vk}" id="vk_user">
								<img src="../files/avatars/no_avatar.jpg" alt="">
								<span>Загрузка...</span>
							</a>
							<script>get_vk_profile_info('{vk_api}', '#vk_user img', '#vk_user span', '{vk}');</script>
						</div>
						<button class="btn btn-outline-primary" type="button" onclick="unset_vk();">Открепить профиль</button>
						<div id="unset_vk_result"></div>
					{/if}

					{conf_mess}
				{/if}
			</div>

			{if($auth_api->steam_api != '2')}
			<div class="block" id="steam_area">
				<div class="block_head">
					Аккаунт Steam
				</div>

				{if('{steam_api}' == '0')}
				<div class="noty-block">
					Если Ваш профиль будет прикреплен к аккаунту Steam, то Вы сможете авторизовываться на сайте в один клик по кнопке "Войти через Steam".
				</div>
				<a class="btn btn-outline-primary" id="steam_link" href="">Прикрепить профиль к Steam</a><br>
				<script>attach_user_steam();</script>
				{else}
				<div class="noty-block info">
					<a target="_blank" href="https://steamcommunity.com/profiles/{steam_api}/" id="steam_user">
						<img src="../files/avatars/no_avatar.jpg" alt="">
						<span>Загрузка...</span>
					</a>
					<script>get_user_steam_info('{steam_api}');</script>
				</div>
				<button class="btn btn-outline-primary" type="button" onclick="unset_steam();">Открепить профиль</button>
				<div id="unset_steam_result"></div>
				{/if}

				{conf_mess2}
			</div>
			{/if}

			<div class="block" id="fb_area">
				<div class="block_head">
					Аккаунт Facebook
				</div>

				<div class="form-group">
					<label>
						<h4>
							ID
						</h4>
					</label>
					<div class="input-group">
						<div class="input-group-prepend">
							<button class="btn btn-outline-primary" type="button" onclick="edit_user_fb();">Изменить</button>
						</div>
						<input type="text" class="form-control" id="user_fb" maxlength="20" autocomplete="off" value="{fb}" placeholder="Введите свой ID в Facebook">
					</div>
					<div id="edit_user_fb_result"></div>
				</div>

				{if($auth_api->fb_api == '1')}
					{if($user->fb_api == '0')}
						<div class="noty-block">
							Если Ваш профиль будет прикреплен к аккаунту Facebook, то Вы сможете авторизовываться на сайте в один клик по кнопке "Войти через Facebook".
						</div>
						<a class="btn btn-outline-primary" id="fb_link" href="">Прикрепить профиль к Facebook</a><br>
						<script>attach_user_fb();</script>
					{else}
						<div class="noty-block info">
							<a target="_blank" href="#" id="fb_user">
								<img src="../files/avatars/no_avatar.jpg" alt="">
								<span>Загрузка...</span>
							</a>
							<script> get_fb_profile_info('{fb_api}', '{fb}', '#fb_user', '#fb_user img', '#fb_user span'); </script>
						</div>
						<button class="btn btn-outline-primary" type="button" onclick="unset_fb();">Открепить профиль</button>
						<div id="unset_fb_result"></div>
					{/if}

					{conf_mess3}
				{/if}
			</div>

			{if('{referral_program}' == '1')}
			<div class="block block-table">
				<div class="block_head">
					Реферальная программа
				</div>
				<div class="noty-block info">
					Пользователи, зарегистрированные на сайте по вашей ссылке, будут являться Вашими рефералами. При пополнении своего баланса рефералом, Вы будете получать на Ваш баланс {referral_percent}% от суммы его пополнения.
				</div>
				<div class="table-responsive mb-0">
					<table class="table table-bordered">
						<tr>
							<td colspan="2">
								Ваша уникальная ссылка: <b>{referral_link}</b>
							</td>
						</tr>
						<tr>
							<td>
								<button class="btn btn-outline-primary w-100" data-target="#referrals" data-toggle="modal" onclick="get_referrals();">Мои рефералы</button>
							</td>
							<td>
								<button class="btn btn-outline-primary w-100" data-target="#profit" data-toggle="modal" onclick="get_ref_profit();">Моя прибыль</button>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<script>$('#referrals').modal('hide');</script>
			<div id="referrals" class="modal fade">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">Рефералы</h4>
						</div>
						<div class="modal-body">
							<div class="table-responsive mb-0">
								<table class="table table-bordered">
									<thead>
										<tr>
											<td>#</td>
											<td>Профиль</td>
											<td>Дата регистрации</td>
											<td>Прибыль</td>
										</tr>
									</thead>
									<tbody id="referrals_body">
										<tr>
											<td colspan="10">
												<div class="loader"></div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script>$('#profit').modal('hide');</script>
			<div id="profit" class="modal fade">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">Прибыль</h4>
						</div>
						<div class="modal-body">
							<div class="table-responsive mb-0">
								<table class="table table-bordered">
									<thead>
										<tr>
											<td>#</td>
											<td>Реферал</td>
											<td>Сумма</td>
											<td>Дата</td>
										</tr>
									</thead>
									<tbody id="profit_body">
										<tr>
											<td colspan="10">
												<div class="loader"></div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			{/if}
		</div>
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
			url: "ajax/actions_a.php",
			data: data,
			contentType: false,
			processData: false,
		}).done(function (html) {
			$("#edit_user_avatar_result").empty();
			$("#edit_user_avatar_result").append(html);
			$('#edit_user_avatar_form')[0].reset();
		});
		NProgress.done();
	});
</script>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar.tpl"}
</div>