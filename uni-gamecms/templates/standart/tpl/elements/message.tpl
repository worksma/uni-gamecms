<style>
	#messages {
		overflow: hidden;
	}

	.row {
		margin-top: 10px;
	}
	
	.avatar img {
		width: 100%;
		margin-left: 10px;
	}
	
	.private-chat-frame {
		position: relative;
		margin-left:4px;
	}
	
	.private-chat-frame img {
		background: none;
		filter: none;
		display: block;
		padding: 0;
		width: 100%;
		height: 100%;
	}
	
	.private-chat-avatar-frame {
		position: absolute;
	}
	
	.private-chat-avatar-frame > img {
		transform: scale(1.23);
		background: none;
	}
</style>
<div class="row" id="mess_{id}">
	<div class="col-lg-2 avatar">
		<?$playground = new Playground($pdo, $conf);?>
		
		{if($fmimage = $playground->get_resource_active(3, {user_id1}))}
			<div class="private-chat-frame mb-4">
				<div class="private-chat-avatar-frame">
					<img src="../files/playground/<?echo $fmimage;?>">
				</div>
				{if($avaimage = $playground->get_resource_active(2, {user_id1}))}
					<img src="../files/playground/<?echo $avaimage;?>">
				{else}
					<img src="{avatar}">
				{/if}
			</div>
		{else}
			{if($avaimage = $playground->get_resource_active(2, {user_id1}))}
				<img src="../files/playground/<?echo $avaimage;?>">
			{else}
				<img src="{avatar}">
			{/if}
		{/if}
	</div>
	<div class="col-lg-10">
		<div class="info">
			{if($very = new Verification($pdo) and $very->is_very('{user_id1}'))}
				<a href="../profile?id={user_id1}">{login}</a><?echo $very->get_very_style('standart');?>
			{else}
				<a href="../profile?id={user_id1}">{login}</a>
			{/if}
			
			<span class="date" tooltip="yes" data-placement="left" title="{date_full}">{date_short}</span>	
		</div>
		{text}
	</div>
</div>