function toggle(selector) {
  if ($(selector).css('opacity') != 0) {
    $(selector).css('opacity', 0);
  } else {
    $(selector).css('opacity', 1);
  }
}
function forceOff(selector) {
  $(selector).css('opacity', 0);
}

function updateBracket(force) {
// surely there is a better way
  $('#bracket iframe').attr('src', $('#bracket iframe').attr('src'));
}
function updateStandings(force) {
  if (force || $("#standings .panel").css("opacity") != 0) {
    $.getJSON("/standings.php", function (data) {
      $("#standings tbody").empty();
      $.each(data, function (id, payload) {
        var line = $("<tr />");
        $("<td />", {
          id: id,
          style: 'text-align: left;',
          text: payload['name']
        }).appendTo(line);
        $("<td />", {text: payload['w']}).appendTo(line);
        $("<td />", {text: payload['l']}).appendTo(line);
        $("<td />", {text: payload['pct']}).appendTo(line);
        $("<td />", {text: payload['sets']}).appendTo(line);
        $("<td />", {text: payload['pts']}).appendTo(line);
        $("<td />", {text: payload['diff']}).appendTo(line);
        line.appendTo("#standings tbody");
      });
    });
  }
}
function updateMatchbug(force) {
  if (force || $("#matchbug .panel").css("opacity") != 0) {
    $.getJSON("/round.php", function (data) {
      $("#matchbug #tname").html(data.tname);
      $("#matchbug #round").html(data.round);
      $("#matchbug tbody").empty();
      $.each(data.teams, function (id, payload) {
        var line = $("<tr />", {
          id: "matchbug" + id
        });

        $("<td style=\"width:40%;text-align: right;\"><h4 id=\"p1\">" + data.teams[id].p1 + "</h4></td>\n\
           <td><small><small style=\"text-transform: uppercase;\" id\"match\">" + data.teams[id].match + "</small></small><br /><h5 id=\"score\">" + data.teams[id].score + "</h5></td>\n\
           <td style=\"width:40%;text-align: left;\"><h4 id=\"p2\">" + data.teams[id].p2 + "</h4></td>").appendTo(line);
        // I KNOW IT SUCKS, OKAY?

        line.appendTo("#matchbug tbody");
      });
    });
  }
}

function updateScorebug(force) {
  if (force || $("#scorebug .panel").css("opacity") != 0) {
    $.getJSON("/match.php?match=active", function (data) {
      $("#scorebug #tname").html(data.title);
      $("#scorebug #mname").html(data.match);
      $("#scorebug #score_p1").html(data.p1);
      $("#scorebug #score_p1p1").html(data.p1pts.r1);
      $("#scorebug #score_p1p2").html(data.p1pts.r2);
      $("#scorebug #score_p1p3").html(data.p1pts.r3);
      $("#scorebug #score_p2").html(data.p2);
      $("#scorebug #score_p2p1").html(data.p2pts.r1);
      $("#scorebug #score_p2p2").html(data.p2pts.r2);
      $("#scorebug #score_p2p3").html(data.p2pts.r3);
    });
  }
}
$(document).ready(function () {
  updateStandings(true);
  window.setInterval(updateStandings, 15000);
  updateScorebug(true);
  window.setInterval(updateScorebug, 15000);
  updateMatchbug(true);
  window.setInterval(updateMatchbug, 15000);
  var socket = io('http://localhost:3000'); // @TODO: config
  socket.on('streamctrl', function (msg) {
    var payload = JSON.parse(msg);
    console.log(payload);
    if (payload.type == 17 && payload.value == 1) {
      switch (payload.id) {
        case 11: // sys mute
          break;
        case 12: // mic mute
          break;
        case 13: // SHIT
          break;
        case 8: // Break1
          break;
        case 9: // Break2
          break;
        case 10: // Break3
          break;
        case 5: // Decklink
          break;
        case 6: // Aux1
          break;
        case 7: // Aux2
          toggle("#matchbug .panel");
          forceOff("#standings .panel");
          forceOff("#scorebug .panel");
          forceOff("#bracket .panel");
          updateMatchbug(true);
          break;
        case 2: // Opt1
          forceOff("#matchbug .panel");
          toggle("#standings .panel");
          forceOff("#scorebug .panel");
          forceOff("#bracket .panel");
          updateStandings(true);
          break;
        case 3: // Opt2
          forceOff("#matchbug .panel");
          forceOff("#standings .panel");
          toggle("#scorebug .panel");
          forceOff("#bracket .panel");
          updateScorebug(true);
          break;
        case 4: // Opt3
          forceOff("#matchbug .panel");
          forceOff("#standings .panel");
          forceOff("#scorebug .panel");
          toggle("#bracket .panel");
          updateBracket(true);
          break;
        default:
          break;
      }
    } else if (payload.type == 16) {
      var v = ((payload.value + 1) * 100 / 1024);
      switch (payload.id) {
        case 4: // sys volume
          App.setPrimarySpeakerLevel(v);
          break;
        case 5: // mic volume
          App.setPrimaryMicLevel(v);
          break;
        case 3: // dog opacity
          break;
        case 2: // opt1 opacity
          break;
        case 0: // opt2 opacity
          break;
        case 1: // opt3 opacity
          break;
        default:
          break;
      }
    }
  });
});