<tr>
	<td>{id}</td>
	<td id="name{id}">{name}</td>
	<td id="price{id}">{price}</td>
	<td id="availability{id}">{availability}</td>
	<td>
		<button class="btn btn-default" onclick="onEditor({id});" id="onEditor{id}">
			<i id="onIcon{id}" class="glyphicon glyphicon-edit"></i>
		</button>
		<button class="btn btn-default rcon" data-product="{id}">
			<i class="glyphicon glyphicon-export"></i>
		</button>
		<button class="btn btn-default" onclick="remove({id});">
			<i class="glyphicon glyphicon-trash"></i>
		</button>
	</td>
</tr>