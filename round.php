<?php

/**
 * Display a table showing current round games. Good for round-robin
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require_once 'config.php';
require_once 'misc.php';
require_once 'sorts.php';

$data = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      ".json?api_key=" .
      $config['challonge_api'] .
      "&include_participants=1&include_matches=1"
    ), true)['tournament'];
$matches = array();

if (array_key_exists('round', $_GET) && is_numeric($_GET['round'])) {
  $r = (int) $_GET['round'];
} else {
  $matchsumm = [];
  foreach ($data['matches'] as $i => $payload) {
    if ($payload['match']['state'] == 'open') {
      if (!array_key_exists($payload['match']['round'], $matchsumm)) {
        $matchsumm[$payload['match']['round']] = 0;
      }
      $matchsumm[$payload['match']['round']] ++;
    }
  }
  $r = array_keys($matchsumm, max($matchsumm))[0];
}

foreach ($data['matches'] as $i => $payload) {
  if ($payload['match']['round'] == $r) {
    $matches[$payload['match']['id']] = $payload['match'];
    $attach = json_decode(file_get_contents(
        "https://api.challonge.com/v1/tournaments/" .
        $config['challonge_id'] .
        "/matches/{$payload['match']['id']}/attachments.json?api_key=" .
        $config['challonge_api']
      ), true);
    foreach ($attach as $n => $pay) {
      if (substr($pay['match_attachment']['description'], 0, 18) == '$BYEPENDINGSCORE$:') {
        $matches[$payload['match']['id']]['scores_csv'] = str_replace('$BYEPENDINGSCORE$:', '', $pay['match_attachment']['description']);
      }
    }
    $matches[$payload['match']['id']]['p1'] = getName($matches[$payload['match']['id']]['player1_id'], $data);
    $matches[$payload['match']['id']]['p2'] = getName($matches[$payload['match']['id']]['player2_id'], $data);
    $points = explode(",", $matches[$payload['match']['id']]['scores_csv']);
    $matches[$payload['match']['id']]['p1s'] = 0;
    $matches[$payload['match']['id']]['p2s'] = 0;
    foreach ($points as $k => $val) {
      $p = explode("-", $val);
      if (array_key_exists(1, $p)) {
        if ($p[0] > $p[1]) {
          $matches[$payload['match']['id']]['p1s'] ++;
        } else {
          $matches[$payload['match']['id']]['p2s'] ++;
        }
      }
    }
    if ($matches[$payload['match']['id']]['state'] == "complete" && $matches[$payload['match']['id']]['p1s'] > $matches[$payload['match']['id']]['p2s']) {
      $matches[$payload['match']['id']]['p2'] = "<span class=\"text-muted\">{$matches[$payload['match']['id']]['p2']}</span>";
    } elseif ($matches[$payload['match']['id']]['state'] == "complete" && $matches[$payload['match']['id']]['p1s'] < $matches[$payload['match']['id']]['p2s']) {
      $matches[$payload['match']['id']]['p1'] = "<span  class=\"text-muted\">{$matches[$payload['match']['id']]['p1']}</span>";
    }
  }
}

$payload = [
  'tname' => $data['name'],
  'round' => "Round $r",
  'teams' => [],
];

foreach ($matches as $id => $data) {
  $roundl = "";
  if ($data['state'] == "complete" && $data['p1s'] + $data['p2s'] > 0) {
    $scorebug = "{$data['p1s']} - {$data['p2s']}";
  } elseif ($data['state'] != "complete" && ($data['p1s'] + $data['p2s'] > 0)) {
    $scorebug = "{$data['p1s']} - {$data['p2s']}";
    $roundl = " - In Progress";
  } else {
    $scorebug = "<small>MATCH PENDING</small>";
  }
  $payload['teams'][] = [
    'p1' => $data['p1'],
    'p2' => $data['p2'],
    'match' => "Match " . $data['identifier'] . $roundl,
    'score' => $scorebug,
  ];
}
header("Content-Type: application/json; charset=utf-8");
echo json_encode($payload);
