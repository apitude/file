<?php
namespace Apitude\File;

use Apitude\Core\Provider\AbstractServiceProvider;
use Silex\Application;
use Apitude\File\Controller\FileController;
use Apitude\File\Services\FileService;

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Silex\ServiceProviderInterface;
use WyriHaximus\SliFly\FlysystemServiceProvider;

class FileServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected $services = [
        FileController::class,
        FileService::class,
    ];

    public function __construct()
    {
        $this->entityFolders['Apitude\File\Entities'] = realpath(__DIR__.'/Entities');
    }

    public function register(Application $app)
    {
        parent::register($app);

        $adapters = [
            'local__DIR__' => [
                'adapter' => Local::class,
                'args' => [
                    __DIR__,
                ]
            ],
        ];

        $config = $app['config']['files'];

        if (isset($config['filesystems']['s3'])) {
            $client = new S3Client([
                'credentials' => [
                    'key' => $config['credentials']['AWS_ACCESS_KEY_ID'],
                    'secret' => $config['credentials']['AWS_SECRET_ACCESS_KEY'],
                ],
                'region' => $config['region'],
                'version' => $config['version'],
            ]);

            $adapters['s3'] = [
                'adapter' => AwsS3Adapter::class,
                'args' => [$client, $config['bucket']],
            ];
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