<?php

namespace incodenz\SessionTrack\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the base-model class for table "session_track".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $session_id
 * @property string $ip_address
 * @property string $created_at
 * @property string $updated_at
 */
class SessionTrack extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'session_track';
    }

    /**
     *
     */
    public static function label($n = 1)
    {
        return Yii::t('app', '{n, plural, =1{Session} other{Sessions}}', ['n' => $n]);
    }

    /**
     *
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['session_id'], 'string', 'max' => 64],
            [['ip_address'], 'string', 'max' => 32],
            [['user_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'session_id' => 'Session',
            'ip_address' => 'IP Address',
            'created_at' => 'Session Started',
            'updated_at' => 'Last Activity',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function search($params = null)
    {
        $query = self::find();

        if ($params === null) {
            $params = array_filter(Yii::$app->request->get($this->formName(), array()));
        }

        $this->attributes = $params;

        $query->andFilterWhere([
            'session_track.id' => $this->id,
            'session_track.user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'session_track.session_id', $this->session_id])
            ->andFilterWhere(['like', 'session_track.ip_address', $this->ip_address])
            ->andFilterWhere(['like', 'session_track.created_at', $this->created_at])
            ->andFilterWhere(['like', 'session_track.updated_at', $this->updated_at]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
    }
}
