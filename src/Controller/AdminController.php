<?php

namespace src\controller;

use src\Model\Article;
use src\Model\Category;
use src\Model\Media;
use src\Model\Topic;
use src\Model\BDD;
use src\Model\User;
use src\Pagination;

class AdminController extends AbstractController
{

    private string $PageTitle;

    public function __construct()
    {
        parent::__construct();
        // authentification role admin sur le constructeur / On test user role admin = 1
        USER::is_admin();
        $this->PageTitle = "Administration | The Holy BF";
    }
    /**
     * Accueil Administration
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $this->PageTitle = "Administration du site";
        return $this->twig->render("Admin/index.html.twig", [
            'pageTitle' => $this->PageTitle
        ]);
    }
    //********************************************************************//
    // Administration du Blog CRUD
    //********************************************************************//

    /**
     * Affiche la liste des articles du blog CRUD
     * @return string
     */
    public function listBlog($params = [])
    {
        // ON test si une recherche est passée en GET
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $q = strip_tags(trim($_GET['q']));
            $link = "?q=" . $q;
            $search = explode(" ", strip_tags(trim($_GET['q'])));
        } else {
            $q = "";
            $search = [];
        }

        $this->PageTitle = "Administration du Blog";
        $articles = new Article();

        // controle session success / error
        if (isset($_SESSION['SUCCESS'])) {
            $success = $_SESSION['SUCCESS'];
            unset($_SESSION['SUCCESS']);
        } else {
            $success = "";
        }
        if (isset($_SESSION['ERROR'])) {
            $error = $_SESSION['ERROR'];
            unset($_SESSION['ERROR']);
        } else {
            $error = "";
        }

        // PAGINATION
        if (!empty($params)) {
            $currentPage = (int) strip_tags($params[0]);
        } else {
            $currentPage = 1;
        }
        $pagination = new Pagination();
        $pagination->setCurrentPage($currentPage);
        $pagination->setInnerLinks(5);
        $pagination->setNbElementsInPage(5);
        $pagination->setNbMaxElements((int) $articles->sqlCount($search));
        if (isset($link)) {
            $pagination->setUrl("/admin/listblog/{i}{$link}");
        } else {
            $pagination->setUrl("/admin/listblog/{i}");
        }

        $pagination->renderBootstrapPagination();

        // On contôle la page passée dans l'url
        if (empty($currentPage)) {
            $this->redirect404();
        }

        // Récupération des derniers articles paginés
        $articleList = $articles->sqlGetList($pagination->getFirstElement(), $pagination->getNbElementsInPage(), $search);
        return $this->twig->render("Admin/Blog/index.html.twig", ['blog' => [
            'pageTitle' => $this->PageTitle,
            'articleList' => $articleList,
            'pagination' => $pagination,
            'getSearch' => $q,
            'success' => $success,
            'error' => $error
        ]]);
    }

    //********************************************************************//
    // Administration du Forum CRUD
    //********************************************************************//

    public function listForum($params = [])
    {
        // ON test si une recherche est passée en GET
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $q = strip_tags(trim($_GET['q']));
            $link = "?q=" . $q;
            $search = explode(" ", strip_tags(trim($_GET['q'])));
        } else {
            $q = "";
            $search = [];
        }
        $this->PageTitle = "Administration du Forum";

        $topics = new Topic();

        if (isset($_SESSION['SUCCESS'])) {
            $success = $_SESSION['SUCCESS'];
            unset($_SESSION['SUCCESS']);
        } else {
            $success = "";
        }
        if (isset($_SESSION['ERROR'])) {
            $error = $_SESSION['ERROR'];
            unset($_SESSION['ERROR']);
        } else {
            $error = "";
        }


        // PAGINATION
        if (!empty($params)) {


            $currentPage = (int) strip_tags($params[0]);
        } else {
            $currentPage = 1;
        }

        $pagination = new Pagination();

        $pagination->setCurrentPage($currentPage);
        $pagination->setInnerLinks(5);
        $pagination->setNbElementsInPage(5);
        $pagination->setNbMaxElements((int) $topics->sqlCount($search));
        if (isset($link)) {
            $pagination->setUrl("/admin/listForum/{i}{$link}");
        } else {
            $pagination->setUrl("/admin/listForum/{i}");
        }

        $pagination->renderBootstrapPagination();

        // On contôle la page passée dans l'url
        if (empty($currentPage)) {
            $this->redirect404();
        }

        // Récupération des 10 derniers topics
        $topicList = $topics->sqlGetList($pagination->getFirstElement(), $pagination->getNbElementsInPage(), $search);
        return $this->twig->render("Admin/Forum/listForum.html.twig", ['forum' => [
            'pageTitle' => $this->PageTitle,
            'topicList' => $topicList,
            'pagination' => $pagination,
            'getSearch' => $q,
            'success' => $success,
            'error' => $error
        ]]);
    }

    //********************************************************************//
    // Administration des catégories CRUD
    //********************************************************************//

    public function addCategory()
    {
        // TODO Ajouter une catégorie CRUD CATEGORIES
        if (isset($_SESSION['ERROR'])) {
            echo $_SESSION['ERROR']['message'];
            unset($_SESSION['ERROR']);
        }
    }
}
