<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Klasa wyswietla informacje (bledy, komunikaty, ostrzezenia)
 */
class Box
{
	/**
	 * Wyswietla strone informacyjna z przyciskiem ok
	 * @param string $title Tytul strony
	 * @param string $text Tresc komunikatu
	 * @param string $referer Opcjonalny argument (przekierowanie na jakas strone)
	 */
	public static function information($title, $text, $referer = '', $filename = 'information_box')
	{
		if (!$referer)
		{
			$referer = Core::getInstance()->input->getReferer();
		}
	
		$view = new View($filename);
		Core::getInstance()->output->setTitle($title);

		$view->assign(array(
			'u_referer'			=> $referer,
			'message_title'		=> $title,
			'message_text'		=> $text
			)
		);
		echo $view;
	}
	
	/**
	 * Wyswietla strone z oknem 'confirm'
	 * @param string $title Tytul strony
	 * @param string $text Tekst
	 * @param string $s_hidden_data Dodatkowe dane majace znalezc sie w <form>
	 * @param string $confirm URL do ktorego zostanie przekierowany user w momencie nacisneicia przycisku
	 * @param string $filename Nazwa widoku zawierajacego kod HTML
	 */
	public static function confirm($title, $text, $s_hidden_data = '', $confirm = '', $filename = 'confirm_box')
	{
		if (Core::getInstance()->input->post->yes)
		{ 
			return true;
		}
		if (Core::getInstance()->input->post->no)
		{ 
			return false;
		}

		$view = new View($filename);
		Core::getInstance()->output->setTitle($title);

		$view->assign(array(
			'u_confirm'			=> $confirm,
			'message_text'		=> $text,
			'message_title'		=> $title,
			's_hidden_data'		=> $s_hidden_data
			)
		);
		echo $view;

		exit;
	}
}

?>