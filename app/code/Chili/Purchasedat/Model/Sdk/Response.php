<?php
/**
 * This code is part of the purchased.at client SDK. For more information please read http://docs.purchased.at
 */
namespace Chili\Purchasedat\Model\Sdk {
    class Response
    {
        /** @var string */
        private $redirect;
        /** @var string */
        private $notification;
        /** @var bool */
        private $email;
        public function build()
        {
            return array('redirect' => $this->redirect, 'notification' => $this->notification, 'email' => $this->email);
        }
        /** @return string */
        public function getRedirect()
        {
            return $this->redirect;
        }
        /**
         * @param string $redirect The URL to redirect after completed payment.
         * @return Response
         */
        public function setRedirect($redirect)
        {
            $this->redirect = $redirect;
            return $this;
        }
        /** @return string */
        public function getNotification()
        {
            return $this->notification;
        }
        /**
         * @param string $notification The API URL to post transaction status changes to.
         * @return Response
         */
        public function setNotification($notification)
        {
            $this->notification = $notification;
            return $this;
        }
        /** @return bool */
        public function getEmail()
        {
            return $this->email;
        }
        /**
         * @param bool $email Send email to vendor provided address after successful payment.
         * @return Response
         */
        public function setEmail($email)
        {
            $this->email = $email;
            return $this;
        }
    }
}