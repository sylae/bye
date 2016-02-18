<?php

/**
 * A little lower-third bug to show the set scores for the current match.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require_once 'config.php';
require_once 'sorts.php';
require_once 'misc.php';

if (is_numeric($_GET['match'])) {
  $m = (int) $_GET['match'];
  $match = json_decode(file_get_contents(
        "https://api.challonge.com/v1/tournaments/" .
        $config['challonge_id'] .
        "/matches/$m.json?api_key=" .
        $config['challonge_api']
      ), true)['match'];
} elseif ($_GET['match'] == "active") {
  $m = "active";
} else {
  die();
}

$data = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      ".json?api_key=" .
      $config['challonge_api'] .
      "&include_participants=1&include_matches=1"
    ), true)['tournament'];

if ($m == "active") {
  foreach ($data['matches'] as $i => $d) {
    if (!is_null($d['match']['underway_at'])) {
      $match = $d['match'];
      break;
    }
  }
}

if (!isset($match)) { // just find an open match to present
  foreach ($data['matches'] as $i => $d) {
    if ($d['match']['state'] == "open") {
      $match = $d['match'];
      break;
    }
  }
}

// okay, now we panic
if (!isset($match)) {
  die();
}

// An attachment score CSV will override the challonge-reported score.
// This is because challonge API will not let us save a score without
// finalizing the match. We can, however, do attachments.
$attach = json_decode(file_get_contents(
    "https://api.challonge.com/v1/tournaments/" .
    $config['challonge_id'] .
    "/matches/{$match['id']}/attachments.json?api_key=" .
    $config['challonge_api']
  ), true);
foreach ($attach as $n => $payload) {
  if (substr($payload['match_attachment']['description'], 0, 18) == '$BYEPENDINGSCORE$:') {
    $match['scores_csv'] = str_replace('$BYEPENDINGSCORE$:', '', $payload['match_attachment']['description']);
  }
}
$loser = ($match['round'] < 0) ? "Loser's Bracket " : "";
$match['round'] = ($match['round'] < 0) ? $match['round'] * -1 : $match['round'];

$p1 = array(1 => "", 2 => "", 3 => "");
$p2 = array(1 => "", 2 => "", 3 => "");
if (strlen($match['scores_csv']) > 2) {
  $points = explode(",", $match['scores_csv']);
  $r = 1;
  foreach ($points as $k => $val) {
    $p = explode("-", $val);
    if (array_key_exists(1, $p)) {
      $p1[$r] = $p[0];
      $p2[$r] = $p[1];
      $r++;
    }
  }
}
foreach (array(1, 2, 3) as $n) {
  if ($p1[$n] > $p2[$n]) {
    $p1[$n] = "<strong>{$p1[$n]}</strong>";
  } else {
    $p2[$n] = "<strong>{$p2[$n]}</strong>";
  }
}
$score = array(
  'p1' => getName($match['player1_id'], $data),
  'p2' => getName($match['player2_id'], $data),
  'p1pts' => $p1,
  'p2pts' => $p2,
);
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $m;?> - Match</title>
    <script src="/js/jquery.min.js" type="text/javascript" charset="UTF-8"></script>
    <link href="/css/bootstrap.css" rel="stylesheet" />
    <script src="/js/bootstrap.min.js"></script>
    <meta charset="UTF-8" />
    <script>
      $(document).ready(function() {
        $(".panel").click(function() {
          if ($(this).css('opacity') == 0) {
            $(this).css('opacity', 1);
          } else {
            $(this).css('opacity', 0);
          }
        });
        setTimeout(function() {
          $(".panel").css('opacity', 1);
        }, 1000);
      });
    </script>
  </head>
  <body style="background:transparent;">
    <div class="container">
      <div class="panel panel-default navbar-fixed-bottom" style="background: rgba(0,0,0,0);transition: opacity 1s ease-in-out; opacity:0;">
        <table class="table" style="background: url('/img/rocket_bg.png') no-repeat scroll right top, url('/img/stripe.png') no-repeat scroll left top, rgba(21, 21, 21, 0.7);">
          <thead>
            <tr>
              <th width="100%" colspan="4"><small><small style="text-transform: uppercase;"><?php echo $data['name']; ?></small></small><br /><h5><?php echo $loser ?>Round <?php echo $match['round']; ?>, Match <?php echo $match['identifier']; ?></h5></th>
          </thead>
          <tbody style="text-align: center;">
            <tr>
              <th><h4><?php echo $score['p1']; ?></h4></th>
          <td><h4><?php echo $score['p1pts']['1']; ?></h4></td>
          <td><h4><?php echo $score['p1pts']['2']; ?></h4></td>
          <td><h4><?php echo $score['p1pts']['3']; ?></h4></td>
          </tr>
          <tr>
            <th><h4><?php echo $score['p2']; ?></h4></th>
          <td><h4><?php echo $score['p2pts']['1']; ?></h4></td>
          <td><h4><?php echo $score['p2pts']['2']; ?></h4></td>
          <td><h4><?php echo $score['p2pts']['3']; ?></h4></td>
          </tr>
          </tbody>
        </table><?php
        if ($config['challonge_expose']['scorebug']) {
          echo <<<CHA
        <div style="background: rgba(21, 21, 21, 0.7);"><div class="panel-footer" style="background: rgba(21, 21, 21, 0.7);"><span class="pull-right"><small>Powered by Challonge <img src="img/challonge.png" style="height:1em" /></small></span><br /></div></div>
CHA;
        }
        ?>
      </div>
    </div>
  </body>
</html>
