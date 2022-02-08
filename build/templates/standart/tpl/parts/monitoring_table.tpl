<div class="table-responsive monitoring-table {if($monitoringType == 2)}big-monitoring-table{/if}">
    <table class="table table-bordered">
        <thead>
        <tr>
            <td>Название сервера</td>
            <td>Карта</td>
            <td>Игроков</td>
            <td>IP-адрес</td>
            <td>Действия</td>
        </tr>
        </thead>
        <tbody id="servers">
        <tr>
            <td colspan="10">
                <div class="loader"></div>
                <script>get_servers();</script>
            </td>
        </tr>
        </tbody>
    </table>
</div>