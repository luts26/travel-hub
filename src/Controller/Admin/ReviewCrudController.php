<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Enum\ReviewStatus;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\ORM\EntityManagerInterface;

class ReviewCrudController extends AbstractCrudController
{
    public function __construct(private CacheItemPoolInterface $cachePool) {}

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        $this->cachePool->deleteItem('trip_stats_' . $entityInstance->getTrip()->getId());
    }

    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', 'Статус')->setChoices(
                array_combine(
                    array_map(fn (ReviewStatus $s) => $s->label(), ReviewStatus::cases()),
                    array_map(fn (ReviewStatus $s) => $s->value, ReviewStatus::cases()),
                )
            ));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('trip', 'Подорож'),
            ChoiceField::new('status', 'Статус')
                ->setChoices(array_combine(
                    array_map(fn (ReviewStatus $s) => $s->label(), ReviewStatus::cases()),
                    ReviewStatus::cases(),
                ))
                ->renderAsBadges([
                    ReviewStatus::Submitted->value => 'warning',
                    ReviewStatus::Published->value => 'success',
                    ReviewStatus::Rejected->value  => 'danger',
                ]),
            TextField::new('author', 'Автор'),
            EmailField::new('email', 'Email'),
            TextEditorField::new('comment', 'Коментар'),
            IntegerField::new('rating', 'Рейтинг'),
            DateTimeField::new('createdAt', 'Дата створення')->hideOnForm(),
        ];
    }
}
