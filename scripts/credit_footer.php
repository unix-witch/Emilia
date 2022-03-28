<?php 
    function echoFooterText() {
        require 'configs/config.php';
        
        echo "
            Original software created using <a href=\"{$software_link}\">{$software_name}</a>. 
            
            Original author is <a href=\"{$author_link}\">
                roylatgnail
            </a>
        ";
    }
?>