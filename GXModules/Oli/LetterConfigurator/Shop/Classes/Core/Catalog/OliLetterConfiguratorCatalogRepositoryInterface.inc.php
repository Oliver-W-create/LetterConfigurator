<?php

/**
 * Read-only access to the active configurator master data of a product template.
 */
interface OliLetterConfiguratorCatalogRepositoryInterface
{
    /**
     * @param int $productTemplateId
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function loadForProductTemplate($productTemplateId);
}
