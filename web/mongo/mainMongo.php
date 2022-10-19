<?

ini_set('session.gc_maxlifetime', 3600 * 24 * 30);
ini_set('session.cookie_lifetime', 3600 * 24 * 30);
session_start();

require 'vendor/autoload.php';
$client = new MongoDB\Client("");
$db = $client->bg;
define("__DISCORD_BG_WEB_HOOK", "https://discord.com/");
define("__HOST__", $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_HOST']);
define("__REGION_LIST__", ["ALL", "RUS", "EU"]);
define("__MODS_LIST__", ["1vs1", "2vs2", "3vs3", "5vs5"]);
define("LIST_2V2_MAPS", [
    "de_vertigo",
    "de_inferno",
    "de_train",
    "gd_rialto_bg",
    "de_cbble",
    "de_overpass",
    "de_shortdust",
    "de_shortnuke",
    "de_bank",
    "de_lake",
    "de_stmarc"
]);
define("LIST_5V5_MAPS", [
    "de_mirage",
    "de_inferno",
    "de_overpass",
    "de_train",
    "de_dust2",
    "de_office",
    "de_nuke",
    "de_cache_bg",
    "de_italy"
]);
define("LIST_DECLINE_USERNAME", [
    "owner", "support", "admin", "creater"
]);
define("PAYOK_SHOP_ID", 2351);
define("PAYOK_SECRET_KEY", "");
define("URL_DEF_AVATAR", "/files/images/icons/persone.png");
$configIncludes = (object)[
    "user_auth" => "user_auth_gG3.js",
    "auth" => "auth_123g.js",
];
$UPDATE_KEY = md5("");
$staff_access_groups = [1, 2, 3];
$RANK_LIST = [
    1 => 0,
    2 => 801,
    3 => 951,
    4 => 1101,
    5 => 1251,
    6 => 1401,
    7 => 1551,
    8 => 1701,
    9 => 1851,
    10 => 2001
];

function insert_in_db_one($name_db, $array_insert)
{
    global $db;
    $result = $db->$name_db->insertOne(
        $array_insert
    );
    $result->uid = $result->getInsertedId();
    return $result;
}
function update_in_db_one($name_db, $array_search, $array_change)
{
    global $db;

    $result =  $db->$name_db->updateOne($array_search, ['$set' => $array_change]);
    $result->isUpdated =  (bool) $result->getMatchedCount() == 1;
    return $result;
}
function update_in_db($name_db, $array_search, $array_change)
{
    global $db;

    $result =  $db->$name_db->updateMany($array_search, ['$set' => $array_change]);
    $result->isUpdated =  (bool) $result->getMatchedCount() == 1;
    return $result;
}
function delete_in_db($name_db, $array_search)
{
    global $db;
    $result =  $db->$name_db->deleteMany($array_search);
    if ((int)$result->getDeletedCount() > 0) {
        $result->isDeleted = true;
    } else {
        $result->isDeleted = false;
    }
    return $result;
}
function find_in_db_array($name_db, $find_str)
{
    global $db;
    $result = $db->$name_db->find(
        $find_str
    )->toArray();
    return $result;
}
function find_in_db_array_with_sort($name_db, $find_str, $sort_by = null, $sort_value = null, $limit = null)
{
    global $db;
    $search = [];
    if ($sort_by != null && $limit != null) {
        $search = [
            'sort' => ['"' . $sort_by . '"' => (int)$sort_value],
            'limit' => (int)$limit
        ];
    } elseif ($sort_by != null && $limit == null) {
        $search = [
            'sort' => ['"' . $sort_by . '"' => (int)$sort_value]
        ];
    } elseif ($sort_by == null && $limit != null) {
        $search = [
            'limit' => $limit
        ];
    }
    $result = $db->$name_db->find(
        $find_str,
        $search
    )->toArray();
    return $result;
}
function find_in_db($name_db, $find_str)
{
    global $db;
    $result = $db->$name_db->findOne(
        $find_str
    );
    return (object)$result;
}
function insert_in_db_array($name_db, $array_insert)
{
    global $db;
    $result = $db->$name_db->insertMany(
        $array_insert
    );
    $result->counts = $result->getInsertedCount();
    $result->uids = $result->getInsertedIds();
    return $result;
}
function findUserByID($id)
{
    $id = (int)$id;
    $user = find_in_db("users", ["id" => $id]);
    if (empty((array)$user)) $user = false;
    return $user;
}
function FindUserByToken($token)
{
    if (!isValidMd5($token)) return false;
    $token = $token;
    $user = find_in_db("users", ["token" => $token]);
    if (empty((array)$user)) $user = false;
    return $user;
}
function get_info_by_steam64id($id)
{
    if ((int)$id < 1) return false;
    $get = json_decode(file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=&steamids=" . $id));
    $get = (object)$get->response->players[0];
    if (empty($get->avatarfull)) {
        $get = (object)["avatarfull" => "/files/images/icons/persone.png"];
    }
    return $get;
}
function user_auth()
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['username']) || empty($_SESSION['password']))
        return false;
    $user_id = (int)$_SESSION['user_id'];
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];
    if ($user_id == 0) return false;
    $user = findUserByID($user_id);
    if ($user == false) return;
    if ($user->username != $username && $user->password != $password) return false;
    if ((int)$user->id > 0) {
        $user->notify = getUserNotify($user);
        $user->friends = getFriends($user);
        $user->historyplayers = getHistoryPlayers($user);
        $user->medals = getUserMedals($user);
        $user->isTickets = getUserNewsTickets($user);
    }
    return $user;
}
function getUserNewsTickets($user)
{
    if (empty($user) || (int)$user->id == 0) return false;
    $query = find_in_db_array("tickets", ["owner_id" => (int)$user->id, "status" => 0]);
    if ((int)count($query) == 0) return false;
    for ($i = 0; $i < count($query); $i++) {
        $q = (object)$query[$i];
        $thisTicketMsgs = find_in_db_array("MsgInTickets", ["ticket_id" => (int)$q->id, 'user_id' => ['$ne' => (int)$user->id]]);
        if (count($thisTicketMsgs) > 0) return true;
    }
    return false;
}
function getUserNotify($user)
{
    if (empty($user) || (int)$user->id == 0) return;
    $senders = find_in_db_array("friend_list", ['recipient_id' => (int)$user->id, 'status' => 0]);
    $notify = [];
    for ($i = 0; $i < count($senders); $i++) {
        $res = $senders[$i];
        $thisUser = FindUserByID($res->sender_id);
        $notify[] = ["id" => $thisUser->id, "username" => subName8($thisUser->username)];
    }
    return $notify;
}
function getFriends($user)
{
    if (empty($user) || (int)$user->id == 0) return;
    $user_id = (int)$user->id;
    $friends = find_in_db_array("friend_list", ['$or' => [['sender_id' => $user_id, "status" => 1], ['recipient_id' => $user_id, "status" => 1]]]);
    $friendList = [];

    for ($i = 0; $i < count($friends); $i++) {
        $friend = $friends[$i];
        $id_friend = $friend->sender_id;
        if ($id_friend == $user->id) $id_friend = $friend->recipient_id;
        $thisUser = FindUserByID($id_friend);
        $icon = $thisUser->avatar;
        $friendList[] = ["id" => $thisUser->id, "icon" => $icon, "username" => $thisUser->username];
    }
    return $friendList;
}
function getInfoAboutMedalsByID($medal_id)
{
    $medal_id = (int)$medal_id;
    $q = find_in_db_array("medals", ["id" => $medal_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getUserMedals($user)
{
    $arr = find_in_db_array("medals_users", ["user_id" => (int)$user->id]);
    if (count($arr) == 0) return false;
    $userMedals = [];
    for ($i = 0; $i < count($arr); $i++) {
        $userMedal = (object)$arr[$i];
        $info = getInfoAboutMedalsByID((int)$userMedal->id_medal);
        if ($info != false) {
            $userMedals[] = (object)["name" => $info->name, "icon" => $info->icon, "color" => $info->color];
        }
    }
    return $userMedals;
}
function getHistoryPlayers($user)
{
    if (empty($user) || (int)$user->id == 0) return print("not found");
    $user_id = (int)$user->id;
    $players = [];
    $rooms = [];
    global $db;

    $q_get_players =  $db->playersListInRoom->find(['user_id' => $user_id], [
        'sort' => ['id' => -1],
        'limit' => 5
    ])->toArray();
    for ($i = 0; $i < count($q_get_players); $i++) {
        $rooms[] = ["team" => $q_get_players[$i]['team'], "room" => (int)$q_get_players[$i]['room_id']];
    }
    for ($i = 0; $i < count($rooms); $i++) {
        $room = $rooms[$i];
        $q_room = find_in_db_array("playersListInRoom", ['user_id' => ['$ne' => $user_id], 'room_id' => (int)$room['room'], 'team' => ['$ne' => $room['team']]]);
        for ($j = 0; $j < count($q_room); $j++) {
            $res_roomlist = $q_room[$j];
            $thisUser = FindUserByID((int)$res_roomlist['user_id']);
            $for_add = ["id" => $thisUser->id, "icon" => $thisUser->avatar, "username" => $thisUser->username];
            if (!in_array_r($for_add, $players)) $players[] = $for_add;
        }
    }
    return $players;
}
function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function subName8($str)
{
    return mb_substr($str, 0, 9, 'UTF-8');
}
function sendMessageInWall($owner_id, $user_id, $text)
{
    global $db;
    $owner_id = (int)$owner_id;
    $user_id = (int)$user_id;
    $last_id = $db->users_walls->find([], [
        'sort' => ['id' => -1],
        'limit' => 1
    ])->toArray();
    $new_id = 0;
    if (count($last_id) == 0) $new_id = 1;
    else $new_id = 1 + (int)$last_id[0]['id'];

    $date = date("Y-m-d H:i:s");
    return insert_in_db_one("users_walls", ["id" => (int)$new_id, "owner_id" => (int)$owner_id, "sender_id" => (int)$user_id, "text" => $text, "date" => $date]);
}
function getModesObject($name = null, $id = null)
{
    $name = strtolower($name);
    $id = (int)$id;
    $obj = [];

    if (!empty($name) && $id == 0) {
        switch ($name) {
            case '1vs1':
                $obj = ["id" => 1, "name" => "1vs1"];
                break;
            case '2vs2':
                $obj = ["id" => 2, "name" => "2vs2"];
                break;
            case "3vs3":
                $obj = ["id" => 3, "name" => "3vs3"];
                break;
            case '5vs5':
                $obj = ["id" => 4, "name" => "5vs5"];
                break;
        }
    }

    return (object)$obj;
}
function getRegionsObject($name = null, $id = null)
{
    $name = strtoupper($name);
    $id = (int)$id;
    $obj = [];

    if (!empty($name) && $id == 0) {
        switch ($name) {
            case 'ALL':
                $obj = ["id" => 0, "name" => "ALL"];
                break;
            case 'RUS':
                $obj = ["id" => 1, "name" => "RUS"];
                break;
            case "EU":
                $obj = ["id" => 2, "name" => "EU"];
                break;
        }
    }

    return (object)$obj;
}
function different_numbers($num1, $num2)
{
    return ($num1 / $num2 - 1) * 100;
}
function unset_with_save_pos($unset_index, $global_array)
{
    unset($global_array[$unset_index]);
    return array_values($global_array);
}
function get_news()
{
    global $db;
    $news = $db->news->find([], [
        'sort' => ['id' => -1]
    ])->toArray();
    return $news;
}
function isFriend($user_id, $profile_id)
{
    $user_id = (int)$user_id;
    $profile_id = (int)$profile_id;
    if ($user_id == 0 || $profile_id == 0) return false;
    $res = find_in_db_array("friend_list", ['$or' => [['sender_id' => $profile_id, "recipient_id" => $user_id], ['sender_id' => $user_id, "recipient_id" => $profile_id]]]);
    if ((int)count($res) == 0) return false;
    $res =  (object)$res[0];
    return $res;
}
function getRatingElo($ratingA, $ratingB, $result, $noob = 0, $vip = false, $my_score = 0, $rounds = 0)
{
    $Ea = 1 / (1 + pow(10, ($ratingB - $ratingA) / 400));
    ($noob == 1 ? $K = 30 : ($ratingA < 2400 ? $K = 15 : $K = 10));
    ($result == 0 ? $Sa = 1 : ($result == 2 ? $Sa = 0.5 : $Sa = 0));
    if ($vip && $result == 0) {
        $resultRatingA = $ratingA + ($K * ($Sa - $Ea)) * 5.5;
    } elseif ((int)$rounds > 0 && $result == 1) {
        $rounds = (int)$rounds;
        $my_score = (int)$my_score;
        $multiply = 4;
        $round_diff = round(($rounds / $my_score), 2);
        if (1.20 > $round_diff) {
            $multiply = 1;
        }elseif (1.40 > $round_diff) {
            $multiply = 2;
        }elseif (1.60 > $round_diff) {
            $multiply = 3;
        }else {
            $multiply = 4;
        }
        $resultRatingA = $ratingA + ($K * ($Sa - $Ea)) * $multiply;
    } else {
        $resultRatingA = $ratingA + ($K * ($Sa - $Ea)) * 4;
    }


    return round($resultRatingA);
}
function getRankByElo($elo)
{
    $elo = (int)$elo;
    global $RANK_LIST;
    $ranks = $RANK_LIST;
    if ($ranks[2] > $elo) return 1;
    if ($ranks[2] <= $elo && $ranks[3] > $elo) return 2;
    if ($ranks[3] <= $elo && $ranks[4] > $elo) return 3;
    if ($ranks[4] <= $elo && $ranks[5] > $elo) return 4;
    if ($ranks[5] <= $elo && $ranks[6] > $elo) return 5;
    if ($ranks[6] <= $elo && $ranks[7] > $elo) return 6;
    if ($ranks[7] <= $elo && $ranks[8] > $elo) return 7;
    if ($ranks[8] <= $elo && $ranks[9] > $elo) return 8;
    if ($ranks[9] <= $elo && $ranks[10] > $elo) return 9;
    if ($ranks[10] <= $elo) return 10;
}
function mathKD($kills, $deaths)
{
    $kills = (int)$kills;
    $deaths = (int)$deaths;
    if ($kills == 0) return 0.0;
    if ($deaths == 0) return round($kills, 1);
    return round(($kills / $deaths), 1);
}
define("__DISCORD_WEB_HOOK_SOTHACK__", "https://discord.com/api/webhooks//");

function DiscordSendWithHook($HOOK, $name, $content)
{
    $webhookurl = $HOOK;

    $timestamp = date("c", strtotime("now"));

    $json_data = json_encode(array("content" => $content, "username" => $name));



    $postdata = ($json_data);

    $opts = array(
        'http' =>
        array(
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $postdata
        )
    );
    $context = stream_context_create($opts);
    file_get_contents($webhookurl, false, $context);
}
function prepare_headers($headers)
{
    return
        implode(
            '',
            array_map(function ($key, $value) {
                return "$key: $value\r\n";
            }, array_keys($headers), array_values($headers))
        );
}

function http_post($url, $data, $ignore_errors = false)
{
    $data_query = http_build_query($data);
    $data_len = strlen($data_query);

    $headers = array(
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Accept' => 'application/xml',
        'Content-Length' => $data_len
    );

    $response =
        file_get_contents($url, false, stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST',
                    'header' => prepare_headers($headers),
                    'content' => $data_query,
                    'ignore_errors' => $ignore_errors
                )
            )
        ));

    return (false === $response) ? false :
        $response;
}
function POST_REQUEST($url, $type, $postdata)
{
    if ($type == 'json') {
        $opts = array(
            'http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    } elseif ($type == 'form') {
        $opts = array(
            'http' =>
            array(
                'method'  => 'POST',
                'header'  => 'application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    } else {
        return false;
    }
}
function getPayokOrder($order_id)
{
    if (strlen($order_id) == 0 || empty($order_id)) return false;
    $api_global_payok = "";
    $api_global_id_payok = 1153;
    $request = json_decode(POST_REQUEST("https://payok.io/api/transaction", "form", "API_ID={$api_global_id_payok}&API_KEY={$api_global_payok}&shop=" . PAYOK_SHOP_ID . "&payment={$order_id}"));
    if (empty($request)) return false;
    $error = $request->status == 'error';
    if ($error) return false;
    $new_array = [];
    foreach ($request as $key => $value) {
        $new_array = $value;
    }
    $new_array->status = $request->status;
    $user_id =  htmlspecialchars_decode($new_array->custom_fields);
    $user_id = trim($user_id, '{}');
    $user_id = str_replace(":", "", $user_id);
    $user_id = str_replace("user", "", $user_id);
    $user_id = str_replace('"', "", $user_id);
    $new_array->user_id = (int)$user_id;

    if (!filter_var($new_array->email, FILTER_VALIDATE_EMAIL))  $new_array->email = "none";
    return $new_array;
}
function getNewUserForVeto($room_id, $team)
{
    global $db;
    $res = $db->playersListInRoom->find(['room_id' => (int)$room_id, 'team' => ['$ne' => $team]], [
        'limit' => 1
    ])->toArray();
    if (count($res) == 0) return false;
    else $res = (object)$res[0];
    return $res->user_id;
}
function playerGetStatsByID($id, $sort = false, $sort_by_mode = 0, $sort_by_region = 0)
{
    $id = (int)$id;
    $stats = [
        "kills" => 0,
        "deaths" => 0,
        "assists" => 0,
        "winrate" => 0,
        "hs_rate" => 0,
        "hs" => 0,
        "kd" => 0,
        "wins" => 0,
        "lose" => 0,
    ];
    if ($sort) {
        $q = find_in_db_array("playersListInRoom", ["user_id" => (int)$id]); // qr("SELECT * FROM `playersListInRoom` WHERE `user_id` = '{$id}'");
        for ($i = 0; $i < count($q); $i++) {
            $res = (object)$q[$i];
            $thisRoom = getRoomById($res->room_id);
            if ((int)$sort_by_mode != 0 && (int)$thisRoom->mode_id != (int)$sort_by_mode) continue;
            if ((int)$sort_by_region != 0 && (int)$thisRoom->region_id != (int)$sort_by_region) continue;
            $stats['kills'] += (int)$res->kills;
            $stats['deaths'] += (int)$res->deaths;
            $stats['assists'] += (int)$res->assists;
            $stats['hs'] += (int)$res->hs;
            if ($res->team == $thisRoom->teamwin) {
                $stats['wins'] += 1;
            } else if ($thisRoom->teamwin != 'error') {
                $stats['lose'] += 1;
            }
        }
    } else {
        $q = find_in_db_array("playersListInRoom", ["user_id" => (int)$id]);
        for ($i = 0; $i < count($q); $i++) {
            $res = (object)$q[$i];
            $thisRoom = getRoomById($res->room_id);
            $stats['kills'] += (int)$res->kills;
            $stats['deaths'] += (int)$res->deaths;
            $stats['assists'] += (int)$res->assists;
            $stats['hs'] += (int)$res->hs;
            if ($res->team == $thisRoom->teamwin) {
                $stats['wins'] += 1;
            } else if ($thisRoom->teamwin != 'error') {
                $stats['lose'] += 1;
            }
        }
    }

    return (object)$stats;
}
function getRoomById($id)
{
    $id = (int)$id;
    if ($id < 1) return false;
    $q = find_in_db_array("rooms", ["id" => (int)$id]);
    if ((int)count($q) == 0) return false;
    return (object)$q[0];
}
function getPlayersInRoomById($id)
{
    $id = (int)$id;
    if ($id < 1) return false;
    $q = find_in_db_array("playersListInRoom", ["id" => (int)$id]);
    if ((int)count($q) == 0) return false;
    return (object)$q[0];
}

function getPlayerInRoom($user_id, $room_id)
{
    $user_id = (int)$user_id;
    if ($user_id < 1) return false;
    $room_id = (int)$room_id;
    if ($room_id < 1) return false;
    $q = find_in_db_array("playersListInRoom", ["user_id" => (int)$user_id, "room_id" => (int)$room_id]);
    if ((int)count($q) == 0) return false;
    return (object)$q[0];
}
function topSetClassActiveByMode($type)
{
    global $mode;
    if ($mode == $type) return "class='active'";
}
function topSetClassActiveByRegion($type)
{
    global $region;
    if ($region == $type) return "class='active'";
}
function steam_auth_login_url()
{
    $host = $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_HOST'];
    $link = "https://steamcommunity.com/openid/login";
    $params = array(
        'openid.ns'         => 'http://specs.openid.net/auth/2.0',
        'openid.mode'       => 'checkid_setup',
        'openid.return_to'  => $host . '/auth/steam.php',
        'openid.realm'      => $host,
        'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
    );
    $sep = '&';

    $link = $link . '?' . http_build_query($params, '', $sep);
    return $link;
}
function discord_auth_login_url()
{
    $client_id = 995080379092451489;
    $url = "https://discord.com/api/oauth2/authorize?client_id=$client_id&redirect_uri=https%3A%2F%2Fbloodgodz.com%2Fapi%2Fdiscord%2Fauth&response_type=code&scope=identify";
    return $url;
}

function insertUserInDB($username, $mail, $password, $news)
{
    global $db;
    if ($news == 'on') $news = 1;
    else $news = 0;
    $token = md5(time());
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $date = date("Y-m-d H:i:s");
    $user_last_id = $db->users->find([], [
        'sort' => ['id' => -1],
        'limit' => 1
    ])->toArray();
    $new_id = 0;
    $eloReg = 1020;
    $rankReg = 3;
    if (count($user_last_id) == 0) $new_id = 1;
    else $new_id = 1 + (int)$user_last_id[0]['id'];
    $res = insert_in_db_one(
        "users",
        [
            "id" => (int)$new_id, "mail" => $mail,
            "username" => $username, "password" => $password, "steam_id" => 0,
            "group_id" => 5, "getUpdates" => $news,
            "rank_1" => $rankReg, "rank_2" => $rankReg, "rank_3" => $rankReg, "rank_4" => $rankReg,
            "elo_1" => $eloReg, "elo_2" => $eloReg, "elo_3" => $eloReg, "elo_4" => $eloReg,
            "balance" => 0, "active_room" => NULL, "active_premium_lobby" => NULL, "register_date" => $date, "token" => $token, "discord_tag" => NULL, "youtube" => NULL, "ip" => $ip, "avatar" => "/files/images/icons/persone.png",
            "unix" => time(), "discord_id" => NULL, "ban" => NULL
        ]
    );
    return $res->uid;
}
function registerFindUser($username, $mail)
{
    $q = find_in_db_array("users", ['$or' => [['mail' => $mail], ['username' => $username]]]);
    $bool = count($q) > 0;
    return (bool)$bool;
}
function IsipUsed($ip)
{
    $q = find_in_db_array("users", ['ip' => $ip]);
    if (count($q) == 0) return false;
    else return true;
}
function getMedalByID($medal_id)
{
    $q = find_in_db_array("medals", ["id" => (int)$medal_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getNewsByID($news_id)
{
    $q = find_in_db_array("news", ["id" => (int)$news_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getMarketItemByID($item_id)
{
    $q = find_in_db_array("market_items", ["id" => (int)$item_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getServerByID($server_id)
{
    $q = find_in_db_array("servers", ["id" => (int)$server_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getRegionByID($region_id)
{
    $q = find_in_db_array("regions", ["id" => (int)$region_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getModeByID($mode_id)
{
    $q = find_in_db_array("mods", ["id" => (int)$mode_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function getGroupNameByID($id)
{
    $id = (int)$id;
    switch ($id) {
        case 1:
            return "Администратор";
        case 2:
            return "Модератор";
        case 3:
            return "Саппорт";
        case 4:
            return "Премиум";
        case 5:
            return "Пользователь";
    }
}
function getRussianStatusByID($id)
{
    $id = (int)$id;
    switch ($id) {
        case 0:
            return "Вето карт";
        case 1:
            return "Ожидание игроков";
        case 2:
        case 3:
            return "Матч идёт";
        case 4:
            return "Закончен";
        case 5:
            return "Отменён";
    }
}
function getSSHByID($ssh_id)
{
    $q = find_in_db_array("ssh", ["id" => (int)$ssh_id]);
    if (count($q) == 0) return false;
    return (object)$q[0];
}
function loginFindUser($username, $password)
{
    $q = find_in_db_array("users", ['username' => ['$regex' => '^' . $username . '$', '$options' => 'i'], 'password' => $password]);
    if ((int)count($q) == 0) return false;
    return $q[0];
}
function j_print($arr)
{
    return print(json_encode($arr));
}
function isValidMd5($md5 = '')
{
    return strlen($md5) == 32 && ctype_xdigit($md5);
}
function profileStats($user)
{
    global $db;
    if ((int)$user->id == 0) return false;
    $wins = 0;
    $kills = 0;
    $deaths = 0;
    $assists = 0;
    $hs = 0;
    $games = [];
    $q = $db->playersListInRoom->find(["user_id" => (int)$user->id,],  ['sort' => ['id' => -1]])->toArray();
    $countRooms = 0;
    for ($i = 0; $i < count($q); $i++) {
        $res = (object)$q[$i];
        $room = getRoomById($res->room_id);
        if ($room->status == 5) continue;
        $countRooms = $countRooms + 1;
        if ($room->teamwin == $res->team) $wins += 1;
        $kills += (int)$res->kills;
        $deaths += (int)$res->deaths;
        $assists += (int)$res->assists;
        $hs  += (int)$res->hs;
        $score = "0";
        if ($res->team == 'team1') {
            $score = $room->team1 . "/" . $room->team2;
        } else {
            $score = $room->team2 . "/" . $room->team1;
        }
        $games[] = ["date" => $res->date, "mode" => __MODS_LIST__[$room->mode_id - 1], "score" => $score, "map" => $room->map, "roomid" => $room->id];
    }
    $kd = round(($kills / $deaths), 1);
    if ($kills == 0) $kd = 0;
    if ($deaths == 0) $kd = $kills;
    $stats = (object)[
        "count" => $countRooms,
        "wins" => $wins,
        "deaths" => $deaths,
        "assists" => $assists,
        "kills" => $kills,
        "hs" => $hs,
        "kd" => $kd,
        "games" => $games
    ];
    return $stats;
}
function get_new_id_for_db($db_name)
{
    global $db;
    $last_id = $db->$db_name->find([], [
        'sort' => ['id' => -1],
        'limit' => 1
    ])->toArray();
    $new_id = 0;
    if (count($last_id) == 0) $new_id = 1;
    else $new_id = 1 + (int)$last_id[0]['id'];
    return $new_id;
}
//Get5 Generator
function get_json_for_match($api_key, $match_id, $map, $players, $cfgName = "5x5,cfg", $game_mode_id = 1, $capts, $playersName)
{
    $json = (object)[];
    $json->matchid = "{$match_id}";
    $json->num_maps = 1;
    $json->players_per_team = count($players) / 2;
    $json->min_players_to_ready = count($players);
    $json->min_spectators_to_ready = 0;
    $json->skip_veto = true;
    $json->veto_first = "team1";
    $json->side_type = "standard";
    $json->maplist = [$map];
    $json->favored_percentage_team1 = (int)65;
    $json->favored_percentage_text = "HLTV Bets";
    $json->team1->name = $capts[0];
    $json->team1->tag = "";
    $json->team1->flag = "";
    $json->team1->logo = "";
    $json->team1->players = [];
    for ($i = 0; $i < count($players) / 2; $i++) {
        $json->team1->players[] = $players[$i];
    }
    $json->team2->name = $capts[1];
    $json->team2->tag = "";
    $json->team2->flag = "";
    $json->team2->logo = "";
    $json->team2->players = [];
    for ($i = (count($players) / 2); $i < count($players); $i++) {
        $json->team2->players[] = $players[$i];
    }
    $json->cvars = [
        "game_type" => "0",
        "game_mode" => "{$game_mode_id}",
        "get5_live_cfg" => $cfgName
    ];
    // game_mode 1 == MM
    // game_mode 2 == Wingman
    return json_encode($json, JSON_PRETTY_PRINT);
}
function getServerForJoin($server_id)
{
    $server_id = (int)$server_id;
    if ($server_id < 1) return false;
    $q_server = getServerByID($server_id);
    if ((int)($q_server->id) == 0) return false;
    $server = $q_server;
    $ssh = getSSHByID($server->ssh_id);
    $obj = (object)[];
    $obj->text = "connect {$ssh->ip}:{$server->port}";
    $obj->link = "steam://connect/{$ssh->ip}:{$server->port}";
    return $obj;
}
function getUserBySteamId($id)
{
    $id = (int)$id;
    if ($id < 1000) return false;
    $q = find_in_db_array("users", ["steam_id" => $id]);
    if ((int)count($q) == 0) return false;
    return (object)$q[0];
}
function rcon_exec($array)
{

    $socket = stream_socket_client("tcp://{$array->ip}:{$array->port}", $errno, $errstr);

    stream_set_timeout($socket, 2);

    if (!$socket) {
        return false; 
    }

    $id = rand(0, 10);
    $type = 3;
    $s1 = $array->rcon;
    $s2 = "";
    $data  = pack('VV', $id, $type);
    $data .= $s1 . chr(0) . $s2 . chr(0);
    $data  = pack('V', strlen($data)) . $data;

    fwrite($socket, $data, strlen($data));

    $id = rand(0, 10);
    $type = 2; 
    $s1 = $array->exec;
    $s2 = "";
    $data  = pack('VV', $id, $type);
    $data .= $s1 . chr(0) . $s2 . chr(0);
    $data  = pack('V', strlen($data)) . $data;

    fwrite($socket, $data, strlen($data));
    fclose($socket);
    return true;
}
function rcon_exec_with_read($array)
{

    $socket = stream_socket_client("tcp://{$array->ip}:{$array->port}", $errno, $errstr);

    stream_set_timeout($socket, 2);

    if (!$socket) {
        return false;
    }


    $id = rand(0, 10);
    $type = 3; 
    $s1 = $array->rcon; 
    $s2 = "";
    $data  = pack('VV', $id, $type);
    $data .= $s1 . chr(0) . $s2 . chr(0);
    $data  = pack('V', strlen($data)) . $data;

    fwrite($socket, $data, strlen($data));

    $id = rand(0, 10);
    $type = 2; 
    $s1 = $array->exec;
    $s2 = "";
    $data  = pack('VV', $id, $type);
    $data .= $s1 . chr(0) . $s2 . chr(0);
    $data  = pack('V', strlen($data)) . $data;

    fwrite($socket, $data, strlen($data));


    $rarray = [];
    $count = 0;

    while ($data = fread($socket, 4)) {
        $data = unpack('V1Size', $data);

        if ($data['Size'] > 4096) {
            $packet = '';
            for ($i = 0; $i < 8; $i++) {
                $packet .= "\x00";
            }
            $packet .= fread($socket, 4096);
        } else {
            $packet = fread($socket, $data['Size']);
        }

        $rarray[] = unpack('V1ID/V1Response/a*S1/a*S2', $packet);
    }
    fclose($socket);
    $res = $rarray[2]["S1"];
    if (empty($res)) return false;
    if (strpos($res, "L " . date("m/d/Y"))) $res = explode("L " . date("m/d/Y"), $res);
    if (empty($res[0])) return false;
    return $res[0];
}

function IsuserInRoom($user_id, $room_id)
{
    $user_id = (int)$user_id;
    $room_id = (int)$room_id;
    if ($user_id < 1) return false;
    if ($room_id < 1) return false;

    $q = find_in_db_array("playersListInRoom", ["user_id" => (int)$user_id, "room_id" => (int)$room_id]);
    if ((int)count($q) == 0) return false;
    else return true;
}
function getPercentForNewRank($now_rank, $now_elo)
{
    global $RANK_LIST;
    $now_elo = (int)$now_elo;
    $now_rank = (int)$now_rank;
    //  () . " - ".  ($elo1_need_for_up_rank - $user->elo_1 )
    $eloForNewRank = $RANK_LIST[$now_rank + 1] - $RANK_LIST[$now_rank];
    $eloCurrentRank = $RANK_LIST[$now_rank + 1] - $now_elo;

    return round((($eloForNewRank - $eloCurrentRank) / $eloForNewRank) * 100);
}
function isValidYTVideo($video_id)
{
    $video = getVideoInfoByYT($video_id);
    if (empty($video) || empty($video->pageInfo) || empty($video->pageInfo->totalResults)) return false;
    if ((int)$video->pageInfo->totalResults == 0) return false;
    else return true;
}
function getVideoInfoByYT($video_id)
{
    $url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key=";
    $url = file_get_contents($url);
    return json_decode($url);
}
function validateStr($str)
{
    return preg_match('/^[0-9a-zA-Z-_.]+$/', $str);
}
function payok_create_payment($sum, $user_id)
{
    $arr = [
        $amount = (float)$sum,
        $payment =  mb_strimwidth(md5(time()), 0, 15, ""),
        $shop = (int)PAYOK_SHOP_ID,
        $currency = 'RUB',
        $desc = 'buy premium on bloodgodz.com',
        $secret = PAYOK_SECRET_KEY
    ];
    $sign = md5(implode('|', $arr));

    $pay = "https://payok.io/pay?amount=$amount&payment=$payment&shop=$shop&currency=$currency&desc=$desc&user=$user_id&sign=$sign";
    return $pay;
}
function findPremiumUserStateMissons($user_id, $date)
{
    if ((int)$user_id == 0) return false;
    $q = find_in_db_array("users_missions", ["user_id" => (int)$user_id, "date" => $date]);
    if ((int)count($q) > 0) return (object)$q[0];
    $state = new stdClass();
    $state->kills = 0;
    $state->ace = 0;
    $state->assists = 0;
    $state->defuse = 0;
    $state->kd = 0;
    $state->plant = 0;
    $state->suicide = 0;
    $state->hs = 0;
    return $state;
}
function getPremiumStateAboutMission($mission)
{
    $local_mission = [];
    foreach ($mission as $key => $value) {
        if ($key != '_id' && $key != 'id' && $key != 'name' && $key != 'sum' && $key != 'visible') {
            $local_mission[$key] = $value;
        }
    }
    $type = "none";
    $need_key = "none";
    foreach ($local_mission as $key => $value) {
        if ((int)$value > 0) {
            $need_key = $value;
            $type = $key;
        }
    }
    return (object)[
        "need" => $need_key,
        "key" => $type
    ];
}
function gen_str($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function mail_send_resetpassword($to, $pass)
{
    $from = 'admin@bloodgodz.com';
    $fromName = 'Bloodgodz';

    $subject = "Вы успешно сбросили пароль на bloodgodz.com";

    $htmlContent = ' 
    <html>
    <head>
    </head>
        <body style="background-color: #17171B;text-align:center;color:white;"> 
        <div style="background: #202024;border-radius: 0px 13px 13px 0px;width:calc(100% - 60%);margin-left:30%;padding:5em;text-align:center;">
            <h1><a href="https://bloodgodz.com"><img src="https://i.imgur.com/J3IYvaO.png"  style="margin-top: 5%;"></a></h1> 
            <p style="color:white;">Вы успешно зарегистрировались на bloodgodz.com</p>
            <div style="background:#2b2b2f;border-radius:13px;padding:1em;">
            <p style="color:white;">Ваш новый пароль: ' . $pass . '</p>
            </div>
            <p style="margin-bottom:5%;color:white;text-align:center;">Используйте эти данные для авторизации на <a href="https://bloodgodz.com">bloodgodz.com</a></p>
            </div>
        </body></html>';


    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    $headers .= 'From: ' . $fromName . '<' . $from . '>' . "\r\n";

    return mail($to, $subject, $htmlContent, $headers);
}
function color_name_to_hex($color_name)
{
    $colors  =  array(
        'aliceblue' => 'F0F8FF',
        'antiquewhite' => 'FAEBD7',
        'aqua' => '00FFFF',
        'aquamarine' => '7FFFD4',
        'azure' => 'F0FFFF',
        'beige' => 'F5F5DC',
        'bisque' => 'FFE4C4',
        'black' => '000000',
        'blanchedalmond ' => 'FFEBCD',
        'blue' => '0000FF',
        'blueviolet' => '8A2BE2',
        'brown' => 'A52A2A',
        'burlywood' => 'DEB887',
        'cadetblue' => '5F9EA0',
        'chartreuse' => '7FFF00',
        'chocolate' => 'D2691E',
        'coral' => 'FF7F50',
        'cornflowerblue' => '6495ED',
        'cornsilk' => 'FFF8DC',
        'crimson' => 'DC143C',
        'cyan' => '00FFFF',
        'darkblue' => '00008B',
        'darkcyan' => '008B8B',
        'darkgoldenrod' => 'B8860B',
        'darkgray' => 'A9A9A9',
        'darkgreen' => '006400',
        'darkgrey' => 'A9A9A9',
        'darkkhaki' => 'BDB76B',
        'darkmagenta' => '8B008B',
        'darkolivegreen' => '556B2F',
        'darkorange' => 'FF8C00',
        'darkorchid' => '9932CC',
        'darkred' => '8B0000',
        'darksalmon' => 'E9967A',
        'darkseagreen' => '8FBC8F',
        'darkslateblue' => '483D8B',
        'darkslategray' => '2F4F4F',
        'darkslategrey' => '2F4F4F',
        'darkturquoise' => '00CED1',
        'darkviolet' => '9400D3',
        'deeppink' => 'FF1493',
        'deepskyblue' => '00BFFF',
        'dimgray' => '696969',
        'dimgrey' => '696969',
        'dodgerblue' => '1E90FF',
        'firebrick' => 'B22222',
        'floralwhite' => 'FFFAF0',
        'forestgreen' => '228B22',
        'fuchsia' => 'FF00FF',
        'gainsboro' => 'DCDCDC',
        'ghostwhite' => 'F8F8FF',
        'gold' => 'FFD700',
        'goldenrod' => 'DAA520',
        'gray' => '808080',
        'green' => '008000',
        'greenyellow' => 'ADFF2F',
        'grey' => '808080',
        'honeydew' => 'F0FFF0',
        'hotpink' => 'FF69B4',
        'indianred' => 'CD5C5C',
        'indigo' => '4B0082',
        'ivory' => 'FFFFF0',
        'khaki' => 'F0E68C',
        'lavender' => 'E6E6FA',
        'lavenderblush' => 'FFF0F5',
        'lawngreen' => '7CFC00',
        'lemonchiffon' => 'FFFACD',
        'lightblue' => 'ADD8E6',
        'lightcoral' => 'F08080',
        'lightcyan' => 'E0FFFF',
        'lightgoldenrodyellow' => 'FAFAD2',
        'lightgray' => 'D3D3D3',
        'lightgreen' => '90EE90',
        'lightgrey' => 'D3D3D3',
        'lightpink' => 'FFB6C1',
        'lightsalmon' => 'FFA07A',
        'lightseagreen' => '20B2AA',
        'lightskyblue' => '87CEFA',
        'lightslategray' => '778899',
        'lightslategrey' => '778899',
        'lightsteelblue' => 'B0C4DE',
        'lightyellow' => 'FFFFE0',
        'lime' => '00FF00',
        'limegreen' => '32CD32',
        'linen' => 'FAF0E6',
        'magenta' => 'FF00FF',
        'maroon' => '800000',
        'mediumaquamarine' => '66CDAA',
        'mediumblue' => '0000CD',
        'mediumorchid' => 'BA55D3',
        'mediumpurple' => '9370D0',
        'mediumseagreen' => '3CB371',
        'mediumslateblue' => '7B68EE',
        'mediumspringgreen' => '00FA9A',
        'mediumturquoise' => '48D1CC',
        'mediumvioletred' => 'C71585',
        'midnightblue' => '191970',
        'mintcream' => 'F5FFFA',
        'mistyrose' => 'FFE4E1',
        'moccasin' => 'FFE4B5',
        'navajowhite' => 'FFDEAD',
        'navy' => '000080',
        'oldlace' => 'FDF5E6',
        'olive' => '808000',
        'olivedrab' => '6B8E23',
        'orange' => 'FFA500',
        'orangered' => 'FF4500',
        'orchid' => 'DA70D6',
        'palegoldenrod' => 'EEE8AA',
        'palegreen' => '98FB98',
        'paleturquoise' => 'AFEEEE',
        'palevioletred' => 'DB7093',
        'papayawhip' => 'FFEFD5',
        'peachpuff' => 'FFDAB9',
        'peru' => 'CD853F',
        'pink' => 'FFC0CB',
        'plum' => 'DDA0DD',
        'powderblue' => 'B0E0E6',
        'purple' => '800080',
        'red' => 'FF0000',
        'rosybrown' => 'BC8F8F',
        'royalblue' => '4169E1',
        'saddlebrown' => '8B4513',
        'salmon' => 'FA8072',
        'sandybrown' => 'F4A460',
        'seagreen' => '2E8B57',
        'seashell' => 'FFF5EE',
        'sienna' => 'A0522D',
        'silver' => 'C0C0C0',
        'skyblue' => '87CEEB',
        'slateblue' => '6A5ACD',
        'slategray' => '708090',
        'slategrey' => '708090',
        'snow' => 'FFFAFA',
        'springgreen' => '00FF7F',
        'steelblue' => '4682B4',
        'tan' => 'D2B48C',
        'teal' => '008080',
        'thistle' => 'D8BFD8',
        'tomato' => 'FF6347',
        'turquoise' => '40E0D0',
        'violet' => 'EE82EE',
        'wheat' => 'F5DEB3',
        'white' => 'FFFFFF',
        'whitesmoke' => 'F5F5F5',
        'yellow' => 'FFFF00',
        'yellowgreen' => '9ACD32'
    );

    $color_name = strtolower($color_name);
    if (isset($colors[$color_name])) {
        return ('#' . $colors[$color_name]);
    } else {
        return ($color_name);
    }
}
function isHex($color)
{
    if (preg_match('/^#[a-f0-9]{6}$/i', $color))  return true;
    else false;
}
function createDiscordChannel($name)
{
    $token = "";
    $url = "https://discord.com/api/v9/guilds/696477334064070656/channels";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "authorization: $token",
        "content-type: application/json",
        "dnt: 1",
        "origin: https://discord.com",
        "referer: https://discord.com/channels/696477334064070656/1016074184973103144",
        "sec-ch-ua-mobile: ?1",
        "sec-fetch-dest: empty",
        "sec-fetch-mode: cors",
        "sec-fetch-site: same-origin",
        "user-agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Mobile Safari/537.36",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = '{"type":2,"name":"' . $name . '","permission_overwrites":[{"id":"696477334064070656","type":0,"allow":"0","deny":"1024"},{"id":"696477334093692989","type":0,"deny":"0","allow":"1024"}],"parent_id":"1016074043402760313"}';

    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = json_decode(curl_exec($curl));
    curl_close($curl);
    return $resp;
}
function msg_tg($message, $chatId)
{
    $botToken = "";
    $website = "https://api.telegram.org/bot" . $botToken;
    $url = $website . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}
function deleteDiscordChannel($id)
{
    $token = ".-.";
    $url = "https://discord.com/api/v9/channels/$id";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
        "authorization: $token",
        "origin: https://discord.com",
        "referer: https://discord.com/channels/696477334064070656/698237391206350920",
        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = json_decode(curl_exec($curl));
    curl_close($curl);
    return $resp;
}
function remath_Len_search_players($customlobby, $lobby, $mode)
{

    $customLobbyModeLen =  count($customlobby);
    $lobby = count($lobby);
    if ($customLobbyModeLen != 0) {
        switch ($mode) {
            case '2vs2':
                $customLobbyModeLen = $customLobbyModeLen * 2;
                break;
            case '3vs3':
                $customLobbyModeLen = $customLobbyModeLen * 3;
                break;
            case '5vs5':
                $customLobbyModeLen = $customLobbyModeLen * 5;
                break;
        }
    } else {
        $customLobbyModeLen = 0;
    }
    $search_lobby_len =   $customLobbyModeLen + $lobby;
    return (int)$search_lobby_len;
}
