<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Solr
{
	CONST SOLR_VERSION	=	'1.4';

	const GET		=	'GET';
	const POST		=	'POST';

	private $host;
	private $port;
	private $path;
	private $curl;
	private $parameters = array();
	private $enableSuggestion = false;
	/**
	 * URL zadania wysylanego do solra
	 */
	private $solrUrl;

	function __construct($host = 'localhost', $port = 8983, $path = '/solr/')
	{
		$this->setHost($host);
		$this->setPort($port);
		$this->setPath($path);

		$this->curl = curl_init();
	}

	public function isConnected()
	{
		return $this->ping();
	}

	function __destruct()
	{
		curl_close($this->curl);
	}

	public function setHost($host)
	{
		$this->host = $host;
	}

	public function setPort($port)
	{
		$this->port = $port;
	}

	public function setPath($path)
	{
		$this->path = $path;
	}

	public function getBaseUrl()
	{
		return 'http://' . $this->host . ':' . $this->port . $this->path;
	}

	public function ping()
	{
		$start = microtime(true);

		$context = stream_context_create(
			array(
				'http' => array(
					'method' => 'HEAD',
					'timeout' => 5
				)
			)
		);
		$ping = @file_get_contents($this->getBaseUrl() . 'admin/ping', false, $context);

		if ($ping !== false)
		{
			return microtime(true) - $start;
		}
		else
		{
			return false;
		}
	}

	public function sendPost(&$data)
	{
		$headers = array(
			'Content-type: text/xml; charset=UTF-8'
		);

		curl_setopt($this->curl, CURLOPT_URL, $this->getBaseUrl() . 'update');
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($this->curl);
		$info = curl_getinfo($this->curl);

		if ($info['http_code'] != 200)
		{
			throw new Exception('Solr error while adding document: ' . str_replace("\n", ' ', strip_tags($response)));
		}
	}

	public function sendGet($url)
	{
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		$response = new Solr_Response(curl_exec($this->curl), curl_getinfo($this->curl));

		if ($response->getHttpCode() != 200)
		{
			throw new Exception('Solr error while retriving document: ' . $response->getError());
		}

		return $response;
	}

	public function addDocument(Search_Document $document)
	{
		$xml = '<add>' . $this->documentToXml($document) . '</add>';
		return $this->sendPost($xml);
	}

	public function addDocuments($documents)
	{
		$xml = '<add>';

		foreach ($documents as $document)
		{
			if ($document instanceof Search_Document)
			{
				$xml .= $this->documentToXml($document);
			}
		}
		$xml .= '</add>';

		return $this->sendPost($xml);
	}

	public function commit()
	{
		$xml = '<commit />';
		return $this->sendPost($xml);
	}

	public function optimize()
	{
		$xml = '<optimize />';
		return $this->sendPost($xml);
	}

	private function documentToXml(Search_Document &$document)
	{
		$xml = '<doc';

		if ($document->getBoost() !== false)
		{
			$xml .= ' boost="' . $document->getBoost() . '"';
		}
		$xml .= '>';

		foreach ($document as $name => $value)
		{
			$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
			$boost = $document->getFieldBoost($name);

			if (is_array($value))
			{
				foreach ($value as $multiValue)
				{
					$xml .= '<field name="' . $name . '"';

					if ($boost)
					{
						$xml .= ' boost="' . $boost . '"';
					}
					$xml .= '>';

					$xml .= htmlspecialchars($multiValue, ENT_NOQUOTES, 'UTF-8');
					$xml .= '</field>';
				}
			}
			else
			{
				$xml .= '<field name="' . $name . '"';

				if ($boost)
				{
					$xml .= ' boost="' . $boost . '"';
				}
				$xml .= '>';

				$xml .= htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
				$xml .= '</field>';
			}
		}

		$xml .= '</doc>';

		return $this->stripCtrlChars($xml);
	}

	private function stripCtrlChars($string)
	{
		return preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', ' ', $string);
	}

	public function enableSuggestion($value = true)
	{
		$this->enableSuggestion = $value;
	}

	public function addParameter($key, $value)
	{
		$this->parameters[$key] = $value;
	}

	public function setParameters(array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			$this->addParameter($key, $value);
		}
	}

	public function getParameter($key)
	{
		return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
	}

	public function getParameters()
	{
		return $this->parameters;
	}

	public function removeParameter($key, $value)
	{
		unset($this->parameters[$key]);
	}

	public function search($query, $offset = 0, $limit = 10, array $parameters = array())
	{
		$this->addParameter('version', self::SOLR_VERSION);
		$this->addParameter('wt', 'json');
		$this->addParameter('q', $query);
		$this->addParameter('start', $offset);
		$this->addParameter('rows', $limit);

		if ($this->enableSuggestion)
		{
			$this->addParameter('spellcheck', 'true');
			$this->addParameter('spellcheck.count', 1);
			$this->addParameter('spellcheck.collate', 'true');
//			$this->addParameter('spellcheck.build', 'false');
		}
		$this->setParameters($parameters);

		$queryString = http_build_query($this->getParameters(), null, '&');
		$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

//echo implode('<br>', explode('&', $queryString));

		Log::add('Solr query: ' . $queryString, E_DEBUG);

		$url = $this->getBaseUrl() . ($this->enableSuggestion ? 'spell' : 'select');
		$this->solrUrl = $url . '?' . $queryString;

		return $this->sendGet($url . '?' . $queryString);
	}

	public function terms($prefix, $limit = 10, array $parameters = array())
	{
		$this->addParameter('version', self::SOLR_VERSION);
		$this->addParameter('wt', 'json');
		$this->addParameter('terms.limit', $limit);
		$this->addParameter('terms.prefix', $prefix);

		$this->setParameters($parameters);
		$queryString = http_build_query($this->getParameters(), null, '&');
		$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

		return $this->sendGet($this->getBaseUrl() . 'terms?' . $queryString);
	}

	public function suggest($prefix, $limit = 10, $parameters = array())
	{
		$this->addParameter('version', self::SOLR_VERSION);
		$this->addParameter('wt', 'json');
		$this->addParameter('spellcheck.count', $limit);
		$this->addParameter('q', $prefix);

		$this->setParameters($parameters);
		$queryString = http_build_query($this->getParameters(), null, '&');
		$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

		return $this->sendGet($this->getBaseUrl() . 'suggest?' . $queryString);
	}

	public function delete($xml)
	{
		return $this->sendPost($xml);
	}

	public function deleteById($id)
	{
		$id = htmlspecialchars($id, ENT_NOQUOTES, 'UTF-8');
		$xml = '<delete><id>' . $id . '</id></delete>';

		return $this->delete($xml);
	}

	public function deleteByIds($ids)
	{
		$xml = '<delete>';

		foreach ($ids as $id)
		{
			$id = htmlspecialchars($id, ENT_NOQUOTES, 'UTF-8');
			$xml .= '<id>' . $id . '</id>';
		}
		$xml .= '</delete>';

		return $this->delete($xml);
	}

	public function deleteByQuery($query)
	{
		$query = htmlspecialchars($query, ENT_NOQUOTES, 'UTF-8');
		$xml = '<delete><query>' . $query . '</query></delete>';

		return $this->delete($xml);
	}

	public function getSolrUrl()
	{
		return $this->solrUrl;
	}
}

?>