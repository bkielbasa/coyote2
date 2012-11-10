<?php

interface Connector_Interface
{
	function __construct($data = array());
	/**
	 * Zwraca ID strony
	 */
	public function getId();
	public function setSubject($subject);
	/**
	 * Zwraca tytul strony
	 */
	public function getSubject();
	public function setTitle($title);
	/**
	 * Zwraca rozszerzony tytul strony
	 */
	public function getTitle();
	public function setPath($path);
	/**
	 * Zwraca sciezke do dokumentu.
	 * Nie jest to pelna sciezka, jedynie string identyfikujacy strone - np.
	 * Foo_Bar
	 */
	public function getPath();	
	/**
	 * Zwraca pelna sciezke identyfikujaca strone - np. Foo/Bar/1/2/3
	 * Jest to unikalna wartosc
	 */
	public function getLocation();
	public function setParentId($parentId);
	/**
	 * Zwraca ID strony-rodzica
	 */
	public function getParentId();
	/**
	 * Kazda strona ma przydzielony ID konektora
	 */
	public function getConnectorId();
	/**
	 * ID modulu 
	 */
	public function getModuleId();
	/**
	 * Zwraca nazwe kontrolera
	 */
	public function getController();
	/**
	 * Zwraca nazwe akcji
	 */
	public function getAction();
	/**
	 * Zwraca nazwe folderu, w ktorym zlokalizowany jest kontroler
	 */
	public function getFolder();
	public function getConnectorName();
	
	public function setContent($content);
	public function getContent($parseContent = true);
	public function setLog($log);
	public function getLog();
	public function getUserId();
	public function setContentType($contentType);
	public function getContentType();
	
	public function setIsPublished($flag);
	public function isPublished();
	public function setPublishDate($publishDate);
	public function getPublishDate();
	public function setUnpublishDate($unpublishDate);
	public function getUnpublishDate();
	public function setIsCached($flag);
	public function isCached();
	public function setRichTextId($richTextId);
	public function getRichTextId();
	public function setIsDeleted($flag);
	public function isDeleted();
	public function setIp($ip);
	public function getIp();
	public function getChildren();
	public function getDepth();
	public function getTime();
	public function getEditTime();
	public function getCache();
	public function getCacheTime();
	public function setTemplate($template);
	public function getTemplate();
	public function setMetaTitle($metaTitle);
	public function getMetaTitle();
	public function setMetaDescription($metaDescription);
	public function getMetaDescription();
	public function setMetaKeywords($metaKeywords);
	public function getMetaKeywords();
	
	public function renderForm();
	public function delete();
	public function move($parentId);
	
	
}
?>