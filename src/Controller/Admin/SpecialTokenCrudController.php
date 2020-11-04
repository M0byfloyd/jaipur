<?php

namespace App\Controller\Admin;

use App\Entity\SpecialToken;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SpecialTokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SpecialToken::class;
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
