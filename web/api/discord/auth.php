<?php
include("../../mongo/mainMongo.php");
$user = user_auth();
if (!isset($_GET['code']) || $user == false) {
    header("Location: " . __HOST__ . "/settings");
    exit();
}
if ((int)$user->discord_id > 1000) {
    header("Location: " . __HOST__ . "/settings");
    exit();
}
$discord_code = $_GET['code'];


$payload = [
    'code' => $discord_code,
    'client_id' => '995080379092451489',
    'client_secret' => 'VRrIjcQ5L5sHMQjnurQpKIja55u_ed2y',
    'grant_type' => 'authorization_code',
    'redirect_uri' => 'https://bloodgodz.com/api/discord/auth',
    'scope' => 'identify',
];



$payload_string = http_build_query($payload);
$discord_token_url = "https://discordapp.com/api/oauth2/token";
///
$oauth2_token = json_decode(http_post("https://discordapp.com/api/oauth2/token", $payload, false));
//
if (empty($oauth2_token)) {
    header("Location: " . __HOST__ . "/settings");
    exit();
}

$access_token = $oauth2_token->access_token;
//
$discord_users_url = "https://discordapp.com/api/users/@me";
$header = array("Authorization: Bearer $access_token", "Content-Type: application/x-www-form-urlencoded");

$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_URL, $discord_users_url);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$res = json_decode(curl_exec($ch));
if (empty($res) || empty($res->id) || empty($res->username)) {
    header("Location: " . __HOST__ . "/settings");
    exit();
}
$tag = $res->username . "#" . $res->discriminator;
update_in_db_one("users", ["id" => (int)$user->id], ["discord_id" => (int)$res->id, "discord_tag" => $tag]);
header("Location: " . __HOST__ . "/settings");
exit();
