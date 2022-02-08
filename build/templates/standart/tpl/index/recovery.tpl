<div class="col-lg-3 order-is-last">
	{include file="/index/authorization.tpl"}
	{include file="/index/sidebar.tpl"}
</div>

<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Восстановление пароля
		</div>

		{if("{message}" == "")}
			<form id="send_new_pass" class="form-horizontal">
				<input type="text" maxlength="255" class="form-control" id="email_2" placeholder="Введите ваш e-mail">

				{if($conf->captcha != '2')}

					<div id="recaptcha_2" class="clearfix"></div>
					<script src="https://www.google.com/recaptcha/api.js?hl=ru&onload=onloadReCaptcha&render=explicit" async defer></script>

					<script>
						var recaptcha_2;
						var onloadReCaptcha = function() { recaptcha_2 = grecaptcha.render('recaptcha_2', {"sitekey":"{{$conf->captcha_client_key}}"}); }
					</script>
				{/if}
				<div id="result3" class="mt-3"></div>

				<input type="submit" value="Выслать новый пароль" class="btn btn-primary mt-2">
			</form>
			<script> send_form('#send_new_pass', 'send_new_pass();'); </script>
		{else}
			{message}
		{/if}
	</div>
</div>