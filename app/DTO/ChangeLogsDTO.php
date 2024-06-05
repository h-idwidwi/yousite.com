<?php
namespace App\DTO;

class ChangeLogsDTO
{
    public $id;
    public $entity_type;
    public $entity_id;
    public $before;
    public $after;
    public $created_at;

    public function __construct($id, $entity_type, $entity_id, $before, $after, $created_at)
    {
        $this->id = $id;
        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id;
        $this->before = $before;
        $this->after = $after;
        $this->created_at = $created_at;
    }
}
