<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Entity\PlayUser;
use App\Entity\Token;
use App\Entity\User;
use App\Entity\Card;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Toumai');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-id-badge', User::class);
        yield MenuItem::section('Jeu');
        yield MenuItem::linkToCrud('Interface joueur', 'fa fa-address-card', PlayUser::class);
        yield MenuItem::linkToCrud('Partie', 'fa fa-game', Game::class);
        yield MenuItem::section('Ressources de jeu');
        yield MenuItem::linkToCrud('Cartes', 'fa fa-address-card', Card::class);
        yield MenuItem::linkToCrud('Jetons', 'fa fa-address-card', Token::class);

    }
}
