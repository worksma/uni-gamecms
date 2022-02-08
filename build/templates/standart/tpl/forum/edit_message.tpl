<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Редактирование сообщения
		</div>

		<textarea id="text" rows="10">{text}</textarea>

		<div class="smile_input_forum mt-3">
			<button id="create_btn" onclick="edit_message('{id}');" type="button" class="btn btn-primary">Изменить</button>
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