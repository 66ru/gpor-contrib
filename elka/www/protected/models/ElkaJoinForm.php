<?php
/**
 * Модель для формы поиска программ
 * @author stepanoff
 * @version 1.0
 *
 */
class BanksProgrammSearchForm extends CFormModel
{
	const ORDER_BANK = 10;
	const ORDER_RATE = 20;
	const ORDER_NAME = 30;
	const ORDER_DEADLINE = 40;
	const ORDER_SUM = 50;
	const ORDER_PAYMENT = 60;
	const ORDER_OVERPAYMENT = 70;
	const ORDER_PROFIT = 80;
	
	const ORDER_ASC = 10;
	const ORDER_DESC = 20;
	
	public $deadline; // для одного срока
	public $deadline_min; // минимальный срок для интервала сроков
	public $deadline_max; // максимальный срок для интервала сроков
	public $deadline_type_min;
	public $deadline_type_max;
	public $deadline_type;
	public $deadlineType; // тип срока из селекта
	public $sum;
	public $currency;
	public $age;
	
	public $order;
	public $order_type;
	
	public static function orderTypes()
	{
		return array(
			self::ORDER_BANK => 'по банку',
			self::ORDER_RATE => 'по ставке',
			self::ORDER_NAME => 'по название',
			self::ORDER_DEADLINE => 'по сроку',
			self::ORDER_SUM => 'по сумме',
			self::ORDER_PAYMENT => 'по ежемесячной выплате',
			self::ORDER_OVERPAYMENT => 'по переплате',
			self::ORDER_PROFIT => 'по доходу',
			);
	}
	
	public static function orderDirections()
	{
		return array(
			self::ORDER_ASC => 'по возрастанию',
			self::ORDER_DESC => 'по убыванию',
		);
	}
	
	public function rules()
    {
        return array(
			array('order, order_type', 'numerical', 'integerOnly' => true),
        	array('deadline_type, deadline_type_min, deadline_type_max', 'in', 'range' => array_keys(BanksProgramm::deadlineTypesList()), 'strict'=>true ),
			array('order', 'in', 'range' => array_keys(self::orderTypes()) ),
			array('order_type', 'in', 'range' => array_keys(self::orderDirections()) ),
			array('currency, deadline_min, deadline_max, deadlineType', 'numerical'),
			array('age', 'checkAge'),
			array('sum', 'checkSum'),
			array('deadline', 'checkDeadline'),
		);
    }
    
    
    public function checkSum($attribute, $params)
    {
    	$label = $this->getAttributeLabel($attribute);
    	if (!$this->$attribute)
			$this->addError($attribute, 'Не указана '.$label);
    	elseif (preg_match('/[^0-9]/', $this->$attribute) )
			$this->addError($attribute, $label.' должна быть цифрой');
    }
    
    public function checkStartSum($attribute, $params)
    {
    	$label = $this->getAttributeLabel($attribute);
    	if (empty($this->$attribute) && $this->$attribute !== '0')
    	{
    		$this->$attribute = '0';
    		//$this->addError($attribute, 'Не указан '.$label);
    	}
    	elseif (preg_match('/[^0-9]/', $this->$attribute) )
			$this->addError($attribute, $label.' должен быть цифрой');
		elseif ($this->$attribute >= $this->sum)
			$this->addError($attribute, $label.' больше суммы кредита');
    }
    
    public function checkAge($attribute, $params)
    {
    	if ($this->$attribute && preg_match('/[^0-9]/', $this->$attribute) )
			$this->addError($attribute, 'Возраст должен быть цифрой');
		elseif ((int)$this->$attribute > 500)
			$this->addError($attribute, 'Вы Дункан Маклауд?');
    }
    
    public function checkDeadline($attribute, $params)
    {
		if (!$this->$attribute && !$this->deadlineType)
			$this->addError($attribute, 'Укажите срок');
    	elseif (!$this->deadlineType && preg_match('/[^0-9]/', $this->$attribute) )
			$this->addError($attribute, 'Cрок должен быть цифрой');
    }

    public function afterValidate()
    {
    	return parent::afterValidate();
    }

    public function beforeValidate()
    {
    	$this->sum = preg_replace('/[\s\.\,]/', '', $this->sum);
		$this->deadline = preg_replace('/[\s\.\,]/', '', $this->deadline);
		$this->age = preg_replace('/[\s\.\,]/', '', $this->age);
    	
    	return parent::beforeValidate();
    }
    
    public function attributeLabels()
    {
        return array(
        	'deadline' => 'Срок',
        	'deadlineType' => 'Срок',
        	'deadline_min' => 'Минимальный срок',
        	'deadline_max' => 'Максимальный срок',
        	'deadline_type' => 'Тип срока',
        	'deadline_type_min' => 'Тип минимального срока',
        	'deadline_type_max' => 'Тип максимального срока',
        	'sum' => 'Сумма',
        	'_formated_sum' => 'Сумма',
        	'currency' => 'Валюта суммы',
        );
    }
    
	/*
	 * Генерация параметров для ссылки сортировки результатов поиска программ 
	 */
	public static function generateOrderLinkAttributes ($attributes, $order, $key = 'order')
	{
		$tmp = array();
		foreach ($attributes as $k=>$v)
		{
			if ($v)
				$tmp[$k] = $v;
		}
		$attributes = $tmp;
		
		if (isset($attributes[$key]) && $attributes[$key] == $order)
		{
			$attributes[$key.'_type'] = $attributes[$key.'_type']==self::ORDER_ASC?self::ORDER_DESC:self::ORDER_ASC;
		}
		else
		{
			$attributes[$key] = $order;
			$attributes[$key.'_type'] = self::ORDER_ASC;
		}
		return $attributes;
	}
    
    public static function deadlineTypes ()
    {
    	/*
    	return array (
    		10 => array ('name'=>'до 1 года', 'deadline_min'=>0 , 'deadline_max'=>1, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		20 => array ('name'=>'1-2 года', 'deadline_min'=>1 , 'deadline_max'=>2, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		30 => array ('name'=>'2-5 лет', 'deadline_min'=>2 , 'deadline_max'=>5, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		40 => array ('name'=>'более 5 лет', 'deadline_min'=>5 , 'deadline_max'=>0, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    	);
    	*/
    	/*return array (
    		10 => array ('name'=>'1 месяц', 'deadline_min'=>0 , 'deadline_max'=>1, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_MONTH),
    		20 => array ('name'=>'3 месяца', 'deadline_min'=>1 , 'deadline_max'=>3, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_MONTH),
    		30 => array ('name'=>'6 месяцев', 'deadline_min'=>3 , 'deadline_max'=>6, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_MONTH),
    		40 => array ('name'=>'1 год', 'deadline_min'=>6 , 'deadline_max'=>1, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		50 => array ('name'=>'2 года', 'deadline_min'=>1 , 'deadline_max'=>2, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		60 => array ('name'=>'3 года', 'deadline_min'=>2 , 'deadline_max'=>3, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		70 => array ('name'=>'более 3 лет', 'deadline_min'=>3 , 'deadline_max'=>0, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    	);*/
		return array (	
    		20 => array ('name'=>'от 1 до 3 месяцев', 'deadline_min'=>1, 'deadline_max'=>3, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_MONTH),
    		30 => array ('name'=>'от 3 до 6 месяцев', 'deadline_min'=>3 , 'deadline_max'=>6, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_MONTH),
    		40 => array ('name'=>'от 6 месяцев до 1 года ', 'deadline_min'=>6 , 'deadline_max'=>1, 'deadline_type_min'=>BanksProgramm::DEADLINE_MONTH, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		50 => array ('name'=>'от 1 до 2 лет', 'deadline_min'=>1 , 'deadline_max'=>2, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		60 => array ('name'=>'от 2 до 3 лет', 'deadline_min'=>2 , 'deadline_max'=>3, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    		70 => array ('name'=>'более 3 лет', 'deadline_min'=>3 , 'deadline_max'=>0, 'deadline_type_min'=>BanksProgramm::DEADLINE_YEAR, 'deadline_type_max'=>BanksProgramm::DEADLINE_YEAR),
    	);
    }

    public static function deadlineTypesValues ()
    {
    	$res = array();
    	foreach (self::deadlineTypes() as $k=>$v)
    		$res[$k] = $v['name'];
    	return $res;
    }
    
    public function setDefaults ($params = array())
    {
    	$params = is_array($params)?$params:array();
    	//$defParams = $this->getDefaults ();
    	//$params = array_replace_recursive($defParams, $params);
    	$this->setAttributes($params);
    	return true;
    }
    
    /*
     * рисует скрытые поля атрибутов формы
     */
    public function hiddenFields ()
    {
    	foreach ($this->attributes as $k=>$v)
    	{
    		if (!$v)
    			continue;
    		echo Chtml::activeHiddenField($this, $k);
    	}
    }
}
?>