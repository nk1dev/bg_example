<?

include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
$profile = FindUserByID((int)$_GET['id']);
if ($profile == false) {
    header("Location: " . __HOST__);
    exit();
}
$stats = profileStats($profile);
$isFriend = isFriend($user->id, $profile->id);

//BloodGodz league
?>

<head>
    <?
    include(__DIR__ . "/sections/head.php");
    ?>
    <? if ($profile->id == 1) : ?>
        <style>
            .profile__steam_art {
                color: rgba(255, 255, 255, 1);
                margin: auto;
                position: relative;
                max-width: 1279px;
                width: 100%;
                margin-top: 2rem;
                display: flex;
                text-align: center;
                align-items: center;
                justify-content: center;
            }

            .profile__steam_art>div {
                background: linear-gradient(180deg, #222222 0%, #181818 100%);
                border-radius: 24px;
                padding: 1rem 2rem;
                text-align: center;
            }

            .artwork_steam_img>div:nth-child(1)>img {
                border-radius: 5% 0 0 5%;
                border-radius: 2rem 0 0 2rem;
            }

            .artwork_steam_img>div:nth-child(2)>img {
                border-radius: 0 2rem 2rem 0;
            }

            .artwork_steam_img {
                display: flex;
            }
        </style>
    <? endif; ?>
    <? if ($profile->group_id != 5) : ?>
        <style>
            <? if (!empty($profile->background_id)) : $background = find_in_db_array("backgroundsList", ["id" => (int)$profile->background_id]);
                $background = $background[0]['url']; ?>#app>.appContent {
                background: url('<?= $background ?>');
            }

            <? endif; ?>
        </style>
    <? endif; ?>
</head>

<body cz-shortcut-listen="true">
    <div id="app">
        <div class="appContent">
            <?
            include(__DIR__ . "/sections/header.php");
            ?>
            <div page='profile'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/setting/tag.png" alt="">
                        <div class="font-bold">#ПРОФИЛЬ АККАУНТА</div>
                    </div>
                    <div>
                        <div>
                            <div><img src="<?= $profile->avatar; ?>" alt=""></div>
                            <div>
                                <div class="profile_group_id_<?= $profile->group_id; ?>">
                                    <input type="text" value="<?= htmlspecialchars($profile->username);
                                                                if ($profile->group_id == 4) echo  " 👑"; ?> " disabled>
                                    <? if ($profile->id == $user->id) :; ?>
                                        <a href="/settings"><i class="fas fa-cog"></i> Настройки</a>
                                    <? endif; ?>
                                </div>
                                <div><textarea name="" id="" cols="30" rows="3" placeholder="Заметки пользователь" disabled><?= (string)$profile->desc ?></textarea></div>
                                <? if ($profile->id == 1) : ?>
                                    <div>
                                        <i class="fas fa-code" style="color: #FF334D;animation: rotate360  2s linear infinite;"></i>
                                        <span style="margin: 0 1rem;">Dev</span>
                                        <i class="fas fa-code" style="color: #FF334D;animation: rotate360  2s linear infinite;"></i>
                                    </div>
                                <? endif; ?>
                                <div class="user__medals">
                                    <?
                                    $profileMedals = getUserMedals($profile);
                                    for ($i = 0; $i < count($profileMedals); $i++) {
                                        $medal = $profileMedals[$i];
                                        echo "<i class='" . $medal->icon . " fa-lg' style='color: " . $medal->color . ";' title='" . $medal->name . "'></i>";
                                    }
                                    ?>
                                </div>
                                <? if ($user != false && $profile->id != $user->id) : ?>
                                    <div class="user__actionWith__friends">
                                        <? if ($isFriend == false) : ?>
                                            <button addfriend>Добавить в друзья</button>
                                        <? elseif ($isFriend != false && $isFriend->status == 0) : ?>
                                            <button deleteaddrequest>Удалить запрос в друзья</button>
                                        <? elseif ($isFriend != false && $isFriend->status == 1) : ?>
                                            <button deletefriend>Удалить из друзей</button>
                                        <? endif; ?>
                                    </div>
                                <? endif; ?>

                            </div>
                        </div>

                    </div>
                    <div class="profile__status_ranks">
                        <div>
                            <div profile_ranks_stats>
                                <?
                                $elo1_need_for_up_rank = $RANK_LIST[getRankByElo($profile->elo_1) + 1];
                                $elo1_change_color =  getPercentForNewRank($profile->rank_1, $profile->elo_1);
                                $elo2_need_for_up_rank = $RANK_LIST[getRankByElo($profile->elo_2) + 1];
                                $elo2_change_color =   getPercentForNewRank($profile->rank_2, $profile->elo_2);
                                $elo3_need_for_up_rank = $RANK_LIST[getRankByElo($profile->elo_3) + 1];
                                $elo3_change_color =   getPercentForNewRank($profile->rank_3, $profile->elo_3);
                                $elo4_need_for_up_rank = $RANK_LIST[getRankByElo($profile->elo_4) + 1];
                                $elo4_change_color =   getPercentForNewRank($profile->rank_4, $profile->elo_4);
                                ?>
                                <div>Статистика игрока</div>
                                <div>
                                    <div>
                                        <div>
                                            <div>1vs1</div>
                                            <div><img src="/files/images/levels/<?= $profile->rank_1; ?>.png" alt="" style="width: 3rem;"></div>
                                        </div>
                                        <div>
                                            <div><span><?= $profile->elo_1; ?> ELO</span><span><?= $RANK_LIST[getRankByElo($profile->elo_1) + 1]; ?></span></div>
                                            <div>
                                                <div style="width: <?= $elo1_change_color; ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <div>
                                            <div>2vs2</div>
                                            <div><img src="/files/images/levels/<?= $profile->rank_2; ?>.png" alt="" style="width: 3rem;"></div>
                                        </div>
                                        <div>
                                            <div><span><?= $profile->elo_2; ?> ELO</span><span><?= $RANK_LIST[getRankByElo($profile->elo_2) + 1]; ?></span></div>
                                            <div>
                                                <div style="width: <?= $elo2_change_color; ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <div>
                                            <div>3vs3</div>
                                            <div><img src="/files/images/levels/<?= $profile->rank_3; ?>.png" alt="" style="width: 3rem;"></div>
                                        </div>
                                        <div>
                                            <div><span><?= $profile->elo_3; ?> ELO</span><span><?= $RANK_LIST[getRankByElo($profile->elo_3) + 1]; ?></span></div>
                                            <div>
                                                <div style="width: <?= $elo3_change_color; ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <div>
                                            <div>5vs5</div>
                                            <div><img src="/files/images/levels/<?= $profile->rank_4; ?>.png" alt="" style="width: 3rem;"></div>
                                        </div>
                                        <div>
                                            <div><span><?= $profile->elo_4; ?> ELO</span><span><?= $RANK_LIST[getRankByElo($profile->elo_4) + 1]; ?></span></div>
                                            <div>
                                                <div style="width: <?= $elo4_change_color; ?>%;"></div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div>Cоц. сети</div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fab fa-steam-symbol"></i></div>
                                    <div>
                                        <input type="text" placeholder="Steam" value="<? if (!empty($profile->steam_id)) echo "https://steamcommunity.com/profiles/{$profile->steam_id}"; ?>" disabled>
                                    </div>
                                </div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fab fa-discord"></i></div>
                                    <div><input type="text" disabled value="<?= htmlspecialchars($profile->discord_tag); ?>" placeholder="Discord"></div>
                                </div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fas fa-play"></i></div>
                                    <div><input type="text" disabled value="<?= htmlspecialchars($profile->youtube); ?>" placeholder="Youtube"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <? if ($profile->id == 1) : ?>
                        <div class="profile__steam_art">
                            <div>
                                <h3>Steam</h3>
                                <div class="artwork_steam_img">
                                    <div><img src="https://steamuserimages-a.akamaihd.net/ugc/1644340512391554560/FC99ECED0D59311A93B6069CFE477581173171DD/?imw=506&&ima=fit&impolicy=Letterbox&imcolor=%23000000&letterbox=false" alt=""></div>
                                    <div><img src="https://steamuserimages-a.akamaihd.net/ugc/1644340512391548073/2D80F98D3DF8BF204CBEB0780A147E263A9994F3/?imw=100&&ima=fit&impolicy=Letterbox&imcolor=%23000000&letterbox=false" alt=""></div>
                                </div>
                            </div>
                        </div>
                    <? endif; ?>
                    <div class="profile__stats_1">
                        <div>
                            <div>
                                <div>Сыграно матчей</div>
                                <div><?= $stats->count; ?></div>
                            </div>
                            <div>
                                <div>Выиграно матчей</div>
                                <div><?= $stats->wins; ?></div>
                            </div>
                            <div>
                                <div>К/Д всех игр</div>
                                <div><?= $stats->kd; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="profile__stats_2">
                        <div>
                            <div>
                                <div>Убийства</div>
                                <div><?= $stats->kills; ?></div>
                            </div>
                            <div>
                                <div>Смертей</div>
                                <div><?= $stats->deaths; ?></div>
                            </div>
                            <div>
                                <div>Убийства в голову</div>
                                <div><?= $stats->hs; ?></div>
                            </div>
                        </div>
                    </div>
                    <? if ($profile->group_id != 5) : ?>
                        <div class="profile__premium_videos">
                            <div>
                                <?
                                $videos = find_in_db_array("youtube_videos", ["user_id" => $profile->id]);
                                if (count($videos) != 0) {
                                    for ($i = 0; $i < count($videos); $i++) {
                                        $video = str_replace("https://youtu.be/", "", $videos[$i]['link']);
                                        echo '<div><iframe width="220" src="https://www.youtube-nocookie.com/embed/' . $video . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                                    }
                                }

                                ?>
                            </div>
                        </div>
                    <? endif; ?>
                    <div class="profile__history_matches">
                        <div>
                            <div>История матчей</div>
                            <div>
                                <div>
                                    <div>Дата</div>
                                    <div>Режим</div>
                                    <div>Результат</div>
                                    <div>Карта</div>
                                    <? if ($profile->id == $user->id) echo "<div>Просмотр</div>"; ?>

                                </div>
                                <div list_profile_games>

                                    <?
                                    $games = $stats->games;
                                    // print_r($games);
                                    for ($i = 0; $i < count($games); $i++) {
                                        $game = $games[$i];
                                        echo '<div>';
                                        echo "<div>" . $game['date'] . "</div>";
                                        echo "<div>" . $game['mode'] . "</div>";
                                        echo "<div>" . $game['score'] . "</div>";
                                        echo "<div>" . $game['map'] . "</div>";
                                        if ($profile->id == $user->id) echo "<div><a href='" . __HOST__ . "/room/{$game['roomid']}'>Посмотреть</a></div>";
                                        echo '</div>';
                                    }
                                    ?>
                                    <!---    <div>
                                        <div>21-12-23</div>
                                        <div>MODE</div>
                                        <div>RES</div>
                                        <div>Map</div>
                                    </div>
                                    <div>
                                        <div>21-12-23</div>
                                        <div>MODE</div>
                                        <div>RES</div>
                                        <div>Map</div>
                                    </div>
                                --->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile__chat">
                        <div>
                            <div>Стенка пользователя</div>
                            <div user_wall>
                                <div>

                                </div>
                            </div>
                            <div userwall_message>
                                <textarea name="" id="" cols="30" rows="3" placeholder="Ваше сообщение..."></textarea>
                                <button><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.2005 0.155459C19.0461 0.00625628 18.8145 -0.0396225 18.6119 0.0387054L1.07549 6.81675C0.874842 6.8943 0.740481 7.07894 0.732768 7.28766C0.725091 7.49635 0.845591 7.68975 1.04002 7.78087L7.95518 11.0216L11.309 17.7037C11.4005 17.8859 11.5915 18.0009 11.7999 18.0009C11.8064 18.0009 11.8129 18.0008 11.8194 18.0006C12.0353 17.9931 12.2265 17.8633 12.3067 17.6694L19.3213 0.724216C19.4023 0.528326 19.3549 0.304627 19.2005 0.155459ZM2.62772 7.35274L16.5486 1.97212L8.25235 9.98868L2.62772 7.35274ZM11.7521 16.1695L9.02414 10.7344L17.3205 2.71785L11.7521 16.1695Z" fill="#FEFEFE"></path>
                                    </svg></button>
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
        include(__DIR__ . "/sections/popup_auth.php");
        echo '<script src="/js/' . $configIncludes->auth . '"></script>';
    } else {
        echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
    }
    ?>
    <script>
        socket_closed = false;
        const wall = new WebSocket('wss://bloodgodz.com/userwallws');
        wall.onopen = function() {
            console.log('UserWall status: Connected');
            wall.send(JSON.stringify({
                action: 'userConnect',
                data: $("body > noscript").text(),
                profile: location.pathname
            }));
        };
        wall.onmessage = function(message) {
            var json = JSON.parse(message.data);
            switch (json.type) {
                case 'get_messages':
                    var msgs = json.msg;
                    for (let i = 0; i < msgs.length; i++) {
                        let msg = msgs[i];
                        let text = "<div msgid='" + msg.id + "'>";
                        let addText = '';
                        if (json.userid == msg.userid) addText = "<span delete_message_profile='" + msg.id + "'><i class='fas fa-trash'></i><span>";
                        text += "<div>" + msg.username + "  (" + msg.date + ") " + addText + "</div>";
                        text += "<div>" + "</div>";
                        text += "</div>";
                        $("[user_wall] > div").append(text);
                        $("[user_wall] > div > [msgid='" + msg.id + "'] > div:last-child").text(msg.text);
                        let timer = 500 + ((i) * 500);
                        $("[msgid='" + msg.id + "']").hide().slideDown(timer);
                    }
                    break;
                case 'new_message':
                    var msg = json.msg;
                    let text = "<div msgid='" + msg.id + "'>";
                    let addText = '';
                    if (json.userid == msg.userid) addText = "<span delete_message_profile='" + msg.id + "'><i class='fas fa-trash'></i><span>";
                    text += "<div>" + msg.username + "  (" + msg.date + ") " + addText + "</div>";
                    text += "<div>" + "</div>";
                    text += "</div>";
                    $("[user_wall] > div").prepend(text);
                    $("[user_wall] > div > [msgid='" + msg.id + "'] > div:last-child").text(msg.text);
                    $("[msgid='" + msg.id + "']").hide().slideDown(1000);
                    break;
                case 'message_deleted':
                    $.notify('Сообщение в профиль удалено!', {
                        color: "#fff",
                        background: "#20D67B"
                    });
                    break;
                case 'alert':
                    alert(json.msg);
                    break;
                default:
                    break;
            }
        };
        wall.onclose = function(event) {
            socket_closed = true;
            if (event.wasClean) {
                console.log('Соединение закрыто чисто');
            } else if (event.code === 1006) {
                console.log('Соединение закрыто как 1006');
            } else {
                console.log('Обрыв соединения code:' + event.code);
            }
        };
        setInterval(() => {
            wall.send(JSON.stringify({
                action: 'userOnline',
                data: $("body > noscript").text()
            }));
        }, 30000);
        $("[userwall_message] > button").click(function() {
            var text = $("[userwall_message] > textarea").val();
            $("[userwall_message] > textarea").val("");
            $(this).prop("disabled", true);
            wall.send(JSON.stringify({
                action: 'sendMessage',
                profile: location.pathname,
                text: text
            }));
            $.notify('Сообщение в профиль успешн отправлено!!', {
                color: "#fff",
                background: "#20D67B"
            });
            setTimeout(() => {
                $("[userwall_message] > button").prop("disabled", false);
            }, 1000);
        });
        $("body").delegate("[delete_message_profile]", "click", function(e) {
            $(this).prop("disabled", true);
            $(this).hide('slow');
            var msg_id = $(this).attr("delete_message_profile");
            $("[msgid='" + msg_id + "']").hide('slow');
            wall.send(JSON.stringify({
                action: 'deleteMessageInProfile',
                id: msg_id
            }));
        });
    </script>
</body>