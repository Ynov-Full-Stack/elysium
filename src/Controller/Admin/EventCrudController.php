<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\EventType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            TextField::new('name', "Nom de l'événement")
                ->setColumns('col-md-6'),

            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->onlyOnForms(),

            TextEditorField::new('description'),

            FormField::addFieldset('Dates')->setIcon('fa fa-calendar'),
            DateField::new('eventDate', "Date de l'événement"),
            DateField::new('registrationStartAt', "Ouverture des réservations")->onlyOnForms(),
            DateField::new('registrationEndAt', "Fermeture des réservations")->onlyOnForms(),

            FormField::addFieldset('Localisation')->setIcon('fa fa-map-marker'),
            CountryField::new('country')->onlyOnForms(),
            TextField::new('city', "Ville"),
            TextField::new('postalCode')->onlyOnForms(),
            TextField::new('streetNumber')->onlyOnForms(),
            TextField::new('street')->onlyOnForms(),
            TextField::new('venueName')->onlyOnForms(),
            FormField::addFieldset('Informations')->setIcon('fa fa-info-circle'),
            ChoiceField::new('type', "Type d'événement")
                ->setChoices(array_combine(
                    array_map(fn(EventType $t) => $t->label(), EventType::cases()),
                    EventType::cases()
                ))
                ->setRequired(true)
                ->formatValue(fn(?EventType $type, $entity) => $type?->label() ?? ''),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En cours' => 'en cours',
                    'Annulé' => 'annulé',
                ]),
            IntegerField::new('totalSeats')->onlyOnForms(),
            AssociationField::new('organizer', 'Organisateur')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->select('u')
                        ->from('App\Entity\User', 'u')
                        ->orderBy('u.displayName', 'ASC');
                }),
            DateField::new('createdAt', 'Créé le')->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::DELETE);
    }
}
