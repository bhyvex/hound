<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 * @property string $password
 * @property string $email
 * @property integer $status
 * @property string $last_login
 * @property string $created
 * @property string $created_by
 * @property string $modified
 * @property string $modified_by
 */
class Users extends AZActiveRecord
{
	
	
        const ENABLE=1;
		const DISABLE=0;
		
		const SUPER_ADMIN = 'superadmin';
		const ADMIN= 'admin';
		const MANAGER='manager';
		const GUEST='guest';
	
	public function getStatusOptions()
	{
		// display status value in drop down list
		return array(
			self::ENABLE=>'Enable',
			self::DISABLE=>'Disable',
					);
	}
	public function getRolesOptions()
	{
		// display the roles for users in drop down list
		return array(
			self::SUPER_ADMIN=>'Super Admin',
			self::ADMIN=>'Admin',
			self::MANAGER=>'Manager',
			self::GUEST=>'Guest',
					);
	}
	
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, last_name, username, password, email, last_login, created_by, modified, modified_by', 'required'),
			array('email, username', 'unique'),
			array('email', 'email'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('status', 'in', 'range'=>self::getStatusRange()), // check status in range
			array('roles', 'in', 'range'=>self::getRolesRange()),	// check roles in range
			array('first_name, last_name, username, email', 'length', 'max'=>75),
			array('password', 'length', 'max'=>255),
			array('created_by, modified_by', 'length', 'max'=>11),
			array('created', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, first_name, last_name, username, password, email, status, roles, last_login, created, created_by, modified, modified_by', 'safe', 'on'=>'search'),
		);
	}
	
	public static function getStatusRange()
	{
		// check the value of status in range
			return array(
			self::ENABLE,
			self::DISABLE,
			);
	}
	
	public static function getRolesRange()
	{
		// check the roles for users are in range
			return array(
			self::SUPER_ADMIN,
			self::ADMIN,
			self::MANAGER,
			self::GUEST,
			);
	}
  
	public function getStatusText()
	{
		//this is used for display status text
		$statusOptions=$this->statusOptions;
		return isset($statusOptions[$this->status]) ?
		$statusOptions[$this->status] : "unknown status ({$this->status})";
	}
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		$relations = parent::relations();
		return $relations;
	}
	
	/**
	 * @return array beforeValidate
	 */	
    protected function beforeValidate() {
		if ($this->isNewRecord) {
			$this->last_login 		= '0000-00-00 00:00:00';
		}

		return parent::beforeValidate();
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'username' => 'Username',
			'password' => 'Password',
			'email' => 'Email',
			'status' => 'Status',
			'roles'=>'Roles',
			'last_login' => 'Last Login',
			'created' => 'Created',
			'created_by' => 'Created By',
			'modified' => 'Modified',
			'modified_by' => 'Modified By',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('last_login',$this->last_login,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('created_by',$this->created_by,true);
		$criteria->compare('modified',$this->modified,true);
		$criteria->compare('modified_by',$this->modified_by,true);
		
		$records_per_page = new CDbCriteria;	// this criteria is used for getting the pagination size from cofigurations table in show record per page according this getting size
		$records_per_page->select = "records_per_page";
		$Configurations = Configurations::model()->find($records_per_page);
		$records_per_page = $Configurations['records_per_page'];
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array('pageSize'=>$records_per_page),
		));
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Users the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	// update the last login time
	public function updateLastLogin($primary_key) {
		return $this->updateByPk(array($primary_key), array( "last_login" => new CDbExpression('NOW()')));
	}
	
	// return the full name (first_name & last_name)
	public function name() {
		return $this->first_name." ".$this->last_name; 
	}
	/**
	* Return the integer which contains total number of users in db table users.
		*/
	public function countAllUsers()
	{
		 $criteria = new CDbCriteria();
		 $criteria->select = '*';
		 $users = Users::model()->findAll($criteria);
		 return count($users);
	}
	/**
	* Compare the $id parameter and return the users credentials for reset password.
	*/
	public function restPasswordverification($id)
	{
		$user_credentials = Users::model()->find(array(
											'condition'=>'ID=:id',
											'params'=>array(':id'=>$id),
										));

		return $user_credentials;
	}
	/**
	* Generate the random passwrod for reset the password of users.
	*/
	public function randomPassword()
    {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$password = array(); //remember to declare $pass as an array
		$alphabetLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphabetLength);
			$password[] = $alphabet[$n];
		}
		return implode($password); //turn the array into a string
	}
	
}
