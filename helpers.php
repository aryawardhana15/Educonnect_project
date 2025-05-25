<?php

/**
 * Get consistent default avatar for a user
 * @param int $user_id ID dari user
 * @return string Path to default avatar
 */
function getRandomDefaultAvatar($user_id = null) {
    $defaultAvatars = [
        'assets/images/default-avatars/avatar1.png',
        'assets/images/default-avatars/avatar2.png',
        'assets/images/default-avatars/avatar3.png',
        'assets/images/default-avatars/avatar4.png',
        'assets/images/default-avatars/avatar5.png'
    ];
    
    if ($user_id === null) {
        // Jika tidak ada user_id, gunakan random avatar
        return $defaultAvatars[array_rand($defaultAvatars)];
    }
    
    // Gunakan modulo untuk mendapatkan index yang konsisten berdasarkan user_id
    $index = ($user_id - 1) % count($defaultAvatars);
    return $defaultAvatars[$index];
}