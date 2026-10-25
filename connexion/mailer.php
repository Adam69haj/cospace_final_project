<?php
// Lit une variable depuis ai_service/.env sans exposer les secrets dans le code
function getEnvValue($key) {
    $envFile = __DIR__ . '/../ai_service/.env';
    if (!file_exists($envFile)) return null;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2 && trim($parts[0]) === $key) {
            return trim($parts[1]);
        }
    }
    return null;
}
// Envoie un email HTML via Gmail SMTP (port 465 SSL)
function sendCoSpaceEmail($to, $subject, $htmlContent) {
    $smtpUser = getEnvValue('SMTP_USER');
    $smtpPass = str_replace(' ', '', (string)getEnvValue('SMTP_PASS'));
    if (!$smtpUser || !$smtpPass || !$to) {
        return false;
    }
    $ctx = stream_context_create([
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $socket = @stream_socket_client(
        'ssl://smtp.gmail.com:465', $errno, $errstr, 8, STREAM_CLIENT_CONNECT, $ctx
    );
    if (!$socket) return false;
    stream_set_timeout($socket, 8);
    $read = function() use ($socket) {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) === ' ') break;
        }
        return $data;
    };
    $cmd = function($command) use ($socket, $read) {
        fwrite($socket, $command . "\r\n");
        return $read();
    };
    $read();
    $cmd("EHLO localhost");
    $cmd("AUTH LOGIN");
    $cmd(base64_encode($smtpUser));
    $authRes = $cmd(base64_encode($smtpPass));
    // 235 = Authentication successful
    if (strpos($authRes, '235') === false) {
        fclose($socket);
        return false;
    }
    $cmd("MAIL FROM:<{$smtpUser}>");
    $cmd("RCPT TO:<{$to}>");
    $cmd("DATA");
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers  = "From: CoSpace <{$smtpUser}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Subject: {$encodedSubject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $body = "
    <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:20px;border:1px solid #e4e8f5;border-radius:10px;'>
        <h2 style='color:#6355e8;margin-top:0;'>CoSpace</h2>
        <div style='color:#1a1d2e;line-height:1.6;font-size:15px;'>{$htmlContent}</div>
        <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
        <p style='color:#888;font-size:12px;margin:0;'>Cet email a été envoyé automatiquement par CoSpace.</p>
    </div>";
    $cmd($headers . $body . "\r\n.");
    $cmd("QUIT");
    fclose($socket);
    return true;
}