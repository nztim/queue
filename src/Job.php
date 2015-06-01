<?php namespace NZTim\Queue;

interface Job
{
    public function handle();
}