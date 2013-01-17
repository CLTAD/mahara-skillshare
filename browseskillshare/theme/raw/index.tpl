{include file="header.tpl"}
<div id="container-skillshare">

    <div id="browse-container" class="clearfix">
    <div id="browse-options" class="contentlinks">
      
        <div id="select-filters" class="select-filters fr">
            <div id="filter-tabs" class="fr">
            <div id="filter-sharetype-container" class="chzn-container filter-section">        
                <select data-placeholder="Sharetype" id="filter-sharetype" class="filter-section chzn-select">
                    <option value=""></option>
                    <option value="1">Offered</option>
                    <option value="2">Wanted</option>
                </select>
            </div>
            <!-- 
            <div id="filter-college-container" class="chzn-container filter-section">                    
                <select data-placeholder="College" id="filter-college" class="filter-section chzn-select">
                    <option value=""></option>
                    {foreach from=$colleges item=item name=college}
                        <option value="{$dwoo.foreach.college.index + 1}">
                            {$item}
                        </option>
                    {/foreach}
                </select>
            </div>
        
            <div id="filter-course-activate-container" class="chzn-container filter-section">
                <div id="activate-course-search" class="chzn-container-single chzn-default">
                    <a class="chzn-single chzn-default" href="javascript:void(0)"><span>Course</span>
                    <div><b></b></div>
                    </a>
                </div> 
            </div>
             -->
            </div><!-- filter-tabs -->
            
            <div id="filter-keyword-container" class="filter-section fl">
                <label for="filter-keyword">Search</label>
                <input type="text" placeholder="Title,tag or description" value="" maxlength="250" tabindex="1" size="20" name="keyword" id="filter-keyword" class="text fl">
                <button id="query-button-keyword" class="add-text-filter-button fl" type="submit" value="keyword">{str tag="go"}</button>
            </div>

        </div><!-- select-filters -->
    </div><!-- browse-options -->
    </div>
    
    <div class="remove-filter hidden" id="filter-remove-filter-entry-container">
        <input type="button" class="remove-filter-button ui-icon ui-state-default ui-icon-circle-close">
    </div>

<div id="filter-course-container">
    <div id="filter-course-wrapper">
            <label for="filter-course" class="fl">Course name or ID</label>
            <input type="text" value="" maxlength="250" tabindex="12" size="12" name="course" id="filter-course" class="text fl">
            <button id="query-button-course" class="add-text-filter-button fl" type="submit" value="course">{str tag="go"}</button>
    </div>
</div>
<div id="active-filters-container">
    <div id="active-filters" class="clearfix"></div>
</div>    

    <div id="browsewrap">
    <table id="browselist">
    <tr><td>
        <div id="pagination">
            {$items.pagination|safe}
        </div>
    {$items.tablerows|safe}
    </td></tr>
    </table>
    </div>
</div>
{include file="footer.tpl"}
