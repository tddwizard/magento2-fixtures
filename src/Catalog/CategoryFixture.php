<?php

namespace TddWizard\Fixtures\Catalog;

use Magento\Catalog\Api\Data\CategoryInterface;

class CategoryFixture
{
    /**
     * @var CategoryInterface
     */
    private $category;

    public function __construct(CategoryInterface $category)
    {
        $this->category = $category;
    }

    public function getId() : int
    {
        return $this->category->getId();
    }

}
