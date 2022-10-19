<?
include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
if ($user == false) {
    header("Location: " . __HOST__);
    exit();
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
            <div page='settings'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/setting/tag.png" alt="">
                        <div class="font-bold">#НАСТРОЙКИ АККАУНТА</div>
                    </div>
                    <div>
                        <div>
                            <div><img src="<?= $user->avatar; ?>" alt=""></div>
                            <div>
                                <div><input type="text" value="<?= $user->username; ?>" disabled></div>
                                <div style="position: relative;">
                                    <textarea name="" id="" cols="30" rows="3" placeholder="Ваши заметки" desctext><?= (string)$user->desc; ?></textarea>
                                    <? if ($user->group_id != 5) : ?>
                                        <button style="margin-right: 1rem; position: absolute; top: 0; right: 0; margin-top: 1rem;"><i class="fas fa-save fa-lg" savemydesc></i></button>
                                    <? endif; ?>
                                </div>
                                <? if ($user->group_id == 5) : ?>
                                    <div><button>PREMIUM</button>
                                        <div>Нажми и узнай о преимуществах BloodGodz premium</div>
                                    </div>
                                <? endif; ?>

                                <div><a href="<?= __HOST__ . "/user/" . $user->id; ?>" style='position: absolute; top: 0; margin-right: 2rem; right: 0; margin-top: 1.7rem; font-family: "Roboto"; font-style: normal; font-weight: 400; font-size: 1rem; color: #969696;'>Вернуться в профиль</a></div>
                            </div>
                        </div>

                    </div>
                    <div>
                        <div>
                            <div>
                                <div>Изменить пароль</div>
                                <div><input type="password" placeholder="Введите старый пароль" old_password_for_change></div>
                                <div><input type="password" name="" placeholder="Введите новый пароль" new_password_for_change></div>
                                <div><input type="password" name="" placeholder="Повторите новый пароль" new_repassword_for_change></div>
                                <div><button user_save_password>Сохранить</button></div>
                            </div>
                            <div>
                                <div>Введите ссылки на соц. сети, чтобы играть</div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fab fa-steam-symbol"></i></div>
                                    <div>
                                        <? if (empty($user->steam_id)) : ?>
                                            <a href="<?= steam_auth_login_url(); ?>"><button>Привязать аккаунт</button></a>
                                        <? endif; ?>
                                        <? if (!empty($user->steam_id)) : ?>
                                            <a href="<?= __HOST__ ?>/auth/remove_steam"><button>Отвязать стим аккаунт</button></a>
                                        <? endif; ?>
                                    </div>
                                </div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fab fa-discord"></i></div>
                                    <div>
                                        <? if (empty($user->discord_id) && (int)$user->discord_id < 1000) : ?>
                                            <a href="<?= discord_auth_login_url(); ?>"><button>Привязать дискорд аккаунт</button></a>
                                        <? else : ?>
                                            <a href="<?= __HOST__ ?>/auth/remove_discord"><button>Отвязать дискорд аккаунт</button></a>
                                        <? endif; ?>
                                    </div>
                                </div>
                                <div class="setting_social">
                                    <div class="icon"><i class="fas fa-play"></i></div>
                                    <div><input type="text" placeholder="Youtube link" name="yt_social" value="<?= $user->youtube; ?>"></div>
                                </div>
                                <div><button save_social>Сохранить</button></div>
                            </div>
                        </div>
                    </div>
                    <div class="user__mail__change">
                        <div>
                            <div>Почта</div>
                            <div>
                                <input type="text" placeholder="Ваша почта" value="<?= $user->mail; ?>" name="mailforchange">
                            </div>
                            <div><button user_save_mail>Сохранить</button></div>
                        </div>

                    </div>
                    <? if ($user->group_id != 5) : ?>
                        <div class="premium__add__video">
                            <div>
                                <div>Ссылки на видео</div>
                                <? if ($user->group_id == 1 || $user->group_id == 4) : ?>

                                    <div>
                                        <?
                                        $videos = find_in_db_array("youtube_videos", ["user_id" => $user->id]);
                                        for ($i = 0; $i < 5; $i++) {
                                            echo '<div>';
                                            if (!empty($videos[$i]) && !empty($videos[$i]['id'])  && (int)$videos[$i]['id'] != 0) {
                                                echo '<div><input type="text" placeholder="Введите ссылку на видео YouTube (пример: https://youtu.be/qAcD6sIt7lc)" value="' . $videos[$i]['link'] . '" inputvideo="' . $videos[$i]['id'] . '"></div>';
                                                echo '<div><i class="fas fa-save fa-lg" premium_videoid=' . $videos[$i]['id'] . '></i></div>';
                                            } else {
                                                echo '<div><input type="text" placeholder="Введите ссылку на видео YouTube (пример: https://youtu.be/qAcD6sIt7lc)"  inputvideo="-1"></div>';
                                                echo '<div><i class="fas fa-save fa-lg" premium_videoid="-1"></i></div>';
                                            }
                                            echo "</div>";
                                        }
                                        echo '</div>';
                                        ?>
                                    <? else : ?>
                                        <div>
                                            Для того чтобы добавлять свои видео в профиль, нужно купить премиум
                                        </div>
                                    <? endif; ?>
                                    </div>
                            </div>
                            <div class="premium__custom_background">
                                <div>
                                    <div>Выбрать кастомный фон профиля</div>
                                    <div>
                                        <select name="" id="" selectbackground>
                                            <option value="0">Выберите фон</option>
                                            <?
                                            
                                            $backgrounds = find_in_db_array("backgroundsList", []);
                                            foreach ($backgrounds as $key => $value) {
                                                echo '<option value="' . $value['id'] . '"';
                                                if(!empty($user->background_id) && (int)$user->background_id == (int)$value['id']) echo " selected";

                                                echo ">".$value['name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div><button premium_save_background>Сохранить</button></div>
                                </div>
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
        <?
        if ($user == false) {
            include(__DIR__ . "/sections/popup_auth.php");
            echo '<script src="/js/' . $configIncludes->auth . '"></script>';
        } else {
            echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
        }
        ?>
        <script>
            $("[user_save_mail]").click(() => {
                $(this).prop('disabled', true);
                var handler_id = "user_change_mail";
                var mail = $("[name=mailforchange]").val();
                $.ajax({
                    type: 'post',
                    url: 'handler.php',
                    data: {
                        handler_id,
                        mail
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
                            case -1:
                                $.notify("Почта неверно введена пример (sad@gmail.com)", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -2:
                                $.notify("Указанная почта уже используется на другом аккаунте", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
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
            $("[user_save_password]").click(() => {
                $(this).prop('disabled', true);
                var handler_id = "user_change_password";
                var oldpass = $("[old_password_for_change]").val();
                var pass = $("[new_password_for_change]").val();
                var repass = $("[new_repassword_for_change]").val();
                $.ajax({
                    type: 'post',
                    url: 'handler.php',
                    data: {
                        handler_id,
                        oldpass,
                        pass,
                        repass
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        switch (json.type) {
                            case 1:
                                $.notify('Пароль успешно изменён!', {
                                    color: "#fff",
                                    background: "#20D67B"
                                });
                                break;
                            case -1:
                                $.notify("Старый пароль некорректный", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -2:
                                $.notify("Пароли введены некорректно", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -3:
                                $.notify("Минимально 3 символа для пароля", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -4:
                                $.notify("Максимально 99 символов для пароля", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
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
            $("[save_social]").click(() => {
                $("[save_social]").prop("disabled", true);
                var yt = $("[name='yt_social']").val();
                var handler_id = "save_social";
                $.ajax({
                    type: 'post',
                    url: 'handler.php',
                    data: {
                        handler_id,
                        yt
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        switch (json.type) {
                            case 1:
                                $.notify('Успешно сохранены данные!', {
                                    color: "#fff",
                                    background: "#20D67B"
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
            $("[premium_videoid]").click(function() {
                var id = $(this).attr("premium_videoid");
                var handler_id = "premium_save_video";
                var link = $("[inputvideo='" + id + "']").val();
                $(this).prop('disabled', true);
                $.ajax({
                    type: 'post',
                    url: '/handler.php',
                    data: {
                        handler_id,
                        id,
                        link
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        switch (json.type) {
                            case 1:
                                $.notify('Успешно сохранено видео!', {
                                    color: "#fff",
                                    background: "#20D67B"
                                });
                                break;
                            case -1:
                                $.notify("Ошибка", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -2:
                                $.notify("Неверная ссылка", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -3:
                                $.notify("Ролик на Youtube не найден", {
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
            $("[savemydesc]").click(function(e) {
                $(this).prop('disabled', true);
                var handler_id = "premium__saveMyDesc";
                var desc = $("[desctext]").val();
                $.ajax({
                    type: 'post',
                    url: '/handler.php',
                    data: {
                        handler_id,
                        desc
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        switch (json.type) {
                            case 1:
                                $.notify('Ваш текст был сохранён!', {
                                    color: "#fff",
                                    background: "#20D67B"
                                });
                                break;
                            case -1:
                                $.notify("Минимальное количество символов 2", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
                                break;
                            case -2:
                                $.notify("Максимальное количество символов 255", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
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
            $("[premium_save_background]").click(function(e) {
                $(this).prop('disabled', true);
                var handler_id = "premium__saveMyBackground";
                var background = $("[selectbackground]").val(); 
                $.ajax({
                    type: 'post',
                    url: '/handler.php',
                    data: {
                        handler_id,
                        background
                    },
                    success: function(result) {
                        var json = JSON.parse(result);
                        switch (json.type) {
                            case 1:
                                $.notify('Ваш фон был успешно сохранён!', {
                                    color: "#fff",
                                    background: "#20D67B"
                                });
                                break;
                            case -1:
                                $.notify("Выбранный фон не найден!", {
                                    color: "#fff",
                                    background: "#D44950"
                                });
                                $(this).prop('disabled', false);
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