<?php

namespace Devices\MyDevicesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Devices\MyDevicesBundle\Entity\DeviceRepository;

class StatisticsController  extends Controller
{
    /**
     * Lists all Device entities.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('DevicesMyDevicesBundle:Statistics:index.html.twig');
    }

    /**
     * Get data for pie chart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataForPieChartAction(Request $request)
    {
        $response = new JsonResponse();

        $requestedField = $request->get('field');

        /** @var DeviceRepository $deviceRepository */
        $deviceRepository = $this->getDoctrine()->getRepository('DevicesMyDevicesBundle:Device');
        $data = $deviceRepository->getDataForPieChart($requestedField);

        if (false === $data) {
            $error = 'Requested field "' . $requestedField . '" is incorrect!';
        } else {
            $error = '';
            $data = array_map(create_function('$array', 'return array($array["field"], (int)$array["quantity"]);'), $data);
        }

        $response->setData(array(
            'data' => $data,
            'error' => $error
        ));

        return $response;
    }
} 