<?php
require_once 'config.php';
require_once 'sorts.php';

$data = file_get_contents("https://api.challonge.com/v1/tournaments/" . $config['challonge_id'] . ".json?api_key=" . $config['challonge_api'] . "&include_participants=1&include_matches=1");
$data = json_decode($data, true)['tournament'];
$teams = array();
$matches = array();

// take what we need from the participants array and make our own with blackjack and hookers.
foreach ($data['participants'] as $k => $v) {
  $teams[$v['participant']['id']] = array(
    'name' => $v['participant']['name'],
    'seed' => $v['participant']['seed'],
    'w' => 0,
    'l' => 0,
    'sets' => 0,
    'pts' => 0,
    'ptsgiven' => 0,
  );
}

// same thing for matches
foreach ($data['matches'] as $k => $v) {
  if ($v['match']['state'] == "complete") {
    $points = explode(",", $v['match']['scores_csv']);
    $p1 = 0;
    $p2 = 0;
    $p1s = 0;
    $p2s = 0;
    foreach ($points as $k => $val) {
      $p = explode("-", $val);
      $p1 += $p[0];
      $p2 += $p[1];
      if ($p[0] > $p[1]) {
        $p1s++;
      } else {
        $p2s++;
      }
    }
    $matches[$v['match']['id']] = array(
      'name' => $v['match']['identifier'],
      'p1' => $v['match']['player1_id'],
      'p2' => $v['match']['player2_id'],
      'w' => $v['match']['winner_id'],
      'l' => $v['match']['loser_id'],
      'p1pts' => $p1,
      'p2pts' => $p2,
      'p1set' => $p1s,
      'p2set' => $p2s,
    );
  }
}

// Now we have everything we need. forget all that challonge shit.
// Import the $matches info into the team array
foreach ($matches as $id => $data) {
  $teams[$data['p1']]['pts'] += $data['p1pts'];
  $teams[$data['p2']]['ptsgiven'] += $data['p1pts'];
  $teams[$data['p1']]['sets'] += $data['p1set'];
  $teams[$data['p2']]['pts'] += $data['p2pts'];
  $teams[$data['p1']]['ptsgiven'] += $data['p2pts'];
  $teams[$data['p2']]['sets'] += $data['p2set'];
  $teams[$data['w']]['w'] ++;
  $teams[$data['l']]['l'] ++;
}

// Hopefully that went well and didn't fuck up everything. Let's try to fuck it up again
foreach ($teams as $id => $data) {
  if ($data['w'] + $data['l'] > 0) {
    $teams[$id]['pct'] = number_format($data['w'] / ($data['w'] + $data['l']), 3);
  } else {
    $teams[$id]['pct'] = "-";
  }
  $teams[$id]['diff'] = $teams[$id]['pts'] - $teams[$id]['ptsgiven'];
}
uasort($teams, 'bigSort');
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Standings</title>
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
      });
    </script>
  </head>
  <body style="background:transparent;">
    <div class="container">
      <div class="panel panel-default navbar-fixed-bottom" style="background: rgba(0,0,0,0);transition: opacity 1s ease-in-out;">
        <table class="table" style="background: url('/img/rocket_bg.png') no-repeat scroll right top, url('/img/stripe.png') no-repeat scroll left top, rgba(21, 21, 21, 0.7);">
          <thead>
            <tr>
              <th width="100%">STANDINGS</th>
              <th style="text-align: center;">W</th>
              <th style="text-align: center;">L</th>
              <th style="text-align: center;">Pct</th>
              <th style="text-align: center;">Sets</th>
              <th style="text-align: center;">Goals</th>
              <th style="text-align: center;">Diff</th>
            </tr>
          </thead>
          <tbody style="text-align: center;">
            <?php
            foreach ($teams as $id => $data) {
              echo
              <<<STUFF
            <tr>
              <td style="text-align: left;">{$data['name']}</td>
              <td>{$data['w']}</td>
              <td>{$data['l']}</td>
              <td>{$data['pct']}</td>
              <td>{$data['sets']}</td>
              <td>{$data['pts']}</td>
              <td>{$data['diff']}</td>
            </tr>
STUFF;
            }
            ?>
          </tbody>
        </table><?php if ($config['challonge_expose']) {
          echo <<<CHA
        <div style="background: rgba(21, 21, 21, 0.7);"><div class="panel-footer" style="background: rgba(21, 21, 21, 0.7);"><span class="pull-right"><small>Powered by Challonge <img src="img/challonge.png" style="height:1em" /></small></span><br /></div></div>
CHA;
          } ?>
      </div>
    </div>
  </body>
</html>
