<?php

namespace App\Controller\Admin;

use App\Entity\PlayUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PlayUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayUser::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
