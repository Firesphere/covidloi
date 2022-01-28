<div id="modal-item" class="modal">
    <div class="modal-window large">
        <span class="close" data-dismiss="modal">&times;</span>
        <h2 id="modal-title">$Title</h2>

        <div id="modal-content">$Content</div>
        <% if $ClassName == 'Page' %>
            <h4>Get a calendar reminder for your booster shot</h4>
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
            <a href="#" target="_blank" id="google-link">Google Calendar</a> |
            <a href="#" target="_blank" id="outlook-link">Outlook Calendar</a> |
            <a href="#" target="_blank" id="yahoo-link">Yahoo Calendar</a> |
            <a href="#" target="_blank" id="ics-link">ICS Calendar file</a>
        </div>
        <% end_if %>
    </div>
</div>
