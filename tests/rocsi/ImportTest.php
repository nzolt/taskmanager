<?php

namespace RoCsI;
use Slim\Environment;
use Guzzle\Service\Command\Factory;

date_default_timezone_set('UTC');

/**
 * Class ImportTest
 * @package RoCsI
 * UnitTest for Email Service from C#
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    protected $app;
    protected $_rocsi;
    protected $config;
    protected $testConfig;

    /**
     * Create Import class Instance and load test config values
     */
    public function __construct(){

        $this->config = new \RoCsI\TestConfig();
        $this->app = new \Slim\Slim(array(
            'view' => new \RoCsI\View(),
            'templates.path' => APP_HOME . '/templates',
            'debug' => true,
            'log.enabled' => true,
            'Name' => 'RoCsI',
        ));
        $this->app->config($this->config->getConfig());

        $this->receivedMessage = array('Messages' =>
            array(
                array(
                    'ReceiptHandle' => '43ec5f1d-7f7b-440c-9c17-0b3fe5f6dace',
                    'Body' => '["XX","N","1234567890","BRANCH.556xxx@KURTGEIGER.COM","001571168","MISS","ALICE","TULLY","5664547209","CALLY","NINEWEST","TAUPE","7","KG BRENT CROSS","UNIT B3","BRENT CROSS SHOPPING CENTRE","BRENT CROSS","LONDON","NW4  3FQ","0556","KG BRENT CROSS","02082020852","15505991084605C"]'
                )
            )
        );

        $mockSqsClient = $this->getMockBuilder('Aws\Sqs\SqsClient')
            ->disableOriginalConstructor()
            ->setMethods(array('getQueueUrl', 'receiveMessage', '_deleteMessage', 'getConfig'))
            ->getMock();
        $mockSqsClient->expects($this->any())
            ->method('getQueueUrl')
            ->will($this->returnValue(array('QueueUrl' => "https://sqs.eu-west-1.amazonaws.com/558450988367/kg_email_service")));
        $mockSqsClient->expects($this->any())
            ->method('receiveMessage')
            ->will($this->returnValue($this->receivedMessage));

        $mockSqsClient->expects($this->any())
            ->method('_deleteMessage')
            ->with(array(
                'QueueUrl' => 'dumyUrl',
                'ReceiptHandle' => 'justADumyHandle'))
            ->will($this->returnValue(TRUE));

        $this->testConfig = $this->config->getConfig();

        $this->_rocsi = new \RoCsI\Importer($this->app, $mockSqsClient);
    }

    public function testAwsConfigSetup()
    {
        $this->assertEquals($this->testConfig['awsConfig'], $this->_rocsi->getAwsConfig());
    }

    public function testQueueUrl()
    {
        $this->assertEquals("https://sqs.eu-west-1.amazonaws.com/558450988367/".$this->testConfig['awsConfig']["QueueName"], $this->_rocsi->getQueueUrl());
    }

    public function testGetGender()
    {
        $this->assertEquals('F', $this->_rocsi->_getGender('MISS'));
        $this->assertEquals('M', $this->_rocsi->_getGender('MR'));
    }

    public function testLineMapping()
    {
        $this->_rocsi->_doSqsQuery();
        $line = $this->_rocsi->getLines();
        $this->assertEquals(
            array(
                'ENV_KEY' => "XX",
                'CVSCARDNUMBER_FIELD' => "1234567890",
                'EMAIL_FIELD' => "BRANCH.556xxx@KURTGEIGER.COM",
                'CVSORDERREF_FIELD' => "001571168",
                'TITLE_FIELD' => "Miss",
                'GENDER_FIELD' => "F",
                'FIRSTNAME_FIELD' => "Alice",
                'LASTNAME_FIELD' => "Tully",
                'COP_LINENO_FIELD' => "5664547209",
                'CVSLINENAME_FIELD' => "CALLY",
                'CVSBRAND_FIELD' => "NINEWEST",
                'CVSCOLOUR_FIELD' => "TAUPE",
                'CVSSIZE_FIELD' => "7",
                'DELIVERYADDRESS1_FIELD' => "Kg brent cross",
                'DELIVERYADDRESS2_FIELD' => "Unit b3",
                'DELIVERYADDRESS3_FIELD' => "Brent cross shopping centre",
                'DELIVERYADDRESS4_FIELD' => "Brent cross",
                'DELIVERYTOWNCITY_FIELD' => "London",
                'DELIVERYPOSTCODE_FIELD' => "NW4  3FQ",
                'CVSREQSTORENO_FIELD' => "0556",
                'CVSREQSTORENAME_FIELD' => "KG BRENT CROSS",
                'CVSRECVSTORENAME_FIELD' => "KG BRENT CROSS",
                'CVSREQSTOREPHONE_FIELD' => "02082020852",
                'PROMOCODE_FIELD' => date('d/m/Y'),
            ),
            $this->_rocsi->_mapLine(json_decode($line[0]['body']))
        );
    }

    public function testEmailSendingUrl()
    {
        $this->_rocsi->_doSqsQuery();
        $line = $this->_rocsi->getLines();
        $this->assertEquals(
            'http://p3tre.emv3.com/D2UTF8?emv_tag=63299580800033EF&emv_ref=EdX7CqkdF_r48SA9MOPQLarRK05zFajL-jDde6k1W7WvKRw&ENV_KEY=XX&CVSCARDNUMBER_FIELD=1234567890&EMAIL_FIELD=BRANCH.556xxx@KURTGEIGER.COM&CVSORDERREF_FIELD=001571168&TITLE_FIELD=Miss&GENDER_FIELD=F&FIRSTNAME_FIELD=Alice&LASTNAME_FIELD=Tully&COP_LINENO_FIELD=5664547209&CVSLINENAME_FIELD=CALLY&CVSBRAND_FIELD=NINEWEST&CVSCOLOUR_FIELD=TAUPE&CVSSIZE_FIELD=7&DELIVERYADDRESS1_FIELD=Kg%20brent%20cross&DELIVERYADDRESS2_FIELD=Unit%20b3&DELIVERYADDRESS3_FIELD=Brent%20cross%20shopping%20centre&DELIVERYADDRESS4_FIELD=Brent%20cross&DELIVERYTOWNCITY_FIELD=London&DELIVERYPOSTCODE_FIELD=NW4%20%203FQ&CVSREQSTORENO_FIELD=0556&CVSREQSTORENAME_FIELD=KG%20BRENT%20CROSS&CVSRECVSTORENAME_FIELD=KG%20BRENT%20CROSS&CVSREQSTOREPHONE_FIELD=02082020852&PROMOCODE_FIELD='.date('d/m/Y'),
            $this->_rocsi->_getURI($this->_rocsi->_mapLine(json_decode($line[0]['body'])))
        );
    }

    public function testEmailCredentialsByEnvKey()
    {
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=4510CAC0400005A5&emv_ref=EdX7CqkdF_rr8SA9MOPQLarTLU16b9yw-jzde6k2XbHcKXI&ENV_KEY=SS", $this->_rocsi->_getURI(array("ENV_KEY"=>'SS')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=18F7DA2665602000&emv_ref=EdX7CqkdF_s08SA9MOPQLarWIDp9aNzB_D7Yfak0WMDZJlY&ENV_KEY=SK", $this->_rocsi->_getURI(array("ENV_KEY"=>'SK')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=86C9B5342B010086&emv_ref=EdX7CqkdF_sz8SA9MOPQLarfLj9zbqjA_jqve6g2WMjfJlA&ENV_KEY=OR", $this->_rocsi->_getURI(array("ENV_KEY"=>'OR')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=2F0D0AC0400023A2&emv_ref=EdX7CqkdF_sy8SA9MOPQLarVXkwOHNyw-jzde6k0W7HbJm4&ENV_KEY=DP", $this->_rocsi->_getURI(array("ENV_KEY"=>'DP')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=E09A1580800073A4&emv_ref=EdX7CqkdF_sx8SA9MOPQLaqiKEULHajL-jDde6kxW7HdJlU&ENV_KEY=LA", $this->_rocsi->_getURI(array("ENV_KEY"=>'LA')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=1A26596685602001&emv_ref=EdX7CqkdF_sw8SA9MOPQLarWWU58GaTF_DDYfak0WMDYJk4&ENV_KEY=DC", $this->_rocsi->_getURI(array("ENV_KEY"=>'DC')));
        $this->assertEquals("http://p3tre.emv3.com/D2UTF8?emv_tag=63299580800033EF&emv_ref=EdX7CqkdF_r48SA9MOPQLarRK05zFajL-jDde6k1W7WvKRw&ENV_KEY=XX", $this->_rocsi->_getURI(array("ENV_KEY"=>'XX')));
    }

    /**
     * Test Import class most functions if exists
     */
    public function testImportCSV()
    {
        $this->assertMethodExist($this->_rocsi, 'doImport');
        $this->assertMethodExist($this->_rocsi, '_processLines');
        $this->assertMethodExist($this->_rocsi, '_createSqsClient');
        $this->assertMethodExist($this->_rocsi, '_sendRequest');
        $this->assertMethodExist($this->_rocsi, '_getGender');
        $this->assertMethodExist($this->_rocsi, '_doSqsQuery');
        $this->assertMethodExist($this->_rocsi, '_deleteMessage');
        $this->assertMethodExist($this->_rocsi, '_mapLine');
        $this->assertMethodExist($this->_rocsi, '_getURI');
    }

    public function testSqsQueryResponse()
    {
        $this->_rocsi->_doSqsQuery();
        $expectedLine = array(
            array(
                'key' => '43ec5f1d-7f7b-440c-9c17-0b3fe5f6dace',
                'body' => '["XX","N","1234567890","BRANCH.556xxx@KURTGEIGER.COM","001571168","MISS","ALICE","TULLY","5664547209","CALLY","NINEWEST","TAUPE","7","KG BRENT CROSS","UNIT B3","BRENT CROSS SHOPPING CENTRE","BRENT CROSS","LONDON","NW4  3FQ","0556","KG BRENT CROSS","02082020852","15505991084605C"]'
            )
        );
        $this->assertEquals($expectedLine, $this->_rocsi->getLines());
    }

    /**
     * Assert that a class has a method
     *
     * @param string $class name of the class
     * @param string $method name of the searched method
     * @throws ReflectionException if $class don't exist
     * @throws PHPUnit_Framework_ExpectationFailedException if a method isn't found
     */
    protected function assertMethodExist($class, $method) {
        $oReflectionClass = new \ReflectionClass($class);
        $this->assertTrue($oReflectionClass->hasMethod($method));
    }

    protected function assertMethodNotExist($class, $method) {
        $oReflectionClass = new \ReflectionClass($class);
        $this->assertFalse($oReflectionClass->hasMethod($method));
    }
}

class TestConfig
{
    public function getConfig()
    {
        return array(
            'importQueryLimit'                  => 1, // 1-10
            'importMessageLimit'                => 1,
            'importMessage'                     => "COP EMV Email processing finished succesfully",
            'importErrMessage'                  => "COP EMV Email file not present",
            'logDir'                            => "/log",
            'chmod.enabled'                     => FALSE,
            'strServer'                         => "http://p3tre.emv3.com/D2UTF8",
            'awsConfig'                         => array(
                "key" => "key",
                'secret' => 'secret',
                "profile" => "KG_EmailService",
                "region" => "eu-west-1",
                "QueueName" => "kg_email_service",
            ),
        );
    }
}
