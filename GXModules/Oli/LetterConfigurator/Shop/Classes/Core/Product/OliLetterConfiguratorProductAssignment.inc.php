<?php

/**
 * Immutable assignment between a Gambio product and a configurator definition.
 */
final class OliLetterConfiguratorProductAssignment implements JsonSerializable
{
    private int $assignmentId;
    private int $productsId;
    private int $productTemplateId;
    private string $configuratorType;
    private bool $active;

    public function __construct(
        int $assignmentId,
        int $productsId,
        int $productTemplateId,
        string $configuratorType = 'contour_text',
        bool $active = true
    ) {
        $this->assignmentId = max(0, $assignmentId);
        $this->productsId = max(0, $productsId);
        $this->productTemplateId = max(0, $productTemplateId);
        $this->configuratorType = trim($configuratorType) !== '' ? trim($configuratorType) : 'contour_text';
        $this->active = $active;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['assignment_id'] ?? 0),
            (int)($data['products_id'] ?? 0),
            (int)($data['product_template_id'] ?? 0),
            (string)($data['configurator_type'] ?? 'contour_text'),
            (bool)($data['is_active'] ?? false)
        );
    }

    public function getAssignmentId(): int { return $this->assignmentId; }
    public function getProductsId(): int { return $this->productsId; }
    public function getProductTemplateId(): int { return $this->productTemplateId; }
    public function getConfiguratorType(): string { return $this->configuratorType; }
    public function isActive(): bool { return $this->active; }

    public function jsonSerialize(): array
    {
        return [
            'assignmentId' => $this->assignmentId,
            'productsId' => $this->productsId,
            'productTemplateId' => $this->productTemplateId,
            'configuratorType' => $this->configuratorType,
            'active' => $this->active,
        ];
    }
}
