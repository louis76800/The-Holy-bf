<?php


namespace src\Controller;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Permet de contacter l'administrateur du site
 * @author Tony
 * Class ContactController
 * @package src\Controller
 */
class ContactController extends AbstractController
{
    /**
     * Formulaire de contact (utilise la sendBox Mailtrap pour la démo)
     * @author Tony
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index() :string
    {
        $error="";
        $success="";
        $post = [];

        // Traitement du formulaire
        if ($_POST)
        {
            $post = $_POST;

            // On vérifie que les champs ne soient pas vides
            if (!self::helperFormValidate($_POST, ['name', 'mail', 'message']))
            {
                $error = 'Merci de remplir tous les champs';
            }
            else
            {
                // On vérifie que l'on a bien un email valide
                if(!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))
                {
                    $error = 'Merci de renseigner une adresse email valide';
                }
                else
                {
                    // On nettoie les données postées
                    $name = self::helperFormInputClean($_POST['name']);
                    $email = self::helperFormInputClean($_POST['mail']);
                    $message = self::helperFormInputClean($_POST['message']);

                    // Instance PHPMailer
                    $phpmailer = new PHPMailer();
                    $phpmailer->CharSet = 'UTF-8';
                    $phpmailer->isSMTP();
                    $phpmailer->Host = 'smtp.mailtrap.io';
                    $phpmailer->SMTPAuth = true;
                    $phpmailer->Port = 2525;
                    $phpmailer->Username = '8549ad760e118d';
                    $phpmailer->Password = '04c9641e7b55f8';

                    // Construction du message
                    $phpmailer->setFrom('no-reply@holy-bf.di', 'Formulaire de contact');
                    $phpmailer->addAddress('admin@holy-bf.di', 'Administrateur Holy-bf');
                    //Content
                    $phpmailer->isHTML(true);
                    $phpmailer->Subject = "Contact Holy-bf ";
                    $phpmailer->Body    =
                        "<p><strong>Nom</strong> : {$name}</p>
                        <p><strong>Email</strong> : {$email}</p>
                        <p><strong>Message</strong> :</p>
                        <p>".nl2br($message)."</p>";
                    $phpmailer->AltBody = "{$name}\n{$email}\n{$message}";

                    // Envoi
                    if($phpmailer->send()){
                        $post = "";
                        $success = 'Votre message a bien été envoyé ! Nous vous recontacterons prochainement';
                    }
                    else{
                        $error = 'Une erreur s\'est produite !';
                    }
                }
            }
        }

        // Affiche le formulaire de contact
        return $this->twig->render("Contact/index.html.twig", [
            'error' => $error,
            'success' => $success,
            'post' => $post
        ]);
    }
}