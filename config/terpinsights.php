<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Starter Plan Limits
    |--------------------------------------------------------------------------
    |
    | Rolling window size: current month + (N-1) prior months. Starter users
    | can only access data within this window.
    |
    */

    'starter_allowed_months' => (int) env('STARTER_ALLOWED_MONTHS', 3),

    'starter_history_limit' => (int) env('STARTER_HISTORY_LIMIT', 3),

];
