<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;

class CategoryFixture
{
    /**
     * @var CategoryInterface
     */
    private $category;

    /**
     * @return CategoryInterface
     */
    public function getCategory(): CategoryInterface
    {
        return $this->category;
    }

    public function __construct(CategoryInterface $category)
    {
        $this->category = $category;
    }

    public function getId() : int
    {
        return $this->category->getId();
    }

    public function getUrlKey() : string
    {
        /** @var Category $category */
        $category = $this->category;
        return $category->getUrlKey();
    }
}
