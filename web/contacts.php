<?
include(__DIR__."/mongo/mainMongo.php");
$user = user_auth();
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
            <div page='contacts'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/contacts/tag.png" alt="">
                        <div class="font-bold">#КОНТАКТЫ</div>
                    </div>
                    <div>
                        <div>
                            <div>
                                <div>
                                    <div><i class="fas fa-envelope fa-6x"></i></div>
                                    <div>Почта поддержки</div>
                                    <div>support@bloodgodz.com</div>
                                </div>
                                <div>
                                    <div><i class="fas fa-envelope fa-6x"></i></div>
                                    <div>Почта для предложений сотрудничества</div>
                                    <div>support@bloodgodz.com</div>
                                </div>
                            </div>
                            <div>
                                <div class="">
                                    <a href="/"><img src="/files/images/icons/telegram.png" alt=""></a>
                                    <a href="/"><img src="/files/images/icons/vk.png" alt=""></a>
                                    <a href="/"><img src="/files/images/icons/discord.png" alt=""></a>
                                </div>
                            </div>
                            <div>
                                <div>Тут в будущем будет уже наш ИП (номера ОГРНИП и прочая законная лабуда</div>
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