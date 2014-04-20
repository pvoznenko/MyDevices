<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 19/04/14
 * Time: 16:01
 */

namespace Devices\MyDevicesBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DeviceRepository  extends EntityRepository
{

    /**
     * Method will return list of all devices for specified user
     *
     * @param User $user user
     * @return array
     */
    public function findByUser(User $user)
    {
        return $this->findBy(array(
            'user' => $user
        ));
    }

    /**
     * Method will return device for specified user by device fingerprint
     *
     * @param User $user user
     * @param string $fingerprint device fingerprint
     * @return null|Device
     */
    public function findOneByUserAndFingerprint(User $user, $fingerprint)
    {
        return $this->findOneBy(array(
            'user' => $user,
            'fingerprint' => $fingerprint
        ));
    }

    /**
     * Method returns data for pie chart
     *
     * @param string $field should be one of key in array ['device-screen-size', 'browser-size', 'type-of-device', 'os', 'browser']
     * @return array|bool will return array with a data, otherwise if specified $field is not exist will return false
     */
    public function getDataForPieChart($field)
    {
        $builder = $this->createQueryBuilder('p');

        if (in_array($field, array('device-screen-size', 'browser-size'))) {
            switch ($field) {
                case 'device-screen-size':
                    $width = 'p.deviceScreenWidth';
                    $height = 'p.deviceScreenHeight';
                    break;
                default:
                    $width = 'p.browserWidth';
                    $height = 'p.browserHeight';
            }

            $query = $builder->select('CONCAT(' . $height . ', \'x\',  ' . $width . ') field', 'COUNT(p) quantity')
                ->groupBy($height . ', ' . $width)
                ->getQuery();
        } else {
            switch ($field) {
                case 'type-of-device':
                    $field = 'p.device';
                    break;
                case 'os':
                    $field = 'p.osName';
                    break;
                case 'browser':
                    $field = 'p.browserName';
                    break;
                default:
                    return false;
            }

            $query = $builder->select($field . ' field', 'COUNT(p) quantity')
                ->groupBy($field)
                ->getQuery();
        }

        return $query->getResult();
    }
} 