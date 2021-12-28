<?php

return [
    'stubs' => [
        'path' =>  base_path('stubs/nova-flexible-content/'),
        'to' => [
            'cast'     => app_path('Casts/%s.php'),
            'layout'   => app_path('Nova/Flexible/Layouts/%s.php'),
            'preset'   => app_path('Nova/Flexible/Presets/%s.php'),
            'resolver' => app_path('Nova/Flexible/Resolvers/%s.php'),
        ],
    ],
];
