<?php

/**
 * Get random default avatar from the available default avatars
 * @return string Path to random default avatar
 */
function getRandomDefaultAvatar() {
    $defaultAvatars = [
        'assets/images/default-avatars/avatar1.png',
        'assets/images/default-avatars/avatar2.png',
        'assets/images/default-avatars/avatar3.png',
        'assets/images/default-avatars/avatar4.png',
        'assets/images/default-avatars/avatar5.png'
    ];
    
    return $defaultAvatars[array_rand($defaultAvatars)];
}

/**
 * Asset helper function
 * @param string $path Path relatif ke asset
 * @return string Full URL ke asset
 */
function asset($path) {
    // Hapus leading slash jika ada
    $path = ltrim($path, '/');
    
    // Base URL dari config, atau gunakan relatif path jika tidak ada
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    
    return $baseUrl . '/' . $path;
} 