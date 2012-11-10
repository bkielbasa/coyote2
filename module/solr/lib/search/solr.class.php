<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Search_Solr extends Context implements Search_Interface
{
	private $queryString;
	private $sort;
	private $timeLimit;
	private $fields = array();
	private $groups = array();
	private $params = array();
	private $filters = array();
	private $totalRows;
	private $totalTime;
	private $enableSuggestion;
	private $suggestion;
	private $enableFacet;
	private $facetField = 'connector';
	private $facets;
	private $startPage;
	private $limit;
	private $enableHighlight;
	private $highlightFields;
	private $highlightSnippets;
	private $highlightSize;
	private $highlightTag;

	public function setQueryString($queryString)
	{
		$this->queryString = trim($queryString);
		return $this;
	}

	public function getQueryString()
	{
		return $this->queryString;
	}

	public function addField($field, $boost = 1.0)
	{
		$this->fields[$field] = $boost;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function setSort($sort)
	{
		$this->sort = $sort;
		return $this;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setTimeLimit($timeLimit)
	{
		$this->timeLimit = $timeLimit;
		return $this;
	}

	public function getTimeLimit()
	{
		return $this->timeLimit;
	}

	public function addGroup($groupId)
	{
		$this->groups[] = $groupId;
		return $this;
	}

	public function setGroups(array $groups)
	{
		$this->groups = $groups;
		return $this;
	}

	public function getGroups()
	{
		return $this->groups;
	}

	public function addParam($name, $value)
	{
		if (!isset($this->params[$name]))
		{
			$this->params[$name] = array();
		}

		$this->params[$name][] = $value;
		return $this;
	}

	public function setParams(array $params = array())
	{
		$this->params = $params;
		return $this;
	}

	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	public function addFilter($filter, $value)
	{
		if (!isset($this->filters[$filter]))
		{
			$this->filters[$filter] = array();
		}

		$this->filters[$filter][] = $value;
		return $this;
	}

	public function addFilters($filter, array $values)
	{

	}

	public function setFilters(array $filters = array())
	{
		$this->filters = $filters;
		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function getFilter($filter)
	{
		return isset($this->filters[$filter]) ? $this->filters[$filter] : null;
	}

	public function removeFilter($filter)
	{
		unset($this->filters[$filter]);
		return $this;
	}

	public function removeFilters()
	{
		$this->filters = array();
		return $this;
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getTotalRows()
	{
		return $this->totalRows;
	}

	public function getTotalTime()
	{
		return $this->totalTime;
	}

	public function setStartPage($start)
	{
		$this->startPage = (int) $start;
		return $this;
	}

	public function getStartPage()
	{
		return $this->startPage;
	}

	public function setLimit($limit)
	{
		$this->limit = (int) $limit;
		return $this;
	}

	public function getLimit()
	{
		return $this->limit;
	}

	public function setEnableSuggestion($flag)
	{
		$this->enableSuggestion = (bool) $flag;
		return $this;
	}

	public function isSuggestionEnabled()
	{
		return $this->enableSuggestion;
	}

	public function getSuggestion()
	{
		return $this->suggestion;
	}

	public function setEnableFacet($flag)
	{
		$this->enableFacet = (bool) $flag;
		return $this;
	}

	public function setFacetField($field)
	{
		$this->facetField = $field;
		return $this;
	}

	public function getFacetField()
	{
		return $this->facetField;
	}

	public function isFacetEnabled()
	{
		return $this->enableFacet;
	}

	public function getFacets()
	{
		return $this->facets;
	}

	public function setEnableHighlight($flag)
	{
		$this->enableHighlight = (bool) $flag;
		return $this;
	}

	public function isHighlightEnabled()
	{
		return $this->enableHighlight;
	}

	public function setHighlightFields($fields)
	{
		$this->highlightFields = $fields;
		return $this;
	}

	public function getHighlightFields()
	{
		return $this->highlightFields;
	}

	public function setHighlightSize($size)
	{
		$this->highlightSize = $size;
		return $this;
	}

	public function getHighlightSize()
	{
		return $this->highlightSize;
	}

	public function setHighlightSnippets($snippets)
	{
		$this->highlightSnippets = $snippets;
		return $this;
	}

	public function getHighlightSnippets()
	{
		return $this->highlightSnippets;
	}

	public function setHighlightTag($tag)
	{
		$this->highlightTag = $tag;
		return $this;
	}

	public function getHighlightTag()
	{
		return $this->highlightTag;
	}

	public function find($queryString = '', $start = null, $limit = null)
	{
		if ($queryString != '')
		{
			$this->setQueryString($queryString);
		}
		if ($start !== null)
		{
			$this->setStartPage($start);
		}
		if ($limit !== null)
		{
			$this->setLimit($limit);
		}

		if ($this->getFields())
		{
			$this->addParam('defType', 'edismax');
			$this->addParam('tie', '0.1');
//			$this->addParam('ps', 4);

			$qf = $pf = '';
			foreach ($this->getFields() as $field => $boosts)
			{
				$qf .= $field . ' ';
				$pf .= $field . ($boosts != 1.0 ? "^$boosts " : ' ');
			}

			$this->addParam('qf', rtrim($pf));
//			$this->addParam('pf', rtrim($pf));
		}

		if ($this->getSort())
		{
			$this->addParam('sort', $this->getSort());
		}
		if ($this->getTimeLimit())
		{
			$this->addFilter('timestamp', '[' . (time() - $this->getTimeLimit()) . ' TO ' . time() . ']');
		}

		$group = array();
		foreach ($this->getGroups() as $groupId)
		{
			$group[] = 'group:' . $groupId;
		}

		if ($group)
		{
			$this->addParam('fq', implode(' OR ', $group));
		}

		foreach ($this->getFilters() as $name => $values)
		{
			if (count($values) <= 1)
			{
				$this->addParam('fq', $name . ':' . $values[0]);
			}
			else
			{
				$filter = array();

				foreach ($values as $value)
				{
					$filter[] = "$name:$value";
				}

				$this->addParam('fq', implode(' OR ', $filter));
			}
		}

		if ($this->isFacetEnabled())
		{
			/**
			 * @todo Umozliwic ustawianie tych wartosci z zewnatrz klasy
			 */
			$this->addParam('facet', 'true');
			$this->addParam('facet.limit', '-1');
			$this->addParam('facet.field', $this->getFacetField());
		}
		if ($this->isHighlightEnabled())
		{
			$this->addParam('hl', 'true');

			if ($this->getHighlightFields())
			{
				$this->addParam('hl.fl', $this->getHighlightFields());
			}
			if ($this->getHighlightSnippets())
			{
				$this->addParam('hl.snippets', $this->getHighlightSnippets());
			}
			if ($this->getHighlightSize())
			{
				$this->addParam('hl.fragsize', $this->getHighlightSize());
			}
			if ($this->getHighlightTag())
			{
				$this->addParam('hl.simple.pre', '<' . $this->getHighlightTag() . '>');
				$this->addParam('hl.simple.post', '</' . $this->getHighlightTag() . '>');
			}
		}

		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));
		if (!$solr->isConnected())
		{
			return false;
		}
		else
		{
			if ($this->isSuggestionEnabled())
			{
				$solr->enableSuggestion();
			}
			$this->addParam('qt', 'standard'); // typ wyszukiwania
			$hits = $solr->search($this->getQueryString(), $this->getStartPage(), $this->getLimit(), $this->getParams());

			$this->totalTime = $hits->getTotalTime();
			$this->totalRows = $hits->getFoundRows();
			$this->suggestion = $hits->getSuggestion();

			$this->facets = $hits->getFacets();

			$this->params = array(); // czyszczenie parametrow
			return $hits;
		}
	}

	public function addDocuments(array $pageIds)
	{
		Page::setOmmitDelete(true);
		Page::setOmmitUnpublished(true);
		Page::setEnableRedirect(false);
		Page::setEnable404(false);

		$documents = array();

		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));

		foreach ($pageIds as $pageId)
		{
			try
			{
				$page = Page::load((int) $pageId);
			}
			catch (Exception $e)
			{
				Log::add($e->getMessage(), E_ERROR);
				continue;
			}

			if ($page !== false)
			{
				$documents[] = $page->getDocument();

				if (count($documents) == 10)
				{
					$solr->addDocuments($documents);
					$solr->commit();

					$documents = array();
				}
			}
			else
			{
				$solr->deleteById($pageId);
				$solr->commit();
			}

			unset($page);
		}

		if (count($documents) > 0)
		{
			$solr->addDocuments($documents);
			$solr->commit();

			unset($documents);
		}

		$solr->optimize();
		unset($solr);
	}

	public function addDocument($pageId)
	{
		Page::setOmmitDelete(true);
		Page::setOmmitUnpublished(true);
		Page::setEnableRedirect(false);
		Page::setEnable404(false);

		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));

		$page = Page::load((int) $pageId);
		if ($page !== false)
		{
			$solr->addDocument($page->getDocument());
			unset($document);
		}
		else
		{
			$solr->deleteById($pageId);
		}

		$solr->commit();
		$solr->optimize();
		unset($solr);

		unset($page);
	}

	public function delete($pageId)
	{
		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));
		$solr->deleteById($pageId);

		unset($solr);
	}

	public function deleteAll()
	{
		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));
		$solr->deleteByQuery('*:*');
		$solr->commit();
	}

	public function optimize()
	{
		$solr = new Solr(Config::getItem('solr.host'), Config::getItem('solr.port'), Config::getItem('solr.path'));
		$solr->optimize();
	}
}
?>