<?
	$frame = trading()->get_resource_active(3, '{user_id}');
?>
<div class="comment" id="message_id_{id}">
	<a href="../profile?id={user_id}" title="{login}">
		{if(isset($frame))}
			<div class="playground ml-2">
				<div class="frame">
					<img src="/files/playground/{{$frame}}" alt="{login}" class="rounded-0">
				</div>

				<img src="<?=convert_avatar('{user_id}');?>" alt="{login}" class="rounded-0">
			</div>
		{else}
			<img src="<?=convert_avatar('{user_id}');?>" alt="{login}">
		{/if}
	</a>
	<div>
		<div class="info">
			<div class="author">
				<a title="Группа: {gp_name}" style="color: {gp_color}" onclick="treatment_comment('{login}')" >{login}</a>
			</div>
			<div class="date" tooltip="yes" data-placement="left" title="{date_full}, {date_short}">{date_full}, {date_short}</div>
			{dell}
		</div>
		<div class="with_code">
			{text}
		</div>
	</div>
</div>
<div class="clearfix"></div>