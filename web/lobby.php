<?
include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
if ($user == false) {
    header("Location: " . __HOST__);
    exit();
}
if (!empty($user->active_room)) {
    header("Location: " . __HOST__ . "/room/" . (int)$user->active_room);
    exit;
}
$avatar = $user->avatar;

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

            <div page='lobby'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/lobby/tag.png" alt="">
                        <div class="font-bold">#ЛОББИ</div>
                        <div class="font-bold" titlelobby>1vs1 RU</div>
                        <div class="search_bar">
                            <div>
                                <div>Идет поиск игры.</div>
                                <div>пожалуйста, подождите</div>
                                <div>В поиске <span countsPlayer></span> игроков</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div>
                            <div choose_mods>
                                <div>
                                    <span>Выбрать регион сервера:</span>
                                    <select name="" id="" sel='region'>
                                        <? for ($i = 1; $i < count(__REGION_LIST__); $i++) {
                                            if ($i == 2) continue;
                                            echo "<option value='$i'>" . __REGION_LIST__[$i] . "</option>";
                                        } ?>
                                    </select>
                                </div>

                                <div>
                                    <span>Выбрать режим игры:</span>
                                    <select sel='mods'>
                                        <? for ($i = 0; $i < count(__MODS_LIST__); $i++) {
                                            echo "<option value='$i'>" . __MODS_LIST__[$i] . "</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div>
                                    <span>Выбрать Громкость Уведомлений:</span>
                                    <select volume_notify>
                                        <option value="0">0%</option>
                                        <option value="0.25">25%</option>
                                        <option value="0.5">50%</option>
                                        <option value="0.75">75%</option>
                                        <option value="1" selected>100%</option>
                                    </select>
                                </div>
                                <div class="time_start_lobby">00:00</div>
                            </div>
                            <div chat>
                                <div messages>

                                </div>
                            </div>
                            <div send_in_chat_commands>
                                <div>Команды чата (Кликабельны): </div>
                                <div> <button sendcommand='/casino'>Casino</button> <span> | </span> <button sendcommand='/roll'>Roll</button></div>
                            </div>
                            <div send_in_chat>
                                <div> <textarea name="" id="" cols="30" rows="3" placeholder="Ваше сообщение..." inputchat></textarea></div>
                                <div>
                                    <button sendmsginchat><i class="fas fa-paper-plane fa-2x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <? if ((int)$user->steam_id > 5000) : ?>
                        <div class="search_lobby_list">
                            <div>
                                <div class="search_tab">
                                    <div style="height: 4.5rem;"><button class="search_game" game_start_lobby>Поиск игры</button></div>
                                    <? if ($user->group_id != 5) : ?>
                                        <div><i class="far fa-times-circle"></i><input type="checkbox" id="autoacceptid"><label for="autoacceptid"> Автоматическое принятие игры</label></div>
                                    <? else : ?>
                                        <div style="opacity:.3;">Автоматическое принятие игры только для premium</div>
                                    <? endif; ?>
                                </div>
                                <div>
                                    <div class="list_players">
                                        <div class="leader">
                                            <div><img src="<?= $user->avatar; ?>" alt="">
                                                <div class="username"><span><?= $user->username; ?></span></div>
                                            </div>
                                            <div class="kickuser" title="Кикнуть пользователя с лобби"><i class="fas fa-user-times fa-3x"></i></div>
                                        </div>

                                        <? if (false) : ?> <div class="user">
                                                <div><img src="<?= $user->avatar; ?>" alt="">
                                                    <div class="username"><span><?= $user->username; ?></span></div>
                                                </div>
                                                <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                            </div>
                                        <? endif; ?>
                                        <div class="free_slot">
                                            <div><i class="fas fa-user-circle fa-4x"></i></div>
                                            <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                        </div>
                                        <div class="free_slot">
                                            <div><i class="fas fa-user-circle fa-4x"></i></div>
                                            <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                        </div>
                                        <div class="free_slot">
                                            <div><i class="fas fa-user-circle fa-4x"></i></div>
                                            <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                        </div>
                                        <div class="free_slot">
                                            <div><i class="fas fa-user-circle fa-4x"></i></div>
                                            <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                        </div>
                                        <div class="lobby_options">
                                            <div class="lobby_options__link"> <i class="far fa-copy fa-lg"></i></div>
                                            <div class="lobby_options__refresh"> <i class="fas fa-sign-out-alt fa-lg"></i></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <? else : ?>
                        <div style="text-align: center;">
                            <b> <a href="https://bloodgodz.com/settings">Для того чтобы играть нужно привязать стим в https://bloodgodz.com/settings</a></b>
                        </div>
                    <? endif; ?>
                </div>
            </div>
            <?
            include(__DIR__ . "/sections/footer.php");
            include(__DIR__ . "/sections/user_rightbar.php");
            ?>
        </div>
    </div>
    <div popup='confirm_lobby'>
        <div>
            <div>
                <div>
                    <h2>Принять вход в лобби?</h2>
                </div>
                <div confirm_list>
                </div>
                <div>
                    <button confirm-lobby>Принять</button>
                </div>
            </div>
        </div>
    </div>
    <div popup='invite_confirm_in_lobby' userid='0'>
        <div>
            <div>
                <div>
                    <h2>Принять вход в лобби к <span invite_username></span>?</h2>
                </div>
                <div>
                </div>
                <div>
                    <button confirm-invite='0'>Принять</button>
                    <button decline-invite='0'>Отклонить</button>
                </div>
            </div>
        </div>
    </div>
    <input type="text" id="copy_url" style="opacity: 0; position: absolute;top:0;">
    <noscript><?= $user->token; ?></noscript>
    <script>
        $("#autoacceptid").change(function() {
            var val = $(this).is(':checked');
            if (val)
                $(".search_tab > div:last-child > i").attr("class", "far fa-check-circle");
            else
                $(".search_tab > div:last-child > i").attr("class", "far fa-times-circle");
        });
    </script>
    <script>
        var connectID;
        var invervalTimeLobbyStart;
        var invervalTimeLobby = 0;
        var volume_notify = 1;
        if (localStorage.getItem('mode') != null) $("[sel=mods]").val(localStorage.getItem('mode'));
        if (localStorage.getItem('volume') != null) $("[volume_notify]").val(localStorage.getItem('volume'));

        $("body").click(function(e) {
            var popupRegister = $("[popup=register]").css("display") == "none";
            var popupLogin = $("[popup=login]").css("display") == "none";
            if (!popupRegister && $(e.target).attr("form-action") == undefined && $(e.target).prop("tagName").toLowerCase() != "button") {
                $("[popup=register]").removeClass("active_popup");
            }
            if (!popupLogin && $(e.target).attr("form-action") == undefined && $(e.target).prop("tagName").toLowerCase() != "button") {
                $("[popup=login]").removeClass("active_popup");
            }

        });
        $(".lobby_options__link").click(function(e) {
            var copyText = document.getElementById("copy_url");
            copyText.select();
            document.execCommand("copy");
            $.notify('Вы успешно скопировали ссылку на лобби!', {
                color: "#fff",
                background: "#20D67B"
            });
        });
        $("[volume_notify]").change(function() {
            volume_notify = $(this).val();
            localStorage.setItem('volume', volume_notify);
        });
        $("label[form-action]").click(function() {
            if ($(this).attr("disabled") != undefined) {
                return;
            }
            var target = $(this).attr("class");
            var agree = $(this).attr("agree");
            $(this).attr("disabled", "");
            if (agree == 'no') {
                target = "." + target + " > .checkbox";
                $(target).css("background", "red");
                $(this).attr("agree", 'yes');
            } else {
                target = "." + target + " > .checkbox";
                $(target).css("background", "transparent");
                $(this).attr("agree", 'no');
            }
            setTimeout(
                () => {
                    $(this).removeAttr("disabled")
                }, 500, $(this));
        });

        $("body").delegate(".lobby_options__refresh", "click", function() {
           C
        });
        $("body").delegate("[kickuseridlobby]", "click", function() {
            console.log("clicked kick");
            var val = $(this).attr("kickuseridlobby");
            lobby.send(JSON.stringify({
                action: 'kick_user_from_lobby',
                userid: val
            }));
        });

        $("[sel]").change(() => {
            var region = $("[sel='region']").find("[value=" + $("[sel='region']").val() + "]").text();
            var mods = $("[sel='mods']").find("[value=" + $("[sel='mods']").val() + "]").text();
            localStorage.setItem('mode', $("[sel='mods']").val());
            $("[titlelobby]").text(mods + " " + region);
        });
        const lobby = new WebSocket('wss://bloodgodz.com/lobbyws');
        const chatws = new WebSocket('wss://bloodgodz.com/chatws');
        lobby.onopen = function() {
            console.log('Lobby status: Connected');
            console.log(location.pathname);
            lobby.send(JSON.stringify({
                action: 'userConnect',
                data: $("body > noscript").text(),
                path: location.pathname
            }));
        };


        lobby.onclose = () => {
            location = location.origin + "/lobby";
        };
        chatws.onopen = function() {
            console.log('Chat status: Connected');
            chatws.send(JSON.stringify({
                action: 'userConnect',
                data: $("body > noscript").text()
            }));
        };

        $("[game_start_lobby]").click(function(e) {
            var className = $(this).attr('class');
            $(this).prop("disabled", true);
            if (className == 'search_game') {
                var audio = new Audio('/files/music/start_search.mp3');
                audio.volume = volume_notify;
                audio.play();
                invervalTimeLobbyStart = setInterval(() => {
                    invervalTimeLobby += 1;
                    var mins = parseInt(invervalTimeLobby / 60);
                    var seconds = invervalTimeLobby;
                    if (seconds > 59) seconds = invervalTimeLobby - (mins * 60);
                    if (seconds < 10) seconds = "0" + seconds;
                    $(".time_start_lobby").text(mins + ":" + seconds);
                }, 1000);
                $(this).attr("class", "exit_search");
                anim_text(this, "Остановить поиск");
                setTimeout(() => $('[game_start_lobby]').prop("disabled", false), 1000);
                $("[sel='mods']").prop("disabled", true);
                $("[sel='region']").prop("disabled", true);
                $(".search_bar").animate({
                    opacity: 1
                }, 400);
                lobby.send(JSON.stringify({
                    action: 'search_lobby',
                    region: $("[sel='region']").find("[value=" + $("[sel='region']").val() + "]").text(),
                    mode: $("[sel='mods']").find("[value=" + $("[sel='mods']").val() + "]").text()
                }));
                $("[confirm_list] > div").remove();
                if ($("[sel='mods']").val() == 0) {
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                }
                if ($("[sel='mods']").val() == 1) {
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                }
                if ($("[sel='mods']").val() == 2) {
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                }
                if ($("[sel='mods']").val() == 3) {
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                    $("[confirm_list]").append('<div><i class="fas fa-user fa-4x"></i></div>');
                }
            } else if (className == 'exit_search') {
                lobby.send(JSON.stringify({
                    action: 'exit_in_search'
                }));
                clearInterval(invervalTimeLobbyStart);
                invervalTimeLobby = 0;
                $(this).attr("class", "search_game");
                $(".time_start_lobby").hide("slow");;
                $(".search_bar").animate({
                    opacity: 0
                }, 400);
                anim_text(this, "Поиск игры");
                setTimeout(() => $('[game_start_lobby]').prop("disabled", false), 1000);
                $("[sel='mods']").prop("disabled", false);
                $("[sel='region']").prop("disabled", false);
            }


        });
        lobby.onmessage = function(message) {
            try {
                var json = JSON.parse(message.data);
                switch (json.action) {
                    case 'alert':
                        alert(json.msg);
                        $.notify(json.msg, {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'users_online_connect':
                        var users = json.users;
                        for (let i = 0; i < users.length; i++) {
                            let user = users[i];
                            $("[friend='" + user + "']").addClass("online");
                            $("[history_player='" + user + "']").addClass("online");
                        }
                        break;
                    case 'lobby_started_search':
                        $('[sel="mods"]').val($('[sel="mods"]').find("option:contains(" + json.mode + ")").val());
                        $('[sel="region"]').val($('[sel="region"]').find("option:contains(" + json.region + ")").val());
                        $("[game_stop_search]").prop("disabled", true);
                        $("[game_search]").hide();
                        $("[game_stop_search]").hide();
                        $("[game_search]").prop("disabled", true);
                        $(".search_bar").animate({
                            opacity: 1
                        }, 400);
                        break;
                    case 'reload_page':
                        location = location.origin + "/lobby";
                        break;
                    case 'connection_id_failed':
                        location = location.origin + "/lobby";
                        break;
                    case 'not connected steam':
                        $.notify("Пользователь " + json.username + " не привязал стим аккаунт!", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'kickeduserfromlobby':
                        var kicked = json.kicked;
                        if (kicked) {
                            $.notify("Вы исключили " + json.username + " из лобби!", {
                                color: "#fff",
                                background: "#20D67B"
                            });
                        } else {
                            $.notify("Не удалось исключить " + json.username + " !", {
                                color: "#fff",
                                background: "#D44950"
                            });


                        }
                        break;
                    case 'exit_lobby_by_owner_lobby':
                        $(".search_bar").animate({
                            opacity: 0
                        }, 400);
                        $.notify("Админ комнаты отменил поиск игры", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'users_online':
                        var user = json.id;
                        $("[friend='" + user + "']").addClass("online");
                        $("[history_player='" + user + "']").addClass("online");
                        break;
                    case 'users_online_close':
                        var user = json.id;
                        $("[friend='" + user + "']").removeClass("online");
                        $("[history_player='" + user + "']").removeClass("online");
                        break;
                    case 'confirm lobby':
                        $("[popup=confirm_lobby]").show("slow");
                        $("[confirm-lobby]").show("slow");
                        $(this).prop("disabled", false);
                        new Audio('/files/music/search_room.mp3').play();
                        if ($("#autoacceptid").is(':checked')) $("[confirm-lobby]").click();

                        break;
                    case 'lobby canceled':
                        $("[popup=confirm_lobby]").hide("slow");
                        $.notify("Кто-то из лобби не принял игру в течение 15 секунд", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        $('[game_start_lobby]').attr("class", "search_game");
                        $('.search_bar').css("opacity", "0");
                        $('[game_start_lobby]').prop("disabled", false);
                        $('[game_start_lobby]').text("Поиск игры");
                        new Audio('/files/music/cancel_room.mp3').play();
                        $("[popup='confirm_lobby']").hide("slow");
                        $("[sel]").prop("disabled", false);
                        break;
                    case 'You are out of search':
                        $.notify("Вы остановили поиск игры", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'join_in_lobby':
                        location = "http://bloodgodz.com/room/" + json.roomid;
                        break;
                    case 'countPlayersInSearch':
                        anim_text("[countsPlayer]", json.num);
                        break;
                    case 'steam_error':
                        $("[game_stop_search]").hide();
                        $.notify("Для того чтобы играть, нужно привязать стим в настройках", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'new_confirmed':
                        var len = 1 + $(".player_ready").length;
                        $("[confirm_list] > div:nth-child(" + len + ")").addClass("player_ready").animate({
                            opacity: 1
                        }, 1000);
                        break;
                    case 'invite_by_user':
                        anim_text("[invite_username]", json.username);
                        //   $("").text(json.username);
                        $("[popup=invite_confirm_in_lobby]").attr("userid", json.id);
                        $("[popup=invite_confirm_in_lobby]").fadeIn('slow');
                        $("[popup=invite_confirm_in_lobby]").find("button").prop("disabled", false);
                        break;
                    case 'sended_invite':
                        $.notify("Пользователю " + json.username + " отправлен инвайт!", {
                            color: "#fff",
                            background: "#20D67B"
                        });
                        break;
                    case 'send_invite_failed_reason_already_in_lobby':
                        $.notify("Пользователь " + json.username + " уже играет в лобби с друзьями!", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'send_invite_failed':
                        $.notify("Пользователь " + json.username + " не в сети!", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    case 'userAcceptedUrInvite':
                        $.notify("Пользователь " + json.username + " принял ваш инвайт!", {
                            color: "#fff",
                            background: "#20D67B"
                        });
                        break;
                    case 'join_in_frined_lobby':
                        location = location.origin + "/lobbyid/" + json.lobbyid;
                        break;
                    case 'cooldown':
                        $.notify("У вас cooldown на 2 минуту!", {
                            color: "#fff",
                            background: "#D44950"
                        });
                        $("[game_start_lobby]").hide();
                        $(".search_bar").remove();
                        $(".search_tab > div:nth-child(2)").html("У вас cooldown на 2 минуты");
                        break;
                    case 'connection_id':
                        connectID = location.origin + "/lobbyconnect/" + json.id;
                        $("#copy_url").val(connectID);
                        break;
                    case 'user_lobby_connect':
                        var players = json.players;
                        var playersLen = players.length;
                        var playersNew = [];
                        var owner_id = json.owner_id;
                        var userid = json.user_id;
                        var owner;
                        var text;

                        for (let i = 0; i < players.length; i++) {
                            let player = players[i];
                            if (player.id == owner_id) {
                                owner = player;
                            } else {
                                playersNew.push(player);
                            }
                        }
                        text = "";
                        console.log("before check");
                        console.log(userid != owner_id);
                        if (userid != owner_id) {
                            $("[sel='mods']").prop("disabled", true);
                            $("[sel='region']").prop("disabled", true);
                            $(".search_tab > div:first-child").remove();
                        }
                        $(".list_players > div").remove();
                        $(".list_players").append('<div class="leader" userid="' + owner.id + '"> <div><img src="' + owner.avatar + '" alt=""> <div class="username"><span>' + owner.username + '</span></div> </div> </div>');
                        for (let i = 0; i < playersNew.length; i++) {
                            let player = playersNew[i];
                            if (userid == owner_id) {
                                text = '<div class="kickuser" kickuseridlobby="' + player.id + '" title="Кикнуть пользователя с лобби"><i class="fas fa-user-times fa-3x"></i></div>';
                            }
                            $(".list_players").append('<div class="user" userid="' + player.id + '"> <div><img src="' + player.avatar + '" alt=""> <div class="username"><span>' + player.username + '</span></div></div>' + text + ' </div>');
                        }
                        for (let i = playersLen; i < 5; i++) {
                            $(".list_players").append('<div class="free_slot"> <div><i class="fas fa-user-circle fa-4x"></i></div> <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div> </div>');
                        }
                        $('.list_players').append('<div class="lobby_options"> <div class="lobby_options__link"> <i class="far fa-copy fa-lg"></i></div> <div class="lobby_options__refresh">  <i class="fas fa-sign-out-alt fa-lg"></i></div> </div>');
                        break;
                    case 'user_accepted_ur_invite':
                        //   $(".lobby_users_list > .list_users").find("[userid='0']").eq(0).attr("userid", json.id).html('<img src="' + json.avatar + '" alt="" class="player_in_lobby_list">');
                        $.notify("Пользователь " + json.username + " принял ваш инвайт!", {
                            color: "#fff",
                            background: "#20D67B"
                        });
                        break;
                    case 'customlobby_user_close_connect':
                        $(".list_players > [userid='" + json.userid + "']").removeAttr("userid").attr("class", "free_slot").html('<div><i class="fas fa-user-circle fa-4x"></i></div> <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>');
                        break;
                    case 'cant_run_search_game':
                        setTimeout(() => {
                            $("[game_stop_search]").hide();
                            $("[game_search]").hide();
                        }, 1000);
                        break;
                    case 'error_start_lobby_by_mode':
                        switch (json.type) {
                            case 1:
                                $.notify("Для режима 1vs1 нужен 1 человек в лобби!", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                break;
                            case 2:
                                $.notify("Для режима 2vs2 нужно 2 человека в лобби!", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                break;
                            case 3:
                                $.notify("Для режима 3vs3 нужно 3 человека в лобби!", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                break;
                            case 4:
                                $.notify("Для режима 5vs5 нужно 5 человека в лобби!", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                break;
                        }
                        setTimeout(() => {
                            $("[game_stop_search]").prop("disabled", true);
                            $("[game_search]").prop("disabled", false);
                            $("[game_stop_search]").hide();
                            $("[game_search='search']").show();
                            $(".search_bar").animate({
                                opacity: 0
                            }, 400);
                        }, 1000);

                        break;
                    case 'start_error_fr':
                        $.notify(json.msg, {
                            color: "#fff",
                            background: "#D44950"
                        });
                        break;
                    default:

                        break;
                }
            } catch {
                // console.log('Message: %s', message.data);
            }
        };
        chatws.onmessage = function(message) {
            var data = JSON.parse(message.data);
            if (data.type == "new_message") {
                var text = "";
                var classL = "chat_group_id_" + data.group;
                var titleUrl = "Пользователь";
                var icon = '<i class="fas fa-user"></i>';
                var titleUrl = "Пользователь";
                if (data.group == 1) {
                    icon = '<i class="fas fa-user-crown"></i>';
                    titleUrl = "Администратор";
                } else if (data.group == 4) {
                    icon = '<i class="fas fa-crown"></i>';
                    titleUrl = "Премиум";
                } else if (data.group == 2) {
                    icon = '<i class="fas fa-user-cog"></i>';
                    titleUrl = "Модератор";
                }

                var new_id = parseInt($("[msg_id]").attr("msg_id")) | 1;
                new_id = new_id + 1;
                text = "<div class='" + classL + "' msg_id='" + new_id + "'><div><a href='" + location.origin + "/user/" + data.userid + "' title='" + titleUrl + "'>" + data.username + "</a> " + icon + "</div>";
                text += "<div></div></div>";
                $("[messages]").prepend(text);
                $("[msg_id='" + new_id + "'] > div:last-child").text(data.text);
                $("[messages] > div:first-child").hide().show("slow");


            }
            if (data.type == 'min_3_characters') {
                $.notify("Минимально 3 символа для отправки сообщения", {
                    color: "#fff",
                    background: "#D44950"
                });
            }
            if (data.type == '10_sec_limit') {
                $.notify("Подождите " + data.time + " секунд перед отправкой следующего сообщения", {
                    color: "#fff",
                    background: "#D44950"
                });
            }
            if (data.type == 'get_messages') {
                var msg = data.msg
                for (let i = 0; i < msg.length; i++) {
                    let arr = msg[i];
                    var text = "";
                    var classL = "chat_group_id_" + arr.group;
                    var icon = '<i class="fas fa-user"></i>';
                    var titleUrl = "Пользователь";
                    if (arr.group == 1) {
                        icon = '<i class="fas fa-user-crown"></i>';
                        titleUrl = "Администратор";
                    } else if (arr.group == 4) {
                        icon = '<i class="fas fa-crown"></i>';
                        titleUrl = "Премиум";
                    } else if (arr.group == 2) {
                        icon = '<i class="fas fa-user-cog"></i>';
                        titleUrl = "Модератор";
                    }

                    text = "<div class='" + classL + "' msg_id='" + i + "'><div><a href='" + location.origin + "/user/" + arr.userid + "' title='" + titleUrl + "'>" + arr.username + "</a> " + icon + "</div>";
                    text += "<div></div></div>";
                    $("[messages]").prepend(text);
                    $("[msg_id='" + i + "'] > div:last-child").text(arr.text);
                }
            }
        };
        $("[confirm-lobby]").click(() => {
            $("[confirm-lobby]").hide("slow");
            $(this).prop("disabled", true);
            lobby.send(JSON.stringify({
                action: 'confirm_lobby',
                data: $("body > noscript").text()
            }));
        });
        $("[sendmsginchat]").click(() => {
            chatws.send(JSON.stringify({
                action: 'sendMessage',
                data: $("[inputchat]").val(),
                region: $("[sel='region']").find("[value=" + $("[sel='region']").val() + "]").text(),
                mode: $("[sel='mods']").find("[value=" + $("[sel='mods']").val() + "]").text()
            }));
            $("[inputchat]").val("")
        });
        $("[sendcommand]").click(function(e) {
            var msg = $(this).attr("sendcommand");
            chatws.send(JSON.stringify({
                action: 'sendMessage',
                data: msg,
                region: $("[sel='region']").find("[value=" + $("[sel='region']").val() + "]").text(),
                mode: $("[sel='mods']").find("[value=" + $("[sel='mods']").val() + "]").text()
            }));
        });
        setInterval(() => {
            chatws.send(JSON.stringify({
                action: 'userOnline',
                data: $("body > noscript").text()
            }));
            lobby.send(JSON.stringify({
                action: 'userOnline',
                data: $("body > noscript").text()
            }));
        }, 15000);
        $(".user_rightbar").hover(() => {
            $(".user_rightbar").addClass("user_rightbarOpen", 1000);
        }, () => {
            $(".user_rightbar").removeClass("user_rightbarOpen");
        });



        $("[inviteuser]").click(function() {
            var val = $(this).attr("inviteuser");
            lobby.send(JSON.stringify({
                action: 'inviteUser',
                userid: val,
                path: location.pathname
            }));
        });
        $("[confirm-invite]").click(function() {
            $(this).prop("disabled", true);
            $("[popup=invite_confirm_in_lobby]").fadeOut('slow');
            lobby.send(JSON.stringify({
                action: 'acceptRequestInLobby',
                userid: $("[popup=invite_confirm_in_lobby]").attr("userid"),
                path: location.pathname
            }));
        });
        $("[decline-invite]").click(function() {
            $(this).prop("disabled", true);
            $("[popup=invite_confirm_in_lobby]").fadeOut('slow');
        });
    </script>
    <?
    if ($user == false) {
        include(__DIR__ . "/sections/popup_auth.php");
        echo '<script src="/js/' . $configIncludes->auth . '"></script>';
    } else {
        echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
    }
    ?>
</body>