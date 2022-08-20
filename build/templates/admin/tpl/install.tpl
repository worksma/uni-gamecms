<div class="central-block install-block">
	<div class="central-block-header">
		<a href="../">
			<img src="../templates/admin/img/logo.png" alt="UNI GameCMS">
		</a>
		<h3>Установка UNI GameCMS</h3>
	</div>
	
	<div class="central-block-body">
		<div class="bs-callout bs-callout-info">
			<h4>Необходимое окружение для работы движка</h4>

			<ul class="mt-10">
				<li>• PHP 7.4-8.1</li>
				<li>• MySQL 5.6 - 5.7</li>
				<li>• Права записи на файлы движка для PHP</li>
				<li>• Включенный AllowOverride для Apache</li>
				<li>• Наличие модулей GD, mbstring для PHP</li>
			</ul>
		</div>
		<br>

		<h4>Подключение к базе данных</h4>
		<input type="hidden" id="key" value="{key}">
		<input type="text" class="form-control mt-10" placeholder="Адрес хостинга" id="host" autocomplete="off">
		<input type="text" class="form-control mt-10" placeholder="Имя базы данных" id="base" autocomplete="off">
		<input type="text" class="form-control mt-10" placeholder="Имя пользователя" id="user" autocomplete="off">
		<input type="text" class="form-control mt-10" placeholder="Пароль" id="pass" autocomplete="off">
		<div class="mt-10" id="result1"></div>
		<button class="btn2 btn-cancel" onclick="try_connect();">Проверить соединение</button>
		<br>
		<br>

		<h4>Основные настройки</h4>
		<div>
			<input type="text" class="form-control mt-10" placeholder="Название проекта" id="name" maxlength="255">
			<br>
			<input id="checbox" title="dont_agree" type="checkbox" onclick="on_install();"> Я ознакомлен и согласен с <a target="_blank" href="https://worksma.ru/uni-gamecms/license">пользовательским соглашением</a><br>
			<button id="install" class="btn2 btn-big mt-10 disabled" onclick="">Установить</button>
			<div class="mt-10" id="result2"></div>
		</div>
		<br>

		<div class="copyright t-c">
			Разработано при поддержке <a href="https://worksma.ru" target="_blank">Торговой площадки WORKSMA</a>
		</div>
	</div>
</div>