<?
include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
$room = getRoomById($_GET['id']);
if ($user->group_id != 1) {
    if ($user == false || $room == false || !IsuserInRoom($user->id, $room->id)) {
        header("Location: " . __HOST__);
        exit();
    }
}
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
            <div page='room'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/lobby/tag.png" alt="">
                        <div class="font-bold">#ЛОББИ ИГРЫ</div>
                        <div class="font-bold" titlelobby><?= __MODS_LIST__[$room->mode_id - 1] . " " . __REGION_LIST__[$room->region_id]; ?></div>
                        <div matchinfo>
                            <div><?= $room->team1 . "-" . $room->team2; ?></div>

                            <?
                            $room_state = (int)$room->status;
                            switch ($room_state) {
                                case 0:
                                    echo '<div>Выбор карты</div>';
                                    break;
                                case 1:
                                    echo '<div>Сервер создан, ожидание игроков</div>';
                                    break;
                                case 2:
                                    echo '<div>Игра идёт</div>';

                                    break;
                                case 4:
                                    echo '<div>Игра закончилась</div>';
                                    break;
                                case 5:
                                    echo '<div>Игра закончилась по техническим причинам</div>';
                                    break;
                                default:
                                    break;
                            }
                            ?>

                            <div style="display:none;" user_action_veto>[VETO] Выберите карту</div>
                            <div style="display:none;" user_action_confirm>[Game confirm] Подтвердите игру</div>
                        </div>
                    </div>
                    <div first>
                        <div>
                            <div>
                                <div>
                                    <div>
                                        <div></div>
                                        <div></div>
                                        <div>Рейтинг</div>
                                        <div>Коэф у/c</div>
                                        <div>K/D/A</div>
                                        <div></div>
                                    </div>
                                    <?
                                    $team1 = find_in_db_array("playersListInRoom", ["team" => "team1", "room_id" => (int)$room->id]);
                                    for ($i = 0; $i < count($team1); $i++) {
                                        $team = (object)$team1[$i];
                                        $player = FindUserByID($team->user_id);
                                        $avatar = $player->avatar;
                                        $rank = "rank_" . $room->mode_id;
                                        $rank = $player->$rank;
                                        $elo = "elo_" . $room->mode_id;
                                        $elo = $player->$elo;
                                        echo '<div userid="' . $player->id . '">';

                                        echo "<div><img src='{$avatar}' class='room_user_group_".$player->group_id."'>" . "</div>";
                                        echo "<div><a href='" . __HOST__ . "/user/{$player->id}'>" . subName8($player->username) . "</a></div>";
                                        echo "<div>$elo" . "</div>";
                                        echo "<div>" . mathKD($team->kills, $team->deaths) . "</div>";
                                        echo "<div>{$team->kills}/{$team->deaths}/{$team->assists}" . "</div>";
                                        echo "<div><img src='/files/images/levels/{$rank}.png'>" . "</div>";

                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <? if ($room->teamwin == 'team1') : ?>
                                    <div>
                                        <img src="/files/images/lobby/winteam.png" alt="" class="tag_teamwin">
                                    </div>
                                <? endif; ?>
                            </div>

                            <div>
                                <div mapslist>
                                    <?
                                    $room_state = (int)$room->status;
                                    switch ($room_state) {
                                        case 0:
                                            echo '<div>Выбор карты</div>';
                                            break;
                                        case 1:
                                        case 2:
                                            echo "<div>Карта: {$room->map} </div>";
                                            $server =  getServerForJoin($room->server_id);
                                            echo "<div><a href='{$server->link}'>" . $server->text . "</a></div>";
                                            if ((int)$room->mode_id > 1) {
                                                echo "<div class='discord_info_channel'>Чтобы зайти в <a href='https://discord.gg/UgAxsafnnd'>Discord</a> к тимейтам в канале games/joinrooms напишите /voice</div>";
                                            }
                                            break;
                                        case 4:
                                            echo "<div>Выиграла команда: {$room->teamwin}</div>";
                                            break;
                                        default:
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <div>
                                        <div></div>
                                        <div></div>
                                        <div>Рейтинг</div>
                                        <div>Коэф у/c</div>
                                        <div>K/D/A</div>
                                        <div></div>
                                    </div>
                                    <?
                                    $team2 = find_in_db_array("playersListInRoom", ["team" => "team2", "room_id" => (int)$room->id]);
                                    for ($i = 0; $i < count($team2); $i++) {
                                        $team = (object)$team2[$i];
                                        $player = FindUserByID($team->user_id);
                                        $rank = "rank_" . $room->mode_id;
                                        $rank = $player->$rank;
                                        $elo = "elo_" . $room->mode_id;
                                        $elo = $player->$elo;
                                        $avatar = $player->avatar;


                                        echo '<div userid="' . $player->id . '">';

                                        echo "<div><img src='{$avatar}' class='room_user_group_".$player->group_id."'>" . "</div>";
                                        echo "<div><a href='" . __HOST__ . "/user/{$player->id}'>" . subName8($player->username) . "</a></div>";
                                        echo "<div>$elo" . "</div>";
                                        echo "<div>" . mathKD($team->kills, $team->deaths) . "</div>";
                                        echo "<div>{$team->kills}/{$team->deaths}/{$team->assists}" . "</div>";
                                        echo "<div><img src='/files/images/levels/{$rank}.png'>" . "</div>";

                                        echo '</div>';
                                    }

                                    ?>
                                </div>
                                <? if ($room->teamwin == 'team2') : ?>
                                    <div>
                                        <img src="/files/images/lobby/winteam.png" alt="" class="tag_teamwin">
                                    </div>
                                <? endif; ?>
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
    <noscript><?= $user->token; ?></noscript>
    <?
    if ($user == false) {
        echo '<script src="/js/' . $configIncludes->auth . '"></script>';
    } else {
        echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
    }
    ?>
    <script>
        var socket_closed = false;
        const room = new WebSocket('wss://bloodgodz.com/roomws');
        room.onopen = function() {
            console.log('Room status: Connected');
            room.send(JSON.stringify({
                action: 'userConnect',
                data: $("body > noscript").text(),
                room: location.pathname
            }));
        };
        room.onmessage = function(message) {
            var json = JSON.parse(message.data);
            switch (json.data) {
                case 'start_veto_choose_map':
                    $("[user_action_veto]").show("slow");
                    $("[mapslist] > div").remove()
                    var maps = json.maps
                    for (let i = 0; i < maps.length; i++) {
                        $("[mapslist]").append("<div onclick=" + '"kick_map(this)"' + ">" + maps[i] + "</div>");
                    }
                    break;
                case 'update_room_page':
                    location.reload();
                    break;
                case 'kicked_map':
                    var map = json.map;
                    var userid = json.new_veto_player;
                    $(".veto_player").removeClass("veto_player");
                    $("[mapslist] > div:contains('" + map + "')").hide('slow');
                    setTimeout(() => {
                        $("[mapslist] > div:contains('" + map + "')").remove();
                    }, 1000);
                    $.notify("Карта " + map + " была исключена", {
                        color: "#fff",
                        background: "#D44950"
                    });
                    $('[userid="' + userid + '"] > div:nth-child(2)').addClass("veto_player");
                    break;
                case 'server_created_for_match':
                    anim_text("[matchinfo] > div:nth-child(2)", "Сервер создан, ожидание игроков");
                    //  $("[matchinfo] > div:nth-child(2)").text("Сервер создан, ожидание игроков")
                    $("[mapslist]").append("<div><a href='steam://connect/" + json.ip + ":" + json.port + "'>connect " + json.ip + ":" + json.port + "</a></div>");
                    break;
                case 'map_is_selected':
                    var map = json.map;
                    var map_kicked = json.map_kicked;
                    $("[mapslist] > div:contains('" + map_kicked + "')").hide('slow');
                    setTimeout(() => {
                        $("[mapslist] > div:contains('" + map_kicked + "')").remove();
                    }, 1000);
                    $.notify("Карта " + map + " была выбрана", {
                        color: "#fff",
                        background: "#00FF00"
                    });
                    $(".veto_player").removeClass("veto_player");
                    break;
                case 'discord_info':
                    $("[mapslist]").append("<div class='discord_info_channel'>Чтобы зайти в <a href='https://discord.gg/UgAxsafnnd'>Discord</a> к тимейтам в канале games/joinrooms напишите /voice</div>");
                    break;
                case 'start_veto':
                    $.notify("Вето запускается", {
                        color: "#fff",
                        background: "#00D49B"
                    });
                    $("[mapslist] > div").remove()
                    var maps = json.maps
                    var userid = json.user_veto;
                    for (let i = 0; i < maps.length; i++) {
                        $("[mapslist]").append("<div>" + maps[i] + "</div>");
                    }
                    $('[userid="' + userid + '"] > div:nth-child(2)').addClass("veto_player");

                    break;
                case 'updateStats':
                    var players = json.players;
                    var teamscore = json.teamscore;
                    anim_text("[matchinfo] > div:first-child", teamscore);
                    // $("[matchinfo] > div:first-child").text();
                    for (let i = 0; i < players.length; i++) {
                        var player = players[i];
                        var target = '[userid="' + player.userid + '"]';
                        anim_text(target + " > div:nth-child(4)", player.kd);
                        anim_text(target + " > div:nth-child(5)", player.stats);
                        // $(target + " > div:nth-child(4)").text(player.kd);
                        //  $(target + " > div:nth-child(5)").text(player.stats);
                    }
                    break;
                case 'updateAfterKniferound':
                    //   $("[matchinfo] > div:nth-child(2)").text("Игра идёт");
                    anim_text("[matchinfo] > div:nth-child(2)", "Игра идёт");
                    break;
                case 'updateAfterTeamWin':
                    $("[matchinfo] > div:nth-child(2)").text("Игра закончилась"); //here
                    $("[mapslist] > div").remove();
                    anim_html("[mapslist]", "<div>Выиграла команда: " + json.teamwin + "</div>")
                    //   $("[mapslist]").append("<div>Выиграла команда: " + json.teamwin + "</div>");
                    break;
                default:
                    break;
            }
        };
        room.onclose = function(event) {
            location.reload();
        };
        $("[confirm-lobby]").click(() => {
            $("[popup=confirm_lobby]").hide("slow");
            $(this).prop("disabled", true);
            room.send(JSON.stringify({
                action: 'confirm_lobby',
                data: $("body > noscript").text()
            }));
        });
        setInterval(() => {
            if (socket_closed) return;
            room.send(JSON.stringify({
                action: 'userOnline',
                data: $("body > noscript").text()
            }));
        }, 30000);

        function kick_map(e) {
            if ($(e).attr("disabled") != undefined) return;
            $("[user_action_veto]").hide("slow");
            var map = $(e).text();
            room.send(JSON.stringify({
                action: 'veto_map',
                data: $("body > noscript").text(),
                map: map,
                room: location.pathname
            }));
            $("[mapslist] > div").attr('disabled', 'disabled');
        }
    </script>
</body>