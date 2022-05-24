<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';

    function error($error) {                                                    //Echo error messages a bit better
        echo "<span class=\"error\">$error</span>";                             //Print the span that contains error info
    }

    $post_error  = "";
    $cont_error  = "";
    $image_error = "";
    $error = false;
    
    $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {                                 //Check if POST method has been sent
        if (empty($_SESSION["authed"]) || !$_SESSION["authed"])
            $post_error = "You are not signed in. Sign in to post";


        $username = $_SESSION["username"];
        $content  = filter_var($_POST["content"], FILTER_SANITIZE_STRING);
        $board_name = $user_request;

        if (empty($content)) $cont_error = "Content cannot be empty";
        if (empty($post_error) || empty($cont_error)) $error = true;

        if (!$error) {
            $image_path = "";
            $post_data    = array();
            $comment_data = array();
            $current_date = date("Y-m-d H:i:s"); 

            $thread = $thread_check[2];
            $file_name = $_FILES['files']['name'][0];
            $file_ext = end((explode(".", $file_name)));                        //get the extension of the file
            $file = $_FILES['files']['tmp_name'][0];                            //name of the temporary file
            $file_name = basename($_FILES["files"]["tmp_name"][0]);             //name without directory
            $database_name = $file_name . "." . $file_ext;                      //name in the static file databse

            $is_image = @is_array(getimagesize($file));                         //Check if file is image
            $file_uploaded = !empty($file);                                     //Check if file was uploaded

            $should_upload = 
                ($is_image && $file_uploaded) ||                                //Continue if file and is image
                (!$file_uploaded);  

            if ($should_upload) {                                               //Check if data should be uploaded
                $image_status = move_uploaded_file(                             //Move the file into the actual database
                    $_FILES["files"]["tmp_name"][0],                            //Temporary file path
                    "./database/images/" . $database_name                       //Database images path
                );

                if ($image_status)
                    $image_path = "/database/images/" . $database_name;
                else $image_path = NULL;
                
                $t_id = intval($thread);

                $comment_data["user"] = $username;
                $comment_data["cont"] = $content;
                $comment_data["time"] = $current_date;
                $comment_data["image"] = $image_path;

                $listing_data = file_get_contents(
                    "./database/posts/posts.json"
                );

                $post_data = file_get_contents(
                    "./database/posts/$board_name/$thread.json"
                );

                $listing_data = json_decode($listing_data, true);
                $post_data = json_decode($post_data, true);
                $post_data["comments"][
                    max(array_keys($post_data["comments"]))+1
                    ] 
                    = $comment_data;
                

                echo "<br>";

                array_unshift($listing_data["recent"][$board_name], $thread);
                $listing_data["recent"][$board_name] = 
                    array_unique($listing_data["recent"][$board_name]);

                
                $fp = fopen("./database/posts/$board_name/$thread.json", "w");
                fwrite($fp, json_encode($post_data) . '\n');
                fclose($fp);
            } else $image_error = "File is not an image";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $user_request . ':' . $thread_check[2]?></title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie: Register">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/thread.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>

        <div class="thread-form-contents">
            <div class="header-div">
                <h1 class="thread-header">
                    <?php
                        $board_name = $user_request;
                        $thread = $thread_check[2];
                        $thread_path = 
                            "./database/posts/$board_name/$thread.json";
                            
                        
                        $post_file = file_get_contents($thread_path);
                        $thread_data = json_decode($post_file, true);

                        echo $thread_data["title"];
                    ?>
                </h1>
                
                

                <form 
                    method="POST" 
                    action="<?php echo $form_action ?>"
                    enctype="multipart/form-data"
                >
                    <div class="form-div">
                        <?php error($post_error) . '<br>'; ?>
                        <label>Content: <?php error($cont_error); ?></label>
                        <textarea name="content" id="content"></textarea>

                        <label>Image: <?php error($image_error); ?></label>
                        <input type="file" name="files[]" id="image"><br>

                        <div style="text-align: center">
                            <input type="submit" value="Comment">
                        </div>
                    </div>
                </form><br><br>

                <a href="/<?php echo $board_name; ?>">Go back</a><br>

                <?php

                    foreach($thread_data["comments"] as $comment) {             //Loop over the comments
                        $image_path = $comment["image"];


                        if ($comment["placeholder"]) continue;

                        $username = $comment["user"];

                        echo "<div class=\"thread-comment\">";
                        echo $comment["time"] . '<br>';
                        echo "<a href=\"/usr/$username\">".$username."</a>";
                        echo ': ' . $comment["cont"];
                        echo "<br>";

                        if ($comment["image"] != null)
                            echo "<img class=\"img\" src=\"$image_path\">";

                        echo "</div>";
                    }
                ?>
                <a href="/<?php echo $board_name; ?>">Go back</a><br>
            </div>
        </div>
        <br><br>

        <?php display_footer(); ?>
    </body>
</html>
