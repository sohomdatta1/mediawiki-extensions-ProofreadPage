<?php

namespace ProofreadPage\Page;

use MediaWiki\Revision\RevisionRecord;
use ProofreadPage\Context;
use ViewAction;

/**
 * @license GPL-2.0-or-later
 *
 * ViewAction for a Page: page
 */
class PageViewAction extends ViewAction {

	/**
	 * @see FormlessAction::show()
	 */
	public function show() {
		$out = $this->getOutput();
		$title = $this->getTitle();

		if ( !$title->inNamespace( Context::getDefaultContext()->getPageNamespaceId() ) ||
			$out->isPrintable() || $this->getContext()->getRequest()->getCheck( 'diff' )
		) {
			$this->getArticle()->view();

			return;
		}

		$wikiPage = $this->getWikiPage();
		$content = $wikiPage->getContent( RevisionRecord::FOR_THIS_USER, $this->getUser() );
		if ( $content === null || $content->getModel() !== CONTENT_MODEL_PROOFREAD_PAGE ||
			$content->isRedirect()
		) {
			$this->getArticle()->view();

			return;
		}
		$pageDisplayHandler = new PageDisplayHandler( Context::getDefaultContext() );

		// render HTML
		$out->addHTML( $pageDisplayHandler->buildPageContainerBegin() );
		$this->getArticle()->view();
		$out->addHTML( $pageDisplayHandler->buildPageContainerEnd( $title ) );

		// add modules
		$out->addModules( 'ext.proofreadpage.ve.pageTarget.init' );
		$out->addModuleStyles( [ 'ext.proofreadpage.base', 'ext.proofreadpage.page' ] );
		$out->addJsConfigVars( [
			// @phan-suppress-next-line PhanUndeclaredMethod Content doesn't have getLevel
			'prpPageQuality' => $content->getLevel()->getLevel()
		] );

		// custom CSS
		$css = $pageDisplayHandler->getCustomCss( $title );
		if ( $css !== '' ) {
			$out->addInlineStyle( $css );
		}
	}
}
