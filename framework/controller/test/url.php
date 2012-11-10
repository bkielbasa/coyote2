<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Url_Controller extends Controller
{
	public function main()
	{
		$test = &$this->getLibrary('unit_test');

		$test->assertEqual(Text::transformUrl('http://wymyslonyadres.com/Sacré-Coeur'), '<a href="http://wymyslonyadres.com/Sacré-Coeur">http://wymyslonyadres.com/Sacré-Coeur</a>');
		$test->assertEqual(Text::transformUrl('"http://patrz.na.to"'), '"<a href="http://patrz.na.to">http://patrz.na.to</a>"');
		$test->assertEqual(Text::transformUrl('\'http://wp.pl\''), '\'<a href="http://wp.pl">http://wp.pl</a>\'');
		$test->assertEqual(Text::transformUrl('http://foo.com/bar(v=11).aspx'), '<a href="http://foo.com/bar(v=11).aspx">http://foo.com/bar(v=11).aspx</a>');
		$test->assertEqual(Text::transformUrl('(http://google.pl)'), '(<a href="http://google.pl">http://google.pl</a>)');
		$test->assertEqual(Text::transformUrl('http://msdn.microsoft.com/en-us/library/ms648049(v=vs.85).aspx'), '<a href="http://msdn.microsoft.com/en-us/library/ms648049(v=vs.85).aspx">http://msdn.microsoft.com/en-us/library/ms648049(v=vs.85).aspx</a>');
		$test->assertEqual(Text::transformUrl('http://wp.pl<'), '<a href="http://wp.pl">http://wp.pl</a><');
		$test->assertEqual(Text::transformUrl('http://wikipedia.org/wiki/Matymatyka_(nauka)'), '<a href="http://wikipedia.org/wiki/Matymatyka_(nauka)">http://wikipedia.org/wiki/Matymatyka_(nauka)</a>');
		$test->assertEqual(Text::transformUrl('(http://wikipedia.org/wiki/Matymatyka_(nauka))'), '(<a href="http://wikipedia.org/wiki/Matymatyka_(nauka)">http://wikipedia.org/wiki/Matymatyka_(nauka)</a>)');
		$test->assertEqual(Text::transformUrl('www.onet.pl?'), '<a href="http://www.onet.pl">www.onet.pl</a>?');
		$test->assertEqual(Text::transformUrl('http://pl.wikipedia.org/wiki/Wikipedia:Poczekalnia/artykuły/2011:03:05:4programmers.net'), '<a href="http://pl.wikipedia.org/wiki/Wikipedia:Poczekalnia/artykuły/2011:03:05:4programmers.net">http://pl.wikipedia.org/wiki/Wikipedia:Poczekalnia/artykuły/2011:03:05:4programmers.net</a>');
		$test->assertEqual(Text::transformUrl('<img src="http://up.programosy.pl/foto/ss_37.jpg" alt="user image"/>'), '<img src="http://up.programosy.pl/foto/ss_37.jpg" alt="user image"/>');
		$test->assertEqual(Text::transformUrl('http://www.<tt>microsoft.pl</tt>'), 'http://www.<tt>microsoft.pl</tt>');
		$test->assertEqual(Text::transformUrl('<a href="http://wp.pl/sport">http://wp.pl/muzyka</a>'), '<a href="http://wp.pl/sport">http://wp.pl/muzyka</a>');
		$test->assertEqual(Text::transformUrl('www.przyjaciółka.com/ószy'), '<a href="http://www.przyjaciółka.com/ószy">www.przyjaciółka.com/ószy</a>');
		$test->assertEqual(Text::transformUrl('www.google.pl?host=www.wp.pl'), '<a href="http://www.google.pl?host=www.wp.pl">www.google.pl?host=www.wp.pl</a>');
		$test->assertEqual(Text::transformUrl('www.kóórka.pl?x=u&y=1&par1=ółóąśćłóżźłć'), '<a href="http://www.kóórka.pl?x=u&amp;y=1&amp;par1=ółóąśćłóżźłć">www.kóórka.pl?x=u&y=1&par1=ółóąśćłóżźłć</a>');
		$test->assertEqual(Text::transformUrl('http://translate.google.com/#pl|en|http://blog.4zal.net'), '<a href="http://translate.google.com/#pl|en|http://blog.4zal.net">http://translate.google.com/#pl|en|http://blog.4zal.net</a>');
		$test->assertEqual(Text::transformUrl('<a href="http://translate.google.com/#pl|en|http://blog.4zal.net">Test</a>'), '<a href="http://translate.google.com/#pl|en|http://blog.4zal.net">Test</a>');
		$test->assertEqual(Text::transformUrl('http://webcache.googleusercontent.com/search?q=cache:w1eA8ne2Xp4J:forum.4programmers.net/Moderatorzy/Kapownik/174021-a_deus_ssie+Moderatorzy+site:forum.4programmers.net&cd=2&hl=pl&ct=clnk&gl=pl&source=www.google.pl'), '<a href="http://webcache.googleusercontent.com/search?q=cache:w1eA8ne2Xp4J:forum.4programmers.net/Moderatorzy/Kapownik/174021-a_deus_ssie+Moderatorzy+site:forum.4programmers.net&amp;cd=2&amp;hl=pl&amp;ct=clnk&amp;gl=pl&amp;source=www.google.pl">http://webcache.googleusercontent.com/search?q=cache:w1eA8ne2Xp4J:forum.4programmers.net/Moderatorzy/Kapownik/174021-a_deus_ssie+Moderatorzy+site:forum.4programmers.net&cd=2&hl=pl&ct=clnk&gl=pl&source=www.google.pl</a>');
		$test->assertEqual(Text::transformUrl('http://webcache.googleusercontent.com/search?q=cache:1LM0nT1jhV0J:www.worldofasp.net/tut/ShoppingCart/Building_Simple_Shopping_Cart_using_ASPNET_and_Cookies_129.aspx+build+a+shopping+cart+example+asp.net&cd=4&hl=pl&ct=clnk&gl=pl&client=firefox-a&source=www.google.pl'), '<a href="http://webcache.googleusercontent.com/search?q=cache:1LM0nT1jhV0J:www.worldofasp.net/tut/ShoppingCart/Building_Simple_Shopping_Cart_using_ASPNET_and_Cookies_129.aspx+build+a+shopping+cart+example+asp.net&amp;cd=4&amp;hl=pl&amp;ct=clnk&amp;gl=pl&amp;client=firefox-a&amp;source=www.google.pl">http://webcache.googleusercontent.com/search?q=cache:1LM0nT1jhV0J:www.worldofasp.net/tut/ShoppingCart/Building_Simple_Shopping_Cart_using_ASPNET_and_Cookies_129.aspx+build+a+shopping+cart+example+asp.net&cd=4&hl=pl&ct=clnk&gl=pl&client=firefox-a&source=www.google.pl</a>', '#370');
		$test->assertEqual(Text::transformUrl('http://homepage.mac.com/randyhyde/webster.cs.ucr.edu/www.artofasm.com/Windows/index.htm'), '<a href="http://homepage.mac.com/randyhyde/webster.cs.ucr.edu/www.artofasm.com/Windows/index.htm">http://homepage.mac.com/randyhyde/webster.cs.ucr.edu/www.artofasm.com/Windows/index.htm</a>', '#367');
		$test->assertEqual(Text::transformUrl('http://google.pl/www.wp.pl'), '<a href="http://google.pl/www.wp.pl">http://google.pl/www.wp.pl</a>');
		$test->assertEqual(Text::transformUrl('http://google.pl?host=www.google.pl'), '<a href="http://google.pl?host=www.google.pl">http://google.pl?host=www.google.pl</a>');
		$test->assertEqual(Text::transformUrl('http://google.pl?www.google.pl'), '<a href="http://google.pl?www.google.pl">http://google.pl?www.google.pl</a>', 'http://google.pl?www.google.pl');
		$test->assertEqual(Text::transformUrl('?www.google.pl'), '?<a href="http://www.google.pl">www.google.pl</a>');
		$test->assertEqual(Text::transformUrl('www.google.pl">'), '<a href="http://www.google.pl">www.google.pl</a>">');
		$test->assertEqual(Text::transformUrl('>www.google.pl'), '><a href="http://www.google.pl">www.google.pl</a>');
		$test->assertEqual(Text::transformUrl('.www.google.pl'), '.<a href="http://www.google.pl">www.google.pl</a>');
		$test->assertEqual(Text::transformUrl('aaaawwww.google.pl'), 'aaaawwww.google.pl');

		echo $test->report();
	}
}
?>