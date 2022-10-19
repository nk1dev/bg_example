<?
include(__DIR__ . "/mongo/mainMongo.php");
$user = user_auth();
$news = get_news();
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
            <div page="index">
                <div>
                    <div>
                        <div class="agent">
                            <img class="" src="/files/solder.png" alt="">
                        </div>
                        <div class="container">
                            <div class="">
                                <div class="time_play">
                                    <button>ВРЕМЯ ИГРАТЬ</button>
                                </div>
                                <div class="news_slider">
                                    <div class="slider">
                                        <div>
                                            <?
                                            for ($i = 0; $i < count($news); $i++) {
                                                echo '<div>';

                                                echo "<div><img src='/files/images/news/news_img.png'></div>";
                                                echo "<div>";
                                                echo "<div>{$news[$i]->title}</div>";
                                                echo "<div>{$news[$i]->text}</div>";
                                                echo '</div>';

                                                echo '</div>';
                                            }
                                            ?>
                                        </div>

                                    </div>
                                    <div>
                                        <div>
                                            <div slider_left><img src="/files/images/icons/arrow.png" alt=""></div>
                                            <div>
                                                <input type="range" class="range__slider__news" min="0" max="<?= count($news) * 48; ?>" value="0">
                                            </div>
                                            <div slider_right><img src="/files/images/icons/arrow.png" alt=""></div>
                                        </div>
                                        <div>
                                            <div class=""><?= count($news); ?></div>
                                            <div></div>
                                            <div class="" textSliderPos>1</div>
                                        </div>
                                    </div>

                                </div>

                                <!--<div>
                                    <div>
                                        <a href="/"><img src="/files/images/icons/telegram.png" alt=""></a>
                                        <a href="https://vk.com/bg_league"><img src="/files/images/icons/vk.png" alt=""></a>
                                        <a href="https://discord.com/invite/GkqYNQ94Ca"><img src="/files/images/icons/discord.png" alt=""></a>
                                    </div>
                                    <div>
                                        <div class="">Server stats:</div>
                                        <div class="">
                                            <div class="">RUS<div class=""></div>
                                            </div>
                                            <div class="">EU<div class=""></div>
                                            </div>
                                            <div class="">US<div class=""></div>
                                            </div>
                                        </div>
                                    </div>
                                </div> --->
                            </div>
                        </div>
                        <div class="social__media">
                            <div>
                                <a href="https://t.me/bg_league"><i class="fab fa-telegram-plane fa-2x" style="border-radius: 9px; border: 1px solid #4E4E4E;color:#4E4E4E; padding:.5rem;"></i></a>
                                <a href="https://vk.com/bg_league"><i class="fab fa-vk fa-2x" style="border-radius: 9px; border: 1px solid #4E4E4E;color:#4E4E4E; padding:.5rem;"></i></a>
                                <a href="https://discord.gg/UgAxsafnnd"><i class="fab fa-discord fa-2x" style="border-radius: 9px; border: 1px solid #4E4E4E;color:#4E4E4E; padding:.5rem;"></i></a>
                            </div>
                        </div>
                        <div class="serverlist">
                            <img src="/files/images/news/flashLight1.png" alt="">
                            <div class="relative container m-auto text-white mt-20"><img class="absolute top-12 left-0" src="/files/images/news/news.png" alt="">
                                <div class="">
                                    <div class="flex xl:flex-row flex-col items-center">
                                        <div class="text-5xl font-bold mb-5 xl:mb-0 xl:mr-20 mr-0">#NEWS</div>
                                        <div class="flex-1 grid lg:grid-cols-3 grid-flow-row gap-4">
                                            <div style="display: none;" class="relative w-full rounded-full flex items-center pl-6 pr-12 py-1 whitespace-nowrap text-white bg-gradient-to-r from-green-600 to-green-400">
                                                <div class="">
                                                    <div class="text-gray-100">Russian server</div>
                                                    <div class="flex text-xs">
                                                        <div class="">Stable work</div>
                                                        <div class="ml-6">Ping: 20ms</div>
                                                    </div>
                                                </div>
                                                <div class="absolute top-1/2 right-2 transform -translate-y-1/2 w-7 h-7 rounded-full bg-green-300 border-2 border-green-200"></div>
                                            </div>
                                            <div style="display: none;" class="relative w-full rounded-full flex items-center pl-6 pr-12 py-1 whitespace-nowrap text-white bg-gradient-to-r from-red-600 to-red-400">
                                                <div class="">
                                                    <div class="text-gray-100">French server</div>
                                                    <div class="flex text-xs">
                                                        <div class="">Stable work</div>
                                                        <div class="ml-6">Ping: 60ms</div>
                                                    </div>
                                                </div>
                                                <div class="absolute top-1/2 right-2 transform -translate-y-1/2 w-7 h-7 rounded-full bg-red-400 border-2 border-red-300"></div>
                                            </div>
                                            <div class="relative w-full rounded-full flex items-center pl-6 pr-12 py-1 whitespace-nowrap text-white bg-gray-600" style="display: none;">
                                                <div class="">
                                                    <div class="text-gray-100">Baly server</div>
                                                    <div class="flex text-xs">
                                                        <div class="">Not work</div>
                                                    </div>
                                                </div>
                                                <div class="absolute top-1/2 right-2 transform -translate-y-1/2 w-7 h-7 rounded-full bg-gray-800 border-2 border-gray-700"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative my-20 flex flex-col space-y-5"></div>
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
        var scrollCount = 1;
        $("[slider_left]").click(() => {
            var val = $(".range__slider__news").val();
            var newOffset = 0;
            if (val < 40) {
                newOffset = 0;
                $(".range__slider__news").val(0);
            } else {
                newOffset = val - 40;
                $(".range__slider__news").val(newOffset);
            }
            $('.news_slider > .slider').animate({
                scrollLeft: newOffset * 7
            }, 400);
            var pageInt = parseInt(newOffset / 40);
            if (pageInt == 0) pageInt = 1;
            $("[textSliderPos]").text(pageInt);
        });

        $("[slider_right]").click(() => {
            var val = $(".range__slider__news").val();
            var max = parseInt($(".range__slider__news").attr("max"));
            var newOffset = 0;
            if ((val + 40) > max) {
                newOffset = max;
                $(".range__slider__news").val(newOffset);
            } else {
                newOffset = val + 40;
                $(".range__slider__news").val(newOffset);
            }
          //  $(".news_slider > .slider").scrollLeft(newOffset * 7);
            $('.news_slider > .slider').animate({
                scrollLeft: newOffset * 7
            }, 400);
            var pageInt = parseInt(newOffset / 40);
            if (pageInt == 0) pageInt = 1;
            $("[textSliderPos]").text(pageInt);
        });
        $('.range__slider__news').on('input change', function() {
            var val = $(".range__slider__news").val();
            $('.news_slider > .slider').animate({
                scrollLeft: val * 7
            }, 10);
            var pageInt = parseInt(val / 40);
            if (pageInt == 0) pageInt = 1;
            $("[textSliderPos]").text(pageInt);
        });
    </script>

</body>