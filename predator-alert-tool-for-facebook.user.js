/**
 *
 * This is a Greasemonkey script and must be run using a Greasemonkey-compatible browser.
 *
 * @author maymay <bitetheappleback@gmail.com>
 */
// ==UserScript==
// @name           Predator Alert Tool for Facebook (PAT-Facebook)
// @version        0.1
// @namespace      com.maybemaimed.facebook.predator-alert-tool
// @updateURL      https://userscripts.org/scripts/source/177813.user.js
// @description    Alerts you of people who have allegedly violated other people's consent as you browse Facebook.
// @include        https://www.facebook.com/*
// @include        http://www.facebook.com/*
// @include        https://apps.facebook.com/*
// @include        http://apps.facebook.com/*
// @include        https://serene-ravine-7926.herokuapp.com/*
// @exclude        https://www.facebook.com/ajax*
// @exclude        http://www.facebook.com/ajax*
// @exclude        https://www.facebook.com/ai.php*
// @exclude        http://www.facebook.com/ai.php*
// @grant          GM_log
// @grant          GM_xmlhttpRequest
// @grant          GM_addStyle
// @grant          GM_setValue
// @grant          GM_getValue
// @grant          GM_openInTab
// ==/UserScript==
PAT_FB = {};
PAT_FB.CONFIG = {
    'debug': false, // switch to true to debug.
    'api_url': 'https://serene-ravine-7926.herokuapp.com/api.php?fbid='
};

// Utility debugging function.
PAT_FB.log = function (msg) {
    if (!PAT_FB.CONFIG.debug) { return; }
    GM_log('PAT-Facebook: ' + msg);
};

// Initializations.
GM_addStyle('\
.has-predator-alert-tool-reports { border: 5px solid red; }\
');
PAT_FB.init = function () {
    // We need to capture the session cookies from the PAT-FB server, so if we
    // loaded the server's pages, save the cookies locally for later use.
    // TODO: But, um, this functionality really starts requiring a browser ext.
    if (unsafeWindow.location.host == PAT_FB.parseApiUrl().host) {
        GM_setValue('pat_fb_cookies', document.cookie);
    }
    var MutationObserver = unsafeWindow.MutationObserver || unsafeWindow.WebKitMutationObserver;
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.addedNodes) {
                for (var i = 0; i < mutation.addedNodes.length; i++) {
                    // Skip text nodes.
                    if (mutation.addedNodes[i].nodeType == Node.TEXT_NODE) { continue; }
                    // Process all the rest.
                    PAT_FB.main(mutation.addedNodes[i]);
                }
            }
        });
    });
    var el = document.body;
    observer.observe(el, {
        'childList': true,
        'subtree': true
    });
    PAT_FB.main(document);
};
window.addEventListener('DOMContentLoaded', PAT_FB.init);

// main() is given a start node (HTML tree) and processes appropriately.
PAT_FB.main = function (node) {
    PAT_FB.log('Starting main() on page ' + unsafeWindow.location.toString());
    PAT_FB.processElements(node.querySelectorAll('[data-hovercard]'));
};

PAT_FB.processElements = function (els) {
    for (var i = 0; i < els.length; i++) {
        var fbid = els[i].getAttribute('data-hovercard').match(/id=(\d+)/)[1];
        PAT_FB.log('Found Facebook ID ' + fbid + '.');
        PAT_FB.maybeFlagEntity(fbid, els[i]);
    }
};

PAT_FB.parseApiUrl = function () {
    var a = document.createElement('a');
    a.setAttribute('href', PAT_FB.CONFIG.api_url);
    return {
        'protocol': a.protocol,
        'host': a.host,
        'port': a.port,
        'pathname': a.pathname
    };
}

/**
 * Queries the PAT-FB server for reports by Facebook ID. If a result is found,
 * applies styling to the HTML node appropriately.
 *
 * @param fbid The numeric Facebook ID to query.
 * @param el The HTML node from which the ID was scraped.
 */
PAT_FB.maybeFlagEntity = function (fbid, el) {
    if (!fbid) { PAT_FB.log('Invalid ID passed to maybeFlagEntity().'); return false; }
    PAT_FB.log('About to query for reports on ID ' + fbid.toString());
    GM_xmlhttpRequest({
        'method': 'GET',
        'url': PAT_FB.CONFIG.api_url + fbid.toString(),
        'headers': {
            'Cookie': GM_getValue('pat_fb_cookies')
        },
        'onload': function (response) {
            try {
                resp = JSON.parse(response.responseText);
                PAT_FB.log('Parsed response from PAT-FB for ' + fbid.toString() + ': ' + response.responseText);
                if (resp.reports) {
                    el.setAttribute('class', el.getAttribute('class') + ' has-predator-alert-tool-reports');
                }
            } catch (e) {
                PAT_FB.log('Caught error from reply: ' + response.responseText);
            }
        }
    });
};
