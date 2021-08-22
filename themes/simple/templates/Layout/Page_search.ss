<div id="modal-item" class="modal open">
    <div class="modal-window large">
        <span class="close" data-dismiss="modal">&times;</span>
        <h2 id="modal-title">Search results for "$Query.XML"</h2>

        <div id="modal-content" class="searchresults">
            <% loop $Results.getPaginatedMatches %>
                <h3><a href="/#$ID">$Name</a></h3>
                <p>$Help</p>
                <div class="visit-times">
                    <% loop $Times %>
                        <b>$Day.Nice:</b>$StartTime.Nice - $EndTime.Nice<br />
                    <% end_loop %>
                </div>
                <p>$Address</p>
                <% if not $Last %><hr /><% end_if %>
            <% end_loop %>
        </div>
        <div class="pagination">
            <% with $Results.getPaginatedMatches %>
                <% if $MoreThanOnePage %>
                    <div id="PageNumbers">
                        <div class="pagination">
                            <% if $NotFirstPage %>
                                <a class="pagenum prev" href="$PrevLink" title="View the previous page">&larr;</a>
                            <% end_if %>

                            <% loop $Pages %>
                                <% if $CurrentBool %>
                                    <span class="pagenum current">$PageNum</span>
                                <% else %>
                                    <a href="$Link" title="View page number $PageNum" class="pagenum go-to-page">$PageNum</a>
                                <% end_if %>
                            <% end_loop %>
                            <% if $NotLastPage %>
                                <a class="pagenum next" href="$NextLink" title="View the next page">&rarr;</a>
                            <% end_if %>
                        </div>
                    </div>
                <% end_if %>
            <% end_with %>
        </div>
    </div>
</div>
