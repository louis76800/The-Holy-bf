<?php


namespace src\Controller;

use src\Model\BDD;
use src\Model\Category;
use src\Model\User;

class CatController extends AbstractController
{
    public function index()
    {
        //var_dump(ici);
//TODO appelle fonction de list()
    }

    public function listCategory()
    {
        if (isset($_SESSION['SUCCESS']))
        {
            $success = $_SESSION['SUCCESS'];
            unset($_SESSION['SUCCESS']);
        }
        else{
            $success = "";
        }
        if (isset($_SESSION['ERROR']))
        {
            $error = $_SESSION['ERROR'];
            unset($_SESSION['ERROR']);
        }
        else{
            $error = "";
        }

        $pageTitle = "Catégories existantes";
        $category = new Category();
        $categoryList = $category->sqlGetAll(BDD::getInstance());
        //var_dump("ici");
        return $this->twig->render("Admin/Category/index.html.twig",  ['categories' => ['pageTitle' => $pageTitle, 'categoryList'=> $categoryList]]);//, 'success'=> $success, 'error'=> $error]]);
    }

    public function deleteOne(int $id)
    {

    }
    public function updateOne(array $params = [])
    {
        User::is_admin();

        $pageTitle = "Edition de catégorie";
        $error = "";
        $success = "";
        $curCategory = NULL;
        $category = NULL;

        if (empty($params) || count($params) > 1 || !is_numeric($params[0])) {
            //var_dump($params);
            $this->redirect404();
        }
        $curId = strip_tags($params[0]);
        //var_dump("catégorie en cours");
        $curCategory = New Category();
        $curCategory = $curCategory->sqlFindById($curId);
//var_dump("avant le post");
        if(isset($_POST['descript'])){
            //formulaire d'update
            //var_dump($_POST);

            $category = new Category();
            //$id = $category->sqlFindById($_POST["id"]);

            $category->setId($curCategory['CAT_ID']);
            if(empty($_POST['nom']) ||$_POST['nom'] == ''){
                $category->setName($curCategory['CAT_NAME']);
                            }
            else{
                $category->setName($_POST['nom']);

            }
            if(empty($_POST['descript']) ||$_POST['descript'] == ''){
                $category->setDescription($curCategory['CAT_DESCRIPTION']);
            }
            else{
                $category->setDescription($_POST['descript']);

            }
            //var_dump($category);

            $result = $category->sqlUpdateCategory(BDD::getInstance());
            //var_dump("ici");
            if (isset($result)){
                $success = "La catégorie a bien été mise à jour";
                $_SESSION['SUCCESS'] = $success;
                header("location:/cat/listCategory");

                //header("location:/?controller=Cat&action=listCategory");
                    }
            }
        return $this->twig->render("Admin/Category/editCategory.html.twig",  ['data' => ['pageTitle' => $pageTitle, 'curCategory'=>$curCategory,'category'=> $category]]);//, 'success'=> $success, 'error'=> $error]]);

    }
    public function list(array $params =[])
    {
        //TODO ajout success et error
        if(empty($params)|| !is_numeric($params[0])){
            http_response_code('404');
            return $this->twig->render("/404.html.twig");

        }
        $id = strip_tags($params[0]);
        $category = new Category();
        $category->setId($id);
        $result = $category->initCategory(BDD::getInstance());
        $category->setName($result["CAT_NAME"]);
        $category->setDescription($result["CAT_DESCRIPTION"]);

        $articlesList = $category->sqlGetArticlesFromCategory(BDD::getInstance(),$id);
        $name = $category->getName();
        $pageTitle = "Liste des articles reliés à la catégorie $name";
        return  $this->twig->render("Admin/Category/showCategory.html.twig",["Instance" =>['pageTile'=>$pageTitle,'category'=>$category,'articlesList'=>$articlesList]]);

    }

    public function add()
    {
        unset($_SESSION["Message"]);
        $pageTitle = "Ajouter une catégorie";
        $error = "";
        $success="";
        $category = NULL; // TODO : erreur au niveau du return si non instanciée
        $message = NULL;

        $_SESSION["Message"]= "Bravo vous allez insérer votre catégorie";


        //var_dump('ici');

        if($_POST){
            //TODO vérification user

            //TODO injection code à prévenir

            $category = new Category();

            if (isset($_POST['descript']) || $_POST['descript']= "" ){
                $category->setDescription($_POST['descript']);
                //var_dump($category->getDescription());
//TODO : else error
            }else{
                $message="description vide, merci de le remplir";
            }
            if (isset($_POST['nom']) || $_POST['nom']= "" ){
                $category->setName($_POST['nom']);
//TODO : else error, à traiter dans la vue twig ?
            }else{
                $message="nom catégorie est vide, merci de le remplir";
            }
            //var_dump("post effectué, prêt à insérer");
            if (!empty($category->getName()) && !empty($category->getDescription())){
                $result = $category->sqlAdd(BDD::getInstance());
                if(isset($result)){
                    return $this->listCategory();
                }
            }

            if(isset ($_SESSION['ERROR'])){
                try{
                    $idLastCategory = $category->sqlAdd(BDD::getInstance());
                    $_SESSION['SUCCESS'] = ['message' =>"la catégorie $idLastCategory a bien été ajoutée"];
                    header('Location:/cat/listCategory');
                    exit();
                }catch (\Exception $e) {
                    $_SESSION['ERROR'] = ['message' => $e->getMessage()];
                }
            } else {
                $error = $_SESSION['ERROR'];
                unset($_SESSION['ERROR']);
                $this->redirect404();
                //TODO gestion d'erreur proprement
            }

        }
    return$this->twig->render("Admin/Category/addCat.html.twig",['data'=>[
                                                                'pageTitle'=>$pageTitle,
                                                                'category'=> $category]]); //TODO

    }
}