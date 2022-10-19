<?
include("../../mongo/mainMongo.php");
$payment_id = $_POST['payment_id'];
$email = $_POST['email'];
$profit = (float)$_POST['profit'];
$amount = (float)$_POST['amount'];
$user_id = (int)$_POST['custom']['user'];
/*
$data = "\_GET:\n".print_r($_GET,true). "\n";
$data .= "\nPOST:\n".print_r($_POST,true). "\n";
$data .= "\nServer:\n".print_r($_SERVER,true). "\n";
file_put_contents("log.txt", $data, FILE_APPEND);
print(1);
*/
$search_order = getPayokOrder($payment_id);
if ($search_order == false) return print("Bill not found!");
$user = findUserByID($user_id);
if ($user == false) return print("User not found!");
$last_id = (int)get_new_id_for_db("orders");
$add_date = 0;
if ($user->group_id == 4 && (int)$user->group_end > 100) {
    $exp = (int)$user->group_end - (int)time();
    if ($exp > 1) $add_date = $exp;
}
$insert = insert_in_db_one("orders", ["id"=> $last_id, "payment"=> "payok", "user_id"=> $user_id, "bill"=> $payment_id, "email"=> $email, "sum" => $profit, "date"=> (int)time()]);
$group_end = time() + (86400*30) + $add_date;
$update = $db->users->updateOne(["id"=> (int)$user->id], ['$set'=> ["group_end"=> (int)$group_end, "group_id"=> 4]]);
print("Set new group");
?>