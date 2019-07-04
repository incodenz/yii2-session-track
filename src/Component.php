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
use yii\helpers\Json;

/**
 * Class SessionTrack
 * @package app\components
 */
class Component extends \yii\base\Component implements \yii\base\BootstrapInterface
{
    const TRACK_ALL = 10;
    const TRACK_PUBLIC_ONLY = 20;
    const TRACK_USERS_ONLY = 30;

    const EVENT_BEFORE_PAGE_TRACK = 'beforePageTrack';

    public $whoToTrack = self::TRACK_USERS_ONLY;
    public $trackPages = false;

    public $startTime;

    /**
     * @var string
     */
    public $trackingClass = '\incodenz\SessionTrack\models\SessionTrack';
    public $trackingPageClass = '\incodenz\SessionTrack\models\SessionTrackPage';
    /**
     * @var array
     * exclude specific paths / pages - can be a regex
     */
    public static $exceptions = [
        '/^debug/',
        '/^assets/',
    ];

    /**
     * Bootstrap method is executed on every request.
     *
     * @param \yii\base\Application $app
     * @return void|\yii\web\Response
     */
    public function bootstrap($app)
    {
        if (Yii::$app instanceof \yii\console\Application || $this->isExceptionRoute($this->getRoute())) {
            return;
        }
        $this->startTime = new \DateTime();
        $user = Yii::$app->getUser();
        if (($this->whoToTrack == self::TRACK_PUBLIC_ONLY && !$user->isGuest) || ($this->whoToTrack == self::TRACK_USERS_ONLY && $user->isGuest)) {
            return;
        }
        if (in_array($this->whoToTrack, [self::TRACK_USERS_ONLY, self::TRACK_ALL])) {
            $user->on(User::EVENT_AFTER_LOGIN, function ($event) {
                $this->updateRecord();
            });
            $user->on(User::EVENT_BEFORE_LOGOUT, function ($event) {
                $this->updateRecord();
            });
        }
        $app->on(\yii\web\Application::EVENT_BEFORE_REQUEST, function ($event) {
            $this->updateRecord();
        });
    }

    /**
     * update session track record
     */
    private function updateRecord() {
        $trackingClass = $this->trackingClass;
        $session = Yii::$app->session;
        /** @var ActiveQuery $query */
        $query = call_user_func($trackingClass.'::find');
        $query->andWhere(['session_id' => $session->id]);
        /** @var \incodenz\SessionTrack\models\SessionTrack $model */
        $model = $query->one();
        if ($model) {
            $model->updated_at = new Expression('CURRENT_TIMESTAMP');
            $model->updateAttributes(['updated_at']);
            $this->trackPage($model);
            return;
        }
        $user = Yii::$app->user;

        $model = new $trackingClass();
        $model->session_id = $session->id;
        $model->user_id = $user->id;
        $model->ip_address = self::getIPAddress();
        $model->created_at = new Expression('CURRENT_TIMESTAMP');
        $model->updated_at = new Expression('CURRENT_TIMESTAMP');
        $model->save(false);
        $this->trackPage($model);
    }

    /**
     * @param $sessionTrack \incodenz\SessionTrack\models\SessionTrack
     */
    private function trackPage($sessionTrack)
    {
        if (!$this->trackPages) {
            return;
        }
        $event = new SessionTrackEvent();
        $this->trigger(self::EVENT_BEFORE_PAGE_TRACK, $event);
        if (!$event->isValid) {
            return;
        }
        /** @var $request yii\web\Request */
        $request = Yii::$app->request;
        $trackingPageClass = $this->trackingPageClass;
        /** @var $model \incodenz\SessionTrack\models\SessionTrack */
        $model = new $trackingPageClass();
        $model->session_track_id = $sessionTrack->id;
        $model->created_at = new Expression('CURRENT_TIMESTAMP');
        $model->request_type = $request->method;
        $model->request_path = $request->url;
        $model->request_params = Json::encode($request->post());
        $model->request_params = $model->request_params === '[]' ? '' : $model->request_params;


        try {
            $model->save();
            \Yii::$app->on(\yii\web\Application::EVENT_AFTER_REQUEST, function ($event) use ($model) {
                $now = new \DateTime();
                $diff = $now->diff($this->startTime);
                $model->request_time = sprintf('%0.3f', $diff->f);
                $model->updateAttributes(['request_time']);
            });
        } catch (\yii\db\Exception $dbException) {
            if (strpos($dbException->getMessage(), 'Incorrect string value') !== false) {
                // an db encoding/charset issue -- retry
                try {
                    $model->request_params = Json::encode($request->post(), JSON_UNESCAPED_SLASHES);
                    $model->save();
                } catch (\Exception $e) {
                    // give up
                }
            }
        } catch (\Exception $e) {
            // give up
        }
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

    private static function getIPAddress()
    {
        $ip = Yii::$app->request->getUserIP();
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $realIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                $ip = $realIp;
            }
        }
        return $ip;
    }
}

