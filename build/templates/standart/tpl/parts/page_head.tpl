{if(strpos($_SERVER['REQUEST_URI'], "market") !== false or isset($offparts))}
	
{elseif(strpos($_SERVER['REQUEST_URI'], "profile") !== false)}
    <div class="cover" data-src="<?=get_user_cover($profile->id);?>">
        {if($profile->id == $_SESSION['id'])}
        <div class="d-flex justify-content-end">
            <input id="cover" type="file" accept="image/*">
            <button class="btn" id="cover_btn">
                <i class="far fa-edit" aria-hidden="true"></i>
            </button>
        </div>
        {/if}
    </div>
{else}
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
{/if}