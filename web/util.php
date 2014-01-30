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

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';


function store_credentials($user_id, $credentials) {
  $db = init_db();

  $user_id = $db->escapeString(strip_tags($user_id));
  $credentials = $db->escapeString(strip_tags($credentials));

  $insert = "insert into credentials values ('$user_id', '$credentials')";
  $db->exec($insert);
}

function get_credentials($user_id) {
  $db = init_db();
  $user_id = $db->escapeString(strip_tags($user_id));

  $results = $db->query("select * from credentials where userid = '$user_id'");

  $row = $results->fetchArray();
  return $row['credentials'];
}

function list_credentials() {
  $db = init_db();

  // Must use explicit select instead of * to get the rowid
  $query = sqlite_query($db, 'select userid, credentials from credentials');
  return sqlite_fetch_all($query, SQLITE_ASSOC);
  
}

// Create the credential storage if it does not exist
function init_db() {
  global $sqlite_database;
  
  $db = new SQLite3($sqlite_database);
  
  $test_query = "select count(*) from sqlite_master where name = 'credentials'";
  $results = $db->querySingle($test_query);
  
  if ($results == 0) {
	 $create_table = "create table credentials (userid text, credentials text);";
	 $db->exec($create_table);
  }  
  
  return $db;
}

function bootstrap_new_user() {
  global $base_url;

  $client = get_google_api_client();
  $client->setAccessToken(get_credentials($_SESSION['userid']));

  // A glass service for interacting with the Mirror API
  $mirror_service = new Google_MirrorService($client);

  $new_timeline_item = new Google_TimelineItem();
  
	// <article><figure><img src=\"http://sv.3dprinting.status.s3.amazonaws.com/snaps/from_mbr1d_20130708_30.jpg\"></figure><section><h1 class=\"text-large\">Print Complete</h1><p class=\"text-x-small align-center green\">plumb-fitting-v4c<br></p><hr><p class=\"text-small align-center\"><span style=\"padding-bottom: 0.5em;\">Build Time</div><br>2 <span class=\"text-x-small\">h</span> 32 <span class=\"text-x-small\">m</span> 14 <span class=\"text-x-small\">s</span></p></section></article>
  
   
    $new_timeline_item->setHTML("<article>
	<figure>
		<p class=\"text-small align-center\" style=\"padding:1.2em\"><b>Eclipse M2M <br/><br/>Data from M3DA Broker</b></p>
	</figure>
	<section>
		<table class=\"text-smallalign-justify\">
			<tbody>
				<tr>
					<td class=\"green\">Temp</td>
					<td>26.0 &deg;</td>
				</tr>
				<tr>
					<td>Lux</td>
					<td>91.0</td>
				</tr>
				<tr>
					<td class=\"blue\">Humidity</td>
					<td>93.3 &percnt;</td>
				</tr>
				<tr>
					<td>Roof</td>
					<td>Open</td>
				</tr>
			</tbody>
		</table>
	</section>
</article>");

    $notification = new Google_NotificationConfig();
    $notification->setLevel("DEFAULT");
    $new_timeline_item->setNotification($notification);

    $menu_items = array();

    $menu_item = new Google_MenuItem();
    $menu_item->setAction("DELETE");
    array_push($menu_items, $menu_item);

    $new_timeline_item->setMenuItems($menu_items);

    insert_timeline_item($mirror_service, $new_timeline_item, null, null);

}