<nav id="hamnav">
    <!-- (B) THE HAMBURGER -->
    <label for="hamburger">&#9776;</label>
    <input type="checkbox" id="hamburger"/>

    <!-- (C) MENU ITEMS -->
    <div id="hamitems">
        <% cached 'PageMenu', $Pages.Max('LastEdited') %>
            <% loop $Pages %>
                <% if $ClassName.ShortName = 'RedirectorPage' %>
                    <a href="$Link" target="_blank">$Menutitle</a>
                <% else %>
                    <a href="$Link" data-toggle="modal" data-target="menu$ID">$Menutitle</a>
                <% end_if %>
            <% end_loop %>
        <% end_cached %>
    </div>
</nav>
