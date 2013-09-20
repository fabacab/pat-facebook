      function isNumeric (n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
      }
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#postToWall').click(function() {
          FB.ui(
            {
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendToFriends').click(function() {
          FB.ui(
            {
              method : 'send',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#fb-logout-button').click(function() {
          FB.logout();
        });

        $('#reportee_name').change(function() {
          PAT_Facebook.search_results = [];
          $('#reportee_name').after('<span class="fetch-progress">(loading&hellip;)</span>');
          if (isNumeric(this.value) || (-1 == this.value.indexOf(' '))) {
              PAT_Facebook.api(
                  '/' + encodeURIComponent(this.value) + '?fields=id,name,picture.type(square),gender,bio,link',
                  PAT_Facebook.UI.handleReporteeSearch, this.value
              );
          }
          PAT_Facebook.api(
              '/search?type=user&fields=id,name,picture.type(square),gender,bio,birthday,link&q=' + encodeURIComponent(this.value),
              PAT_Facebook.UI.handleReporteeSearch, this.value
          );
          // If there's a space, assume a legal name, so remove the spaces to attempt conversion to a username.
          if (-1 != this.value.indexOf(' ')) {
              var username = this.value.replace(' ', '');
              PAT_Facebook.api(
                  '/' + encodeURIComponent(username) + '?fields=id,name,picture.type(square),gender,bio,link',
                  PAT_Facebook.UI.handleReporteeSearch, this.value
              );
          }
        });
      });

PAT_Facebook = {};
PAT_Facebook.search_results = [];
PAT_Facebook.addSearchResults = function (results) {
    for (var i = 0; i < results.length; i++) {
        PAT_Facebook.search_results.push(results[i]);
    }
    $.event.trigger({
        'type': 'searchResultsAdded',
        'results': results
    });
}
// Wrapper for FB.api that accepts additional parameters.
PAT_Facebook.api = function () {
    var cbf = arguments[1]; // callback function
    var args = arguments[2];
    FB.api(arguments[0], function (response) {
        cbf(response, args);
    });
};
PAT_Facebook.UI = {};
PAT_Facebook.UI.handleReporteeSearch = function (response, search_str) {
    if (response.error && (isNumeric(search_str) || -1 == search_str.indexOf(' '))) {
        // If we got an error on a single entity lookup, assume we were blocked, so try again sans API.
        $.ajax({
            'type': 'GET',
            'url': 'https://graph.facebook.com/' + search_str,
            'error': function () {
                $.event.trigger({
                    'type': 'searchResultsError',
                    'message': 'Failed to find Facebook entity "' + search_str + '" which may simply mean it does not exist.'
                });
            }
        }).done(PAT_Facebook.UI.handleReporteeSearch);
    } else if (response.error) {
        if (console && console.log) {
            console.log(response.error);
        }
        $.event.trigger({
            'type': 'searchResultsError',
            'message': response.error.message
        });
        return false;
    }
    if (response.id) { // only one result, so coerce
        var results = [];
        results[0] = response;
        PAT_Facebook.addSearchResults(results);
    }
    if (response.data && response.data.length > 0) { // multiple results
        PAT_Facebook.addSearchResults(response.data);
    } else if (response.data && response.data.length == 0) { // zero results
        if (PAT_Facebook.search_results.length == 0) {
            $.event.trigger({
                'type': 'searchResultsError',
                'message': 'Your search returned no results.'
            });
        }
    }
};
PAT_Facebook.UI.displayReporteeSearch = function (e) {
    var el = PAT_Facebook.UI.resetReporteeContainer();
    // Dynamically create or add to a list of clickable options.
    var list = document.querySelector('#disambiguate-reportee-container ul') || document.createElement('ul');
    list.setAttribute('id', 'disambiguate-reportee');

    for (var i = 0; i < e.results.length; i++) {
        (function(i) {
            var li = document.createElement('li');
            var label = document.createElement('label');
            var fbid = e.results[i].id;
            var name = e.results[i].name;
            var pic  = 'https://graph.facebook.com/' + e.results[i].id + '/picture';
            var sex  = e.results[i].gender;
            var bio  = e.results[i].bio;
            var bday = e.results[i].birthday;
            var link = e.results[i].link;
            li.setAttribute('data-fbid', fbid);
            var html = '<input type="radio" />';
            html += '<img alt="' + name + ' (' + sex + ')' + '" src="' + pic + '" />';
            html += '<a href="' + link + '" target="_blank">';
            html += name + ' (' + sex + ')';
            html += (bday) ? ' [Birthday: ' + bday + ']': '';
            html += (bio) ? '<br />' + bio : '';
            html += '</a>';
            label.innerHTML = html;
            $(label).click(function() {
                $('#reportee_id').attr('value', fbid);
                $('#reportee_name').attr('value', name);
                $('#reportee_picture').attr({
                    'src': pic,
                    'style': ''
                });
                $('#disambiguate-reportee-container').hide();
                $('#disambiguate-reportee-container ul').remove();
            });
            li.appendChild(label);
            list.appendChild(li);
        })(i)
    }
    el.innerHTML = '<p>Which "' + document.getElementById('reportee_name').value + '" did you mean?</p>';
    el.appendChild(list);
};
PAT_Facebook.UI.displayReporteeSearchError = function (e) {
    var el = PAT_Facebook.UI.resetReporteeContainer();
    var x = document.createElement('button');
    x.innerHTML = 'Okay';
    x.addEventListener('click', function (e) {
        e.preventDefault();
        el.setAttribute('style', 'display: none;');
        document.getElementById('reportee_name').focus();
    });
    var html = '<p>There was an error running your search.</p>';
    html += '<blockquote><p>' + e.message + '</p></blockquote>';
    el.innerHTML = html;
    el.appendChild(x);
};
PAT_Facebook.UI.resetReporteeContainer = function () {
    $('label .fetch-progress').remove();
    var el = document.getElementById('disambiguate-reportee-container');
    el.removeAttribute('style'); // re-style to make visible
    return el;
};
PAT_Facebook.init = function () {
    // Prepare DOM for event handlers.
    if (document.getElementById('reportee_name')) {
        var el = document.createElement('div');
        el.setAttribute('id', 'disambiguate-reportee-container');
        el.setAttribute('style', 'display: none;');
        $('#reportee_name').closest('form')[0].appendChild(el);
        $(el).on('searchResultsAdded', PAT_Facebook.UI.displayReporteeSearch);
        $(el).on('searchResultsError', PAT_Facebook.UI.displayReporteeSearchError);
    }
}
window.addEventListener('DOMContentLoaded', PAT_Facebook.init);
