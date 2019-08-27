<?php

namespace App\Forms;

use App\Entity\Rate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RateType extends AbstractType
{
    /** string */
    private $baseCurrency;

    public function __construct(string $baseCurrency)
    {
        $this->baseCurrency = $baseCurrency;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currency', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 3,
                    'maxlength' => 3,
                    'placeholder' => 'Enter custom currency name (e.g. EUR)'
                ]
            ])
            ->add('rate', NumberType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter rate (based on ' . $this->baseCurrency . ')',
                    'min' => 0
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-primary btn-block',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rate::class,
            'html5' => true
        ]);
    }
}
