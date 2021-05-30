<style>
	.div-frame {
		position: relative;
		margin-left:4px;
	}
	
	.div-frame img {
		background: none;
		filter: none;
		display: block;
		padding: 0;
		width: 100%;
		height: 100%;
	}
	
	.div-avatar-frame {
		position: absolute;
	}
	
	.div-avatar-frame > img {
		transform: scale(1.23);
		background: none;
	}
</style>
<div class="chat_message" id="message_id_{id}">
	<a href="../profile?id={user_id}" title="{gp_name}">
		<?$playground = new Playground($pdo, $conf);?>
		
		{if($fmimage = $playground->get_resource_active(3, {user_id}))}
			<div class="div-frame mb-4">
				<div class="div-avatar-frame">
					<img style="border-radius: 0px !important;" src="../files/playground/<?echo $fmimage;?>">
				</div>
				{if($avaimage = $playground->get_resource_active(2, {user_id}))}
					<img style="border-radius: 0px !important;" src="../files/playground/<?echo $avaimage;?>">
				{else}
					<img style="border-radius: 0px !important;" src="{avatar}">
				{/if}
			</div>
		{else}
			{if($avaimage = $playground->get_resource_active(2, {user_id}))}
				<img src="../files/playground/<?echo $avaimage;?>">
			{else}
				<img src="{avatar}">
			{/if}
		{/if}
	</a>
	<div class="message">
		<div class="info">
			{if($very->is_very('{user_id}'))}
				<div class="author" onclick="treatment('{login}');" title="{gp_name}" style="color: {gp_color}">{login} </div><?echo $very->get_very_style('standart');?>
			{else}
				<div class="author" onclick="treatment('{login}');" title="{gp_name}" style="color: {gp_color}">{login}</div>
			{/if}
			
			<div class="date" tooltip="yes" data-placement="left" title="{date_full}">{date_short}</div>
			{if(strripos("{gp_rights}", "d") !== false)}
				<span onclick="dell_chat_message('{id}');" tooltip="yes" data-placement="left" title="Удалить" class="m-icon icon-trash dell_message"></span>
				<span id="edit_message_{id}" onclick="edit_chat_message('{id}', this);" tooltip="yes" data-placement="left" title="Редактировать" class="m-icon icon-pencil edit_message"></span>
			{/if}
		</div>
		<div id="message_text_{id}" class="with_code">
			{text}
		</div>
		{if(strripos("{gp_rights}", "d") !== false)}
			<textarea id="message_text_e_{id}" class="form-control disp-n">{text}</textarea>
		{/if}
	</div>
</div>