<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Connector_News extends Connector_Document implements Connector_Interface
{
	private $url;
	private $hash;
	private $newsId;
	private $host;
	private $score;
	private $rate;
	private $priority = 1;
	private $isSponsored;
	private $thumbnail;

	function __construct($data = array())
	{
		parent::__construct($data);

		if ($this->getId())
		{
			$news = &$this->getModel('news');
			$result = $news->getByPage($this->getId())->fetchAssoc();

			$this->setUrl($result['news_url']);
			$this->setNewsId($result['news_id']);
			$this->setPriority($result['news_priority']);
			$this->setIsSponsored($result['news_sponsored']);
			$this->setThumbnail($result['news_thumbnail']);

			$this->score = $result['news_score'];
			$this->hash = $result['news_hash'];
			$this->rate = $result['news_rate'];
		}
		else
		{
			/*
			 * Ustawienie wartosci domyslnych
			 */
			$this->setModuleId($this->module->getId('news'));
			$this->setContentType(Page::HTML);
			$this->setTemplate('newsView.php');
		}
	}

	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		$this->setHost(parse_url($url, PHP_URL_HOST));

		$this->hash = md5($url);

		return $this;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getHash()
	{
		return $this->hash;
	}

	public function setNewsId($newsId)
	{
		$this->newsId = $newsId;
		return $this;
	}

	public function getNewsId()
	{
		return $this->newsId;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function getRate()
	{
		return $this->rate;
	}

	/**
	 * @todo Walidacja liczby priorytetu (w przedziale 1-6)
	 */
	public function setPriority($priority)
	{
		$this->priority = (int) $priority;
		return $this;
	}

	public function getPriority()
	{
		return $this->priority;
	}

	public function setIsSponsored($flag)
	{
		$this->isSponsored = (bool) $flag;
		return $this;
	}

	public function isSponsored()
	{
		return $this->isSponsored;
	}

	public function setThumbnail($thumbnail)
	{
		$this->thumbnail = $thumbnail;
	}

	public function getThumbnail()
	{
		return $this->thumbnail;
	}

	public function renderForm()
	{
		parent::renderForm();

		$fieldset = &$this->getFieldset('content');
		$fieldset->removeElement('page_path');

		$url = $fieldset->createElement('text', 'url');
		$url->setLabel('Adres URL')
			->setDescription('Wymagany URL linku')
			->setOrder(-1)
			->setValue('http://')
			->setRequired(true);

		$url->addValidator(new Validate_Url);

		if (!$this->getId())
		{
			$url->addValidator(new Validate_News);
		}
		else
		{
			$url->setValue($this->getUrl());
		}

		$priorityList = array(
			1		=> '1 (domyślnie)',
			2		=> '2',
			3		=> '3',
			4		=> '4',
			5		=> '5'
		);

		$priority = $fieldset->createElement('select', 'priority');
		$priority->setLabel('Priorytet')
				 ->setDescription('Im większy priorytet, tym większa pozycja wpisu na liście')
				 ->setOrder(-1)
				 ->setMultiOptions($priorityList)
				 ->setValue(1)
				 ->addFilter('int');

		$sponsored = $fieldset->createElement('checkbox', 'sponsored');
		$sponsored->setLabel('Wpis sponsorowany')
				  ->setDescription('Zaznacz, jeżeli wpis ma być sponsorowany (wysoka pozycja na liście)')
				  ->setValue($this->isSponsored())
				  ->setOrder(-1)
				  ->addFilter('int');

		if ($this->getThumbnail())
		{
			$thumbnail = $fieldset->createElement('checkbox', 'deleteThumbnail');
			$thumbnail->setLabel('Usuń miniaturę')
					  ->setDescription('Do tego wpisu przypisana jest miniatura. Zaznacz, aby usunąć')
					  ->setValue(0)
					  ->setOrder(-1)
					  ->addFilter('int');

		}

		$fieldset = &$this->getFieldset('setting');
		$fieldset->getElement('page_template')->setValue('newsView.php');

		$this->setDefaults();
	}

	public function onBeforeSave()
	{
		parent::onBeforeSave();

		if ($this->isRenderMode())
		{
			$values = $this->getValues();

			$this->setUrl(@$values['url']);
			$this->setPriority(@$values['priority']);
			$this->setIsSponsored(@$values['sponsored']);

			if ($this->getThumbnail())
			{
				if (isset($this->post->deleteThumbnail))
				{
					$store = $this->module->news('store', $this->getId());
					@unlink($store . $this->getThumbnail());
					@unlink($store . '120-' . $this->getThumbnail());

					$this->setThumbnail('');
				}
			}
		}
		else
		{
			$query = $this->db->select('parser_id')->from('parser')->where('parser_default = 1');
			$this->setParsers($query->fetchCol());
		}

		if (!$this->getPath())
		{
			$this->setPath($this->getSubject());
		}
		if (!$this->getContentType())
		{
			$this->setContentType(Page::HTML);
		}
		if (!$this->getTemplate())
		{
			$this->setTemplate('newsView.php');
		}

		$news = &$this->getModel('news');

		if (!$this->getId())
		{
			$uniqId = $news->getNextId();
			$this->setPath($uniqId . '-' . $this->getPath());
		}
		else
		{
			if (!$this->isRenderMode())
			{
				if (!preg_match('#(\d+)\-(.*)#', $this->getPath()))
				{
					$this->setPath($this->getId() . '-' . $this->getPath());
				}
			}
		}
	}

	public function onAfterSave()
	{
		parent::onAfterSave();

		$data = array(
			'news_url'			=> (string) $this->getUrl(),
			'news_hash'			=> (string) $this->getHash(),
			'news_host'			=> (string) $this->getHost(),
			'news_priority'		=> (int) $this->getPriority(),
			'news_sponsored'	=> (bool) $this->isSponsored(),
			'news_thumbnail'	=> (string) $this->getThumbnail()
		);

		$news = &$this->getModel('news');

		if (!$this->getNewsId())
		{
			$data['news_page'] = $this->getId();
			$data['news_user'] = User::$id;

			$news->insert($data);
			$this->setNewsId($this->db->nextId());

			$news->vote->setVote($this->getNewsId(), 1);
		}
		else
		{
			$news->update($data, 'news_id = ' . $this->getNewsId());
		}
	}

	public function delete()
	{
		$this->db->delete('news', "news_page = " . $this->getId());

		if ($this->getThumbnail())
		{
			$store = $this->module->news('store', $this->getId());
			@unlink($store . $this->getThumbnail());
			@unlink($store . '120-' . $this->getThumbnail());
		}
		parent::delete();
	}
}
?>