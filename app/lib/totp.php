<?php
declare(strict_types=1);

/* TOTP (RFC 6238), compatible Google Authenticator, Microsoft Authenticator, Aegis, FreeOTP. */

const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

function totp_generate_secret(int $bytes = 20): string
{
    return base32_encode(random_bytes($bytes));
}

function base32_encode(string $data): string
{
    $bits = '';
    foreach (str_split($data) as $char) {
        $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 5) as $chunk) {
        $out .= BASE32_ALPHABET[bindec(str_pad($chunk, 5, '0'))];
    }
    return $out;
}

function base32_decode(string $data): string
{
    $data = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $data) ?? '');
    $bits = '';
    foreach (str_split($data) as $char) {
        $bits .= str_pad(decbin(strpos(BASE32_ALPHABET, $char)), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 8) as $byte) {
        if (strlen($byte) === 8) {
            $out .= chr(bindec($byte));
        }
    }
    return $out;
}

function totp_code(string $secret, int $step): string
{
    $hash = hash_hmac('sha1', pack('N*', 0, $step), base32_decode($secret), true);
    $offset = ord($hash[19]) & 0x0f;
    $value = ((ord($hash[$offset]) & 0x7f) << 24)
        | (ord($hash[$offset + 1]) << 16)
        | (ord($hash[$offset + 2]) << 8)
        | ord($hash[$offset + 3]);
    return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
}

/**
 * Vérifie un code avec une tolérance de ±30 s.
 * Retourne le pas de temps accepté (à mémoriser pour empêcher la réutilisation) ou null.
 */
function totp_verify(string $secret, string $code, int $lastStep = 0): ?int
{
    $code = preg_replace('/\D/', '', $code) ?? '';
    if (strlen($code) !== 6) {
        return null;
    }
    $current = intdiv(time(), 30);
    for ($i = -1; $i <= 1; $i++) {
        $step = $current + $i;
        if ($step > $lastStep && hash_equals(totp_code($secret, $step), $code)) {
            return $step;
        }
    }
    return null;
}

function totp_uri(string $secret, string $account, string $issuer): string
{
    return sprintf(
        'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
        rawurlencode($issuer),
        rawurlencode($account),
        $secret,
        rawurlencode($issuer)
    );
}
