<?php

namespace NZTim\Queue\Commands;

use Illuminate\Support\Collection;
use NZTim\Queue\QueuedJob\QueuedJob;

class MakeTable
{
    public function fromJobs(Collection $jobs): array
    {
        $rows = [];
        foreach ($jobs as $job) {
            /** @var QueuedJob $job */
            $row['Created'] = $job->created()->format('Y-m-d - H:i');
            $row['ID'] =  "ID:{$job->id()}";
            $row['Class'] = get_class($job->command());
            $row['Status'] = is_null($job->completed()) ? "Incomplete" : "Complete";
            $row['Status'] .= " ({$job->attempts()})";
            if (!$job->attempts()) {
                $row['Status'] = "Failed!!!";
            }
            $row['Completed'] = $job->completed() ? "Yes" : '';
            $rows[] = $row;
        }
        return $rows;
    }
}
