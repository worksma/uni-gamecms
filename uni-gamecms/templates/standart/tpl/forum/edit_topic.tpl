<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Редактирование темы
		</div>
		{if(is_worthy("e"))}
		<div class="form-group">
			<label for="input_keywords">
				<h4>
					Форум
				</h4>
			</label>
			<div id="forums_list"></div>
			<script>load_forums_list('{forum_id}');</script>
			<button id="create_btn2" onclick="edit_topic_forum('{id}');" type="button" class="btn btn-primary">Изменить</button>
		</div>
		<hr>
		<div class="form-group">
			<label for="input_keywords">
				<h4>
					Статус
				</h4>
			</label>
			<select id="status" class="form-control">
				<option value="1" {if('{status}'=='1')} selected {/if}>Открыта</option>
				<option value="2" {if('{status}'=='2')} selected {/if}>Закрыта</option>
				<option value="3" {if('{status}'=='3')} selected {/if}>Закреплена</option>
				<option value="4" {if('{status}'=='4')} selected {/if}>Закреплена и закрыта</option>
			</select>
			<button id="create_btn3" onclick="edit_topic_status('{id}');" type="button" class="btn btn-primary">Изменить</button>
		</div>
		<hr>
		{/if}
		<div class="form-group">
			<label for="img">
				<h4>
					Изображение
				</h4>
			</label>
			<div class="row">
				<div class="col-lg-2">
					<img id="pic" src="../{img}" class="w-100">
				</div>
				<div class="col-lg-10">
					<form enctype="multipart/form-data" action="ajax/actions_a.php" method="POST" id="add_img_form">
						<input type="hidden" id="token" name="token" value="{token}">
						<input type="hidden" id="id" name="id" value="{id}">
						<input type="hidden" id="add_topic_img" name="add_topic_img" value="1">
						<input type="hidden" id="phpaction" name="phpaction" value="1">
						<input type="file" id="img" accept="image/*" name="img"/><br>
						<input class="btn btn-primary" type="submit" value="Загрузить">
						<div id="img_result">
							<input value="{img}" type="hidden" id="topic_img" maxlength="255" autocomplete="off">
						</div>
					</form>
				</div>
			</div>
			<script>
				$("#add_img_form").submit(function (event){
					NProgress.start();
					event.preventDefault();
					var data = new FormData($('#add_img_form')[0]);
					$.ajax({
						type: "POST",
						url: "../ajax/actions_a.php",
						data: data,
						contentType: false,
						processData: false,
					}).done(function (html) {
						$("#img_result").empty();
						$("#img_result").append(html);
						$('#add_img_form')[0].reset();
						edit_topic('{id}', 1);
					});
					NProgress.done();
				});
			</script>
		</div>
		<div class="form-group">
			<label for="name">
				<h4>
					Название
				</h4>
			</label>
			<input type="text" class="form-control" id="name" maxlength="250" autocomplete="off" value="{name}">
		</div>
		<div class="form-group">
			<label for="input_keywords">
				<h4>
					Текст темы
				</h4>
			</label>
			<textarea id="text" rows="10">{text}</textarea>
		</div>
		
		<div class="smile_input_forum">
			<button id="create_btn" onclick="edit_topic('{id}');" type="button" class="btn btn-primary">Изменить</button>
			<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
		</div>
		<div id="topic_result"></div>
	</div>
</div>
<script>
	$(document).ready(function() {
		init_tinymce("text", "forum", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
		get_smiles('#smile_btn', 1);
	});

	$('#smile_btn').popover({ html: true, animation: true, trigger: "click" });
	$('#smile_btn').on('show.bs.popover', function () {
		$(document).mouseup(function (e) {
			var container = $(".popover-body");
			if (container.has(e.target).length === 0){
				$('#smile_btn').popover('hide');
				selected = 'gcms_smiles';
			}
		});
	});

	function set_smile(elem){
		var smile =  "<img src=\""+$(elem).attr("src")+"\" class=\"g_smile\" height=\"20px\" width=\"20px\">";
		tinymce.activeEditor.insertContent(smile);
		$('#smile_btn').popover('hide');
		selected = 'gcms_smiles';
	}
</script>

<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/forum/sidebar.tpl"}
</div>