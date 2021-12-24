<?xml version="1.0"?>
<rss version="2.0"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:georss="http://www.georss.org/georss">
	<channel>
		<title>$Title</title>
		<link>$Link</link>
		<atom:link href="$Link('home/rss')" rel="self" type="application/rss+xml" />
		<description>$Description.XML</description>
        <lastBuildDate>$lastModified</lastBuildDate>
		<% loop $Entries %>
            <% cached 'RSSEntry', $ID, $LastEdited %>
                <item>
                    <title><% if $Location.ExposureType == 'Close' %>🔴 <% end_if %><% if $Title %>$Title.XML<% else %>$Location.Address.XML<% end_if %></title>
                    <link>$AbsoluteLink.XML</link>
                    <description><![CDATA[<h3>$Location.Address</h3>
                    <% if $Location.ExposureType == 'Close' %><h4>🔴 Close contact location</h4><% end_if %>
                        <b>Date:</b> $Day.Nice<br />
                        <b>Time:</b> $StartTime.Nice - $EndTime.Nice<br />
                        <% if $Location.Type %><b>Exposure type:</b> $Location.Type<% end_if %>
                        <b>What to do:</b><br />
                        $Location.Help
                        <% if $Location.Map %>
                            <br />
                            <figure id='map-$ID'>
                            <% with $Location %>
                            <img src='$Map.AbsoluteLink' alt='Map for $Name' title='Map for $Name' />
                            <figcaption>$Name, $Lat $Lng</figcaption>
                            <% end_with %>
                        </figure>
                        <% end_if %>
                    ]]>
                    </description>
                    <% if $Date %><pubDate>$Date.Rfc822</pubDate>
                    <% else %><pubDate>$Created.Rfc822</pubDate><% end_if %>
                    <% if $Author %><dc:creator>$Author.XML</dc:creator><% end_if %>
                    <% with $Location %>
                        <% if $City.Name %><category>$City.Name</category><% end_if %>
                        <% if $Lat %><georss:point>$Lat $Lng</georss:point><% end_if %>
                    <% end_with %>
                    <guid>$AbsoluteLink-$ID</guid>
                </item>
            <% end_cached %>
		<% end_loop %>
	</channel>
</rss>
