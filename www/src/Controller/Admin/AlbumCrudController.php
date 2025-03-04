<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AlbumCrudController extends AbstractCrudController
{
    //on crée nos constantes 
    public const ALBUM_BASE_PATH = 'upload/images/albums';
    public const ALBUM_UPLOAD_DIR = 'public/upload/images/albums';


    public static function getEntityFqcn(): string
    {
        return Album::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //permet de renommer les différentes pages
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des albums')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un album')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un album');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre de l\'album'),
            AssociationField::new('genre', 'Catégories de l\'album')
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Doctrine\ORM\PersistentCollection || is_array($value)) {
                        $labels = array_map(function ($genre) {
                            return $genre->getLabel();
                        }, $value->toArray());
                        return implode(', ', $labels);
                    }
                    return '';
                }),
            AssociationField::new('artist', 'Artiste'),
            ImageField::new('image_path', 'Image de l\'album')
                ->setBasePath(self::ALBUM_BASE_PATH)
                ->setUploadDir(self::ALBUM_UPLOAD_DIR)
                ->setUploadedFileNamePattern(
                    fn(UploadedFile $file): string => sprintf(
                        'upload_%d_%s.%s',
                        random_int(1, 999),
                        $file->getFilename(),
                        $file->guessExtension()
                    )
                ),
            DateField::new('releaseDate', 'Date de sortie')
            ->formatValue(function ($value) {
                return $value->format('d/m/Y') ?? ''; // au format: 12 janvier 2021
            }),
            IntegerField::new('createdAt', 'Date de création')
                ->formatValue(function ($value) {
                    return $value ? (new \DateTime("@$value"))->format('d/m/Y') : '';
                }),
            IntegerField::new('updatedAt', 'Date de modification')
                ->formatValue(function ($value) {
                    return $value ? (new \DateTime("@$value"))->format('d/m/Y') : '';
                }),
            AssociationField::new('songs', 'nbr de pistes')->hideOnForm(),
            BooleanField::new('isActive', 'En ligne'),
        ];
    }
    //fonction pour agir sur les boutons d'actions
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            //on redéfinit les boutons d'actions de la page index
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Ajouter')
                    ->setCssClass('btn btn-success')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) => $action
                    ->setIcon('fa fa-pen')
                    ->setLabel('Modifier')
            )
            ->remove(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn(Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            //on redéfinit les boutons d'actions de la page edit
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et quitter')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et continuer')
            )
            //on redéfinit les boutons d'actions de la page new
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et quitter')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et ajouter un nouveau')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn(Action $action) => $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('Voir')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn(Action $action) => $action
                    ->setIcon('fa fa-pen')
                    ->setLabel('Modifier')
            )
            ->remove(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn(Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::INDEX,
                fn(Action $action) => $action
                    ->setIcon('fa fa-list')
                    ->setLabel('Retour à la liste')
            );
    }

    //méthode pour persister les dates de creation 
    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        //si l'entité n'est pas de type Album, on ne fait rien
        if (!$entityInstance instanceof Album) return;
        // sinon on lui passe sa date de création en timestamp
        $entityInstance->setCreatedAt(time());
        parent::persistEntity($em, $entityInstance);
    }

    //méthode pour persister les dates de modification
    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        //si l'entité n'est pas de type Album, on ne fait rien
        if (!$entityInstance instanceof Album) return;
        // sinon on lui passe sa date de modification en timestamp
        $entityInstance->setUpdatedAt(time());
        parent::updateEntity($em, $entityInstance);
    }
}
