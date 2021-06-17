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
				<div class="input-group" style="margin-bottom:0.2rem;">
					<span class="input-group-addon">
						<i class="fas fa-user"></i>
					</span>
					<input type="text" class="form-control" placeholder="Введите логин" id="login">
				</div>
				
				<div class="input-group" style="margin-bottom:0.5rem;">
					<span class="input-group-addon">
						<i class="fas fa-unlock"></i>
					</span>
					<input type="password" class="form-control" placeholder="Введите пароль" id="password">
				</div>
			
			
				{if($conf->captcha != '2')}
					<div style="transform:scale(1.13);-webkit-transform:scale(1.13);transform-origin:0 0;-webkit-transform-origin:0 0;" data-theme="light" class="g-recaptcha mb-15 clearfix" data-sitekey="{{$conf->captcha}}"></div>
					<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
				{/if}
				<div id="result"></div>
				
				<center>
					<input type="submit" value="Войти" class="btn2 btn-primary btn-lg btn-block" style="background:#4B0082;">
					<a href="/recovery">Забыли пароль?</a>
				</center>
			</form>
			<script> send_form('#admin_login', 'admin_login();'); </script>
		</div>
		<div id="copyright">
			<center>Copyright © 2021 <a href="https://worksma.ru" target="_blank">@WORKSMA</a>. All rights reserved.</center>
		</div>
	</div>
</div>