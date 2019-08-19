<?php
use \Module\OAuth2\Services\Repositories;

return [
    Repositories\RepoClientsService::class => [
        'collection' => [
            // query on which collection
            'name' => 'oauth.clients',
            // which client to connect and query with
            'client' => 'master',
            // ensure indexes
            'indexes' => [
                ['key' => ['identifier' => 1]],
                ['key' => ['identifier' => 1, 'secret_key' => 1]],
            ],],],

    Repositories\RepoUsersApprovedClientsService::class => [
        'collection' => [
            // query on which collection
            'name' => 'oauth.users.approved_clients',
            // which client to connect and query with
            'client' => 'master',
            // ensure indexes
            'indexes' => [
                ['key' => ['user' => 1,]],
                ['key' => ['user' => 1,  'clients_approved.client' => 1]],
            ],],],

    Repositories\RepoValidationCodesService::class => [
        'collection' => [
            // query on which collection
            'name' => 'oauth.users.validation_codes',
            // which client to connect and query with
            'client' => 'master',
            // ensure indexes
            'indexes' => [
                [ 'key' => ['validation_code' => 1, ] ],
                [ 'key' => ['user_identifier' => 1, ] ],
                [ 'key' => ['user_identifier'=>1, 'auth_codes.type'=>1, 'auth_codes.validated'=>1, ] ],
                // db.oauth.users.validation_codes.createIndex({"date_mongo_expiration": 1}, {expireAfterSeconds: 0});
                [ 'key' => ['datetime_expiration_mongo' => 1 ], 'expireAfterSeconds'=> 0],
            ],],],

    Repositories\RepoUsersService::class => [
        'collection' => [
            // query on which collection
            'name' => 'oauth.users',
            // which client to connect and query with
            'client' => 'master',
            #'client' => 'prod1',
            // ensure indexes
            'indexes' => [
                [ 'key' => ['date_created_mongo'=>1, ] ],
                [ 'key' => ['identifiers.type'=>1, 'identifiers.value'=>1, 'identifiers.validated'=>1, ] ],
            ],],],
];
