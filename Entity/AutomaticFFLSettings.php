<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Entity with settings for Automatic FFL integration
 *
 * @ORM\Entity
 */
class AutomaticFFLSettings extends Transport
{
    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="rfg_autoffl_transport_label",
     *      joinColumns={
     *          @ORM\JoinColumn(name="transport_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     */
    private $labels;

    /**
     * @var string
     *
     * @ORM\Column(name="autoffl_store_hash", type="string", length=255, nullable=false)
     */
    protected $autofflStoreHash;

    /**
     * @var bool
     *
     * @ORM\Column(name="autoffl_sandbox_mode", type="boolean", nullable=false, options={"default"=false})
     */
    protected $autofflTestMode = false;

    /**
     * @var string
     *
     * @ORM\Column(name="autoffl_maps_api_key", type="string", length=255, nullable=false)
     */
    protected $autofflMapsApiKey;

    /**
     * @var ParameterBag
     */
    private $settings;

    public function __construct()
    {
        $this->labels = new ArrayCollection();
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getLabels(): Collection
    {
        return $this->labels;
    }

    public function addLabel(LocalizedFallbackValue $label): AutomaticFFLSettings
    {
        if (!$this->labels->contains($label)) {
            $this->labels->add($label);
        }

        return $this;
    }

    public function removeLabel(LocalizedFallbackValue $label): AutomaticFFLSettings
    {
        if ($this->labels->contains($label)) {
            $this->labels->removeElement($label);
        }

        return $this;
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setAutoFFLStoreHash($storeHash)
    {
        $this->autofflStoreHash = $storeHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoFFLStoreHash()
    {
        return $this->autofflStoreHash;
    }

    /**
     * @return bool
     */
    public function isAutoFFLTestMode()
    {
        return $this->autofflTestMode;
    }

    /**
     * @param bool $testMode
     *
     * @return $this
     */
    public function setAutoFFLTestMode($testMode)
    {
        $this->autofflTestMode = $testMode;

        return $this;
    }

    /**
     * @param string $apiKey
     *
     * @return $this
     */
    public function setAutoFFLMapsApiKey($mapsApiKey)
    {
        $this->autofflMapsApiKey = $mapsApiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutoFFLMapsApiKey()
    {
        return $this->autofflMapsApiKey;
    }

    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    'labels' => $this->getLabels()->toArray(),
                    'store_hash' => $this->getAutoFFLStoreHash(),
                    'test_mode' => $this->isAutoFFLTestMode(),
                    'maps_api_key' => $this->getAutoFFLMapsApiKey()
                ]
            );
        }

        return $this->settings;
    }
}
