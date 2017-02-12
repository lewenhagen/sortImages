<?php

error_reporting(1);

include("config.php");
include("ImageHandler.php");

$newFinishedName = null;
$newUnfinishedName = null;

$newFinishedName = isset($_GET["finishedName"]) && $_GET["finishedName"] != null ? htmlentities($_GET["finishedName"]) : "finished";
$newUnfinishedName = isset($_GET["unfinishedName"]) && $_GET["unfinishedName"] != null ? htmlentities($_GET["unfinishedName"]) : "unfinished";

if(isset($_GET["go"])) {

    $handler = new ImageHandler($base, $newUnfinishedName, $newFinishedName);

    $handler->initFiles();
}

?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sort Images</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="main">
            <h4>Choose folder names (skip for default) and click OK</h4>
            <form method="GET">
                <table>
                    <tr><td>Finished folder name:</td><td><input type="text" max="20" name="finishedName"> (Default "finished")</td></tr>
                    <tr><td>Unfinished folder name:</td><td><input type="text" max="20" name="unfinishedName"> (Default "unfinished")</td></tr>

                    <tr><td><button type="submit" name="go" value="1">OK</button></td></tr>
                </table>
            </form>
        </div>
        <div class="info">
            <h4>Result:</h4>
            <p>
            <?php
                if(isset($_GET["go"])) {
                    echo $handler->getResult();
                }
            ?>
            <br><a href="index.php">NEW</a>
        </p>
        </div>
    </body>
</html>
