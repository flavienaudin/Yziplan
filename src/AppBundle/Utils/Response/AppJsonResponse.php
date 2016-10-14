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
 * - mise à jour/ajout de contenu HTML dans la page
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
     *      self::FORM_ERRORS => array( nom complet du champ du formulaire (Cf. FormUtils::getFullFormErrorFieldName(FormError)) => message d'erreur au champ )
     *      )
     * )
     */

    const MESSAGES = "messages";
    const HTML_CONTENTS = "htmlContents";
    const FORM_ERRORS = "formErrors";

    /** Replace the element by the content */
    const HTML_CONTENT_ACTION_REPLACE = "replaceWith";
    /** Append the content to the element */
    const HTML_CONTENT_ACTION_APPEND_TO = "append";
    /** Change the html content of the element by the content */
    const HTML_CONTENT_ACTION_HTML = "html";
}