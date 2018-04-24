<?php
/**
 * @link https://gihub.com/incodenz/yii2-session-track
 * @copyright Copyright (c) 2018 Webtools Ltd
 */
namespace incodenz\SessionTrack;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\User;

/**
 * Class SessionTrack
 * @package app\components
 */
class Component extends \yii\base\Component implements \yii\base\BootstrapInterface
{
    /**
     * @var string
     */
    public $trackingClass = 'incodenz\SessionTrack\models\SessionTrack';
    /**
     * @var array
     */
    public static $exceptions = [
    ];

    /**
     * Bootstrap method is executed on every request.
     *
     * @param \yii\base\Application $app
     * @return void|\yii\web\Response
     */
    public function bootstrap($app)
    {
        if ($this->isExceptionRoute($this->getRoute())) {
            return;
        }
        $user = Yii::$app->getUser();
        $user->on(User::EVENT_AFTER_LOGIN, function ($event) {
            self::updateRecord($this->trackingClass);
        });
        $user->on(User::EVENT_BEFORE_LOGOUT, function ($event) {
            self::updateRecord($this->trackingClass);
        });
        if (Yii::$app instanceof \yii\console\Application || $user->isGuest) {
            return;
        }
        $app->on(\yii\web\Application::EVENT_BEFORE_REQUEST, function ($event) {
            self::updateRecord($this->trackingClass);
        });
    }

    /**
     * @param $trackingClass
     */
    private static function updateRecord($trackingClass) {

        $session = Yii::$app->session;
        /** @var ActiveQuery $query */
        $query = call_user_func($trackingClass.'::find');
        $query->andWhere(['session_id' => $session->id]);
        /** @var \incodenz\SessionTrack\models\SessionTrack $model */
        $model = $query->one();
        if ($model) {
            $model->updated_at = new Expression('CURRENT_TIMESTAMP');
            $model->updateAttributes(['updated_at']);
            return;
        }
        $user = Yii::$app->user;

        $model = new $trackingClass();
        $model->session_id = $session->id;
        $model->user_id = $user->id;
        $model->ip_address = Yii::$app->getRequest()->getUserIP();
        $model->created_at = new Expression('CURRENT_TIMESTAMP');
        $model->updated_at = new Expression('CURRENT_TIMESTAMP');
        $model->save(false);
    }

    /**
     * Determine if the given route is an exception
     *
     * @param $route
     * @return bool
     */
    public function isExceptionRoute($route)
    {
        foreach(self::$exceptions as $exception) {
            if ($exception === $route || (substr($exception, 0, 2) === '/^' && preg_match($exception, $route) === 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current route (eg. /user/login)
     * excluding the QueryString.
     *
     * @return string
     */
    public function getRoute()
    {
        try {
            $path = Yii::$app->getRequest()->getPathInfo();
        } catch(\yii\base\InvalidConfigException $e) {
            $path = '';
        }

        return $path;
    }

    /**
     * @param $value
     */
    public function setExceptions($value)
    {
        self::$exceptions = $value;
    }
}

