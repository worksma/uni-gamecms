<style>
	.mini-friend-frame {
		position: relative;
		margin-left:4px;
	}
	
	.mini-friend-frame img {
		background: none;
		filter: none;
		display: block;
		padding: 0;
		width: 100%;
		height: 100%;
	}
	
	.mini-friend-avatar-frame {
		position: absolute;
	}
	
	.mini-friend-avatar-frame > img {
		transform: scale(1.23);
		background: none;
	}
</style>
<a class="small-friend" href="../profile?id={id}" title="{login}">
	<?$playground = new Playground($pdo, $conf);?>
	
	{if($fmimage = $playground->get_resource_active(3, {id}))}
		<div class="mini-friend-frame">
			<div class="mini-friend-avatar-frame">
				<img style="border-radius: 0px !important;" src="../files/playground/<?echo $fmimage;?>">
				</div>
			{if($avaimage = $playground->get_resource_active(2, {id}))}
				<img style="border-radius: 0px !important;" src="../files/playground/<?echo $avaimage;?>">
			{else}
				<img style="border-radius: 0px !important;" src="{avatar}">
			{/if}
			</div>
	{else}
		{if($avaimage = $playground->get_resource_active(2, {id}))}
			<img src="../files/playground/<?echo $avaimage;?>">
		{else}
			<img src="{avatar}">
		{/if}
	{/if}
	<span>{login}</span>
</a>