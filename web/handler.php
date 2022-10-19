<?
switch ($_POST['handler_id']) {
    case 'user_register':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user != false) return print("dont have access!");
        $mail = $_POST['mail'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $repassword = $_POST['repassword'];
        $policy = $_POST['policy'];
        $news = $_POST['news'];
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        $username = htmlspecialchars($username);
        if (strpos($mail, "riseup")) return j_print(["type" => -14, "msg" => "suck balls"]);
        if (!validateStr($username)) return j_print(["type" => -14, "msg" => "username_incorrect_error"]);
        if (in_array($username, LIST_DECLINE_USERNAME)) return j_print(["type" => -13, "msg" => "username_decline_error"]);
        if ($policy != 'on') return j_print(["type" => 0, "msg" => "policy_error"]);
        if (!empty($news) && $news != 'on') return j_print(["type" => -1, "msg" => "news_error"]);
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) return j_print(["type" => -2, "msg" => "email_error"]);
        if ($password != $repassword) return j_print(["type" => -3, "msg" => "password_error"]);
        if (strlen($username) < 3) return j_print(["type" => -4, "msg" => "username_min_error"]);
        if (strlen($username) > 100) return j_print(["type" => -5, "msg" => "username_max_error"]);
        if (strlen($password) < 3) return j_print(["type" => -6, "msg" => "password_min_error"]);
        if (strlen($password) > 100) return j_print(["type" => -7, "msg" => "password_max_error"]);
        if (registerFindUser($username, $mail)) return j_print(["type" => -8, "msg" => "user_already_registered_error"]);
        if (IsipUsed($ip)) return j_print(["type" => -11, "msg" => "limit_ip_address"]);
        $password = md5($password);
        $insert = insertUserInDB($username, $mail, $password, $news);
        if (empty($insert)) return j_print(["type" => -8, "msg" => "insert_user_error"]);
        $_SESSION['user_id'] = $insert;
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        j_print(["type" => 1, "msg" => "successfully registered"]);
        break;
    case 'user_login':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user != false) return print("dont have access!");
        $username = $_POST['username'];
        $password = $_POST['password'];
        if (strlen($username) < 3) return j_print(["type" => -1, "msg" => "username_min_error"]);
        if (strlen($username) > 100) return j_print(["type" => -2, "msg" => "username_max_error"]);
        if (strlen($password) < 3) return j_print(["type" => -3, "msg" => "password_min_error"]);
        if (strlen($password) > 100) return j_print(["type" => -4, "msg" => "password_max_error"]);
        $password = md5($password);
        $find = loginFindUser($username, $password);
        if ($find == false) return j_print(["type" => -5, "msg" => "user_not_found"]);
        $_SESSION['user_id'] = $find->id;
        $_SESSION['username'] = $find->username;
        $_SESSION['password'] = $find->password;
        j_print(["type" => 1, "msg" => "successfully authorized"]);
        break;
    case 'user_change_mail':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $mail = $_POST['mail'];
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) return j_print(["type" => -1, "msg" => "email_error"]);
        $q_mail = find_in_db_array("users", ["mail" => $mail]);
        if ((int)count($q_mail) > 0) return j_print(["type" => -2, "msg" => "mail_already_in_use_error"]);
        $update = $db->users->updateOne(["id" => (int)$user->id], ['$set' => ["mail" => $mail]]);
        j_print(["type" => 1, "msg" => "successfully changed mail"]);
        break;
    case 'user_change_password':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $oldpass = md5($_POST['oldpass']);
        $pass = md5($_POST['pass']);
        $repass = md5($_POST['repass']);
        if ($user->password != $oldpass) return j_print(["type" => -1, "msg" => "wrong_password_error"]);
        if ($pass != $repass)  return j_print(["type" => -2, "msg" => "password_not_equal_repassword_error"]);
        if (strlen($_POST['pass']) < 3) return j_print(["type" => -3, "msg" => "password_min_error"]);
        if (strlen($_POST['pass']) > 99) return j_print(["type" => -4, "msg" => "password_max_error"]);
        $update = $db->users->updateOne(["id" => (int)$user->id], ['$set' => ["password" => $pass]]);
        $_SESSION['password'] = $pass;
        j_print(["type" => 1, "msg" => "successfully changed password"]);
        break;
    case 'save_social':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $yt = $_POST['yt'];
        $q_YT = "";
        if (strlen($yt) < 10) {
            $q_YT = "`youtube`= NULL";
        } else {
            $q_YT = "`youtube`= '$yt'";
        }
        $update = $db->users->updateOne(["id" => (int)$user->id], ['$set' => ["youtube" => $yt]]);
        j_print(["type" => 1]);
        break;
    case 'addfriend':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $profile_id = (int)str_replace("/user/", "", $_POST['profile']);
        if ($profile_id == 0) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        if (isFriend($user->id, $profile->id) != false) return j_print(["type" => -1, "msg" => "already_added"]);
        $last_id = $db->friend_list->find([], [
            'sort' => ['id' => -1],
            'limit' => 1
        ])->toArray();
        $new_id = 0;
        if (count($last_id) == 0) $new_id = 1;
        else $new_id = 1 + (int)$last_id[0]['id'];
        $date = date("Y-m-d H:i:s");
        $insert = insert_in_db_one("friend_list", ["id" => (int)$new_id, "sender_id" => (int)$user->id, "recipient_id" => (int)$profile->id, "status" => 0, "date" => $date]);
        if (!empty($insert->uid)) j_print(["type" => 1, "msg" => "added"]);
        else  j_print(["type" => -4, "msg" => "insert error"]);
        break;
    case 'deletefriend':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $profile_id = (int)str_replace("/user/", "", $_POST['profile']);
        if ($profile_id == 0) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $isFriend = isFriend($user->id, $profile->id);
        if ($isFriend == false) return j_print(["type" => -1, "msg" => "already_deleted"]);
        $db->friend_list->deleteOne(["id" => (int)$isFriend->id]);
        j_print(["type" => 1, "msg" => "deleted", "id" => $isFriend->id, 'log' => $isFriend->obj]);
        break;
    case 'declineuser':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $profile_id = (int)$_POST['userid'];
        if ($profile_id == 0) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $isFriend = isFriend($user->id, $profile->id);
        if ($isFriend == false) return j_print(["type" => -1, "msg" => "profile_not_found"]);
        $db->friend_list->deleteOne(["id" => (int)$isFriend->id]);
        j_print(["type" => 1, "msg" => "declined"]);
        break;
    case 'acceptInFriend':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $profile_id = (int)$_POST['userid'];
        if ($profile_id == 0) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $profile = FindUserByID($profile_id);
        if ($profile == false) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $isFriend = isFriend($user->id, $profile->id);
        if ($isFriend == false) return j_print(["type" => -2, "msg" => "profile_not_found"]);
        $db->friend_list->updateOne(["id" => (int)$isFriend->id], ['$set' => ["status" => 1]]);
        j_print(["type" => 1, "msg" => "accepted"]);
        break;
    case 'getNotify':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        j_print(getUserNotify($user));
        break;
    case 'premium_save_video':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        if ((int)$user->group_id == 5) return j_print(["type" => "dont have access for add video"]);
        $array = $_POST['videos'];
        $link = $_POST['link'];
        if (strripos($link, "https://youtu.be/") === false) {
            return j_print(["type" => -2]); //invalid link
        }
        $db_videos = find_in_db_array("youtube_videos", ["user_id" => (int)$user->id]);
        $id = (int)$_POST['id'];
        if ($id == 0) return j_print(["type" => "not found video id!"]); // https://youtu.be/LXPdMMtKb34
        if (!isValidYTVideo(str_replace("https://youtu.be/", "", $link))) return j_print(["type" => -3, "msg" => "Video in YT not found"]);
        if ($id == -1) {
            if (count($db_videos) > 4) {
                return j_print(["type" => "limit videos"]);
            } else {
                $new_id = (int)get_new_id_for_db("youtube_videos");
                insert_in_db_one("youtube_videos", ["id" => $new_id, "user_id" => (int)$user->id, "link" => $link]);
                j_print(["type" => 1]);
            }
        }
        if ($id > 0) {
            $find = find_in_db_array("youtube_videos", ["user_id" => (int)$user->id, "id" => $id]);
            if (count($find) == 0) return j_print(["type" => "error get video id"]);
            $updateID = (int)$find[0]['id'];
            $db->youtube_videos->updateOne(["id" => (int)$updateID], ['$set' => ["link" => $link]]);
            j_print(["type" => 1]);
        }
        break;
    case 'create_ticket':
        include(__DIR__ . "/mongo/mainMongo.php");
        $text = $_POST['text'];
        $group = (int)$_POST['group'];
        $user = user_auth();
        if ($group < 1) return j_print(["type" => -2, "group ticket not found"]);
        if (strlen($text) < 10) return j_print(["type" => -1, "minimum 10 characters"]);
        if ($user == false) return print("dont have access!");
        $user_tickets = find_in_db_array("tickets", ['owner_id' => (int)$user->id]);
        $find_group = find_in_db_array("ticketsGroups", ["id" => (int)$group]);
        if ((int)count($find_group) == 0) return j_print(["type" => -2, "group ticket not found"]);
        if ((int)count($user_tickets) > 0 && ((int)time() - (int)$user_tickets->date) < 600) return j_print(["type" => 0, "msg" => "limit 10 min for create new tickets"]);
        $new_ticket_id = (int)get_new_id_for_db("tickets");
        $last_ticket = insert_in_db_one("tickets", ['id' => $new_ticket_id, "owner_id" => (int)$user->id, "status" => 0, "group_id" => $group, "text" => $text, "date" => (int)time()]);
        j_print(["type" => 1, "successfully created"]);

        break;
    case 'ticketSendMessage':
        include(__DIR__ . "/mongo/mainMongo.php");
        $text = $_POST['text'];
        $ticket_id = (int)$_POST['ticket'];
        $user = user_auth();
        if ($ticket_id < 1) return j_print(["type" => -2, "msg" => "ticket not found < 1", "ticket:" => $ticket_id]);
        if (strlen($text) < 3) return j_print(["type" => -1, "msg" => "minimum 3 characters"]);
        if ($user == false) return print("dont have access!");
        $user_tickets = find_in_db_array("tickets", ["id" => (int)$ticket_id, 'owner_id' => (int)$user->id]);
        if ((int)count($user_tickets) == 0) return j_print(["type" => -2, "msg" => "ticket not found"]);
        $ticket = (object)$user_tickets[0];
        $lastMsges = $db->MsgInTickets->find(['user_id' => (int)$user->id, "ticket_id" => (int)$ticket->id], [
            'sort' => ['id' => -1],
            'limit' => 1
        ])->toArray();
        if (count($lastMsges) > 0 && ((int)time() - (int)$lastMsges[0]['date']) < 120) return j_print(["type" => 0, "msg" => "limit 10 min for answer ticket"]);
        $new_msg_id = (int)get_new_id_for_db("MsgInTickets");
        insert_in_db_one("MsgInTickets", ["id" => (int)$new_msg_id, "ticket_id" => (int)$ticket->id, "user_id" => (int)$user->id, "text" => $text, "date" => (int)time()]);
        j_print(["type" => 1, "successfully send"]);
        break;
    case 'ticketSendMessageStaff':
        include(__DIR__ . "/mongo/mainMongo.php");
        $text = $_POST['text'];
        $ticket_id = (int)$_POST['ticket'];
        $user = user_auth();
        $groupName = "";
        if ($ticket_id < 1) return j_print(["type" => -2, "msg" => "ticket not found < 1", "ticket:" => $ticket_id]);
        if (strlen($text) < 3) return j_print(["type" => -1, "msg" => "minimum 3 characters"]);
        if ($user == false || !in_array((int)$user->group_id, $staff_access_groups)) return print("dont have access!");
        $user_tickets = find_in_db_array("tickets", ["id" => (int)$ticket_id]);
        if ((int)count($user_tickets) == 0) return j_print(["type" => -2, "msg" => "ticket not found"]);
        $ticket = (object)$user_tickets[0];
        $lastMsges = $db->MsgInTickets->find(['user_id' => (int)$user->id, "ticket_id" => (int)$ticket->id], [
            'sort' => ['id' => -1],
            'limit' => 1
        ])->toArray();
        if (count($lastMsges) > 0 && ((int)time() - (int)$lastMsges[0]['date']) < 10) return j_print(["type" => 0, "msg" => "limit 10 sec for answer ticket"]);
        $new_msg_id = (int)get_new_id_for_db("MsgInTickets");
        switch ($user->group_id) {
            case 1:
                $groupName = "Администратор";
                break;
            case 2:
                $groupName = "Модератор";
                break;
            case 3:
                $groupName = "Саппорт";
                break;
            case 4:
                $groupName = "Премиум";
                break;
            case 5:
                $groupName = "Пользователь";
                break;
        }
        insert_in_db_one("MsgInTickets", ["id" => (int)$new_msg_id, "ticket_id" => (int)$ticket->id, "user_id" => (int)$user->id, "text" => $text, "date" => (int)time()]);
        j_print(["type" => 1, "msg" => "successfully send", "class" => "tickets_group_id_" . $user->group_id, "group" => $groupName, "avatar" => $user->avatar, "username" => $user->username, "text" => htmlspecialchars($text)]);
        break;
    case 'ticketChangeStatusStaff':
        include(__DIR__ . "/mongo/mainMongo.php");
        $ticket_id = (int)$_POST['ticket'];
        $user = user_auth();
        $status = (int)$_POST['status'];
        if ($status != 1 && $status != 2) return j_print(["type" => -3, "msg" => "status error"]);
        if ($ticket_id < 1) return j_print(["type" => -2, "msg" => "ticket not found < 1", "ticket:" => $ticket_id]);
        if ($user == false || !in_array((int)$user->group_id, $staff_access_groups)) return print("dont have access!");
        $ticket = (object)find_in_db_array("tickets", ["id" => (int)$ticket_id])[0];
        if ($status == 1) {
            $db->tickets->updateOne(["id" => (int)$ticket->id], ['$set' => ["status" => 0]]);
            return j_print(["type" => 2, "msg" => "set open ticket"]);
        } elseif ($status == 2) {
            $db->tickets->updateOne(["id" => (int)$ticket->id], ['$set' => ["status" => 1]]);
            return j_print(["type" => 1, "msg" => "set closed ticket"]);
        }
        break;
    case 'buy_software':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        $item_id = (int)$_POST['item'];
        if ($user == false) return print("dont have access!");
        if ($item_id == 0) return j_print(["type" => -3, "msg" => "item not found!"]);
        $item = find_in_db_array("market_items", ["id" => $item_id]);
        if ((int)count($item) == 0) return j_print(["type" => -3, "msg" => "item not found!"]);
        $item = (object)$item[0];
        if ($item->sum > $user->balance) return j_print(["type" => -1, "msg" => "not enough money!"]);
        if ((int)$item->availability == 0) return j_print(["type" => 0, "msg" => "not availability for buy!"]);
        break;
    case 'resetPassword':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user != false) return print("dont have access!");
        $mail = $_POST['mail'];
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) return j_print(["type" => -2, "msg" => "Mail is not correct"]);
        $findMail = find_in_db_array("users", ["mail" => $mail]);
        if ((int)count($findMail) == 0)  return j_print(["type" => -3, "msg" => "User not found", "count" => count($findMail), "mail" => $mail]);
        $thisUser = (object)$findMail[0];
        $newPassword = gen_str(20);
        $md5Password = md5($newPassword);
        mail_send_resetpassword($thisUser->mail, $newPassword);
        update_in_db_one("users", ["id" => (int)$thisUser->id], ["password" => $md5Password]);
        return j_print(["type" => 1, "msg" => "Successfully!"]);
        break;
    case 'premium__saveMyDesc':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        if ((int)$user->group_id == 5) return j_print(["type" => "dont have access for update Desc"]);
        $desc = $_POST['desc'];
        $descLen = (int)strlen($desc);
        if ($descLen < 2)  return j_print(["type" => -1, 'msg' => "Min len 2"]);
        if ($descLen > 255) return j_print(["type" => -2, 'msg' => "Maz len 255"]);
        update_in_db("users", ["id" => (int)$user->id], ['desc' => $desc]);
        j_print(['type' => 1, "msg" => "Successfully!"]);
        break;
    case 'premium__saveMyBackground':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        if ((int)$user->group_id == 5) return j_print(["type" => "dont have access for update Background"]);
        $back_id = (int)$_POST['background'];
        if ($back_id == 0) return j_print(['type' => -1, "msg" => "Background not found"]);
        $findThisBackground = find_in_db_array('backgroundsList', ['id' => $back_id]);
        if ((int)count($findThisBackground) == 0) return j_print(['type' => -1, "msg" => "Background not found"]);
        update_in_db("users", ["id" => (int)$user->id], ['background_id' => $back_id]);
        j_print(['type' => 1, "msg" => "Successfully!"]);
        break;
    case 'get_maps_in_mode':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return print("dont have access!");
        $mode = (int)$_POST['mode'];
        if ($mode != 3) {
            j_print(["type" => 1, "maps" => LIST_2V2_MAPS]);
        } else {
            j_print(["type" => 1, "maps" => LIST_5V5_MAPS]);
        }
        break;
    case 'premium__buy_get_link':
        include(__DIR__ . "/mongo/mainMongo.php");
        $user = user_auth();
        if ($user == false) return j_print(['type' => -1, 'msg' => "dont have access!"]);
        $plan = (int)$_POST['plan'];
        switch ($plan) {
            case 1:
                return j_print(['type' => 1, "url" => payok_create_payment(199, $user->id)]);
                break;
            case 2:
                return j_print(['type' => 1, "url" => payok_create_payment(470, $user->id)]);
                break;
            case 3:
                return j_print(['type' => 1, "url" => payok_create_payment(550, $user->id)]);
                break;
            case 4:
                return j_print(['type' => 1, "url" => payok_create_payment(999, $user->id)]);
                break;
            default:
                return j_print(["type" => -2, "msg" => 'Plan not found!']);
                break;
        }
        break;
    default:
        print("нахуй с пляжа!");
        break;
}
