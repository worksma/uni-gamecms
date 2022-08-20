<tr>
	<td>
	{if('{shilings}' > '0')}
		<span class="text-success">{shilings} {{sys()->currency()->lang}}</span>
	{else}
		<span class="text-danger">{shilings} {{sys()->currency()->lang}}</span>
	{/if}
	</td>
	<td>{type}</td>
	<td>{date}</td>
</tr>