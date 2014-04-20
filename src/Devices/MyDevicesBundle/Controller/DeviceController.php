<?php

namespace Devices\MyDevicesBundle\Controller;

use Devices\MyDevicesBundle\Entity\DeviceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Devices\MyDevicesBundle\Entity\Device;
use Devices\MyDevicesBundle\Form\DeviceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\DBALException;
use Devices\MyDevicesBundle\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Device controller.
 *
 */
class DeviceController extends Controller
{

    /**
     * Device Repository
     *
     * @var DeviceRepository
     */
    private $deviceRepository;

    /**
     * Lists all Device entities.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $isUserAdmin = $this->isUserAdmin();

        if ($isUserAdmin) {
            $entities = $this->getDeviceRepository()->findAll();
        } else {
            $user = $this->getUser();
            $entities = $this->getDeviceRepository()->findByUser($user);
        }

        return $this->render('DevicesMyDevicesBundle:Device:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Action for checking device
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkDeviceAction(Request $request)
    {
        $response = new JsonResponse();

        $deviceFingerPrint = $request->get('fingerprint');
        /** @var User $user */
        $user = $this->getUser();

        $response->setData(array(
            'assigned' => $user->hasDeviceFingerprint($deviceFingerPrint)
        ));

        return $response;
    }

    /**
     * Action for adding new device
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addDeviceAction(Request $request)
    {
        $error = '';
        $responseArray = array();

        $deviceInfo = $request->get('device');
        /** @var User $user */
        $user = $this->getUser();

        if ($user->hasDeviceFingerprint($deviceInfo['fingerprint'])) {
            $error = 'This device already assigned to your account. Device fingerprint: ' . $deviceInfo['fingerprint'];
        } else {
            $device = new Device();
            $form = $this->createForm(new DeviceType(), $device);

            $form->submit($request);

            if ($form->isValid()) {
                $device->setUser($user);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($device);
                    $em->flush();

                    $responseArray['id'] = $device->getId();
                } catch (DBALException $e) {
                    $error = 'During adding new Device error occurred!';
                }
            } else {
                $error = $form->getErrorsAsString();
            }
        }

        $responseArray['error'] = $error;

        $response = new JsonResponse();
        $response->setData($responseArray);

        return $response;
    }

    /**
     * Finds and displays a Device entity.
     *
     * @param int $id device id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction($id)
    {
        $this->ensureActionAllowed($id);

        $entity = $this->getDeviceRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Device entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DevicesMyDevicesBundle:Device:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Device entity.
     *
     * @param int $id device id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id)
    {
        $this->ensureActionAllowed($id);

        /** @var Device $entity */
        $entity = $this->getDeviceRepository()->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Device entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('DevicesMyDevicesBundle:Device:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Device entity.
    *
    * @param Device $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Device $entity)
    {
        $form = $this->createForm(new DeviceType(), $entity, array(
            'action' => $this->generateUrl('device_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update Device'));

        return $form;
    }

    /**
     * Edits an existing Device entity.
     *
     * @param Request $request
     * @param int $id device id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateAction(Request $request, $id)
    {
        $this->ensureActionAllowed($id);

        $em = $this->getDoctrine()->getManager();

        /** @var Device $entity */
        $entity = $em->getRepository('DevicesMyDevicesBundle:Device')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Device entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('device_show', array('id' => $id)));
        }

        return $this->render('DevicesMyDevicesBundle:Device:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Device entity.
     *
     * @param Request $request
     * @param int $id device id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function deleteAction(Request $request, $id)
    {
        $this->ensureActionAllowed($id);

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $response = $this->redirect($this->generateUrl('device'));

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var Device $entity */
            $entity = $em->getRepository('DevicesMyDevicesBundle:Device')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Device entity.');
            }

            $em->remove($entity);
            $em->flush();

            $cookies = $request->cookies;

            if ($cookies->has('DEVICE_FINGERPRINT') && $cookies->get('DEVICE_FINGERPRINT') == $entity->getFingerprint()) {
                $response->headers->setCookie(new Cookie('DEVICE_FINGERPRINT', ''));
            }
        }

        return $response;
    }

    /**
     * Creates a form to delete a Device entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('device_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Method for checking if current user allowed to do action with specified device
     *
     * @param int $id device id
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException if action is not allowed for current user
     */
    private function ensureActionAllowed($id)
    {
        $isUserAdmin = $this->isUserAdmin();

        if (!$isUserAdmin) {
            /** @var User $user */
            $user = $this->getUser();

            if (!$user->hasDevice($id)) {
                throw new AccessDeniedException();
            }
        }
    }

    /**
     * Method returns Device Repository
     *
     * @return DeviceRepository
     */
    private function getDeviceRepository()
    {
        if (!($this->deviceRepository instanceof DeviceRepository)) {
            $this->deviceRepository = $this->getDoctrine()->getRepository('DevicesMyDevicesBundle:Device');
        }

        return $this->deviceRepository;
    }

    /**
     * Method returns bool value is current user granted with role admin
     *
     * @return bool
     */
    private function isUserAdmin()
    {
        return $this->get('security.context')->isGranted('ROLE_ADMIN');
    }
}
