<?php

return [
    'max_active' => (int) env('SPJ_COMPILE_MAX_ACTIVE', 5),
    'max_active_per_opd' => (int) env('SPJ_COMPILE_MAX_ACTIVE_PER_OPD', 2),
    'part_bytes' => (int) env('SPJ_COMPILE_PART_BYTES', 1024 * 1024 * 1024),
    'part_files' => (int) env('SPJ_COMPILE_PART_FILES', 1000),
    'min_free_bytes' => (int) env('SPJ_COMPILE_MIN_FREE_BYTES', 1024 * 1024 * 1024),
    'merge_max_bytes' => 64 * 1024 * 1024,
    'merge_max_pages' => 300,
    'retention_hours' => 24,
];
