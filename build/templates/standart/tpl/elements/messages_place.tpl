<div id="chat">
	<form>
		<div id="drop_zone" class="">
			<div id="drop_mask"></div>

			<div id="messages">
				{messages}
			</div>
			<input id="load_val" name="load_val" type="hidden" value="1" />
			<input id="last_mess" name="last_mess" type="hidden" value="{last_mess}" />
			<input id="stop_sending" type="hidden" value="0">
		</div>
		<textarea class="form-control mt-1" name="text" id="text"></textarea>
		<div class="smile_input_forum mt-2">
			<input id="send_button" type="button" class="btn btn-primary" value="Отправить" onclick="{func}('{id}');">
			<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
		</div>
	</form>
</div>
<div id="passive" class="disp-n">
	<center>
		<img src="{site_host}templates/{template}/img/zzz.png">
		<p class="mt-10">Включен пассивный режим.</p>
		<p>Вы отсутствовали более чем <b></b> минут.</p>
	</center>
</div>
<script>
	$(document).ready( function(){
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

				clearInterval(pm_interval);
				NProgress.start();
				$.ajax({
					type: "POST",
					url: "../ajax/pm_actions.php",
					data: data,
					processData: false,
					contentType: false,
					dataType: "json",

					success: function(result) {
						dropZone.removeClass('loader');
						NProgress.done();
						get_messages(1,{id});
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
						get_messages(1,{id});
					}
				});
			};
		}
	});

	//smiles&stickers
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
	function set_sticker(elem){
		if($('#stop_sending').val() == 0){
			var text = "sticker"+$(elem).attr("src");
			text = encodeURIComponent(text);
			{func}('{id}', text);

			$('#smile_btn').popover('hide');
			selected = 'gcms_smiles';
		}
	}
	function set_smile(elem){
		$("#text").focus();
		var text = $("#text").val();
		var smile = $(elem).attr("title");
		$("#text").val(text+" "+smile+" ");
		$('#smile_btn').popover('hide'); selected = 'gcms_smiles';
	}

	//init chat
	set_enter('#text', '{func}({id})');
	get_smiles('#smile_btn');
	clearInterval(pm_interval);
	clearInterval(check_mess);

	{if('{func}' == 'send_message')}
		check_messages('none', 2);
		var check_mess = setInterval("check_messages({id}, 2)", 30000);
		var pm_interval = setInterval("get_messages(2,{id})", 5000);

		var block = document.getElementById("messages");
		var load_val = $('#load_val').val();
		block.onscroll = function() {
			if((block.scrollTop < 300) && (load_val == $('#load_val').val())) {
				$('#load_val').val(+load_val + 1);
				load_messages({id});
			}
		}

		idleTimer = null;
		idleState = false;
		idleWait = 600000;
		$(document).ready( function(){
			$(document).bind('mousemove keydown scroll', function(){
				clearTimeout(idleTimer);
				if(idleState == true){ 
					reset_page();
				}

				idleState = false;
				idleTimer = setTimeout(function(){ 
					clearInterval(pm_interval);
					$("#chat").fadeOut();
					$("#passive").fadeIn();
					$("#passive b").append(idleWait/1000/60);
					idleState = true; 
				}, idleWait);
			});
			$("body").trigger("mousemove");
		});
	{/if}
</script>