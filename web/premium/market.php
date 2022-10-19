<?
include("../mongo/mainMongo.php");
$user = user_auth();
if ($user == false || $user->group_id == 5) {
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
                        <img src="/files/images/market/market.png" alt="">
                        <div class="font-bold">#МАРКЕТ</div>
                    </div>
                    <div class="market__sort">
                        <div>
                            <div>
                                <select name="" id="">
                                    <option value="-1" disabled selected>Сортировать по цене</option>
                                    <option value="DESC">По возрастанию</option>
                                    <option value="ASC">По убыванию</option>
                                </select>
                            </div>
                           
                            <div > <input type="text" placeholder="Поиск..."><i class="fas fa-search"></i>
                                
                            </div>
                            <div><span><?= $user->balance; ?></span> <i class="far fa-usd-circle"></i></div>
                        </div>
                    </div>
                    <div class="marketbox">
                        <div>
                            <?
                            $items = find_in_db_array("market_items", []);
                            for ($i = 0; $i < count($items); $i++) {
                                $item = (object)$items[$i];
                                echo "<div style='max-width: 15%;'>";
                                echo "<div><img src='" . $item->img . "' alt=''></div>";
                                echo "<div>{$item->name}</div>";
                                echo "<div>{$item->sum} <i class='far fa-usd-circle'></i></div>";
                                echo "<div>Наличие: {$item->availability}</div>";
                                echo "<div><button buyitem='{$item->id}'>Приобрести</button></div>";
                                echo "</div>";
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="premium_userbar">
                <div>
                    <div><a href="/premium">Premium</a></div>
                    <div class="active"><a href="market">Market</a></div>
                    <div style="display: none;"><a href="skinchanger">SkinChanger</a></div>
                    <div><a href="lobby">Custom Lobby</a></div>
                </div>
            </div>
            <?
            include("../sections/footer.php");
            ?>
        </div>
    </div>
    <script>
        $("[buyitem]").click(function() {
            $(this).prop('disabled', true);
            var item = $(this).attr("buyitem");
            var handler_id = "buy_software";
            $.ajax({
                type: 'post',
                url: '/handler.php',
                data: {
                    handler_id,
                    item
                },
                success: function(result) {
                    var json = JSON.parse(result);
                    switch (json.type) {
                        case 1:
                            $.notify('Успешно сохранена новая почта!', {
                                color: "#fff",
                                background: "#20D67B"
                            });
                            break;
                        case 0:
                            $.notify("Закончился товар, попробуйте позднее", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                        case -1:
                            $.notify("Недостаточно монет для покупки", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                        case -3:
                            $.notify("Софт не найден", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                        default:
                            $.notify(json.msg, {
                                color: "#fff",
                                background: "#D44950"
                            });
                            $(this).prop('disabled', false);
                            break;
                    }
                }
            });
        });
    </script>
</body>

</html>