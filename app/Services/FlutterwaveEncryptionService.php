<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class FlutterwaveEncryptionService
{
    protected string $aesKey;

    /**
     * Constructor
     *
     * @param string $encryptionKey Base64 encoded key
     */
    public function __construct(string $encryptionKey)
    {
        $this->aesKey = base64_decode($encryptionKey);
        if ($this->aesKey === false) {
            Log::error('Failed to decode base64 encryption key.', ['encryption_key' => $encryptionKey]);
            throw new Exception('Invalid base64 encryption key.');
        }
    }

    /**
     * Generate nonce (12 characters)
     */
    public static function generateNonce(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $nonce = '';

        for ($i = 0; $i < $length; $i++) {
            $nonce .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $nonce;
    }

    /**
     * Encrypt a single value using AES-256-GCM
     */
    public function encrypt(string $plainText, string $nonce): string
    {
        if (empty($plainText) || empty($nonce)) {
            throw new Exception('Both plain_text and nonce are required for encryption.');
        }

        $cipher = 'aes-256-gcm';
        $nonceBytes = $nonce; // 12 bytes recommended for GCM
        $tag = '';

        $cipherText = openssl_encrypt(
            $plainText,
            $cipher,
            $this->aesKey,
            OPENSSL_RAW_DATA,
            $nonceBytes,
            $tag
        );

        if ($cipherText === false) {
            throw new Exception('Encryption failed.');
        }

        // Combine ciphertext + tag (important for GCM)
        $encrypted = $cipherText . $tag;
        return base64_encode($encrypted);
    }

    /**
     * Encrypt associative array (dictionary)
     */
    public function encryptArray(array $data): array
    {
        if (!is_array($data)) {
            throw new Exception("Data must be an array.");
        }

        $nonce = self::generateNonce();

        $encryptedData = [
            'nonce' => $nonce
        ];

        foreach ($data as $key => $value) {
            $encryptedData[$key] = $this->encrypt((string) $value, $nonce);
        }

        return $encryptedData;
    }
}