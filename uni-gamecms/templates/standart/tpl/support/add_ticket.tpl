<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Создать тикет
		</div>

		<div class="form-group">
			<label>
				<h4>
					Тема
				</h4>
			</label>
			<input type="text" class="form-control" id="name" maxlength="255" autocomplete="off">
		</div>

		<div class="form-group">
			<label>
				<h4>
					Текст
				</h4>
			</label>
			<textarea id="text" rows="10" maxlength="2000"></textarea>
		</div>

		<div id="ticket_result"></div>
		<button onclick="add_ticket();" type="button" class="btn btn-primary">Создать</button>
	</div>
</div>

<script>
	$(document).ready(function() {
		init_tinymce("text", "lite", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
	});
	$("#load_file_form").submit(function (event){
		NProgress.start();
		event.preventDefault();
		var data = new FormData($('#load_file_form')[0]);
		$.ajax({
			type: "POST",
			url: "../ajax/actions_a.php",
			data: data,
			contentType: false,
			processData: false,
		}).done(function (html) {
			NProgress.done();
			$("#load_file_result").html(html);
			$('#load_file_form')[0].reset();
		});
	});
</script>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>