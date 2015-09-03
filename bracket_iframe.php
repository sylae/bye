<?php
require_once 'config.php';
require 'qp.php'; // don't fall for that 2.x crap.

$data = file_get_contents("http://challonge.com/" . $config['challonge_id'] . "/module?theme=2&&match_width_multiplier=0.8");

$info = htmlqp($data)->remove("script, #challonge_promo, .live_stamp");
ob_start();
$info->writeHTML();
$html = ob_get_contents();
ob_end_clean();
echo str_replace(array("</head>", '<a class="btn btn-link match_identifier dropdown-toggle">'), array('<link rel="stylesheet" href="css/bracket_over.css" type="text/css" /></head>', '<a class="match_identifier">'), $html);