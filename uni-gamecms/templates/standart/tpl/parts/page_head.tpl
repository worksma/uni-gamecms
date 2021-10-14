{if($monitoringType == 2)}
    {include file="parts/monitoring_table.tpl"}
{else}
    <div class="row">
        <div class="col-lg-3">
            <div class="logo">
                <a href="{site_host}" title="{site_name}">
                    <img src="{site_host}templates/{template}/img/logo.png" alt="{site_name}">
                </a>
            </div>
        </div>
        {if($monitoringType == 0)}
            <div class="col-lg-9 px-0 px-lg-3">
                {include file="parts/monitoring_block.tpl"}
            </div>
        {else}
            <div class="col-lg-9">
                {include file="parts/monitoring_table.tpl"}
            </div>
        {/if}
    </div>
{/if}