<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 17/04/14
 * Time: 14:07
 */

namespace Devices\MyDevicesBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Devices\MyDevicesBundle\Entity\DeviceRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="device", uniqueConstraints={@ORM\UniqueConstraint(name="user_id_fingerprint", columns={"user_id", "fingerprint"})})
 */
class Device
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Devices\MyDevicesBundle\Entity\User", inversedBy="device")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $fingerprint;

    /**
     * @ORM\Column(name="user_agent", type="string", nullable=true)
     */
    protected $userAgent;

    /**
     * @ORM\Column(name="browser_name", type="string", length=32, nullable=true)
     */
    protected $browserName;

    /**
     * @ORM\Column(name="browser_version_string", type="string", nullable=true)
     */
    protected $browserVersionString;

    /**
     * @ORM\Column(name="browser_width", type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $browserWidth;

    /**
     * @ORM\Column(name="browser_height", type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $browserHeight;

    /**
     * @ORM\Column(name="device_screen_width", type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $deviceScreenWidth;

    /**
     * @ORM\Column(name="device_screen_height", type="integer", nullable=true, options={"unsigned"=true})
     */
    protected $deviceScreenHeight;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $device;

    /**
     * @ORM\Column(name="os_name", type="string", length=32, nullable=true)
     */
    protected $osName;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="modified_at", type="datetime", nullable=true)
     */
    protected $modifiedAt;

    /**
     * Doctrine will update 'modified_at' on update and set 'created_at' on create
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $currentDateTime = new DateTime(date('Y-m-d H:i:s'));

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt($currentDateTime);
        }

        $this->setModifiedAt($currentDateTime);
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
     * Set fingerprint
     *
     * @param string $fingerprint
     * @return Device
     */
    public function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    /**
     * Get fingerprint
     *
     * @return string 
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * Set userAgent
     *
     * @param string $userAgent
     * @return Device
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string 
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set browserName
     *
     * @param string $browserName
     * @return Device
     */
    public function setBrowserName($browserName)
    {
        $this->browserName = $browserName;

        return $this;
    }

    /**
     * Get browserName
     *
     * @return string 
     */
    public function getBrowserName()
    {
        return $this->browserName;
    }

    /**
     * Set browserVersionString
     *
     * @param string $browserVersionString
     * @return Device
     */
    public function setBrowserVersionString($browserVersionString)
    {
        $this->browserVersionString = $browserVersionString;

        return $this;
    }

    /**
     * Get browserVersionString
     *
     * @return string 
     */
    public function getBrowserVersionString()
    {
        return $this->browserVersionString;
    }

    /**
     * Set browserWidth
     *
     * @param string $browserWidth
     * @return Device
     */
    public function setBrowserWidth($browserWidth)
    {
        $this->browserWidth = $browserWidth;

        return $this;
    }

    /**
     * Get browserWidth
     *
     * @return string 
     */
    public function getBrowserWidth()
    {
        return $this->browserWidth;
    }

    /**
     * Set browserHeight
     *
     * @param string $browserHeight
     * @return Device
     */
    public function setBrowserHeight($browserHeight)
    {
        $this->browserHeight = $browserHeight;

        return $this;
    }

    /**
     * Get browserHeight
     *
     * @return string 
     */
    public function getBrowserHeight()
    {
        return $this->browserHeight;
    }

    /**
     * Set deviceScreenWidth
     *
     * @param string $deviceScreenWidth
     * @return Device
     */
    public function setDeviceScreenWidth($deviceScreenWidth)
    {
        $this->deviceScreenWidth = $deviceScreenWidth;

        return $this;
    }

    /**
     * Get deviceScreenWidth
     *
     * @return string 
     */
    public function getDeviceScreenWidth()
    {
        return $this->deviceScreenWidth;
    }

    /**
     * Set deviceScreenHeight
     *
     * @param string $deviceScreenHeight
     * @return Device
     */
    public function setDeviceScreenHeight($deviceScreenHeight)
    {
        $this->deviceScreenHeight = $deviceScreenHeight;

        return $this;
    }

    /**
     * Get deviceScreenHeight
     *
     * @return string 
     */
    public function getDeviceScreenHeight()
    {
        return $this->deviceScreenHeight;
    }

    /**
     * Set device
     *
     * @param string $device
     * @return Device
     */
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return string 
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set osName
     *
     * @param string $osName
     * @return Device
     */
    public function setOsName($osName)
    {
        $this->osName = $osName;

        return $this;
    }

    /**
     * Get osName
     *
     * @return string 
     */
    public function getOsName()
    {
        return $this->osName;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return Device
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param DateTime $modifiedAt
     * @return Device
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set user
     *
     * @param \Devices\MyDevicesBundle\Entity\User $user
     * @return Device
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Devices\MyDevicesBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
