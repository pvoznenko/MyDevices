<?php

namespace Devices\MyDevicesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Devices\MyDevicesBundle\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class StatisticsControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->client = static::createClient();

        $userManager = static::$kernel->getContainer()->get('fos_user.user_manager');

        $this->user = new User();

        $this->user->setUsername('TestuserondbAdmin');
        $this->user->setEmail('TestuserondbAdmin@gmail.com');
        $this->user->setPlainPassword('TestuserondbAdmin');
        $userManager->updatePassword($this->user);
        $this->user->setEnabled(true);

        $this->em->persist($this->user);
        $this->em->flush();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->client->request('GET', '/logout');
        $this->client = null;

        $this->em->remove($this->user);
        $this->em->flush();

        $this->em->close();
    }

    /**
     * Method do test scenario for Admin user
     *
     * @covers Devices\MyDevicesBundle\Controller\StatisticsController::indexAction
     * @covers Devices\MyDevicesBundle\Controller\StatisticsController::getDataForPieChartAction
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::getDataForPieChart
     */
    public function testCompleteScenario()
    {
        // check that not users that not logged in get redirected to login page
        $this->client->request('GET', '/');
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");
        $this->client->followRedirect();

        // check that login page is available
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /login");

        // Fill in the form for login and submit it
        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => $this->user->getUsername(),
            '_password'  => $this->user->getUsername()
        ));

        $this->client->submit($form);
        $this->client->followRedirect();

        // check that we now have access to /
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");

        // check that user without admin right do not have access to statistics page
        $this->client->request('GET', '/statistics');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /statistics");

        // set admin role to user
        $this->user->setRoles(array('ROLE_ADMIN'));

        $this->em->persist($this->user);
        $this->em->flush();

        // and relogin

        $this->client->request('GET', '/logout');

        // check that login page is available
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /login");

        // Fill in the form for login and submit it
        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => $this->user->getUsername(),
            '_password'  => $this->user->getUsername()
        ));

        $this->client->submit($form);
        $this->client->followRedirect();

        // now we should have access to page /statistics
        $this->client->request('GET', '/statistics');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /statistics");

        // only post should be allowed
        $this->client->request('GET', '/statistics/pie-chart-data');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /statistics/pie-chart-data");

        $this->client->request('POST', '/statistics/pie-chart-data', array('field' => 'wrong-field'), array(), array(
            'CONTENT_TYPE'          => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for POST /statistics/pie-chart-data");

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        // we sent wrong fields name, should get error back
        $responseContent = $this->client->getResponse()->getContent();
        $expectedValue = '{"data":false,"error":"Requested field \\u0022wrong-field\\u0022 is incorrect!"}';
        $this->assertEquals($expectedValue, $responseContent);

        // now lets send correct request
        $this->client->request('POST', '/statistics/pie-chart-data', array('field' => 'type-of-device'), array(), array(
            'CONTENT_TYPE'          => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for POST /statistics/pie-chart-data");

        $responseContent = $this->client->getResponse()->getContent();
        $encoder = new JsonEncoder();
        $normalizer = new GetSetMethodNormalizer();
        $serializer = new Serializer(array($normalizer), array($encoder));

        $content = $serializer->decode($responseContent, 'json');

        $this->assertTrue(isset($content['error']) && $content['error'] == '' && isset($content['data']) && $content['data'] !== false);
    }
}
