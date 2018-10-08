<?php
session_start();
?>

<?php
    echo date('Y-m-d H:i:s');
    echo '<br/>';
    
    $connection = mysqli_connect('localhost','root','','auth');
    mysqli_set_charset($connection,'utf8'); 
    
    if( $connection == false)
    {
        echo 'No connect server<br>';
        echo mysqli_connect_error();
        exit();
    }
    echo 'Connect server<br>';
	echo '<br/>';

	$stream_opts = [
    	"ssl" => [
        	"verify_peer"=>false,
        	"verify_peer_name"=>false,
    		]
	];  

$client_id = '6710165'; // ID приложения
$client_secret = '-- Secret KEY --'; // Защищённый ключ
$redirect_uri = 'http://localhost/vk-auth'; // Адрес сайта

$url = 'http://oauth.vk.com/authorize';

$params = array(
'client_id' => $client_id,
'redirect_uri' => $redirect_uri,
'response_type' => 'code',
'display' => 'popup',
'scope' => 'friends,groups,email,offline',
'response_type' => 'code&v=5.80',
);

if (isset($_GET['code'])) {
$params = array(
'client_id' => $client_id,
'client_secret' => $client_secret,
'code' => $_GET['code'],
'redirect_uri' => $redirect_uri
);

$token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params)),false,stream_context_create($stream_opts)), true);


if(isset($token)){
$params=[
'user_ids' => $token['user_id'],
'fields' => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
'access_token' => $token['access_token'],
'v' => '5.80',
];

$userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params)),false,stream_context_create($stream_opts)), true);

$extended = 1;
$count = 5;
$g_params=[
'user_id' => $token['user_id'],
'extended' => $extended,
'access_token' => $token['access_token'],
'count' => $count,
'v' => '5.80',
];

$groupsInfo = json_decode(file_get_contents('https://api.vk.com/method/groups.get' . '?' . urldecode(http_build_query($g_params)),false,stream_context_create($stream_opts)), true);

$groupsInfo = $groupsInfo['response']['items'];
$userInfo   = $userInfo['response'][0];

}else{
exit('Ошибка авторизации');
}


	if (isset($userInfo)) {
$sql = "SELECT * FROM users WHERE social_id = ".$token['user_id']." LIMIT 1 ";
$result = mysqli_query($connection, $sql);

	if (!mysqli_num_rows($result) > 0) {
    // output data of each row

	$foto  = "'".$userInfo['photo_big']."'";
	$bdate = "'".$userInfo['bdate']."'";
	$fname = "'".$userInfo['first_name']."'";
   		
	   
	$sql = "INSERT INTO users (social_id, sex, avatar, birthday, name)
	VALUES (".$token['user_id'].",".$userInfo['sex'].",".$foto.",".$bdate.",".$fname.")";

	if (mysqli_query($connection, $sql)) {
    echo "New record created successfully";
	} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($connection);
	}	
//	mysqli_close($connection);
//  добавляем в базу группы
	for ($i = 0; $i <= $count-1; $i++) {
	$gfoto = "'".$groupsInfo[$i]['photo_100']."'";
	$gname = "'".$groupsInfo[$i]['name']."'";
	$sqlgroups = "INSERT INTO groups (name, avatar)
	VALUES (".$gname.",".$gfoto.")";
	if (mysqli_query($connection, $sqlgroups)) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sqlgroups . "<br>" . mysqli_error($connection);
		}
	}

	}
	while($row = mysqli_fetch_assoc($result)) {
        echo "id: " . $row["social_id"]. "<br>";
    }
	}

//  Создаем сессию для users 
	$query = "SELECT * FROM users WHERE social_id = ".$token['user_id']." LIMIT 1 ";
	$user = mysqli_query($connection, $query);

	while($row = mysqli_fetch_array($user))
{
    $user_i = $row['social_id'];
    $fname = $row['name'];
    $bday = $row['birthday'];
    $avatar = $row['avatar'];
}

//  Создаем сессию для groups 
	$query = "SELECT * FROM `groups` LIMIT 5";
	$group_params = mysqli_query($connection, $query);


    for ($i = 0; $i <= $count-1; $i++) {
       	$row = mysqli_fetch_assoc($group_params);
    	$grname[$i] = $row['name'];
    	$gavatar[$i] = $row['avatar'];
    }

   
$group_params = array(
    'gname' =>$grname,
    'gavatar' =>$gavatar
);
    


$user_params  = array(
	'user_id' =>$user_i,
	'fname'   => $fname,
	'bday'    => $bday,
	'avatar'  =>$avatar,
    'gname' =>$grname,
    'gavatar' =>$gavatar
		
);

$_SESSION['user']  = $user_params;
//-------------------------------------------------------------------
}
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
	echo date('Y-m-d H:i:s');
    echo '<br/>';
if (isset($_SESSION['user'])) {
	echo '<p><a href="info.php">Закрытый контент</a></p>';
	$info = $_SESSION['user'];
	echo '<img src="' . $info[avatar] . '" />'; echo "<br />";
    echo "Социальный ID пользователя: " . $info[user_id] . '<br />';
    echo "Имя пользователя: " . $info[fname] . '<br />';
    echo "День Рождения: " . $info[bday] . '<br />';
//- выводим группы
    echo "Мои группы: " ;
    echo "<br />";
    for ($i = 0; $i <= 4; $i++) {
    echo "Группа: " . $info['gname'][$i];
    echo '<img src="' . $info['gavatar'][$i] . '" />'; echo "<br />";
	}

    echo '<p><a href="logout.php">Выйти из Аутентиикации</a></p>';
} else if (!isset($_GET['code']) && !isset($_SESSION['user'])) {
    
	echo $link = '<p><a href="' . $url . '?' . urldecode(http_build_query($params)) . '">Аутентификация через ВКонтакте</a></p>';

}
?>
</body>
</html>

