<?php

function bigSort($a, $b) {
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
