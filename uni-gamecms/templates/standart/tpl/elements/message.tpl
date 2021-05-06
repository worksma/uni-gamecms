<div class="im_message" id="mess_{id}">
	<img src="{avatar}" alt="{login}">
	<div>
		<div class="info">
			{if($very = new Verification($pdo) and $very->is_very('{user_id1}'))}
				<a href="../profile?id={user_id1}">{login}</a><?echo $very->get_very_style('standart');?>
			{else}
				<a href="../profile?id={user_id1}">{login}</a>
			{/if}
			
			<span class="date" tooltip="yes" data-placement="left" title="{date_full}">{date_short}</span>	
		</div>
		<div class="with_code">{text}</div>
	</div>
</div>