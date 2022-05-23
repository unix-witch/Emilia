<?php
    function get_server_uptime() {
        $ut = strtok( exec( "cat /proc/uptime" ), "." );
        $days = sprintf( "%2d", ($ut/(3600*24)) );
        $hours = sprintf( "%2d", ( ($ut % (3600*24)) / 3600) );
        $min = sprintf( "%2d", ($ut % (3600*24) % 3600)/60  );
        $sec = sprintf( "%2d", ($ut % (3600*24) % 3600)%60  );

        return "Uptime: $days:$hours:$min:$sec";
    }
?>