<?php

namespace src;

/**
 * Generate a Pagination (with Bootstrap).
 *
 * @version 1.0.0
 * @link https://github.com/Zheness/Pagination/ Github Repo
 * @author Zheness / Tony
 */

class Pagination{

    private $nbMaxElements;
    private $nbElementsInPage;
    private $currentPage;
    private $url;
    private $innerLinks;
    private $linksSeparator;
    private $firstElement;
    private $nbPages;
    private $htmlRender;

    /**
     * Pagination constructor.
     * @author Tony
     */
    public function __construct()
    {
        $this->nbMaxElements = 200;
        $this->nbElementsInPage = 20;
        $this->currentPage = 1;
        $this->url = '?page={i}';
        $this->innerLinks = 2;
        $this->linksSeparator = '...';
        $this->firstElement = 0;
        $this->nbPages = 0;
        $this->htmlRender = "";
    }

    /**
     * Set the maximum elements in your database. (Or other way)
     *
     * Example :
     *
     * I have 200 articles in my database, so I type :
     *
     * $Pagination->setNbMaxElements(200);
     *
     * @param int $int The maximum elements
     * @author Zheness
     */
    public function setNbMaxElements($int) {
        $this->nbMaxElements = (int) $int;
        return $this;
    }

    /**
     * @return int
     * @author Tony
     */
    public function getNbMaxElements(): int{
        return $this->nbMaxElements;
    }

    /**
     * Set the number of elements to display in the page.
     *
     * Example :
     *
     * I would display 20 articles per pages, so I type :
     *
     * $Pagination->setNbElementsInPage(20);
     *
     * @param int $int The number of elements
     * @author Zheness
     */
    public function setNbElementsInPage($int) {
        $this->nbElementsInPage = (int) $int;
        return $this;
    }

    /**
     * @return int
     * @author Tony
     */
    public function getNbElementsInPage(): int{
        return $this->nbElementsInPage;
    }

    /**
     * Set the current page
     *
     * Example :
     *
     * The current page is the 5, so I type :
     *
     * $Pagination->setCurrentPage(5);
     *
     * @param int $int The current page
     * @author Zheness
     */
    public function setCurrentPage($int) {
        $this->currentPage = (int) $int;
        return $this;
    }

    /**
     * @return int
     * @author Tony
     */
    public function getCurrentPage(): int {
        return $this->currentPage;
    }

    /**
     * Set the url in the links. You MUST include "{i}" where you want display the number of the page.
     *
     * Example :
     *
     * I would display my articles on "articles.php?page=X" where X is the number of page. So I type :
     *
     * $Pagination->setUrl("articles.php?page={i}");
     *
     * Why {i} ? Because the number of page can be placed everywhere. If you have your url like this "articles/month/08/page/X/sort/date-desc", you can place {i} instead of X.
     *
     * @param string $string The url in the link
     * @author Zheness
     */
    public function setUrl($string) {
        $this->url = $string;
        return $this;
    }

    /**
     * @return string
     * @author Tony
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set the number of links before and after the current page
     *
     * Example :
     *
     * The current page is the 5, and I want 3 links after and before, so I type :
     *
     * $Pagination->setInnerLinks(3);
     *
     * @param int $int The number of links before and after the current links
     * @author Zheness
     */
    public function setInnerLinks($int) {
        $this->innerLinks = (int) $int;
        return $this;
    }

    /**
     * @return int
     * @author Tony
     */
    public function getInnerLinks(): int {
        return $this->innerLinks;
    }

    /**
     * Set the separtor between links.
     *
     * Example :
     *
     * I would display " - " between my links, so I type :
     *
     * $Pagination->setLinksSeparator(" - ");
     *
     * By default "..." is display.
     *
     * @param string $string The url in the link
     * @author Zheness
     */
    public function setLinksSeparator($string) {
        $this->linksSeparator = $string;
        return $this;
    }

    /**
     * @return string
     * @author Tony
     */
    public function getLinksSeparator(): string {
        return $this->linksSeparator;
    }

    /**
     * Initialise le premier élément d'une page
     * @return $this
     * @author Tony
     */
    private function setFirstElement(){
        $this->firstElement = ($this->getCurrentPage() * $this->getNbElementsInPage()) - $this->getNbElementsInPage();
        return $this;
    }

    /**
     * Retourne le premier élément d'une page (sql limit parametre 1)
     * @return int
     * @author Tony
     */
    public function getFirstElement(): int {
        return $this->firstElement;
    }

    /**
     * Initialise le nombre de pages en fonction du nombre d'élements à afficher par page et du nombre total d'éléments
     * @param int $nbPages
     * @return $this
     * @author Tony
     */
    private function setNbPages(int $nbPages){
        $this->nbPages = $nbPages;
        return $this;
    }

    /**
     * @return int
     * @author Tony
     */
    public function getNbPages(): int{
        return $this->nbPages;
    }

    /**
     * Initialise la vue html pagination
     * @param string $html
     * @return $this
     * @author Tony
     */
    private function setHtmlRender(string $html){
        $this->htmlRender = $html;
        return $this;
    }

    /**
     * Retourne la vue html pagination
     * @return string
     * @author Tony
     */
    public function getHtmlRender(): string{
        return $this->htmlRender;
    }

    /**
     * This is the function to call for render the Pagination.
     *
     * You have just to configure the options and call this function.
     *
     * @return  The HTML Pagination (it use Bootstrap)
     * @author Zheness
     */
    public function renderBootstrapPagination() {
        $array_pagination = $this->generateArrayPagination();
        $this->setHtmlRender($this->generateHtmlPagination($array_pagination));
        return $this;
    }

    /**
     * Generate the Pagination in array.
     *
     * @return array Each value is the link to display.
     * @author Zheness
     */
    private function generateArrayPagination() {
        $array_pagination = array();
        $keyArray = 0;

        $subLinks = $this->currentPage - $this->innerLinks;
        $nbLastLink = ceil($this->nbMaxElements / $this->nbElementsInPage);
        $this->setNbPages($nbLastLink);
        $this->setFirstElement();

        if ($this->currentPage > 1) {
            $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', 1, $this->url) . '">1</a>';
        }
        if ($subLinks > 2) {
            $array_pagination[$keyArray++] = $this->linksSeparator;
        }
        for ($i = $subLinks; $i < $this->currentPage; $i++) {
            if ($i >= 2) {
                $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', $i, $this->url) . '">' . $i . '</a>';
            }
        }

        if ($this->nbElementsInPage != $this->nbMaxElements){
            $array_pagination[$keyArray++] = '<b>' . $this->currentPage . '</b>';
        }

        for ($i = ($this->currentPage + 1); $i <= ($this->currentPage + $this->innerLinks); $i++) {
            if ($i < $nbLastLink) {
                $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', $i, $this->url) . '">' . $i . '</a>';
            }
        }
        if (($this->currentPage + $this->innerLinks) < ($nbLastLink - 1)) {
            $array_pagination[$keyArray++] = $this->linksSeparator;
        }
        if ($this->currentPage != $nbLastLink) {
            $array_pagination[$keyArray++] = '<a class="page-link" href="' . str_replace('{i}', $nbLastLink,
                    $this->url) . '">' . $nbLastLink . '</a>';
        }

        return $array_pagination;
    }

    /**
     * Generate the HTML pagination with the array in parameter
     *
     * @param array $array_pagination The array generate with previous function.
     * @return string Pagination in HTML. Use Bootstrap
     * @author Zheness
     */
    private function generateHtmlPagination($array_pagination) {
        $html = "";
        $html .= '<div>';
       // $html .= '<ul class="pagination">';
        if ($this->nbMaxElements && $this->nbMaxElements > $this->nbElementsInPage) {
            foreach ($array_pagination as $v) {
                if ($v == $this->linksSeparator) {
                    $html .=/* '<li class="page-item none disabled">*/ '<span class="left pagination ">' .$this->linksSeparator . '</span>'/*<'/li>&nbsp;'*/;
                } else if (preg_match("/<b>(.*)<\/b>/i", $v)) {
                    $html .= /*'<li class="page-item none active">*/'<span class="left pagination ">' .strip_tags($v) . '</span>'/*</li>&nbsp;'*/;
                } else {
                    $html .= /*'<li class="page-item none ">'*/ "". $v .""/*. '</li>&nbsp;'*/;
                }
            }
        }
    //    $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }
}
