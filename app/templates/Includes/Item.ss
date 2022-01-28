<item>
    <title><% if $Location.ExposureType == 'Close' %>🔴 <% end_if %><% if $Title %>$Title.XML<% else %>$Location.Address.XML<% end_if %></title>
    <link>$AbsoluteLink.XML</link>
    <% with $Location %>
    <description>
    <![CDATA[
        <h3>$Address</h3>
        <% if $ExposureType == 'Close' %><h4>🔴 Close contact location</h4><% end_if %>
        <b>Date:</b> $Up.Day.Nice<br />
        <b>Time:</b> $Up.StartTime.Nice - $Up.EndTime.Nice<br />
            <% if $Type %><b>Exposure type:</b> $Type<% end_if %>
            <b>What to do:</b><br />
            $Help
            <% if $Map %>
                <br />
                <figure id='map-$ID'>
                    <img src='$Map.AbsoluteLink' alt='Map for $Name' title='Map for $Name' />
                    <figcaption>$Name, $Lat $Lng</figcaption>
                </figure>
            <% end_if %>
    ]]>
    </description>
    <% if $City.Name %><category>$City.Name</category><% end_if %>
    <% if $Lat %><georss:point>$Lat $Lng</georss:point><% end_if %>
    <pubDate>
        <% if $LastUpdated %>
            $LastUpdated.Rfc822
        <% else_if $Date %>
            $Date.Rfc822
        <% else %>
            $Created.Rfc822
        <% end_if %>
    </pubDate>
    <% end_with %>
    <guid>$AbsoluteLink-$ID</guid>
</item>
