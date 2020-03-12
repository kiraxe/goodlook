<?php


namespace kiraxe\AdminCrmBundle\Services\TableMeta;


class TableMeta
{

    private $tableName;

    public function getTableName($em): array
    {
        $this->tableName = [

            $em->getClassMetadata('kiraxeAdminCrmBundle:Orders')->getTableName() => "Заказ-наряд",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Calendar')->getTableName() => "Календарь",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Revenue')->getTableName() => "Доход",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Expenses')->getTableName() => "Расход",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Certificates')->getTableName() => "Сертификаты",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Materials')->getTableName() => "Материалы",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Workers')->getTableName() => "Сотрудники",
            $em->getClassMetadata('kiraxeAdminCrmBundle:Services')->getTableName() => "Услуги",
            "Автомобиль" => [
                $em->getClassMetadata('kiraxeAdminCrmBundle:Brand')->getTableName() => "Бренд автомобиля",
                $em->getClassMetadata('kiraxeAdminCrmBundle:Model')->getTableName() => "Модель автомобиля",
                $em->getClassMetadata('kiraxeAdminCrmBundle:BodyType')->getTableName() => "Тип кузова",
            ],
            $em->getClassMetadata('kiraxeAdminCrmBundle:Clientele')->getTableName() => "Клиенты",
            "Настройки" => [
                $em->getClassMetadata('kiraxeAdminCrmBundle:Measure')->getTableName() => "Единицы измерения",
                $em->getClassMetadata('kiraxeAdminCrmBundle:User')->getTableName() => "Пользователи",
            ]

        ];

        return $this->tableName;
    }
}