<?php

// User agent to use for any requests
ini_set("user_agent", "sylae/bye-" . trim(`git rev-parse HEAD`));

$config = array();

// API key from https://challonge.com/settings/developer
$config['challonge_api'] = 'ddce269a1e3d054cae349621c198dd52';

// Tournament ID (either API id or the URL bit)
$config['challonge_id'] = 'sample';
