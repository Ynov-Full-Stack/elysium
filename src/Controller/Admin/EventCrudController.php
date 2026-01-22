<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            TextField::new('name')
                ->setLabel("Nom de l'événement"),
            TextEditorField::new('description')
                ->setLabel("Description"),
            DateField::new('eventDate')
                ->setLabel("Date de l'événement"),
            DateField::new('registrationStartDate')
                ->setLabel("Date d'ouverture des"),
            // Champ Enum pour EventType
            ChoiceField::new('type')
                ->setChoices(array_combine(
                    array_map(fn(EventType $type) => $type->value, EventType::cases()), // label
                    EventType::cases() // Enum
                ))
                ->setRequired(true)
                ->setFormTypeOption('choice_value', function (?EventType $choice) {
                    // transforme l'objet Enum en string pour le formulaire
                    return $choice?->value;
                })
                ->setFormTypeOption('choice_label', function (EventType $choice) {
                    // label affiché dans le formulaire
                    return $choice->value;
                }),

            TextField::new('city'),
            TextField::new('status'),
        ];
    }
}
