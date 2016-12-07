<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/10/2016
 * Time: 10:56
 */

namespace AppBundle\Utils\Response;


use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AppJsonResponse
 * @package AppBundle\Utils\Response
 * Le but est de définir un modèle de DATA à retourner lors des requêtes Ajax afin d'automatiser les traitements récurrents :
 * - affichage des messages d'erreur/succes/...
 * - mise à jour/ajout de contenu HTML dans la page pour les formulaires et templates. Les mises à jour très spécifiques à la vue doivent être effectuées via Javascript
 */
class AppJsonResponse extends JsonResponse
{
    /*
     * data = array(
     *      self::MESSAGES => array(
     *          FlashBagTypes::XXX => tableau contenant les messages du type FlashBagTypes::XXX
     *      ),
     *      self::HTML_CONTENTS => array(
     *          self::HTML_CONTENT_ACTION_REPLACE => array( htmlElementId => contenu HTML à utiliser ),
     *          self::HTML_CONTENT_ACTION_APPEND_TO => array( htmlElementId => contenu HTML à utiliser )
     *          self::HTML_CONTENT_ACTION_HTML => array( htmlElementId => contenu HTML à utiliser )
     *      ),
     *      self::DATA => array( données à transmettre à la fonction JS callback (done/fail) pour effectuer des traitements spécifiques (mise à jour d'affichage,...) )
     *
     * )
     */

    const MESSAGES = "messages";
    const HTML_CONTENTS = "htmlContents";
    const DATA = "data";

    /** Replace the element by the content */
    const HTML_CONTENT_ACTION_REPLACE = "replaceWith";
    /** Append the content to the element */
    const HTML_CONTENT_ACTION_APPEND_TO = "append";
    /** Change the html content of the element by the content */
    const HTML_CONTENT_ACTION_HTML = "html";
}