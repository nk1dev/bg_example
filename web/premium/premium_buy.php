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
            <div page='premium'>
                <div>
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/premium/tag_prem.png" alt="">
                        <div class="font-bold">#BloodGodz premium</div>
                    </div>
                    <div class="premium__buy_bar">
                        <div>
                            <div class="premium__privileges">
                                <div>Преимущества</div>
                                <div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-search"></i></span></div>
                                        <div>Быстрее поиск игроков</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-check-circle"></i></span></div>
                                        <div>Авто - принятие игры</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-clock"></i></span></div>
                                        <div>Укороченный срок бана если вы не приняли игру</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-shopping-cart"></i></span></div>
                                        <div>Доступ к маркету</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-trash"></i></span></div>
                                        <div>Возможность удалять коментарии в профиле</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-images"></i></span> </div>
                                        <div>Возможность менять фон в профиле</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-gamepad"></i></span></div>
                                        <div>Возможность создавать custom игры</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-coins"></i></span> </div>
                                        <div>Возможность получить монеты</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fab fa-youtube"></i></span> </div>
                                        <div>Возможность ставить видео в профиль</div>
                                    </div>
                                    <div>
                                        <div><span class="icon"><i class="fas fa-calendar-week"></i></span></div>
                                        <div>Сброс статистики раз в месяц</div>
                                    </div>
                                </div>
                            </div>
                            <div class="premium__pay">
                                <div>
                                    <div>ОПЛАТА</div>
                                    <div></div>
                                    <div>
                                        <div class="choose__plan_days">
                                            <div class="active__period">30 дней</div>
                                            <div>60 дней</div>
                                            <div>90 дней</div>
                                            <div>180 дней</div>
                                        </div>
                                        <input type="range" name="" id="" min='1' max='4' step="1" value="1" class="change_period_premium">
                                    </div>
                                </div>
                                <div>
                                    <div>
                                        <div><i class="fas fa-clock fa-3x"></i></div>
                                        <div>
                                            <div>990 ₽</div>
                                            <div>за 60 дней</div>
                                        </div>
                                    </div>
                                    <div><button class="btn_premium__pay">Оплатить</button></div>
                                </div>
                            </div>
                        </div>

                    </div>
                   


                </div>
            </div>
            <?
            include("../sections/footer.php");
            include("../sections/user_rightbar.php");
            ?>
        </div>
    </div>
    <?
    if ($user == false) {
        include("../sections/popup_auth.php");
        echo '<script src="/js/' . $configIncludes->auth . '"></script>';
    } else {
        echo '<script src="/js/' . $configIncludes->user_auth . '"></script>';
    }
    ?>
    <script>
        $(".change_period_premium").on("input change", function(e) {
            val = parseInt($(this).val());
            $(".active__period").removeClass("active__period");
            $(".choose__plan_days > div:nth-child(" + val + ")").addClass("active__period");
            switch (val) {
                case 1:
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:first-child', "199 ₽", 200);
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:last-child', "за 30 дней", 200);
                    break;
                case 2:
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:first-child', "470 ₽", 200);
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:last-child', "за 60 дней", 200);
                    break;
                case 3:
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:first-child', "550 ₽", 200);
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:last-child', "за 90 дней", 200);
                    break;
                case 4:
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:first-child', "999 ₽", 200);
                    anim_text_with_delay('.appContent>[page="premium"]>div>div.premium__buy_bar>div>div.premium__pay>div:nth-child(2)>div:nth-child(1)>div:last-child>div:last-child', "за 180 дней", 200);
                    break;
            }
        });
        $(".btn_premium__pay").click(function(e) {
            $(this).prop('disabled', true);
            var handler_id = "premium__buy_get_link";
            var plan = $(".change_period_premium").val();
            $.ajax({
                type: 'post',
                url: '/handler.php',
                data: {
                    handler_id,
                    plan
                },
                success: function(result) {
                    var json = JSON.parse(result);
                    switch (json.type) {
                        case 1:
                            location = json.url;
                            break;
                        case -1:
                            $.notify("Для покупки премиума нужно авторизоваться!", {
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