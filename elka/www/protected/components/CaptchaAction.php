<?php
/**
 * CaptchaAction class file.
 *
 * @author Stepanoff <stenlex@gmail.com>
 */

/**
 * CaptchaAction renders a CAPTCHA image.
 *
 * CCaptchaAction is used together with {@link CCaptcha} and {@link CCaptchaValidator} and {@link CCaptchaAction}
 * to provide the {@link http://en.wikipedia.org/wiki/Captcha CAPTCHA} feature.
 */
class CaptchaAction extends CCaptchaAction
{
	/**
	 * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
	 */
	const REFRESH_GET_VAR='refresh';
	/**
	 * Prefix to the session variable name used by the action.
	 */
	const SESSION_VAR_PREFIX='Yii.CCaptchaAction.';
	/**
	 * @var integer how many times should the same CAPTCHA be displayed. Defaults to 3.
	 */
	public $testLimit=3;
	/**
	 * @var integer the width of the generated CAPTCHA image. Defaults to 120.
	 */
	public $width=120;
	/**
	 * @var integer the height of the generated CAPTCHA image. Defaults to 50.
	 */
	public $height=50;
	/**
	 * @var integer padding around the text. Defaults to 2.
	 */
	public $padding=2;
	/**
	 * @var integer the background color. For example, 0x55FF00.
	 * Defaults to 0xFFFFFF, meaning white color.
	 */
	public $backColor=0xFFFFFF;
	/**
	 * @var integer the font color. For example, 0x55FF00. Defaults to 0x2040A0 (blue color).
	 */
	public $foreColor=0x2040A0;
	/**
	 * @var boolean whether to use transparent background. Defaults to false.
	 * @since 1.0.10
	 */
	public $transparent=false;
	/**
	 * @var integer the minimum length for randomly generated word. Defaults to 6.
	 */
	public $minLength=6;
	/**
	 * @var integer the maximum length for randomly generated word. Defaults to 7.
	 */
	public $maxLength=7;
	/**
	 * @var string the TrueType font file. Defaults to Duality.ttf which is provided
	 * with the Yii release.
	 */
	public $fontFile;

	/**
	 * Generates a new verification code.
	 * @return string the generated verification code
	 */
	protected function generateVerifyCode()
	{
		if($this->minLength<3)
			$this->minLength=3;
		if($this->maxLength>20)
			$this->maxLength=20;
		if($this->minLength>$this->maxLength)
			$this->maxLength=$this->minLength;
		$length=rand($this->minLength,$this->maxLength);

		/*
		$letters='bcdfghjklmnpqrstvwxyz';
		$vowels='aeiou';
		$code='';
		for($i=0;$i<$length;++$i)
		{
			if($i%2 && rand(0,10)>2 || !($i%2) && rand(0,10)>9)
				$code.=$vowels[rand(0,4)];
			else
				$code.=$letters[rand(0,20)];
		}
		*/
		$letters='1234567890';
		$code='';
		for($i=0;$i<$length;++$i)
		{
			$code.=$letters[rand(0,9)];
		}

		return $code;
	}
}