<?php 
    function echoFooterText() {
        require 'configs/config.php';
        
        echo "
            Original software created using <a href=\"\">{$software_name}</a>. 
            
            Original author is <a>
                roylatgnail
            </a>
        ";
    }
?>