<script>
	var pm_interval;
	var check_mess;
</script>

<div class="col-lg-9 order-is-first">
	<div id="messages_sound_player"></div>
	<div class="block dialogs">
		<div class="block_head">
			Диалоги
			<button id="back_btn" class="disp-n btn btn-outline-primary btn-sm" onclick="load_dialogs();">К диалогам</button>
		</div> 
		<div id="place_for_messages">
			<div class="loader"></div>
		</div>
		<script>
			{load_dialogs}
		</script>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<div class="block_head">
			Друзья
		</div> 
		<div id="companions">
			<div class="loader"></div>
			<script>load_companions();</script>
		</div>
	</div>

	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>