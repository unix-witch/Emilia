<?php
    require './configs/router.php';
    require './configs/boards.php';
    require './configs/site.php';

    require './server/board_display.php';
    require './server/uptime.php';
    require './server/footer.php';

    session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Gay Internet Place</title>
        
    
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="robots" content="noindex, nofollow">

        <meta property="og:title"   content="Ellie">
        <meta property="og:type"    content="website">
        <meta property="og:url"     content="https://beau.tification.club">
        <meta property="og:image"   content="graph-image.png">

        <link rel="stylesheet"      href="/static/style.css">
        <link rel="stylesheet"      href="/static/announcments.css">
        <link rel="stylesheet"      href="/static/board-display.css">
    </head>
    <body>
        <?php display_board($boards); ?>
        <div style="text-align:center;"><h1><?php echo $site_name ?></h1></div>
        <h4><?php echo $random_messages[array_rand($random_messages)]; ?></h4>
        <div class="announcments-container">
            <?php                                                               //PHP to generate announcments
                $announc = new DirectoryIterator('./database/announcements');   //Iterator for the announcments
                foreach($announc as $file_info) {                               //Loop over files in announcments dir
                    if ($file_info->isDot()) continue;                          //Check if dot

                    $filename = $file_info->getFilename();                      //Get the filename

                    echo "<div class=\"announcments-post\">";                   //Announcments post div
                    readfile('./database/announcements/' . $filename);          //Put the contents into the DOM
                    echo "</div>";                                              //Close the div
                }
            ?>
        </div>

        <?php display_footer(); ?>
    </body>
</html>