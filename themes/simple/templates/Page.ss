<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
	<% base_tag %>
	<title>Home</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	$MetaTags(false)
	<link rel="shortcut icon" href="themes/simple/images/favicon.ico" />
	<link rel="alternate" type="application/rss+xml"
  title="RSS Feed for Locations of Interest"
  href="/home/rss" />
 <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
   integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
   crossorigin=""/>
   <style>
     body { margin:0; padding:0; }
  #mapid { position:absolute; top:0; bottom:0; width:100%; }
</style>
   </head>
<body class="$ClassName.ShortName">
		$Layout
 <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
   integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
   crossorigin=""></script>
<script type="text/javascript">
   var locations = [
   <% loop $Locations %>["<b>$Name.XML</b><br />$Address.XML<br /><br /><% loop $Times %>$Day.Nice: $StartTime.Nice - $EndTime.Nice<br /><% end_loop %>", $Lat, $Lng, $ID]<% if not $Last %>,<% end_if %>
   <% end_loop %>
   ];

var mymap = L.map('mapid').setView([-41.00, 174.00], 6);
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a> | Locations of Interest: <a href="https://health.govt.nz">Ministry of Health</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoiZmlyZXNwaGVyZSIsImEiOiJja3Nqb2kzMXUwZjF4MnJyMThmZ2J4NHl6In0.9tPUrRRN07w_ftJGz8I8AQ'
}).addTo(mymap);

   for (var i = 0; i < locations.length; i++) {
  marker = new L.marker([locations[i][1], locations[i][2]])
    .bindPopup(locations[i][0])
    .addTo(mymap);
  if (window.location.hash == '#' + locations[i][3]) {
      marker.openPopup();
  }
}
</script>
</body>
</html>
