<?php
namespace Apitude\File;

use Silex\Application;
use Apitude\File\Controller\AbstractFileController;
use Apitude\File\Services\AwsCredentialsService;
use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Silex\ServiceProviderInterface;
use WyriHaximus\SliFly\FlysystemServiceProvider;

class FileServiceProvider implements ServiceProviderInterface
{
    protected $services = [
        AbstractFileController::class,
        AwsCredentialsService::class,
    ];

    public function __construct()
    {
        $this->entityFolders['Apitude\File\Entities'] = realpath(__DIR__.'/Entities');
    }

    public function register(Application $app)
    {
        $adapters = [
            'local__DIR__' => [
                'adapter' => Local::class,
                'args' => [
                    __DIR__,
                ]
            ],
        ];

        $config = $app['config']['files'];

        if ($config['filesystem'] === 's3') {
            $app['aws-credentials.service'] = $app->share(function ($app) {
                return new AwsCredentialsService($_SERVER);
            });

            // If s3 config exists
            if (array_key_exists('bucket', $config) && $app['aws-credentials.service']->checkS3Credentials()) {
                $client = new S3Client([
                    'credentials' => [
                        'key' => $config['credentials']['AWS_ACCESS_KEY_ID'],
                        'secret' => $config['credentials']['AWS_SECRET_ACCESS_KEY'],
                    ],
                ]);

                $adapters['s3'] = [
                    'adapter' => AwsS3Adapter::class,
                    'args' => [$client, $bucket],
                ];
            }
        }

        $app->register(new FlysystemServiceProvider(), [
           'flysystem.filesystems' => $adapters
        ]);
    }

    public function boot(Application $app)
    {
        //noop
    }
}