<?php
if(!isset($_SESSION)){
    session_start();
}
include('config.php');
include('variablesandFunctions.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://www.google.com/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <title><?= $title ?></title>
</head>

<body>
    <div class="guestbookarea">
        <h1 id="welcome">Welcome to <?= $title ?>!</h1>
        <p id="madeby">Powered by <a href="https://github.com/javierbarthe" target="_blank"> <?php echo $dbtech ?></a></p>
        
        <form method="POST">
            <p class="inputid">Name:</p>
            <p><input type="text" name="name" class="inputfields" maxlength="60" placeholder="Your Name"></p>

            <p class="inputid">Message:</p>
            <p><textarea name="message" rows="3" maxlength="600" class="inputfields" placeholder="Your Message"></textarea></p>

            <input type="submit" value="send" title="Send your message!">
            <input type="hidden" name="action" value="sendmessage">
        </form>
        <p id="sessionmsg"> <span></span></p>
        <p>Current Time:  <?= date('Y-m-d H:i:s') ?></p>
        <?php
            if(isset($_POST['name']) && isset($_POST['message'])) {
                $name = $_POST['name'];
                $message = $_POST['message'];
                $timedate = date("Y-m-d H:i:s");
            
                $sqlcmd = "INSERT INTO $schema.$tablename (name, message, date) VALUES ('$name', '$message', '$timedate');";
                $stmt = $conn->prepare($sqlcmd);
                $stmt->execute();
                unset($_POST['name']);
                unset($_POST['message']);
            }

            if ($dbtech == 'Cloud SQL for PostgreSQL w/ Redis'){
                $key = $tablename;
                if (!$redis->get($key)) {
                    $hit='La app esta haciendo lectura de la DB PgSQL (Cache Miss)';

                    $sqlcmd = "SELECT * FROM $schema.$tablename ORDER BY id DESC";
                    $stmt = $conn->prepare($sqlcmd);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $messages[] = $row;
                    }

                    $redis->set($key, serialize($messages));
                    $redis->expire($key, 10);

                } else {
                    $hit='La app esta haciendo CACHE HIT en MemoryStore for REDIS';
                    $messages = unserialize($redis->get($key));
                }

            }
            else {
                    $hit='La app no esta utilizando MemoryStore para Caching';

                    $sqlcmd = "SELECT * FROM $schema.$tablename ORDER BY id DESC";
                    $stmt = $conn->prepare($sqlcmd);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $messages[] = $row;
                    }
            }    
            foreach ($messages as $result):
        ?>
        <div class="outputarea">
            <div class="user">
                <p><?= ($result['name']) ?><span><?= ($result['date']) ?> <?=$timezone?></span></p>
            </div>
            <p><?= ($result['message']) ?></p>
        </div>
        <?php
            endforeach;
        ?>
        <div class="pagination">
            <p><?php echo $hit; ?></p>
        </div>
    </div>
</body>

</html>