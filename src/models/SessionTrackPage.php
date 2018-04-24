<?php

namespace incodenz\SessionTrack\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the base-model class for table "session_track_page".
 *
 * @property integer $id
 * @property integer $session_track_id
 * @property string $created_at
 * @property string $request_type
 * @property string $request_path
 * @property string $request_params
 */
class SessionTrackPage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'session_track_page';
    }

    /**
     *
     */
    public static function label($n = 1)
    {
        return Yii::t('app', '{n, plural, =1{Session Track Page} other{Session Track Pages}}', ['n' => $n]);
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
            [['created_at'], 'safe'],
            [['request_params'], 'string'],
            [['request_path'], 'string', 'max' => 255],
            [['request_type'], 'string', 'max' => 6],
            [['session_track_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'session_track_id' => 'Session Track',
            'created_at' => 'Created At',
            'request_type' => 'Request Type',
            'request_path' => 'Request Path',
            'request_params' => 'Request Params',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params = null)
    {
        $query = self::find();

        if ($params === null) {
            $params = array_filter(Yii::$app->request->get($this->formName(), array()));
        }

        $this->attributes = $params;

        $query->andFilterWhere([
            'session_track_page.id' => $this->id,
            'session_track_page.session_track_id' => $this->session_track_id,
        ]);

        $query->andFilterWhere(['like', 'session_track_page.created_at', $this->created_at])
            ->andFilterWhere(['like', 'session_track_page.request_type', $this->request_type])
            ->andFilterWhere(['like', 'session_track_page.request_path', $this->request_path])
            ->andFilterWhere(['like', 'session_track_page.request_params', $this->request_params]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);
    }
}
