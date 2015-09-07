<?php
/**
 * Display a bracket. Useful for elimination tournaments.
 * @uses bracket_iframe.php
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

require_once 'config.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Bracket</title>
    <script src="/js/jquery.min.js" type="text/javascript" charset="UTF-8"></script>
    <link href="/css/bootstrap.css" rel="stylesheet" />
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/jquery.autoheight.js"></script>
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
              <th width="100%">BRACKET</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><iframe class="autoHeight" src="bracket_iframe.php" width="100%" height="500" frameborder="0" scrolling="no" allowtransparency="true"></iframe></td>
            </tr>
          </tbody>
        </table><?php if ($config['challonge_expose']['bracket']) {
          echo <<<CHA
        <div style="background: rgba(21, 21, 21, 0.7);"><div class="panel-footer" style="background: rgba(21, 21, 21, 0.7);"><span class="pull-right"><small>Powered by Challonge <img src="img/challonge.png" style="height:1em" /></small></span><br /></div></div>
CHA;
          } ?>
      </div>
    </div>
  </body>
</html>
