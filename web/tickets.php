<?

include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
if ($user == false) {
    header("Location: " . __HOST__);
    exit();
}
$find = $db->tickets->find(["owner_id" => (int)$user->id], ['sort' => ['id' => -1]])->toArray();
//BloodGodz league
?>

<head>
    <?
    include(__DIR__ . "/sections/head.php");
    ?>
</head>

<body cz-shortcut-listen="true">
    <div id="app">
        <div class="appContent">
            <?
            include(__DIR__ . "/sections/header.php");
            ?>
            <div page='tickets'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/support/tickets.png" alt="">
                        <div class="font-bold">#ТИКЕТЫ</div>
                    </div>

                    <div class="main__tickets__box">
                        <div>
                            <div>Максимально подробно опишите свою проблему поддержке. Ответим максимально быстро.</div>
                            <div><select name="" id="">
                                    <?
                                    $groups = find_in_db_array("ticketsGroups", []);
                                    for ($i = 0; $i < count($groups); $i++) {
                                        $group = (object)$groups[$i];
                                        echo '<option value="' . $group->id . '">' . $group->name . '</option>';
                                    }
                                    ?>
                                </select></div>
                            <div>
                                <textarea name="" id="" cols="30" rows="5" placeholder="Опишите свою проблему..."></textarea>
                            </div>
                            <div>
                                <button>Создать тикет</button>
                            </div>
                        </div>
                    </div>
                    <div class="main__tickets_history">
                        <div>
                            <div>Ваши тикеты:</div>
                            <div>
                                <div>
                                    <div>Дата</div>
                                    <div>Статус тикета</div>
                                    <div>Тема тикета</div>
                                </div>
                                <div <? if (count($find) > 5) echo "style='height: 20rem;'";
                                        else echo "style='height: auto;'"; ?>>
                                    <?

                                    if (count($find) !== 0) {
                                        for ($i = 0; $i < count($find); $i++) {
                                            $ticket = (object)$find[$i];
                                            $group = find_in_db("ticketsGroups", ["id" => (int)$ticket->group_id]);
                                            $msges = find_in_db_array("MsgInTickets", ["ticket_id" => (int)$ticket->id]);
                                            echo "<div ticket='" . $ticket->id . "'>";
                                            echo "<div ticketid='" . $ticket->id . "'>";
                                            echo "<div>" . date("F j, Y, g:i a", $ticket->date) . "</div>";
                                            switch ($ticket->status) {
                                                case 0:
                                                    echo "<div>Создан</div>";
                                                    break;
                                                case 1:
                                                    echo "<div>Закрыт</div>";
                                                default:
                                                    break;
                                            }

                                            echo "<div>" . $group->name . "</div>";
                                            echo "<div><i class='fas fa-angle-down' direct='down' class_angle='fas fa-angle-'></i></div>";
                                            echo "</div>";
                                            echo "<div class='ticket_more_info'>";
                                            echo "<div class='ticket_msges' ";
                                            if (count($msges) == 0 || $ticket->status == 1) echo 'style="padding-bottom: 1rem;">';
                                            else echo '>';
                                            echo "<div class='reverse'>";
                                            echo "<div>";
                                            echo "<img src='$user->avatar'>";
                                            echo "</div>";

                                            echo "<div>";
                                            echo "<div>$user->username</div>";
                                            echo "<div>$ticket->text</div>";
                                            echo "</div>";
                                            echo "</div>";
                                            if (count($msges) != 0) {
                                                for ($j = 0; $j < count($msges); $j++) {
                                                    $current_msg = (object)$msges[$j];
                                                    $current_msg_user = findUserByID((int)$current_msg->user_id);
                                                    if ($current_msg->user_id == $user->id)  echo "<div class='reverse'>";
                                                    else  echo "<div>";
                                                    echo "<div>";
                                                    echo "<img src='$current_msg_user->avatar'>";
                                                    echo "</div>";

                                                    echo "<div>";
                                                    switch ((int)$current_msg_user->group_id) {
                                                        case 1:
                                                            echo "<div class='tickets_group_id_" . $current_msg_user->group_id . "'>$current_msg_user->username (Администратор)</div>";
                                                            break;
                                                        case 2:
                                                            echo "<div class='tickets_group_id_" . $current_msg_user->group_id . "'>$current_msg_user->username (Модератор)</div>";
                                                            break;
                                                        case 3:
                                                            echo "<div class='tickets_group_id_" . $current_msg_user->group_id . "'>$current_msg_user->username (Саппорт)</div>";
                                                            break;
                                                        default:
                                                            echo "<div>$current_msg_user->username</div>";
                                                            break;
                                                    }
                                                    echo "<div>$current_msg->text</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                            }

                                            echo "</div>";
                                            if ($ticket->status == 0) :
                                                echo "<div class='ticket_sendmsg'>";
                                                echo '<textarea name="" id="" cols="30" rows="3" placeholder="Введите сообщение"></textarea>';
                                                echo '<button sendMessage="' . $ticket->id . '"><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19.2005 0.155459C19.0461 0.00625628 18.8145 -0.0396225 18.6119 0.0387054L1.07549 6.81675C0.874842 6.8943 0.740481 7.07894 0.732768 7.28766C0.725091 7.49635 0.845591 7.68975 1.04002 7.78087L7.95518 11.0216L11.309 17.7037C11.4005 17.8859 11.5915 18.0009 11.7999 18.0009C11.8064 18.0009 11.8129 18.0008 11.8194 18.0006C12.0353 17.9931 12.2265 17.8633 12.3067 17.6694L19.3213 0.724216C19.4023 0.528326 19.3549 0.304627 19.2005 0.155459ZM2.62772 7.35274L16.5486 1.97212L8.25235 9.98868L2.62772 7.35274ZM11.7521 16.1695L9.02414 10.7344L17.3205 2.71785L11.7521 16.1695Z" fill="#FEFEFE"></path>
                                            </svg></button>';
                                                echo "</div>";
                                            endif;
                                            echo "</div>";


                                            echo "</div>";
                                        }
                                    }

                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?
            include(__DIR__ . "/sections/footer.php");
            include(__DIR__ . "/sections/user_rightbar.php");
            ?>
        </div>
    </div>
    <?
    if ($user == false) {
        include(__DIR__ . "/sections/popup_auth.php");
        echo '<script src="/js/' . $configIncludes->auth . '"></script>';
    } else {
        echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
    }
    ?>
    <script>
        $(".main__tickets__box").find("button").click(function() {
            $(this).prop("disabled", true);
            var handler_id = "create_ticket";
            var group = $(".main__tickets__box").find("select").val();
            var text = $(".main__tickets__box").find("textarea").val();
            $.ajax({
                type: 'post',
                url: '/handler.php',
                data: {
                    handler_id,
                    group,
                    text
                },
                success: function(result) {
                    var json = JSON.parse(result);
                    switch (json.type) {
                        case 1:
                            $.notify('Успешно создан тикет!', {
                                color: "#fff",
                                background: "#20D67B"
                            });
                            $(".main__tickets__box").find("textarea").val("")
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                            break;
                        case 0:
                            $.notify("Новые тикеты можно создать только через 10 минут", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                        case -1:
                            $.notify("Минимально 10 символов для тикета", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            $(this).prop("disabled", false);
                            break;
                        default:
                            $.notify(json.msg, {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                    }
                }
            });
        });
        $("[ticket] > div:first-child").click(function(e) {
            var val = $(this).attr("ticketid");
            var activeTicket = $("[active_ticket]").attr("ticket");
            var angle = $("[ticket]").find("i[direct]").attr("class_angle");
            if (activeTicket != val) {
                $("[active_ticket]").removeAttr("active_ticket");

                $("[active_ticket]").find("i[direct]").attr("class", $("[ticket]").find("i[direct]").attr("class_angle") + "down").attr("direct", "down");
                $("[ticket=" + val + "]").attr("active_ticket", "").find("i[direct]").attr("class", angle + "up").attr("direct", "up");
                $(".ticket_more_info").slideUp('slow');
                $("[ticket=" + val + "]").find(".ticket_more_info").slideDown('slow');
            } else {
                $("[ticket=" + val + "]").find("i[direct]").attr("class", angle + "down").attr("direct", "down");
                $("[active_ticket]").removeAttr("active_ticket").find(".ticket_more_info").slideUp('slow');
            }
        });
        $(".ticket_sendmsg > [sendMessage]").click(function() {
            $(this).prop("disabled", true);
            var val = $(this).attr("sendMessage");
            var handler_id = "ticketSendMessage";
            var ticket = val;
            var text = $("[ticket=" + val + "]").find(".ticket_sendmsg > textarea").val();
            $.ajax({
                type: 'post',
                url: '/handler.php',
                data: {
                    handler_id,
                    ticket,
                    text
                },
                success: function(result) {
                    var json = JSON.parse(result);
                    switch (json.type) {
                        case 1:
                            $.notify('Сообщение в тикет успешно отправлено!', {
                                color: "#fff",
                                background: "#20D67B"
                            });
                            $("[ticket=" + val + "]").find(".ticket_sendmsg > textarea").val("");
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                            break;
                        case 0:
                            $.notify("Одно сообщение раз в 2 минуты", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                        case -1:
                            $.notify("Минимально 3 символов для отправки сообщения", {
                                color: "#fff",
                                background: "#D44950"
                            });
                            $(this).prop("disabled", false);
                            break;
                        default:
                            $.notify(json.msg, {
                                color: "#fff",
                                background: "#D44950"
                            });
                            break;
                    }
                }
            });
        });
    </script>
</body>