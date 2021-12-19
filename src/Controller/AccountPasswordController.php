<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/compte/modifier-mot-de-passe", name="account_password")
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification = null;
        $user = $this->getUser();
        $passwordForm = $this->createForm(ChangePasswordType::class, $user);

        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $old_password = $passwordForm->get('old_password')->getData();
            if ($hasher->isPasswordValid($user, $old_password)) {
                $new_password = $passwordForm->get('new_password')->getData();
                $hashedPassword = $hasher->hashPassword($user, $new_password);
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();
                $notification = ['message' => 'Votre mot de passe a été mis à jour', 'color' => 'success'];
            }
            else {
                $notification = ['message' => 'Votre mot de passe actuel est incorrecte', 'color' => 'danger'];
            }
        }

        return $this->render('account/password.html.twig', [
            'passwordForm' => $passwordForm->createView(),
            'notification' => $notification
        ]);
    }
}
