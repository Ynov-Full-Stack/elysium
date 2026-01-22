<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('event', 'Événement'),
            IntegerField::new('seatQuantity', 'Nombre de places'),
            DateField::new('createdAt', 'Créée le'),
            AssociationField::new('user', 'Utilisateur'),
            TextField::new('stripeSessionId', 'Session Stripe'),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En cours' => 'en_cours',
                    'Annulée' => 'annulee',
                ])
                ->renderExpanded(false)
                ->renderAsBadges([
                    'en_cours' => 'success',
                    'annulee' => 'danger',
                ]),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE);
    }

}
