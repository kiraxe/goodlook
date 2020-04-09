<?php

namespace kiraxe\AdminCrmBundle\Form;

use kiraxe\AdminCrmBundle\Entity\Workers;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class WorkersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null ,array('label' => 'ФИО'))
            ->add('phone',TelType::class, array('label' => 'Телефон'))
            ->add('passport', null ,array(
                'label' => 'Паспортные данные',
                'required' => false,
            ))
            ->add('address', null ,array(
                'label' => 'Адрес',
                'required' => false,
            ))
            ->add('typeworkers', ChoiceType::class ,array(
                    'label' => 'Тип сотрудника',
                     'choices' => [
                         'Рабочий' => 0,
                         'Менеджер' => 1
                     ]
                )
            )
            ->add('managerpercent', CollectionType::class, array(
                'entry_type' => ManagerPercentType::class,
                'entry_options' => ['label' => false, 'attr' => ['class' => 'managerpercent-box']],
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'prototype_name' => "0"
            ))
            ->add('workerservice', CollectionType::class, [
                'entry_type' => WorkerServiceType::class,
                'entry_options' => ['label' => false, 'attr' => ['class' => 'workerservice-box']],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false
            ])
            ->add('freelancer', CheckboxType::class ,array(
                    'label' => 'Фрилансер',
                    'required' => false,
                )
            );
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Workers::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'kiraxe_admincrmbundle_workers';
    }


}
