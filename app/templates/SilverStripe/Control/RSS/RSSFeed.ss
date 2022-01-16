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
            <% include Item %>
		<% end_loop %>
	</channel>
</rss>
