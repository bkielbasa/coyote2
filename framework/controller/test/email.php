<?php
/**
 * @package Coyote-F
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) Adam Boduch (Coyote Group)
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class Email_Controller extends Controller
{
	public function main()
	{
		$test = &$this->getLibrary('unit_test');

		$test->assertEqual(Text::transformEmail('foo@bar.com'), '<a href="mailto:foo@bar.com">foo@bar.com</a>');
		$test->assertEqual(Text::transformEmail('[foo@bar.com]'), '[<a href="mailto:foo@bar.com">foo@bar.com</a>]');
		$test->assertEqual(Text::transformEmail('(foo@bar.com)'), '(<a href="mailto:foo@bar.com">foo@bar.com</a>)');
		$test->assertEqual(Text::transformEmail('Foo Bar &lt;foo@bar.com&gt;'), 'Foo Bar &lt;<a href="mailto:foo@bar.com">foo@bar.com</a>&gt;');
		$test->assertEqual(Text::transformEmail('Foo @bar'), 'Foo @bar');
		$test->assertEqual(Text::transformEmail('Foo @@bar'), 'Foo @@bar');
		$test->assertEqual(Text::transformEmail('Foo@@bar'), 'Foo@@bar');
		$test->assertEqual(Text::transformEmail('Foo@@bar.com'), 'Foo@@bar.com');
		$test->assertEqual(Text::transformEmail('Foo@_bar.com'), 'Foo@_bar.com');
		$test->assertEqual(Text::transformEmail('mailto:foo@bar.com'), 'mailto:<a href="mailto:foo@bar.com">foo@bar.com</a>');
		$test->assertEqual(Text::transformEmail('foo@bar@foobar.com'), 'foo@bar@foobar.com');
		$test->assertEqual(Text::transformEmail('foo..bar@foobar.com'), 'foo..bar@foobar.com');
		$test->assertEqual(Text::transformEmail('foo(bar)@foobar.com'), 'foo(bar)@foobar.com');
		$test->assertEqual(Text::transformEmail('Lorem ipsum:foo@bar.com'), 'Lorem ipsum:<a href="mailto:foo@bar.com">foo@bar.com</a>');
		$test->assertEqual(Text::transformEmail('<strong>foo@bar.com</strong>'), '<strong><a href="mailto:foo@bar.com">foo@bar.com</a></strong>');
		$test->assertEqual(Text::transformEmail('<foo@bar.com>'), '<<a href="mailto:foo@bar.com">foo@bar.com</a>>');
		$test->assertEqual(Text::transformEmail('-->foo@bar.com<--'), '--><a href="mailto:foo@bar.com">foo@bar.com</a><--');

		echo $test->report();
	}
}
?>