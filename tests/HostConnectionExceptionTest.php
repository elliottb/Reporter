<?php
namespace Reporter;
use Reporter;

class HostConnectionExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     */
    public function testException()
    {
        throw new HostConnectionException("Host Error");
    }
}
