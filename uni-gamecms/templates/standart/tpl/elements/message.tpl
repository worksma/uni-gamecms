<?
	$frame = trading()->get_resource_active(3, '{user_id1}');
?>
<style>
	#messages {
		overflow: hidden;
	}
</style>
<div class="row mt-2 ml-2" id="mess_{id}">
		{if(isset($frame))}
		<div class="playground m-40">
			<div class="frame">
				<img src="/files/playground/{{$frame}}">
			</div>

			<img src="<?=convert_avatar('{user_id1}');?>">
		</div>
		{else}
			<img src="<?=convert_avatar('{user_id1}');?>" width="40" height="40" class="rounded-circle">
		{/if}

		<div class="ml-2 info">
			{if($very = new Verification($pdo) and $very->is_very('{user_id1}'))}
				<a href="../profile?id={user_id1}">{login}</a><?echo $very->get_very_style('standart');?>
			{else}
				<a href="../profile?id={user_id1}">{login}</a>
			{/if}
			
			<span class="date" tooltip="yes" data-placement="left" title="{date_full}">{date_short}</span>
			<br>
			{text}
		</div>
</div>