<?php

namespace src\Controller;

use Twig\Extra\String\StringExtension;
use Twig\Extra\Intl\IntlExtension;

abstract class AbstractController
{
    protected $loader;
    protected $twig;

    public function getTwig()
    {
        return $this->twig;
    }

    /**
     * Helper template Twig
     * AbstractController constructor.
     */
    public function __construct()
    {
        $this->loader = new \Twig\Loader\FilesystemLoader($_SERVER["DOCUMENT_ROOT"] . "/../templates");
        $this->twig = new \Twig\Environment($this->loader, [
            "debug" => true,
            "cache" => $_SERVER["DOCUMENT_ROOT"] . "/../var/cache"
        ]);
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        // Filtre truncate text for Twig
        $this->twig->addExtension(new StringExtension());
        // Gestion des dates
        $this->twig->addExtension(new IntlExtension());
        // Gestion des sessions
        $this->twig->addGlobal('session', $_SESSION);
    }

    /**
     * Redirection 404
     * @author Tony
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function redirect404()
    {
        http_response_code('404');
        echo $this->twig->render("/404.html.twig");
        exit();
    }

    /**
     * Helper form : Vérifie si les champs requis ne sont pas vides
     * @author Tony
     * @param array $form ($_POST ou $_GET)
     * @param array $fields champs à vérifier
     * @return bool
     */
    public static function helperFormValidate(array $form, array $fields):bool
    {
        foreach($fields as $field){
            // Si le champ est absent ou vide dans le tableau
            if(!isset($form[$field]) || empty($form[$field])){
                return false;
            }
        }
        // Sinon
        return true;
    }

    /**
     * Nettoie une donnée utilisateur transmise
     * @author Tony
     * @param $data
     * @return mixed
     */
    public static function helperFormInputClean($data)
    {
        $data = trim($data);
        $data = stripcslashes($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);

        return $data;
    }
}
