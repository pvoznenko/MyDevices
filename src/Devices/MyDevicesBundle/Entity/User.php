<?php

namespace Devices\MyDevicesBundle\Entity;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Devices\MyDevicesBundle\Entity\Device", mappedBy="user", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $devices;

    public function __construct()
    {
        parent::__construct();

        $this->devices = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add devices
     *
     * @param Device $devices
     * @return User
     */
    public function addDevice(Device $devices)
    {
        $this->devices[] = $devices;

        return $this;
    }

    /**
     * Remove devices
     *
     * @param Device $devices
     */
    public function removeDevice(Device $devices)
    {
        $this->devices->removeElement($devices);
    }

    /**
     * Get devices
     *
     * @return Collection
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * Method checking if user has device with specified fingerprint
     *
     * Since Doctrine is doing Lazy load by default @see http://docs.doctrine-project.org/en/2.0.x/tutorials/extra-lazy-associations.html
     * It should not create extra performance issue
     *
     * @param string $fingerprint searchable device fingerprint
     * @return bool
     */
    public function hasDeviceFingerprint($fingerprint)
    {
        foreach ($this->getDevices() as $device) {
            /** @var Device $device */
            if ($device->getFingerprint() == $fingerprint) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method checking if user has device with specified id
     *
     * Since Doctrine is doing Lazy load by default @see http://docs.doctrine-project.org/en/2.0.x/tutorials/extra-lazy-associations.html
     * It should not create extra performance issue
     *
     * @param int $id searchable device ind
     * @return bool
     */
    public function hasDevice($id)
    {
        foreach ($this->getDevices() as $device) {
            /** @var Device $device */
            if ($device->getId() == $id) {
                return true;
            }
        }

        return false;
    }
}
