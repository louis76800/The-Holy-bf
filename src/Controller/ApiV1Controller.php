<?php


namespace src\Controller;


use src\Model\Article;
use src\Model\BDD;

class ApiV1Controller extends AbstractController
{
    /**
     * API Home
     */
    public function index(){
        header("Content-Type: text/plain");
        echo "API Holy-Bf v1\n";
        echo "\n";
        echo "*********************************";
        echo "\n";
        echo "\n";
        echo "*********************************";
        echo "\n";
    }

    /**
     * Get all articles
     */
    public function articles(){
        $articles = new Article();
        $result = $articles->sqlGetAll(BDD::getInstance());
        $i = 0;
        $data = [];
        //var_dump($result);
        foreach ($result as $value){
            $article = new Article();
            $data[$i] = $article->setId($value->ART_ID);
            $data[$i] = $article->setTitle($value->ART_TITLE);
            $data[$i] = $article->setDateCreate(new \DateTime($value->ART_DATEADD));
            $data[$i] = $article->setContent($value->ART_BODY);
            $data[$i] = $article->setAuthorId($value->USER_ID);

            // Récupération img
            $imgId[$i] = $article->sqlGetMedias(BDD::getInstance(), $value->ART_ID);
            $i++;
        }
        header("Content-Type: application/json");
        echo json_encode($data);

    }
    /**
     * Get articleById
     */
    public function article($id){
        $id = (int)$id[0];
        $article = new Article();
        $result = $article->sqlGetOne(BDD::getInstance(), $id);
        $data = $article->setId((int)$result->ART_ID);
        $data = $article->setTitle($result->ART_TITLE);
        $data = $article->setDateCreate(new \DateTime($result->ART_DATEADD));
        $data = $article->setContent($result->ART_BODY);
        $data = $article->setAuthorId($result->USER_ID);

        header("Content-Type: application/json");
        echo json_encode($data);

    }

}