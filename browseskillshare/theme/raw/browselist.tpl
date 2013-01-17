<div id="skillsharelistings">
    <div id="loadingmessage" class="hidden">Loading...</div>
       <a id="addlisting" href="{$WWWROOT}artefact/skillshare"> + Add your listing</a>
{if $items.count > 0 }
{foreach $items.data artefact}
        <div class="listing" name="{$artefact.id}">
            <div class="wantedofferedrow cl">
                {if $artefact.offered}
                <div class="offered fl">Offered</div>
                {/if}
                {if $artefact.wanted}
                <div class="wanted fl">Wanted</div>
                {/if}
            </div>
            <div class="cl">
                <div class="exampleimages cr">
                    {foreach $artefact.images image}
                        <img src="{$image.source}" title="{$image.title}">
                    {/foreach}
                </div>
                <div class="listingtitle"><h3>{$artefact.statementtitle}</h3></div>
                <div class="listingrow statement">{$artefact.statement|safe}</div>
                {if $artefact.tags}<div class="listingrow"><h5>Tags: <em>{$artefact.tags}</em></h5></div>{/if}
                <div class="viewmore">View full listing...</div> 
            </div>
            <div class="listingrow">Profile Page: <a href="{$artefact.profilepage}">{$artefact.owner}'s Profile Page</a></div>
            {if $artefact.college}<div class="listingrow">College: {$artefact.college}</div>{/if}
            {if $artefact.course}{foreach $artefact.course coursename}{if $coursename != 'none'}<div class="listingrow">Course: {$coursename}</div>{/if}{/foreach}{/if}
            {if $artefact.externalwebsite}<div class="listingrow">External website example: <a href="{$artefact.externalwebsite}">{$artefact.externalwebsite}</a></div>{/if}
            {if $artefact.externalwebsiterole}<div class="listingrow">External website role: {$artefact.externalwebsiterole}</div>{/if}
            <div class="btn"><a href="{$artefact.messagelink}">{str tag='sendmessage' section='artefact.browseskillshare'}</a></div>
        </div>
{/foreach}
{else}
        <div class="cl">
            <div class="listingtitle"><p>No results found.</p></div>
        </div>
{/if}
</div>