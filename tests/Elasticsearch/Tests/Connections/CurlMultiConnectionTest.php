<?php
/**
 * User: zach
 * Date: 5/7/13
 * Time: 2:19 PM
 */

namespace Elasticsearch\Tests\Connections;
use Elasticsearch;
use Mockery as m;

/**
 * Class CurlMultiConnectionTest
 *
 * @category   Tests
 * @package    Elasticsearch
 * @subpackage Tests/Sniffers
 * @author     Zachary Tong <zachary.tong@elasticsearch.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache2
 * @link       http://elasticsearch.org
 */
class CurlMultiConnectionTest extends \PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    /**
     * Test no multihandle
     *
     * @expectedException \Elasticsearch\Common\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage curlMultiHandle must be set in connectionParams
     *
     * @covers \Elasticsearch\Connections\CurlMultiConnection::performRequest
     * @return void
     */
    public function testNoMultihandle()
    {
        $host = 'localhost';
        $port = 9200;
        $connectionParams = null;

        $log = $this->getMockBuilder('\Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $connection = new Elasticsearch\Connections\CurlMultiConnection($host, $port, $connectionParams, $log, $log);

    }//end testNoMultihandle()


    /**
     * Test bad host name
     *
     * @expectedException \Elasticsearch\Common\Exceptions\TransportException
     *
     * @covers \Elasticsearch\Connections\CurlMultiConnection::performRequest
     * @return void
     */
    public function testBadHost()
    {
        $host = 'localhost5';
        $port = 9200;
        $connectionParams['curlMultiHandle'] = curl_multi_init();

        $log = $this->getMockBuilder('\Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $connection = new Elasticsearch\Connections\CurlMultiConnection($host, $port, $connectionParams, $log, $log);
        $ret = $connection->performRequest('GET', '/');

    }//end testBadHost()


    /**
     * Test bad port number
     *
     * @expectedException \Elasticsearch\Common\Exceptions\TransportException
     *
     * @covers \Elasticsearch\Connections\CurlMultiConnection::performRequest
     * @return void
     */
    public function testBadPort()
    {
        $host = 'localhost';
        $port = 9800;
        $connectionParams['curlMultiHandle'] = curl_multi_init();
        $log = $this->getMockBuilder('\Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $connection = new Elasticsearch\Connections\CurlMultiConnection($host, $port, $connectionParams, $log, $log);
        $ret = $connection->performRequest('GET', '/');

    }

    public function testPingTimeout()
    {

        $host = 'localhost';
        $port = 9800;

        $opts = array();
        $connectionParams['curlMultiHandle'] = curl_multi_init();

        $argsValidator = function($args) {
            $this->assertEquals($args[155], 5000);
            $this->assertEquals($args[156], 5000);

            return true;
        };

        $log = m::mock('Psr\Log\LoggerInterface')->shouldReceive('debug')->with("Curl Options:", \Mockery::on($argsValidator))->getMock();
        $trace = m::mock('Psr\Log\LoggerInterface');

        $connection = new Elasticsearch\Connections\CurlMultiConnection($host, $port, $connectionParams, $log, $trace);
        try{
            $ret = $connection->performRequest('GET', '/', null, null, array('timeout' => 5000));
        } catch (\Exception $e) {

        }


    }


}