<?php

namespace src\Controller;

use src\Model\User;
use src\Model\Category;
use src\Model\Message;
use src\Model\Topic;
use src\Model\BDD;


class TopicController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $pageTitle = "Forum";
        $topic = new Topic();
        $topicList = $topic->sqlGetAll(BDD::getInstance());

        return $this->twig->render("Topic/index.html.twig", ['forum' => [
            'pageTitle' => $pageTitle,
            'topicList' => $topicList
       ]]);


    }

    /**
     * SHOW
     * Affiche un topic du forum
     * @return string
     */
  //faire une fonction en plus messageadd

    public function messageadd()
    {
//on verifie que l'utilisateur est connecté
if (isset($_SESSION['user']["Role_ID"])){

    $message = new Message();
    if (isset($_POST['submit'])) {
        if (($_POST['MessBody']) <> ""&&strlen($_POST['MessBody'])>2) {

            $message->setMessBody($_POST['MessBody']);
            $message->setTopicID($_POST['Topic']);
            $MessDate = new \datetime;

            $message->setMessDate($MessDate);
            $message->setUserId($_SESSION['user']['Id']);
            // on insert
            $message->MessageAdd(BDD::getInstance());
            header('location: /Topic/Show/' . $message->getTopicId());
            exit();
        } else {
            $message->setTopicID($_POST['Topic']);
            header('location: /Topic/Show/' . $message->getTopicId());
        }

    }


}else{
    header('location: /User/login');
}
die();



    }
       //try catch




    public function MessageDel($params)
    {        if (isset($_SESSION)){
        $idSessuser= $_SESSION['user']['Id'] ;
        $idmessage = strip_tags($params[2]);
        $idtopic = strip_tags($params[0]);
        $iduser = strip_tags($params[1]);
        $idSessuser=$_SESSION['user']['Role_ID'];

        if ($idSessuser==$iduser || $idSessuser=1){

           $message = new Message();
           $message->MessageDel(BDD::getInstance(), $idmessage);
           if (!isset($_SESSION['ERROR'])){
               $_SESSION['SUCCESS'] = ['message' => "Le message id $idmessage a bien été supprimé !"];
           }
       header('location: /Topic/Show/'.$idtopic);
       }else{
           header('location: /Topic/Show/'.$idtopic);

       }
    }

    }





    public function show(array $params = null)
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
            $topic = new Topic();
            $topicOne = $topic->sqlGetOne(BDD::getInstance(), $id);
            $message = new Message();
            $messageAll = $message->MessagesTopic(BDD::getInstance(), $id);

            if (isset($_SESSION["user"]["Id"])){
                $id = $_SESSION["user"]["Id"];

            }else{
                $id ="";
            }




                    // Si on récupère un topic
            if ($topicOne) {
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

                    return $this->twig->render("Topic/show.html.twig",
                        [
                            'topic' => $topicOne,
                            'success' => $success,
                            'error' => $error,
                            'messages' =>$messageAll,
                            'id'=>$id
                        ]);
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
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function delete(array $params = [])
    {
        // rôle admin session_user else redirection
        User::is_admin();
        if (empty($params) || count($params) > 1 || !is_numeric($params[0]))
        {
            // Redirection 404 vue twig
            http_response_code('404');
            return $this->twig->render("/404.html.twig");
        }

        $id = strip_tags($params[0]);
        $topic = new Topic();
        $topic->sqlDelete(BDD::getInstance(), $id);
        if (!isset($_SESSION['ERROR'])){
            $_SESSION['SUCCESS'] = ['message' => "Le topic id $id a bien été supprimé !"];
        }
        header('location: /Admin/listForum');

    }




public function add()
{
    // rôle user sinon redirect page login
    User::is_admin();
    // On initialise les datas passées à la vue Twig
    (string) $pageTitle = "Ajouter un topic";
    (string) $error = "";
    $topic = new Topic();

    // Gestion des catégories
    $category = new Category();
    $categories = $category->sqlGetAll(BDD::getInstance());
    // Il doit y avoir au moins une catégorie en bdd pour ajouter un topic
    if (!$categories){
        $_SESSION['ERROR'] = ['message' => 'Merci d\'ajouter au moins une catégorie avant de créer un topic'];
        header('Location:addCategory');
        exit();
    }

    // Si le formulaire est posté
    if ($_POST){

        // On vérifie si l'auteur existe (connecté)

        if (isset($_SESSION['user']['Id'])){
            $topic->setTopicAuthorId($_SESSION['user']['Id']);

        }
        else{
            $topic->setTopicAuthorId(0);
        }


        // On vérifie les champs postés et on hydrate l'objet (failles XSS)
        $topic->setContent(isset($_POST['content']) ? $_POST['content'] : "");
// On stocke les id catégories dans un tableau pour vérifier si une catégorie postée existe bien en bdd
        // (faille inspecteur elt F12)

        foreach ($categories as $categoryId){
            $categoriesIdBdd[] = $categoryId->CAT_ID;
        }
        $topic->setCategoriesId(isset($_POST['categories']) ? $_POST['categories'] : array(), $categoriesIdBdd);

        $topic->setSlug(isset($_POST['slug']) ? $_POST['slug'] : "");

        $topic->setTitle(isset($_POST['title']) ? $_POST['title'] : "");


        // On initialise l'objet DateTime
        $topic->setDateCreate(new \DateTime());
        // Si pas d'erreur, on insère les données

        if (!isset($_SESSION['ERROR'])){
            try {

                // On insère le topic t_topic / post(categories)
                $topicLastId = $topic->sqlAdd(BDD::getInstance());

                // C'est terminé !!! on redirige vers le gestionnaire du forum CRUD
                $_SESSION['SUCCESS'] = ['message' => "Le topic sous l'id $topicLastId a bien été ajouté ! "];
                header('Location:/admin/listForum');
                exit();

            }catch (\Exception $e){
                $_SESSION['ERROR'] = ['message' => $e->getMessage()];
            }
        }
        else{
            $error = $_SESSION['ERROR'];
            unset($_SESSION['ERROR']);
        }
    }

    // On affiche la vue du formulaire
    return $this->twig->render("Admin/Forum/add.html.twig", ['data' => [
        'pageTitle' => $pageTitle,
        'error' => $error,
        'topic' => $topic,
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
        if (empty($params) || count($params) > 1 || !is_numeric($params[0]))
        {
            $this->redirect404();
        }

        // Initialisation des variables
        $id = strip_tags($params[0]);
        $error = '';
        $post = []; // Permet de récupérer une variable postée si erreur dans maj formulaire
        $o_topic = new Topic();
        // On récupère les catégories en BDD pour tester faille inpecteur F12
        $o_categories = new Category();
        $categoriesBDD = $o_categories->sqlGetAll(BDD::getInstance());

        // Si on a une requête de modification d'un topic
        if ($_POST)
        {


            // On hydrate l'objet topic
            $o_topic->setTopicID($id);

            $o_topic->setTopicDescription(isset($_POST['content']) ? $_POST['content'] : "");
            $post['content'] = trim($o_topic->getTopicDescription());
           $o_topic->setTopicUrl(isset($_POST['slug']) ? $_POST['slug'] : "");
            $post['slug'] = $o_topic->getSlug();
            $o_topic->setTopicTitle(isset($_POST['title']) ? $_POST['title'] : "");
          $post['title'] = $o_topic->getTopicTitle();
            // Categories
            // On stocke les id catégories de la bdd dans un tableau pour vérifier si une catégorie postée existe
            // bien en bdd
            // (faille inspecteur elt F12)
            foreach ($categoriesBDD as $categoryId){
                $categoriesIdBdd[] = $categoryId->CAT_ID;
            }


           $o_topic->setCategoriesId(isset($_POST['categories']) ? $_POST['categories'] : array(), $categoriesIdBdd);
            $post['categories'] = $o_topic->getCategoriesId();




            // On fait l'update
            if (!isset($_SESSION['ERROR']))
            {
                try {
                    $o_topic->sqlUpdate();


                $_SESSION['SUCCESS'] = ['message' => "Le topic a bien été mis à jour ! "];
                header('Location:/Topic/show/'.$id);
                exit();
            } catch (\Exception $e) {
            $_SESSION['ERROR'] = ['message' => $e->getMessage()];
            $error = $_SESSION['ERROR'];
            unset($_SESSION['ERROR']);
        }
            }
            else
            {
                $error = $_SESSION['ERROR'];
                unset($_SESSION['ERROR']);
            }


        }// end if POST

        // On affiche le formulaire de modification de le topic en cours
        // On va chercher le topic en bdd
        $topic = $o_topic->sqlGetOne(BDD::getInstance(), $id);

        // Si le topic n'exite pas
        if (!$topic)
        {
            $this->redirect404();
        }

        // Si pas de catégories dans la base de données
        if (!$categoriesBDD)
        {
            $_SESSION['ERROR'] = ['message' => 'Merci d\'ajouter au moins une catégorie'];
            header('location: /admin/addCategory');
            exit();
        }

        // On récupère les catégories de le topic
        $categoriesTOPIC = $o_topic->sqlGetCategories(BDD::getInstance(), $id);
        // On affiche la vue du formulaire de le topic à éditer avec ses datas
        $pageTitle = $this->PageTitle = $topic->TOPIC_TITLE;
return $this->twig->render("Admin/Forum/edit.html.twig", ['data' => [
            'pageTitle' => $pageTitle,
            'topic' => $topic,
            'categoriesBDD' => $categoriesBDD,
            'categoriesTOPIC' => $categoriesTOPIC,
            'error' => $error,
            'post' => $post
        ]]);

    }
}
