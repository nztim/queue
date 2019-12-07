<?php

namespace NZTim\Queue\QueuedJob;

use Carbon\Carbon;

class QueuedJob
{
    private ?int $id;
    // base64-encoded, serialized version of the command
    private string $command;
    // how many tries before failing
    private int $attempts;
    private Carbon $created;
    private Carbon $updated;
    private ?Carbon $completed;

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private function __construct() {}

    public static function fromCommand(object $command, int $attempts = 5): QueuedJob
    {
        $job = new QueuedJob();
        $job->id = null;
        $job->command = base64_encode(serialize($command));
        $job->attempts = $attempts;
        $job->created = now();
        $job->updated = now();
        $job->completed = null;
        return $job;
    }

    public static function fromDb(object $data): QueuedJob
    {
        $job = new QueuedJob();
        $job->id = $data->id;
        $job->command = $data->command;
        $job->attempts = $data->attempts;
        $job->created = Carbon::createFromFormat(QueuedJob::DATE_FORMAT, $data->created);
        $job->updated = Carbon::createFromFormat(QueuedJob::DATE_FORMAT, $data->updated);
        $job->completed = $data->completed ? Carbon::createFromFormat(QueuedJob::DATE_FORMAT, $data->completed) : null;
        return $job;
    }

    public function toDb(): array
    {
        return [
            'command'   => $this->command,
            'attempts'  => $this->attempts,
            'created'   => $this->created,
            'updated'   => $this->updated,
            'completed' => $this->completed,
        ];
    }

    // Read -------------------------------------------------------------------

    public function id(): ?int
    {
        return $this->id;
    }

    public function command(): object
    {
        try {
            return unserialize(base64_decode($this->command));
        } catch (\Throwable $e) {
            throw new UnserializeError("Error unserializing id:" . $this->id() . "\n" . $e->getMessage());
        }
    }

    public function rawCommand(): string
    {
        return $this->command;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }

    public function failed(): bool
    {
        return $this->attempts === 0;
    }

    public function processingTime(): int
    {
        if (is_null($this->completed)) {
            return 0;
        }
        return $this->completed->diffInSeconds($this->created);
    }

    public function created(): Carbon
    {
        return $this->created;
    }

    public function updated(): Carbon
    {
        return $this->updated;
    }

    public function completed(): ?Carbon
    {
        return $this->completed;
    }

    // Write ------------------------------------------------------------------

    public function touch(): void
    {
        $this->updated = now();
    }

    public function setComplete(): void
    {
        $this->completed = now();
    }

    public function retry(): void
    {
        if (!$this->attempts) {
            $this->attempts++;
        }
    }

    public function decrementAttempts(): void
    {
        if ($this->attempts > 0) {
            $this->attempts--;
        }
    }
}
