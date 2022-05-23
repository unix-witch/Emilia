<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';


    $username = filter_var($thread_check[2], FILTER_SANITIZE_STRING);           //Sanitize username because SQL used later
    $database = new SQLite3("./database/users/users.db");                       //Create a new sqlite3 database thing
    $user_exists = true;

    $user_banned = false;
    $user_address = "";
    $user_bio = "";
    $user_account_created = "";
    $user_perms = 0;

    $database->query("CREATE TABLE IF NOT EXISTS Users (
        UserID      INTEGER         NOT NULL PRIMARY KEY AUTOINCREMENT, --
        UserName    VARCHAR(256)    NOT NULL,                           --Username. 256 char limit
        UserPass    VARCHAR(80)     NOT NULL,                           --Password. 80 is bcrypt's hash size
        UserBio     VARCHAR(256)    NOT NULL,                           --User's bio. 256 limit
        UserAddr    VARCHAR(13)     NOT NULL,                           --User's IP address
        UserCDate   VARCHAR(256)    NOT NULL,                           --Date the account was created
        UserLDate   VARCHAR(256)    NOT NULL,                           --Last time user was online
        UserBanned  INT             NOT NULL,                           --Is the user banned or not
        UserPerms   INT             NOT NULL                            --Is the user mod, admin, dev, etc
    );");

    $user_exist = $database->query(sprintf(                             //Query to check if the user exists
        "SELECT UserID FROM Users WHERE UserName = '%s'", $username
    ));

    if (is_null($user_exist)) $account_exist = false;
    else {
        $account_exist = true;
        $user_banned_sql = sprintf(
            "SELECT UserBanned FROM Users WHERE UserName IS '%s'",
            $username
        );

        $user_address_sql = sprintf(
            "SELECT UserAddr FROM Users WHERE UserName IS '%s'",
            $username
        );

        $user_bio_sql = sprintf(
            "SELECT UserBio FROM Users WHERE UserName IS '%s'",
            $username
        );
        
        $creationdate_sql = sprintf(
            "SELECT UserCDate FROM Users WHERE UserName is '%s'",
            $username
        );

        $user_perms_sql = sprintf(
            "SELECT UserPerms FROM Users WHERE UserName is '%s'",
            $username
        );


        $user_banned_sql = $database->query($user_banned_sql);
        $user_address_sql = $database->query($user_address_sql);
        $user_bio_sql = $database->query($user_bio_sql);
        $creationdate_sql = $database->query($creationdate_sql);
        $user_perms_sql = $database->query($user_perms_sql);

        $user_banned = $user_banned_sql->fetchArray()[0];
        $user_address = $user_address_sql->fetchArray()[0];
        $user_bio = $user_bio_sql->fetchArray()[0];
        $user_account_created = $creationdate_sql->fetchArray()[0];
        $user_perms = $user_perms_sql->fetchArray()[0];
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php ?></title>
        
    
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/user.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <br><br>
        <?php
            if (!$account_exist) {
                c404();
                die;
            }

            


            if ($user_banned) {
                echo "<div class=\"user-banned\">";
                echo "<h1 class=\"user-banned-title\">Rip Bozo</h1>";
                echo "This user, $username ($user_address) has been banned";
                echo "</div><br><br>";
            }
            
        ?>

        <div class="user-content">
            <h1 class="user-content-title"><?php echo $username ?></h1>
            <div class="user-data">
                <?php 
                    switch ($user_perms) {                                      //Switch case for permission level
                        case 0: echo "This user is a regular user"; break;
                        case 1: echo "This user is a trial mod"; break;
                        case 2: echo "This user is a regular mod"; break;
                        case 3: echo "This user is a admin"; break;
                        case 4: echo "This user is a developer"; break;

                        default: echo "This users permissions are unknown";     //Shoudnt happen but here anyway
                    }
                    echo "<br>Account created on $user_account_created<br>";
                    echo "Bio: $user_bio";
                ?>
            </div>
        </div>
    </body>
</html>