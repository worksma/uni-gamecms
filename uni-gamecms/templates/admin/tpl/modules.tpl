<div class="page">
	<div class="col-md-12">
		{if('{dev_mode}' == '1' && '{safe_mode}' != '1')}
		<div class="block">
			<div class="block_head">
				Установить модуль, закачав zip архив
			</div>
			<form enctype="multipart/form-data" action="ajax/actions_panel.php" method="POST" id="load_module_file">
				<input type="hidden" id="token" name="token" value="{token}">
				<input type="hidden" id="install_module" name="install_module" value="1">
				<input type="hidden" id="phpaction" name="phpaction" value="1">
				<input class="f-l" type="file" id="zip_file" accept="zip/*" name="zip_file"/>
				<input id="btn" class="btn btn-default" type="submit" value="Установить">
			</form>
			<div id="loading_result"></div>
		</div>
		<script>
			$("#load_module_file").submit(function (event){
				event.preventDefault();
				var data = new FormData($('#load_module_file')[0]);
				$.ajax({
					type: "POST",
					url: "../ajax/actions_panel.php",
					data: data,
					contentType: false,
					processData: false,
					beforeSend: function() {
						$('#btn').attr('onclick', '');
						$('#btn').attr('value', 'Установка...');
					}
				}).done(function (html) {
					$("#loading_result").empty();
					$("#loading_result").append(html);
					$('#btn').attr('value', 'Установить');
					$('#load_module_file')[0].reset();
				});
			});
		</script>
		{/if}
		<div class="block">
			<div class="block_head">
				Установить модуль, введя ключ
			</div>
			<div class="input-group">
				<span class="input-group-btn">
					<button class="btn btn-default" onclick="install_module_by_key();">Установить</button>
				</span>
				<input type="text" class="form-control" id="module_key" maxlength="40" autocomplete="off" placeholder="Введите ключ">
			</div>
			<div id="installing_result"></div>
		</div>

		<div id="modules">
			<center><img src="{site_host}templates/admin/img/loader.gif"></center>
			<script>load_modules();</script>
		</div>
	</div>
</div>