<?php

namespace kiraxe\AdminCrmBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class OrdersType extends AbstractType
{
    /**
     * {@inheritdoc}
     */

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateOpen', DateTimeType::class ,array(
            'label' => 'Дата и время открытия',
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'format' => 'yyyy-MM-dd',
            )
        )
            ->add('workeropen', EntityType::class , [
                'class' => 'kiraxe\AdminCrmBundle\Entity\Workers',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $workeropen) {
                    return $workeropen->createQueryBuilder('w')->where("w.typeworkers = 1")->andWhere('w.workeractive = 1');
                },
                'label' => 'Открытие заказа',
                'required' => false,
                'placeholder' => 'Выберите менеджера',
                'empty_data' => null,
            ])
            ->add('dateClose', DateTimeType::class ,array(
                    'label' => 'Дата и время закрытия',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'required' => false,
                )
            )
            ->add('datePayment', DateTimeType::class ,array(
                    'label' => 'Дата и время оплаты',
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'format' => 'yyyy-MM-dd',
                    'required' => false,
                )
            )
            ->add('workerclose', EntityType::class , [
                'class' => 'kiraxe\AdminCrmBundle\Entity\Workers',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $workerclose) {
                    return $workerclose->createQueryBuilder('w')->where("w.typeworkers = 1")->andWhere('w.workeractive = 1');
                },
                'label' => 'Закрытие заказа',
                'required' => false,
                'placeholder' => 'Выберите менеджера',
                'empty_data' => null,
            ])
            ->add('workerorders', CollectionType::class, [
                'entry_type' => WorkerOrdersType::class,
                'entry_options' => ['label' => false, 'attr' => ['class' => 'workerorder-box']],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'Сотрудник и вид работы'
            ])
            ->add('name', null ,array('label' => 'ФИО владельца авто', 'attr'=> ['class' => "ajax-autocomplete nameAvto", 'data-type' => 'name']))
            ->add('brandId', EntityType::class , [
                'class' => 'kiraxe\AdminCrmBundle\Entity\Brand',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $brand) {
                    return $brand->createQueryBuilder('b');
                },
                'label' => 'Бренд автомобиля',
                'required' => true,
                'attr'=> ['class' => "brandAvto"],
                'placeholder' => 'Выберите бренд',
                'empty_data' => null,
            ])
            ->add('carId', EntityType::class , [
                'class' => 'kiraxe\AdminCrmBundle\Entity\Model',
                'query_builder' => function (EntityRepository $model) {
                    return $model->createQueryBuilder('m');
                },
                'required' => true,
                'choice_label' => 'name',
                'label' => 'Модель автомобиля',
                'attr'=> ['class' => "modelAvto"],
                'placeholder' => 'Выберите модель автомобиля'
            ])
            ->add('bodyId', EntityType::class , [
                'class' => 'kiraxe\AdminCrmBundle\Entity\BodyType',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $bodyType) {
                    return $bodyType->createQueryBuilder('b');
                },
                'label' => 'Тип кузова',
                'required' => true,
                'attr'=> ['class' => "bodyId"],
                'placeholder' => 'Выберите тип кузова',
                'empty_data' => null,
            ])
            ->add('color', TextType::class ,array('label' => 'Цвет', 'attr'=> ['class' => "colorAvto"],))
            ->add('number', TextType::class ,array('label' => 'Номер авто', 'attr'=> ['class' => "numberAvto ajax-autocomplete", 'data-type' => 'number'],))
            ->add('vin', TextType::class ,array(
                'label' => 'VIN',
                'attr'=> ['class' => "vinAvto ajax-autocomplete", 'data-type' => 'vin'],
                'required' => false,
            ))
            ->add('description', TextareaType::class ,array(
                'label' => 'Комплектность ДТС, ценные вещи, которые в нем находятся',
                'required' => false,
            ))
            ->add('damages', TextareaType::class ,array(
                'label' => 'При приеме ДТС имеет следующие повреждения',
                'required' => false,
                'empty_data' => 'Автомобиль загрязнен. Фотофиксация после мойки кузова автомобиля.',
                /*'attr' => array(
                    'placeholder' => 'Автомобиль загрязнен. Фотофиксация после мойки кузова автомобиля.'
                )*/
            ))
            ->add('recommendations', TextareaType::class ,array(
                'label' => 'Рекомендации',
                'required' => false,
            ))
            ->add('note', TextareaType::class ,array(
                'label' => 'Примечание',
                'required' => false,
            ))
            ->add('images', FileType::class, [
                'label' => 'Фотографии',
                'multiple' => true,
                'required' => false,
            ])
            ->add('phone',TextType::class ,array('label' => 'Телефон', 'attr'=> ['class' => "phone ajax-autocomplete" , 'data-type' => 'phone']))
            ->add('email', EmailType::class ,array(
                'label' => 'E-mail',
                'attr'=> ['class' => "email"],
                'required' => false,
            ))
            ->add('payment',ChoiceType::class ,array(
                "label" => "Вид оплаты",
                'choices'  => [
                    'Эквайринг' => 2,
                    'Оплата по счету' => 1,
                    'Наличный' => 0,
                ]
            ))
            ->add('price', HiddenType::class ,array('label' => 'Стоимость'))
            ->add('notification', null ,array('label' => 'Оповещение'))
            ->add('close', null ,array('label' => 'Закрыт'));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'kiraxe\AdminCrmBundle\Entity\Orders'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'kiraxe_admincrmbundle_orders';
    }


}
