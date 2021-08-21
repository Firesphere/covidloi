{
"type": "FeatureCollection",
"name": "locations-of-interest",
"features": [
<% loop $Locations %>{
 "type": "Feature",
 "properties": {
     "id": "$ID",
     "Event": "$Name",
     "Location": "$Address",
     "City": "$City.Name",
     "Times": [
     <% loop $Times %>{
        "Date": "$Day",
        "Start": "$StartTime",
        "End": "$EndTime"
     }<% if not $Last %>,<% end_if %><% end_loop %>
     ],
     "Information": "$Help"
 },
 "geometry": {
     "type": "Point",
     <% if $Lat && $Lng %>
     "coordinates": [ $Lng, $Lat ]
     <% else %>
     "coordinates": [0,0]
     <% end_if %>
  }
  }<% if not $Last %>,<% end_if %>
<% end_loop %>]}
