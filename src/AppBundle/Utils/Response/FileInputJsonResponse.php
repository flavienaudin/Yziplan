<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 25/10/2016
 * Time: 15:39
 */

namespace AppBundle\Utils\Response;


use Symfony\Component\HttpFoundation\JsonResponse;

class FileInputJsonResponse extends JsonResponse
{

    /*
     * data = array(
     *      self::ERRORS => string,
     *      self::INITIAL_PREVIEW => array( )
     *      self::INITIAL_PREVIEW_CONFIG => array( )
     *      self::INITIAL_PREVIEW_THUMB_TAGS => array( )
     * )
     */

    const ERROR = "error";
    const INITIAL_PREVIEW = "initialPreview";
    const INITIAL_PREVIEW_CONFIG = "initialPreviewConfig";
    const IPC_URL = "url";
    const IPC_KEY = "key";

    const INITIAL_PREVIEW_THUMB_TAGS = "initialPreviewThumbTags";
}