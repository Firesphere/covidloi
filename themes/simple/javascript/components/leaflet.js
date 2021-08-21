import L from 'leaflet'

let mymap = L.map('mapid').setView([-41.00, 174.00], 6);
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
    attribution: 'Locations of Interest: <a href="https://health.govt.nz">Ministry of Health</a> |' +
        ' <a href="/home/rss">RSS Feed</a>',
    maxZoom: 18,
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1,
    accessToken: 'pk.eyJ1IjoiZmlyZXNwaGVyZSIsImEiOiJja3Nqb2kzMXUwZjF4MnJyMThmZ2J4NHl6In0.9tPUrRRN07w_ftJGz8I8AQ'
}).addTo(mymap);

for (let i = 0; i < locations.length; i++) {
    let marker = new L.marker([locations[i][1], locations[i][2]])
        .bindPopup(locations[i][0])
        .addTo(mymap);
    if (window.location.hash === '#' + locations[i][3]) {
        marker.openPopup();
    }
}
