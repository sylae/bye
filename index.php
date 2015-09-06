<?php
require_once 'config.php';
require_once 'sorts.php';
require_once 'misc.php';

$data = json_decode(file_get_contents(
      "https://api.challonge.com/v1/tournaments/" .
      $config['challonge_id'] .
      ".json?api_key=" .
      $config['challonge_api'] .
      "&include_participants=1&include_matches=1"
    ), true)['tournament'];
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
      $(document).ready(function() {
        $(this).find(".panel-heading").click(function(event) {
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
            <li><a href="/bracket.php">Bracket</a></li>
            <li><a href="/standings.php">Standings</a></li>
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
      <div class="col-md-5">
        <h3>Pending Matches</h3>
        <?php
        foreach ($data['matches'] as $n => $info) {
          $d = $info['match'];
          $p1 = getName($d['player1_id'], $data, $d['player1_prereq_match_id'], $d['player1_is_prereq_match_loser']);
          $p2 = getName($d['player2_id'], $data, $d['player2_prereq_match_id'], $d['player2_is_prereq_match_loser']);

          if ($d['state'] == "open") {
            echo <<<OPEN
        <div class="panel panel-default">
          <div class="panel-heading">
            <div class="pull-right"><p class="text-muted">Open</p></div>
            <a name="match_{$d['id']}"></a>{$d['identifier']} (Round {$d['round']}) <small>Match ID {$d['id']}</small>
            </div>
            <div class="panel-body collapse">
              <form class="form-horizontal" method="post">
                <div class="form-group">
                  <label class="col-sm-6 control-label">{$p1}</label>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r1">
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r2">
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p1r3">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-6 control-label">{$p2}</label>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r1">
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r2">
                  </div>
                  <div class="col-sm-2">
                    <input class="form-control input-sm" name="p2r3">
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-6 col-sm-6">
                    <button type="submit" class="btn btn-default">Submit Score</button>
                  </div>
                </div>
              </form>
          </div>
          <div class="panel-footer collapse"><a href="/match.php?match=">Matchbug</a> - <a href="/team.php?team=">Team 1</a> - <a href="/team.php?team=">Team 2</a></div>
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
          <div class="panel-footer collapse"><a href="/match.php?match=">Matchbug</a> - <a href="/team.php?team=">Team 1</a> - <a href="/team.php?team=">Team 2</a></div>
        </div>
PEND;
          }
        }
        ?>
      </div>
      <div class="col-md-7">
        <h3>Tournament Information</h3>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h6 class="panel-title">Steam Information</h6>
          </div>
          <?php
          $twitch = json_decode(file_get_contents("https://api.twitch.tv/kraken/streams/{$config['twitch_channel']}?client_id={$config['twitch_clientid']}"), true)['stream'];
          if (is_null($twitch)) {
            echo "<div class=\"panel-body\">Twitch stream {$config['twitch_channel']} is offline.</div>";
          } else {
            echo <<<TWITCH
              <table class="table">
                <tr>
                  <th>Status</th>
                  <td>{$twitch['channel']['status']}</td>
                </tr>
                <tr>
                  <th>Game</th>
                  <td>{$twitch['game']}</td>
                </tr>
                <tr>
                  <th>Video</th>
                  <td>{$twitch['video_height']}p @ {$twitch['average_fps']}fps</td>
                </tr>
                <tr>
                  <th>Viewers</th>
                  <td>{$twitch['viewers']}</td>
                </tr>
                <tr>
                  <td colspan="2"><img src="{$twitch['preview']['large']}" class="img-responsive" /></td>
                </tr>
              </table>
TWITCH;
          }
          ?>
        </div>
      </div>
    </div>
    <footer class="footer">
      <?php
      //attention whore
      $auth = json_decode(file_get_contents("https://api.github.com/users/sylae"), true);
      ?>
      <div class="container">
        <hr />
        <div class="row">
          <div class="col-md-4">
            <p>
              <strong><a href="https://github.com/sylae/bye">Bye - Tournament streaming toolkit</a></strong><br />
              &copy; 2015 <img src="<?php echo $auth['avatar_url']; ?>" style="height:1em" /> Sylae Jiendra Corell &lt;<a href="mailto:sylae@calref.net">sylae@calref.net</a>&gt;
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
                <?php var_dump($twitch, $data, $data['matches'], $data['participants']); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>

  </body>
</html>
