<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Solr_Response implements IteratorAggregate
{
	private $contentType;
	private $httpCode;
	private $headerSize;
	private $totalTime;
	private $url;
	private $queryTime;
	private $foundRows;
	private $documents = array();
	private $terms = array();
	private $facet = array();
	private $suggestion;
	private $error;

	function __construct($data, $httpHeaders)
	{
		$this->contentType = @$httpHeaders['content_type'];
		$this->httpCode = $httpHeaders['http_code'];
		$this->headerSize = $httpHeaders['header_size'];
		$this->totalTime = $httpHeaders['total_time'];
		$this->url = $httpHeaders['url'];

		if (!$data)
		{
			return;
		}
		if ($this->httpCode != 200)
		{
			$this->error = trim(str_replace("\n", ' ', strip_tags($data)));
			return;
		}
		$data = json_decode($data, true);

		$this->queryTime = $data['responseHeader']['QTime'];

		if (isset($data['grouped']))
		{
			$key = key($data['grouped']);

			$this->foundRows = $data['grouped'][$key]['ngroups'];
			$this->parseGroupedDocuments($data);

			if (isset($data['spellcheck']['suggestions']))
			{
				$this->suggestion = array_pop($data['spellcheck']['suggestions']);
			}
		}
		else
		{
			if (isset($data['response']['numFound']))
			{
				$this->foundRows = $data['response']['numFound'];
			}

			if (isset($data['terms']))
			{
				$this->parseTerms($data['terms']);
			}

			if (isset($data['facet_counts']))
			{
				$this->parseFacet($data['facet_counts']);
			}

			if (isset($data['spellcheck']['suggestions']))
			{
				$this->suggestion = $data['spellcheck']['suggestions'];

				if (is_array($this->suggestion))
				{
					$this->suggestion = array_pop($this->suggestion);
				}
			}

			if (!empty($data['response']['docs']))
			{
				$this->parseDocuments($data);
			}
		}


	}

	private function parseTerms(array &$terms)
	{
		if (empty($terms))
		{
			return false;
		}

		foreach ($terms as $arr)
		{
			if (is_array($arr))
			{
				for ($i = 0, $count = sizeof($arr); $i < $count; $i += 2)
				{
					$this->terms[$arr[$i]] = $arr[$i + 1];
				}
			}
		}
	}

	private function parseFacet(array &$facet)
	{
		if (isset($facet['facet_fields']))
		{
			foreach ($facet['facet_fields'] as $category => $arr)
			{
				for ($i = 0, $count = sizeof($arr); $i < $count; $i += 2)
				{
					$this->facet[$category][$arr[$i]] = $arr[$i + 1];
				}
			}
		}
	}

	private function parseDocuments(&$data)
	{
		foreach ($data['response']['docs'] as $json)
		{
			$document = new Search_Document;

			foreach ($json as $key => $value)
			{
				/**
				 * @todo Na sztywno wpisany klucz glowny - do zmiany!
				 */
				if ($key == 'id' && !empty($data['highlighting'][$json[$key]]))
				{
					$document->addField('highlight', $data['highlighting'][$json[$key]]);
				}
				elseif (is_array($value) && count($value) <= 1)
				{
					$value = array_shift($value);
				}

				$document->addField($key, $value);
			}

			$this->documents[] = $document;
		}
	}

	private function parseGroupedDocuments(&$data)
	{
		$index = key($data['grouped']);

		foreach ($data['grouped'][$index]['groups'] as $json)
		{
			foreach ($json['doclist']['docs'] as $rowset)
			{
				$document = new Search_Document();

				foreach ($rowset as $key => $value)
				{
					if (!empty($data['highlighting'][$rowset['id']][$key]))
					{
						$document->addField('highlight', $data['highlighting'][$rowset['id']]);
					}
					elseif (is_array($value) && count($value) <= 1)
					{
						$value = array_shift($value);
					}

					$document->addField($key, $value);
				}

				$this->documents[] = $document;
			}
		}
	}

	public function getIterator()
	{
        return new ArrayIterator($this->documents);
    }

	public function getDocuments()
	{
		return $this->documents;
	}

	public function getContentType()
	{
		return $this->contentType;
	}

	public function getHttpCode()
	{
		return $this->httpCode;
	}

	public function getHeaderSize()
	{
		return $this->headerSize;
	}

	public function getTotalTime()
	{
		return $this->totalTime;
	}

	public function getQueryTime()
	{
		return $this->queryTime;
	}

	public function getFoundRows()
	{
		return $this->foundRows;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getAutoSuggest()
	{
		$result = array();

		foreach ($this->suggestion as $key => $rowset)
		{
			if (is_array($rowset))
			{
				if (isset($rowset['suggestion']))
				{
					$result = $rowset['suggestion'];
					break;
				}
			}
		}

		return $result;
	}

	public function getSuggestion()
	{
		return $this->suggestion;
	}

	public function getTerms()
	{
		return $this->terms;
	}

	public function getFacets()
	{
		return $this->facet;
	}

	public function getError()
	{
		return $this->error;
	}
}
?>