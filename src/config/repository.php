<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Repository namespace
    |--------------------------------------------------------------------------
    | The namespace for the repository classes.
    |
    */
    'namespace' => 'App\Repositories',

    /*
    |--------------------------------------------------------------------------
    | Contract/Interface repository namespace
    |--------------------------------------------------------------------------
    | The namespace for the contract or interface repository classes.
    |
    */
    'contract' => 'App\Contracts',

    /*
    |--------------------------------------------------------------------------
    | Repository and it's contract/interface path
    |--------------------------------------------------------------------------
    | The path to the repository and interface/contract folder.
    |
    */
    'path' => [
        'repository'    => 'app' . DIRECTORY_SEPARATOR . 'Repositories',
        'contract'      => 'app' . DIRECTORY_SEPARATOR . 'Contracts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Model namespace
    |--------------------------------------------------------------------------
    | The model namespace.
    |
    */
    'model' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Pagination attribute
    |--------------------------------------------------------------------------
    |
    */
    'pagination' => [
        'perPage' => 10
    ],
];