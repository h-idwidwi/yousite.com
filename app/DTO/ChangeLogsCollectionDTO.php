<?php
namespace App\DTO;

use Illuminate\Support\Collection;

class ChangeLogsCollectionDTO
{
    public $logs;

    public function __construct(Collection $logs)
    {
        $this->logs = $logs->map(function ($log) {
            return new ChangeLogsDTO(
                $log->id,
                $log->entity_type,
                $log->entity_id,
                json_decode($log->before, true),
                json_decode($log->after, true),
                $log->created_at
            );
        });
    }
}
