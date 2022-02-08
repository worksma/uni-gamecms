<tr id="{id}">
	<td>{i}</td>
	<td>{name}</td>
	<td>{rights}</td>
	<td>{bought_date}</td>
	<td>
		<div>
			<p>{ending_date}</p>
			<button id="extend{id}" class="btn btn-default btn-sm {disp}" {if('{active}' != '2')}onclick="show_tarifs({id})";{/if} {disabled}>Продлить</botton>
		</div>
		{if('{active}' != '2')}
			<div class="clearfix"></div>
			<div class="disp-n" id="extend_block{id}">
				<button id="extend_btn{id}" {if('{active}' != '2')}onclick="buy_extend({id}, {id2});"{/if} class="btn btn-default btn-sm" type="button" {disabled}>ОК</button>
				{services}
			</div>
		{/if}
	</td>
	<td>
		<p class="text-{color}">
			{left}
		</p>
		{if('{sum}' != '0')}
			<button id="return{id}" class="btn btn-default btn-sm" {if('{active}' != '2')}onclick="get_return({id});"{/if} {disabled}>Возврат {sum}{{$messages['RUB']}}</botton>
		{/if}
	</td>
</tr>

