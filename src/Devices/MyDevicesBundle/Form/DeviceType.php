<?php

namespace Devices\MyDevicesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeviceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fingerprint')
            ->add('userAgent')
            ->add('browserName')
            ->add('browserVersionString')
            ->add('browserWidth')
            ->add('browserHeight')
            ->add('deviceScreenWidth')
            ->add('deviceScreenHeight')
            ->add('device')
            ->add('osName')
            ->add('user')
            ->add('createdAt')
            ->add('modifiedAt');
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Devices\MyDevicesBundle\Entity\Device',
            'csrf_protection' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'device';
    }
}
