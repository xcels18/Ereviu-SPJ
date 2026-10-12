<?php

return [
    'binary' => env('DB_BACKUP_BINARY', 'mysqldump'),
    'min_free_bytes' => 1073741824,
];
