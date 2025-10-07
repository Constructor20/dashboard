<?php
$ssh_key = "/var/www/.ssh/id_ed25519";
$ip = "192.168.1.22";

// Chemin Python + script
$cmd = "nohup /c/Users/aleix/AppData/Local/Programs/Python/Python312/python.exe F:/all_serv/server_api.py > server.log 2>&1 &";

// Commande SSH complète
$ssh = 'ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o BatchMode=yes -i ' 
     . escapeshellarg($ssh_key) 
     . ' aleix@' . escapeshellarg($ip) 
     . ' ' . escapeshellarg($cmd);

// Définir les pipes
$descriptorspec = [
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"]   // stderr
];

// Lancer le processus
$process = proc_open($ssh, $descriptorspec, $pipes);

// if (is_resource($process)) {
//     // Lire stdout en direct
//     echo "<pre>";
//     while ($line = fgets($pipes[1])) {
//         echo htmlspecialchars($line) . "<br>";
//         flush();
//     }
//     // Lire stderr en direct
//     while ($line = fgets($pipes[2])) {
//         echo "<span style='color:red'>" . htmlspecialchars($line) . "</span><br>";
//         flush();
//     }
//     echo "</pre>";

//     $exitCode = proc_close($process);
//     echo "<p>Code de sortie : $exitCode</p>";
// } else {
//     echo "<p>Impossible de lancer la commande SSH.</p>";
// }
