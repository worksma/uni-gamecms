{if(is_worthy("t"))}
<div class="col-lg-6">
	<div class="block">
		<a data-target="#add_section" data-toggle="modal" class="btn btn-outline-primary btn-xl">Добавить раздел</a>
	</div>
</div>
<div class="col-lg-6">
	<div class="block">
		<a data-target="#add_forum" onclick="load_sections_list();" data-toggle="modal" class="btn btn-outline-primary btn-xl">Добавить форум</a>
	</div>
</div>
{/if}

<div class="col-lg-12">
	<div id="sections">
		<div class="loader"></div>
		<script>load_sections();</script>
	</div>
</div>

<div id="add_section" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Добавить раздел</h4>
			</div>
			<div class="modal-body">
				<form id="section_settings">
					<div class="form-group">
						<label for="section_name">
							<h4>Название</h4>
						</label>
						<input type="text" class="form-control" name="section_name" id="section_name" maxlength="255" autocomplete="off">
					</div>
					<div class="form-group">
						<label>
							<h4>Доступ</h4>
						</label>
						<div class="btn-group-toggle" data-toggle="buttons" id="access">
							{groups}
						</div>
					</div>

					<button onclick="add_section();" type="button" class="btn btn-primary mt-3">Создать</button>
				</form>
			</div>
		</div>
	</div>
</div>

<div id="add_forum" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">Добавить форум</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="section_name">
						<h4>Изображение</h4>
					</label>
					<div class="row">
						<div class="col-lg-2">
							<img id="image" src="../files/forums_imgs/none.jpg" class="w-100">
						</div>
						<div class="col-lg-10">
							<div class="input-group">
								<form enctype="multipart/form-data" action="ajax/actions_c.php" method="POST" id="img_form">
									<input type="hidden" id="token" name="token" value="{token}">
									<input type="hidden" id="id" name="id" value="">
									<input type="hidden" id="load_forum_img" name="load_forum_img" value="1">
									<input type="hidden" id="phpaction" name="phpaction" value="1">
									<input type="file" id="img" accept="image/*" name="img">
									<br>
									<input class="btn btn-primary" type="submit" value="Загрузить">
									<div id="img_result"></div>
								</form>
								<script>
									$("#img_form").submit(function (event){
										NProgress.start();
										event.preventDefault();
										var data = new FormData($('#img_form')[0]);
										$.ajax({
											type: "POST",
											url: "../ajax/actions_c.php",
											data: data,
											contentType: false,
											processData: false,
											beforeSend: function() {
												$('#img_loader').show();
											}
										}).done(function (html) {
											$("#img_result").empty();
											$("#img_result").append(html);
											$('#img_form')[0].reset();
											NProgress.done();
										});
									});
								</script>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label for="section_name">
						<h4>Название</h4>
					</label>
					<input type="text" class="form-control" id="forum_name" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<label for="section_name">
						<h4>Описание</h4>
					</label>
					<input type="text" class="form-control" id="forum_description" maxlength="255" autocomplete="off">
				</div>
				<div class="form-group">
					<label for="section_name">
						<h4>Раздел</h4>
					</label>
					<div id="sections_list"></div>
				</div>
				<div id="add_forum_result"></div>
				<button onclick="add_forum();" type="button" class="btn btn-primary mt-3">Создать</button>
			</div>
		</div>
	</div>
</div>