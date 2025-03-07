<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UserAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        //on récupère les datas envoyées par le front
        $data = json_decode($request->getContent(), true);

        // On vérifie si l'utilisateur exite déja
        $existingUser = $entityManager
        ->getRepository(User::class)
        ->findOneBy(['email' => $data['email']]);

        if($existingUser){
            return new JsonResponse([
                'success' => false,
                'message' => 'L\'email est déjà utilisé'
            ]);
        }
        // On vérifie si l'utilisateur exite déja
        $existingNickname = $entityManager
        ->getRepository(User::class)
        ->findOneBy(['nickname' => $data['nickname']]);

        if($existingNickname){
            return new JsonResponse([
                'success' => false,
                'message' => 'Le nickname est déjà utilisé'
            ]);
        }

        // On vérifie que l'email est au format email
        $validEmail = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        if(!$validEmail){
            return new JsonResponse([
                'success' => false,
                'message' => 'L\'email n\'est pas valide'
            ]);
        }

        // On vérifie que le mdp n'est pas vide
        if(empty($data['password'])){
            return new JsonResponse([
                'success' => false,
                'message' => 'Le mot de passe ne peut pas être vide'
            ]);
        }

        //on crée un nouvel utilisateur
        $user = new User();
        //on lui set les paramètres
        $user->setEmail(strtolower($data['email']));
        $user->setNickname($data['nickname']);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $data['password']
            )
        );
        //on lui donne le paramètre de createdAt
        $user->setCreatedAt(time());
        //on persiste l'utilisateur
        $entityManager->persist($user);
        //on flush
        $entityManager->flush();

        //on retourne une réponse json
        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }

    #[Route('/check-password', name: 'app_check-password', methods: ['GET', 'POST'])]
    public function checkPassword(
        //on récupère les données du formulaire react
        Request $request,
        //on recupère le service d'encodage de mdp
        UserPasswordHasherInterface $userPasswordHasher,
        //on récupère le repo de User
        UserRepository $userRepository
    ): Response {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'];
        $password = $data['password'];
        $user = $userRepository->find($id);
        $isPasswordValid = $userPasswordHasher->isPasswordValid($user, $password);
        if ($isPasswordValid) {
            return $this->json([
                'message' => 'password is valid',
                'response' => true
            ]);
        } else {
            return $this->json([
                'message' => 'password is not valid',
                'response' => false
            ]);
        }
    }
}
