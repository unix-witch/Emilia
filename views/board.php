<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';

    $error = false;
    $post_sent = false;
    $post_error = "";
    $content_error = "";
    $title_error = "";
    $image_error = "";

    $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);
    
    function error($error) {                                                    //Echo error messages a bit better
        echo "<span class=\"error\">$error</span>";                             //Print the span that contains error info
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {                                 //Check if form was submitted

        if (empty($_SESSION["authed"]) || !$_SESSION["authed"])
            $post_error = "You are not signed in. Sign in to post";

        $username = $_SESSION["username"];                                      //Already sanitized and client cannot change
        $title   = filter_var($_POST["title"],   FILTER_SANITIZE_STRING);       //Filter inputs
        $content = filter_var($_POST["content"], FILTER_SANITIZE_STRING);       //Filter inputs
    
        if (empty($title)) $title_error     = "Title cannot be empty";          //Check if title empty
        if (empty($content)) $content_error = "Content cannot be empty";        //Check if content empty
    
        if (strlen($title) > $max_title_size)                                   //Check if title too long
            $title_error = "Title is too long"; 
        
        if (strlen($content) > $max_content_size)                               //Check if content too big 
            $content_error = "Content is too long";

        if (!empty($post_error) ||
            !empty($content_error) ||
            !empty($title_error) ||
            !empty($image_error)) $error = true;                                //Check if there are any errors

        if (!$error) {

            $new_data = array();
            $image_path = "";
            $current_date = date("Y-m-d H:i:s");                                //Get the current date for timestamping
            
            $file_ext = end((explode(".", $_FILES['files']['name'][0])));       //get the extension of the file
            $file = $_FILES['files']['tmp_name'][0];                            //name of the temporary file
            
            $file_name = basename($_FILES["files"]["tmp_name"][0]);             //name without directory
            $database_name = $file_name . "." . $file_ext;                      //name in the static file databse

            $is_image = @is_array(getimagesize($file));                         //Check if file is image
            $file_uploaded = !empty($file);                                     //Check if file was uploaded

            $should_upload = 
                ($is_image && $file_uploaded) ||                                //Continue if file and is image
                (!$file_uploaded);                                              //Continue if no file uploaded

            if ($should_upload) {                                               //Check if data should be uploaded
                $image_status = move_uploaded_file(                             //Move the file into the actual database
                    $_FILES["files"]["tmp_name"][0],                            //Temporary file path
                    "./database/images/" . $database_name                       //Database images path
                );
                


                if ($image_status)                                              //Check if image was uploaded
                    $image_path = "/database/images/" . $database_name;         //relative URL to the uploaded image
                else $image_path = NULL;                                        //image is null if no image uploaded

                $new_data["data"] = $current_date;                              //date
                $new_data["title"] = $title;                                    //title of the post
                $new_data["comments"] = array(
                    "0" => array(                                               //0 is the first comment always
                        "user" => $username,                                    //Username of the first "comment" 
                        "cont" => nl2br($content),                              //Actual content,
                        "time" => $current_date,                                //Date to check if something is dead or not
                        "image" => $image_path                                  //Path to the image data
                    ),
                    "-1" => array(                                              //Put here to that json works correctly
                        "placeholder" => true
                    )
                );

                $listing_dta = file_get_contents("./database/posts/posts.json");//Get the listing data
                $listing_arr = json_decode($listing_dta, true);                 //Decode the json data


                $post_number = intval($listing_arr["max"][$user_request] + 1);  //Number of the post
                $listing_arr["max"][$user_request] = $post_number;              //Update the recent number
                array_unshift(                                                  //Append to the listing arr
                    $listing_arr["recent"][$user_request], $post_number
                );

                $listing_arr["recent"][$user_request] = 
                    array_unique($listing_arr["recent"][$user_request]);        //Remove unique values from array

                $fp = fopen(                                                    //Open a new post JSON file
                    "./database/posts/" . 
                    $user_request . 
                    '/' .
                     $post_number . 
                    '.json',
                    "w"
                );

                

                fwrite($fp, json_encode($new_data));                            //Write the json data of the post
                fclose($fp);                                                    //Close the file

                $fp = fopen("./database/posts/posts.json", "w");                //Open the listing file

                fwrite($fp, json_encode($listing_arr));                         //Update the file
                fclose($fp);                                                    //Close the file

            } else $image_error = "File is not an image";                       //Throw error if not image
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $user_request ?></title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie: board">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/board.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>

        <div class="board-form-contents">
            <div class="header-div">
                <h1 class="board-header"><?php echo $user_request ?></h1>
                <h4><?php echo $boards[$user_request]; ?></h4>
                
                <?php
                    if ($post_sent) {
                        echo "<p>Your message has been posted<p>";
                    }
                ?>

                <form 
                    method="POST" 
                    action="<?php echo $form_action ?>" 
                    enctype="multipart/form-data"
                >
                    <div class="form-div">
                        <?php error($post_error) ?>
                        <label for="title">
                            Title: <?php error($title_error); ?>
                        </label>
                        <input name="title" id="title">

                        <label for="content">
                            Content: <?php error($content_error); ?>
                        </label>
                        <textarea name="content" id="content"></textarea>
                        
                        <label for="image">
                            Image: <?php error($image_error); ?>
                        </label>
                        <input type="file" name="files[]" id="image"><br>
                        
                        <div style="text-align: center">
                            <input type="submit" value="Post">
                        </div>
                    </div>
                </form>
                <br>
                <br>
            </div><br>

            <?php
                $board_name = $user_request;                                    //Here to make code more clear
                $board_directory = "./database/posts/$board_name/";             //Directory of the board
                $files = scandir($board_directory, SCANDIR_SORT_DESCENDING);    //Files have timestamps in name. use this
                unset($files[sizeof($files) - 2]);
                unset($files[sizeof($files) - 1]);

                $list_data = file_get_contents("./database/posts/posts.json");
                $list_data = json_decode($list_data, true);
                $list_data = $list_data["recent"];
                $new_list_data = array();


                foreach ($list_data[$board_name] as $current_post) {                             //Loop over files
                    
                    $post_number = $current_post;
                    $file_data = file_get_contents(
                        "./database/posts/$board_name/$post_number.json"
                    );

                    $file_data = json_decode($file_data, true);

                    $latest_id = max($file_data["comments"]);
                    $comment = $latest_id["cont"];
                   
                    
                    echo "<div class=\"header-div\">";
                    echo "<h3 class=\"board-post\">";
                    echo "<a href=\"/$board_name/$post_number\">";
                    echo $file_data["title"] . ': ' . $comment . '<br>';
                    
                    echo "</a>";
                    echo "</h3>";
                    echo "</div><br>";
                }
            ?>
        </div>

        <?php display_footer(); ?>
    </body>
</html>