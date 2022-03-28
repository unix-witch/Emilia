<!DOCTYPE html>
<html>
    <head>
        <title>Admin Dashboard</title>

        <?php
            session_start();


            if ($_SESSION['type'] <= 1) {
                require 'views/errors/403.php';
                die;
            }


            $error = false;

            $usernameError = "";
            $typeChangeError = "";
            $banUserError = "";

            $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
            $typeChange = $_POST["type-change"];

            $database = new SQLite3("./database/users.db");
            $database->query("CREATE TABLE IF NOT EXISTS Users (
                UserID          INTEGER             NOT NULL PRIMARY KEY AUTOINCREMENT,
                UserName        VARCHAR(256)        NOT NULL,   --Max username limit
                UserPass        VARCHAR(80)         NOT NULL,   --Use Bcrypt
                UserEmail       VARCHAR(256)        NOT NULL,   --Email of the user
                UserBiogr       VARCHAR(256)        NOT NULL,   --Bio of the user
                UserAddr        VARCHAR(13)         NOT NULL,   --IP address, used for bans
                UserCDate       VARCHAR(256)        NOT NULL,   --Date the user register
                UserLDate       VARCHAR(256)        NOT NULL,   --Date the user was last online
                UserCoins       INT                 NOT NULL,   --Coins
                UserBanned      INT                 NOT NULL,   --Is the user banned or not
                UserType        INT                 NOT NULL    --Just read the docs dont have time to explain
            );");

            if (!isset($username)) { $usernameError = "Username not set"; $error = true; }

            if (isset($typeChange)) {
                if ($typeChange != "") {
                    if ($typeChange > (int) $_SESSION["type"]) {
                        $typeChangeError = "Error: Your type is not high enough for this";
                    
                    
                    } else {
                        if (!$error) {
                            $database->query(sprintf(
                                "UPDATE Users SET UserType = %d WHERE UserName = '%s'",
                                (int) $typeChange,
                                $username
                            ));

                            $typeChangeError = "Type changed";
                        }
                    }
                }
            }

            if (isset($_POST["ban-user"])) {
                $userType = $_SESSION["type"];

                $targetTypeSqliteObj = $database->query(
                    sprintf(
                        "SELECT UserType FROM Users WHERE UserName = '%s'",
                        $username
                    )
                );

                $targetBanedSqliteObj = $database->query(
                    sprintf(
                        "SELECT UserBan FROM Users WHERE UserName = '%s'",
                        $username
                    )
                );

                $targetUsernameType = $targetTypeSqliteObj->fetchArray()[0];
                if ($userType < (int) $targetUsernameType) {
                    $banUserError = "Error: your type is too low for this";
                
                } else {
                    $database->query(sprintf(
                        "UPDATE Users SET UserBanned = %d WHERE UserName = '%s'",
                        0,
                        $username
                    ));
                }
            }


        ?>

        <link rel="stylesheet" href="/static/style.css">
    </head>
    <body>
        <div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label class="board-label" for="username">Username:</label>
                <input name="username" class="board-input" id="username" type="text">

                <label class="board-label" for="type-changer">Set user type: </label>
                <select name="type-change" id="type-changer">
                    <option value="">Dont change type</option>
                    <option value="0">Regular user</option>
                    <option value="1">Trail mod</option>
                    <option value="2">Regular mod</option>
                    <option value="3">Higher mod</option>
                    <option value="4">Admin</option>
                    <option value="5">Developer</option>
                </select><br>


                <label class="board-label" for="ban-box">Ban user? </label>
                <input type="checkbox" name="ban-user" id="ban-box">

                <input type="submit">
            </form>

            <span>
                <?php 
                    echo $usernameError . "<br>";
                    echo $typeChangeError . "<br>";
                    echo $banUserError . "<br>";
                ?>
            </span>
        </div>
    </body>
</html>