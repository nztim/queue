<?php namespace NZTim\Queue\Commands;

use Illuminate\Support\Collection;
use NZTim\Queue\QueuedJob\QueuedJob;

class ListJobs
{
    public function table(Collection $entries)
    {
        $jobs = [];
        foreach ($entries as $entry) {
            /** @var QueuedJob $entry */
            $job['Created'] = $entry->created_at->format('Y-m-d - H:i');
            $job['ID'] =  "ID:{$entry->getId()}";
            $job['Class'] = get_class($entry->getJob());
            $job['Status'] = is_null($entry->deleted_at) ? "Incomplete" : "Complete";
            $job['Status'] .= " ({$entry->attempts})";
            if ($entry->attempts == 0) {
                $job['Status'] = "Failed!!!";
            }
            $jobs[] = $job;
        }
        return $jobs;
    }
}
