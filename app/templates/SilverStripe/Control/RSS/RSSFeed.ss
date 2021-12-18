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
		<item>
			<title>$Title.XML</title>
			<link>$AbsoluteLink.XML</link>
			<% if $Description %><description>$Description.CDATA()</description><% end_if %>
			<% if $Date %><pubDate>$Date.Rfc822</pubDate>
			<% else %><pubDate>$Created.Rfc822</pubDate><% end_if %>
			<% if $Author %><dc:creator>$Author.XML</dc:creator><% end_if %>
			<% with $Location %>
                <% if $City.Name %><category>$City.Name</category><% end_if %>
                <% if $Lat %><georss:point>$Lat $Lng</georss:point><% end_if %>
            <% end_with %>
		</item>
		<% end_loop %>
	</channel>
</rss>
