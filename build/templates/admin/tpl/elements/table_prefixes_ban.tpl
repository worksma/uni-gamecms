<tr>
	<td width="1%">{id}</td>
	<td><input class="form-control" type="text" id="speech{id}" placeholder="Слово" autocomplete="off" value="{speech}"></td>
	
	<td width="20%">
		<div class="btn-group" role="group">
			<button onclick="editSpeech({id});" class="btn btn-default" type="button">
				<span class="glyphicon glyphicon-pencil"></span>
			</button>
			<button onclick="delSpeech({id});" class="btn btn-default" type="button">
				<span class="glyphicon glyphicon-trash"></span>
			</button>
		</div>
	</td>
</tr>