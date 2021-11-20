<div class="page">
	{if('{message}' != '')}
	<div class="col-md-12">
		<div class="bs-callout bs-callout-error">
			<h5>Важно!</h5>
			<p>{message}</p>
		</div><br>
	</div>
	{/if}

	<div id="message" class="disp-n"></div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Основные настройки</div>
			<div class="panel-body">
				<b>Название сайта</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_site_name();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="site_name" maxlength="255" autocomplete="off" value="{site_name}">
				</div>
				<div id="edit_site_name_result"></div>
				<hr>
				<b>Привязка сессий к ip адресу</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {ipp_act}" onclick="edit_ip_protect('1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {ipp_act2}" onclick="edit_ip_protect('2');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<small class="c-868686">Рекомендуется включить для большей безопасности админ центра</small>
				<hr>
				<b>Проверка токена</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {token_act}" onclick="change_value('config','token','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {token_act2}" onclick="change_value('config','token','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<small class="c-868686">Рекомендуется включить для защиты от CSRF</small>
				<hr>
				<b>Глобальный бан</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {gban_act}" onclick="change_value('config','global_ban','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {gban_act2}" onclick="change_value('config','global_ban','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="bs-callout bs-callout-info mt-10">
					<p><a target="_blank" href="https://github.com/worksma/uni-gamecms/wiki/3.-%D0%93%D0%BB%D0%BE%D0%B1%D0%B0%D0%BB%D1%8C%D0%BD%D1%8B%D0%B9-%D0%B1%D0%B0%D0%BD"><span class="glyphicon glyphicon-link"></span> Подробнее о глобальном бане</a></p>
				</div>
				<div class="clearfix"></div>
				<hr>
				<b>Ключ для отключения копирайта</b>
				<div class="input-group">
					<span class="input-group-btn">
						<a class="btn btn-default" target="_blank" href="https://worksma.ru/service-gamecms-copyright">Получить</a>
						<button class="btn btn-default" type="button" onclick="edit_copyright_key();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="copyright_key" maxlength="40" autocomplete="off" value="{copyright_key}">
				</div>
				<div class="clearfix"></div>
				<hr>
				<b>Режим разработчика</b>
				<div class="input-group">
					<div class="input-group-btn" data-toggle="buttons">
						<label class="btn btn-default {developer_mode}" onclick="developer_mode_on('1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {developer_mode2}" onclick="developer_mode_on('2');">
							<input type="radio">
							Выключить
						</label>
					</div>
					<input type="password" class="form-control" id="dev_key" maxlength="32" value="{dev_key}" placeholder="Введите ключ">
				</div>
				<div class="bs-callout bs-callout-info mt-10">
					<p><a target="_blank" href="https://github.com/worksma/uni-gamecms/wiki/%D0%A0%D0%B5%D0%B6%D0%B8%D0%BC-%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%87%D0%B8%D0%BA%D0%B0"><span class="glyphicon glyphicon-link"></span> Как получить ключ и что это такое?</a></p>
				</div>
				<input type="hidden" class="form-control" id="host" value="{host}">

				<hr>
				<b>Капча</b><br>
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {captcha_inactive}" onclick="onCaptcha();">
						<input type="radio">
						Включить
					</label>

					<label class="btn btn-default {captcha_active}" onclick="offCaptcha();">
						<input type="radio">
						Выключить
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="editCaptcha();">Изменить</button>
					</span>
					<input value="{captcha_client_key}" type="text" class="form-control" id="captcha_client_key" maxlength="256" autocomplete="off" placeholder="Клиентский ключ">
					<input value="{captcha_secret}" type="text" class="form-control" id="captcha_secret" maxlength="256" autocomplete="off" placeholder="Секретный ключ">
				</div>

				<div class="bs-callout bs-callout-info mt-10">
					<p><a target="_blank" href="https://worksma.ru/wiki/captcha"><span class="glyphicon glyphicon-link"></span> Инструкция по настройке</a></p>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>

		<script>get_main_info();</script>

		<div class="panel panel-default">
			<div class="panel-heading">Регистрация пользователей</div>
			<div class="panel-body mb-0">
				<b>Требовать подтверждение e-mail'a при регистрации</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {cact}" onclick="change_value('config','conf_us','1','1');">
							<input type="radio">
							Да
						</label>

						<label class="btn btn-default {cact2}" onclick="change_value('config','conf_us','2','1');">
							<input type="radio">
							Нет
						</label>
					</div>
				</div>
				<hr>
				<b>Сообщение о согласии с политикой конфиденциальности</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {ppact}" onclick="change_value('config','privacy_policy','1','1');">
							<input type="radio">
							Да
						</label>

						<label class="btn btn-default {ppact2}" onclick="change_value('config','privacy_policy','2','1');">
							<input type="radio">
							Нет
						</label>
					</div>
				</div>
				<hr>
				<b>Регистрация через Вконтакте</b><br>
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {vact}" onclick="change_value('config__secondary','vk_api','1','1','vk_id,vk_key,vk_service_key');">
						<input type="radio">
						Включить
					</label>

					<label class="btn btn-default {vact2}" onclick="change_value('config__secondary','vk_api','2','1');">
						<input type="radio">
						Выключить
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_vk_api();">Изменить</button>
					</span>
					<input value="{vk_id}" type="text" class="form-control" id="vk_id" maxlength="100" autocomplete="off" placeholder="ID приложения">
					<input value="{vk_key}" type="text" class="form-control" id="vk_key" maxlength="100" autocomplete="off" placeholder="Защищенный ключ">
					<input value="{vk_service_key}" type="text" class="form-control" id="vk_service_key" maxlength="100" autocomplete="off" placeholder="Сервисный ключ доступа">
				</div>
				<div id="edit_vk_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<h5>Инструкция</h5>
					<p><a target="_blank" href="https://github.com/worksma/uni-gamecms/wiki/2.-%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D1%87%D0%B5%D1%80%D0%B5%D0%B7-%D0%92%D0%9A%D0%BE%D0%BD%D1%82%D0%B0%D0%BA%D1%82%D0%B5"><span class="glyphicon glyphicon-link"></span> Нажмите для перехода к инструкции</a></p>
				</div>
				<hr>

				<div class="row">
					<div class="col-md-4">
						<b>Регистрация через Steam</b><br>
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-default {sact}" onclick="change_value('config__secondary','steam_api','1','1','steam_key');">
								<input type="radio">
								Включить
							</label>

							<label class="btn btn-default {sact2}" onclick="change_value('config__secondary','steam_api','2','1');">
								<input type="radio">
								Выключить
							</label>
						</div>
					</div>
					<div class="col-md-4">
						<b>Автозаполнение STEAM ID</b><br>
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-default {if('{auto_steam_id_fill}' == '1')} active {/if}" onclick="change_value('config__secondary','auto_steam_id_fill','1','1');">
								<input type="radio">
								Включить
							</label>

							<label class="btn btn-default {if('{auto_steam_id_fill}' == '2')} active {/if}" onclick="change_value('config__secondary','auto_steam_id_fill','2','1');">
								<input type="radio">
								Выключить
							</label>
						</div>
					</div>
					<div class="col-md-4">
						<b>Формат STEAM ID</b><br>
						<select class="form-control" onchange="change_value('config__secondary','steam_id_format',$(this).val(),'1');">
							<option value="0" {if('{steam_id_format}' == '0')} selected {/if}>
								STEAM_0:X:XXXXXX (cs1.6)
							</option>
							<option value="1" {if('{steam_id_format}' == '1')} selected {/if}>
								STEAM_1:X:XXXXXX (cs:go)
							</option>
						</select>
					</div>
				</div>

				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_steam_api();">Изменить</button>
					</span>
					<input value="{steam_key}" type="text" class="form-control" id="steam_key" maxlength="200" autocomplete="off" placeholder="Ключ">
				</div>
				<div id="edit_steam_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<h5>Инструкция</h5>
					<p><a target="_blank" href="https://github.com/worksma/uni-gamecms/wiki/7.-%D0%90%D0%B2%D1%82%D0%BE%D1%80%D0%B8%D0%B7%D0%B0%D1%86%D0%B8%D1%8F-%D1%87%D0%B5%D1%80%D0%B5%D0%B7-Steam"><span class="glyphicon glyphicon-link"></span> Нажмите для перехода к инструкции</a></p>
				</div>
				<hr>
				<b>Регистрация через facebook</b><br>
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {fbact}" onclick="change_value('config__secondary','fb_api','1','1','fb_id,fb_key');">
						<input type="radio">
						Включить
					</label>

					<label class="btn btn-default {fbact2}" onclick="change_value('config__secondary','fb_api','2','1');">
						<input type="radio">
						Выключить
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_fb_api();">Изменить</button>
					</span>
					<input value="{fb_id}" type="text" class="form-control" id="fb_id" maxlength="100" autocomplete="off" placeholder="ID приложения">
					<input value="{fb_key}" type="text" class="form-control" id="fb_key" maxlength="100" autocomplete="off" placeholder="Секрет приложения">
				</div>
				<div id="edit_fb_result"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Пагинатор</div>
			<div class="panel-body">
				<small class="c-868686">Элементов на странице всех пользователей</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="users_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице всех пользователей" value="{users_lim}">
				</div>
				<small class="c-868686">Элементов на странице банлиста</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="bans_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице банлиста" value="{bans_lim}">
				</div>
				<small class="c-868686">Элементов на странице заявок на разбан</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="bans_lim2" maxlength="3" autocomplete="off" placeholder="Элементов на странице заявок на разбан" value="{bans_lim2}">
				</div>
				<small class="c-868686">Элементов на странице мутлиста</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="muts_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице мутлиста" value="{muts_lim}">
				</div>
				<small class="c-868686">Элементов на странице каталога новостей</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="news_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице каталога новостей" value="{news_lim}">
				</div>
				<small class="c-868686">Элементов на странице статистики</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="stats_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице статистики" value="{stats_lim}">
				</div>
				<small class="c-868686">Элементов на странице жалоб</small>
				<div class="input-group w-100">
					<input type="number" class="form-control" id="complaints_lim" maxlength="3" autocomplete="off" placeholder="Элементов на странице жалоб" value="{complaints_lim}">
				</div>
				<button class="btn btn-default mt-10" type="button" onclick="edit_paginator();">Изменить</button>
				<div id="edit_paginator_result"></div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">Уголок разработчика</div>
			<div class="panel-body">
				<div id="update_server_status"></div>
				<?
					if(!extension_loaded("zip")):
						?>
						<div class="bs-callout bs-callout-error">
							<p>У Вас отключено расширение ZIP, настройте его в своём PHP</p>
						</div><br>
						<?
					endif;
				?>
				<b class="f-l">Версия движка: </b> <div id="version"><img src="{site_host}templates/admin/img/loader.gif"></div>
				<div class="claerfix"></div><br>
				<hr>
				<label for="update_servers">Сервер обновления</label>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_update_server();">Изменить</button>
					</span>
					<select id="update_servers" class="form-control">{update_servers}</select>
				</div>
			</div>
		</div>
	
		<div class="panel panel-default">
			<div class="panel-heading">Виджеты</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-4">
						<b>«Кот»</b>
						<div class="form-group">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-default {cote_act}" onclick="change_value('config','cote','1','1');">
									<input type="radio">
									Вкл
								</label>

								<label class="btn btn-default {cote_act2}" onclick="change_value('config','cote','2','1');">
									<input type="radio">
									Выкл
								</label>
							</div>
						</div>
					</div>
					<div class="col-xs-4">
						<b>«Новогодняя мишура»</b>
						<div class="form-group">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-default {new_year_act}" onclick="change_value('config','new_year','1','1');">
									<input type="radio">
									Вкл
								</label>

								<label class="btn btn-default {new_year_act2}" onclick="change_value('config','new_year','2','1');">
									<input type="radio">
									Выкл
								</label>
							</div>
						</div>
					</div>
					<div class="col-xs-4">
						<b>«Георгиевская лента»</b>
						<div class="form-group">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-default {win_day_act}" onclick="change_value('config','win_day','1','1');">
									<input type="radio">
									Вкл
								</label>

								<label class="btn btn-default {win_day_act2}" onclick="change_value('config','win_day','2','1');">
									<input type="radio">
									Выкл
								</label>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<b>Виджет «Сегодня нас посетили»</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {last_online_act}" onclick="change_value('config','disp_last_online','1','1');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {last_online_act2}" onclick="change_value('config','disp_last_online','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<hr>
				<b>Виджет «Последние новости»</b>
				<div class="input-group">
					<div class="input-group-btn" data-toggle="buttons">
						<label class="btn btn-default {nact}" onclick="change_value('config','show_news','3','1'); $('#show_news').val('3');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {nact2}" onclick="change_value('config','show_news','0','1'); $('#show_news').val('0');">
							<input type="radio">
							Выключить
						</label>
					</div>
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_show_news();">Изменить</button>
					</span>
					<input type="number" class="form-control" id="show_news" maxlength="1" autocomplete="off" value="{show_news}">
				</div>
				<small class="f-r c-868686">Количество выводимых новостей</small>
				<br>
				<hr>
				<b>Виджет «Последние события»</b>
				<div class="input-group">
					<div class="input-group-btn" data-toggle="buttons">
						<label class="btn btn-default {eact}" onclick="change_value('config','show_events','3','1'); $('#show_events').val('3');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {eact2}" onclick="change_value('config','show_events','0','1'); $('#show_events').val('0');">
							<input type="radio">
							Выключить
						</label>
					</div>
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_show_events();">Изменить</button>
					</span>
					<input type="number" class="form-control" id="show_events" maxlength="1" autocomplete="off" value="{show_events}">
				</div>
				<small class="f-r c-868686">Количество выводимых событий</small>
				<br>
				<hr>
				<b>Виджет «Топ донатеров»</b>
				<div class="input-group">
					<div class="input-group-btn" data-toggle="buttons">
						<label class="btn btn-default {topDonatorsWidgetIsOn}" onclick="change_value('config','top_donators','1','1');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {topDonatorsWidgetIsOff}" onclick="change_value('config','top_donators','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>

				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="editTopDonatorsWidget();">Изменить</button>
					</span>
					<select id="top_donators_show_sum" class="form-control">
						<option value="1" {if('{top_donators_show_sum}' == '1')} selected {/if}>Отображать сумму доната</option>
						<option value="2" {if('{top_donators_show_sum}' == '2')} selected {/if}>Не отображать сумму доната</option>
					</select>
					<input value="{top_donators_count}" type="text" class="form-control" id="top_donators_count" maxlength="2" placeholder="Количество выводимых донатеров">
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Виджеты Вконтакте/Facebook</div>
			<div class="panel-body mb-0">
				<p><b>Тип виджетов</b></p>
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {widgets_type_1}" onclick="switch_widgets_type('1');">
						<input type="radio">
						Вконтакте
					</label>

					<label class="btn btn-default {widgets_type_2}" onclick="switch_widgets_type('2');">
						<input type="radio">
						Facebook
					</label>
				</div>
				<hr>
				<p><b>Группа(ы)</b></p>
				<div class="btn-group" data-toggle="buttons" id="vk_group_selector">
					<label class="btn btn-default {vk_group}" onclick="switch_widget('1', '1');">
						<input type="radio">
						Включить
					</label>

					<label class="btn btn-default {vk_group2}" onclick="switch_widget('2', '1');">
						<input type="radio">
						Выключить
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_vk_group_id();">Изменить</button>
					</span>
					<input value="{vk_group_id}" type="text" class="form-control" id="vk_group_id" maxlength="80" autocomplete="off" placeholder="ID группы (если несколько, то через запятую без пробелов)">
				</div>
				<small class="f-r c-868686">ID группы (если несколько, то через запятую без пробелов)</small>
				<div id="edit_vk_group_result"><br></div>
				<hr>
				<p><b>Администратор(ы)</b></p>
				<div class="btn-group" data-toggle="buttons" id="vk_admin_selector">
					<label class="btn btn-default {vk_admin}" onclick="switch_widget('1', '2');">
						<input type="radio">
						Включить
					</label>

					<label class="btn btn-default {vk_admin2}" onclick="switch_widget('2', '2');">
						<input type="radio">
						Выключить
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_vk_admin_id();">Изменить</button>
					</span>
					<input value="{vk_admin_id}" type="text" class="form-control" id="vk_admin_id" maxlength="80" autocomplete="off" placeholder="ID пользователя (если несколько, то через запятую без пробелов)">
				</div>
				<small class="f-r c-868686">ID пользователя (если несколько, то через запятую без пробелов)</small>
				<div id="edit_vk_admin_result" class="mb-0"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Часовой пояс сайта</div>
			<div class="panel-body mb-0">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_site_time_zone();">Изменить</button>
					</span>
					<select id="time_zone" class="form-control">
						{time_zones}
					</select>
				</div>
				<div id="edit_time_zone_result"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Очистка движка</div>
			<div class="panel-body">
				<b>Удалить все сообщения чата (<span id="chat_number">{chat_number}</span>)</b><br>
				<button class="btn btn-default" type="button" onclick="dell_all_chat_messages();">Выполнить</button>
				<hr>
				<b>Удалить старые заявки на разбан (более 30дней назад)</b><br>
				<button class="btn btn-default" type="button" onclick="fast_admin_action('dell_old_bans');">Выполнить</button>
				<hr>
				<b>Удалить старые тикеты (более 30дней назад)</b><br>
				<button class="btn btn-default" type="button" onclick="fast_admin_action('dell_old_tickets');">Выполнить</button>
				<hr>
				<b>Сбросить кэш</b><br>
				<button class="btn btn-default" type="button" onclick="fast_admin_action('dell_cache');">Выполнить</button>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Главный администратор</div>
			<div class="panel-body mb-0">
				<b>Укажите id профиля главного администратора(ему будут приходить уведомления о покупке прав пользователями)</b><br>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_admins_ids();">Изменить</button>
					</span>
					<input value="{admins_ids}" type="text" class="form-control" id="admins_ids" maxlength="80" autocomplete="off" placeholder="ID пользователя (если несколько, то через запятую без пробелов)">
				</div>
				<small class="f-r c-868686">ID пользователя (если несколько, то через запятую без пробелов)</small>
				<div id="edit_admins_ids_result"><br></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Выключение сайта</div>
			<div class="panel-body mb-0">
				<b>При выключении сайта, он будет доступен лишь пользователю, авторизованному в админ центре</b><br>
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {off_act2}" onclick="change_value('config','off','2','1');">
						<input type="radio">
						Включить сайт
					</label>

					<label class="btn btn-default {off_act}" onclick="change_value('config','off','1','1');">
						<input type="radio">
						Выключить сайт
					</label>
				</div>
				<div class="input-group mt-10">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_off_message();">Изменить</button>
					</span>
					<input value="{off_message}" type="text" class="form-control" id="off_message" maxlength="250" autocomplete="off" placeholder="Сообщение пользователям">
				</div>
				<div id="edit_off_message_result"></div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Протокол, используемый сайтом</div>
			<div class="panel-body">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_protocol();">Изменить</button>
					</span>
					<select id="protocol" class="form-control">
						<option value="1" {if('{protocol}' == '1')} selected {/if}>Определять автоматически</option>
						<option value="2" {if('{protocol}' == '2')} selected {/if}>HTTP</option>
						<option value="3" {if('{protocol}' == '3')} selected {/if}>HTTPS</option>
					</select>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Ограничение на смену информации</div>
			<div class="panel-body">
				<b>Раз в сколько дней разрешить смену логина:</b>
				<div class="form-group mb-0">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_col_login();">Изменить</button>
						</span>
						<input type="text" class="form-control" id="col_login" maxlength="3" autocomplete="off" value="{col_login}">
					</div>
					<div id="edit_col_login_result"></div>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Защита от флуда</div>
			<div class="panel-body">
				<div class="form-group">
					<div class="btn-group mb-10" data-toggle="buttons">
						<label class="btn btn-default {act}" onclick="edit_protect('1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {act2}" onclick="edit_protect('2');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>

				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_violations_delta();">Изменить</button>
					</span>
					<input type="number" class="form-control" id="violations_delta" maxlength="5" autocomplete="off" value="{violations_delta}">
				</div>
				<small class="f-r c-868686">Время(в сек.), в пределах которого разрешается выполнить 1 действие.</small>
				<div id="edit_violations_delta_result"></div><br> 

				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_violations_number();">Изменить</button>
					</span>
					<input type="number" class="form-control" id="violations_number" maxlength="5" autocomplete="off" value="{violations_number}">
				</div>
				<small class="f-r c-868686">Количество допустимых нарушений.</small>
				<div id="edit_violations_number_result"></div><br> 

				<!--
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="edit_ban_time();">Изменить</button>
					</span>
					<input type="number" class="form-control" id="ban_time" maxlength="5" autocomplete="off" value="{ban_time}">
				</div>
				<small class="f-r c-868686">Время(в секундах) блокировки пользователя</small>
				<div id="edit_ban_time_result"></div><br> 
				<div class="bs-callout bs-callout-info mt-10">
					<h5>Принцип работы</h5>
					<p>Устанавливается <label for="violations_delta">определенное время</label>, в течение которого разрешено выполнять лишь одно действие, если пользователь нарушает данное правило, выполняя более одного действия за это время, то он получает +1 нарушение. Если нарушения идут подряд, то все они суммируются и после того как у пользователя их становится <label for="violations_number">определенное количество</label>, сайт блокирует данного нарушителя на <label for="ban_time">заданное время</label>.</p>
				</div>
				-->
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Запрещенные слова</div>
			<div class="panel-body">
				<button class="btn btn-default" data-target="#forbidden-words" data-toggle="modal" onclick="loadForbiddenWords();">Добавить | редактировать</button>
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading">Скрывать STEAM ID / IP игроков</div>
			<div class="panel-body">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="editHidingPlayersId();">Изменить</button>
					</span>
					<select id="hidePlayersIdType" class="form-control">
						<option value="0" {if('{hidePlayersId}' == '0')} selected {/if}>Не скрывать</option>
						<option value="1" {if('{hidePlayersId}' == '1')} selected {/if}>Скрывать у всех</option>
						<option value="2" {if('{hidePlayersId}' == '2')} selected {/if}>Только у админов</option>
						<option value="3" {if('{hidePlayersId}' == '3')} selected {/if}>Только у игроков</option>
					</select>
				</div>

				<div class="bs-callout bs-callout-info mt-10">
					<p class="mb-0">Пользователи с любым из флагов i, k, s, j имеют иммунитет к данной опции<p>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="forbidden-words" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Запрещенные слова</h4>
			</div>
			<div class="modal-body">
				<div class="bs-callout bs-callout-info mb-10 fs-14">
					Данная функция запрещает отправку определенных слов в чате, комментариях, на форуме, в личных сообщениях и т.д.<br>
				</div>

				<form id="forbidden-words-list"></form>
				<button class="btn btn-default mt-5 f-l" onclick="saveForbiddenWords();">Сохранить</button>
				<button class="btn btn-default mt-5 ml-5 f-l" onclick="addForbiddenWordInput();">Добавить</button>
				<div class="clearfix"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="description" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"> Описание
			</div>
			<div class="modal-body" id="update_description">
				<center><img src="{site_host}templates/admin/img/loader.gif"></center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>
<link href="{site_host}files/toasts/toasty.min.css?v={cache}" rel="stylesheet">
<script src="{site_host}files/toasts/toasty.min.js?v={cache}" type="text/javascript"></script>