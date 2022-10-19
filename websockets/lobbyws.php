<?
ini_set("memory_limit", "-1");
set_time_limit(0);
ini_set('log_errors', 'On');
ini_set('error_log', '/var/log/php_errors.log');
include("/var/www/www-root/data/www/bloodgodz.com/mongo/mainMongo.php");
$ws = new \Workerman\Worker("websocket://127.0.0.1:9501");
$ws->count = 1;
$global_uid = 1;
$global_custom_lobby_id = 1;
$search = [];
$search_lobby_len = [
    "1vs1" => 0,
    "2vs2" => 0,
    "3vs3" => 0,
    "5vs5" => 0,
];
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
$customlobby = [
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
$customLobbyList = [];
$friendsLobbyList = [];
$confirmsList = [
    /* example [
        "users" => [20,20],
        "region" => "RU",
        "mode" => "1vs1",
        "time" => time()
    ]*/];
$confirmedList = [
    /* example [
        "users" => [20,20],
        "region" => "RU",
        "mode" => "1vs1"
    ]*/];
$cooldownList = [];
$ws->onConnect = function ($connection) {
    global $global_uid;
    $userId = time();
    $connection->uid = $userId;
    global $ws;
    $connection->isSearch = false;
    $connection->connect_id = md5(gen_str(30));
};
$ws->onWorkerStart = function ($task) {
    \Workerman\Lib\Timer::add(2.5, function () {
        global $ws, $lobby, $confirmedList, $confirmsList, $customlobby, $customLobbyList, $friendsLobbyList, $cooldownList;
        foreach ($ws->connections as $key => $conn) {
            if (!empty($conn->userid) && !empty($conn->isAdmin) && (int)$conn->isAdmin == 1) {
                $json = json_encode($lobby);
                $json = json_decode($json);
                $arr = (object)[];
                $arr->type = 'lobby';
                $arr->lobby = $json;
                $arr = json_encode($arr);
                $conn->send($arr);
            }
        }
        foreach ($confirmsList as $key => $value) {
            $users = $value['users'];
            $time = $value['time'];
            if ((int)$time != 0 && time() > (int)$time + 15) {
                $conf_list = $confirmedList[$time];
                $users_accepted = $conf_list['users'];
                $users_not_accepted = array_diff($users, $users_accepted);
                foreach ($users_not_accepted as $k => $user_id) {
                    $user = findUserByID((int)$user_id);
                    if ($user == false) continue;
                    if ($user->group_id == 5) $cooldownList[(int)$user->id] = time();
                }
                foreach ($ws->connections as $k => $conn) {
                    if (in_array($conn->userid, $users)) {
                        $conn->send(json_encode(["action" => "lobby canceled"]));
                        $conn->NeedConfirmLobby = false;
                        $conn->isSearch = false;
                        unset($conn->lobby);
                        unset($conn->regionName);
                        unset($conn->modeName);
                        print_r($lobby);
                    }
                }
                unset($confirmedList[$time]);
                unset($confirmsList[$key]);
            }
        }
    });
};
$ws->onMessage = function ($connection, $data) {
    $data = json_decode($data);
    global $search, $lobby, $ws, $confirmsList, $confirmedList, $global_custom_lobby_id, $customlobby, $db, $friendsLobbyList, $cooldownList, $search_lobby_len;
    if (!empty($data->action)) {
        if ($data->action == 'userConnectAdminS') {
            foreach ($ws->connections as $key => $conn) {
                $conn->send(json_encode([
                    "action" => "alert",
                    "msg" => $data->alert
                ]));
            }
        }
        switch ($data->action) {
            case 'customlobbylist':
                $connection->send(json_encode($customlobby));
                break;
            case 'lobbylist':
                $connection->send(json_encode($lobby));
                break;
            case 'friendslist':
                $connection->send(json_encode($friendsLobbyList));
                break;
            case 'join_player':
                break;
            case 'userConnect':
                $token = $data->data;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $connection->token = $token;
                $connection->userid = $thisUser->id;
                $connection->NeedConfirmLobby = false;
                $path = str_replace("/lobbyconnect/", "", $data->path);
                $listonlineUsers = [];
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && $connection->uid != $conn->uid) {
                        $listonlineUsers[] = $conn->userid;
                    }
                }
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && $connection->uid != $conn->uid) {
                        $conn->send(json_encode(['action' => "users_online", "id" => (int)$connection->userid]));
                    }
                }
                $connection->send(json_encode(["action" => "users_online_connect", "users" => $listonlineUsers]));
                $connection->isCustomLobby = false;
                $isFindLobbyConnect = false;
                $isFindUserConnectID = false;
                if (strpos($data->path, "lobbyconnect")) {
                    if (strlen($path) > 20) {
                        $profile = false;
                        foreach ($ws->connections as $key => $conn) {
                            if (!empty($conn->userid) && $connection->uid != $conn->uid && !empty($conn->connect_id) && $conn->connect_id == $path) {
                                $isFindUserConnectID = true;
                                $profile = findUserByID($conn->userid);
                            }
                        }
                        if ($isFindUserConnectID) {
                            if ($profile == false) return;
                            if ((bool)$connection->isSearch == true) return;
                            if ((bool)$connection->NeedConfirmLobby == true) return;
                            $token  = $connection->token;
                            if (empty($token)) return;
                            $cooldown = (int)$cooldownList[(int)$thisUser->id];
                            if (!empty($cooldown) && (int)$cooldown + 120 > time()) {
                                $connection->send(json_encode(['action' => "cooldown", "msg" => "U have coolddown on " . ($cooldown + 120) - time(), "time" => ($cooldown + 120) - time()]));
                                return;
                            }
                            $thisUser = FindUserByToken($token);
                            $connection->connect_id = $conn->connect_id;
                            if ($thisUser == false) return;
                            if ($profile == false) return;
                            if ($data->path == '/lobby') return;

                            $usersInFriendsLobby = [];
                            $useridOwner_lobby = 0;
                            $idList = [];
                            foreach ($ws->connections as $key => $conn) {
                                if (!empty($conn->userid) && $conn->userid == $profile->id) {
                                    if (!empty($conn->friendsLobby) && (int)$conn->friendsLobby > 0) {
                                        $myFriendsLobbyid = (int)$conn->friendsLobby;
                                        $isFindLobbyConnect = true;
                                        foreach ($ws->connections as $k => $c) {
                                            if (!empty($c->userid) && !empty($c->friendsLobby) && $c->friendsLobby == $myFriendsLobbyid) {
                                                $user_id =  $c->userid;
                                                $c_user = findUserByID($user_id);
                                                if ($c_user == false) continue;
                                                $usersInFriendsLobby[] = ["id" => $c_user->id, "username" => $c_user->username, "avatar" => $c_user->avatar];
                                                $idList[] = $c_user->id;
                                            }
                                        }
                                        $connection->friendsLobby = $conn->friendsLobby;
                                        $connection->isOwnerLobby = 0;
                                        $idList[] = $thisUser->id;
                                        $usersInFriendsLobby[] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                                        $getThisLobby = $friendsLobbyList[$conn->friendsLobby];
                                        $friendsLobbyList[$conn->friendsLobby]['users'][] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                                        $friendsLobbyList[$conn->friendsLobby]['users_id'][] = ["id" => $thisUser->id];
                                        foreach ($ws->connections as $k => $c) {
                                            if (!empty($c->userid) && !empty($c->friendsLobby) && in_array($c->userid, $idList) && $conn->friendsLobby == $c->friendsLobby && $connection->userid != $c->userid) {
                                                $isOwner = $c->userid == $getThisLobby['owner'];
                                                $c->send(json_encode(['action' => 'user_lobby_connect', 'players' => $usersInFriendsLobby, 'owner_id' => $getThisLobby['owner'], 'user_id' => $c->userid, "sender" => $isOwner]));
                                            }
                                        }
                                        $connection->send(json_encode(['action' => 'user_lobby_connect', 'players' => $usersInFriendsLobby, 'owner_id' => $getThisLobby['owner'], 'user_id' => $thisUser->id, "sender" => false]));
                                    } else {
                                        $global_custom_lobby_id = $global_custom_lobby_id + 1;
                                        $conn->isOwnerLobby = 1;
                                        $idLobby = $global_custom_lobby_id;
                                        $conn->friendsLobby = $idLobby;
                                        $connection->friendsLobby = $idLobby;
                                        $connection->isOwnerLobby = 0;
                                        $usersArray = [];
                                        $usersIdArray = [$thisUser->id, $profile->id];
                                        $usersArray[] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                                        $usersArray[] = ["id" => $profile->id, "username" => $profile->username, "avatar" => $profile->avatar];
                                        $lobbyArray = ["users_id" => $usersIdArray, 'users' => $usersArray, "owner" => $profile->id, "date" => time()];
                                        $friendsLobbyList[$idLobby] = $lobbyArray;
                                        $conn->send(json_encode(['action' => 'user_lobby_connect', 'players' => $lobbyArray['users'], 'owner_id' => $lobbyArray['owner'], 'user_id' => $conn->userid, "sender" => true]));
                                        $connection->send(json_encode(['action' => 'user_lobby_connect', 'players' => $lobbyArray['users'], 'owner_id' => $lobbyArray['owner'], 'user_id' => $connection->userid, "sender" => false]));
                                    }
                                }
                            }
                        }
                    }
                    if ($isFindUserConnectID == false) {
                        $connection->send(json_encode(['action' => "connection_id_failed", "id" => $connection->connect_id]));
                    }
                }
                $connection->send(json_encode(['action' => "connection_id", "id" => $connection->connect_id, "strpos" => strpos($data->path, "lobbyconnect"), "isFindLobbyConnect" => $isFindLobbyConnect, "path" => $data->path]));
                break;
            case 'userConnectAdmin':
                $token = $data->data;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                if ($thisUser->group_id != 1) return;
                $connection->isAdmin = 1;
                $connection->userid = $thisUser->id;
                break;
            case 'search_lobby':
                if ((bool)$connection->isSearch) return $connection->send('(bool)$connection->isSearch');
                $token  = $connection->token;
                $region = strtoupper($data->region);
                $mode = strtolower($data->mode);
                if (empty($token) || empty($mode) || empty($region)) return $connection->send('empy any');
                if (!in_array($mode, __MODS_LIST__) || !in_array($region, __REGION_LIST__)) return $connection->send('empy mods');
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return $connection->send('empy user');
                if ((int)$thisUser->steam_id < 1000) {
                    $connection->send(json_encode(['action' => "steam_error", "msg" => "need", "need to link steam account"]));
                    return;
                }
                $cooldown = (int)$cooldownList[(int)$thisUser->id];
                if (!empty($cooldown) && (int)$cooldown + 120 > time()) {
                    $connection->send(json_encode(['action' => "cooldown", "msg" => "U have coolddown on " . ($cooldown + 120) - time(), "time" => ($cooldown + 120) - time()]));
                    return;
                }
                if (in_array($thisUser->id, $lobby[$region][$mode])) return;
                foreach ($confirmsList as $key => $value) {
                    $v_users = $value['users'];
                    if (in_array($thisUser->id, $v_users)) {
                        return  $connection->send(json_encode(["action" => "You are out of search"]));
                    }
                }
                $isFriendsLobbyOwner = false;
                $isFriendsLobby = false;
                if (!empty($connection->friendsLobby) && !empty($friendsLobbyList[$connection->friendsLobby]) && (int)count($friendsLobbyList[$connection->friendsLobby]) != 0) {
                    $isFriendsLobby = true;
                    if (!empty($connection->isOwnerLobby) && (int)$connection->isOwnerLobby == 1) {
                        $isFriendsLobbyOwner = true;
                    }
                } else {
                    if (!empty($connection->userid) && !in_array($connection->userid,  $lobby[$region][$mode])) {
                        $lobby[$region][$mode][] = $connection->userid;
                    }
                }
                $connection->isSearch = true;
                $connection->lobby = ["region" => $region, "mode" => $mode];
                $connection->regionName = $region;
                $connection->modeName = $mode;
                if ($isFriendsLobbyOwner) {
                    if (empty($friendsLobbyList[$connection->friendsLobby])) return;
                    $countFriendsList = (int)count($friendsLobbyList[$connection->friendsLobby]['users']);
                    if ($mode == '1vs1') return $connection->send(json_encode(["action" => "error_start_lobby_by_mode", "type" => 1]));
                    if ($mode == '2vs2' && $countFriendsList != 2) return $connection->send(json_encode(["action" => "error_start_lobby_by_mode", "type" => 2]));
                    if ($mode == '3vs3' && $countFriendsList != 3) return $connection->send(json_encode(["action" => "error_start_lobby_by_mode", "type" => 3]));
                    if ($mode == '5vs5' && $countFriendsList != 5) return $connection->send(json_encode(["action" => "error_start_lobby_by_mode", "type" => 4]));
                    $FriendsListID = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                    foreach ($ws->connections as $key => $conn) {
                        if (!empty($conn->userid) && in_array($conn->userid, $FriendsListID) && $conn->userid != $connection->userid) {
                            $conn->send(json_encode(["action" => "lobby_started_search", "region" => $region, "mode" => $mode]));
                            $conn->regionName = $region;
                            $conn->modeName =  $mode;
                        }
                    }
                }

                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && !empty($conn->regionName) && $conn->regionName == $region && $conn->modeName == $mode) {
                        $count =  count($lobby[$region][$mode]);
                        if ($isFriendsLobbyOwner) {
                            if ($mode == '2vs2') {
                                $count += (int)(1 + count($customlobby[$region][$mode]) * 2);
                            } elseif ($mode == '3vs3') {
                                $count += (int)(1 + count($customlobby[$region][$mode]) * 3);
                            } elseif ($mode == '5vs5') {
                                $count += (int)(1 + count($customlobby[$region][$mode]) * 5);
                            }
                        }
                        $conn->send(json_encode(["action" => "countPlayersInSearch", "num" => $count]));
                    }
                }

                print("lobbyWS >> [User {$thisUser->id} join]: {$thisUser->username} region[{$region}] and mode[{$mode}]\n");
                if ($isFriendsLobby == true) {
                    $customlobby_save_users = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                    $customlobby_save = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                    foreach ($customlobby[$region][$mode] as $key => $val) {
                        if ($val['customlobbyid'] == $connection->friendsLobby) {
                            return;
                        }
                    }
                    $customlobby[$region][$mode][] = ["customlobbyid" => $connection->friendsLobby, "users" => $customlobby_save_users, "owner_id" => $connection->userid];
                    switch ($mode) {
                        case '1vs1':
                            $connection->send(json_encode(["action" => "start_error_fr", "msg" => "Now start with friends dont work #1"]));
                            return;
                            break;
                        case '2vs2':
                            $num = count($lobby[$region][$mode]);
                            $customlobby_save = $customlobby_save_users;
                            if ((int)count($customlobby_save) != 2) {
                                $connection->send(json_encode(["action" => "start_error_fr", "msg" => "Now start with friends dont work #4"]));
                                return;
                            }
                            if ($num == 2) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 2);
                                $playersWithCustomLobby = $customlobby_save_users;
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            } elseif (count($customlobby[$region][$mode]) > 1) {
                                $allcustomlobby = array_slice($customlobby[$region][$mode], 0, 2);
                                echo "allcustomlobby sliced\n";
                                $customlobby[$region][$mode] = array_slice($customlobby[$region][$mode], 2);
                                echo "customlobby sliced\n";
                                $customlobby_1 = $friendsLobbyList[(int)$allcustomlobby[0]['customlobbyid']]['users_id'];
                                $customlobby_2 = $friendsLobbyList[(int)$allcustomlobby[1]['customlobbyid']]['users_id'];
                                /* echo "customlobby_1:\n";
                                print_r($customlobby_1);
                                echo "\ncustomlobby_2:\n";
                                print_r($customlobby_2);*/
                                $allplayers = array_merge($customlobby_1, $customlobby_2);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                            } else {
                                $customlobby[$region][$mode][] = ["customlobbyid" => $connection->friendsLobby, "users" => $customlobby_save, "owner_id" => $connection->userid];
                            }
                            break;
                        case '3vs3':
                            $num = count($lobby[$region][$mode]);
                            $customlobby_save = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                            if ((int)count($customlobby_save) != 3) {
                                $conn->send(json_encode(["action" => "start_error_fr", "msg" => "Now start with friends dont work #7"]));
                                return;
                            }
                            if ($num == 3) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 3);
                                $playersWithCustomLobby = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            } elseif (count($customlobby[$region][$mode]) > 1) {
                                $allcustomlobby = array_slice($customlobby[$region][$mode], 0, 2);
                                $customlobby[$region][$mode] = array_slice($customlobby[$region][$mode], 2);
                                $customlobby_1 = $friendsLobbyList[(int)$allcustomlobby[0]['customlobbyid']]['users_id'];
                                $customlobby_2 = $friendsLobbyList[(int)$allcustomlobby[1]['customlobbyid']]['users_id'];
                                $allplayers = array_merge($customlobby_1, $customlobby_2);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                            } else {
                                foreach ($customlobby[$region][$mode] as $key => $val) {
                                    if ($val['customlobbyid'] == $connection->friendsLobby) {
                                        return;
                                    }
                                }
                                $customlobby[$region][$mode][] = ["customlobbyid" => $connection->friendsLobby, "users" => $customlobby_save, "owner_id" => $connection->userid];
                            }
                            break;
                        case '5vs5':
                            $num = count($lobby[$region][$mode]);
                            $customlobby_save = $friendsLobbyList[$connection->friendsLobby]['users'];
                            if ((int)count($customlobby_save) != 5) {
                                $conn->send(json_encode(["action" => "start_error_fr", "msg" => "Now start with friends dont work #9"]));
                                return;
                            }
                            if ($num == 5) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 5);
                                $playersWithCustomLobby = $friendsLobbyList[$connection->friendsLobby]['users_id'];
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $connection->friendsLobby];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            } elseif (count($customlobby[$region][$mode]) > 1) {
                                $allcustomlobby = array_slice($customlobby[$region][$mode], 0, 2);
                                $customlobby[$region][$mode] = array_slice($customlobby[$region][$mode], 2);
                                $customlobby_1 = $friendsLobbyList[(int)$allcustomlobby[0]['customlobbyid']]['users_id'];
                                $customlobby_2 = $friendsLobbyList[(int)$allcustomlobby[1]['customlobbyid']]['users_id'];
                                $allplayers = array_merge($customlobby_1, $customlobby_2);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => (int)$allcustomlobby[0]['customlobbyid'], "customlobbyid_2" => (int)$allcustomlobby[1]['customlobbyid']];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                            } else {
                                foreach ($customlobby[$region][$mode] as $key => $val) {
                                    if ($val['customlobbyid'] == $connection->friendsLobby) {
                                        return;
                                    }
                                }
                                $customlobby[$region][$mode][] = ["customlobbyid" => $connection->friendsLobby, "users" => $customlobby_save, "owner_id" => $connection->userid];
                            }
                            break;
                        default:
                            return false;
                            break;
                    }
                } else {
                    switch ($mode) {
                        case '1vs1':
                            $num = count($lobby[$region][$mode]);
                            if ($num == 2) {
                                $playersArr = array_slice($lobby[$region][$mode], 0, 2);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $playersArr, "region" => $region, "mode" => $mode];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $playersArr)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArr)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            break;
                        case '2vs2':
                            $num = count($lobby[$region][$mode]);
                            if ($num == 4) {
                                $playersArr = array_slice($lobby[$region][$mode], 0, 4);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $playersArr, "region" => $region, "mode" => $mode];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $playersArr)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArr)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            if ($num == 2 && count($customlobby[$region][$mode]) > 0) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 2);
                                $customLobby_save = $customlobby[$region][$mode][0];
                                $playersWithCustomLobby = $customLobby_save['users'];
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $custom_lb_Id = $customLobby_save['customlobbyid'];
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                        case '3vs3':
                            $num = count($lobby[$region][$mode]);
                            if ($num == 6) {
                                $playersArr = array_slice($lobby[$region][$mode], 0, 6);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $playersArr, "region" => $region, "mode" => $mode];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $playersArr)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArr)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            if ($num == 3 && count($customlobby[$region][$mode]) > 0) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 3);
                                $customLobby_save = $customlobby[$region][$mode][0];
                                $playersWithCustomLobby = $customLobby_save['users'];
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $custom_lb_Id = $customLobby_save['customlobbyid'];
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            break;
                        case '5vs5':
                            $num = count($lobby[$region][$mode]);
                            if ($num == 10) {
                                $playersArr = array_slice($lobby[$region][$mode], 0, 10);
                                $timeConfirm = time();
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $playersArr, "region" => $region, "mode" => $mode];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode];
                                foreach ($ws->connections as $key => $conn) {
                                    if (in_array($conn->userid, $playersArr)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                    }
                                }
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArr)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            if ($num == 5 && count($customlobby[$region][$mode]) > 0) {
                                $playersArrSearch = array_slice($lobby[$region][$mode], 0, 5);
                                $customLobby_save = $customlobby[$region][$mode][0];
                                $playersWithCustomLobby = $customLobby_save['users'];
                                $allplayers = array_merge($playersArrSearch, $playersWithCustomLobby);
                                $timeConfirm = time();
                                $custom_lb_Id = $customLobby_save['customlobbyid'];
                                $confirmsList[] = ["time" => $timeConfirm, "users" => $allplayers, "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $confirmedList[$timeConfirm] = ["users" => [], "region" => $region, "mode" => $mode, "customlobbyid" => $custom_lb_Id];
                                $listUserInSearch = [];
                                foreach ($lobby[$region][$mode] as $k => $v) {
                                    if (!in_array($v, $playersArrSearch)) {
                                        $listUserInSearch[] = $v;
                                    }
                                }
                                foreach ($ws->connections as $key => $conn) {
                                    if (!empty($conn->userid) && in_array($conn->userid, $allplayers)) {
                                        $conn->send(json_encode(["action" => "confirm lobby"]));
                                        echo "userid->{$conn->userid} sended confirm lobby event\n";
                                        $ws->connections[$key]->NeedConfirmLobby = true;
                                        $conn->NeedConfirmLobby = true;
                                        $conn->isSearch = true;
                                    }
                                }
                                echo "ALL PLAYERS BEFORE CONFIRMS\n";
                                print_r($allplayers);
                                echo "\nALL PLAYERS BEFORE CONFIRMS END\n";
                                $lobby[$region][$mode] =  $listUserInSearch;
                            }
                            break;
                        default:
                            return false;
                            break;
                    }
                }
                $customlobby[$region][$mode] = array_unique($customlobby[$region][$mode]);
                $search_lobby_len_mode =  remath_Len_search_players($customlobby[$region][$mode], $lobby[$region][$mode], $mode);
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && !empty($conn->modeName) && $conn->modeName == $mode) {
                        $conn->send(json_encode(["action"=> "countPlayersInSearch", "num"=> $search_lobby_len_mode]));
                    }
                }
              
                break;
            case "search_lobby_with_frinds":
                if ((bool)$connection->isSearch) return;
                $token  = $connection->token;
                $region = strtoupper($data->region);
                $mode = strtolower($data->mode);
                if (empty($token) || empty($mode) || empty($region)) return;
                if (!in_array($mode, __MODS_LIST__) || !in_array($region, __REGION_LIST__)) return;
                break;
            case 'exit_in_search':
                if ((bool)$connection->isSearch == false) return;
                $token  = $connection->token;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                if (empty($connection->regionName) || empty($connection->modeName)) return;
                $connection->isSearch = false;
                $region = $connection->regionName;
                $mode = $connection->modeName;
                $isCustomLobbyOwner = false;
                $isCustomLobby = false;
                $CustomLobbyId = 0;
                if (!empty($connection->friendsLobby) && !empty($customLobbyList[$connection->friendsLobby])) {
                    if ($customLobbyList[$connection->friendsLobby]['owner'] == $connection->userid) {
                        $isCustomLobbyOwner = true;
                        $isCustomLobby = true;
                        $CustomLobbyId = (int)$connection->friendsLobby;
                    }
                }
                if ($isCustomLobby) {
                    $connection->send(json_encode(["action" => "You are out of search"]));
                    $newCustomLobby = [];
                    foreach ($customlobby[$region][$mode] as $key => $val) {
                        if ($val['customlobbyid'] != $CustomLobbyId) {
                            $newCustomLobby[] = $val;
                        }
                    }
                    $customlobby[$region][$mode] = $newCustomLobby;
                    foreach ($ws->connections as $key => $conn) {
                        if (!empty($conn->userid) && in_array($conn->userid, $friendsLobbyList[$connection->friendsLobby]['users_id']) && $conn->userid != $connection->userid) {
                            $conn->send(json_encode(["action" => "exit_lobby_by_owner_lobby"]));
                            unset($ws->connections[$key]->modeName);
                            unset($ws->connections[$key]->regionName);
                            $ws->connections[$key]->NeedConfirmLobby = false;
                            $ws->connections[$key]->isSearch = false;
                        }
                    }
                } else {
                    $connection->send(json_encode(["action" => "You are out of search"]));
                    unset($connection->regionName);
                    unset($connection->modeName);
                    $newArrayLobby = [];
                    foreach ($ws->connections as $key => $conn) {
                        if (!empty($conn->userid) && !empty($connection->userid) && !empty($region) && !empty($mode) && !empty($conn->regionName) && $conn->modeName && $region == $conn->regionName && $mode == $conn->modeName && in_array($conn->userid, $lobby[$region][$mode]) && $conn->userid != $connection->userid) {
                            $newArrayLobby[] = $conn->userid;
                        }
                    }
                    $connection->isSearch = false;
                    $lobby[$region][$mode] = $newArrayLobby;
                    $connection->NeedConfirmLobby = false;
                }

    

                break;
            case 'inviteUser':
                $profile_id = (int)$data->userid;
                if ($profile_id == 0) return;
                if ((bool)$connection->isSearch == true) return;
                if ((bool)$connection->NeedConfirmLobby == true) return;
                $token  = $connection->token;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $profile = FindUserByID($profile_id);
                if ($profile == false) return;
                $isSendInvite = false;
                if ((int)$profile->steam_id < 5000) return $connection->send(json_encode(["action" => "not connected steam", "username" => $profile->username]));
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && $conn->userid == $profile->id) {
                        if (!empty($conn->friendsLobby) && (int)$conn->friendsLobby > 0) {
                            return $connection->send(json_encode(["action" => "send_invite_failed_reason_already_in_lobby", "username" => $profile->username, '$conn->customlobbyid' => $conn->customlobbyid]));
                        } else {
                            $conn->send(json_encode(["action" => "invite_by_user", "id" => (int)$thisUser->id, "username" => $thisUser->username]));
                            $isSendInvite = true;
                        }
                    }
                }
                if ($isSendInvite)  $connection->send(json_encode(["action" => "sended_invite", "username" => $profile->username]));
                else  $connection->send(json_encode(["action" => "send_invite_failed", "username" => $profile->username]));
                break;
                
            case "kick_user_from_lobby":
                $profile_id = (int)$data->userid;
                if ($profile_id == 0) return;
                $token  = $connection->token;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $profile = FindUserByID($profile_id);
                if ($profile == false) return;
                $isKicked = false;
                $username = $profile->username;
                if (empty($connection->friendsLobby) || (int)$connection->friendsLobby == 0) return;
                if (empty($connection->isOwnerLobby) && (int)$connection->isOwnerLobby != 1) return;
                $saveLobbyId = $connection->friendsLobby;
                $saveLobby = $friendsLobbyList[$saveLobbyId];
                if (empty($saveLobby) || (int)count($saveLobby) == 0) return;
                if ((int)$saveLobby['owner'] == (int)$profile_id) return;
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && !empty($conn->friendsLobby) && (int)$conn->userid == (int)$profile->id) {
                        $conn->send(json_encode(["action" => "reload_page"]));
                        $isKicked = true;
                    }
                }
                if ($isKicked) {
                    $connection->send(json_encode(["action" => "kickeduserfromlobby", "kicked" => true, "username" => $username]));
                } else {
                    $connection->send(json_encode(["action" => "kickeduserfromlobby", "kicked" => false, "username" => $username]));
                }
                break;
            case 'acceptRequestInLobby':
                $profile_id = (int)$data->userid;
                if ($profile_id == 0) return;
                if ((bool)$connection->isSearch == true) return;
                if ((bool)$connection->NeedConfirmLobby == true) return;
                $token  = $connection->token;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $profile = FindUserByID($profile_id);
                if ($profile == false) return;
                if ($data->path != '/lobby') return;
                $usersInFriendsLobby = [];
                $useridOwner_lobby = 0;
                $idList = [];
                $cooldown = (int)$cooldownList[(int)$thisUser->id];
                if (!empty($cooldown) && (int)$cooldown + 120 > time()) {
                    $connection->send(json_encode(['action' => "cooldown", "msg" => "U have coolddown on " . ($cooldown + 120) - time(), "time" => ($cooldown + 120) - time()]));
                    return;
                }
                if (!empty($connection->friendsLobby) ) return;
                foreach ($ws->connections as $key => $conn) {
                    if (!empty($conn->userid) && $conn->userid == $profile->id) {
                        if (!empty($conn->friendsLobby) && (int)$conn->friendsLobby > 0) {
                            $myFriendsLobbyid = (int)$conn->friendsLobby;
                            foreach ($ws->connections as $k => $c) {
                                if (!empty($c->userid) && !empty($c->friendsLobby) && $c->friendsLobby == $myFriendsLobbyid) {
                                    $user_id =  $c->userid;
                                    $c_user = findUserByID($user_id);
                                    if ($c_user == false) continue;
                                    $usersInFriendsLobby[] = ["id" => $c_user->id, "username" => $c_user->username, "avatar" => $c_user->avatar];
                                    $idList[] = $c_user->id;
                                }
                            }
                            $connection->friendsLobby = $conn->friendsLobby;
                            $connection->isOwnerLobby = 0;
                            $idList[] = $thisUser->id;
                            $usersInFriendsLobby[] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                            $usersInFriendsLobby = array_unique($usersInFriendsLobby);
                            $getThisLobby = $friendsLobbyList[$conn->friendsLobby];
                            $friendsLobbyList[$conn->friendsLobby]['users'][] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                            $friendsLobbyList[$conn->friendsLobby]['users_id'][] = ["id" => $thisUser->id];
                            foreach ($ws->connections as $k => $c) {
                                if (!empty($c->userid) && !empty($c->friendsLobby) && in_array($c->userid, $idList) && $conn->friendsLobby == $c->friendsLobby && $connection->userid != $c->userid) {
                                    $isOwner = $c->userid == $getThisLobby['owner'];
                                    $c->send(json_encode(['action' => 'user_lobby_connect', 'players' => $usersInFriendsLobby, 'owner_id' => $getThisLobby['owner'], 'user_id' => $c->userid, "sender" => $isOwner]));
                                }
                            }
                            $connection->send(json_encode(['action' => 'user_lobby_connect', 'players' => $usersInFriendsLobby, 'owner_id' => $getThisLobby['owner'], 'user_id' => $thisUser->id, "sender" => false]));
                        } else {
                            $global_custom_lobby_id = $global_custom_lobby_id + 1;
                            $conn->isOwnerLobby = 1;
                            $idLobby = $global_custom_lobby_id;
                            $conn->friendsLobby = $idLobby;
                            $connection->friendsLobby = $idLobby;
                            $connection->isOwnerLobby = 0;
                            $usersArray = [];
                            $usersIdArray = [$thisUser->id, $profile->id];
                            $usersArray[] = ["id" => $thisUser->id, "username" => $thisUser->username, "avatar" => $thisUser->avatar];
                            $usersArray[] = ["id" => $profile->id, "username" => $profile->username, "avatar" => $profile->avatar];
                            $lobbyArray = ["users_id" => $usersIdArray, 'users' => $usersArray, "owner" => $profile->id, "date" => time()];
                            $friendsLobbyList[$idLobby] = $lobbyArray;
                            $conn->send(json_encode(['action' => 'user_lobby_connect', 'players' => $lobbyArray['users'], 'owner_id' => $lobbyArray['owner'], 'user_id' => $conn->userid, "sender" => true]));
                            $connection->send(json_encode(['action' => 'user_lobby_connect', 'players' => $lobbyArray['users'], 'owner_id' => $lobbyArray['owner'], 'user_id' => $connection->userid, "sender" => false]));
                        }
                    }
                }
                break;
            case 'confirm_lobby':
                if ((bool)$connection->isSearch == false) return;
                if ((bool)$connection->NeedConfirmLobby == false) return;
                $token  = $connection->token;
                if (empty($token)) return;
                $thisUser = FindUserByToken($token);
                if ($thisUser == false) return;
                $idByTime = 0;
                foreach ($confirmsList as $k => $v) {
                    if (in_array($thisUser->id, $v["users"])) {
                        if ($v["time"] + 15 < time()) {
                            return $connection->send("Maximum confirmation time 15 seconds");
                        }
                        $confirmedList[$v["time"]]['users'][] = $thisUser->id;
                        $idByTime = (int)$v["time"];
                    }
                }
                print("lobbyWS >> [event confirm_lobby] User[{$thisUser->id}] => {$thisUser->username}, id => [$idByTime]" . "\n");
                $modeName = $confirmedList[$idByTime]["mode"];
                $requiredUsers = 2;
                $region = getRegionsObject($confirmedList[$idByTime]["region"]);
                $mode =  getModesObject($confirmedList[$idByTime]["mode"]);
                switch ($modeName) {
                    case __MODS_LIST__[0]:
                        $requiredUsers = 2;
                        break;
                    case __MODS_LIST__[1]:
                        $requiredUsers = 4;
                        break;
                    case __MODS_LIST__[2]:
                        $requiredUsers = 6;
                        break;
                    case __MODS_LIST__[3]:
                        $requiredUsers = 10;
                        break;
                    default:
                        break;
                }
                foreach ($ws->connections as $key => $conn) {
                    foreach ($confirmsList as $k => $v) {
                        if (in_array($conn->userid, $v['users'])) {
                            $conn->send(json_encode([
                                "action" => "new_confirmed"
                            ]));
                        }
                    }
                }

                print("lobbyWS >> confirmed count: " . count($confirmedList[$idByTime]["users"]) . "\n");

                if ($idByTime != 0 && count(array_unique($confirmedList[$idByTime]["users"])) == $requiredUsers) {
                    print("lobbyWS >> All users confirmed [$idByTime] \n");
                    file_put_contents("logs/lobby.log", "\nlobbyws >> confirmedList: \n" . print_r($confirmedList[$idByTime], true), FILE_APPEND);
                    $new_id_room = get_new_id_for_db("rooms");
                    $date = date("Y-m-d H:i:s");
                    $insert = insert_in_db_one("rooms", ["id" => (int)$new_id_room, "region_id" => (int)$region->id, "mode_id" => (int)$mode->id, "status" => 0, "team1" => 0, "team2" => 0, "map" => NULL, "server_id" => NULL, "date" => $date]);

                    $insert = $db->rooms->findOne(['_id' => new MongoDB\BSON\ObjectID($insert->uid)]);
                    $insert = (int)$insert->id;
                    $usersForTeams = array_values(array_unique($confirmedList[$idByTime]["users"]));
                    file_put_contents("logs/lobby.log", "\nlobbyws >> insert_room_id: $insert | array_unique(usersForTeams) => \n" . print_r($usersForTeams, true), FILE_APPEND);
                    if (count($usersForTeams) != $requiredUsers) return;
                    for ($i = 0; $i < count($usersForTeams); $i++) {
                        $db->users->updateOne(["id" => (int)$usersForTeams[$i]], ['$set' => ["active_room" => (int)$insert]]);
                    }
                    if (!empty($confirmedList[$idByTime]['customlobbyid']) && $friendsLobbyList[(int)$confirmedList[$idByTime]['customlobbyid']]) {
                        $custom_lobby = $friendsLobbyList[(int)$confirmedList[$idByTime]['customlobbyid']];
                        $custom_lobby_2 = [];
                        $usersCustomTeam1 = $custom_lobby['users_id'];
                        $usersCustomTeam2 = [];
                        if (!empty($confirmedList[$idByTime]['customlobbyid_2']) && (int)$confirmedList[$idByTime]['customlobbyid_2'] > 0) {
                            $custom_lobby_2 = $friendsLobbyList[(int)$confirmedList[$idByTime]['customlobbyid_2']];
                            $usersCustomTeam2 = $custom_lobby_2['users_id'];
                            $usersCustomTeam1 =  $friendsLobbyList[(int)$confirmedList[$idByTime]['customlobbyid']]['users_id'];
                        } else {
                            for ($i = 0; $i < count($usersForTeams); $i++) {
                                if (!in_array($usersForTeams[$i], $usersCustomTeam1)) {
                                    $usersCustomTeam2[] = (int)$usersForTeams[$i];
                                }
                            }
                        }
                        file_put_contents("logs/lobby.log", "\nlobbyws [room->{$insert}] >> created customlobbyid(1) | usersCustomTeam1:\n" . print_r($usersCustomTeam1, true) . "\nusersCustomTeam2: \n" . print_r($usersCustomTeam2, true), FILE_APPEND);
                        $capt1 = $usersCustomTeam1[0];
                        $capt2 = $usersCustomTeam2[0];
                        switch ((int)$mode->id) {
                            case 2:
                                $usersCustomTeam1 = array_slice($usersCustomTeam1, 0, 2);
                                $usersCustomTeam2 = array_slice($usersCustomTeam2, 0, 2);
                                break;
                            case 3:
                                $usersCustomTeam1 = array_slice($usersCustomTeam1, 0, 3);
                                $usersCustomTeam2 = array_slice($usersCustomTeam2, 0, 3);
                                break;
                            case 4:
                                $usersCustomTeam1 = array_slice($usersCustomTeam1, 0, 5);
                                $usersCustomTeam2 = array_slice($usersCustomTeam2, 0, 5);
                                break;
                        }
                        update_in_db("rooms", ["id" => (int)$insert], ['capt1' => (int)$capt1, "capt2" => $capt2]);
                        for ($i = 0; $i < count($usersCustomTeam1); $i++) {
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            print("\nnew_id_list: {$new_id_list}\n");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$usersCustomTeam1[$i], "team" => "team1", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }
                        for ($i = 0; $i < count($usersCustomTeam2); $i++) {
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$usersCustomTeam2[$i], "team" => "team2", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }

                        foreach ($ws->connections as $key => $conn) {
                            if (!empty($conn->userid) && in_array($conn->userid, $usersForTeams)) {
                                $conn->send(json_encode(["msg" => "created", "roomid" => "$insert", "action" => "join_in_lobby"]));
                                $ws->connections[$key]->NeedConfirmLobby = false;
                            }
                        }
                    } else {
                        file_put_contents("logs/lobby.log", "\nlobbyws [room->{$insert}] >> created randomLobby | :\n" . print_r($usersForTeams, true), FILE_APPEND);
                        $capt1 = 0;
                        $capt2 = 0;
                        $userCustomTeam_re_1 = [];
                        $userCustomTeam_re_2 = [];
                        switch ((int)$mode->id) {
                            case 1:
                                $userCustomTeam_re_1 = [$usersForTeams[0]];
                                $userCustomTeam_re_2 = [$usersForTeams[1]];
                                break;
                            case 2:
                                $userCustomTeam_re_1 = [$usersForTeams[0], $usersForTeams[1]];
                                $userCustomTeam_re_2 = [$usersForTeams[2], $usersForTeams[3]];
                                break;
                            case 3:
                                $userCustomTeam_re_1 = [$usersForTeams[0], $usersForTeams[1], $usersForTeams[2]];
                                $userCustomTeam_re_2 = [$usersForTeams[3], $usersForTeams[4], $usersForTeams[5]];
                                break;
                            case 4:
                                $userCustomTeam_re_1 = [$usersForTeams[0], $usersForTeams[1], $usersForTeams[2], $usersForTeams[3], $usersForTeams[4]];
                                $userCustomTeam_re_2 = [$usersForTeams[5], $usersForTeams[6], $usersForTeams[7], $usersForTeams[8], $usersForTeams[9]];
                                break;
                        }
                        for ($i = 0; $i < count($userCustomTeam_re_1); $i++) {
                            if ($i == 0) $capt1 = $userCustomTeam_re_1[$i];
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$userCustomTeam_re_1[$i], "team" => "team1", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }
                        for ($i = 0; $i < count($userCustomTeam_re_2); $i++) {
                            if ($i == 0) $capt2 = $userCustomTeam_re_2[$i];
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$userCustomTeam_re_2[$i], "team" => "team2", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }
                        /*    for ($i = 0; $i < count($usersForTeams) / 2; $i++) {
                            if ($i == 0) $capt1 = $usersForTeams[$i];
                            print('count($usersForTeams) / 2, id=> ' . (int)$usersForTeams[$i] . "\n");
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$usersForTeams[$i], "team" => "team1", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }
                        for ($i = count($usersForTeams) / 2; $i < count($usersForTeams); $i++) {
                            if ($i = count($usersForTeams) / 2) {
                                $capt2 = $usersForTeams[$i];
                            }
                            print(' count($usersForTeams), id=> ' . (int)$usersForTeams[$i] . "\n");
                            $date = date("Y-m-d H:i:s");
                            $new_id_list = (int)get_new_id_for_db("playersListInRoom");
                            insert_in_db_one("playersListInRoom", ["id" => $new_id_list, "room_id" => (int)$insert, "user_id" => (int)$usersForTeams[$i], "team" => "team2", "date" => $date, "kills" => 0, "deaths" => 0, "assists" => 0, "hs" => 0]);
                        }*/
                        update_in_db("rooms", ["id" => (int)$insert], ['capt1' => (int)$capt1, "capt2" => $capt2]);
                        foreach ($ws->connections as $key => $conn) {
                            if (in_array($conn->userid, $confirmedList[$idByTime]["users"])) {
                                $conn->send(json_encode(["msg" => "created", "roomid" => "$insert", "action" => "join_in_lobby"]));
                                $ws->connections[$key]->NeedConfirmLobby = false;
                            }
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
};
$ws->onClose = function ($connection) {
    global $lobby, $ws, $customLobbyList, $friendsLobbyList, $customlobby, $search_lobby_len;
    if (!empty($connection->userid)) echo "lobbyWS >> " . "User-{$connection->userid} disconnected\n";
    else echo "lobbyWS >> " . "UserUID-$connection->uid\n";
    if (!empty($connection->userid) && $connection->isSearch && empty($connection->friendsLobby)) {
        $new_lobby = [];
        $lb = $lobby[$connection->regionName][$connection->modeName];
        foreach ($lb as $key => $value) {
            if ($value != $connection->userid) $new_lobby[] = $value;
        }
        $lobby[$connection->regionName][$connection->modeName] = $new_lobby;
    }
    if (!empty($connection->userid) && !empty($connection->friendsLobby)) {
        unset($connection->customlobbyid);
    }
    if (!empty($connection->userid) && !empty($connection->friendsLobby)) {
        foreach ($ws->connections as $key => $conn) {
            if (!empty($conn->customlobbyid) && $conn->customlobbyid == $connection->friendsLobby) {
                $conn->send(json_encode(["action" => "customlobby_user_close_connect", "userid" => $connection->userid]));
            }
        }
    }
    if (!empty($connection->userid) && !empty($connection->friendsLobby)) {

        $lobbyID = (int)$connection->friendsLobby;
        if ($lobbyID == 0 || empty($friendsLobbyList[$lobbyID]) || (int)count($friendsLobbyList[$lobbyID]) == 0) return;
        $users = $friendsLobbyList[$lobbyID]['users'];
        $usersIdList = [];
        foreach ($users as $key => $value) {
            if ((int)$value['id'] != $connection->userid) $usersIdList[] = (int)$value['id'];
        }
        if (!empty($connection->isOwnerLobby) && $connection->isOwnerLobby == 1) {
            foreach ($ws->connections as $key => $conn) {
                if (!empty($conn->userid) && in_array($conn->userid, $usersIdList)) {
                    $conn->send(json_encode(['action' => 'reload_page']));
                }
            }
            if (!empty($connection->regionName) && !empty($connection->modeName)) {
                $region =  $connection->regionName;
                $mode = $connection->modeName;
                $saveCustomLobby = $customlobby[$region][$mode];
                $newCustomLobby = [];
                foreach ($saveCustomLobby as $key => $value) {
                    if ($value['owner_id'] != $connection->userid) {
                        $newCustomLobby[] = $value;
                    }
                }
                $customlobby[$region][$mode] = $newCustomLobby;
            }
            unset($friendsLobbyList[$lobbyID]);
        } else {
            $newUsersList = [];
            $newIDList = [];
            foreach ($users as $key => $value) {
                if ((int)$value['id'] != $connection->userid) {
                    $newUsersList[] = $value;
                    $newIDList[] = $value['id'];
                }
            }
            $friendsLobbyList[$lobbyID]['users_id'] = $newIDList;
            $friendsLobbyList[$lobbyID]['users'] = $newUsersList;
            foreach ($ws->connections as $k => $c) {
                if (!empty($c->userid) && !empty($c->friendsLobby) && in_array($c->userid, $newIDList) && $connection->friendsLobby == $c->friendsLobby && $connection->userid != $c->userid) {
                    if ((int)count($newUsersList) < 2) {
                        unset($friendsLobbyList[$lobbyID]);
                        $c->send(json_encode(['action' => 'reload_page']));
                    } else {
                        $isOwner = $c->userid == $friendsLobbyList[$lobbyID]['owner'];
                        $c->send(json_encode(['action' => 'user_lobby_connect', 'players' => $newUsersList, 'owner_id' => $friendsLobbyList[$lobbyID]['owner'], 'user_id' => $c->userid, "sender" => $isOwner]));
                    }
                }
            }
        }
    }
    if (!empty($connection->userid)) {
        $refresh_users_page = [];
        if (!empty($connection->friendsLobby) && !empty($friendsLobbyList[$connection->friendsLobby]) && !empty($friendsLobbyList[$connection->friendsLobby]['owner'])) {
            $customLobby_save = $friendsLobbyList[$connection->friendsLobby];
            $Owner_ID = (int)$customLobby_save['owner'];
            if ($Owner_ID == $connection->userid) {
                $refresh_users_page = $customLobby_save['users'];
                $newCustomLobby  = [];
                foreach ($friendsLobbyList as $key => $val) {
                    if ($key != $connection->friendsLobby) $newCustomLobby[] = $val;
                }
                $friendsLobbyList = $newCustomLobby;
            }
        }
        foreach ($ws->connections as $key => $conn) {
            if (!empty($conn->userid) && $conn->userid != $connection->userid) {
                $conn->send(json_encode(["action" => "users_online_close", "id" => $connection->userid]));
            }
            if (count($refresh_users_page) > 0 && in_array($conn->userid, $refresh_users_page) && $connection->userid != $conn->userid) {
                $conn->send(json_encode(['action' => "reload_page"]));
            }
        }
        if ( !empty($connection->modeName)) {
            $mode = $connection->modeName;
            $region = $connection->regionName;
            $customlobby[$region][$mode] = array_unique($customlobby[$region][$mode]);
            $modeLen =  remath_Len_search_players($customlobby[$region][$mode], $lobby[$region][$mode], $mode);
            foreach ($ws->connections as $key => $conn) {
                if (!empty($conn->userid) && !empty($conn->modeName) && $conn->modeName == $mode) {
                    $conn->send(json_encode(["action"=> "countPlayersInSearch", "num"=> $modeLen]));
                }
            }
        }
    }
};
\Workerman\Worker::runAll();