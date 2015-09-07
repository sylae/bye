<?php
/**
 * Assorted random functions 
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

/**
 * Get a team name from an ID. Also optionally return a "pending" notice that tells
 * the reader what match will determine which player goes in this slot.
 * 
 * @todo I bet this could be coded better
 * @param int $needle team ID to search for
 * @param array $haystack raw challonge payload
 * @param int $pending ID of match we are waiting on
 * @param boolean $pending_loser if true, we want the pending match's loser
 * @return string name of team, or HTML chunk describing what we're waiting on.
 */
function getName($needle, $haystack, $pending = null, $pending_loser = null) {
  if (is_null($needle)) {
    $d = getMatch($pending, $haystack);
    $who = "<em>" . ($pending_loser ? "Winner of " : "Loser of ") . "</em>";
    return $who . $d['identifier'] . ' <small>Match ID <a href="#match_' . $d['id'] . '">' . $d['id'] . "</a></small>";
  }
  foreach ($haystack['participants'] as $i => $data) {
    if ($data['participant']['id'] == $needle) {
      return $data['participant']['name'];
    }
  }
}

/**
 * Turn a match ID into its data array
 * 
 * @todo I'm sure this can be more efficient.
 * @param int $needle match ID to return
 * @param array $haystack raw challonge payload
 * @return array challonge match information
 */
function getMatch($needle, $haystack) {
  foreach ($haystack['matches'] as $i => $data) {
    if ($data['match']['id'] == $needle) {
      return $data['match'];
    }
  }
}

/**
 * Simple function to HTTP PUT an array. Shamelessly stolen from stackoverflow.
 * @link http://stackoverflow.com/a/6609181
 * @param array $payload Data to upload
 * @param string $destination URL to PUT at
 * @return string the response from the server.
 */
function httpPut($payload, $destination) {
  $options = array(
    'http' => array(
      'header' => "Content-type: application/x-www-form-urlencoded\r\n",
      'method' => 'PUT',
      'content' => http_build_query($payload),
    ),
  );
  $context = stream_context_create($options);
  $result = file_get_contents($destination, false, $context);

  return $result;
}
