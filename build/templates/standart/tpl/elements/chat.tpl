<div class="block">
	<div class="block_head">
		Чат
	</div>
	<div id="chat">
		<form>
			<div id="drop_zone" class="">
				<div id="drop_mask"></div>

				<div id="chat_messages">
					<div class="loader"></div>
				</div>

				<div id="chat_sound_playes"></div>
				<input id="load_val" type="hidden" value="1" />
				<input id="last_mess" type="hidden" value="" />
				<input id="stop_sending" type="hidden" value="0">
			</div>
			{if(is_auth())}
			<div class="input-group mt-3">
				<div class="smile_input">
					<input id="message_input" type="text" class="form-control" placeholder="Введите сообщение...">
					<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
				</div>
				<span class="input-group-btn">
					<input id="send_button" type="button" class="btn btn-default" value="Отправить" onclick="chat_send_message();"/>
				</span>
			</div>
			{else}
			<p class="mb-0 mt-4 text-center"><a class="small" data-toggle="modal" data-target="#authorization">Авторизуйтесь</a>, чтобы отправлять сообщения</p>
			{/if}
		</form>
	</div>
	<div id="passive" class="disp-n">
		<center>
			<img src="{site_host}templates/{template}/img/zzz.png">
			<p class="mt-10">Включен пассивный режим.</p>
			<p>Вы отсутствовали более чем <b></b> минут.</p>
		</center>
	</div>
</div>
<script>
	//drag&drop
	$(document).ready(function(){
		var dropZone = $('#drop_zone');
		var dropMask = $('#drop_mask');
		var maxFileSize = 2*1024*1024; //2мб макс размер
		var drop_file = true; //false - выкл , true - вкл | загрузка изображений в чат

		if (typeof(window.FileReader) != 'undefined' && drop_file) {
			dropZone[0].ondragover = function(event) {
				dropZone.addClass('hover');
				dropMask.show();
				return false;
			};

			dropMask[0].ondragleave = function() {
				dropZone.removeClass('hover');
				dropMask.hide();
				return false;
			};

			dropMask[0].ondrop = function(event) {
				event.preventDefault();
				dropMask.hide();
				dropZone.removeClass('hover');
				dropZone.addClass('loader');

				if(event.dataTransfer.files[0] == undefined) {
					dropZone.removeClass('loader');
					show_noty('Down', 'error', '<a>Неверный тип файла</a>', '3000');
					dropZone.addClass('error');
					setTimeout(function(){
						dropZone.removeClass('error');
					}, 2000);
					return false;
				} else {
					var file = event.dataTransfer.files[0];
				}

				if (file.size > maxFileSize) {
					dropZone.removeClass('loader');
					show_noty('Down', 'error', '<a>Файл слишком большой</a>', '3000');
					dropZone.addClass('error');
					setTimeout(function(){
						dropZone.removeClass('error');
					}, 2000);
					return false;
				}

				var data = new FormData;
				data.append("file", file);
				data.append("token", $('#token').val());
				data.append("drop_img", '1');
				data.append("phpaction", '1');
				data.append("id", '{id}');

				clearInterval(chat_interval);
				NProgress.start();
				$.ajax({
					type: "POST",
					url: "../ajax/chat_actions.php",
					data: data,
					processData: false,
					contentType: false,
					dataType: "json",

					success: function(result) {
						dropZone.removeClass('loader');
						NProgress.done();
						chat_get_messages(1);
						if(result.status == 1){
							setTimeout(show_ok, 500);
						} else {
							setTimeout(show_error, 500);
							show_noty('Down', 'error', '<a>'+result.data+'</a>', '3000');
							dropZone.addClass('error');
							setTimeout(function(){
								dropZone.removeClass('error');
							}, 2000);
						}
					},
					error: function(result){
						dropZone.removeClass('loader');
						NProgress.done();
						chat_get_messages(1);
					}
				});
			};
		}
	});

	//smiles&stickers
	$('#smile_btn').popover({ html: true, animation: true, trigger: 'click' });
	$('#smile_btn').on('show.bs.popover', function () {
		$(document).mouseup(function (e) {
			var container = $(".popover-body");
			if (container.has(e.target).length === 0){
				$('#smile_btn').popover('hide');
				selected = 'gcms_smiles';
			}
		});
	});
	function set_sticker(elem){
		if($('#stop_sending').val() == 0){
			var text = "sticker"+$(elem).attr("src");
			text = encodeURIComponent(text);
			chat_send_message(text);

			$('#smile_btn').popover('hide');
			selected = 'gcms_smiles';
		}
	}
	function set_smile(elem){
		$("#message_input").focus();
		var text = $("#message_input").val();
		var smile = $(elem).attr("title");
		$("#message_input").val(text+" "+smile+" ");
		
		$('#smile_btn').popover('hide');
		selected = 'gcms_smiles';
	}

	//init chat
	get_smiles('#smile_btn');
	chat_first_messages();
	set_enter('#message_input', 'chat_send_message()');

	var block = document.getElementById("chat_messages");
	var load_val = $('#load_val').val();
	block.onscroll = function() {
		if((block.scrollTop < 300) && (load_val == $('#load_val').val())) {
			$('#load_val').val(+load_val + 1);
			chat_load_messages();
		}
	}

	idleTimer = null;
	idleState = false;
	idleWait = 600000;

	$(document).ready(function(){
		$(document).bind('mousemove keydown scroll', function(){
			clearTimeout(idleTimer);
			if(idleState == true){ 
				reset_page();
			}

			idleState = false;
			idleTimer = setTimeout(function(){ 
				clearInterval(chat_interval);
				$("#chat").fadeOut();
				$("#passive").fadeIn();
				$("#passive b").append(idleWait/1000/60);
				idleState = true; 
			}, idleWait);
		});
		$("body").trigger("mousemove");
	});
</script>