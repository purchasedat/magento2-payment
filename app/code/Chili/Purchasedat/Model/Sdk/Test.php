<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
    class Test
    {
        const REVISION_LATEST = 'latest';
        const REVISION_IN_REVIEW = 'in_review';
        const REVISION_PUBLISHED = 'published';
        /** @var bool */
        private $enabled = false;
        /** @var string */
        private $country;
        /** @var string */
        private $currency;
        /** @var array */
        private $languages;
        /** @var string */
        private $revisionMode;
        public function build()
        {
            return array('enabled' => $this->enabled, 'country' => $this->country, 'currency' => $this->currency, 'languages' => $this->languages, 'revision_mode' => $this->revisionMode);
        }
        /** @return bool */
        public function getEnabled()
        {
            return $this->enabled;
        }
        /**
         * @param bool $enabled Whether test mode is enabled. All other test related settings are ignored if test mode is false.
         * @return Test
         */
        public function setEnabled($enabled)
        {
            $this->enabled = $enabled;
            return $this;
        }
        /** @return string */
        public function getCountry()
        {
            return $this->country;
        }
        /**
         * @param string $country ISO3166 2 letter country code (US,DE,..)
         * @return Test
         */
        public function setCountry($country)
        {
            $this->country = $country;
            return $this;
        }
        /** @return string */
        public function getCurrency()
        {
            return $this->currency;
        }
        /**
         * @param string $currency ISO4271 3 letter currency code (USD,EUR,..)
         * @return Test
         */
        public function setCurrency($currency)
        {
            $this->currency = $currency;
            return $this;
        }
        /** @return array */
        public function getLanguages()
        {
            return $this->languages;
        }
        /**
         * @param array $languages ISO639 2 letter language codes (en,de,..)
         * @return Test
         */
        public function setLanguages($languages)
        {
            $this->languages = $languages;
            return $this;
        }
        /**
         * @return string
         *
         * @see self::REVISION_*
         */
        public function getRevisionMode()
        {
            return $this->revisionMode;
        }
        /**
         * Controls which version of items are displayed. Use Test::REVISION_* constants for values.
         *
         * @param string $revisionMode
         *
         * @see self::REVISION_*
         * @return Test
         */
        public function setRevisionMode($revisionMode)
        {
            $validValues = array(self::REVISION_PUBLISHED, self::REVISION_LATEST, self::REVISION_IN_REVIEW);
            if ($revisionMode && !in_array($revisionMode, $validValues)) {
                throw new \InvalidArgumentException($revisionMode . ' is not a valid revision mode for testing. Please use one of ' . implode(', ', $validValues) . '.');
            }
            $this->revisionMode = $revisionMode;
            return $this;
        }
    }
}