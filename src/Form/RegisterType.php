<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', TextType::class)
            ->add('roles', ChoiceType::class,[
                'choices'  =>[
                    'gestion' => 'ROLE_GESTION',
                    'vendeur' => 'ROLE_VENDOR',
                    'admin' => 'ROLE_ADMIN',
                    'utilisateur' => 'ROLE_USER'
                ],
                'required' => true,
                'expanded'  => false,
                'multiple'  => true 
            ])
            ->add('password', PasswordType::class)
            ->add('confirm', PasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
