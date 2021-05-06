<tr>
	<td>
	{if('{shilings}' > '0')}
		<p class="text-success">{shilings} {{$messages['RUB']}}</p>
	{else}
		<p class="text-danger">{shilings} {{$messages['RUB']}}</p>
	{/if}
	</td>
	<td>{type}</td>
	<td>{date}</td>
</tr>