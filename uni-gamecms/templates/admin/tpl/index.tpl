<form class="modal fade" id="staticBackdrop" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="staticBackdropLabel">Восстановление пароля</h5>
		  </div>
		  <div class="modal-body">
			<input type="email" id="replace_email_input" maxlength="32" class="form-control" placeholder="webmaster@example.com">
			
			<div id="result_replace"></div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
			<button type="submit" class="btn btn-primary">Восстановить</button>
		  </div>
		</div>
	</div>
</form>
<script> send_form('#staticBackdrop', 'replace_password();'); </script>

<div class="central_block" style="-moz-box-shadow:0 0 10px #000; -webkit-box-shadow:0 0 10px #000; box-shadow:0 0 10px #000;">
	<div class="header">
		<a href="../">
			<img src="../templates/admin/img/logo.png" alt="{site_name}">
		</a>
		<h3>Админ центр</h3>
	</div>
	<div class="central_block_body">
		<div class="form-horizontal">
			<form id="admin_login">
				<p>
					<input id="password" type="password" maxlength="15" class="form-control" placeholder="Пароль">
				</p>
				{if($conf->captcha != '2')}
					<div style="transform:scale(1.13);-webkit-transform:scale(1.13);transform-origin:0 0;-webkit-transform-origin:0 0;" data-theme="light" class="g-recaptcha mb-15 clearfix" data-sitekey="{{$conf->captcha}}"></div>
					<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
				{/if}
				<div id="result"></div>
				<p>
					<center>
						<input type="submit" value="Войти" class="btn2 btn-primary btn-lg btn-block" style="background:#4B0082;">
						<a href="#" data-toggle="modal" data-target="#staticBackdrop">Забыли пароль?</a>
					</center>
				</p>
			</form>
			<script> send_form('#admin_login', 'admin_login();'); </script>
		</div>
		<div id="copyright">
			<center>Copyright © 2021 <a href="https://worksma.ru" target="_blank">@WORKSMA</a>. All rights reserved.</center>
		</div>
	</div>
</div>