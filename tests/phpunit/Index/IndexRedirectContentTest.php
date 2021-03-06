<?php

namespace ProofreadPage\Index;

use MediaWiki\MediaWikiServices;
use ParserOptions;
use ProofreadPageTestCase;
use RequestContext;
use Title;
use User;

/**
 * @group ProofreadPage
 * @covers \ProofreadPage\Index\IndexRedirectContent
 */
class IndexRedirectContentTest extends ProofreadPageTestCase {

	/**
	 * @var RequestContext
	 */
	private $requestContext;

	protected function setUp() : void {
		parent::setUp();

		$this->requestContext = new RequestContext();
		$this->requestContext->setTitle( Title::makeTitle( $this->getIndexNamespaceId(), 'Test.pdf' ) );
		$this->requestContext->setUser( new User() );
	}

	public function testGetModel() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame( CONTENT_MODEL_PROOFREAD_INDEX, $content->getModel() );
	}

	public function testGetContentHandler() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame(
			CONTENT_MODEL_PROOFREAD_INDEX, $content->getContentHandler()->getModelID()
		);
	}

	public function testCopy() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame( $content, $content->copy() );
	}

	public function equalsProvider() {
		return [
			[
				new IndexRedirectContent( Title::newFromText( 'Foo' ) ),
				new IndexRedirectContent( Title::newFromText( 'Foo' ) ),
				true
			],
			[
				new IndexRedirectContent( Title::newFromText( 'Foo' ) ),
				new IndexRedirectContent( Title::newFromText( 'Bar' ) ),
				false
			],
			[
				new IndexRedirectContent( Title::newFromText( 'Foo' ) ),
				new IndexContent( [] ),
				false
			],
		];
	}

	/**
	 * @dataProvider equalsProvider
	 */
	public function testEquals( IndexRedirectContent $a, $b, $equal ) {
		$this->assertSame( $equal, $a->equals( $b ) );
	}

	public function testGetWikitextForTransclusion() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame( '#REDIRECT [[Foo]]', $content->getWikitextForTransclusion() );
	}

	public function testGetTextForSummary() {
		$this->assertSame(
			'', ( new IndexRedirectContent( Title::newFromText( 'Foo' ) ) )->getTextForSummary( 16 )
		);
	}

	public function testPreSaveTransform() {
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();

		$options = ParserOptions::newFromUserAndLang(
			$this->requestContext->getUser(), $contLang
		);
		$originalContent = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$content = $originalContent->preSaveTransform(
			$this->requestContext->getTitle(), $this->requestContext->getUser(), $options
		);

		$this->assertTrue( $content->equals( $originalContent ) );
	}

	public function testPreloadTransform() {
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();

		$options = ParserOptions::newFromUserAndLang(
			$this->requestContext->getUser(), $contLang
		);
		$originalContent = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$content = $originalContent->preloadTransform(
			$this->requestContext->getTitle(), $options
		);

		$this->assertSame( $originalContent, $content );
	}

	public function testGetSize() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame( 3, $content->getSize() );
	}

	public function testGetRedirectTarget() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertSame( Title::newFromText( 'Foo' ), $content->getRedirectTarget() );
	}

	public function testUpdateRedirect() {
		$content = new IndexRedirectContent( Title::newFromText( 'Foo' ) );
		$this->assertEquals(
			new IndexRedirectContent( Title::newFromText( 'Bar' ) ),
			$content->updateRedirect( Title::newFromText( 'Bar' ) )
		);
	}
}
