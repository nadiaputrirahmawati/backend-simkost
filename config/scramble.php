  <?php

use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * resolution logic.
     */
    'api_domain' => env('SCRAMBLE_API_DOMAIN'),

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        'title' => env('APP_NAME', 'Laravel') . ' API',
        'version' => '1.0.0',
        'description' => 'Backend API untuk aplikasi Kost.',
    ],

    /*
     * Customize Scramble UI.
     */
    'ui' => [
        'enabled' => true,
    ],

    /*
     * A list of middleware names that needs to be passed on the documentation
     * routes.
     */
    'middleware' => [
        'web',
        'api',
    ],

    'extensions' => [],
];
