<?php
/**
 * A dashboard for grabbing things from and viewing stream status.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
require_once 'config.php';
require_once 'sorts.php';
require_once 'misc.php';
$debug = array();
$data = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      ".json?api_key=" .
      $config['challonge_api'] .
      "&include_participants=1&include_matches=1"
    ), true)['tournament'];

if (array_key_exists('match', $_POST)) {
  $debug['http_post'] = $_POST;
  // filter time!
  $p = array();
  foreach ($_POST as $k => $v) {
    $p[$k] = is_numeric($v) ? (int) $v : "";
  }
  $m = getMatch($p['match'], $data);
  $score = array();
  $p1w = 0;
  $p2w = 0;

  // round 1
  if ($p['p1r1'] > $p['p2r1']) {
    $p1w++;
  } else {
    $p2w++;
  }
  $score[1] = $p['p1r1'] . "-" . $p['p2r1'];

  // round 2
  if (is_int($p['p1r2']) && is_int($p['p2r2'])) {
    if ($p['p1r2'] > $p['p2r2']) {
      $p1w++;
    } else {
      $p2w++;
    }
    $score[2] = $p['p1r2'] . "-" . $p['p2r2'];
  }

  // round 3 (if applicable)
  if (($p1w > 1 || $p2w > 1) && is_int($p['p1r3']) && is_int($p['p2r3'])) {
    if ($p['p1r3'] > $p['p2r3']) {
      $p1w++;
    } else {
      $p2w++;
    }
    $score[3] = $p['p1r3'] . "-" . $p['p2r3'];
  }
  $match = array(
    'scores_csv' => implode(",", $score),
  );

  // first kill any existing attachment (pending) score
  $attach = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      "/matches/{$m['id']}/attachments.json?api_key=" .
      $config['challonge_api']
    ), true);
  foreach ($attach as $n => $payload) {
    if (substr($payload['match_attachment']['description'], 0, 18) == '$BYEPENDINGSCORE$:') {
      $resp = httpPut(array(
        'api_key' => $config['challonge_api'], 'match' => $match), "https://api.challonge.com/v1/tournaments/{$config['challonge_id']}"
        . "/matches/{$m['id']}/attachments/{$payload['match_attachment']['id']}.json", 'DELETE');
      $debug['challonge_response_delete_existing'] = json_decode($resp, true);
    }
  }

  if (array_key_exists('done', $_POST)) {
    $match['winner_id'] = ($p1w > $p2w) ? $m['player1_id'] : $m['player2_id'];

    $resp = httpPut(array(
      'api_key' => $config['challonge_api'], 'match' => $match), "https://api.challonge.com/v1/tournaments/{$config['challonge_id']}/matches/{$m['id']}.json");
    $debug['challonge_response'] = json_decode($resp, true);
  } else {

    // now (re)make the attachement
    $attachment = array(
      'description' => '$BYEPENDINGSCORE$:' . $match['scores_csv'],
    );
    $resp = httpPut(array(
      'api_key' => $config['challonge_api'], 'match_attachment' => $attachment), "https://api.challonge.com/v1/tournaments/{$config['challonge_id']}/matches/{$m['id']}/attachments.json", 'POST');
    $debug['challonge_response'] = json_decode($resp, true);
  }
  // assume it worked--if it didn't we'd get a text wall of errors. Re-get the data since we did something
  $data = json_decode(file_get_contents(
        "https://api.challonge.com/v1/tournaments/" .
        $config['challonge_id'] .
        ".json?api_key=" .
        $config['challonge_api'] .
        "&include_participants=1&include_matches=1"
      ), true)['tournament'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bye - <?php echo $data['name']; ?></title>
    <script src="/js/jquery.min.js" type="text/javascript" charset="UTF-8"></script>
    <link href="/css/bootstrap.css" rel="stylesheet" />
    <script src="/js/bootstrap.min.js"></script>
    <meta charset="UTF-8" />
    <script>
      $(document).ready(function () {
        $(this).find(".panel-heading").click(function (event) {
          $(this).siblings(".collapse").collapse('toggle');
        });
      });
    </script>
  </head>
  <body>
    <nav class="navbar navbar-default navbar-inverse navbar-static-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><?php echo $data['name']; ?></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="/overlay.html">Overlay</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="http://challonge.com/<?php echo $config['challonge_id']; ?>">Tournament on Challonge <img src="img/challonge.png" style="height:1em" /></a></li>
          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
      <div class="col-md-12">
        <div class="progress">
          <div class="progress-bar progress-bar-striped" style="width: <?php echo $data['progress_meter']; ?>%;">
            <?php echo $data['progress_meter']; ?>%
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <h3>Pending Matches</h3>
        <div style="-moz-column-width: 500px;-webkit-column-width: 500px;">
          <?php
          uasort($data['matches'], 'sortMatches');
          foreach ($data['matches'] as $n => $info) {
            $d = $info['match'];
            $p1 = getName($d['player1_id'], $data, $d['player1_prereq_match_id'], $d['player1_is_prereq_match_loser']);
            $p2 = getName($d['player2_id'], $data, $d['player2_prereq_match_id'], $d['player2_is_prereq_match_loser']);
            $d['round'] = str_replace("-", "L", $d['round']);

            $notif = "";
            if ($d['state'] == "open") {
              if (!is_null($d['underway_at'])) {
                $notif = '<span class="glyphicon glyphicon-star text-primary" title="Active match"></span>';
              }
              echo <<<OPEN
        <div class="panel panel-default">
          <div class="panel-heading">
            <div class="pull-right"><p class="text-muted">$notif Open</p></div>
            <a name="match_{$d['id']}"></a>{$d['identifier']} (Round {$d['round']}) <small>Match ID {$d['id']}</small>
            </div>
            <div class="panel-body collapse">
              <form class="form-horizontal" method="post">
                <input type="hidden" name="match" value="{$d['id']}" />
                <div class="form-group">
                  <label class="col-sm-6 control-label">{$p1}</label>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r1" />
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r2" />
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r3" />
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-6 control-label">{$p2}</label>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r1" />
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r2" />
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r3" />
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-6 col-sm-3">
                    <button type="submit" class="btn btn-default">Submit Score</button>
                  </div>
                  <div class="col-sm-3 checkbox">
                    <label>
                      <input type="checkbox" name="done"> Final?
                    </label>
                  </div>
                </div>
              </form>
          </div>
          <div class="panel-footer collapse"><a href="/match.php?match={$d['id']}">Matchbug</a> - <a href="/team.php?team={$d['player1_id']}">Team 1</a> - <a href="/team.php?team={$d['player2_id']}">Team 2</a></div>
        </div>
OPEN;
            } elseif ($d['state'] == "pending") {
              echo <<<PEND
        <div class="panel panel-default">
          <div class="panel-heading">
            <div class="pull-right"><p class="text-muted">Pending</p></div>
            <a name="match_{$d['id']}"></a>{$d['identifier']} (Round {$d['round']}) <small>Match ID {$d['id']}</small>
          </div>
          <div class="panel-body text-center collapse">
            <p><strong>{$p1}</strong></p>
            <p><small><em>versus</em></small></p>
            <p><strong>{$p2}</strong></p>
          </div>
          <div class="panel-footer collapse"><a href="/match.php?match={$d['id']}">Matchbug</a> - <a href="/team.php?team={$d['player1_id']}">Team 1</a> - <a href="/team.php?team={$d['player2_id']}">Team 2</a></div>
        </div>
PEND;
            }
          }
          ?>
        </div>
      </div>
    </div>
    <footer class="footer">
      <div class="container">
        <hr />
        <div class="row">
          <div class="col-md-4">
            <p>
              <strong><a href="https://github.com/sylae/bye">Bye - Tournament streaming toolkit</a></strong><br />
              &copy; 2016 Sylae Corell &lt;<a href="mailto:sylae@calref.net">sylae@calref.net</a>&gt;
            </p>
            <p><small><em>
                  This program is free software: you can redistribute it and/or modify
                  it under the terms of the GNU General Public License as published by
                  the Free Software Foundation, either version 3 of the License, or
                  (at your option) any later version.<br />

                  This program is distributed in the hope that it will be useful,
                  but WITHOUT ANY WARRANTY; without even the implied warranty of
                  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
                  GNU General Public License for more details.<br />

                  You should have received a copy of the GNU General Public License
                  along with this program.  If not, see &lt;<a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>&gt;.
                </em></small></p>
          </div>
          <div class="col-md-8">
            <table class="table">
              <tbody>
                <tr>
                  <td>Bye version</td>
                  <td><code><?php echo trim(`git rev-parse HEAD`); ?></code></td>
                </tr>
                <tr>
                  <td>PHP version</td>
                  <td><?php echo trim(phpversion()); ?></td>
                </tr>
                <tr>
                  <td>System</td>
                  <td><em><?php echo trim(php_uname()); ?></em></td>
                </tr>
              </tbody>
            </table>
            <div class="panel panel-default">
              <div class="panel-heading">Debug Info</div>
              <div class="panel-body collapse">
                <?php
                foreach ($debug as $k => $v) {
                  echo "<h6>$k</h6>";
                  var_dump($v);
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>

  </body>
</html>
