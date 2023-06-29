<?php
    session_start();
    require 'vendor/autoload.php';
    $m = new MongoDB\Client("mongodb://127.0.0.1/");
    $db = $m->ttkeys;
    $collection = $db->users;
    $user = $collection->findOne(["username" => $_SESSION["username"]]);
    if ($user) {
        $logout_time = new MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ["username" => $_SESSION["username"], "login_records.login" => $_SESSION["login_time"]],
            ['$set' => ["login_records.$.logout" => $logout_time]]
        );
    }
    session_unset();
    session_destroy();
    header("Location: ./index.php");
    exit();
?>
