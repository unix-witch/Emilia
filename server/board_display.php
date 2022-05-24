<?php  
    function display_board($boards) {                                           //boards is arg because shitty php
        $username = $_SESSION["username"];

        echo "<div style=\"text-align: center;\" > ";                           //Echo the centering div
        
        if (!$_SESSION["authed"]) {
            echo "[ <a href=\"/login\">login</a>|";
            echo "<a href=\"/register\">register</a> ]";
        } else 
            echo "[ <a href=\"/usr/$username\">$username</a> ]";

        echo "[ ";

        foreach ($boards as $board_name => $board_desc) {                       //Look over boards with key and value
            echo "<a href=\"/$board_name\" class=\"board-display-link\">";      //Look to the board
            echo "<abbr title=\"".$board_desc."\">".$board_name."</abbr>";      //Echo a abbr with the name and title
            echo "</a> ";                                                       //Echo a space for good formatting
        }
        echo "]";

        if (!$_SESSION["authed"]) {
            echo "[ <a href=\"/session\">log out</a> ]";                        //Easy logout
        }

        echo "</div>";                                                          //Close the centering div
    }
?>