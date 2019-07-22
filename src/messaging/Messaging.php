<?php

namespace bvb\firebase\messaging;

use bvb\singleton\Singleton;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Messaging\CloudMessage;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Messaging is a helper class that
 */
class Messaging extends BaseObject
{
	/**
	 * Implement the Singleton functionality using Yii's container
	 */
	use Singleton;

	/**
	 * @var string The URL of the Firebase API we are communicating with
	 */
	private $apiUrl = 'https://fcm.googleapis.com/fcm/send';

	/**
	 * @var string The key required to authenticate with Firebase servers
	 */
	public $apiKey;

	/**
	 * @var string The path to the service account credentisls
	 */
	public $serviceAccountCredentialsPath;

	/**
	 * @var \Kreait\Firebase\Messaging
	 */
	public $messaging;

	/**
	 * If an API key was not supplied during insantiation it will apply one form a yii param
	 * @throws InvalidConfigException Will throw this if an application param is not set under key ['firebase']['apiKey']
	 * {@inheritdoc}
	 */
	public function init()
	{
		if(empty($this->apiKey) && isset(Yii::$app->params['firebase']['apiKey'])){
			$this->apiKey = Yii::$app->params['firebase']['apiKey'];
		} else {
			throw new InvalidConfigException("An API key must be supplied during object creation or an applicaiton parameter must be set under the key ['firebase']['apiKey']");
		}

		if(empty($this->serviceAccountCredentialsPath) && isset(Yii::$app->params['firebase']['serviceAccountCredentialsPath'])){
			$this->serviceAccountCredentialsPath = Yii::$app->params['firebase']['serviceAccountCredentialsPath'];
		} else {
			throw new InvalidConfigException("An service account credentials pathmust be supplied during object creation or an applicaiton parameter must be set under the key ['firebase']['serviceAccountCredentialsPath']");
		}

		$serviceAccount = ServiceAccount::fromJsonFile(Yii::getAlias($this->serviceAccountCredentialsPath));
		$this->messaging = (new Factory())
			->withServiceAccount($serviceAccount)
			->create()
			->getMessaging();
	}

	/**
	 * Send a notification to devices
	 * @param array $tokens
	 * @param array $messageConfig
	 * @return mixed
	 */
	public function sendNotification($tokens, $messageConfig)
	{
		$message = CloudMessage::fromArray([
			'notification' => $messageConfig['notification'],
			'data' => $messageConfig['data']
		]);
		$sendReport = $this->messaging->sendMulticast($message, $tokens);
	}
}