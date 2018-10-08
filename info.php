<?php
session_start();
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
    <title></title>
</head>
<body>

 

<?php
	 
	echo '<p><a href="index.php">Главная</a></p>'; 
    $info  = $_SESSION['user'];
       
    echo '<img src="' . $info[avatar] . '" />'; echo "<br />";
    echo "Социальный ID пользователя: " . $info[user_id] . '<br />';
    echo "Имя пользователя: " . $info[fname] . '<br />';
    echo "День Рождения: " . $info[bday] . '<br />';
   
    echo "Мои группы: " ;
    echo "<br />";
    for ($i = 0; $i <= 4; $i++) {
    echo "Группа: " . $info['gname'][$i];
    echo '<img src="' . $info['gavatar'][$i] . '" />'; echo "<br />";
	}
    
    echo '<p><a href="index.php">Главная</a></p>';

?>
</body>

</html>
