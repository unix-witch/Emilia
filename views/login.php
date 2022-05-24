<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';

    $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);

    function error($error) {                                                    //Echo error messages a bit better
        echo "<span class=\"error\">$error</span>";                             //Print the span that contains error info
    }

    $username_error = "";
    $password_error = "";

    $error = false;
    $logged_in = false;


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
        $password = filter_var($_POST["pass"], FILTER_SANITIZE_STRING);

        if (empty($username)) $username_error = "Username cannot be empty";
        if (empty($password)) $password_error = "Password cannot be empty";

        if (!empty($username_error) || !empty($password_error)) $error = true;


        if (!$error) {

            $database = new SQLite3("./database/users/users.db");
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

            $user_exist = $database->query(sprintf(
                "SELECT UserID FROM Users WHERE UserName = '%s'", $username
            ));


            if (!is_null($user_exist)) {
                $password_hash_str = sprintf(
                    "SELECT UserPass FROM Users WHERE UserName IS '%s'", 
                    $username
                );

                $user_banned_str = sprintf(
                    "SELECT UserBanned FROM Users WHERE UserName IS '%s'",
                    $username
                );

                $user_perms_str = sprintf(
                    "SELECT UserPerms FROM Users WHERE UserName IS '%s'",
                    $username
                );

                
                $password_sqlobj  = $database->query($password_hash_str);
                $banned_sqlobj    = $database->query($user_banned_str);
                $perms_sqlobj     = $database->query($user_perms_str);

                $password_db_hash = $password_sqlobj->fetchArray()[0];
                $banned_db_value  = $banned_sqlobj->fetchArray()[0];
                $perms_db_value   = $perms_sqlobj->fetchArray()[0];

                if (password_verify($password, $password_db_hash)) {
                    $_SESSION['username'] = $username;
                    $_SESSION["banned"]   = $banned_db_value;
                    $_SESSION["authed"]   = true;
                    $_SESSION["perms"]    = $perms_db_value;


                    $logged_in = true;
                } else $password_error = "Password does not match";
            } else $username_error = "Account does not exist";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie: Login">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/login.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>
        
        <div class="login-form-contents">
            <div class="header-div">
                <h1 class="login-header">Login</h1>
            </div>

            <div class="header-div">
                <p>
                    <?php
                        if ($logged_in) {
                            echo "You have been logged in!<br>";
                            echo "You can return to the homepage ";
                            echo "<a href=\"/\">here</a>";
                        }
                    ?>
                </p>
            </div>

            <form method="post" action="<?php echo $form_action ?>">
                <label for="username">Username <?php error($username_error); ?></label>
                <input type="text" name="username" id="username">

                <label for="password">Password <?php error($password_error); ?></label>
                <input type="password" name="pass" id="password">

                <div style="text-align: center">
                    <input type="submit" value="login">
                </div>
            </form>
        </div>
        <?php display_footer(); ?>
    </body>
</html>