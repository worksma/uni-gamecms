<div class="page servers-settins">
	<div class="block">
		<div class="block_head">
			Настройки мониторинга серверов
		</div>
		<div class="row">
			<div class="col-md-6">
				<b>Интервал обновления мониторинга серверов (в секундах)</b>
				<div class="input-group">
					<span class="input-group-btn">
						<button id="btn_mon_gap" class="btn btn-default {if('{api_active}' == 'active')} disabled {/if}" type="button" onclick="edit_mon_gap();">Изменить</button>
					</span>
					<input {if('{api_active}' == 'active')} disabled {/if} type="number" class="form-control" id="mon_gap" maxlength="5" autocomplete="off" value="{mon_gap}">
				</div>
				<div class="bs-callout bs-callout-info mt-10 mb-10">
					<h5>Рекомендуемые значения</h5>
					<p>60, 120, 180</p>
				</div>
			</div>
			<div class="col-md-6">
				<b>Использовать внешний сервер для мониторинга</b>
				<div class="input-group">
					<div class="input-group-btn" data-toggle="buttons">
						<label class="btn btn-default {api_active}" onclick="edit_mon_api('1')">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {api_active2}" onclick="edit_mon_api('2')">
							<input type="radio">
							Выключить
						</label>
					</div>
					<input type="text" class="form-control" id="mon_key" maxlength="15" autocomplete="off" value="{mon_key}" placeholder="Введите ключ">
				</div>

					<div class="bs-callout bs-callout-info mt-10">
						<p>
							<a target="_blank" href="https://worksma.ru/service-gamecms-monitor">
								<span class="glyphicon glyphicon-link"></span> Получить ключ Внешнего мониторинга
							</a>
						</p>
					</div>

				<div class="bs-callout bs-callout-info mt-10">
					<h5>Важная информация</h5>
					<p>
						Данный пункт следует использовать, если на Вашем web-хостинге не работает стандартный мониторинг.
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="block servers_options">
		<div class="block_head">
			Добавить сервер
		</div>
		<div class="row">
			<div class="col-md-4 mb-10">
				<b>Основные настройки</b>
				<div class="form-group">
					<small>Игра</small>
					<script>
					function local_change_game(id,sign) {
						var game = $('#game'+id+' option:selected').val();
						if(game == 0) {
							$('#opt0_'+id).attr('class', 'disp-b');
							$('#opt1_'+id).attr('class', 'disp-b');
							$('#opt2_'+id).attr('class', 'disp-b');
							$('#opt3_'+id).attr('class', 'disp-b');
							$('#opt4_'+id).attr('class', 'disp-n');
							$('#opt5_'+id).attr('class', 'disp-b');
							$('#opt6_'+id).attr('class', 'disp-n');
							$('#st_opt1_'+id).attr('class', 'disp-b');
							$('#st_opt2_'+id).attr('class', 'disp-b');
							$('#st_opt3_'+id).attr('class', 'disp-b');
							$('#st_opt4_'+id).attr('class', 'disp-b');
							$('#st_opt5_'+id).attr('class', 'disp-n');
							$('#st_opt6_'+id).attr('class', 'disp-n');

							$('#import_settings'+id+' option[data-game="0"]').attr('class', 'disp-b');
							$('#import_settings'+id+' option[data-game="1"]').attr('class', 'disp-n');

							$("#bind_nick_pass").prop("checked", true);
							$('#bind_nick_pass_btn'+id).removeClass('disabled');
							$('#bind_nick_pass_btn'+id).addClass('active');
						} else {
							$('#opt0_'+id).attr('class', 'disp-b');
							$('#opt1_'+id).attr('class', 'disp-n');
							$('#opt2_'+id).attr('class', 'disp-n');
							$('#opt3_'+id).attr('class', 'disp-n');
							$('#opt4_'+id).attr('class', 'disp-b');
							$('#opt5_'+id).attr('class', 'disp-n');
							$('#opt6_'+id).attr('class', 'disp-b');
							$('#st_opt1_'+id).attr('class', 'disp-n');
							$('#st_opt2_'+id).attr('class', 'disp-n');
							$('#st_opt3_'+id).attr('class', 'disp-n');
							$('#st_opt4_'+id).attr('class', 'disp-b');
							$('#st_opt5_'+id).attr('class', 'disp-b');
							$('#st_opt6_'+id).attr('class', 'disp-b');

							$('#import_settings'+id+' option[data-game="0"]').attr('class', 'disp-n');
							$('#import_settings'+id+' option[data-game="1"]').attr('class', 'disp-b');

							$("#bind_nick_pass").prop("checked", false);
							$('#bind_nick_pass_btn'+id).removeClass('active');
							$('#bind_nick_pass_btn'+id).addClass('disabled');
						}
						if(sign != 1){
							$('#type'+id).val(0);
							$('#st_type'+id).val(0);
							select_serv_type(0, id);
							select_stat_type(0, id);
						}
					}
					</script>
					<select class="form-control" id="game" onchange="local_change_game('');">
						<option value="0">Counter-Strike: 1.6</option>
						<option value="1">Counter-Strike: Source</option>
						<option value="2">Counter-Strike: Global Offensive</option>
						<option value="3">Alien Swarm</option>
						<option value="4">CSPromod</option>
						<option value="5">Day of Defeat: Source</option>
						<option value="6">Dystopia</option>
						<option value="7">E.Y.E: Divine Cybermancy</option>
						<option value="8">Fortress Forever</option>
						<option value="9">Garry's Mod</option>
						<option value="10">Half-Life 2 Deathmatch</option>
						<option value="11">Half-Life 2 Capture the Flag</option>
						<option value="12">Hidden: Source</option>
						<option value="13">Insurgency: Source</option>
						<option value="14">Left 4 Dead 2</option>
						<option value="15">Left 4 Dead</option>
						<option value="16">Nuclear Dawn</option>
						<option value="17">Perfect Dark: Source</option>
						<option value="18">Pirates Vikings and Knights II</option>
						<option value="19">Team Fortress 2</option>
						<option value="20">The Ship</option>
						<option value="21">Zombie Panic</option>
					</select>
				</div>
				<div class="form-group">
					<small>Название</small>
					<input type="text" placeholder="Название сервера" class="form-control" id="name" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Ip</small>
					<input type="text" placeholder="IP адрес сервера" class="form-control" id="ip" maxlength="30" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Port</small>
					<input type="text" placeholder="Port сервера" class="form-control" id="port" maxlength="5" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Отображаемый адрес</small>
					<input type="text" placeholder="IP:Port" class="form-control" id="address" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Скидка на услуги в %</small>
					<input value="0" placeholder="От 0 до 99" type="number" class="form-control" id="discount" maxlength="2" autocomplete="off">
				</div>
				<div class="form-group">
					<small>Отображение в мониторинге</small>
					<select class="form-control" id="show">
						<option value="1" selected>Показывать</option>
						<option value="2">Скрывать</option>
					</select>
				</div>
				<div class="form-group disp-n" id="import">
					<small>Импортировать админов, услуги и тарифы</small>
					<select class="form-control" id="import_settings">
						{servers}
					</select>
				</div>
				<div class="form-group">
					<small>Способы привязки услуг</small>
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default btn-sm active" id="bind_nick_pass_btn" for="bind_nick_pass">
							<input type="checkbox" id="bind_nick_pass" autocomplete="off"> Ник + пароль
						</label>
						<label class="btn btn-default btn-sm active" id="bind_steam_btn" for="bind_steam">
							<input type="checkbox" id="bind_steam" autocomplete="off"> STEAM ID
						</label>
						<label class="btn btn-default btn-sm active" id="bind_steam_pass_btn" for="bind_steam_pass">
							<input type="checkbox" id="bind_steam_pass" autocomplete="off"> STEAM ID + пароль
						</label>
					</div>

					<script>
						$("#bind_nick_pass").prop("checked", true);
						$("#bind_steam").prop("checked", true);
						$("#bind_steam_pass").prop("checked", true);
					</script>
				</div>
			</div>
			<div class="col-md-4 mb-10">
				<b>Дополнительные настройки</b>
				<div class="form-group">
					<small>Интеграция</small>
					<script>
					function local_change_type(id) {
						var type = $('#type'+id+' option:selected').val();
						select_serv_type(type, id);
					}
					</script>
					<select class="form-control" id="type" onchange="local_change_type('');">
						<option id="opt0_" value="0">Нет настроек</option>
						<option id="opt1_" value="1">Файл (Users.ini)</option>
						<option id="opt2_" value="2">AmxBans/CsBans</option>
						<option id="opt3_" value="3">AmxBans/CsBans + файл</option>
						<option id="opt4_" value="4">SourceBans/MaterialAdmin</option>
						<option id="opt5_" value="5">AmxBans/CsBans + GameCMS API</option>
						<!--<option id="opt6_" value="66">SourceBans/MaterialAdmin + GameCMS API</option>-->
					</select>
				</div>
				<div id="none_">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<p>Чтение/запись привилегий и банов не осуществляется</p>
					</div>
				</div>
				<div id="tip1_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии</h4>
						<p>Для чтения/записи привилегий используется файл</p>
					</div>
				</div>
				<div id="tip2_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи банов и привилегий используется база данных от AmxBans/CsBans</p>
					</div>
				</div>
				<div id="tip3_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи привилегий используется файл, для чтения/записи банов используется база данных от AmxBans/CsBans</p>
					</div>
				</div>
				<div id="tip4_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи банов и привилегий используется база данных от SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a></p>
					</div>
				</div>
				<div id="tip5_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от AmxBans/CsBans. Данный тип интеграции требует установку плагина GameCMS API(amx) на игровой сервер</p>
					</div>
				</div>
				<div id="tip6_" class="disp-n">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<h4>Поддержка: привилегии и баны</h4>
						<p>Для чтения/записи привилегий используется база данных текущего сайта, для чтения/записи банов используется база данных от SourceBans/<a href="https://github.com/SB-MaterialAdmin" target="_blank">MaterialAdmin</a>. Данный тип интеграции требует установку плагина GameCMS API(sm) на игровой сервер</p>
					</div>
				</div>
				<div id="auth_prefix" class="disp-n">
					<div class="form-group">
						<small>Префикс для авторизации админа на сервере</small>
						<input type="text" class="form-control" id="pass_prifix" maxlength="10" autocomplete="off" placeholder="_pw">
					</div>
				</div>
				<div id="ftp" class="disp-n">
					<div class="form-group">
						<small>FTP хост</small>
						<input type="text" class="form-control" id="ftp_host" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>FTP порт</small>
						<input type="text" class="form-control" id="ftp_port" maxlength="5" autocomplete="off" placeholder="По умлочанию: 21">
					</div>
					<div class="form-group">
						<small>FTP логин</small>
						<input type="text" class="form-control" id="ftp_login" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>FTP пароль</small>
						<input type="password" class="form-control" id="ftp_pass" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>Путь до файла (Пример: cstrike/addons/amxmodx/configs/users.ini)</small>
						<input type="text" class="form-control" id="ftp_string" maxlength="255" autocomplete="off">
					</div>
				</div>
				<div id="db" class="disp-n">
					<div class="form-group">
						<small>db хост</small>
						<input type="text" class="form-control" id="db_host" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db логин</small>
						<input type="text" class="form-control" id="db_user" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db пароль</small>
						<input type="password" class="form-control" id="db_pass" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db база</small>
						<input type="text" class="form-control" id="db_db" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db префикс</small>
						<input type="text" class="form-control" id="db_prefix" maxlength="32" autocomplete="off" placeholder="amx / cs / gm / sb">
					</div>
					<div class="form-group">
						<small>Кодировка</small>
						<select class="form-control" id="db_code">
							<option value="0">Определять автоматически</option>
							<option value="1">utf-8</option>
							<option value="2">latin1</option>
							<option value="3">utf8mb4</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-4 mb-10">
				<b>Настройки статистики</b>
				<div class="form-group">
					<small>Интеграция</small>
					<script>
					function local_change_type_st(id) {
						var st_type = $('#st_type'+id+' option:selected').val();
						select_stat_type(st_type, id);
					}
					</script>
					<select class="form-control" id="st_type" onchange="local_change_type_st('');">
						<option id="st_opt0_" value="0">Нет настроек</option>
						<option id="st_opt1_" value="1">CsStats MySQL</option>
						<option id="st_opt2_" value="2">Army Ranks Ultimate</option>
						<option id="st_opt3_" value="3">CSstatsX SQL</option>
						<option id="st_opt4_" value="4">HLstatsX:CE</option>
						<option id="st_opt5_" value="5">RankMe</option>
						<option id="st_opt6_" value="6">Level Rank</option>
					</select>
				</div>
				<div id="st_none_">
					<div class="bs-callout bs-callout-info bs-callout-sm mt-5">
						<p>Статистика данного сервера не будет отображаться на сайте</p>
					</div>
				</div>
				<div id="st_tip1_" class="disp-n">
					<div class="form-group">
						<small>db хост</small>
						<input type="text" class="form-control" id="st_db_host" maxlength="64" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db логин</small>
						<input type="text" class="form-control" id="st_db_user" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db пароль</small>
						<input type="password" class="form-control" id="st_db_pass" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>db база</small>
						<input type="text" class="form-control" id="st_db_db" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group" id="st_db_table_input">
						<small>db таблица</small>
						<input type="text" class="form-control" id="st_db_table" maxlength="32" autocomplete="off">
					</div>
					<div class="form-group">
						<small>Кодировка</small>
						<select class="form-control" id="st_db_code">
							<option value="0">Определять автоматически</option>
							<option value="1">utf-8</option>
							<option value="2">latin1</option>
							<option value="3">utf8mb4</option>
						</select>
					</div>
					<div class="form-group">
						<small>Сортировка</small>
						<select class="form-control" id="st_sort_type">
							<option value="0">Убийства - смерти - teamkills</option>
							<option value="1">Убийства</option>
							<option value="2">Убийства + headshods</option>
							<option value="3">Skill</option>
							<option value="4">Время онлайн</option>
							<option value="5">Место</option>
							<option value="6">Продвинутая</option>
							<option value="7">Ранг</option>
							<option value="8">Очки</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<div id="add_serv_result"></div>
				<button onclick="server('add');" type="button" class="btn2 btn-big">Создать</button><br>
			</div>
		</div>
	</div>

	<div id="servers">
		<br><center><img src="{site_host}templates/admin/img/loader.gif"></center>
		<script>
			local_change_game('', 1);
			load_servers();
		</script>
	</div>
</div>