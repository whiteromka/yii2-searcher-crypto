<?php

namespace app\models;

use app\models\base\BaseActiveRecord;
use app\models\query\UserQuery;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $surname
 * @property integer $gender
 * @property integer $status
 * @property string|null $birthday
 * @property string $password_hash
 * @property string $email
 * @property string $access_token
 * @property string $auth_key
 * @property integer $created_at
 * @property integer|null $updated_at
 *
 * @property string $fullName
 * @property int $fullAge
 * @property Passport $passport
 * @property Estate $estate
 */
class User extends BaseActiveRecord implements IdentityInterface
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 0;

    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['gender', 'status'], 'integer'],
            [['name', 'surname', 'auth_key', 'access_token', 'password_hash', 'email'], 'string', 'max' => 255],
            ['email', 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'birthday' => 'Дата рождения',
            'gender' => 'Пол',
            'status' => 'Статус',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();
        unset(
            $fields['password_hash'],
            $fields['access_token'],
            $fields['auth_key']
        );
        return $fields;
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @return ActiveQuery
     */
    public function getPassport()
    {
        return $this->hasOne(Passport::class, ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getEstate()
    {
        return $this->hasMany(Estate::class, ['user_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    /**
     * @return string
     */
    public function getNameGender(): string
    {
        $genders = self::getNamedGenders();
        return $genders[$this->gender];
    }

    /**
     * @return int
     */
    public function getFullAge(): int
    {
        $arrDate = explode('-', $this->birthday);
        $y = $arrDate[0];
        $m = $arrDate[1];
        $d = $arrDate[2];
        if($m > date('m') || $m == date('m') && $d > date('d')) {
            return (date('Y') - $y - 1);
        }
        return (date('Y') - $y);
    }

    /**
     * @return array
     */
    public static function getNamedGenders(): array
    {
        return [self::GENDER_FEMALE => 'Ж', self::GENDER_MALE => 'М'];
    }

    /**
     * @return array
     */
    public static function getNamedStatuses(): array
    {
        return [self::STATUS_NOT_ACTIVE => 'Нет', self::STATUS_ACTIVE => 'Да'];
    }

    /**
     * @return array
     */
    public static function getNamedSortItems(): array
    {
        return [
            'id' => 'ID (прямая)',
            '-id' => 'ID (обратная)',
            'name'=>'Имя (прямая)',
            '-name'=>'Имя (обратная)',
            'birthday' => 'Дата рождения (прямая)',
            '-birthday' => 'Дата рождения (обратная)',
        ];
    }

    /**
     * @param array $request
     * @return User
     * @throws Exception
     */
    public function dataSave(array $request): self
    {
        $userData = ArrayHelper::getValue($request, 'User');
        /** @var User $user */
        $user = self::find()->where(['id' => $userData['id']])->one();
        $user->load($userData, '');
        if (!$user->save()) {
            throw new Exception('Пользователь не сохранился! ' . $user->saveError);
        }
        return $user;
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id]);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @param string $password
     * @return string
     * @throws Exception
     */
    public static function generatePasswordHash(string $password): string
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Валидация пароля
     *
     * @param string $password
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Генерация хеша пароля
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Генерация auth token
     */
    public function generateAuthToken(): string
    {
        $token = Yii::$app->security->generateRandomString(32);
        $this->access_token = $token;
        $this->save();
        return $token;
    }

    /**
     * Получить токен или сгенерировать его
     */
    public function getAuthTokenOrGenerate(): string
    {
        if (!$this->access_token) {
            return $this->generateAuthToken();
        }
        return $this->access_token;
    }
}