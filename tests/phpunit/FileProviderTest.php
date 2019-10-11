<?php

namespace ProofreadPage;

use File;
use ProofreadPageTestCase;
use Title;

/**
 * @group ProofreadPage
 * @covers \ProofreadPage\FileProvider
 */
class FileProviderTest extends ProofreadPageTestCase {

	private function getFileFromName( $fileName ) {
		return $this->getContext()->getFileProvider()->getFileFromTitle(
			Title::makeTitle( NS_MEDIA, $fileName )
		);
	}

	/**
	 * @dataProvider indexFileProvider
	 */
	public function testGetFileForIndexTitle(
		Title $indexTitle, File $file, FileProvider $fileProvider
	) {
		$this->assertEquals( $file, $fileProvider->getFileForIndexTitle( $indexTitle ) );
	}

	public function indexFileProvider() {
		$fileProvider = new FileProviderMock( [
			$this->getFileFromName( 'LoremIpsum.djvu' ),
			$this->getFileFromName( 'Test.jpg' )
		] );

		return [
			[
				Title::makeTitle( $this->getIndexNamespaceId(), 'LoremIpsum.djvu' ),
				$this->getFileFromName( 'LoremIpsum.djvu' ),
				$fileProvider
			],
		];
	}

	/**
	 * @dataProvider indexFileNotFoundProvider
	 */
	public function testGetFileForIndexPageWithFileNotFound(
		Title $indexTitle, FileProvider $fileProvider
	) {
		$this->expectException( \ProofreadPage\FileNotFoundException::class );
		$fileProvider->getFileForIndexTitle( $indexTitle );
	}

	public function indexFileNotFoundProvider() {
		$fileProvider = new FileProviderMock( [
			$this->getFileFromName( 'LoremIpsum.djvu' ),
			$this->getFileFromName( 'Test.jpg' )
		] );

		return [
			[
				Title::makeTitle( $this->getIndexNamespaceId(), 'LoremIpsum2.djvu' ),
				$fileProvider
			],
			[
				Title::makeTitle( $this->getIndexNamespaceId(), 'Test' ),
				$fileProvider
			],
		];
	}

	/**
	 * @dataProvider pageFileProvider
	 */
	public function testFileGetForPageTitle(
		Title $pageTitle, File $file, FileProvider $fileProvider
	) {
		$this->assertEquals( $file, $fileProvider->getFileForPageTitle( $pageTitle ) );
	}

	public function pageFileProvider() {
		$fileProvider = new FileProviderMock( [
			$this->getFileFromName( 'LoremIpsum.djvu' ),
			$this->getFileFromName( 'Test.jpg' )
		] );

		return [
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'LoremIpsum.djvu/4' ),
				$this->getFileFromName( 'LoremIpsum.djvu' ),
				$fileProvider
			],
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'LoremIpsum.djvu/djvu/1' ),
				$this->getFileFromName( 'LoremIpsum.djvu' ),
				$fileProvider
			],
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'LoremIpsum.djvu' ),
				$this->getFileFromName( 'LoremIpsum.djvu' ),
				$fileProvider
			],
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'Test.jpg' ),
				$this->getFileFromName( 'Test.jpg' ),
				$fileProvider
			],
		];
	}

	/**
	 * @dataProvider pageFileNotFoundProvider
	 */
	public function testGetForPagePageWithFileNotFound(
		Title $pageTitle, FileProvider $fileProvider
	) {
		$this->expectException( \ProofreadPage\FileNotFoundException::class );
		$fileProvider->getFileForPageTitle( $pageTitle );
	}

	public function pageFileNotFoundProvider() {
		$fileProvider = new FileProviderMock( [
			$this->getFileFromName( 'LoremIpsum.djvu' ),
			$this->getFileFromName( 'Test.jpg' )
		] );

		return [
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'LoremIpsum2.djvu/4' ),
				$fileProvider
			],
			[
				Title::makeTitle( $this->getPageNamespaceId(), 'Test' ),
				$fileProvider
			],
		];
	}

	public function testGetPageNumberForPageTitle() {
		$fileProvider = new FileProviderMock( [] );
		$this->assertEquals( 1, $fileProvider->getPageNumberForPageTitle(
			Title::makeTitle( $this->getPageNamespaceId(), 'Test.djvu/1' )
		) );
	}

	public function testGetPageNumberForPageNumberNotFound() {
		$fileProvider = new FileProviderMock( [] );
		$this->expectException( \ProofreadPage\PageNumberNotFoundException::class );
		$fileProvider->getPageNumberForPageTitle(
			Title::makeTitle( $this->getPageNamespaceId(), 'Test.djvu' )
		);
	}

	public function testGetPageNumberForPageNotANumber() {
		$fileProvider = new FileProviderMock( [] );
		$this->expectException( \ProofreadPage\PageNumberNotFoundException::class );
		$fileProvider->getPageNumberForPageTitle(
			Title::makeTitle( $this->getPageNamespaceId(), 'Test.djvu/foo' )
		);
	}

	public function testGetPageNumberForPageBadNumber() {
		$fileProvider = new FileProviderMock( [] );
		$this->expectException( \ProofreadPage\PageNumberNotFoundException::class );
		$fileProvider->getPageNumberForPageTitle(
			Title::makeTitle( $this->getPageNamespaceId(), 'Test.djvu/-1' )
		);
	}
}
