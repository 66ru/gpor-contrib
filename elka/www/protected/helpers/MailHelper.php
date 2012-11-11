<?php
class MailHelper
{
	const ENCODING_CP1251 = 'cp1251';
	const ENCODING_UTF8 = 'utf-8';
	
	public static function senderEmail ()
	{
		return Yii::app()->params['senderEmail'];
	}
	
	public static function adminEmails ()
	{
		return array(
			Yii::app()->params['adminEmail'] => 'admin',
		);
	}
	
	
	public static function sendMailToAdmin ($message)
	{
		foreach (self::adminEmails() as $email => $name)
		{
			$message['to_email'] = $email;
			$message['to_username'] = $name;
			$res = self::sendMail ($message);
			if (!$res)
				return false;
		}
		return true;
	}


	public static function sendMail ($message)
	{
		$body = $message['html'];
		
		require_once(LIB_PATH. DS . 'PHPMailer' . DS . 'class.phpmailer.php');
		require_once(LIB_PATH. DS. 'emogrifier'. DS .'emogrifier.php');

		if(preg_match('/<style.*?>(.*?)<\/style>/usix', $body, $matches))
		{
			$body = preg_replace('/<style.*?>(.*?)<\/style>/usix','', $body);

			$parse = new Emogrifier($body, $matches[1]);
			$body = $parse->emogrify();
		}

		$mail = new PHPMailer();

		$mail->Mailer = 'sendmail';
		$mail->Encoding = 'base64';
		$mail->Sender = self::senderEmail();
		
		if (!isset($message['from_email']) || empty($message['from_email']))
			$message['from_email'] = self::senderEmail();

		if (!isset($message['from_username']) || empty($message['from_username']))
			$message['from_username'] = '';
		
		$message['encoding_id'] = isset($message['encoding_id']) ? $message['encoding_id'] : self::ENCODING_UTF8;

		$textPlain = isset($message['text']) ? $message['text'] : '';

		switch($message['encoding_id'])
		{
			case self::ENCODING_CP1251:
				$mail->CharSet = 'windows-1251';
				$mail->SetFrom($message['from_email'], iconv('UTF-8', 'CP1251',$message['from_username']));
				$mail->AddAddress($message['to_email'], iconv('UTF-8', 'CP1251',$message['to_username']));
				$mail->Subject = iconv('UTF-8', 'CP1251', $message['subject']);
				if ($textPlain)
					$mail->AltBody = iconv('UTF-8', 'CP1251', $textPlain);
				$body = str_replace('<meta content="text/html; charset=utf-8"','<meta content="text/html; charset=windows-1251"', $body);
				$mail->MsgHTML(iconv('UTF-8', 'CP1251',$body));
				break;
			default:
				$mail->CharSet = 'UTF-8';
				$mail->SetFrom($message['from_email'], $message['from_username']);
				$mail->AddAddress($message['to_email'], $message['to_username']);
				$mail->Subject = $message['subject'];
				if ($textPlain)
					$mail->AltBody = $textPlain;
				$mail->MsgHTML($body);
				break;
		}
            
		if (isset($message['files']) && $message['files'])
		{
			foreach ($message['files'] as $file)
			{
				$fileData = array_replace_recursive (array(
					'name' => 'attachment',
 					'path' => '',
					'encoding' => 'base64',
 					'type' => 'application/octet-stream',
				), $file);
            		
				$mail->AddAttachment(
 				$fileData['path'],
				$fileData['name'],
				$fileData['encoding'],
				$fileData['type']
				);
			}
		}

		if($mail->Send()) {
			return true;
		}
		return false;
	}

}
?>