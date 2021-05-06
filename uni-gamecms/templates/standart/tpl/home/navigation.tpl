<div class="block">
	<div class="block_head">
		Навигация
	</div>
	<div class="vertical-navigation">
		<ul>
			{for($i=0;$i < count($vertical_menu);$i++)}  
			<li>
				<a href="{{$vertical_menu[$i]['link']}}">{{$vertical_menu[$i]['name']}}</a>
			</li>
			{/for}
		</ul>
	</div>
</div>