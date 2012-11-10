<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Helper ulatwiajacy tworzenie znacznikow HTML odpowiadajacych za formularze
 */
class Form
{
	/**
	 * Generuje znacznik otwierajacy <form>
	 * @param string $url Adres do skryptu obslugi formularza
	 * @param mixed $attributes Atrybuty dla znacznika HTML
	 * @return string
	 */
	public static function open($url = '', $attributes = array())
	{
		if ($url)
		{
			$attributes['action'] = url($url);
		}
		return Html::tag('form', true, $attributes);
	}

	/**
	 * Generuje znacznik zamykajacy </form>
	 * @return string
	 */
	public static function close()
	{
		return '</form>';
	}

	/**
	 * Generuje znacznik otwierajacy <form> z atrybutem enctype
	 * @param string $url Adres do skryptu obslugi formularza
	 * @param mixed $attributes Atrybuty dla znacznika HTML
	 * @return string
	 */
	public static function openMultipart($url, $attributes = array())
	{
		$attributes += array(
			'method'			=> 'POST',
			'enctype'			=> 'multipart/form-data'
		);
		return self::open($url, $attributes);
	}

	/**
	 * Generuje znacznik <input>
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function input($name, $value = '', $attributes = array())
	{
		if (empty($attributes['type']))
		{
			$attributes['type'] = 'text';
		}
		$attributes += array(
			'value'				=> $value
		);
		if ($name)
		{
			$attributes['name'] = $name;
		}

		return Html::tag('input', false, $attributes);
	}

	/**
	 * Generuje znacznik <input type="text">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function text($name, $value = '', $attributes = array())
	{
		$attributes += array(
			'type'		=> 'text',
			'value'		=> $value
		);

		if (!empty($name))
		{
			$attributes['name'] = $name;
		}

		return Html::tag('input', false, $attributes);
	}

	/**
	 * Generuje znacznik <input type="hidden">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function hidden($name, $value = '', $attributes = array())
	{
		$attributes['type'] = 'hidden';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="password">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function password($name, $value = '', $attributes = array())
	{
		$attributes['type'] = 'password';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="file">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function file($name, $value = '', $attributes = array())
	{
		$attributes['type'] = 'file';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="button">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function button($name = '', $value = '', $attributes = array())
	{
		$attributes['type'] = 'button';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="submit">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function submit($name = '', $value = '', $attributes = array())
	{
		$attributes['type'] = 'submit';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="reset">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function reset($name = 'reset', $value = '', $attributes = array())
	{
		$attributes['type'] = 'reset';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="checkbox">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function checkbox($name, $value = '', $checked = false, $attributes = array())
	{
		if ($checked)
		{
			$attributes['checked'] = 'checked';
		}
		$attributes['type'] = 'checkbox';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <input type="radio">
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function radio($name, $value = '', $checked = false, $attributes = array())
	{
		if ($checked)
		{
			$attributes['checked'] = 'checked';
		}
		$attributes['type'] = 'radio';
		return self::input($name, $value, $attributes);
	}

	/**
	 * Generuje znacznik <textarea>
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function textarea($name, $value = '', $attributes = array())
	{
		$attributes['name'] = $name;
		$xhtml = Html::tag('textarea', true, $attributes, (string) $value);

		return $xhtml;
	}

	/**
	 * Generuje znacznik <select>
	 * @param string $name Nazwa pola
	 * @param string $value Wartosc dla pola (znacznik <option>)
	 * @param mixed $attributes Dodatkowe atrybuty
	 * @return string
	 */
	public static function select($name, $value = '', $attributes = array())
	{
		if (isset($attributes['multiple']))
		{
			$name .= '[]';
		}
		$attributes['name'] = $name;

		return Html::tag('select', true, $attributes, $value);
	}

	/**
	 * Generuje znacznik <option>
	 * @param mixed $value Tablica asocjacyjna key => $value
	 * @param mixed|bool $selected Nazwa klucza (lub tablica) okreslajacego, ktore pozycje beda domyslnie zaznczone
	 * @param mixed $include_key Domyslnie TRUE - oznacza generowanie znacznika z atrybutem value
	 * @param mixed $attributes Tablica dodatkowych atrybutow
	 * @return string
	 */
	public static function option($value, $selected = false, $include_key = true, $attributes = array())
	{
		$output = '';

		if (!is_array($selected))
		{
			$selected = array($selected);
		}
		foreach ($value as $key => $v)
		{
			$attr = isset($attributes[$key]) ? $attributes[$key] : array();

			if ($include_key)
			{
				$attr['value'] = $key;
			}
			if (in_array($key, $selected))
			{
				$attr['selected'] = 'selected';
			}
			$output .= Html::tag('option', true, $attr, $v);
		}
		return $output;
	}

	/**
	 * Generuje znacznik <optgroup>
	 * @param mixed $value Tablica asocjacyjna
	 * @return string
	 */
	public static function optgroup($value)
	{
		$output = '';

		foreach ($value as $label => $v)
		{
			$output .= Html::tag('optgroup', true, array('label' => $label), $v);
		}
		return $output;
	}

	/**
	 * Generuje znacznik <select> zawierajacy liste lat
	 * @param string $name Nazwa znacznika HTML
	 * @param mixed|bool $select Oznacza pozycje, ktora bedzie domyslnie zaznaczona (atrybut select)
	 * @param int $start Rok poczatkowy (pierwsza pozycja na liscie)
	 * @param int $end Rok koncowy (domyslnie zero - aktualny rok)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika
	 * @return string
	 */
	public static function year($name = 'year', $select = false, $start = 1920, $end = 0, $attributes = array())
	{
		if (!$end)
		{
			$end = date('Y');
		}

		$years[0] = '';
		for ($i = $start; $i <= $end; $i++)
		{
			$years[$i] = $i;
		}
		return self::select($name, self::option($years, $select), $attributes);
	}

	/**
	 * Generuje znacznik <select> zawierajacy liste miesiecy
	 * @param string $name Nazwa znacznika HTML
	 * @param mixed|bool $select Oznacza pozycje, ktora bedzie domyslnie zaznaczona (atrybut select)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika
	 * @return string
	 */
	public static function month($name = 'month', $select = false, $attributes = array())
	{
		$_month = array(
				'Styczeń',
				'Luty',
				'Marzec',
				'Kwiecień',
				'Maj',
				'Czerwiec',
				'Lipiec',
				'Sierpień',
				'Wrzesień',
				'Październik',
				'Listopad',
				'Grudzień'
		);
		$months[0] = '';
		for ($i = 1; $i <= 12; $i++)
		{
			$months[$i] = $_month[$i - 1];
		}

		return self::select($name, self::option($months, $select), $attributes);
	}

	/**
	 * Generuje znacznik <select> zawierajacy liste dni miesiaca
	 * @param string $name Nazwa znacznika HTML
	 * @param mixed|bool $select Oznacza pozycje, ktora bedzie domyslnie zaznaczona (atrybut select)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika
	 * @return string
	 */
	public static function day($name = 'day', $select = false, $attributes = array())
	{
		$days[0] = '';
		for ($i = 1; $i <= 31; $i++)
		{
			$days[$i] = sprintf('%02s', $i);
		}

		return self::select($name, self::option($days, $select), $attributes);
	}

	/**
	 * Generuje znacznik <select> zawierajacy liste godzin (00-23)
	 * @param string $name Nazwa znacznika HTML
	 * @param mixed|bool $select Oznacza pozycje, ktora bedzie domyslnie zaznaczona (atrybut select)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika
	 * @return string
	 */
	public function hour($name = 'hour', $select = false, $attributes = array())
	{
		$hours[-1] = '';
		for ($i = 0; $i <= 23; $i++)
		{
			$hours[$i] = sprintf('%02s', $i);
		}
		return self::select($name, self::option($hours, $select), $attributes);
	}

	/**
	 * Generuje znacznik <select> zawierajacy liste minut (1-59)
	 * @param string $name Nazwa znacznika HTML
	 * @param mixed|bool $select Oznacza pozycje, ktora bedzie domyslnie zaznaczona (atrybut select)
	 * @param mixed $attributes Dodatkowe atrybuty dla znacznika
	 * @return string
	 */
	public static function minute($name = 'minute', $select = false, $attributes = array())
	{
		$minutes[0] = '';
		for ($i = 1; $i <= 59; $i++)
		{
			$minutes[$i] = sprintf('%02s', $i);
		}
		return self::select($name, self::option($minutes, $select), $attributes);
	}

}
?>