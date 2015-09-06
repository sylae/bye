<?php

// User agent to use for any requests
ini_set("user_agent","sylae/bye-".trim(`git rev-parse HEAD`));

$config = array();

// API key from https://challonge.com/settings/developer
$config['challonge_api'] = 'ddce269a1e3d054cae349621c198dd52';

// Tournament ID (either API id or the URL bit)
$config['challonge_id'] = 'sample';

// if true, but a "powered by challonge" at the bottom of the module.
$config['challonge_expose'] = array(
  'bracket' => true,
  'standings' => false,
);

// settings for Twitch integration on the dashboard
$config['twitch_clientid'] = "ddce269a1e3d054cae349621c198dd52"; 
$config['twitch_channel'] = "sample";