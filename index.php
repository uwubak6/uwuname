<?php

global $link;
require_once __DIR__ . "/scripts/connect.php";
require_once __DIR__ . "/util/UserManager.php";
require_once __DIR__ . "/util/RandomUtil.php";

if (HTTPS) {
    echo " <script>
    if (window.location.protocol != 'https:')
      window.location.href = 'https:' + window.location.href.substring(window.location.protocol.length);
    </script> ";
}

if (NOINDEX) {
    $meta = "noindex, ";
} else {
    $meta = "index, ";
}
if (NOFOLLOW) {
    $meta .= "nofollow";
} else {
    $meta .= "follow";
}

if (!isset($_GET['page'])) $_GET['page'] = '';
if ($_GET['page'] == 'logout') {
    $delete_auth_key_stmt = $link->prepare("DELETE FROM `auth_keys` WHERE `key` = ?");
    $delete_auth_key_stmt->bind_param("s", $_COOKIE['auth_key']);
    $delete_auth_key_stmt->execute();
    setcookie("usr", "", time() - 3600);
    setcookie("auth_key", "", time() - 3600);
}

if (isset($_POST['login']) and $_POST['login'] == 1) {
    $username = $_POST['usr'];
    $password = $_POST['pw'];

    if ($username == '' or $password == '') {
        $error = '<div class="al_alert">Please enter username and password</div>';
    } else {
        if (validateUser($username, $password)) {
            setcookie('usr', md5($_POST['usr']), time() + 14400, "/");
            setcookie('auth_key', $cookie_value = generateRandomString(), time() + 14400, "/");
            $key = $cookie_value;
            $usr = md5($_POST['usr']);

            $del_usr_stmt = $link->prepare("DELETE FROM `auth_keys` WHERE `user` = ?");
            $del_usr_stmt->bind_param("s", $_POST['usr']);
            $del_usr_stmt->execute();

            $add_auth_key_stmt = $link->prepare("INSERT INTO `auth_keys` (`user`,`key`) VALUES (?, ?)");
            $add_auth_key_stmt->bind_param("ss", $_POST['usr'], $cookie_value);
            $add_auth_key_stmt->execute();
        } else {
            $error = '<div class="al_alert">Username or password are incorrect</div>';
        }
    }
}

function getButton($name, $url)
{
    echo "<a href='?page=$url'> <div style='cursor: pointer;' class='al_btn'>
            <div class='anim_btn'>
              $name
            </div>
            $name
          </div> </a>";
}

$loggedIn = 0;
if (!isset($usr) and isset($_COOKIE['usr'])) $usr = $_COOKIE['usr'];
if (!isset($key) and isset($_COOKIE['auth_key'])) $key = $_COOKIE['auth_key'];
if (!isset($key)) $key = '';
if (!isset($usr)) $usr = '';

$select_auth_key_stmt = $link->prepare("SELECT * FROM `auth_keys` WHERE `key`= ?");
$select_auth_key_stmt->bind_param("s", $key);
$select_auth_key_stmt->execute();
$res = $select_auth_key_stmt->get_result();

if ($res->num_rows > 0) if (md5(mysqli_result($res, 0, 'user')) == $usr) $loggedIn = 1;

$page = $_GET["page"];
if ($page == '' or $page == 'logout') $page = "dashboard";
if ($loggedIn == 0) $page = "login";
?>


<!DOCTYPE html>
<html ng-app="License">
<head>
    <meta charset="utf-8">
    <title>AdvancedLicense-System</title>

    <meta name="robots" content="<?php echo $meta; ?>">

    <script src='https://code.jquery.com/jquery-latest.min.js' type='text/javascript'></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
          integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.9/angular-animate.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="scripts/Angular.JS"></script>
    <link rel='stylesheet' href='css/master.css' type='text/css' charset='utf-8'>
</head>
<body>
<div class="al_nav">
    <div class="title">
        AdvancedLicense
        <i>Coded by Leoko</i>
    </div>

    <?php getButton(" Dashboard", "dashboard"); ?>
    <?php getButton(" Manage  license", "manage"); ?>
    <?php getButton(" Add license", "add"); ?>
    <?php getButton(" Logout", "logout"); ?>
</div>

<div class="content"> <?php require "content/$page.php"; ?> </div>
</body>
</html>
