<?
include("../mongo/mainMongo.php");
$user = user_auth();
if ($user == false|| $user->group_id == 5) {
    include("premium_buy.php");
}else {
    include("premium_user.php");
}
?>
