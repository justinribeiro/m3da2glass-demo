<?php
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
require_once 'util.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';

// we're going to return JSON at the end, trust me
header('Content-Type: application/json');

// Polls the broker from some JSON
$data = file_get_contents('http://m2m.eclipse.org/m3da/clients/RPI000000006f257df2/data');

// Checks to see if we have anything
if ($data != "") {

    // Decode it into an array
    $arrayFromData = json_decode($data, true);

    if ($arrayFromData['greenhouse.data.open'][0]['value'][0]) {
        $roofState = "Open";
    } else {
        $roofState = "Closed";
    }

    $client = get_google_api_client();
    $client->setAccessToken(get_credentials($_SESSION['userid']));

    // A glass service for interacting with the Mirror API
    $mirror_service = new Google_MirrorService($client);

    $new_timeline_item = new Google_TimelineItem();

    $new_timeline_item->setHTML("<article>
        <figure>
            <p class=\"text-small align-center\" style=\"padding:1.2em\"><b>Eclipse M2M <br/><br/>Data from M3DA Broker</b></p>
        </figure>
        <section>
            <table class=\"text-smallalign-justify\">
                <tbody>
                    <tr>
                        <td class=\"green\">Temp</td>
                        <td>" . round($arrayFromData['greenhouse.data.temperature'][0]['value'][0], 1) . " &deg;</td>
                    </tr>
                    <tr>
                        <td>Lux</td>
                        <td>" . round($arrayFromData['greenhouse.data.luminosity'][0]['value'][0], 1) . "</td>
                    </tr>
                    <tr>
                        <td class=\"blue\">Humidity</td>
                        <td>" . round($arrayFromData['greenhouse.data.humidity'][0]['value'][0], 1) . " %</td>
                    </tr>
                    <tr>
                        <td>Roof</td>
                        <td>" . $roofState . "</td>
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

    echo json_encode(array("message" => "Timeline item inserted into Glass...sleeping for 1 minute"));
    
} else {
    echo json_encode(array("message" => "No data from broker...sleeping for 1 minute"));
}

?>