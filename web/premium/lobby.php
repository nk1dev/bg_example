<?
include("../mongo/mainMongo.php");
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
$id_lobby = $_GET['id'];
if ($id_lobby == 'create') {
    
}
?>

<head>
    <?
    include("../sections/head.php");
    ?>
</head>

<body cz-shortcut-listen="true">
    <div id="app">
        <div class="appContent">
            <?
            include("../sections/header.php");
            ?>

            <div page='premium_lobby'>
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
                            <div class="premium_lobby__choose_map">
                                <div>
                                    <?
                                    foreach (LIST_2V2_MAPS as $key => $value) {
                                        echo "<div mapid='$key' mapname='$value'>";
                                        echo "<div><img src='/files/images/maps/$value.webp'></div>";
                                        echo "<div>$value</div>";
                                        echo "<div><button select_map='$key'>Выбрать</button></div>";
                                        echo "</div>";
                                    }
                                    ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <? if ((int)$user->steam_id > 5000) : ?>
                        <div class="premium_lobby__owner_bar">
                            <div>
                                <div><button premium_lobby_start class="start">Начать</button></div>
                                <div class="premium_lobby__settings">
                                    <div class="premium_lobby__settings1">
                                        <div>Карта: <span class="map">de_dust2</span></div>
                                        <div>Регион: <span class="region">RUS</span></div>
                                    </div>
                                    <div class="premium_lobby__settings2">
                                        <div>Режим: <span class="mode">5x5</span></div>
                                        <div>Игроки: <span class="players">4/10</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <? endif; ?>
                    <div class="premium_lobby__party_list">
                        <div>
                            <div class="list_players">
                                <div class="leader">
                                    <div><img src="https://i.imgur.com/kKiS090.gif" alt="">
                                        <div class="username"><span>Mr.Nik</span></div>
                                    </div>
                                    <div class="kickuser" title="Кикнуть пользователя с лобби"><i class="fas fa-user-times fa-3x"></i></div>
                                </div>
                                <div class="user">
                                    <div><img src="https://avatars.akamai.steamstatic.com/8cdccbc8a6431062e2100de187ef329b52e81e14_full.jpg" alt="">
                                        <div class="username"><span>Хуесос</span></div>
                                    </div>
                                    <div class="kickuser" title="Кикнуть пользователя с лобби"><i class="fas fa-user-times fa-3x"></i></div>
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
                    <div class="premium_lobby__teams_list" style="position: relative;">
                        <div>
                            <div class="team_list_players team1">
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
                                <div class="free_slot">
                                    <div><i class="fas fa-user-circle fa-4x"></i></div>
                                    <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                </div>
                                <div class="lobby_options">
                                    <div class="lobby_options__refresh"> <i class="fas fa-sign-out-alt fa-lg"></i></div>
                                </div>
                            </div>
                            <div class="team_list_players team2">
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
                                <div class="free_slot">
                                    <div><i class="fas fa-user-circle fa-4x"></i></div>
                                    <div class="kickuser"><i class="fas fa-user-times fa-3x"></i></div>
                                </div>
                                <div class="lobby_options">
                                    <div class="lobby_options__refresh"> <i class="fas fa-sign-out-alt fa-lg"></i></div>
                                </div>
                            </div>
                            <div style="left: 0;position: absolute;top: -0.5rem;">team1</div>
                            <div style="right: 0;position: absolute;top: -0.5rem;">team2</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="premium_userbar">
                <div>
                    <div><a href="/premium">Premium</a></div>
                    <div><a href="market">Market</a></div>
                    <div style="display: none;"><a href="skinchanger">SkinChanger</a></div>
                    <div class="active"><a href="lobby">Custom Lobby</a></div>
                </div>
            </div>
            <?
            include("../sections/footer.php");
            ?>
        </div>
        <?
        if ($user == false) {
            include("../sections/popup_auth.php");
            echo '<script src="/js/' . $configIncludes->auth . '"></script>';
        } else {
            echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
        }
        ?>
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
            function anim_text_with_css(where, new_text) {
                $(where).css("opacity", "0");
                setTimeout(function() {
                    $(where).css("opacity", "1").text(new_text);
                }, 500, where, new_text);
            }

            function set_maps() {
                $.ajax({
                    type: 'post',
                    url: '/handler.php',
                    data: {
                        handler_id: "get_maps_in_mode",
                        mode: $("[sel=mods]").val()
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        if (json.type == 1) {
                            var maps = json.maps;
                            $(".premium_lobby__choose_map > div > div").remove();
                            for (let i = 0; i < maps.length; i++) {
                                let map = maps[i];
                                let txt = "<div mapid='" + i + "' mapname='" + map + "'>";
                                txt += "<div><img src='/files/images/maps/" + map + ".webp'></div>";
                                txt += "<div>" + map + "</div>";
                                txt += "<div><button select_map='" + i + "'>Выбрать</button></div>";
                                txt += "</div>";
                                $(".premium_lobby__choose_map > div").append(txt);
                            }
                        }
                    }
                });
            }
            var connectID;
            var invervalTimeLobbyStart;
            var invervalTimeLobby = 0;
            var volume_notify = 1;
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
                location.reload();
            });
            $("[sel=mods]").change(function(e) {
                var mode_id = parseInt($(this).val());
                var mapsLen = $(".premium_lobby__choose_map > div > div").length;
                if (mapsLen == 11 && mode_id > 2) {
                    set_maps();
                } else if (mapsLen != 11 && mode_id != 3) {
                    set_maps();
                }
                $(".premium_lobby__settings1").find(".map").text("");
                $(".premium_lobby__choose_map > div > .selected_map").removeClass("selected_map");
            });
            $("body").delegate("[select_map]", "click", function() {
                var mapid = $(this).attr("select_map");
                var activeid = $(".selected_map").attr("mapid");
                if (activeid != undefined && mapid == activeid) {
                    return;
                }
                $(this).prop("disabled", true);
                if (activeid != undefined) {
                    $(".selected_map").find("button").prop("disabled", false);
                    anim_text_with_css($(".selected_map").find("button"), "Выбрать");
                }
                anim_text_with_css(this, 'Выбрано');
                $(".selected_map").removeClass("selected_map");
                anim_text($(".premium_lobby__settings1").find(".map"), $(".premium_lobby__choose_map > div > [mapid='" + mapid + "']").attr("mapname"));
                $(".premium_lobby__choose_map > div > [mapid='" + mapid + "']").addClass("selected_map");
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
                $("[titlelobby]").text(mods + " " + region);
                anim_text($(".premium_lobby__settings2").find(".mode"), mods);
                anim_text($(".premium_lobby__settings1").find(".region"), region);
            });

            $("[premium_lobby_start]").click(function(e) {

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

</body>