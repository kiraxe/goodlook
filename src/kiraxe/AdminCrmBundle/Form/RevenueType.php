<?php

namespace kiraxe\AdminCrmBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RevenueType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name',null ,array(
            "label" => "Название"
        ))
            ->add('amount', null ,array(
                "label" => "Сумма"
            ))
            ->add('type',ChoiceType::class ,array(
                "label" => "Вид оплаты",
                'choices'  => [
                    'Безналичный' => true,
                    'Наличный' => false,
                ]
            ))
            ->add('date',DateTimeType::class ,array(
                "label" => "Дата",
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
            ))
            ->add('comment',null ,array(
                "label" => "Комментарий"
            ));
    }/**
 * {@inheritdoc}
 */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'kiraxe\AdminCrmBundle\Entity\Revenue'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'kiraxe_admincrmbundle_revenue';
    }


}
