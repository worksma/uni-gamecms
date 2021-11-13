<style>
	#messages {
		overflow: hidden;
	}
</style>
<div class="row mt-2" id="mess_{id}">
		<?$playground = new Playground($pdo, $conf);?>
		
		{if($fmimage = $playground->get_resource_active(3, {user_id1}))}
			<div class="profile-frame mb-4 ml-4">
				<div class="profile-avatar-frame">
					<img src="../files/playground/<?echo $fmimage;?>">
				</div>
				{if($avaimage = $playground->get_resource_active(2, {user_id1}))}
					<img src="../files/playground/<?echo $avaimage;?>">
				{else}
					<img src="../{avatar}">
				{/if}
			</div>
		{else}
			{if($avaimage = $playground->get_resource_active(2, {user_id1}))}
				<img src="../files/playground/<?echo $avaimage;?>">
			{else}
				<img src="../{avatar}">
			{/if}
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