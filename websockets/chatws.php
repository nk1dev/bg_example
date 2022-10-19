<?
ini_set("memory_limit", "-1");
set_time_limit(0);
include("/var/www/www-root/data/www/bloodgodz.com/mongo/mainMongo.php");
$ws = new \Workerman\Worker("websocket://127.0.0.1:9505");
$ws->count = 1;
$global_uid = 0;
$chatList = [];
$usersRateTimeLimit = [];
$emoji_casino = [
    "🍋", "7️⃣", "🍒", "💎"
];
$muteList = [];
$ws->onWorkerStart = function ($task) {
};
$ws->onConnect = function ($connection) {
    global $global_uid;
    $userId = time();
    $connection->uid = $userId;
};

$ws->onMessage = function ($connection, $data) {
    $data = json_decode($data);
    global $ws, $chatList, $usersRateTimeLimit, $emoji_casino, $muteList;
    if (empty($data->action)) return;
    if ($data->action == 'userConnect') {
        $token = $data->data;
        if (empty($token)) return;
        $thisUser = FindUserByToken($token);
        if ($thisUser == false) return;
        $connection->token = $token;
        $connection->userid = $thisUser->id;
        $connection->NeedConfirmLobby = false;
        $connection->send(json_encode([
            "type" => "get_messages",
            "msg" => $chatList
        ]));
    }
    if ($data->action == 'sendMessage') {
        $token  = $connection->token;
        $thisUser = FindUserByToken($token);
        if ($thisUser == false) return;
        $clean_msg = $data->data;
        $msg = $data->data;
        $clean_msg = str_replace(" ", "", $clean_msg);
        $clean_msg = str_replace("\t", "", $clean_msg);
        $clean_msg = str_replace("\n", "", $clean_msg);
        if ((int)strlen($clean_msg) < 3) {
            $connection->send(json_encode(["type" => "min_3_characters"]));
            return;
        }
        if (!empty($usersRateTimeLimit[$connection->userid]) && $usersRateTimeLimit[$connection->userid] + 10 > time()) {
            $connection->send(json_encode(["type" => "10_sec_limit", "time" => (int)(($usersRateTimeLimit[$connection->userid] + 10) - time())]));
            return;
        }
        if ((int)strlen($clean_msg) > 100) {
            $connection->send(json_encode(["type" => "max_100_characters"]));
            return;
        }
        $region = strtoupper($data->region);
        $mode = strtolower($data->mode);
        if (empty($mode) || empty($region)) return;
        if (!in_array($mode, __MODS_LIST__) || !in_array($region, __REGION_LIST__)) return;
        if ($data->data == "/clean" && $thisUser->group_id == 1) {
            $chatList = [];
            return;
        }
        if (!empty($muteList[$thisUser->id])) return;
        if (strripos($clean_msg, "script")) {
            $data->data = str_replace("<script>", "", $data->data);
            $data->data = str_replace("</script>", "", $data->data);
        }
        $chatList[] = ["userid" => (int)$thisUser->id, "username" => "{$thisUser->username}", "text" => $data->data, "group" => $thisUser->group_id];
        if ($thisUser->group_id != 1) {
            $usersRateTimeLimit[$connection->userid] = time();
        }



        echo "ChatWS >> " . "[NEW MESSAGE] | username: {$thisUser->username} | text: $data->data" . "\n";
        $json = json_encode(["type" => "new_message", "userid" => $thisUser->id, "username" => "{$thisUser->username}", "text" => $data->data, "group" => $thisUser->group_id]);
        $botEvent = false;
        if ($thisUser->group_id == 1) {
            if (strpos($clean_msg, "unmute")) {
                $unmuteUserID = (int)str_replace("/unmute", "", $clean_msg);
                if ($unmuteUserID == 0) {
                    return;
                }
                $unmuteUser = findUserByID($unmuteUserID);
                if ($unmuteUser == false) {
                    $botEvent = true;
                    $bot_text = "Пользователь с id-{$unmuteUserID} не найден";
                    $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1]);
                    $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1];
                } else {
                    unset($muteList[$unmuteUser->id]);
                    $botEvent = true;
                    $bot_text = "Пользователь {$unmuteUser->username} разблокирован в чате";
                    $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1]);
                    $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1];
                }
            }
            elseif (strpos($clean_msg, "mute")) {
                $muteUserID = (int)str_replace("/mute", "", $clean_msg);
                if ($muteUserID == 0) {
                    return;
                }
                $muteUser = findUserByID($muteUserID);
                if ($muteUser == false) {
                    $botEvent = true;
                    $bot_text = "Пользователь с id-{$muteUserID} не найден";
                    $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1]);
                    $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1];
                } else {
                    $muteList[$muteUser->id] = time();
                    $botEvent = true;
                    $bot_text = "Пользователь {$muteUser->username} заблокирован в чате";
                    $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1]);
                    $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $bot_text, "group" => 1];
                }
            } 
        }
        if ($clean_msg == '/roll') {
            $botEvent = true;
            $random = (int)rand(0, 100);
            $roll_text = "Пользователю {$thisUser->username} выпало число {$random}";
            $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $roll_text, "group" => 1]);
            $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $roll_text, "group" => 1];
        } elseif ($clean_msg == '/casino') {
            $botEvent = true;
            $random = $emoji_casino[(int)rand(0, 3)] . "-" . $emoji_casino[(int)rand(0, 3)] . "-" . $emoji_casino[(int)rand(0, 3)];
            $roll_text = "Пользователю {$thisUser->username} выпало {$random}";
            $json_bot = json_encode(["type" => "new_message", "userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $roll_text, "group" => 1]);
            $chatList[] = ["userid" => 0, "username" => "Bloodgodz.com BOT", "text" => $roll_text, "group" => 1];
        }
        foreach ($ws->connections as $key => $conn) {
            $conn->send($json);
            if ($botEvent) {
                $conn->send($json_bot);
            }
        }
    }
};
$ws->onClose = function ($connection) {
    if (!empty($connection->userid))     echo "chatWS >> " . "User-{$connection->userid} disconnected\n";
    else echo "chatWS >> " . "UserUID-$connection->uid\n";
};
\Workerman\Worker::runAll();
