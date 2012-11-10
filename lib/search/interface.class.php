<?php
/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

interface Search_Interface
{
	public function setQueryString($queryString);
	public function getQueryString();
	public function addField($field, $boost = 1.0);
	public function getFields();
	public function setSort($sort);
	public function getSort();
	public function setTimeLimit($timeLimit);
	public function getTimeLimit();
	public function addGroup($groupId);
	public function setGroups(array $groups);
	public function getGroups();
	public function addParam($name, $value);
	public function setParams(array $params = array());
	public function getParam($key);
	public function addFilter($filter, $value);
	public function setFilters(array $filters = array());
	public function getFilters();
	public function getFilter($filter);
	public function removeFilter($filter);
	public function removeFilters();
	public function getParams();
	public function getTotalRows();
	public function getTotalTime();
	public function setStartPage($start);
	public function getStartPage();
	public function setLimit($limit);
	public function getLimit();
	public function setEnableSuggestion($flag);
	public function isSuggestionEnabled();
	public function getSuggestion();
	public function setEnableFacet($flag);
	public function isFacetEnabled();
	public function setFacetField($field);
	public function getFacetField();
	public function getFacets();
	public function setEnableHighlight($flag);
	public function isHighlightEnabled();
	public function setHighlightFields($fields);
	public function getHighlightFields();
	public function setHighlightSize($size);
	public function getHighlightSize();
	public function setHighlightSnippets($snippets);
	public function getHighlightSnippets();
	public function setHighlightTag($tag);
	public function getHighlightTag();
	public function find($queryString = '', $start = null, $limit = null);
	public function addDocuments(array $pageIds);
	public function addDocument($pageId);
	public function delete($pageId);
	public function deleteAll();
}
?>