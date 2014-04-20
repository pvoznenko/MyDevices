<?php

namespace Devices\MyDevicesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Devices\MyDevicesBundle\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class DeviceControllerTest extends WebTestCase
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
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \Devices\MyDevicesBundle\Entity\DeviceRepository
     */
    private $deviceRepo;

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

        $this->deviceRepo = $this->em->getRepository('DevicesMyDevicesBundle:Device');

        $encoder = new JsonEncoder();
        $normalizer = new GetSetMethodNormalizer();
        $this->serializer = new Serializer(array($normalizer), array($encoder));

        $this->user = new User();

        $this->user->setUsername('Testuserondb');
        $this->user->setEmail('email@gmail.com');
        $this->user->setPlainPassword('Testuserondb');
        $userManager->updatePassword($this->user);
        $this->user->setEnabled(true);

        $this->em->persist($this->user);
        $this->em->flush();

        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->client->request('GET', '/logout');
        $this->client = null;
        $this->serializer = null;
        $this->deviceRepo = null;

        $this->em->remove($this->user);
        $this->em->flush();

        $this->em->close();
    }

    /**
     * Method do full test scenario for not Admin user
     *
     * Notice: Registration and login not covered fully since it handle by vendor FOSUserBundle and it have own tests
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::indexAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::checkDeviceAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::addDeviceAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::showAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::editAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::createEditForm
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::updateAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::deleteAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::createDeleteForm
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::ensureActionAllowed
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::getDeviceRepository
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::isUserAdmin
     * @covers Devices\MyDevicesBundle\Entity\User::hasDeviceFingerprint
     * @covers Devices\MyDevicesBundle\Entity\User::hasDevice
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findByUser
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findOneByUserAndFingerprint
     */
    public function testCompleteScenario()
    {
        // checking login action
        $this->loginActionTest()
            // lets check if our user has device, he should not
            ->checkDeviceActionTest()
            // since user does not have device lets add one
            ->addDeviceActionTest()
            // you can not add the same device to one user twice
            ->addDeviceActionTest(true)
            // now check will say that device assigned to user
            ->checkDeviceActionTest(true)
            // lets check that we can see new device
            ->showActionTest()
            // lets check that page edit exist
            ->editActionTest()
            // lets check that we can delete device
            ->deleteActionTest()
            // after we removed device on user should have possibility to add it again
            ->checkDeviceActionTest();
    }

    /**
     * Check and do login of our test user
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::indexAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::getDeviceRepository
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::isUserAdmin
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findByUser
     *
     * @return $this
     */
    private function loginActionTest()
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

        return $this;
    }

    /**
     * Check if our user has device, he should not
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::checkDeviceAction
     * @covers Devices\MyDevicesBundle\Entity\User::hasDeviceFingerprint
     *
     * @depends loginActionTest
     *
     * @param bool $deviceShouldExist flag for test if device should be already assigned to user
     * @return $this
     */
    private function checkDeviceActionTest($deviceShouldExist = false)
    {
        // only POST allowed
        $this->client->request('GET', '/device/check');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/check");

        $this->client->request('POST', '/device/check', array('fingerprint' => 'test'), array(), array(
            'CONTENT_TYPE'          => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for POST /device/check");

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        // assuming that device with fingerprint 'test' is (not) assigned to the current user. Depends on flag $deviceShouldExist
        $responseContent = $this->client->getResponse()->getContent();

        $expectedValue = $deviceShouldExist ? '{"assigned":true}' : '{"assigned":false}';

        $this->assertEquals($expectedValue, $responseContent);

        return $this;
    }

    /**
     * Add device
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::addDeviceAction
     * @covers Devices\MyDevicesBundle\Entity\User::hasDeviceFingerprint
     *
     * @depends checkDeviceActionTest
     *
     * @param bool $addingShouldFail flag, if true expecting that adding will return error
     * @return $this
     */
    private function addDeviceActionTest($addingShouldFail = false)
    {
        // only POST allowed
        $this->client->request('GET', '/device/add');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/add");

        $deviceParams = array(
            'fingerprint' => 'test',
            'userAgent' => 'Test User Agent',
            'browserName' => 'Test Browser',
            'browserVersionString' => 'Test Browser Version',
            'browserWidth' => 600,
            'browserHeight' => 800,
            'deviceScreenWidth' => 300,
            'deviceScreenHeight' => 450,
            'device' => 'Test Device',
            'osName' => 'Test'
        );

        // lets add new device
        $this->client->request(
            'POST',
            '/device/add',
            array('device' => $deviceParams),
            array(),
            array(
                'CONTENT_TYPE'          => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            )
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for POST /device/add");

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        // check that new device was added
        $responseContent = $this->client->getResponse()->getContent();
        $content = $this->serializer->decode($responseContent, 'json');

        if ($addingShouldFail) {
            // error we will get if we try to add device second time
            $condition = isset($content['error']) && $content['error'] == 'This device already assigned to your account. Device fingerprint: test';
        } else {
            $condition = isset($content['error']) && isset($content['id']) && $content['error'] == '' && $content['id'] > 0;
        }

        $this->assertTrue($condition);

        return $this;
    }

    /**
     * Check show action
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::showAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::ensureActionAllowed
     * @covers Devices\MyDevicesBundle\Entity\User::hasDevice
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::isUserAdmin
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findOneByUserAndFingerprint
     *
     * @depends addDeviceActionTest
     *
     * @return $this
     */
    private function showActionTest()
    {
        $device = $this->deviceRepo->findOneByUserAndFingerprint($this->user, 'test');

        $deviceId = $device->getId();

        $this->client->request('GET', '/device/' . $deviceId . '/show');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/" . $deviceId . "/show");

        return $this;
    }

    /**
     * Check edit action
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::editAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::createEditForm
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::updateAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::ensureActionAllowed
     * @covers Devices\MyDevicesBundle\Entity\User::hasDevice
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::isUserAdmin
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findOneByUserAndFingerprint
     *
     * @depends showActionTest
     *
     * @return $this
     */
    private function editActionTest()
    {
        $device = $this->deviceRepo->findOneByUserAndFingerprint($this->user, 'test');

        $deviceId = $device->getId();

        $crawler = $this->client->request('GET', '/device/' . $deviceId . '/edit');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/" . $deviceId . "/edit");

        // and press delete button
        $form = $crawler->selectButton('device[submit]')->form();

        $this->client->submit($form);
        $this->client->followRedirect();

        // check that we now have access to /
        $this->client->request('GET', '/device/' . $deviceId . '/show');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/" . $deviceId . "/show");

        return $this;
    }

    /**
     * Check delete action
     *
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::deleteAction
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::createDeleteForm
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::ensureActionAllowed
     * @covers Devices\MyDevicesBundle\Entity\User::hasDevice
     * @covers Devices\MyDevicesBundle\Controller\DeviceController::isUserAdmin
     * @covers Devices\MyDevicesBundle\Entity\DeviceRepository::findOneByUserAndFingerprint
     *
     * @depends showActionTest
     *
     * @return $this
     */
    private function deleteActionTest()
    {
        $device = $this->deviceRepo->findOneByUserAndFingerprint($this->user, 'test');

        $deviceId = $device->getId();

        // lets show device info
        $crawler = $this->client->request('GET', '/device/' . $deviceId . '/show');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/" . $deviceId . "/show");

        // and press delete button
        $form = $crawler->selectButton('form[submit]')->form();

        $this->client->submit($form);
        $this->client->followRedirect();

        // check that we now have access to /
        $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");

        // now since device removed we should not have access to this page
        $this->client->request('GET', '/device/' . $deviceId . '/show');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /device/" . $deviceId . "/show");

        return $this;
    }
}
