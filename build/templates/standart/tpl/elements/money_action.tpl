<tr>
	<td>
	{if('{shilings}' > '0')}
		<span class="text-success">{shilings} {{$messages['RUB']}}</span>
	{else}
		<span class="text-danger">{shilings} {{$messages['RUB']}}</span>
	{/if}
	</td>
	<td>{type}</td>
	<td>{date}</td>
</tr>