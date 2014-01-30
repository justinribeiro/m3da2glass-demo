<?php
/*
* Copyright (C) 2013 Google Inc.
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*      http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
//  Author: Jenny Murphy - http://google.com/+JennyMurphy

//
// The quick and the messy
//
// This is a barebones poller; it doesn't do any checks that you'd normally do. I wrote it just to play and 
// took no precautions.
// 
// What should be happening:
//
//  1. You should be comparing the value set to previous; if things haven't changed, then don't send an update
//  2. You should be storing the timelineid and updating the card, not inserting a new one everytime
//  3. I don't like hard polling a broker; it sort of defeats the purpose but it does work.
//  4. Seriously, the libs with services + offline tokens + Mirror API = win: http://m2m.eclipse.org/
//
//  Author: Justin Ribeiro - http://justinribeiro.com
//

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';

$client = get_google_api_client();

// Authenticate if we're not already
if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid']) == null) {
  header('Location: ' . $base_url . '/oauth2callback.php');
  exit;
} else {
  $client->setAccessToken(get_credentials($_SESSION['userid']));
}

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

?>
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>M3DA 2 Glass</title>
  <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  <style>
    .button-icon { max-width: 75px; }
    .tile {
      border-left: 1px solid #444;
      padding: 5px;
      list-style: none;
    }
    .btn { width: 100%; }
  </style>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#">M3DA 2 Glass</a>
      <div class="nav-collapse collapse">
        <form class="navbar-form pull-right" action="signout.php" method="post">
          <button type="submit" class="btn">Sign out</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="container">
	<div class="hero-unit">
		<h1>You've signed in!</h1>
		<p>Looks like you got some auth tokens! Yippee!</p>
	</div>
  
	<div class="row-fluid">
		<div class="span12">
			<h2>Now what?</h2>
			<p>This is a barebones demo, and I mean barebones. So what's going on?</p>
      <ol>
        <li>You've auth'ed your account to get notifications on your Glass.</li>
        <li>This page runs a simple setInterval() script that runs every 1 min</li>
        <li>The ajax callout hits poll.php, which then pings the M3DA broker and parses the JSON</li>
        <li>That is then sent to Glass as a timeline item</li>
      </ol>
      <p>Simple. Incomplete, but a simple working example.</p>
      <p>Note: if you leave this running, this thing will litter your timeline. It doesn't store the timelineid for updates. :-) 5 minute implemention.</p>

      <h3 id="pollUpdate">Waiting to poll service...</h3>			

		</div>
	</div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/static/bootstrap/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
  var intervalId;
 
  function initDemo() {
    // run every 1 min
    intervalId = setInterval(pingService, 60000);
  }

  function pingService() {
    var promise = $.ajax({
        url: "/poll.php",
        type: "POST",
    });

    promise.done(function (data) {
      $("#pollUpdate").html(data.message);
    });

    promise.fail(function (data) {
        $("#pollUpdate").html(data.message);
    });
  }

  // Just hit it once right off the bat
  pingService();

  // then start the service
  initDemo();
});
</script>

</body>
</html>
