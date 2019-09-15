<?php

return [
    // How to access the casted attribute:
    // auto - attribute getter and suffix
    // getter - attribute getter
    // suffix - suffix
    'mode' => 'auto',

    // Allow to access the casted attribute using a suffix
    'suffix' => '_',

    // Access the casted attribute using only the suffix for these types
    'suffix_only' => [
        'uuid',
    ],
];
