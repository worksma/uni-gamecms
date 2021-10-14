<div class="central-block auth-block">
	<div class="central-block-header">
		<a href="../">
			<img src="../templates/admin/img/logo.png" alt="UNI gameCMS">
		</a>
		<h3>Админ центр</h3>
	</div>
	<div class="central-block-body">
		<div class="form-horizontal">
			<form id="admin_login">
				<input id="password" type="password" maxlength="15" class="form-control" placeholder="Пароль">
				
				{if($conf->captcha != '2')}
					<div data-theme="light" class="g-recaptcha mb-15 clearfix" data-sitekey="{{$conf->captcha_client_key}}"></div>
					<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
				{/if}
				<div id="result"></div>

				<button class="btn2" type="submit">Войти</button>
			</form>
			<script> send_form('#admin_login', 'admin_login();'); </script>
		</div>
		<div class="copyright">
			Powered by <a title="Сайт разработан на движке UNI GameCMS" href="https://worksma.ru/uni-gamecms" target="_blank">UNI GameCMS</a>
		</div>
	</div>
</div>