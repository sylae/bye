<?php

function getName($needle, $haystack, $pending, $pending_loser) {
  if (is_null($needle)) {
    $d = getMatch($pending, $haystack);
    $who = "<em>".($pending_loser ? "Winner of " : "Loser of ")."</em>";
    return $who . $d['identifier'] . ' <small>Match ID <a href="#match_' . $d['id'] . '">' . $d['id'] . "</a></small>";
  }
  foreach ($haystack['participants'] as $i => $data) {
    if ($data['participant']['id'] == $needle) {
      return $data['participant']['name'];
    }
  }
}

function getMatch($needle, $haystack) {
  foreach ($haystack['matches'] as $i => $data) {
    if ($data['match']['id'] == $needle) {
      return $data['match'];
    }
  }
}
