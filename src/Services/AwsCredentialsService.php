<?php
namespace Apitude\File\Services;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;

class AwsCredentialsService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $credentialVariables = array(
        'AWS_ACCESS_KEY_ID',
        'AWS_SECRET_ACCESS_KEY',
    );

    protected $kmsVariables = array(
        'AWS_MASTER_KEY_ID',
        'AWS_MASTER_KEY_REGION',
    );

    public function checkKmsCredentials()
    {
        $variables = array_merge($this->kmsVariables, $this->credentialVariables);
        return $this->checkCredentials($variables);
    }

    public function checkS3Credentials()
    {
        return $this->checkCredentials($this->credentialVariables);
    }

    protected function checkCredentials($credentialVariables)
    {
        $missing = false;
        for ($i = 0; $i < count($credentialVariables); $i++) {
            if (! isset($this->container['config'][$credentialVariables[$i]])) {
                $missing = true;
            }
        }

        return ! $missing;
    }
}