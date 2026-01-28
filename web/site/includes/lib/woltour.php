<?php
function send_wol($mac, $broadcast = "255.255.255.255", $port = 9) {
    $mac = str_replace([':', '-'], '', $mac);
    if (strlen($mac) != 12) return false;

    $packet = str_repeat(chr(0xFF), 6);
    for ($i = 0; $i < 16; $i++) {
        $packet .= pack('H12', $mac);
    }

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock === false) return false;

    socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);
    $result = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, $port);
    socket_close($sock);

    return $result !== false;
}

function ping_pc($ip, $timeout = 2) {
    if (stripos(PHP_OS, 'WIN') === 0) {
        $output = shell_exec("ping -n 1 -w " . ($timeout*1000) . " " . escapeshellarg($ip));
        return (strpos($output, "TTL=") !== false);
    } else {
        $output = shell_exec("ping -c 1 -W $timeout " . escapeshellarg($ip));
        return (strpos($output, "ttl=") !== false);
    }
}
