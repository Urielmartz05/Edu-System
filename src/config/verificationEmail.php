<?php

function sendVerificationEmail($email, $token, ?callable $transport = null) {
    $baseUrl = rtrim(getenv('APP_BASE_URL') ?: '', '/');
    // Activation links must use a configured origin, never the request's Host header.
    $from = getenv('MAIL_FROM') ?: '';
    $urlParts = parse_url($baseUrl);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-f0-9]{32}$/', $token)
        || !filter_var($from, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $from)
        || !filter_var($baseUrl, FILTER_VALIDATE_URL)
        || !in_array($urlParts['scheme'] ?? '', ['http', 'https'], true)
        || isset($urlParts['query']) || isset($urlParts['fragment']) || isset($urlParts['user'])) {
        error_log('Verification email unavailable: check APP_BASE_URL and MAIL_FROM configuration.');
        return false;
    }
    $link = $baseUrl . '/src/controllers/verificar/verificar.php?token=' . rawurlencode($token);
    $body = "Confirm your Edu-System email address by opening this link:\n\n" . $link
        . "\n\nIf you did not create this account, ignore this message.\n";
    try {
        $transport ??= 'mail';
        $sent = @$transport($email, 'Confirm your Edu-System email address', $body, [
            'From' => $from,
            'Content-Type' => 'text/plain; charset=UTF-8'
        ]);
    } catch (Throwable $e) {
        $sent = false;
    }
    if (!$sent) {
        error_log('Verification email delivery failed. Check the PHP mail transport configuration.');
    }
    return $sent;
}
