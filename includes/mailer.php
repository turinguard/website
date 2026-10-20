<?php
declare(strict_types=1);

function smtp_send(array $config, string $replyTo, string $subject, string $body): void
{
    foreach (['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_from', 'recipient'] as $key) {
        if (!isset($config[$key]) || $config[$key] === '') {
            throw new RuntimeException("SMTP configuration is missing: $key");
        }
    }

    if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL) || !filter_var($config['smtp_from'], FILTER_VALIDATE_EMAIL) || !filter_var($config['recipient'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('SMTP configuration contains an invalid email address.');
    }

    $host = (string) $config['smtp_host'];
    $ehloHost = (string) ($config['smtp_ehlo'] ?? 'localhost');
    $port = (int) $config['smtp_port'];
    if (!preg_match('/^[A-Za-z0-9.-]+$/', $ehloHost)) {
        throw new RuntimeException('SMTP EHLO hostname is invalid.');
    }
    if (!in_array($port, [465, 587], true)) {
        throw new RuntimeException('SMTP port must be 465 or 587.');
    }

    $transport = $port === 465 ? "ssl://$host" : "tcp://$host";
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
            'peer_name' => $host,
        ],
    ]);
    $socket = stream_socket_client($transport . ":$port", $errorCode, $errorMessage, 15, STREAM_CLIENT_CONNECT, $context);
    if ($socket === false) {
        throw new RuntimeException("SMTP connection failed to $host:$port: $errorMessage ($errorCode)");
    }

    $stage = 'greeting';
    try {
        smtp_expect($socket, 220);
        $stage = 'EHLO';
        smtp_command($socket, "EHLO $ehloHost", 250);
        if ($port !== 465) {
            $stage = 'STARTTLS';
            smtp_command($socket, "STARTTLS", 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Could not enable SMTP TLS.');
            }
            $stage = 'EHLO after TLS';
            smtp_command($socket, "EHLO $ehloHost", 250);
        }
        $stage = 'authentication';
        smtp_command($socket, "AUTH LOGIN", 334);
        smtp_command($socket, base64_encode($config['smtp_username']), 334);
        smtp_command($socket, base64_encode($config['smtp_password']), 235);
        $stage = 'sender and recipient';
        smtp_command($socket, "MAIL FROM:<{$config['smtp_from']}>", 250);
        smtp_command($socket, "RCPT TO:<{$config['recipient']}>", 250);
        $stage = 'message delivery';
        smtp_command($socket, 'DATA', 354);

        $headers = [
            'From: Turinguard website <' . $config['smtp_from'] . '>',
            'Reply-To: ' . $replyTo,
            'To: ' . $config['recipient'],
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $host . '>',
            'Subject: ' . (function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n" . smtp_dot_escape($body) . "\r\n.";
        fwrite($socket, $message . "\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
    } catch (Throwable $exception) {
        throw new RuntimeException("SMTP $stage failed: " . $exception->getMessage(), 0, $exception);
    } finally {
        fclose($socket);
    }
}

function smtp_command($socket, string $command, int $expectedCode): void
{
    fwrite($socket, $command . "\r\n");
    smtp_expect($socket, $expectedCode);
}

function smtp_expect($socket, int $expectedCode): void
{
    $response = '';
    do {
        $line = fgets($socket, 515);
        if ($line === false) {
            throw new RuntimeException('Unexpected end of SMTP response.');
        }
        $response .= $line;
    } while (isset($line[3]) && $line[3] === '-');

    $actualCode = (int) substr($response, 0, 3);
    if ($actualCode !== $expectedCode) {
        throw new RuntimeException("SMTP error $actualCode: " . trim($response));
    }
}

function smtp_dot_escape(string $body): string
{
    return preg_replace('/^\./m', '..', $body) ?? $body;
}
