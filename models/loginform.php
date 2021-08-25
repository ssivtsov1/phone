<?php
// Ввод данных (логин, пароль)
namespace app\models;

use Yii;
use yii\base\Model;

class Loginform extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;

    public function attributeLabels()
    {
        return [
            'username' => 'Ім’я користувача:',
            'password' => 'Пароль:',
        ];
    }

    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required','message' => 'Поле обов’язкове'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неправильне ім’я користувача або пароль.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
       
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = $this->findUserByUsernameOrEmail();
        }

        return $this->_user;
    }

    public  function findUserByUsernameOrEmail()
    {
        return   User::find()->where(['username' => $this->username])->orWhere(['email' => $this->username])->one();

    }
}
