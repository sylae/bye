<?php

/**
 * Display a table showing standings for the teams. Useful for round-robin.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require_once 'config.php';
require_once 'sorts.php';

$data = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      ".json?api_key=" .
      $config['challonge_api'] .
      "&include_participants=1&include_matches=1"
    ), true)['tournament'];
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
      if (array_key_exists(1, $p)) {
        $p1 += $p[0];
        $p2 += $p[1];
        if ($p[0] > $p[1]) {
          $p1s++;
        } else {
          $p2s++;
        }
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
uasort($teams, 'sortStanding');

header("Content-Type: application/json; charset=utf-8");
echo json_encode($teams);
