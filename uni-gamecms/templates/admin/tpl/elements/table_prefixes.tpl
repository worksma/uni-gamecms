<tr>
	<td width="1%">{id}</td>
	<td><input class="form-control" type="text" maxlength="7" id="time{id}" min="0" placeholder="Время (в днях, 0 - навсегда)" autocomplete="off" value="{time}"></td>
	<td><input class="form-control" type="number" maxlength="6" min="0" id="price{id}" placeholder="Цена покупки" autocomplete="off" value="{price}"></td>
	<td><input class="form-control" type="number" maxlength="2" min="0" max="99" id="discount{id}" placeholder="Скидка (в % от 0 до 99)" autocomplete="off" value="{discount}"></td>
	<td><input class="form-control" type="text" id="rcon{id}" placeholder="RCON команда для выполнения" autocomplete="off" value="{rcon}"></td>
	
	<td width="20%">
		<div class="btn-group" role="group">
			<button onclick="editTerm({id});" class="btn btn-default" type="button">
				<span class="glyphicon glyphicon-pencil"></span>
			</button>
			<button onclick="delTerm({id});" class="btn btn-default" type="button">
				<span class="glyphicon glyphicon-trash"></span>
			</button>
		</div>
	</td>
</tr>