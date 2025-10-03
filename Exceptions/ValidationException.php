<?php
namespace Exceptions;

class ValidationException extends \Exception {
    private array $fields;

    public function __construct(string $message, array $fields = []) {
        parent::__construct($message);
        $this->fields = $fields;
    }

    public function getFields(): array {
        return $this->fields;
    }
}
?>
