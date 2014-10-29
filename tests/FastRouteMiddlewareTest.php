<?php

namespace DajePHP\FastRouteMiddleware;

use FastRoute\Dispatcher;
use Symfony\Component\HttpFoundation\Response;

class FastRouteMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;
    private $request;
    private $middleware;

    public function setUp()
    {
        $this->dispatcher = $this->getMock('FastRoute\Dispatcher');
        $this->request    = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $this->middleware = new FastRouteMiddleware($this->dispatcher);
    }

    public function testHandleFound()
    {

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->request->expects($this->once())
            ->method('getRequestUri')
            ->will($this->returnValue('/hello/dajephp'));

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('POST', '/hello/dajephp')
            ->will($this->returnValue(
                    array(
                        Dispatcher::FOUND,
                        function ($params) {
                            return new Response();
                        },
                        array('param' => 'test')
                    )
                )
            );

        $response = $this->middleware->handle($this->request);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
    }

    public function testHandleNotFound()
    {
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->request->expects($this->once())
            ->method('getRequestUri')
            ->will($this->returnValue('/hello/notfound'));

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('POST', '/hello/notfound')
            ->will($this->returnValue(
                    array(
                        Dispatcher::NOT_FOUND
                    )
                )
            );

        $response = $this->middleware->handle($this->request);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not found.', $response->getContent());
    }

    public function testHandleNotAllowed()
    {
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('DELETE'));

        $this->request->expects($this->once())
            ->method('getRequestUri')
            ->will($this->returnValue('/hello/notallowed'));

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('DELETE', '/hello/notallowed')
            ->will($this->returnValue(
                    array(
                        Dispatcher::METHOD_NOT_ALLOWED,
                        array('GET', 'POST')
                    )
                )
            );

        $response = $this->middleware->handle($this->request);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('Method not allowed. Allowed methods: GET, POST', $response->getContent());
    }
}
