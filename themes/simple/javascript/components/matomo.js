window._paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
window._paq.push(['trackPageView']);
window._paq.push(['enableLinkTracking']);
(function () {
    let u = "https://piwik.casa-laguna.net/";
    window._paq.push(['setTrackerUrl', u + 'matomo.php']);
    window._paq.push(['setSiteId', '16']);
    let d = document,
        g = d.createElement('script'),
        s = d.getElementsByTagName('script')[0];
    g.async = true;
    g.src = u + 'matomo.js';
    s.parentNode.insertBefore(g, s);
})();
