<?php

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

function getMatch($needle, $haystack) {
  foreach ($haystack['matches'] as $i => $data) {
    if ($data['match']['id'] == $needle) {
      return $data['match'];
    }
  }
}

function httpPut($payload, $destination) {
  $options = array(
    'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method' => 'PUT',
      'content' => http_build_query($payload),
    ),
  );
  $context = stream_context_create($options);
  $result = file_get_contents($destination, false, $context);

  return $result;
}
