<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tamanho máximo de anexos de chat (em MB)
    |--------------------------------------------------------------------------
    */
    'max_attachment_mb' => env('CHAT_MAX_ATTACHMENT_MB', 50),

    /*
    |--------------------------------------------------------------------------
    | Disco de armazenamento para anexos de chat
    | Valores: 'public', 's3', etc.  (deve corresponder a config/filesystems.php)
    |--------------------------------------------------------------------------
    */
    'disk' => env('CHAT_DISK', env('FILESYSTEM_DISK', 'public')),
];
