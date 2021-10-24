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
	<meta name="google-site-verification" content="afc6wDN6LeVaxTRWlNRzbdfs7p5VrpAJ5NJRjJZB87Y" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
</head>
<body class="$ClassName.ShortName">
<% include Nav %>
<div id="mapid"></div>
$Layout
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
