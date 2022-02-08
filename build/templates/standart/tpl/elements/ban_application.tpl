<tr>
	<td>
		<a href="../bans/ban?id={id}">Подробнее</a>
	</td>
	<td class="d-flex align-items-center">
		<?
			$frame = trading()->get_resource_active(3, '{author}');
		?>
		{if(isset($frame))}
			<div class="playground m-40 mt-2 col-lg-3">
				<div class="frame">
					<img src="/files/playground/{{$frame}}" class="rounded-0 m-40">
				</div>
				<img src="<?=convert_avatar('{author}');?>" alt="{login}" class="rounded-0 m-40"> 
			</div>
		{else}
			<img src="<?=convert_avatar('{author}');?>" alt="{login}" class="m-40">
		{/if}
		<a target="_blank" href="../profile?id={author}" class="col d-flex align-items-center">
			{login}
		</a>
	</td>
	<td><p class="text-{color}">{status}</p></td>
	<td>{date}</td>
</tr>