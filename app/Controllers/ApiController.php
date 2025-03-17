<?php

namespace Controllers;

use Exception;
use Zephyrus\Application\Controller;
use Zephyrus\Network\Response;
use Zephyrus\Network\Router\Get;
use Zephyrus\Network\Router\Post;
use Models\Inventory\Services\UserServices;
use Models\Inventory\Services\TokenServices;
use Zephyrus\Network\Router\Put;

class ApiController extends Controller
{

    #[Post('/login')]
    public function login(): Response
    {
        $form = $this->buildForm();
        $username = $form->getValue('username');
        $password = $form->getValue('password');

        $user = UserServices::authenticate($username, $password);

        if ($user) {
            try {
                $token = TokenServices::generateToken($user->id);
                return $this->json(["token" => $token, "message" => "Authentification réussie"]);
            } catch (Exception $e) {
                return $this->json(["message" => "Erreur serveur"]);
            }
        }
        return $this->json(["message" => "Nom d'utilisateur / mot de passe incorrect"]);
    }

    #[Get('/profile/{token}')]
    public function getProfile(string $token): Response
    {
        return $this->json([
            'token' => $token,
            'username' => 'brice_user',
            'firstname' => 'Brice',
            'lastname' => 'Steve',
            'email' => 'bricesteve@gmail.com',
            'balance' => 500,
            'type' => 'normal'
        ]);
    }

    #[Put('/profile/{token}')]
    public function updateProfile(string $token): Response
    {
        $form = $this->buildForm();
        $firstname = $form->getValue('firstname');
        $lastname = $form->getValue('lastname');

        return $this->json([
            "token" => $token,
            "message" => "Profil mis à jour",
            "firstname" => $firstname,
            "lastname" => $lastname
        ]);
    }


    #[Put('/profile/{token}/password')]
    public function updatePassword(string $token): Response
    {
        $form = $this->buildForm();
        $oldPassword = $form->getValue('old_password');
        $newPassword = $form->getValue('new_password');

        return $this->json([
            "token" => $token,
            "message" => "Mot de passe modifié avec succès"
        ]);
    }

    #[Post('/profile/{token}/credits')]
    public function addCredits(string $token): Response
    {
        $form = $this->buildForm();
        $credit = $form->getValue('credit');

        return $this->json([
            "token" => $token,
            "message" => "$credit crédits ajoutés"
        ]);
    }


    #[Post('/profile/{token}/transactions')]
    public function makeTransaction(string $token): Response
    {
        $form = $this->buildForm();
        $name = $form->getValue('name');
        $price = $form->getValue('price');
        $quantity = $form->getValue('quantity');

        return $this->json([
            "token" => $token,
            "message" => "Transaction pour $name enregistrée",
            "total" => $price * $quantity
        ]);
    }

    #[Get('/profile/{token}/transactions')]
    public function getTransactions(string $token): Response
    {
        return $this->json([
            "token" => $token,
            "transactions" => [
                ["name" => "Meuble", "price" => 25, "quantity" => 2],
                ["name" => "Comode", "price" => 10, "quantity" => 5]
            ]
        ]);
    }

    #[Post('/profile/{token}/elevate')]
    public function elevateAccount(string $token): Response
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match("#^/profile/([a-zA-Z0-9]+)/elevate$#", $_SERVER['REQUEST_URI'], $matches)) {
            $token = $matches[1];
            $user = UserServices::getUserByToken($token);

            if (!$user) {
                http_response_code(400);
                echo json_encode(["mes
                sage" => "Jeton invalide."]);
                exit;
            }
            $transactions = UserServices::getUserTransactions($user['id']);
            $total = 0;
            foreach ($transactions as $transaction) {
                $total += $transaction['price'] * $transaction['quantity'];
            }

            if ($total >= 1000) {
                if ($user['type'] === 'PREMIUM') {
                    http_response_code(400);
                    echo json_encode(["message" => "Ce compte est déjà PREMIUM."]);
                    exit;
                }

                UserServices::elevateUserToPremium($user['id']);
                $newToken = TokenServices::generateToken($user['id']);

                http_response_code(200);
                echo json_encode([
                    "token" => $newToken,
                    "message" => "Élévation réussie. Le compte est maintenant PREMIUM."
                ]);
                exit;
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Le total des achats est insuffisant pour l’élévation."]);
                exit;
            }
        }
        return $this->json([
            "token" => $token,
            "message" => "Compte élevé à PREMIUM"
        ]);
    }

}
