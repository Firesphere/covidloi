<?xml version="1.0"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>$Title</title>
		<link>$Link</link>
		<atom:link href="$Link" rel="self" type="application/rss+xml" />
		<description>$Description.XML</description>
		<% loop $Entries %>
		<item>
			<title>$Title.XML</title>
			<link>$AbsoluteLink.XML</link>
			<% if $Description %><description>$Description.CDATA</description><% end_if %>
			<% if $Date %><pubDate>$Date.Rfc822</pubDate>
			<% else %><pubDate>$Created.Rfc822</pubDate><% end_if %>
            <lastBuildDate>$LastUpdated.Rfc822</lastBuildDate>
			<% if $Author %><dc:creator>$Author.XML</dc:creator><% end_if %>
			<guid isPermaLink="true">$AbsoluteLink</guid>
			<category>$Location.City.Name</category>
		</item>
		<% end_loop %>
	</channel>
</rss>
