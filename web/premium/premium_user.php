<?
if ($user->group_id == 5) {
    header("Location: " . __HOST__ . "/premium");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#000000">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&amp;family=Roboto&amp;display=swap">
    <title>BloodGodz league</title>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/notify/prettify.css">
    <link rel="stylesheet" href="/notify/notify.css">
    <script src="/notify/prettify.js"></script>
    <script src="/notify/notify.js"></script>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous">
    <? include("../css_orig.php"); ?>
</head>

<body>
    <div id="app">
        <div class="appContent">
            <?
            include("../sections/header.php");
            ?>
            <div page='premium_main'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/premium/tagprem.png" alt="">
                        <div class="font-bold">#PREMIUM</div>
                    </div>
                    <div class="premium_info">
                        <div>
                            <div>Информация</div>
                            <div>
                                <div>Подписка : <span><?= (int)(($user->group_end - time()) / 86400) ?> дней</span> <span><a href="<?= payok_create_payment(199, $user->id); ?>"><button>ПРОДЛИТЬ</button></a></span></div>
                                <div>Буст ELO : <span>+1.5%</span></div>
                                <div>Баланс : <span><?= $user->balance; ?> <i class="far fa-usd-circle"></i></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="premium_missions">
                        <div>
                            <div>Задания <span style="opacity: .5;">(обновляются раз в 24 часа)</span></div>
                            <div>
                                <div><b>Выполнение</b></div>
                                <div><b>Награды</b></div>
                                <div><b>Задание</b></div>
                                <div><b>Выполнено</b></div>
                            </div>
                            <div>
                                <?
                                $all_missions = find_in_db_array("missions", ["visible"=> 1]);
                                for ($i = 0; $i < count($all_missions); $i++) {
                                    $date = (string)date("Y/m/d");
                                    $userState = findPremiumUserStateMissons($user->id, $date);
                                    $mission = (object)$all_missions[$i];
                                    $about = getPremiumStateAboutMission($mission);
                                    $missin_key = $about->key;
                                    $isMissionConfirmed = (int)$about->need <= (int)$userState->$missin_key;
                                    $confirmed_mission = find_in_db_array("users_missions", ["user_id" => (int)$user->id, "mission_id" => $mission->id]);
                                    echo '<div>';
                                    if (!$isMissionConfirmed) echo '<div><i class="far fa-times-circle"></i></div>';
                                    else echo '<div><i class="far fa-check-circle"></i></div>';
                                    echo '<div>+' . $mission->sum . ' <i class="far fa-usd-circle"></i></div>';
                                    echo '<div>' . $mission->name . '</div>';
                                    echo '<div>' . $userState->$missin_key . "/" .  $about->need.  "</div>";
                                    echo '</div>';
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                    <div class="history_orders">
                        <div>
                            <div>История операций</div>
                            <div>
                                <div><b>Дата</b></div>
                                <div><b>Операция</b></div>
                                <div><b>Стоимость</b></div>
                                <div><b>Способ оплаты</b></div>
                            </div>
                            <div>
                                <?
                                $history = $db->orders->find(['user_id' => (int)$user->id], [
                                    'sort' => ['id' => -1]
                                ])->toArray();
                                for ($i = 0; $i < count($history); $i++) {
                                    $order = (object)$history[$i];
                                    echo "<div>";

                                    echo "<div>" . date("d M Y - h:i", $order->date) . "</div>";
                                    echo "<div>" . "Продление Премиум" . "</div>";
                                    echo "<div>" . $order->sum . "р</div>";
                                    echo "<div>" . $order->payment . "</div>";

                                    echo "</div>";
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="premium_userbar">
                <div>
                    <div class="active"><a href="#">Premium</a></div>
                    <div><a href="market">Market</a></div>
                    <div  style="display: none;"><a href="skinchanger">SkinChanger</a></div>
                    <div><a href="lobby">Custom Lobby</a></div>
                </div>
            </div>
            <?
            include("../sections/footer.php");
            ?>
        </div>
    </div>
</body>

</html>