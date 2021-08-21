<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
<% cached 'Header', $SiteConfig.LastEdited %>
	<% base_tag %>
	<title>$SiteConfig.Title</title>
<% end_cached %>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="alternate" type="application/rss+xml" title="RSS Feed for Locations of Interest in New Zealand" href="/home/rss" />
   </head>
<body>
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
                    <a href="#menu-$ID" data-toggle="modal" data-target="menu$ID">$Menutitle</a>
                <% end_if %>
            <% end_loop %>
        <% end_cached %>
    </div>
</nav>
<div id="mapid"></div>
<div id="modal-item" class="modal">
    <div class="modal-window large">
        <span class="close" data-dismiss="modal">&times;</span>
        <h3 id="modal-title"></h3>

        <div id="modal-content"></div>
    </div>
</div>
<script type="text/javascript">
<% cached 'Locations', $Locations.Count %>
   var locations = [
       <% loop $Locations %>["$Description.RAW", $Lat, $Lng, $ID]<% if not $Last %>,<% end_if %>
       <% end_loop %>
   ];
<% end_cached %>
<% cached 'Pages', $Pages.Max('LastEdited') %>
    var pagecontent = {
        <% loop $Pages %>"menu$ID": {Title: "$Title.XML", Content: '$Content'}<% if not $Last %>,<% end_if %>
        <% end_loop %>
    };
<% end_cached %>
</script>
</body>
</html>
