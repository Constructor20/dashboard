<?php

function ssh_start_api() {
    $ssh_key = "/var/www/ssh/id_ed25519";
    $ip = "192.168.1.22";
    $user = "aleix";

    // Nouvelle commande : lance la tâche planifiée "MinecraftAPI"
    $cmd = 'schtasks /Run /TN "MinecraftAPI"';

    return ssh_exec($ssh_key, $user, $ip, $cmd);
}

function wait_for_ssh($ip, $port = 22, $timeout = 60) {
    $elapsed = 0;
    while ($elapsed < $timeout) {
        $conn = @fsockopen($ip, $port, $errno, $errstr, 2);
        if ($conn) {
            fclose($conn);
            return true; // SSH dispo
        }
        sleep(5);
        $elapsed += 5;
    }
    return false; // Timeout
}


function ssh_exec($ssh_key, $user, $ip, $cmd) {
    $ssh = 'ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o BatchMode=yes -i '
         . escapeshellarg($ssh_key)
         . ' ' . escapeshellarg("$user@$ip")
         . ' ' . escapeshellarg($cmd);

    $descriptorspec = [
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"]  // stderr
    ];

    $process = proc_open($ssh, $descriptorspec, $pipes);
    if (!is_resource($process)) {
        return ["success" => false, "exitCode" => null, "stdout" => "", "stderr" => "Impossible de lancer SSH"];
    }

    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        "success"  => $exitCode === 0,
        "exitCode" => $exitCode,
        "stdout"   => trim($stdout),
        "stderr"   => trim($stderr)
    ];
}
