
<?
$id = (int)$_GET['id'];
if ($id < 1) exit("not found match!");
include("../../mongo/mainMongo.php");
$room = getRoomById($id);
if ($room == false) exit("not found match!");
$players = [];
$q_players = $db->playersListInRoom->find(['room_id' => (int)$room->id], [
    'sort' => ['team' => 1]
])->toArray();
$playersName = [];
if ((int)count($q_players) == 0) exit("match error!");
for ($i = 0; $i < (int)count($q_players); $i++) {
    $player = (object)$q_players[$i];
    $findPlayer = FindUserByID($player->user_id);
    $players[] = (string)$findPlayer->steam_id;
    $playersName[] = (string)$findPlayer->username;
}

$configName = "5x5.cfg";
$game_type = 1;
if ((int)$room->mode_id != 4) {
    $configName = "2x2.cfg";
    $game_type = 2;
}
if ($room->mode_id == 3) {
    $configName = "3x3.cfg";
    $game_type = 2;
}
$capts = [
    FindUserByID($room->capt1)->username, FindUserByID($room->capt2)->username
];
$match = get_json_for_match("{$room->id}", $room->id, "{$room->map}", $players, $configName, $game_type,$capts, $playersName);
print_r($match);
