<div class="header container-fluid" id="vFoxHead">
	<div class="container">
		<a alt="Игровой движок UNI GameCMS" class="logo full_logo" href="https://worksma.ru" target="_blank">
			<img class="full_logo" src="../templates/admin/img/logo.png">
		</a>
		
        <div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li>
					<a href="https://worksma.ru" style="color:#eee;background:#4B0082;">
						UNI GameCMS
					</a>
				</li>
			</ul>
        </div>
	</div>
</div>

<div class="container">
	<div class="install_block">
		<h3>
			<center>Установка UNI GameCMS</center>
		</h3>
		<hr>
		<h4>Подключение к базе данных</h4>
		<div>
			<input type="text" class="form-control mt-10" placeholder="127.0.0.1" id="host">
			<input type="text" class="form-control mt-10" placeholder="overcs_gamecms" id="base">
			<input type="text" class="form-control mt-10" placeholder="root" id="user">
			<input type="text" class="form-control mt-10" placeholder="****" id="pass">
			
			<button class="btn2 btn-access mt-10 btn-block" onclick="try_connect();">Проверить соединение</button>
			
			<div class="mt-10" id="result1"></div>
		</div>
		<br>
		<h4>Основные настройки</h4>
		<div>
			<input type="text" class="form-control mt-10" placeholder="Название проекта" id="name" maxlength="255">
			<br>
			<input id="checbox" title="dont_agree" type="checkbox" onclick="on_install();"> Я ознакомлен и согласен с <a target="_blank" href="https://vk.com/@overcsru-licenzionnoe-soglashenie-gamecms">лицензионным соглашением</a><br>
			
			<button id="install" class="btn2 btn-big mt-10 btn-block disabled " onclick="">Установить</button>
			<div class="mt-10" id="result2"></div>
		</div>
		<div id="copyright">
			<center>Copyright © 2021 <a href="https://worksma.ru" target="_blank">@WORKSMA</a>. All rights reserved.</center>
		</div>
	</div>
</div>
<input type="hidden" id="key" value="{key}">