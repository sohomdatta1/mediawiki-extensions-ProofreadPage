<?php

namespace ProofreadPage\Page;

use Html;
use OutOfBoundsException;
use ProofreadPage\Context;
use ProofreadPage\FileNotFoundException;
use ProofreadPage\PageNumberNotFoundException;
use ProofreadPagePage;
use Sanitizer;

/**
 * @licence GNU GPL v2+
 *
 * Utility class to do operations related to Page: page display
 */
class PageDisplayHandler {

	/**
	 * @var integer default width for scan image
	 */
	const DEFAULT_IMAGE_WIDTH = 1024;

	/**
	 * @var Context
	 */
	private $context;

	/**
	 * @param Context $context
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Return the scan image width for display
	 * @param ProofreadPagePage $page
	 * @return int
	 */
	public function getImageWidth( ProofreadPagePage $page ) {
		$index = $this->context->getIndexForPageLookup()->getIndexForPage( $page );
		if ( $index !== null ) {
			try {
				$indexContent = $this->context->getIndexContentLookup()->getIndexContent( $index );
				$width = $this->context->getCustomIndexFieldsParser()->parseCustomIndexField(
					$indexContent, 'width'
				)->getStringValue();
				if ( is_numeric( $width ) ) {
					return $width;
				}
			} catch ( OutOfBoundsException $e ) {
				return self::DEFAULT_IMAGE_WIDTH;
			}
		}
		return self::DEFAULT_IMAGE_WIDTH;
	}

	/**
	 * Return custom CSS for the page
	 * Is protected against XSS
	 * @param ProofreadPagePage $page
	 * @return string
	 */
	public function getCustomCss( ProofreadPagePage $page ) {
		$index = $this->context->getIndexForPageLookup()->getIndexForPage( $page );
		if ( $index === null ) {
			return '';
		}
		try {
			$indexContent = $this->context->getIndexContentLookup()->getIndexContent( $index );
			$css = $this->context->getCustomIndexFieldsParser()->parseCustomIndexField(
				$indexContent, 'css'
			);
			return Sanitizer::escapeHtmlAllowEntities(
				Sanitizer::checkCss( $css->getStringValue() )
			);
		} catch ( OutOfBoundsException $e ) {
			return '';
		}
	}

	/**
	 * Return the part of the page container that is before page content
	 * @return string
	 */
	public function buildPageContainerBegin() {
		return
			Html::openElement( 'div', [ 'class' => 'prp-page-container' ] ) .
			Html::openElement( 'div', [ 'class' => 'prp-page-content' ] );
	}

	/**
	 * Return the part of the page container that after page cnotent
	 * @return string
	 */
	public function buildPageContainerEnd( ProofreadPagePage $page ) {
		return
			Html::closeElement( 'div' ) .
			Html::openElement( 'div', [ 'class' => 'prp-page-image' ] ) .
			$this->buildImageHtml( $page, [ 'max-width' => $this->getImageWidth( $page ) ] ) .
			Html::closeElement( 'div' ) .
			Html::closeElement( 'div' );
	}

	/**
	 * Return HTML for the image
	 * @param ProofreadPagePage $page
	 * @param array $options
	 * @return null|string
	 */
	private function buildImageHtml( ProofreadPagePage $page, $options ) {
		$fileProvider = $this->context->getFileProvider();
		try {
			$image = $fileProvider->getForPagePage( $page );
		} catch ( FileNotFoundException $e ) {
			return null;
		}
		if ( !$image->exists() ) {
			return null;
		}
		$width = $image->getWidth();
		if ( isset( $options['max-width'] ) && $width > $options['max-width'] ) {
			$width = $options['max-width'];
		}
		$transformAttributes = [
			'width' => $width
		];

		if ( $image->isMultipage() ) {
			try {
				$transformAttributes['page'] = $fileProvider->getPageNumberForPagePage( $page );
			} catch ( PageNumberNotFoundException $e ) {
			}
		}
		$handler = $image->getHandler();
		if ( !$handler || !$handler->normaliseParams( $image, $transformAttributes ) ) {
			return null;
		}
		$thumbnail = $image->transform( $transformAttributes );
		if ( !$thumbnail ) {
			return null;
		}
		return $thumbnail->toHtml( $options );
	}
}
