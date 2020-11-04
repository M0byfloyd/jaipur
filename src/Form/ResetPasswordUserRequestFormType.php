<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordUserRequestFormType extends AbstractType
{
    private $user;
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->user = $options['user'];
        $builder
            ->add('email', HiddenType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email',
                    ]),
                ],
                'data' => $this->user->getEmail()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'=>User::class,
            'user'=>null
        ]);
    }
}
