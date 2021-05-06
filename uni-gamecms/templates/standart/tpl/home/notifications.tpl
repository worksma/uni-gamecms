<div class="col-lg-3 order-is-last">
	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar.tpl"}
</div>

<div class="col-lg-9 order-is-first">
	<div class="block disp-n" id="notifications_line">
		<div class="block_head">
			Уведомления
			<button onclick="dell_notifications();" class="btn btn-outline-primary btn-sm">Удалить все</button>
		</div> 
		<div id="notifications">
			{func GetData:notifications("{start}", "{limit}")}
		</div>
	</div>

	<div id="pagination2"><center>{pagination}</center></div>
</div>




