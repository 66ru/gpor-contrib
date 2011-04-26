<?php
/**
 * LinkPager class file.
 *
 * @author shmakov
 */

/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * @author shmakov
 */
class LinkPager extends CLinkPager
{
	public $nextPageLabel;
	/**
	 * @var string the text label for the previous page button. Defaults to '&lt; Previous'.
	 */
	public $prevPageLabel;
	/**
	 * @var string the text label for the first page button. Defaults to '&lt;&lt; First'.
	 */
	public $firstPageLabel;
	/**
	 * @var string the text label for the last page button. Defaults to 'Last &gt;&gt;'.
	 */
	public $lastPageLabel;

	/**
	 * @var array HTML attributes for the pages container tag.
	 */
	public $htmlPagesOptions;

	/**
	 * @var mixed JS file used for the widget.
	 */
	public $jsFile;

	public function init()
	{
		$this->prevPageLabel = 'предыдущая';
		$this->nextPageLabel = 'следующая';
		$this->firstPageLabel = 'первая';
		$this->lastPageLabel = 'последняя';

		$this->header = 'Страницы';
		$this->cssFile = false;
		$this->jsFile = false;

		$this->htmlPagesOptions = array(
			"class" => "yiiPagerPages"
		);
		
		parent::init();
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		$pages = $this->createInternalPages();
		$buttons=$this->createPageButtons();
		if(empty($buttons))
			return;
		$this->registerClientScript();
		// Header and buttons next and previous
		echo '<div class="news_section-news-list__pagination"><noindex>';
		echo '<span class="yiiPagerHeader">' . $this->header . '</span>';
		echo CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));
		// internal pages
		echo CHtml::tag('ul', $this->htmlPagesOptions, implode("\n", $pages));
		echo $this->footer;
		echo "</noindex></div>";

		// Header and buttons next and previous
//		$header = '';
//		$header.= '<span class="yiiPagerHeader">' . $this->header . '</span>';
//		$header.= CHtml::tag('ul', $this->htmlOptions, implode("\n", $buttons));
//
//		// internal pages
//		$pages = CHtml::tag('ul', $this->htmlPagesOptions, implode("\n", $pages));
//
//        $this->render('pager', array(
//			'header'	=> $header,
//			'pages'		=> $pages
//		));


	}

	/**
	 * Registers the needed client scripts.
	 */
	public function registerClientScript()
	{
		parent::registerClientScript();
		Yii::app()->getClientScript()->registerScriptFile($this->jsFile, CClientScript::POS_END);
	}

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons()
	{
		if(($pageCount=$this->getPageCount())<=1)
			return array();

		$currentPage=$this->getCurrentPage(false); // currentPage is calculated in getPageRange()
		$lastPage = $this->getPageCount();

		$buttons=array();

//		// first page
//		$buttons[]=$this->createPageButton($this->firstPageLabel,0,self::CSS_FIRST_PAGE,$currentPage<=0,false);

		// prev page
		if ($currentPage != 0) {
			if(($page=$currentPage-1)<0) {
				$page=0;
			}
			$buttons[] = $this->createPageButton2(
				$this->prevPageLabel,
				$page,
				self::CSS_PREVIOUS_PAGE,
				$currentPage<=0,
				false,
				'<span class="ctrl left">← Ctrl </span>'
			);
		}

		// next page
		if(($page=$currentPage+1)>=$pageCount-1) {
			$page=$pageCount-1;
		}
		if ( ( $lastPage - 1 ) !=  $currentPage) {
			$buttons[]=$this->createPageButton2(
				$this->nextPageLabel,
				$page,
				self::CSS_NEXT_PAGE,
				$currentPage>=$pageCount-1,
				false,
				'',
				'<span class="ctrl right">Ctrl →</span>'
			);
		}

//		// last page
//		$buttons[]=$this->createPageButton($this->lastPageLabel,$pageCount-1,self::CSS_LAST_PAGE,$currentPage>=$pageCount-1,false);

		return $buttons;
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * @param string the text label for the button
	 * @param integer the page number
	 * @param string the CSS class for the page button. This could be 'page', 'first', 'last', 'next' or 'previous'.
	 * @param boolean whether this page button is visible
	 * @param boolean whether this page button is selected
	 * @param string text before link
	 * @param string text after link
	 * @return string the generated button
	 */
	protected function createPageButton2($label,$page,$class,$hidden,$selected, $textBefore = '', $textAfter = '')
	{
		if($hidden || $selected)
			$class.=' '.($hidden ? self::CSS_HIDDEN_PAGE : self::CSS_SELECTED_PAGE);
		return '<li class="'.$class.'">' . $textBefore . CHtml::link($label,$this->createPageUrl($page)) . $textAfter . '</li>';
	}

	/**
	 * Create internal pages.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createInternalPages()
	{
		$buttons = array();

		list($beginPage,$endPage)=$this->getPageRange();
		$currentPage=$this->getCurrentPage(false); // currentPage is calculated in getPageRange()
		$lastPage = $this->getPageCount();
		
		for ($i = $beginPage, $j = 0; $i <= $endPage; ++$i, $j++) {
			$label = $i + 1;
			if ($this->maxButtonCount == $j + 1 && $lastPage != ($i + 1) ) {
				$label = '…';
			}
			if ($j == 0 && $i != 0) {
				$label = '…';
			}
			$buttons[] = $this->createPageButton($label, $i, self::CSS_INTERNAL_PAGE, false, $i == $currentPage);
		}

		return $buttons;
	}
}
