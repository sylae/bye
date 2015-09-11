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

if (is_numeric($_GET['round'])) {
  $r = (int) $_GET['round'];
} else {
  die();
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
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Round <?php echo $r; ?></title>
    <script src="/js/jquery.min.js" type="text/javascript" charset="UTF-8"></script>
    <link href="/css/bootstrap.css" rel="stylesheet" />
    <script src="/js/bootstrap.min.js"></script>
    <meta charset="UTF-8" />
    <script>
      $(document).ready(function() {
        $(".panel").click(function() {
          if ($(this).css('opacity') == 0) {
            $(this).css('opacity', 1);
          } else {
            $(this).css('opacity', 0);
          }
        });
        setTimeout(function() {
          $(".panel").css('opacity', 1);
        }, 1000);
      });
    </script>
  </head>
  <body style="background:transparent;">
    <div class="container">
      <div class="panel panel-default navbar-fixed-bottom" style="background: rgba(0,0,0,0);transition: opacity 1s ease-in-out; opacity:0;">
        <table class="table" style="background: url('/img/rocket_bg.png') no-repeat scroll right top, url('/img/stripe.png') no-repeat scroll left top, rgba(21, 21, 21, 0.7);">
          <thead>
            <tr>
              <th width="100%" colspan="3"><small><small style="text-transform: uppercase;"><?php echo $data['name']; ?></small></small><br /><h5>Round <?php echo $r; ?></h5></th>
          </tr>
          </thead>
          <tbody style="text-align: center;">
            <?php
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
              echo
              <<<STUFF
            <tr>
              <td style="width:40%;text-align: right;"><h4>{$data['p1']}</h4></td>
              <td><small><small style="text-transform: uppercase;">Match {$data['identifier']}{$roundl}</small></small><br /><h5>{$scorebug}</h5></td>
              <td style="width:40%;text-align: left;"><h4>{$data['p2']}</h4></td>
            </tr>
STUFF;
            }
            ?>
          </tbody>
        </table><?php
        if ($config['challonge_expose']['standings']) {
          echo <<<CHA
        <div style="background: rgba(21, 21, 21, 0.7);"><div class="panel-footer" style="background: rgba(21, 21, 21, 0.7);"><span class="pull-right"><small>Powered by Challonge <img src="img/challonge.png" style="height:1em" /></small></span><br /></div></div>
CHA;
        }
        ?>
      </div>
    </div>
  </body>
</html>
