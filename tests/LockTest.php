<?php namespace Packages\Queue\tests;

use NZTim\Queue\Lock;
use PHPUnit_Framework_TestCase;

class LockTest extends PHPUnit_Framework_TestCase
{
    /** @var Lock */
    protected $lock;

    /** @before */
    public function setup_lock()
    {
        $this->lock = new Lock($this->lockfile());
        $this->lock->clear();
    }

    protected function lockfile() : string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'nztqueuemgr.lock';
    }

    protected function lockdata() : array
    {
        return explode('|', file_get_contents($this->lockfile()));
    }

    /** @test */
    public function set_lockfile_missing()
    {
        $this->lock->set(10);
        $this->assertTrue(file_exists($this->lockfile()));
        $this->assertTrue(abs((time() + (10 * 60)) - intval($this->lockdata()[0])) < 1);
        $this->assertEquals($this->lockdata()[1], Lock::STATUS_EXEC);
    }

    /** @test */
    public function set_lockfile_zero()
    {
        $this->lock->clear();
        $this->assertEquals("0", file_get_contents($this->lockfile()));
        $this->lock->set(10);
        $this->assertTrue(abs((time() + (10 * 60)) - intval($this->lockdata()[0])) < 1);
        $this->assertEquals(Lock::STATUS_EXEC, $this->lockdata()[1]);
    }

    /** @test */
    public function set_lockfile_paused()
    {
        $this->lock->pause(10);
        $this->assertTrue(abs((time() + (10 * 60)) - intval($this->lockdata()[0])) < 1);
        $this->assertEquals($this->lockdata()[1], Lock::STATUS_PAUSED);
    }

    /** @test */
    public function is_locked()
    {
        $this->assertTrue($this->lock->set(10));
        $this->assertFalse($this->lock->set(10));
        $this->lock->clear();
        $this->assertTrue($this->lock->set(10));
    }

    /** @test */
    public function is_paused()
    {
        $this->assertTrue($this->lock->pause(10));
        $this->assertFalse($this->lock->pause(10));
        $this->lock->clear();
        $this->assertTrue($this->lock->pause(10));
    }

    /** @test */
    public function lock_timeouts()
    {
        $data = strval(time() - 1) . '|' . Lock::STATUS_EXEC;
        file_put_contents($this->lockfile(), $data);
        $this->assertTrue($this->lock->set(10));
        $this->assertTrue(abs((time() + (10 * 60)) - intval($this->lockdata()[0])) < 1);
        $this->assertEquals($this->lockdata()[1], Lock::STATUS_EXEC);
    }

    /** @test */
    public function pause_timeouts()
    {
        $data = strval(time() - 1) . '|' . Lock::STATUS_PAUSED;
        file_put_contents($this->lockfile(), $data);
        $this->assertTrue($this->lock->pause(10));
        $this->assertTrue(abs((time() + (10 * 60)) - intval($this->lockdata()[0])) < 1);
        $this->assertEquals($this->lockdata()[1], Lock::STATUS_PAUSED);
    }

    /** @test */
    public function resume_doesnt_release_exec_lock()
    {
        $this->lock->set(10);
        $this->assertFalse($this->lock->resume());
        $this->assertFalse($this->lock->set(10));
    }

    /** @test */
    public function resume_releases_pause()
    {
        $this->lock->pause(10);
        $this->assertFalse($this->lock->set(10));
        $this->assertTrue($this->lock->resume());
        $this->assertTrue($this->lock->set(10));
    }
}
