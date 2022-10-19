<?
ini_set("memory_limit", "-1");
set_time_limit(0);
include("/var/www/www-root/data/www/bloodgodz.com/mongo/mainMongo.php");
$ws = new \Workerman\Worker("websocket://127.0.0.1:7577");

$ws->count = 1;
$global_uid = 0;

$ws->onWorkerStart = function ($task) {
};
$ws->onConnect = function ($connection) {
    global $global_uid;
    $userId = time();
    $connection->uid = $userId;
    global $ws;
};
$ws->onMessage = function ($connection, $data) {
    $data = json_decode($data);
    global $ws, $chatList,$db;
    if (empty($data->action)) return;
    if ($data->action == 'userConnectAdmin') {
        foreach ($ws->connections as $key => $conn) {
                $conn->send(json_encode([
                    "type" => "alert",
                    "msg" => $data->alert
                ]));
        }
    }
    if ($data->action == 'userConnect') {
        $token = $data->data;
        if (empty($token)) return;
        $thisUser = FindUserByToken($token);
        if ($thisUser == false) return;
        $profile_id = (int)str_replace("/user/", "", $data->profile);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return;
        $connection->token = $token;
        $connection->userid = $thisUser->id;
        $connection->current_view_profile = $profile->id;
        $q_msgs = $db->users_walls->find(['owner_id' => (int)$profile_id], [
            'sort' => ['id' => -1]
        ])->toArray();
        $msgs = [];
        for ($i = 0; $i < count($q_msgs); $i++) {
            $msg = (object)$q_msgs[$i];
            $msgUser = FindUserByID($msg->sender_id);
            $msgs[] = ["id" => $msg->id, "username" => htmlspecialchars($msgUser->username), "text" => $msg->text, 'userid'=> $msg->owner_id, "date" => $msg->date];
        }
        $connection->send(json_encode([
            "type" => "get_messages",
            "msg" => $msgs,
            'userid'=> $thisUser->id
        ]));
    }
    if ($data->action == 'sendMessage') {
        if (empty($connection->token)) return;
        $token  = $connection->token;
        $thisUser = FindUserByToken($token);
        if ($thisUser == false) return;
        $profile_id = (int)str_replace("/user/", "", $data->profile);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return;
        $text = $data->text;
        if (strlen($text) < 3) return;
        $new = sendMessageInWall($profile->id, $thisUser->id, $text);
        if (empty($new->uid)) return;
        $new_msg = ["id"=> $new, "username" => htmlspecialchars($thisUser->username), "text" => $data->text, "userid"=> $thisUser->id, "date" => date("Y-m-d H:i:s")];
        foreach ($ws->connections as $key => $conn) {
            if (!empty($conn->current_view_profile) && $conn->current_view_profile == $profile_id) {
                $conn->send(json_encode([
                    "type" => "new_message",
                    "msg" => $new_msg,
                    'userid'=> $thisUser->id
                ]));
            }
        }
    }
    if ($data->action == 'deleteMessageInProfile') {
        $msg_id = (int)$data->id;
        if ($msg_id == 0) return;
        $findMsg = find_in_db_array("users_walls", ["id"=> $msg_id]);
        if (count($findMsg) == 0) return;
        $findMsg = (object)$findMsg[0];
        if (empty($connection->token)) return;
        $token  = $connection->token;
        $thisUser = FindUserByToken($token);
        if ($thisUser == false) return;
        if ($findMsg->owner_id != $thisUser->id || $findMsg->owner_id == 1080) return;
        delete_in_db("users_walls",  ["id"=> $msg_id]);
        $connection->send(json_encode(["type"=> "message_deleted",'msg'=> 'removed', 'id'=> $msg_id]));
    }
};
$ws->onClose = function ($connection) {
    if (!empty($connection->userid))     echo "userwallWS >> " . "User-{$connection->userid} disconnected\n";
    else echo "userwallWS >> " . "UserUID-$connection->uid\n";
};
\Workerman\Worker::runAll();
