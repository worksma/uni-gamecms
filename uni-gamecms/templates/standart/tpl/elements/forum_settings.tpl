<tr id="forum{id}">
	<td>
		<img id="image{id}" src="../{img}">
	</td>
	<td>
		<div class="input-group">
			<form enctype="multipart/form-data" id="img_form{id}">
				<input type="hidden" id="token" name="token" value="{token}">
				<input type="hidden" id="id" name="id" value="{id}">
				<input type="hidden" id="load_forum_img" name="load_forum_img" value="1">
				<input type="hidden" id="phpaction" name="phpaction" value="1">
				<input type="file" id="img{id}" accept="image/*" name="img">
				<div id="img_result{id}"></div>
			</form>
			<script>
				$("#img{id}").on('change', function(event){
					NProgress.start();
					event.preventDefault();
					var data = new FormData($('#img_form{id}')[0]);
					$.ajax({
						type: "POST",
						url: "../ajax/actions_c.php",
						data: data,
						contentType: false,
						processData: false,
					}).done(function (html) {
						$("#img_result{id}").empty();
						$("#img_result{id}").append(html);
						$('#img_form{id}')[0].reset();
						NProgress.done();
					});
				});
			</script>
		</div>
	</td>
	<td>
		<input id="forum_name{id}" type="text" class="form-control input-sm" value="{name}">
	</td>
	<td>
		<input id="forum_description{id}" type="text" class="form-control input-sm" value="{description}">
	</td>
	<td>
		<div class="btn-group-vertical w-100">
			<button onclick='up_forum("{id}","{section_id}");' class="btn btn-primary btn-sm">
				<i class="d-block d-lg-none m-icon icon-up"></i> <span class="d-none d-lg-block">Поднять</span>
			</button>
			<button onclick='down_forum("{id}","{section_id}");' class="btn btn-primary btn-sm">
				<i class="d-block d-lg-none m-icon icon-down"></i> <span class="d-none d-lg-block">Опустить</span>
			</button>
		</div>
	</td>
	<td>
		<div class="btn-group-vertical w-100">
			<button id="edit_forum_btn{id}" onclick='edit_forum("{id}");' class="btn btn-primary btn-sm">
				<i class="d-block d-lg-none m-icon icon-pencil"></i> <span class="d-none d-lg-block">Изменить</span>
			</button>
			<button onclick='dell_forum("{id}");' class="btn btn-primary btn-sm">
				<i class="d-block d-lg-none m-icon icon-trash"></i> <span class="d-none d-lg-block">Удалить</span>
			</button>
		</div>
	</td>
</tr>