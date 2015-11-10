<?php
namespace Apitude\File\Services;

use Silex\Application;

class AwsCredentialsService
{
    protected $credentialVariables = array(
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
    );

    protected $kmsVariables = array(
        'AWS_MASTER_KEY_ID',
        'AWS_MASTER_KEY_REGION',
    );

    public function checkKmsCredentials(Application $app)
    {
        $variables = array_merge($this->kmsVariables, $this->credentialVariables);
        return $this->checkCredentials($variables, $app);
    }

    public function checkS3Credentials(Application $app)
    {
        return $this->checkCredentials($this->credentialVariables, $app);
    }

    protected function checkCredentials($credentialVariables, Application $app)
    {
        $missing = false;
        for ($i = 0; $i < count($credentialVariables); $i++) {
            if (! isset($app['config']['files']['credentials'][$credentialVariables[$i]])) {
                $missing = true;
            }
        }

        return ! $missing;
    }
}