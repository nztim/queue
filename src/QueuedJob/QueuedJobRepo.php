<?php

namespace NZTim\Queue\QueuedJob;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use stdClass;

class QueuedJobRepo
{
    private Connection $db;
    private string $table = 'queuemgrjobs';

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    // Read -------------------------------------------------------------------

    public function findById(int $id): ?QueuedJob
    {
        $row = $this->db->table($this->table)->find($id);
        return $row ? QueuedJob::fromDb($row) : null;
    }

    /** @return Collection|QueuedJob[] */
    public function outstanding(): Collection
    {
        $rows = $this->db->table($this->table)->whereNull('completed')->where('attempts', '>', 0)->get();
        return $this->hydrate($rows);
    }

    public function recent(int $days): Collection
    {
        $rows = $this->db->table($this->table)
            ->where('created', '>', now()->subDays($days))
            ->orderBy('id', 'asc')
            ->get();
        return $this->hydrate($rows);
    }

    public function completed(int $hours = 24): Collection
    {
        $rows = $this->db->table($this->table)
            ->whereNotNull('completed')
            ->where('created', '>', now()->subHours($hours))
            ->get();
        return $this->hydrate($rows);
    }

    public function failed(): Collection
    {
        $rows = $this->db->table($this->table)
            ->where('attempts', 0)
            ->whereNull('completed')
            ->orderBy('id', 'asc')
            ->get();
        return $this->hydrate($rows);
    }

    /** @return QueuedJob[]|Collection */
    private function hydrate(Collection $rows): Collection
    {
        return $rows->map(function (stdClass $row) {
            return QueuedJob::fromDb($row);
        });
    }

    // Write ------------------------------------------------------------------

    public function persist(QueuedJob $job): int
    {
        $job->touch();
        $data = $job->toDb();
        if (is_null($job->id())) {
            return $this->db->table($this->table)->insertGetId($data);
        }
        $this->db->table($this->table)->where('id', $job->id())->update($data);
        return $job->id();
    }

    public function purgeCompleted(): void
    {
        $this->db->table($this->table)
            ->where('completed', '<', now()->subMonth())
            ->delete();
    }

    public function clearFailed(): void
    {
        $this->db->table($this->table)
            ->where('attempts', 0)
            ->whereNull('completed')
            ->update(['completed' => now()]);
    }

    public function delete(int $id): void
    {
        $this->db->table($this->table)
            ->where('id', $id)
            ->delete();
    }
}
