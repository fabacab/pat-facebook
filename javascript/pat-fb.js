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
          $('#disambiguate-reportee-container').remove();
          $('#reportee_name').after('<span class="fetch-progress">(loading&hellip;)</span>');
          if (isNumeric(this.value)) {
              FB.api(
                  '/' + encodeURIComponent(this.value) + '?fields=id,name,picture.type(square),gender,bio,link',
                  PAT_Facebook.UI.handleReporteeSearch
              );
          } else {
              FB.api(
                  '/search?type=user&fields=id,name,picture.type(square),gender,bio,birthday,link&q=' + encodeURIComponent(this.value),
                  PAT_Facebook.UI.handleReporteeSearch
              );
          }
        });
      });

PAT_Facebook = {};
PAT_Facebook.UI = {};
PAT_Facebook.UI.handleReporteeSearch = function (response) {
    if (response.error) {
        if (console && console.log) {
            console.log(response.error);
        }
        return false;
    }
    $('label .fetch-progress').remove();
    // Dynamically create a list of clickable options.
    var el = document.createElement('div');
    el.setAttribute('id', 'disambiguate-reportee-container');
    var list = document.createElement('ul');
    list.setAttribute('id', 'disambiguate-reportee');
    // If we searched for an ID, we probably didn't get an array. Check for that,
    // and if that does seem to be what's up, coerce the data structure we expect.
    if (!response.data && response.id) {
        response.data = [];
        response.data[0] = {};
        for (var k in response) {
            if (response.hasOwnProperty(k)) {
                response.data[0][k] = response[k];
            }
        }
    }
    for (var i = 0; i < response.data.length; i++) {
        (function(i) {
            var li = document.createElement('li');
            var fbid = response.data[i].id;
            var name = response.data[i].name;
            var pic  = response.data[i].picture.data.url;
            var sex  = response.data[i].gender;
            var bio  = response.data[i].bio;
            var bday = response.data[i].birthday;
            var link = response.data[i].link;
            li.setAttribute('data-fbid', fbid);
            var html = '<a href="' + link + '" target="_blank">';
            html += '<img alt="' + name + ' (' + sex + ')' + '" src="' + pic + '" />';
            html += name + ' (' + sex + ')';
            html += (bday) ? ' [Birthday: ' + bday + ']': '';
            html += (bio) ? '<br />' + bio : '';
            html += '</a>';
            li.innerHTML = html;
            $(li).click(function() {
                $('#reportee_id').attr('value', fbid);
                $('#reportee_name').attr('value', name);
                $('#reportee_picture').attr({
                    'src': pic,
                    'style': ''
                });
                $('#disambiguate-reportee-container').remove();
            });
            list.appendChild(li);
        })(i)
    }
    el.innerHTML = '<p>Which "' + document.getElementById('reportee_name').value + '" did you mean?</p>';
    el.appendChild(list);
    $('#reportee_name').closest('form')[0].appendChild(el);
};
