<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Package;

use Composer\Repository\RepositoryInterface;
use Composer\Repository\PlatformRepository;

/**
 * Base class for packages providing name storage and default match implementation
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
abstract class BasePackage implements PackageInterface
{
    public static $supportedLinkTypes = array(
        'require'   => array('description' => 'requires', 'method' => 'requires'),
        'conflict'  => array('description' => 'conflicts', 'method' => 'conflicts'),
        'provide'   => array('description' => 'provides', 'method' => 'provides'),
        'replace'   => array('description' => 'replaces', 'method' => 'replaces'),
        'require-dev' => array('description' => 'requires (for development)', 'method' => 'devRequires'),
    );

    const STABILITY_STABLE  = 0;
    const STABILITY_RC      = 5;
    const STABILITY_BETA    = 10;
    const STABILITY_ALPHA   = 15;
    const STABILITY_DEV     = 20;

    public static $stabilities = array(
        'stable' => self::STABILITY_STABLE,
        'RC'     => self::STABILITY_RC,
        'beta'   => self::STABILITY_BETA,
        'alpha'  => self::STABILITY_ALPHA,
        'dev'    => self::STABILITY_DEV,
    );

    protected $name;
    protected $prettyName;

    protected $repository;
    protected $id;

    /**
     * All descendants' constructors should call this parent constructor
     *
     * @param string $name The package's name
     */
    public function __construct($name)
    {
        $this->prettyName = $name;
        $this->name = strtolower($name);
        $this->id = -1;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getPrettyName()
    {
        return $this->prettyName;
    }

    /**
     * {@inheritDoc}
     */
    public function getNames()
    {
        $names = array(
            $this->getName() => true,
        );

        foreach ($this->getProvides() as $link) {
            $names[$link->getTarget()] = true;
        }

        foreach ($this->getReplaces() as $link) {
            $names[$link->getTarget()] = true;
        }

        return array_keys($names);
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository(RepositoryInterface $repository)
    {
        if ($this->repository) {
            throw new \LogicException('A package can only be added to one repository');
        }
        $this->repository = $repository;
    }

    /**
     * checks if this package is a platform package
     *
     * @return boolean
     */
    public function isPlatform()
    {
        return $this->getRepository() instanceof PlatformRepository;
    }

    /**
     * Returns package unique name, constructed from name, version and release type.
     *
     * @return string
     */
    public function getUniqueName()
    {
        return $this->getName().'-'.$this->getVersion();
    }

    public function equals(PackageInterface $package)
    {
        $self = $this;
        if ($this instanceof AliasPackage) {
            $self = $this->getAliasOf();
        }
        if ($package instanceof AliasPackage) {
            $package = $package->getAliasOf();
        }

        return $package === $self;
    }

    /**
     * Converts the package into a readable and unique string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUniqueName();
    }

    public function getPrettyString()
    {
        return $this->getPrettyName().' '.$this->getPrettyVersion();
    }

    public function __clone()
    {
        $this->repository = null;
        $this->id = -1;
    }
}
