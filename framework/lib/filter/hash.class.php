<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Filtr hashujacy
 */
class Filter_Hash implements IFilter
{
	/**
	 * Nazwa algorytmu (md5, sha256)
	 */
	private $algo;
	/**
	 * Opcjonalny Salt
	 */
	private $salt;

	/**
	 * @param string $algo	Nazwa algorytmu hashujacego
	 * @param string $salt	Salt (opcjonalny)
	 */
	function __construct($algo = null, $salt = null)
	{
		if ($algo !== null)
		{
			$this->setAlgo($algo);
		}
		if ($salt !== null)
		{
			$this->setSalt($salt);
		}
	}

	/**
	 * @param string $algo	Nazwa algorytmu hashujacego
	 */
	public function setAlgo($algo)
	{
		$this->algo = (string) $algo;
		return $this;
	}

	/**
	 * @return string Nazwa algorytmu hashujacego
	 */
	public function getAlgo()
	{
		return $this->algo;
	}

	/**
	 * Ustawienie saltu
	 * @param string $salt
	 */
	public function setSalt($salt)
	{
		$this->salt = (string) $salt;
		return $this;
	}

	/**
	 * Zwraca Salt
	 * @return string
	 */
	public function getSalt()
	{
		return $this->salt;
	}

	public function filter($value)
	{
		if (!$this->getAlgo())
		{
			throw new Exception('Hash filter: missing algorithm');
		}

		return hash($this->getAlgo(), $this->getSalt() . $value);
	}
}
?>