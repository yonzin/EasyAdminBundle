<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Guesser;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ArrayFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ComparisonFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\DateTimeFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\EntityFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class DoctrineOrmFilterTypeGuesser extends DoctrineOrmTypeGuesser
{
    /**
     * {@inheritdoc}
     */
    public function guessType($class, $property)
    {
        if (!$ret = $this->getMetadata($class)) {
            return null;
        }

        /** @var ClassMetadataInfo $metadata */
        [$metadata, $name] = $ret;

        if ($metadata->hasAssociation($property)) {
            $multiple = $metadata->isCollectionValuedAssociation($property);
            $mapping = $metadata->getAssociationMapping($property);
            $options = ['value_type_options' => [
                'em' => $name,
                'class' => $mapping['targetEntity'],
                'multiple' => $multiple,
                'attr' => ['data-widget' => 'select2'],
            ]];
            if ($metadata->isSingleValuedAssociation($property)) {
                $options['value_type_options']['placeholder'] = 'label.form.empty_value';
            }

            return new TypeGuess(EntityFilterType::class, $options, Guess::HIGH_CONFIDENCE);
        }

        switch ($metadata->getTypeOfField($property)) {
            case Type::TARRAY:
            case Type::SIMPLE_ARRAY:
            case Type::JSON_ARRAY:
                return new TypeGuess(ArrayFilterType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::JSON:
                return new TypeGuess(TextFilterType::class, ['value_type' => TextareaType::class], Guess::MEDIUM_CONFIDENCE);
            case Type::BOOLEAN:
                return new TypeGuess(BooleanFilterType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::DATETIME:
            case Type::DATETIMETZ:
                return new TypeGuess(DateTimeFilterType::class, [], Guess::HIGH_CONFIDENCE);
            case Type::DATETIME_IMMUTABLE:
            case Type::DATETIMETZ_IMMUTABLE:
                return new TypeGuess(DateTimeFilterType::class, ['value_type_options' => ['input' => 'datetime_immutable']], Guess::HIGH_CONFIDENCE);
            case Type::DATEINTERVAL:
                return new TypeGuess(ComparisonFilterType::class, ['value_type' => DateIntervalType::class, 'comparison_type_options' => ['type' => 'datetime']], Guess::HIGH_CONFIDENCE);
            case Type::DATE:
                return new TypeGuess(DateTimeFilterType::class, ['value_type' => DateType::class], Guess::HIGH_CONFIDENCE);
            case Type::DATE_IMMUTABLE:
                return new TypeGuess(DateTimeFilterType::class, ['value_type' => DateType::class, 'value_type_options' => ['input' => 'datetime_immutable']], Guess::HIGH_CONFIDENCE);
            case Type::TIME:
                return new TypeGuess(DateTimeFilterType::class, ['value_type' => TimeType::class], Guess::HIGH_CONFIDENCE);
            case Type::TIME_IMMUTABLE:
                return new TypeGuess(DateTimeFilterType::class, ['value_type' => TimeType::class, 'value_type_options' => ['input' => 'datetime_immutable']], Guess::HIGH_CONFIDENCE);
            case Type::DECIMAL:
                return new TypeGuess(ComparisonFilterType::class, ['value_type' => NumberType::class, 'value_type_options' => ['input' => 'string']], Guess::MEDIUM_CONFIDENCE);
            case Type::FLOAT:
                return new TypeGuess(ComparisonFilterType::class, ['value_type' => NumberType::class], Guess::MEDIUM_CONFIDENCE);
            case Type::INTEGER:
            case Type::BIGINT:
            case Type::SMALLINT:
                return new TypeGuess(ComparisonFilterType::class, ['value_type' => IntegerType::class], Guess::MEDIUM_CONFIDENCE);
            case Type::STRING:
            case Type::GUID:
                return new TypeGuess(TextFilterType::class, [], Guess::MEDIUM_CONFIDENCE);
            case Type::TEXT:
            case Type::OBJECT:
            case Type::BLOB:
                return new TypeGuess(TextFilterType::class, ['value_type' => TextareaType::class], Guess::MEDIUM_CONFIDENCE);
        }

        return null;
    }
}