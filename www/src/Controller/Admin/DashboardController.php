<?php

namespace App\Controller\Admin;

use App\Entity\Song;
use App\Entity\Album;
use App\Entity\Genre;
use App\Entity\Artist;
use App\Entity\Avatar;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{

    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //Redirection par defaut vers la liste des genres
        $url = $this->adminUrlGenerator
            ->setController(GenreCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/logo2.png" alt="logo du site" style="height: 50px; margin-right: 10px;" >  
            <span style="font-size: 18px; color: #4CAF50; font-weight: bold;" >Spotify Admin</span>
            ')
            ->setFaviconPath('images/logo2.png')
            ->renderContentMaximized(); // utilise tout l'espace de l'écran
    }

    public function configureMenuItems(): iterable
    {
        //menu principale
        yield MenuItem::linkToUrl('Accueil', 'fa fa-home', 'http://localhost:8083/admin');
        yield MenuItem::linkToUrl('Aller sur le Swagger', 'fa fa-code', 'http://localhost:8083/api')->setLinkTarget('_blank');;

        //1ere section "catalogue"
        yield MenuItem::section('Catalogue');
        // sous menu pour les genres
        yield MenuItem::subMenu('Genres', 'fa fa-tags')->setSubItems([
            MenuItem::linkToCrud('Ajouter un genre', 'fa fa-plus-circle', Genre::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Voir les genres', 'fa fa-eye', Genre::class)
        ]);
        // sous menu pour les albums
        yield MenuItem::subMenu('Albums', 'fa fa-record-vinyl')->setSubItems([
            MenuItem::linkToCrud('Ajouter un album', 'fa fa-plus-circle', Album::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Voir les albums', 'fa fa-eye', Album::class)
        ]);
        // sous-menu pour les chansons
        yield MenuItem::subMenu('Chansons', 'fa fa-music')->setSubItems([
            MenuItem::linkToCrud('Ajouter une chanson', 'fa fa-plus-circle', Song::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Voir les chansons', 'fa fa-eye', Song::class)
        ]);

        //2eme section "chanteur et avatars"
        yield MenuItem::section('Chanteurs et Avatars');
        // sous menu pour les chanteurs
        yield MenuItem::subMenu('Chanteurs', 'fa fa-user-plus')->setSubItems([
            MenuItem::linkToCrud('Ajouter un chanteur', 'fa fa-plus-circle', Artist::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Voir les chanteurs', 'fa fa-eye', Artist::class)
        ]);
        // sous menu pour les avatars
        yield MenuItem::subMenu('Avatars', 'fa fa-image')->setSubItems([
            MenuItem::linkToCrud('Ajouter un avatar', 'fa fa-plus-circle', Avatar::class)->setAction(Crud::PAGE_NEW),
            MenuItem::linkToCrud('Voir les avatars', 'fa fa-eye', Avatar::class)
        ]);
    }
}
