<div class="page">
	<div class="block">
		<div class="block_head">
			Настройка почтового сервера
		</div>

		<div class="form-group mt-10">
			<div class="btn-group mb-10" data-toggle="buttons">
				<label class="btn btn-default {pact}" onclick="change_value('config__email','use_email','1','1');" checked>
					<input type="radio">
					Включить
				</label>

				<label class="btn btn-default {pact2}" onclick="change_value('config__email','use_email','2','1');">
					<input type="radio">
					Выключить
				</label>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<small class="c-868686">Имя своего почтового ящика</small>
				<input type="text" class="form-control mb-10" id="email_username" maxlength="255" autocomplete="off" value="{email_username}">

				<small class="c-868686">Сервер для отправки почты</small>
				<input type="text" class="form-control mb-10" id="email_host" maxlength="255" autocomplete="off" value="{email_host}">

				<small class="c-868686">Порт</small>
				<input type="number" class="form-control mb-10" id="email_port" maxlength="5" autocomplete="off" value="{email_port}">	
			</div>
			<div class="col-md-6">
				<small class="c-868686">Пароль</small>
				<input type="password" class="form-control mb-10" id="email_password" maxlength="255" autocomplete="off" value="{email_password}">

				<small class="c-868686">Кодировка</small>
				<input type="text" class="form-control mb-10" id="email_charset" maxlength="20" autocomplete="off" value="{email_charset}">

				<small class="c-868686">Проверка имени узла, проверка используемого SSL-сертификата</small>
				<select id="verify_peers" class="form-control mb-10">
					<option value="1" {if('{verify_peers}' == '1')} selected {/if}>Да</option>
					<option value="2" {if('{verify_peers}' == '2')} selected {/if}>Нет</option>
				</select>
			</div>
		</div>

		<small class="c-868686">Ваше имя - или имя Вашего сайта. Будет показывать при прочтении в поле "От кого"</small>
		<input type="text" class="form-control mb-10" id="from_email" maxlength="255" autocomplete="off" value="{from_email}">

		<div id="edit_email_settings_result"></div>
		<button class="btn btn-default" type="button" onclick="edit_email_settings();">Сохранить</button>
	</div>

	<div class="block">
		<div class="block_head">
			Отправить сообщение
		</div>

		<div class="row">
			<div class="col-md-6">
				<b>Получатель</b>
				<input type="text" class="form-control mb-10" id="email" maxlength="255" placeholder="Введите e-mail пользователя(можно несколько через запятую)">
				<b>Тема</b>
				<input type="text" class="form-control mb-10" id="subject" maxlength="255" placeholder="Введите тему сообщения">
			</div>
			<div class="col-md-6">
				<b>Debug режим</b>
				<select id="dubug" class="form-control mb-10">
					<option value="1">Включен</option>
					<option value="2" selected>Выключен</option>
				</select>

				<b>Детализация отчета debug</b>
				<select id="dubug_value" class="form-control mb-10">
					<option value="1">Краткий</option>
					<option value="2">Развернутый</option>
					<option value="4">Подробный</option>
				</select>
			</div>
		</div>

		<b>Сообщение</b><br>
		<textarea name="text" id="text" rows="10" cols="80"></textarea>
		<script> CKEDITOR.replace( 'text' ); </script>
		<button class="btn btn-default mt-10" type="button" onclick="send_email_message();">Отправить</button>
		<div id="result"></div>
	</div>
</div>