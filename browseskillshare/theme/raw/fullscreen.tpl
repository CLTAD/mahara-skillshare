<div id="skillsharefullscreenlisting">
{if count($item.data) > 0 }
        <div class="listing" name="{$item.data.id}">
                <!-- Start Advanced Gallery Html Containers -->
                <input type="button" id="closelisting" class="ui-icon ui-icon-closethick">
                <div id="topblock">
                         <div id="wantedoffered">
                            {if $item.data.offered}
                                <div class="offered">Offered</div>
                            {/if}
                            {if $item.data.wanted}
                                <div class="wanted">Wanted</div>
                            {/if}
                        </div>
                       <div class="listingtitle"><h3>{$item.data.owner}</h3></div>
                         <div id="aboutme" class="clearfix">
                             <div class="fl">
                                    <div>Profile Page: <a href="{$item.data.profilepage}">{$item.data.owner}'s Profile Page</a></div>
                                    {if $item.data.college}<div>College: {$item.data.college}</div>{/if}
                                    {if $item.data.course}{foreach $item.data.course as coursename}{if $coursename != 'none'}<div>Course: {$coursename}</div>{/if}{/foreach}{/if}
                            </div>
                            <div class="fl">
                                <!-- <p>Year of study: {$item.data.yearofstudy}</p> -->
                                {if $item.data.externalwebsite}<div>External website: <a href="{$item.data.externalwebsite}">{$item.data.externalwebsite}</a></div>{/if}
                                {if $item.data.externalwebsiterole}<div>External website role: {$item.data.externalwebsiterole}</div>{/if}
                            </div>
                        </div>
                        <div id="sharelinks">
                            <a rel="nofollow" href="http://www.facebook.com/share.php?u={$item.data.shareurl}" target="_blank" style="text-decoration:none;" title="Share on Facebook" alt="Share on Facebook"><img src="{$wwwroot}theme/raw/static/images/16x16-facebook.png"/></a>
                            <a rel="nofollow" href="https://twitter.com/share" class="twitter-share-button" target="_blank" data-count="none" title="Share on Twitter" alt="Share on Twitter"><img src="{$wwwroot}theme/raw/static/images/16x16-twitter.png"/></a>
                            <a rel="nofollow" href="https://plusone.google.com/_/+1/confirm?hl=en&url={$item.data.shareurl}" target="_blank" style="text-decoration:none;" title="Share on Google Plus" alt="Plus One"><img src="{$wwwroot}theme/raw/static/images/16x16-gplus.png"/></a>
                        </div>
                </div>
                <div id="mainblock">
                    <div id="image-content">
                        <div class="content">
                            <div class="slideshow-container">
                                <div id="loading" class="loader"></div>
                                <div id="slideshow" class="slideshow"></div>
                                <div id="caption" class="caption-container"></div>
                            </div>
                        </div> <!-- close content -->
                    </div> <!-- close image-content -->
                    
                    <div id="text-content">
                        <div class="listingtitle"><h3>{$item.data.statementtitle}</h3></div>    
                        <div class="listingstatement">{$item.data.statement|safe}</div>
                        <div class="listingtags">{if $item.data.tags}<div><h4>Tags: <em>{$item.data.tags}</em></h4></div>{/if}</div>
                    </div> <!-- close textcontent -->
                </div> <!--  mainblock  -->
                    
                <div id="bottomblock">
                    <div class="navigation-container">
                        <div id="thumbs" class="navigation">
                            <ul class="thumbs noscript">
                                {foreach $item.data.images image}
                                <li>
                                    <a class="thumb" name="{$.foreach.default.index + 1}" href="{$image.link}" title="{$image.title}">
                                        <img src="{$image.thumb}" alt="{$image.title}" />
                                    </a>
                                    <div class="caption">Role: {$image.title}</div>
                                </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>
                    <div id="controls" class="controls"></div>
                    <div id="contactuser" class="fr">
                    <div class="btn"><a href="{$item.data.messagelink}">{str tag='sendmessage' section='artefact.browseskillshare'}</a></div>
                    </div>
                </div> <!--  bottomblock  -->
        </div>
{else}
        <div class="listing">
            <input type="button" id="closelisting" class="ui-icon ui-icon-closethick">
            <div class="nolistingtitle"><h3>No results found.</h3></div>
        </div>
{/if}
</div>