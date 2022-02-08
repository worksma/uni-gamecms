<?
	$frame = trading()->get_resource_active(3, '{id}');
?>
{if(isset($frame))}
<a class="small-friend playground" href="../profile?id={id}" title="{login}">
{else}
<a class="small-friend" href="../profile?id={id}" title="{login}">
{/if}
	{if(isset($frame))}
		<div class="playground">
			<div class="frame">
				<img src="/files/playground/{{$frame}}" alt="{login}" class="rounded-0">
			</div>
			<img src="<?=convert_avatar('{id}');?>" alt="{login}" class="rounded-0">
		</div>
	{else}
		<img src="<?=convert_avatar('{id}');?>" alt="{login}">
	{/if}
	<span>{login}</span>
</a>