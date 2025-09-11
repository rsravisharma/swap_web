<?php

return [
    'credentials' => env('FIREBASE_CREDENTIALS'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    
    // Alternative: Direct path if not using .env
    // 'credentials' => storage_path('app/firebase-service-account.json'),
    // 'project_id' => 'your-project-id',
];
