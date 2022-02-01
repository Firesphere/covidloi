<div id="modal-item" class="modal">
    <div class="modal-window large">
        <span class="close" data-dismiss="modal">&times;</span>
        <h2 id="modal-title">$Title</h2>

        <div id="modal-content">$Content</div>
        <% if $ClassName == 'Page' %>
            <h3>Get a calendar reminder for your booster shot</h3>
        <table>
            <tr><td>
                <label for="booster-date">When did you have your last shot?</label><br />
</td><td>            <input type="date" id="booster-date" /><br />
</td></tr>
            <tr><td>
                <label for="booster-location">Location (optional)</label><br />
</td><td>            <input type="text" id="booster-location" /><br />
</td></tr>
        </table>
            <input type="button" value="Get calendar links" id="get-links" />
        <div id="booster-links" class="hidden">
            <br />
            <h4>The earliest you can get your booster is: <span id="booster-plan"></span></h4>
            <p>Click the links to import a reminder in to your calendar.</p>
            <p>
            <a href="#" target="_blank" class="calendar-link" data-type="google">Google Calendar</a> |
            <a href="#" target="_blank" class="calendar-link" data-type="outlook">Outlook Calendar</a> |
            <a href="#" target="_blank" class="calendar-link" data-type="yahoo">Yahoo Calendar</a> |
            <a href="#" target="_blank" class="calendar-link" data-type="ics">ICS Calendar file</a>
            </p>
        </div>
        <% end_if %>
    </div>
</div>
