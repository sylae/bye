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

$p1 = array("r1" => "", "r2" => "", "r3" => "");
$p2 = array("r1" => "", "r2" => "", "r3" => "");
if (strlen($match['scores_csv']) > 2) {
  $points = explode(",", $match['scores_csv']);
  $r = 1;
  foreach ($points as $k => $val) {
    $p = explode("-", $val);
    if (array_key_exists(1, $p)) {
      $p1["r" . $r] = $p[0];
      $p2["r" . $r] = $p[1];
      $r++;
    }
  }
}
foreach (array("r1", "r2", "r3") as $n) {
  if ($p1[$n] > $p2[$n]) {
    $p1[$n] = "<strong>{$p1[$n]}</strong>";
  } else {
    $p2[$n] = "<strong>{$p2[$n]}</strong>";
  }
}
$score = array(
  'title' => $data['name'],
  'match' => "{$loser}Round {$match['round']}, Match {$match['identifier']}",
  'p1' => getName($match['player1_id'], $data),
  'p2' => getName($match['player2_id'], $data),
  'p1pts' => $p1,
  'p2pts' => $p2,
);
header("Content-Type: application/json; charset=utf-8");
echo json_encode($score);
