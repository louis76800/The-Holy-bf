<?php

namespace src\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use src\Model\Article;
use src\Model\BDD;
use src\Model\Category;
use src\Model\Media;
use src\Model\User;

/**
 * Class ArticleController
 * @author Tony
 * @package src\Controller
 */

class ArticleController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Liste les articles du blog
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index(): string
    {
        $pageTitle = "Blog";
        $article = new Article();
        $articleList = $article->sqlGetAll(BDD::getInstance());

        if (!empty($articleList)){

            // Instance model category, medias, users
            $oCat = new Category();
            $oMedias = new Media();
            $oUser = new User();

            // On récupère l'utilisateur pour chaque article
            foreach ($articleList as $value){
                $user[$value->ART_ID] = $oUser->get_user($value->USER_ID);
            }

            // On récupére les id catégories de l'article pour chaque article
            foreach ($articleList as $value){
                $catId[$value->ART_ID] = $value->sqlGetCategories(BDD::getInstance(), $value->ART_ID);

                // On récupère les info des catégories pour chaque id
                foreach ($catId[$value->ART_ID] as $id){
                    $categories[$value->ART_ID][$id] = $oCat->sqlFindById($id);
                }
            }

            // On récupère les id médias de l'article pour chaque article en cours
            foreach ($articleList as $value){
                $medId[$value->ART_ID] = $value->sqlGetMedias(BDD::getInstance(), $value->ART_ID);
                if (!empty($medId[$value->ART_ID])){

                    // On récupère les info des médias pour chaque id
                    foreach ($medId[$value->ART_ID] as $id){
                        $medias[$value->ART_ID][$id] = $oMedias->SqlGetOne(BDD::getInstance(), $id);
                    }
                }
                else{
                    $medId[$value->ART_ID] = [];
                }
            }
        }
        else{
            $articleList = null;
            $categories = null;
            $medias = null;
            $user = null;
        }

        // Retourne la vue Twig
        return $this->twig->render("Article/index.html.twig", ['blog' => [
            'pageTitle' => $pageTitle,
            'articleList' => $articleList,
            'categories' => $categories,
            'medias' => $medias,
            'user' => $user
        ]]);
    }

    /**
     * SHOW
     * Affiche un article du blog (avec catégories, auteur et médias)
     * @author Tony
     * @return string
     */
    public function show(array $params = null): string
    {
        // On nettoie le param id et on vérifie que ce soit un nombre
        if (isset($params[0]) && !empty($params[0])) {
            $id = strip_tags($params[0]);
            $id = (is_numeric($id)) ? $id : 0;
        } else {
            $id = 0;
        }

        // On accepte un seul paramètre sur cette page
        if ($id > 0 && count($params) === 1) {
            $article = new Article();
            $articleOne = $article->sqlGetOne(BDD::getInstance(), $id);

            // Si on récupère un article
            if ($articleOne) {
                if (isset($_SESSION['ERROR'])) {
                    $error = $_SESSION['ERROR']['message'];
                    unset($_SESSION['ERROR']);
                } else {
                    $error = "";
                }
                if (isset($_SESSION['SUCCESS'])) {
                    $success = $_SESSION['SUCCESS']['message'];
                    unset($_SESSION['SUCCESS']);
                } else {
                    $success = "";
                }

                // Récupération de l'auteur de l'article
                $user = new User();
                $articleAuthor = $user->get_user((int)$articleOne->USER_ID);

                // Récupération des médias
                $mediasId = $article->sqlGetMedias(BDD::getInstance(), (int)$articleOne->ART_ID);
                if (!empty($mediasId)){
                    $media = new Media();
                    foreach ($mediasId as $id){
                        $oMedias[] = $media->SqlGetOneObject(BDD::getInstance(), (int)$id);
                    }
                }
                else{
                    $oMedias = null;
                }

                // Récupération des catégories
                $catId = $article->sqlGetCategories(BDD::getInstance(), (int)$articleOne->ART_ID);
                if (!empty($catId)){
                    $cat = new Category();
                    foreach ($catId as $id){
                        $aCat[] = $cat->sqlFindById($id);
                    }
                }
                else{
                    $aCat = null;
                }

                return $this->twig->render(
                    "Article/show.html.twig",
                    [
                        'article' => $articleOne,
                        'user' => $articleAuthor,
                        'medias' => $oMedias,
                        'categories' => $aCat,
                        'success' => $success,
                        'error' => $error
                    ]
                );
            } else {
                // Redirection 404 vue twig
                http_response_code('404');
                return $this->twig->render("/404.html.twig");
            }
        } else {
            // Redirection 404 vue twig
            http_response_code('404');
            return $this->twig->render("/404.html.twig");
        }
    }

    /**
     * DELETE
     * @param array $params
     * @return mixed
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function delete(array $params = [])
    {
        // rôle admin session_user else redirection
        User::is_admin();

        if (empty($params) || count($params) > 1 || !is_numeric($params[0])) {
            // Redirection 404 vue twig
            http_response_code('404');
            return $this->twig->render("/404.html.twig");
        }

        $id = strip_tags($params[0]);
        $article = new Article();
        $article->sqlDelete(BDD::getInstance(), $id);
        if (!isset($_SESSION['ERROR'])) {
            $_SESSION['SUCCESS'] = ['message' => "L'article id $id a bien été supprimé !"];
        }
        header('location: /Admin/listBlog');
    }

    /**
     * ADD
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function add()
    {
        // rôle user sinon redirect page login
        User::is_admin();

        // On initialise les datas passées à la vue Twig
        (string) $pageTitle = "Ajouter un article";
        (string) $error = "";
        $article = new Article();

        // Gestion des catégories
        $category = new Category();
        $categories = $category->sqlGetAll(BDD::getInstance());

        // Il doit y avoir au moins une catégorie en bdd pour ajouter un article
        if (!$categories) {
            $_SESSION['ERROR'] = ['message' => 'Merci d\'ajouter au moins une catégorie avant de créer un article'];
            header('Location:addCategory');
            exit();
        }

        // Si le formulaire est posté
        if ($_POST) {

            // On vérifie si l'auteur existe (connecté)
            if (isset($_SESSION['user']['Role_ID'])) {
                $article->setAuthorId($_SESSION['user']['Id']);
            } else {
                $article->setAuthorId(0);
            }

            // On vérifie les champs postés et on hydrate l'objet (failles XSS)
            $article->setContent(isset($_POST['content']) ? $_POST['content'] : "");

            // On stocke les id catégories dans un tableau pour vérifier si une catégorie postée existe bien en bdd
            // (faille inspecteur elt F12)

            foreach ($categories as $categoryId) {
                $categoriesIdBdd[] = $categoryId->CAT_ID;
            }
            $article->setCategoriesId(isset($_POST['categories']) ? $_POST['categories'] : array(), $categoriesIdBdd);
            $article->setSlug(isset($_POST['slug']) ? $_POST['slug'] : "");
            $article->setTitle(isset($_POST['title']) ? $_POST['title'] : "");

            // On initialise l'objet DateTime
            $article->setDateCreate(new \DateTime());

            // Gestion des médias (images)
            if (isset($_FILES['pictures'])) {
                for ($i = 0; $i < count($_FILES['pictures']['name']); $i++) {
                    if ($_FILES['pictures']['error'][$i] === 0) {
                        $oPicture[$i] = new Media();
                        try {
                            $filename = strip_tags($_FILES['pictures']['name'][$i]);
                            $filetype = $_FILES['pictures']['type'][$i];
                            $filepathtmp = $_FILES['pictures']['tmp_name'][$i];
                            $filesize = $_FILES['pictures']['size'][$i];
                            // On vérifie l'extension et le type Mime
                            $oPicture[$i]->checkFormat($filename, $filetype);
                            // On vérifie la taille
                            $oPicture[$i]->checkSize($filename, $filesize);
                            // On renome le fichier
                            $newfilename = $oPicture[$i]->rename($filename);
                            // On indique le chemin
                            $filepath = ROOT . DS . 'public' . DS . 'assets' . DS . 'uploads' . DS . 'blog';

                            // SI OK on hydrate l'objet pour insertion future en BDD
                            $oPicture[$i]->setName($newfilename);
                            $oPicture[$i]->setFormat($filetype);
                            $oPicture[$i]->setPath($filepath . DS . $newfilename);
                            $oPicture[$i]->setAlt($article->getSlug());
                            $oPicture[$i]->setSize($filesize);
                            $oPicture[$i]->setPathtmp($filepathtmp);
                        } catch (\Exception $e) {
                            $_SESSION['ERROR'] = ['message' => $e->getMessage()];
                        }
                    }
                }
            }

            // Si pas d'erreur, on insère les données
            if (!isset($_SESSION['ERROR'])) {
                try {
                    // On insère l'article t_article / associate(categories)
                    $articleLastId = $article->sqlAdd(BDD::getInstance());

                    // Gestion des images
                    if (isset($oPicture)) {

                        // On déplace les fichiers et on insère les images en BDD
                        if (!is_dir($filepath)) {
                            mkdir($filepath, 0644, true);
                        }
                        foreach ($oPicture as $picture) {
                            move_uploaded_file($picture->getPathtmp(), $picture->getPath());

                            // execution du fichier interdite
                            chmod($picture->getPath(), 0644);

                            // Insertion bdd
                            // Table t_media
                            $pictureLastId = $picture->SqlAdd(BDD::getInstance());

                            // Table jointure contain_article(medias)
                            $article->sqlAddMediaJoin(BDD::getInstance(), $articleLastId, $pictureLastId);
                        }
                    }

                    // C'est terminé !!! on redirige vers le gestionnaire du blog CRUD
                    $_SESSION['SUCCESS'] = ['message' => "L'article sous l'id $articleLastId a bien été ajouté ! "];
                    header('Location:/admin/listBlog');
                    exit();
                } catch (\Exception $e) {
                    $_SESSION['ERROR'] = ['message' => $e->getMessage()];
                }
            } else {
                $error = $_SESSION['ERROR'];
                unset($_SESSION['ERROR']);
            }
        }

        // On affiche la vue du formulaire
        return $this->twig->render("Admin/Blog/add.html.twig", ['data' => [
            'pageTitle' => $pageTitle,
            'error' => $error,
            'article' => $article,
            'categories' => $categories
        ]]);
    }

    /**
     * UPDATE
     * @param array $params
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function update(array $params = [])
    {
        // ROLE ADMIN FOR UPDATE $_SESSION
        User::is_admin();

        // On contrôle le paramètre uri envoyé (on accepte un seul param nombre entier id )
        if (empty($params) || count($params) > 1 || !is_numeric($params[0])) {
            $this->redirect404();
        }

        // Initialisation des variables
        $id = strip_tags($params[0]);
        $error = '';
        $post = []; // Permet de récupérer une variable postée si erreur dans maj formulaire
        $o_article = new Article();
        // On récupère les catégories en BDD pour tester faille inpecteur F12
        $o_categories = new Category();
        $categoriesBDD = $o_categories->sqlGetAll(BDD::getInstance());

        // Si on a une requête de modification d'un article
        if ($_POST) {
            // On hydrate l'objet article
            $o_article->setId($id);
            $o_article->setContent(isset($_POST['content']) ? $_POST['content'] : "");
            $post['content'] = trim($o_article->getContent());
            $o_article->setSlug(isset($_POST['slug']) ? $_POST['slug'] : "");
            $post['slug'] = $o_article->getSlug();
            $o_article->setTitle(isset($_POST['title']) ? $_POST['title'] : "");
            $post['title'] = $o_article->getTitle();
            // Categories
            // On stocke les id catégories de la bdd dans un tableau pour vérifier si une catégorie postée existe
            // bien en bdd
            // (faille inspecteur elt F12)
            foreach ($categoriesBDD as $categoryId) {
                $categoriesIdBdd[] = $categoryId->CAT_ID;
            }
            $o_article->setCategoriesId(isset($_POST['categories']) ? $_POST['categories'] : array(), $categoriesIdBdd);
            $post['categories'] = $o_article->getCategoriesId();

            // Images postées à supprimer
            $o_article->setMediasId(isset($_POST['picturesDel']) ? $_POST['picturesDel'] : array());
            $post['picturesDelId'] = $o_article->getMediasId();

            // Images nouvelles à uploader
            if (isset($_FILES['pictures'])) {
                for ($i = 0; $i < count($_FILES['pictures']['name']); $i++) {
                    if ($_FILES['pictures']['error'][$i] === 0) {
                        $oPicture[$i] = new Media();
                        try {
                            $filename = strip_tags($_FILES['pictures']['name'][$i]);
                            $filetype = $_FILES['pictures']['type'][$i];
                            $filepathtmp = $_FILES['pictures']['tmp_name'][$i];
                            $filesize = $_FILES['pictures']['size'][$i];
                            // On vérifie l'extension et le type Mime
                            $oPicture[$i]->checkFormat($filename, $filetype);
                            // On vérifie la taille
                            $oPicture[$i]->checkSize($filename, $filesize);
                            // On renome le fichier
                            $newfilename = $oPicture[$i]->rename($filename);
                            // On indique le chemin
                            $filepath = ROOT . DS . 'public' . DS . 'assets' . DS . 'uploads' . DS . 'blog';

                            // SI OK on hydrate l'objet pour insertion future en BDD
                            $oPicture[$i]->setName($newfilename);
                            $oPicture[$i]->setFormat($filetype);
                            $oPicture[$i]->setPath($filepath . DS . $newfilename);
                            $oPicture[$i]->setAlt($o_article->getSlug());
                            $oPicture[$i]->setSize($filesize);
                            $oPicture[$i]->setPathtmp($filepathtmp);
                        } catch (\Exception $e) {
                            $_SESSION['ERROR'] = ['message' => $e->getMessage()];
                        }
                    }
                }
            }

            // On fait l'update
            if (!isset($_SESSION['ERROR'])) {
                try {
                    // On update l'article t_article / associate(categories / contain_article (img à supprimer)
                    $o_article->sqlUpdate();

                    // Gestion des images
                    if (isset($oPicture)){

                        // On déplace les fichiers et on insère les images en BDD
                        if(!is_dir($filepath)){
                            mkdir($filepath, 0644, true);
                        }
                        foreach ($oPicture as $picture){
                            move_uploaded_file($picture->getPathtmp(), $picture->getPath());

                            // execution du fichier interdite
                            chmod($picture->getPath(), 0644);

                            // Insertion bdd
                            // Table t_media
                            $pictureLastId = $picture->SqlAdd(BDD::getInstance());

                            // Table jointure contain_article(medias)
                            $o_article->sqlAddMediaJoin(BDD::getInstance(), $id, $pictureLastId);
                        }
                    }

                    $_SESSION['SUCCESS'] = ['message' => "L'article a bien été mis à jour ! "];
                    header('Location:/Article/show/' . $id);
                    exit();
                } catch (\Exception $e) {
                    $_SESSION['ERROR'] = ['message' => $e->getMessage()];
                    $error = $_SESSION['ERROR'];
                    unset($_SESSION['ERROR']);
                }
            } else {
                $error = $_SESSION['ERROR'];
                unset($_SESSION['ERROR']);
            }
        } // end if POST

        // On affiche le formulaire de modification de l'article en cours
        // On va chercher l'article en bdd
        $article = $o_article->sqlGetOne(BDD::getInstance(), $id);

        // Si l'article n'exite pas
        if (!$article) {
            $this->redirect404();
        }

        // Si pas de catégories dans la base de données
        if (!$categoriesBDD) {
            $_SESSION['ERROR'] = ['message' => 'Merci d\'ajouter au moins une catégorie'];
            header('location: /admin/addCategory');
            exit();
        }

        // On récupère les catégories de l'article
        $categoriesART = $o_article->sqlGetCategories(BDD::getInstance(), $id);

        // On récupère les images de l'article en cours
        $mediasId = $o_article->sqlGetMedias(BDD::getInstance(), $id);
        if ($mediasId) {
            $o_media = new Media();
            foreach ($mediasId as $key => $value) {
                $media[] = $o_media->sqlGetOne(BDD::getInstance(), $value);
            }
        } else {
            $media = array();
        }

        // On affiche la vue du formulaire de l'article à éditer avec ses datas
        $pageTitle = $this->PageTitle = $article->ART_TITLE;
        return $this->twig->render("Admin/Blog/edit.html.twig", ['data' => [
            'pageTitle' => $pageTitle,
            'article' => $article,
            'categoriesBDD' => $categoriesBDD,
            'categoriesART' => $categoriesART,
            'picturesART' => $media,
            'error' => $error,
            'post' => $post
        ]]);
    }

    /**
     * Génère un fichier PDF d'un article pour impression
     * @author Tony
     * @param array $params
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function print(array $params = [])
    {
        // Pas de id en GET
        if (empty($params[0])){
            $this->redirect404();
        }

        // On récupère les données de l'article
        $article = new Article();
        $oArticle = $article->sqlGetOne(BDD::getInstance(), (int)$params[0]);

        // Pas de résulat redirection not found
        if (!$oArticle){
            $this->redirect404();
        }

        $mediaId = $article->sqlGetMedias(BDD::getInstance(), (int)$params[0]);
        $catId = $article->sqlGetCategories(BDD::getInstance(), (int)$params[0]);
        $user = new User();
        $author = $user->get_user((int)$oArticle->USER_ID);

        // Récupère les medias de l'article en cours sous un tableau d'objet
        if (!empty($mediaId)){
            $i = 0;
            foreach ($mediaId as $id){
                $media[$i] = new Media();
                $oMedia[$i] = $media[$i]->SqlGetOneObject(BDD::getInstance(), $id);
                $i++;
            }
        }
        else{
            $oMedia = null;
        }

        // Récupère les catégories de l'article en cours sous un tableau
        if (!empty($catId)){
            $i = 0;
            foreach ($catId as $id){
                $cat[$i] = new Category();
                $aCat[$i] = $cat[$i]->sqlFindById($id);
                $i++;
            }
        }
        else{
            $aCat = null;
        }

        // Envoi des variables vers la vue
        $view = $this->twig->render("Article/pdf.html.twig", ['data' => [
            'article' => $oArticle,
            'medias' => $oMedia,
            'categories' => $aCat,
            'user' => $author
        ]]);

        // Envoi de la vue Twig à DomPdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);

        // Gère l'upload d'images sans certificat SSL https  en local
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ]
        ]);
        $dompdf->setHttpContext($context);
        $dompdf->loadHtml($view);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = $oArticle->ART_TITLE;
        $dompdf->stream($filename);
    }

    /**
     * Retourne l'ensemble des articles du blog par catégorie
     * @author Tony
     * @param array $params
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function category(array $params = []){

        if (empty((int) $params[0])){
            $this->redirect404();
        }
        else{
            $id = (int) $params[0];
        }

        // On récupère les info de la catégorie
        $oCat = new Category();
        $catName = $oCat->sqlFindById($id);

        $pageTitle = "Blog - Catégorie ".$catName['CAT_NAME']." ";
        $article = new Article();
        $articleList = $article->sqlFindByCategory($id);

        if (!empty($articleList)){

            // Instance model category, medias, users
            $oMedias = new Media();
            $oUser = new User();

            // On récupère l'utilisateur pour chaque article
            foreach ($articleList as $value){
                $user[$value->ART_ID] = $oUser->get_user($value->USER_ID);
            }

            // On récupére les id catégories de l'article pour chaque article
            foreach ($articleList as $value){
                $catId[$value->ART_ID] = $value->sqlGetCategories(BDD::getInstance(), $value->ART_ID);

                // On récupère les info des catégories pour chaque id
                foreach ($catId[$value->ART_ID] as $id){
                    $categories[$value->ART_ID][$id] = $oCat->sqlFindById($id);
                }
            }

            // On récupère les id médias de l'article pour chaque article en cours
            foreach ($articleList as $value){
                $medId[$value->ART_ID] = $value->sqlGetMedias(BDD::getInstance(), $value->ART_ID);
                if (!empty($medId[$value->ART_ID])){

                    // On récupère les info des médias pour chaque id
                    foreach ($medId[$value->ART_ID] as $id){
                        $medias[$value->ART_ID][$id] = $oMedias->SqlGetOne(BDD::getInstance(), $id);
                    }
                }
                else{
                    $medId[$value->ART_ID] = [];
                }
            }
        }

        // Retourne la vue Twig
        return $this->twig->render("Article/index.html.twig", ['blog' => [
            'pageTitle' => $pageTitle,
            'articleList' => $articleList,
            'categories' => $categories,
            'medias' => $medias,
            'user' => $user
        ]]);
    }
}
