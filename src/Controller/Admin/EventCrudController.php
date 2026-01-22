<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
            TextField::new('name')
            ->setLabel("Nom de l'événement"),
            TextEditorField::new('description')
            ->setLabel("Description"),
            DateField::new('eventDate')
            ->setLabel("Date de l'événement"),
            DataField::new('registrationStartDate')
            ->setLabel("Date d'ouverture des")

        ];
    }

}
