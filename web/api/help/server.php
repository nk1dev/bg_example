<?
echo '<h1>Start</h1><hr>';
$num = (int)$_GET['num'];
$ssh= (int)$_GET['ssh'];
for ($i=0; $i < $num; $i++) { 
    $pos = $i +1;
    if ($pos == 1) {
        echo "./csgoserver st && ";
    }else {
        echo "./csgoserver-".($pos). " st && ";
    }
}
echo '<br><h1>Stop</h1><hr>';
for ($i=0; $i < $num; $i++) { 
    $pos = $i +1;
    if ($pos == 1) {
        echo "./csgoserver sp && ";
    }else {
        echo "./csgoserver-".($pos). " sp && ";
    }
   
 }
 echo '<br><h1>Stop</h1><hr>';
for ($i=0; $i < $num; $i++) { 
    $pos = $i +1;
    if ($pos == 1) {
        echo "./csgoserver r && ";
    }else {
        echo "./csgoserver-".($pos). " r && ";
    }
   
 }
 echo '<br><h1>Kill server tmux</h1><hr>';
 echo 'tmux kill-server';
echo "<hr>";
for ($i=0; $i < $num; $i++) { 
    $pos = $i+1;
    $port = 27010 + $i;
    echo "<br>INSERT INTO `servers` (`id`, `ssh_id`, `id_version_csgo`, `port`, `verison_csgo_id`, `config`, `rcon`, `visible`) VALUES (NULL, '$ssh', '2', '$port', '1', 'csgoserver-$pos', 'ul9FZWhRVXPxyTHnrqQzlzC54ohAfaVD', '0');";
}