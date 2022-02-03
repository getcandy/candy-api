<?php

namespace GetCandy\Api\Core\Payments;

class ThreeDSecureResponse
{
    protected $status;

    protected $transactionId;

    protected $redirect;

    protected $paRequest;

    protected $cRequest;

    protected $dsTranId;

    protected $acsTransId;

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

    public function setDsTranId($dsTranId)
    {
        $this->dsTranId = $dsTranId;

        return $this;
    }

    public function setAcsTransId($acsTransId)
    {
        $this->acsTransId = $acsTransId;

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

    public function setCRequest($cRequest)
    {
        $this->cRequest = $cRequest;

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
            'cRequest' => $this->cRequest,
            'acsTransId' => $this->acsTransId,
            'dsTranId' => $this->dsTranId,
        ];
    }
}
