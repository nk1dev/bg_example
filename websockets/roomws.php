<?
ini_set("memory_limit", "-1");
set_time_limit(0);
include("/var/www/www-root/data/www/bloodgodz.com/mongo/mainMongo.php");
$ws = new \Workerman\Worker("websocket://127.0.0.1:9519");
$ws->count = 1;
$global_uid = 0;
$lobby = [
    "RUS" => [
        "1vs1" => [],
        "2vs2" => [],
        "3vs3" => [],
        "5vs5" => [],
    ],
    "EU" => [
        "1vs1" => [],
        "2vs2" => [],
        "3vs3" => [],
        "5vs5" => []
    ]
];
$rooms = [
    /*
    [time] => [
        [users] => [20,20]
        [liders] => [20,20]
        [maps] => [de_dust2, de_mirage]
        [status] => 0
    ] 
    */];

$ws->onConnect = function ($connection) {
    global $global_uid;
    $userId = time();
    $connection->uid = $userId;
    global $ws;
    $connection->auth = true;
};
$ws->onWorkerStart = function ($task) {
    global $rooms;
    $time_interval = 2.5;
    \Workerman\Lib\Timer::add($time_interval, function () {
        global $ws, $rooms, $db;
        $delete_room = 0;
        $new_rooms_list = [];
        foreach ($rooms as $key => $value) {
            $room_id = (int)$key;
            $room = getRoomById($room_id);
            if ($room != false) $new_rooms_list[$key] = $value;
        }
        $rooms = $new_rooms_list;
    });

    \Workerman\Lib\Timer::add(15, function () {
        global $rooms, $ws, $db;
        foreach ($rooms as $key => $thisRoom) {
            $thisRoom_id = (int)$key;
            $roomInfo = getRoomById($thisRoom_id);
            if ($roomInfo == false) {
                file_put_contents("logs/rooms.log", "\nroomws [Try search room, but roomInfo == NULL] >> thisRoom[$thisRoom_id],  thisRoom:\n" . print_r($thisRoom, true) . "\nRoomInfo == NULL", FILE_APPEND);
                continue;
            }
            if ((int)$thisRoom['unix_veto'] + 180 < time() && (int)$thisRoom['veto_status'] == 0 && (int)$roomInfo->status == 0) {
                file_put_contents("logs/rooms.log", "\nroomws [Try Delete room] >> thisRoom[$thisRoom_id],  thisRoom:\n" . print_r($thisRoom, true) . "\nRoomInfo: " . print_r($roomInfo, true), FILE_APPEND);
                delete_in_db("rooms", ['id' => (int)$thisRoom_id]);
                delete_in_db("playersListInRoom", ['room_id' =>  $thisRoom_id]);
                update_in_db("users", ["active_room" => $thisRoom_id], ["active_room" => null]);
                unset($rooms[$thisRoom_id]);
                foreach ($ws->connections as $k => $c) {
                    if (!empty($c->userid) && !empty($c->roomid) && (int)$c->roomid == $thisRoom_id) {
                        $c->send(json_encode(['data' => "update_room_page"]));
                    }
                }
            } elseif ((int)$thisRoom['unix_veto'] + 30 < time() && $thisRoom['veto_status'] == 1  && (int)$roomInfo->status == 0) {
                if (empty($thisRoom['maps']) || (int)count($thisRoom['maps']) == 0) continue;
                file_put_contents("logs/rooms.log", "\nroomws [Skip veto] >> thisRoom[$thisRoom_id],  thisRoom:\n" . print_r($thisRoom, true) . "\nCapt skip: {$thisRoom['player_veto']}", FILE_APPEND);
                
                $now_capt = $thisRoom['player_veto'];
                $next_capt = 0;
                $array_capts = [(int)$roomInfo->capt1, (int)$roomInfo->capt2];
                foreach ($array_capts as $k => $capt) {
                    if ($now_capt != $capt) $next_capt = (int)$capt;
                }
                print("roomWS [{$roomInfo->id}] >> Skip Veto [$now_capt], next veto user $next_capt" . "\n");
                $maps = $thisRoom['maps'];
                $new_maps = [];
                $kicked_map = $maps[(int)array_rand($maps)];
                foreach ($maps as $key_map => $map) {
                    if ($map != $kicked_map) $new_maps[] = $map;
                }
                $rooms[$thisRoom_id]['maps'] = $new_maps;
                $rooms[$thisRoom_id]['player_veto'] = $next_capt;
                $rooms[$thisRoom_id]['unix_veto'] = time();
                if (count($new_maps) == 1) {

                    $ssh = (object)find_in_db_array("ssh", [])[0];
                    $server = (object)find_in_db_array("servers", ["ssh_id" => (int)$ssh->id, "used" => 0])[0];
                    $rooms[$thisRoom_id]['veto_status'] = 0;
                    if ((int)$roomInfo->mode_id > 1) {
                        $team1_channel =  createDiscordChannel("MATCH #{$roomInfo->id}, team 1");
                        $team2_channel =  createDiscordChannel("MATCH #{$roomInfo->id}, team 2");
                        $updateChannelUsers = find_in_db_array("playersListInRoom", ["room_id" => (int)$roomInfo->id]);
                        for ($i = 0; $i < count($updateChannelUsers); $i++) {
                            $userUpdateState = (object)$updateChannelUsers[$i];
                            if ($userUpdateState->team == 'team1') {
                                update_in_db_one("playersListInRoom", ["id" => (int)$userUpdateState->id], ['channel_id' => $team1_channel->id]);
                            } elseif ($userUpdateState->team == 'team2') {
                                update_in_db_one("playersListInRoom", ["id" => (int)$userUpdateState->id], ['channel_id' => $team2_channel->id]);
                            }
                        }
                    }
                    update_in_db("rooms", ["id" => (int)$roomInfo->id], ["status" => 1, "map" => $new_maps[0], "server_id" => (int)$server->id]);
                    update_in_db("servers", ["id" => (int)$server->id], ["used" => 1]);
                    if ((int)$server->id == 0) {
                        echo "roomWS [$thisRoom_id] >> Server not found\n";
                    } else {
                        echo "roomWS [$thisRoom_id] >> Server found id->{$server->id} | ip->{$ssh->ip} | port->{$server->port}" . "\n";
                    }
                    $ds_log = "";
                    foreach ($ws->connections as $k => $conn) {
                        if (!empty($conn->roomid) && $conn->roomid == $roomInfo->id) {
                            $conn->send(json_encode(["data" => "map_is_selected", "map" => $new_maps[0], "map_kicked" => $map]));
                            $conn->send(json_encode([
                                "data" => "server_created_for_match",
                                "ip" => $ssh->ip,
                                "port" => $server->port,
                            ]));
                            if ($roomInfo->mode_id > 1) {
                                $conn->send(json_encode([
                                    "data" => "discord_info",
                                    "url" => "https://discord.com/invite/GkqYNQ94Ca"
                                ]));
                            }
                        }
                    }
                    DiscordSendWithHook(__DISCORD_BG_WEB_HOOK, "Igor Gay LOG", "Server Running For Room:\nhttps://bloodgodz.com/room/{$roomInfo->id}\nMap: {$new_maps[0]}\nMode: " . __MODS_LIST__[$roomInfo->mode_id - 1] . "\nServer: {$ssh->ip}:{$server->port}");
                    $json_for_send = 'get5_loadmatch_url "https://bloodgodz.com/api/get5/match_generator.php?id=' . $roomInfo->id . '"';
                    rcon_exec((object)["ip" => $ssh->ip, "port" => $server->port, "rcon" => $server->rcon, "exec" => $json_for_send]);
                } else {
                    foreach ($ws->connections as $k_conn => $conn) {
                        if (!empty($conn->roomid) && (int)$conn->roomid == (int)$thisRoom_id && !empty($conn->userid)) {
                            if ((int)$conn->userid == (int)$next_capt) {
                                $conn->send(json_encode([
                                    "data" => 'start_veto_choose_map',
                                    "maps" => $new_maps,
                                ]));
                            } else {
                                $conn->send(json_encode([
                                    "data" => 'kicked_map',
                                    "map" => $kicked_map,
                                    "maps" => $new_maps,
                                    "new_veto_player" => (int)$next_capt
                                ]));
                            }
                        }
                    }
                }
            } elseif ((int)$thisRoom['unix_veto'] + 300 < time() && $thisRoom['veto_status'] == 1  && (int)$roomInfo->status == 0) {
                delete_in_db("playersListInRoom", ['room_id' => (int)$roomInfo->id]);
                update_in_db("users", ['active_room' => (int)$roomInfo->id], ['active_room' => null]);
                delete_in_db("rooms", ['id' => (int)$roomInfo->id]);
            } elseif ((int)$thisRoom['unix_veto'] + 420 < time()  && (int)$roomInfo->status == 1 && !empty($roomInfo->server_id) && (int)$roomInfo->server_id > 0) {
                $server = getServerByID((int)$roomInfo->server_id);
                $ssh = getSSHByID((int)$server->ssh_id);
                rcon_exec((object)[
                    "ip" => $ssh->ip,
                    "port" => $server->port,
                    "rcon" => $server->rcon,
                    "exec" => "get5_endmatch"
                ]);
            }
        }
    });
};

$ws->onMessage = function ($connection, $data) {
    $data = json_decode($data);
    global $ws, $rooms, $keyForSocket, $db;
    if (!empty($data->action)) {
        switch ($data->action) {
            case 'join_player':
                break;
            case 'userConnect':
                $token = $data->data;
                $roomid = (int)str_replace("/room/", "", $data->room);
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $room = getRoomById($roomid);
                if ($room == false) return;
                if (!IsuserInRoom($thisUser->id, $room->id)) return;
                if ($room->status == 3) return;
                $connection->token = $token;
                $connection->userid = $thisUser->id;
                $connection->roomid = $room->id;
                $connection->auth = true;
                $room->mode_id = (int)$room->mode_id;
                $connection->team = getPlayerInRoom($thisUser->id, $room->id)->team;
                if (!empty($rooms[$room->id]) && $rooms[$room->id]['veto_status'] == 1) {
                    $saveArrayRoom = $rooms[$room->id];
                    if ((int)$saveArrayRoom['player_veto'] == (int)$thisUser->id) {
                        $connection->send(json_encode([
                            "data" => "start_veto_choose_map",
                            "maps" => $saveArrayRoom['maps'],
                        ]));
                    } else {
                        $connection->send(json_encode([
                            "data" => "start_veto",
                            "maps" => $saveArrayRoom['maps'],
                            "user_veto" =>  (int)$saveArrayRoom['player_veto']
                        ]));
                    }
                }
                switch ($room->mode_id) {
                    case 1:
                        if ($room->status != 0) return;
                        if (empty($rooms[$room->id])) $rooms[$room->id] = ["users" => [], "capts" => [], "status" => 0, "player_veto" => 0, 'maps' => LIST_2V2_MAPS, "veto_status" => 0, "unix_veto" => time(), "maps_Array" => LIST_2V2_MAPS];
                        if (!in_array($thisUser->id, $rooms[$room->id]['users'])) $rooms[$room->id]['users'][] = $thisUser->id;
                        print("roomWS [{$room->id}] >> In room connected users: " . count($rooms[$room->id]['users']) . " | username->{$thisUser->username} | ['veto_status']: " . $rooms[$room->id]['veto_status'] . " \n");
                        print("roomWS [{$room->id}] >> User veto: " . (int)$room->capt1 . "\n");
                        if (count($rooms[$room->id]['users']) == 2 && (int)$rooms[$room->id]['veto_status'] == 0) {
                            foreach ($ws->connections as $k => $conn) {
                                if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                                    if (!empty($conn->userid) && $conn->userid == (int)$room->capt1) {
                                        $conn->send(json_encode([
                                            "data" => "start_veto_choose_map",
                                            "maps" => LIST_2V2_MAPS,
                                        ]));
                                    } else {
                                        $conn->send(json_encode([
                                            "data" => "start_veto",
                                            "maps" => LIST_2V2_MAPS,
                                            "user_veto" =>  (int)$room->capt1
                                        ]));
                                    }
                                }
                                $rooms[$room->id]['veto_status'] = 1;
                                $rooms[$room->id]['unix_veto'] = time();
                                $rooms[$room->id]['player_veto'] = (int)$room->capt1;
                            }
                        }
                        break;
                    case 2:
                        if ($room->status != 0) return;
                        if (empty($rooms[$room->id])) $rooms[$room->id] = ["users" => [], "capts" => [], "status" => 0, "player_veto" => 0, 'maps' => LIST_2V2_MAPS, "veto_status" => 0, "maps_Array" => LIST_2V2_MAPS];
                        if (!in_array($thisUser->id, $rooms[$room->id]['users'])) $rooms[$room->id]['users'][] = $thisUser->id;
                        print("roomWS [{$room->id}] >> In room connected users: " . count($rooms[$room->id]['users']) . " | username->{$thisUser->username}\n");
                        print("roomWS [{$room->id}] >> User veto: " . (int)$room->capt1 . "\n");
                        if (count($rooms[$room->id]['users']) == 4 && (int)$rooms[$room->id]['veto_status'] == 0) {
                            foreach ($ws->connections as $k => $conn) {
                                if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                                    if (!empty($conn->userid) && $conn->userid == (int)$room->capt1) {
                                        $conn->send(json_encode([
                                            "data" => "start_veto_choose_map",
                                            "maps" => LIST_2V2_MAPS,
                                        ]));
                                    } else {
                                        $conn->send(json_encode([
                                            "data" => "start_veto",
                                            "maps" => LIST_2V2_MAPS,
                                            "user_veto" =>  (int)$room->capt1
                                        ]));
                                    }
                                }
                                $rooms[$room->id]['veto_status'] = 1;
                                $rooms[$room->id]['unix_veto'] = time();
                                $rooms[$room->id]['player_veto'] = $room->capt1;
                            }
                        }
                        break;
                    case 3:
                        if ($room->status != 0) return;
                        if (empty($rooms[$room->id])) $rooms[$room->id] = ["users" => [], "capts" => [], "status" => 0, "player_veto" => 0, 'maps' => LIST_2V2_MAPS, "veto_status" => 0, "maps_Array" => LIST_2V2_MAPS];
                        if (!in_array($thisUser->id, $rooms[$room->id]['users'])) $rooms[$room->id]['users'][] = $thisUser->id;
                        print("roomWS [{$room->id}] >> In room connected users: " . count($rooms[$room->id]['users']) . " | username->{$thisUser->username}\n");
                        print("roomWS [{$room->id}] >> User veto: " . $room->capt1 . "\n");
                        if (count($rooms[$room->id]['users']) == 6 && (int)$rooms[$room->id]['veto_status'] == 0) {
                            foreach ($ws->connections as $k => $conn) {
                                if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                                    if (!empty($conn->userid) && $conn->userid == $room->capt1) {
                                        $conn->send(json_encode([
                                            "data" => "start_veto_choose_map",
                                            "maps" => LIST_2V2_MAPS,
                                        ]));
                                    } else {
                                        $conn->send(json_encode([
                                            "data" => "start_veto",
                                            "maps" => LIST_2V2_MAPS,
                                            "user_veto" => (int)$room->capt1
                                        ]));
                                    }
                                }
                                $rooms[$room->id]['veto_status'] = 1;
                                $rooms[$room->id]['unix_veto'] = time();
                                $rooms[$room->id]['player_veto'] = (int)$room->capt1;
                            }
                        }
                        break;
                    case 4:
                        if ($room->status != 0) return;
                        if (empty($rooms[$room->id])) $rooms[$room->id] = ["users" => [], "capts" => [], "status" => 0, "player_veto" => 0, 'maps' => LIST_5V5_MAPS, "veto_status" => 0, "maps_Array" => LIST_5V5_MAPS];
                        if (!in_array($thisUser->id, $rooms[$room->id]['users'])) $rooms[$room->id]['users'][] = $thisUser->id;
                        print("roomWS [{$room->id}] >> In room connected users: " . count($rooms[$room->id]['users']) . " | username->{$thisUser->username}\n");
                        print("roomWS [{$room->id}] >> User veto: " . (int)$room->capt1 . "\n");
                        if (count($rooms[$room->id]['users']) == 10 && (int)$rooms[$room->id]['veto_status'] == 0) {
                            foreach ($ws->connections as $k => $conn) {
                                if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                                    if (!empty($conn->userid) && $conn->userid == (int)$room->capt1) {
                                        $conn->send(json_encode([
                                            "data" => "start_veto_choose_map",
                                            "maps" => LIST_5V5_MAPS,
                                        ]));
                                    } else {
                                        $conn->send(json_encode([
                                            "data" => "start_veto",
                                            "maps" => LIST_5V5_MAPS,
                                            "user_veto" => (int)$room->capt1
                                        ]));
                                    }
                                }
                                $rooms[$room->id]['veto_status'] = 1;
                                $rooms[$room->id]['unix_veto'] = time();
                                $rooms[$room->id]['player_veto'] = (int)$room->capt1;
                            }
                        }
                    default:
                        break;
                }
                break;
            case 'veto_map':
                $token = $data->data;
                $map = strtolower($data->map);
                $roomid = (int)str_replace("/room/", "", $data->room);
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $room = getRoomById($roomid);
                if ($room == false) return;
                if (!in_array($map, LIST_2V2_MAPS) && !in_array($map, LIST_5V5_MAPS)) return;
                if (!IsuserInRoom($thisUser->id, $room->id)) return;
                if ((empty($rooms[$room->id])) && (int)$rooms[$room->id]['veto_status'] == 0) return;
                if ((int)$thisUser->id != (int)$rooms[$room->id]['player_veto']) return;
                $newListInRoom = [];
                $arrayCapts = [(int)$room->capt1, (int)$room->capt2];
                if (!in_array((int)$thisUser->id, $arrayCapts)) return;
                $nextUserForVeto = 0;
                foreach ($arrayCapts as $key => $value) {
                    if ((int)$value != (int)$thisUser->id) $nextUserForVeto = $value;
                }
                if ($nextUserForVeto == 0) return;
                echo "roomWS [$roomid] >> NextUserForVeto => {$nextUserForVeto}\n";
                if (count($rooms[$room->id]["maps"]) == 1) {
                    return;
                }
                foreach ($rooms[$room->id]["maps"] as $k => $m) {
                    if ($map != $m) $newListInRoom[] = $m;
                }
                echo "roomWS [$roomid] >> [Veto] Kick map => {$map} | maps counts: " . count($newListInRoom) . "\n";
                $rooms[$room->id]["maps"] = $newListInRoom;
                if (count($newListInRoom) == 1) {

                    $ssh = (object)find_in_db_array("ssh", [])[0];
                    $server = (object)find_in_db_array("servers", ["ssh_id" => (int)$ssh->id, "used" => 0])[0]; 
                    $rooms[$room->id]['veto_status'] = 0;
                    if ((int)$room->mode_id > 1) {
                        $team1_channel =  createDiscordChannel("MATCH #{$room->id}, team 1");
                        $team2_channel =  createDiscordChannel("MATCH #{$room->id}, team 2");
                        $updateChannelUsers = find_in_db_array("playersListInRoom", ["room_id" => (int)$room->id]);
                        for ($i = 0; $i < count($updateChannelUsers); $i++) {
                            $userUpdateState = (object)$updateChannelUsers[$i];
                            if ($userUpdateState->team == 'team1') {
                                update_in_db_one("playersListInRoom", ["id" => (int)$userUpdateState->id], ['channel_id' => $team1_channel->id]);
                            } elseif ($userUpdateState->team == 'team2') {
                                update_in_db_one("playersListInRoom", ["id" => (int)$userUpdateState->id], ['channel_id' => $team2_channel->id]);
                            }
                        }
                    }
                    $db->rooms->updateOne(["id" => (int)$room->id], ['$set' => ["status" => 1, "map" => $newListInRoom[0], "server_id" => (int)$server->id]]);
                    $db->servers->updateOne(["id" => (int)$server->id], ['$set' => ["used" => 1]]);
                    if ((int)$server->id == 0) {
                        echo "roomWS [$roomid] >> Server not found\n";
                    } else {
                        echo "roomWS [$roomid] >> Server found id->{$server->id} | ip->{$ssh->ip} | port->{$server->port}" . "\n";
                    }
                    $ds_log = "";
                    foreach ($ws->connections as $k => $conn) {
                        if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                            $conn->send(json_encode(["data" => "map_is_selected", "map" => $newListInRoom[0], "map_kicked" => $map]));
                            $conn->send(json_encode([
                                "data" => "server_created_for_match",
                                "ip" => $ssh->ip,
                                "port" => $server->port,
                            ]));
                            if ($room->mode_id > 1) {
                                $conn->send(json_encode([
                                    "data" => "discord_info",
                                    "url" => "https://discord.com/invite/GkqYNQ94Ca"
                                ]));
                            }
                        }
                    }
                    DiscordSendWithHook(__DISCORD_BG_WEB_HOOK, "Igor Gay LOG", "Server Running For Room:\nhttps://bloodgodz.com/room/{$room->id}\nMap: {$newListInRoom[0]}\nMode: " . __MODS_LIST__[$room->mode_id - 1] . "\nServer: {$ssh->ip}:{$server->port}");
                    $json_for_send = 'get5_loadmatch_url "https://bloodgodz.com/api/get5/match_generator.php?id=' . $room->id . '"';
                    rcon_exec((object)["ip" => $ssh->ip, "port" => $server->port, "rcon" => $server->rcon, "exec" => $json_for_send]);
                } else {
                    foreach ($ws->connections as $k => $conn) {
                        if (!empty($conn->roomid) && $conn->roomid == $room->id) {
                            $conn->send(json_encode([
                                "data" => 'kicked_map',
                                "map" => $map,
                                "maps" => $newListInRoom,
                                "new_veto_player" => (int)$nextUserForVeto
                            ]));
                            if ($nextUserForVeto != 0 && !empty($conn->roomid) && $conn->roomid == $room->id && $conn->userid == $nextUserForVeto) {
                                $conn->send(json_encode([
                                    "data" => 'start_veto_choose_map',
                                    "maps" => $newListInRoom,
                                ]));
                                $rooms[$room->id]['unix_veto'] = time();
                                $rooms[$room->id]['player_veto'] = (int)$nextUserForVeto;
                            }
                        }
                    }
                }

                break;
            case "updateStats_event_start":
                if ($keyForSocket != $data->key) return;
                print("roomWS [{$data->room}] >> " . "Event: {updateStats_event_start}\n");
                foreach ($ws->connections as $k => $conn) {
                    if (!empty($conn->userid) && count(find_in_db_array("playersListInRoom", ["user_id" => (int)$conn->userid, "room_id" => (int)$data->room])) > 0) {
                        $room = getRoomById((int)$data->room);
                        if ($room == false) return;
                        $conn->send(json_encode([
                            "data" => "updateAfterKniferound"
                        ]));
                    }
                }
                break;
            case "updateStats_event_endmatch":
                if ($keyForSocket != $data->key) return;
                print("roomWS [{$data->room}] >> " . "Event: {updateStats_event_endmatch}\n");
                break;
            case "updateStats_event_finish":
                if ($keyForSocket != $data->key) return;
                print("roomWS [{$data->room}] >> " . "Event: {updateStats_event_finish}\n");
                DiscordSendWithHook(__DISCORD_BG_WEB_HOOK, "Igor Gay LOG", "[updateStats_event_finish]\nRoom: bloodgodz.com/room/{$data->room}\nTeam Win: {$data->teamwin}");
                foreach ($ws->connections as $k => $conn) {
                    if (!empty($conn->userid) && count(find_in_db_array("playersListInRoom", ["user_id" => (int)$conn->userid, "room_id" => (int)$data->room])) > 0) {
                        $room = getRoomById((int)$data->room);
                        if ($room == false) return;
                        $conn->send(json_encode([
                            "data" => "updateAfterTeamWin",
                            "teamwin" => $data->teamwin
                        ]));
                    }
                }
                break;
            case "updateStats_new_round":
                if ($keyForSocket != $data->key) return;
                print("roomWS [{$data->room}] >> " . "Event: {updateStats_new_round}\n");
                sleep(1);
                sleep(1);
                sleep(1);
                sleep(1);
                sleep(1);
                foreach ($ws->connections as $k => $conn) {
                    if (!empty($conn->userid) && count(find_in_db_array("playersListInRoom", ["user_id" => (int)$conn->userid, "room_id" => (int)$data->room])) > 0) {
                        $room = getRoomById((int)$data->room);
                        if ($room == false) return;
                        $players = [];
                        $qr_players = $db->playersListInRoom->find(['room_id' => (int)$data->room], [
                            'sort' => ['team' => 1]
                        ])->toArray();
                        for ($i = 0; $i < count($qr_players); $i++) {
                            $player = (object)$qr_players[$i];
                            $player_kd = mathKD($player->kills, $player->deaths);
                            $stats = "{$player->kills}/{$player->deaths}/$player->assists";
                            $players[] = ["userid" => $player->user_id, "stats" => $stats, "kd" => $player_kd];
                        }
                        $conn->send(json_encode([
                            "data" => "updateStats",
                            "teamscore" => $room->team1 . "-" . $room->team2,
                            "players" => $players,
                        ]));
                    }
                }
                break;
            default:
                break;
        }
    }
};
$ws->onClose = function ($connection) {
    if (isset($connection->userid)) {
        echo "roomWS >> " . "User-{$connection->userid} disconnected\n";
    } else {
        echo "roomWS >> " . "User-null disconnected\n";
    }
};
\Workerman\Worker::runAll();
