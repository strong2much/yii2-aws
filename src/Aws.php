<?php
namespace strong2much\aws;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use Aws\Sdk;

/**
 * Manager class that handles AWS initiation and service availability.
 *
 * @author   Denis Tatarnikov <tatarnikovda@gmail.com>
 */
class Aws extends Component
{
	/**
	 * @var string AWS key
	 */
	public $key;
	/**
	 * @var string AWS secret
	 */
	public $secret;
	/**
	 * @var string AWS region (i.e. 'us-east-1')
	 */
	public $region;
	/**
	 * @var string version
	 */
	public $version = 'latest';
	/**
	 * @var array additional options to config
	 */
	public $options = [];

    /**
     * @var array holds configuration
     */
    protected $_config = [];
	
    /**
     * @var Sdk holds the pointer to AWS sdk
     */
    protected $_sdk;
	
	/**
     * Initializes the application component.
     */
    public function init()
    {
		$this->_config = [
			'region' => $this->region,
			'version' => $this->version,
			'credentials' => [
				'key' => $this->key,
				'secret' => $this->secret,
			]
		];
		$this->_config = array_merge($this->_config, $this->options);

		$this->setConfig($this->_config);

		parent::init();
	}

	/**
	 * @return array AWS config
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * @param array $config AWS config
	 * @throws InvalidConfigException
	 */
	public function setConfig($config)
	{
		if (empty($config['key']) || empty($config['secret'])) {
			throw new InvalidConfigException(Yii::t('aws', '"key" and "secret" must be provided'));
		}

		if(empty($config['region'])) {
			throw new InvalidConfigException(Yii::t('aws', '"region" must be provided'));
		}

		$this->_config = $config;
		$this->_sdk = new Sdk($this->_config);
	}

	/**
	 * @return Sdk
	 */
    public function getSdk()
    {
        if (null === $this->_sdk) {
            $this->_sdk = new Sdk($this->_config);
        }

        return $this->_sdk;
    }

	/**
	 * Magic call to wrapped amazon aws methods. If not command found, then call parent component
	 * @param string $name
	 * @param array $params
	 * @return mixed
	 */
	public function __call($name, $params)
	{
		$sdk = $this->getSdk();
		if (is_callable([$sdk, $name]))
			return call_user_func_array([$sdk, $name], $params);
		return parent::__call($name, $params);
	}
}
