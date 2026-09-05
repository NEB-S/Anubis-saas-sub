<?php

return [
    // Set ADMIN_PASSWORD in this project's .env to a long random string.
    // This is separate from anything on the sro-cms side — it only protects
    // this License Server's own dashboard.
    'password' => env('ADMIN_PASSWORD'),
];
