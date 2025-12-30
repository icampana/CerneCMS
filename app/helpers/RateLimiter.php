<?php

namespace app\helpers;

class RateLimiter
{
    private static $storageDir;

    private static function getStorageDir()
    {
        if (!self::$storageDir) {
            self::$storageDir = __DIR__ . '/../../content/cache/ratelimit';
            if (!is_dir(self::$storageDir)) {
                mkdir(self::$storageDir, 0755, true);
            }
        }
        return self::$storageDir;
    }

    /**
     * Check if action is allowed for key
     * @param string $key Identifier (e.g. "ip_address:form_id")
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return bool
     */
    public static function attempt($key, $maxAttempts = 5, $decaySeconds = 60): bool
    {
        $file = self::getStorageDir() . '/' . md5($key) . '.json';
        $now = time();

        $data = ['hits' => 0, 'reset_at' => $now + $decaySeconds];

        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content) {
                $data = json_decode($content, true);
            }
        }

        // Reset if expired
        if ($now > $data['reset_at']) {
            $data = ['hits' => 0, 'reset_at' => $now + $decaySeconds];
        }

        if ($data['hits'] >= $maxAttempts) {
            return false;
        }

        $data['hits']++;
        file_put_contents($file, json_encode($data));
        return true;
    }
}
