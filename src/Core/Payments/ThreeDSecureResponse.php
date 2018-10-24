<?php

namespace GetCandy\Api\Core\Payments;

class ThreeDSecureResponse
{
    protected $status;

    protected $transactionId;

    protected $redirect;

    protected $paRequest;

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function setPaRequest($paRequest)
    {
        $this->paRequest = $paRequest;

        return $this;
    }

    public function params()
    {
        return [
            'threedsecure' => true,
            'status' => $this->status,
            'transactionId' => $this->transactionId,
            'acsUrl' => $this->redirect,
            'paRequest' => $this->paRequest,
        ];
    }
}
