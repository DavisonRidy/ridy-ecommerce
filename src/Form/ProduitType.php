<?php

namespace App\Form;

use App\Entity\Type;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type',EntityType::class,[
                'class' => Type::class,
                'choice_label' => 'genre'
            ])
            ->add('nom')
            ->add('prix')
            ->add('prixInitial')
            ->add('stock')
            ->add('description')
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image.',
                'download_uri' => false,
                'imagine_pattern' => 'image_edit'         
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
