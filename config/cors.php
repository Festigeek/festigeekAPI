<?php

// return [
    /*
     |--------------------------------------------------------------------------
     | Laravel CORS
     |--------------------------------------------------------------------------
     |

     | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
     | to accept any value, the allowed methods however have to be explicitly listed.
     |
     */
//     'supportsCredentials' => false,
//     'allowedOrigins' => ['*'],
//     'allowedHeaders' => ['*'],
//     'allowedMethods' => ['GET', 'POST', 'PUT',  'DELETE'],
//     'exposedHeaders' => [],
//     'maxAge' => 0,
//     'hosts' => [],
// ];

return [
//   'defaults' => [
//       'supportsCredentials' => false,
//       'allowedOrigins' => [],
//       'allowedHeaders' => [],
//       'allowedMethods' => [],
//       'exposedHeaders' => [],
//       'maxAge' => 0,
//       'hosts' => [],
//   ],

    'defaults' => [
        'allowedOrigins' => ['*'],
        'allowedHeaders' => ['*'],
        'allowedMethods' => ['*'],
        'maxAge' => 3600,
   ],

   'paths' => [
       'v1/*' => [
           'allowedOrigins' => ['*'],
           'allowedHeaders' => ['*'],
           'allowedMethods' => ['*'],
           'maxAge' => 3600,
       ],
   ],
];
