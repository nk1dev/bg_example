<?

include(__DIR__."/mongo/mainMongo.php");
$user = user_auth();
$region = $_GET['region'];
$mode = $_GET['mode'];

if (!in_array(strtoupper($region), __REGION_LIST__) || !in_array(strtolower($mode), __MODS_LIST__)) {
    header("Location: " . __HOST__ . "/top/5vs5/all");
    exit();
}
?>

<head>
<?
include(__DIR__."/sections/head.php");
?>
</head>

<body cz-shortcut-listen="true">
    <div id="app">
        <div class="appContent">
            <?
            include(__DIR__ . "/sections/header.php");
            ?>
            <div page='top'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div><img src="/files/images/top/playerTop.png" alt="">
                        <div class="font-bold">#ТОП ИГРОКОВ</div>
                        <div>
                            <div>
                                <a href="/top/1vs1/<?= $region; ?>" <?= topSetClassActiveByMode("1vs1"); ?>>1 vs 1</a>
                                <a href="/top/2vs2/<?= $region; ?>" <?= topSetClassActiveByMode("2vs2"); ?>>2 vs 2</a>
                                <a href="/top/3vs3/<?= $region; ?>" <?= topSetClassActiveByMode("3vs3"); ?>>3 vs 3</a>
                                <a href="/top/5vs5/<?= $region; ?>" <?= topSetClassActiveByMode("5vs5"); ?>>5 vs 5</a>
                            </div>
                            <div>
                                <a href="/top/<?= $mode; ?>/all" <?= topSetClassActiveByRegion("all"); ?>>ALL</a>
                                <a href="/top/<?= $mode; ?>/rus" <?= topSetClassActiveByRegion("rus"); ?>>RUS</a>
                                <a href="/top/<?= $mode; ?>/eu" <?= topSetClassActiveByRegion("eu"); ?>>EU</a>
                            </div>
                        </div>
                        <div>
                            <div>
                                <div>
                                    <div></div>
                                    <div>
                                        <div>Ранг</div>
                                        <div>Имя игрока</div>
                                        <div>ELO рейтинг</div>
                                        <div>Коэф у/c</div>
                                        <div>K/D/A</div>
                                        <div>Коэф. выигрышей</div>
                                        <div>Процент хэдшотов</div>
                                    </div>
                                </div>
                                <div>

                                    <?
                                    $modeObj = getModesObject($mode);
                                    $regionObj = getRegionsObject($region);
                                    if (strtoupper($region) != 'EU'):
                                    $elo_text = "elo_". $modeObj->id;
                                    $top_query = $db->users->find([], [
                                        'sort' => [$elo_text => -1],
                                        'limit' => 10
                                    ])->toArray();
                                 //   $top_query = qr("SELECT * FROM `users` ORDER BY `users`.`elo_".$modeObj->id."` DESC LIMIT 10");
                                    for ($i = 0; $i < count($top_query); $i++) {
                                        $res = (object)$top_query[$i];
                                  //      $q_inroom = find_in_db_array("playersListInRoom",["user_id"=> (int)$res->id]);// qr("SELECT * FROM `playersListInRoom` WHERE `user_id` = '{$res->id}'");
                                        $stats = playerGetStatsByID($res->id, true, $modeObj->id, $regionObj->id);
                                        $kills = $stats->kills;
                                        $deaths = $stats->deaths;
                                        $assists = $stats->assists;
                                        $winrate = 0;
                                        $hs_rate = 0;
                                        $hs = $stats->hs;
                                        $kd = 0;
                                        $wins = $stats->wins;
                                        $lose = $stats->lose;


                                        $kd = round(($kills / $deaths), 1);
                                        $hs_rate = round(($kills / $hs), 1);
                                        $kda = $kills . "/" . $deaths . "/" . $assists;
                                        $winrate = round(($wins / $lose), 1);
                                        if ($lose == 0) $winrate = $wins;
                                        if ($wins == 0) $winrate = 0.0; 
                                        if ($hs == 0) $hs_rate = 0.0;
                                        if ($kills == 0) $kd = 0.0;
                                        if ($deaths == 0) $kd = $kills;
                                        $id_top = $i + 1;
                                        $elo_text = "elo_".$modeObj->id;
                                        echo "<div>";

                                        echo "<div>" . $id_top . "</div>";
                                        echo "<div>";
                                        $rank_text = "rank_". $modeObj->id;
                                        echo "<div><img src='/files/images/levels/{$res->$rank_text}.png'></div>";
                                        echo "<div><a href='".__HOST__.'/user/'.$res->id. "'>" . $res->username . "</a></div>";
                                        echo "<div>" . $res->$elo_text . "</div>";
                                        echo "<div>" . $kd . "</div>";
                                        echo "<div>" . $kda . "</div>";
                                        echo "<div>" . $winrate . "</div>";
                                        echo "<div>" . $hs_rate . "</div>";
                                        echo "</div>";

                                        echo "</div>";
                                    }
                                endif;
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
    if ($user== false) {
        include(__DIR__ . "/sections/popup_auth.php");
        echo '<script src="/js/'.$configIncludes->auth.'"></script>';
    }else {
        echo '<script src="/js/'.$configIncludes->user_auth.'"></script>';
    }
    ?>
</body>