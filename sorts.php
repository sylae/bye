<?php
/**
 * A bunch of functions for sorting challonge data arrays
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

function sortStanding($a, $b) {
  $w = byWins($a, $b);
  $s = bySets($a, $b);
  $p = byPts($a, $b);
  $d = byPtsDiff($a, $b);
  if ($w == 0) {
    if ($s == 0) {
      if ($p == 0) {
        return $d;
      } else {
        return $p;
      }
    } else {
      return $s;
    }
  } else {
    return $w;
  }
}

function byWins($a, $b) {
  $sca = $a['w'];
  $scb = $b['w'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca > $scb) ? -1 : 1;
}

function bySets($a, $b) {
  $sca = $a['sets'];
  $scb = $b['sets'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca > $scb) ? -1 : 1;
}

function byPts($a, $b) {
  $sca = $a['pts'];
  $scb = $b['pts'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca > $scb) ? -1 : 1;
}

function byPtsDiff($a, $b) {
  $sca = $a['pts'] - $a['ptsgiven'];
  $scb = $b['pts'] - $a['ptsgiven'];
  if ($sca == $scb) {
    return 0;
  }
  return ($sca > $scb) ? -1 : 1;
}

function sortMatches($a, $b) {
  $w = byMatchStatus($a, $b);
  $s = byRound($a, $b);
  $p = strcmp($a['match']['identifier'], $b['match']['identifier']);
  if ($w == 0) {
    if ($s == 0) {
      return $p;
    } else {
      return $s;
    }
  } else {
    return $w;
  }
}

function byMatchStatus($a, $b) {
  $sca = _byMatchStatusMakeNumeric($a['match']['state']);
  $scb = _byMatchStatusMakeNumeric($b['match']['state']);
  if ($sca == $scb) {
    return 0;
  }
  return ($sca < $scb) ? -1 : 1;
}

function _byMatchStatusMakeNumeric($a) {
  switch ($a) {
    case 'open':
      return 0;
    case 'pending':
      return 1;
    case 'complete':
      return 2;
    default:
      // something funky
      return -1;
  }
}

function byRound($a, $b) {
  $sca = _byRoundLoserParse($a['match']['round']);
  $scb = _byRoundLoserParse($b['match']['round']);
  if ($sca == $scb) {
    return 0;
  }
  return ($sca < $scb) ? -1 : 1;
}

function _byRoundLoserParse($n) {
  if ($n < 0) {
    // challonge gives losers bracket rounds as negative.
    // A normal sort will put them first. So let's put them last.
    return ($n*-1)+10000;
  }
  return $n;
}