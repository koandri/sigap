<?php

return [
    // Text watermarks
    'text' => [
        'company' => 'PT SIAP',
        'confidential' => 'PT SIAP - CONFIDENTIAL',
        'preview' => 'PREVIEW - PT SIAP'
    ],
    
    // Logo watermark
    'logo' => [
        'path' => public_path('images/watermarks/pt-siap-logo.png'),
        'opacity' => 70,
        'position' => 'bottom-right',
        'max_size_percent' => 15 // 15% of image size
    ],
    
    // Watermark settings by context
    'settings' => [
        'thumbnail' => [
            'fontPath' => public_path('fonts/Montserrat-VariableFont_wght.ttf'),
            'text' => 'PT SIAP',
            'size_ratio' => 25, // fontSize = imageWidth / 25
            'opacity' => 70,
            'position' => 'bottom-right',
            'angle' => 0,
            'color' => 'rgba(255, 255, 255, 0.8)',
            'background_color' => 'rgba(0, 0, 0, 0.5)'
        ],
        'preview' => [
            'fontPath' => public_path('fonts/Montserrat-VariableFont_wght.ttf'),
            'text' => 'PT SIAP - CONFIDENTIAL',
            'size_ratio' => 20, // fontSize = imageWidth / 20
            'opacity' => 60,
            'position' => 'center',
            'angle' => 45,
            'color' => 'rgba(255, 255, 255, 0.7)',
            'background_color' => 'rgba(0, 0, 0, 0.3)'
        ],
        'download' => [
            'fontPath' => public_path('fonts/Montserrat-VariableFont_wght.ttf'),
            'text' => 'PT SIAP - DOWNLOADED',
            'size_ratio' => 15,
            'opacity' => 80,
            'position' => 'center',
            'angle' => 45,
            'color' => 'rgba(255, 255, 255, 0.9)',
            'background_color' => 'rgba(0, 0, 0, 0.4)'
        ]
    ]
];