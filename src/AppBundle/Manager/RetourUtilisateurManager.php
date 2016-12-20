<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 20/12/2016
 * Time: 10:04
 */

namespace AppBundle\Manager;


use AppBundle\Mailer\AppTwigSiwftMailer;
use Trello\Api\Card;
use Trello\Client;
use Trello\Manager;

class RetourUtilisateurManager
{
    /** Nom de la liste de cartes TRELLO où sont enregitrées les cartes  */
    const CARDS_LIST_NAME = "Suggestions/Bugs";
    const CARDS_BOARD_ID = "MY1WgL9v";

    /** @var EmailManager */
    private $emailManager;

    /** @var string */
    private $trelloTokenOrLogin;

    /** @var string */
    private $trelloPass;

    /**
     * RetourUtilisateurManager constructor
     */
    public function __construct(AppTwigSiwftMailer $emailManager, $trelloTokenOrLogin, $trelloPass)
    {
        $this->emailManager = $emailManager;
        $this->trelloTokenOrLogin = $trelloTokenOrLogin;
        $this->trelloPass = $trelloPass;
    }


    /**
     * @param $datas array
     */
    public function posterSuggestion($datas)
    {
        $descriptionSuggestion = $datas['description'] . " \r\n";
        $descriptionSuggestion .= "Nom du rapporteur: " . $datas['nom'] . "\r\n";
        $descriptionSuggestion .= "Email: " . $datas['mail'] . "\r\n";
        $descriptionSuggestion .= "Résolution: " . $datas['resolution'] . " \r\n";
        $descriptionSuggestion .= "OS: " . $datas['os'] . " \r\n";
        $descriptionSuggestion .= "Navigateur: " . $datas['navigateur'] . " \r\n";
        $descriptionSuggestion .= "UserAgent: " . $datas['userAgent'] . " \r\n";
        $descriptionSuggestion .= "PageURL: " . $datas['pageURL'];

        // Ajout d'une carte sur le tableau Trello
        $client = new Client();
        $client->authenticate($this->trelloTokenOrLogin, $this->trelloPass, Client::AUTH_URL_CLIENT_ID);
        $manager = new Manager($client);
        $ardGanBoard = $manager->getBoard(self::CARDS_BOARD_ID);
        if ($ardGanBoard != null) {
            $cardsLists = $ardGanBoard->getLists();
            $cardsListToLink = null;
            foreach ($cardsLists as $cardsList) {
                if ($cardsList->getName() == self::CARDS_LIST_NAME) {
                    $cardsListToLink = $cardsList;
                    break;
                }
            }
            if ($cardsListToLink != null) {
                // Ajout de la demande à Trello
                $trelloData = array(
                    "idList" => $cardsListToLink->getId(),
                    "name" => $datas['titre'] . " (" . $datas['nom'] . ")",
                    "desc" => $descriptionSuggestion
                );
                $cardApi = new Card($client);
                $cardApi->create($trelloData);
            }
        }

        // Envoie d'un email
        /*$message = \Swift_Message::newInstance()
            ->setSubject("[AT][SuggestionArdGanisateur] " . $datas["titre"])
            ->setFrom('contact@ardteam.fr')
            ->setTo(array('contact@ardteam.fr'))
            ->setBody(nl2br($descriptionSuggestion), 'text/html');
        $this->emailManager->send($message);*/
    }
}