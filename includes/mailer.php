<?php
declare(strict_types=1);

function smtp_send(array $config, string $replyTo, string $subject, string $body): void
{
    $host = $config['smtp_host'];
    $port = (int) $config['smtp_port'];
    $socket = fsockopen($host, $port, $errorCode, $errorMessage, 15);
    if ($socket === false) {
        throw new RuntimeException("SMTP connection failed: $errorMessage ($errorCode)");
    }

    try {
        smtp_expect($socket, 220);
        smtp_command($socket, "EHLO turinguard.nl", 250);
        smtp_command($socket, "STARTTLS", 220);
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('Could not enable SMTP TLS.');
        }
        smtp_command($socket, "EHLO turinguard.nl", 250);
        smtp_command($socket, "AUTH LOGIN", 334);
        smtp_command($socket, base64_encode($config['smtp_username']), 334);
        smtp_command($socket, base64_encode($config['smtp_password']), 235);
        smtp_command($socket, "MAIL FROM:<{$config['smtp_from']}>", 250);
        smtp_command($socket, "RCPT TO:<{$config['recipient']}>", 250);
        smtp_command($socket, 'DATA', 354);

        $headers = [
            'From: Turinguard website <' . $config['smtp_from'] . '>',
            'Reply-To: ' . $replyTo,
            'To: ' . $config['recipient'],
            'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8'),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n" . smtp_dot_escape($body) . "\r\n.";
        fwrite($socket, $message . "\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
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
