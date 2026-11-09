<?php

namespace App\Form;

use App\Entity\BudgetTransaction;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BudgetTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', null, [
                'label' => 'Bedrag (€)',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Vul een bedrag in.'
                    ),
                    new Assert\Positive(
                        message: 'Het bedrag moet groter zijn dan 0.'
                    )
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Categorie',
                'placeholder' => 'Kies een categorie',
                'required' => true,
                'constraints' => [
                    new Assert\NotNull(
                        message: 'Kies een categorie.'
                    )
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BudgetTransaction::class,
        ]);
    }
}
