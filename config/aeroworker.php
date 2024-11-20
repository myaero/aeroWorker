<?php

return [
    // Gearman connection settings
    'host' => getenv('GEARMAN_HOST') ?: '127.0.0.1',
    'port' => getenv('GEARMAN_PORT') ?: 4730,

    // Worker settings
    'timeout' => getenv('GEARMAN_TIMEOUT') ?: 1000,    // Worker timeout in milliseconds
    'max_retries' => getenv('GEARMAN_MAX_RETRIES') ?: 3,
    
    // Process settings
    'workers_count' => getenv('GEARMAN_WORKERS_COUNT') ?: 1,  // Number of worker processes to spawn
    
    // Log settings
    'log_errors' => getenv('GEARMAN_LOG_ERRORS') ?: true,
    'error_log_path' => getenv('GEARMAN_ERROR_LOG') ?: sys_get_temp_dir() . '/gearman_errors.log',
];