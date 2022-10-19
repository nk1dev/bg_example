<?
include(__DIR__ . "/mongo/mainMongo.php");
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
            <div page='faq'>
                <div class="">
                    <img src="/files/images/top/flashLight.png" alt="">
                    <div>
                        <img src="/files/images/faq/tag.png" alt="">
                        <div class="font-bold">#FAQ</div>
                    </div>
                    <div>
                        <div>
                            
                        </div>
                    </div>
                </div>
            </div>
            <?
            include(__DIR__."/sections/footer.php");
            ?>
        </div>
    </div>
    <script>
      

    </script>
    <?
    if ($user== false) {
        include(__DIR__ . "/sections/popup_auth.php");
        echo '<script src="/js/'.$configIncludes->auth.'"></script>';
    }else {
        echo '<script src="/js/'.$configIncludes->user_auth.'"></script>';
    }
    ?>
</body>