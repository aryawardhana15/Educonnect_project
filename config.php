<?php
// Pastikan tidak ada spasi atau karakter aneh sebelum <?php
define('GROQ_API_KEY', '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');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('MODEL_NAME', 'mixtral-8x7b-32768'); // atau 'llama3-70b-8192' untuk model terbaru

// Tambahkan validasi
if (!defined('GROQ_API_KEY') || empty(GROQ_API_KEY)) {
    die('API key not configured');
}
?>