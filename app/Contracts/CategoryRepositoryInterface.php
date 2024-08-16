<?php

namespace App\Contracts;

interface CategoryRepositoryInterface
{
    public function getAllCategoriesWithReceiverProductsByType($type);
    public function getCategoriesHavingReceiverProductsByType($type);
}