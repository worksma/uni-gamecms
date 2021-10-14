<tr>
    <td>{i}</td>
    <td>{name}</td>
    <td>{frags}</td>
    <td>{time}</td>
    {if('{server_rcon}' == '1' && is_auth() && is_worthy_specifically("s", '{server_id}'))}
        <td>
           {if(count($commands) == 0)}
                Комманд нет
           {else}
               <div class="input-group input-group-sm">
                   <div class="input-group-prepend">
                       <button
                               class="btn btn-outline-primary"
                               type="button"
                               onclick='doRconCommandOnPlayer(
                                    $("#action-on-player-command-id{server_id}-{i}").val(),
                                    $("#action-on-player-command-id{server_id}-{i} option:selected").attr("data-command-params"),
                                    {coded_nick},
                                    {server_id}
                               );'
                       >
                           Выполнить
                       </button>
                   </div>
                   <select id="action-on-player-command-id{server_id}-{i}" class="form-control">
                       {for($l = 0; $l < count($commands); $l++)}
                           <option value="{{$commands[$l]->id}}" data-command-params='{{$commands[$l]->params}}'>
                               {{$commands[$l]->title}}
                           </option>
                       {/for}
                   </select>
               </div>
           {/if}
        </td>
    {/if}
</tr>