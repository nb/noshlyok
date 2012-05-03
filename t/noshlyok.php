<?php

class WP_Test_Noshlyok extends WP_UnitTestCase {

	var $plugin_slug = 'noshlyok';

	function setUp() {
		parent::setUp();
		$this->n = $GLOBALS['noshlyok'];
	}

	function test_is_noshlyok() {
		$this->assertEquals( 'Noshlyok', get_class( $this->n ) );
	}

	function test_has_bulgarian_letters_should_include_lower_a() {
		$this->assertTrue( $this->n->has_bulgarian_letters( 'а' ) );
	}

	function test_has_bulgarian_letters_should_include_lower_ya() {
		$this->assertTrue( $this->n->has_bulgarian_letters( 'я' ) );
	}

	function test_has_bulgarian_letters_should_include_capital_a() {
		$this->assertTrue( $this->n->has_bulgarian_letters( 'А' ) );
	}

	function test_has_bulgarian_letters_should_include_capital_ya() {
		$this->assertTrue( $this->n->has_bulgarian_letters( 'Я' ) );
	}

	function test_is_russian_email_should_return_false_if_no_at() {
		$this->assertFalse( $this->n->is_russian_email( 'baba') );
	}

	function test_is_russian_email_should_return_true_if_host_ends_with_ru() {
		$this->assertTrue( $this->n->is_russian_email( 'baba@mail.ru' ) );
	}

	function test_is_russian_url_should_return_true_if_the_tld_is_ru() {
		$this->assertTrue( $this->n->is_russian_url( 'http://dir.ru/news/latest?x=y&nomama=nobaba' ) );
	}

	function test_is_russian_should_not_catch_yo() {
		$this->assertFalse( $this->n->is_russian_text( 'ьо, мен'));
	}

}
