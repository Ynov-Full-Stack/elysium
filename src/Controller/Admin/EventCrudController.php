<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\EventType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventCrudController extends AbstractCrudController
{
    public function __construct(
        #[Autowire] private TranslatorInterface $translator
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $typeChoices = array_combine(
            array_map(fn(EventType $type) => $type->trans($this->translator), EventType::cases()),
            EventType::cases()
        );

        $statusChoices = [
            'En cours' => 'ongoing',
            'Annulé' => 'cancelled',
        ];

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name', "Nom de l'événement")
                ->setColumns('col-md-6'),
            NumberField::new('price', 'Prix de la place (€)')
                ->setNumDecimals(2)
                ->setThousandsSeparator(' ')
                ->setDecimalSeparator(',')
                ->setStoredAsString(false)
                ->setColumns('col-md-2'),
            TextEditorField::new('description', "Description"),
            // Date
            FormField::addFieldset('Dates')
                ->setIcon('fa fa-calendar'),
            DateField::new('eventDate', "Date de l'événement")
                ->setColumns('col-md-4'),
            DateField::new('registrationStartAt', "Date d'ouverture des réservations")
                ->setColumns('col-md-4'),
            DateField::new('registrationEndAt', "Date de fermeture des réservations")
                ->setColumns('col-md-4'),
            // localisation
            FormField::addFieldset('Localisation')
                ->setIcon('fa fa-map-marker'),
            CountryField::new('country', "Pays")
                ->setColumns('col-md-4'),
            TextField::new('city', "Ville")
                ->setColumns('col-md-4'),
            TextField::new('postalCode', 'Code postal')
                ->setColumns('col-md-4'),
            TextField::new('streetNumber', 'Numéro de rue')
                ->setColumns('col-md-4'),
            TextField::new('street', 'Nom de la Rue')
                ->setColumns('col-md-4'),
            TextField::new('venueName', "Nom de l'établissement")
                ->setColumns('col-md-4'),
            // information
            FormField::addFieldset('Information complémentaire')
                ->setIcon('fa fa-info-circle'),
            ChoiceField::new('type', "Type d'événement")
                ->setChoices($typeChoices)
                ->setRequired(true)
            ->setColumns('col-md-4'),
            ChoiceField::new('status', "Statut")
                ->setChoices($statusChoices)
                ->setRequired(true)
                ->setColumns('col-md-4'),
            IntegerField::new('totalSeats', "Nombre total de place")
                ->setColumns('col-md-4'),
            // TODO : faut il récupéré tout les admin ou seulement celui connecté
            AssociationField::new('organizer', "Organisateur de l'événement")
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                return $queryBuilder
                    ->select('u')
                    ->from('App\Entity\User', 'u')
                    ->where('u.roles LIKE :role')
                    ->setParameter('role', '%"ROLE_ADMIN"%')
                    ->orderBy('u.lastname', 'ASC');
            }),

        ];
    }
}
